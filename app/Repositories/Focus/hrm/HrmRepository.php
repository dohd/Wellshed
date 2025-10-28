<?php

namespace App\Repositories\Focus\hrm;

use App\Models\Access\Role\Role;
use DB;
use App\Models\hrm\Hrm;
use App\Exceptions\GeneralException;
use App\Models\Access\Permission\Permission;
use App\Models\attendance\Attendance;
use App\Models\Company\Company;
use App\Models\Company\SmsSetting;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Utils\MessageUtil;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Class HrmRepository.
 */
class HrmRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */


    const MODEL = Hrm::class;
    protected $file_picture_path;
    protected $file_sign_path;
    protected $file_cv_path;
    protected $storage;
    protected $messageUtil;

    /**
     * Constructor.
     */
    public function __construct(MessageUtil $messageUtil)
    {
        $this->file_picture_path = 'img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;
        $this->file_sign_path = 'img' . DIRECTORY_SEPARATOR . 'signs' . DIRECTORY_SEPARATOR;
        $this->file_cv_path = 'img' . DIRECTORY_SEPARATOR . 'cvs' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
        $this->messageUtil = $messageUtil;
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query()
            ->withoutGlobalScopes()
            ->where('ins', Auth::user()->ins);

        if (request('rel_type') == 2 and request('rel_id')) {
            $q->whereHas('meta', function ($s) {
                return $s->where('department_id', '=', request('rel_id', 0));
            });
        }

        $q->where('status', request('employeeStatusFilter'));

        return $q->with(['monthlysalary'])->get(['id','tid', 'email', 'picture', 'first_name', 'last_name', 'status', 'created_at']);
    }

    /**
     * Get Attendance Data
     */
    public function getForAttendanceDataTable()
    {
        $q = Attendance::query();

        $q->when(request('rel_id'), function ($q) {
            $q->where('user_id', request('rel_id'));
        });

        return $q->get();
    }


    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'employee') {
                if (isset($val['picture'])) 
                    $input[$key]['picture'] = $this->uploadPicture($val['picture'], $this->file_picture_path);
                if (isset($val['signature'])) 
                    $input[$key]['signature'] = $this->uploadPicture($val['signature'], $this->file_sign_path);
                if (isset($val['cv'])) 
                    $input[$key]['cv'] = $this->uploadPicture($val['cv'], $this->file_cv_path);
            }
            if ($key == 'meta') {
                if (isset($val['id_front'])) 
                    $input[$key]['id_front'] = $this->uploadPicture($val['id_front'], $this->file_sign_path);
                if (isset($val['id_back'])) 
                    $input[$key]['id_back'] = $this->uploadPicture($val['id_back'], $this->file_sign_path);
            }
        }

        DB::beginTransaction();

        $role_id = $input['employee']['role'];
        $role = Role::find($role_id);
        if ($role && $role->status == 1) {
            $password = $input['employee']['email'] ?? @$input['employee']['personal_email'];
            if (!$password) $password = $input['meta']['secondary_contact'] ?? @$input['meta']['primary_contact'];

            // create hrm
            $username = random_username();
            // $password = random_password();
            $input['employee'] = array_replace($input['employee'], [
                'username' => $username,
                'password' => $password, //(new DateTime('now'))->format('y') . '-' . 'Pa$$w0rd!',
                'created_by' => auth()->user()->id,
                'confirmed' => 1,
            ]);
            unset($input['employee']['role']);
            // dd($input['employee'], $input);
            $hrm = Hrm::create($input['employee']);

            // create hrm meta
            $input['meta'] = array_replace($input['meta'], [
                'user_id' => $hrm->id,
            ]);
            unset($input['meta']['alternative_contact']);
            $hrm->meta()->create($input['meta']);

            // create user role and permissions
            $hrm->roleUser()->create(['user_id' => $hrm->id, 'role_id' => $role_id]);
            if (@$input['permission']) $hrm->permissions()->attach($input['permission']);

            if ($hrm) {
                try {
                    DB::transaction(function () use ($input, $password) {
                        $sms_server = SmsSetting::where('ins', auth()->user()->ins)->first();
                        $company = Company::find(auth()->user()->ins);
                        $link = request()->getSchemeAndHttpHost();
                        $clientName = @$input['employee']['first_name'] . ' ' . @$input['employee']['last_name'];
                
                        $text = "Dear {$clientName}, \r\nYour account was successfully created at {$link}\r\n"
                              . "Email: {$input['employee']['email']} \r\nPassword: {$password} \r\n\r\nRegards, \r\n {$company->sms_email_name}";
                
                        $email_input = [
                            'text' => $text,
                            'subject' => 'Login Credentials',
                            'mail_to' => $input['employee']['email'],
                            'customer_name' => $clientName,
                        ];
                
                        if ($sms_server) {
                            $cost_per_160 = 0.6;
                            $charCount = strlen($text);
                            $data = [
                                'subject' => $text,
                                'user_type' => 'employee',
                                'delivery_type' => 'now',
                                'message_type' => 'single',
                                'phone_numbers' => $input['meta']['primary_contact'],
                                'sent_to_ids' => $input['meta']['user_id'],
                                'characters' => $charCount,
                                'cost' => $cost_per_160,
                                'user_count' => 1,
                                'total_cost' => $cost_per_160 * ceil($charCount / 160),
                            ];
                
                            $result = SendSms::create($data);
                
                            try {
                                (new RosesmsRepository(auth()->user()->ins))
                                    ->textlocal($input['meta']['primary_contact'], $text, $result);
                            } catch (\Exception $e) {
                                Log::error('SMS sending failed: ' . $e->getMessage());
                                // Continue execution
                            }
                        }
                
                        try {
                            $email = (new RosemailerRepository(auth()->user()->ins))
                                ->send($email_input['text'], $email_input);
                
                            $email_output = json_decode($email);
                            if ($email_output->status === "Success") {
                                $email_data = [
                                    'text_email' => $email_input['text'],
                                    'subject' => $email_input['subject'],
                                    'user_emails' => $email_input['mail_to'],
                                    'user_ids' => $input['meta']['user_id'],
                                    'user_type' => 'employee',
                                    'delivery_type' => 'now',
                                    'status' => 'sent'
                                ];
                                SendEmail::create($email_data);
                            }
                        } catch (\Exception $e) {
                            Log::error('Email sending failed: ' . $e->getMessage());
                            // Continue execution
                        }
                    });
                } catch (\Exception $e) {
                    Log::error('Transaction failed: ' . $e->getMessage());
                    // You can return a response or throw further if needed
                }

                DB::commit();
                return $hrm;
            }
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Hrm $hrm
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Hrm $hrm, array $input)
    {
        foreach ($input as $key => $val) {
            if ($key == 'employee') {
                if (isset($val['picture'])) {
                    if ($this->storage->exists($this->file_picture_path . $hrm->picture)) {
                        $this->storage->delete($this->file_picture_path . $hrm->picture);
                    }
                    $input[$key]['picture'] = $this->uploadPicture($val['picture'], $this->file_picture_path);
                }
                if (isset($val['signature'])) {
                    if ($this->storage->exists($this->file_sign_path . $hrm->signature)) {
                        $this->storage->delete($this->file_sign_path . $hrm->signature);
                    }
                    $input[$key]['signature'] = $this->uploadPicture($val['signature'], $this->file_sign_path);
                }
                if (isset($val['cv'])) {
                    if ($this->storage->exists($this->file_cv_path . $hrm->cv)) {
                        $this->storage->delete($this->file_cv_path . $hrm->cv);
                    }
                    $input[$key]['cv'] = $this->uploadPicture($val['cv'], $this->file_cv_path);
                }
            }
            if ($key == 'meta') {
                if (isset($val['id_front'])) {
                    if ($this->storage->exists($this->file_sign_path . $hrm->id_front)) {
                        $this->storage->delete($this->file_sign_path . $hrm->id_front);
                    }
                    $input[$key]['id_front'] = $this->uploadPicture($val['id_front'], $this->file_sign_path);
                }
                if (isset($val['id_back'])) {
                    if ($this->storage->exists($this->file_sign_path . $hrm->id_back)) {
                        $this->storage->delete($this->file_sign_path . $hrm->id_back);
                    }
                    $input[$key]['id_back'] = $this->uploadPicture($val['id_back'], $this->file_sign_path);
                }
            }
        }

        DB::beginTransaction();

        $role_id = $input['employee']['role'];
        $role = Role::find($role_id);
        if ($role && $role->status == 1) {
            unset($input['employee']['role']);

            // update hrm
            $hrm->update($input['employee']);
            $hrm->meta()->update($input['meta']);
            $hrm->roleUser()->update(compact('role_id'));
            $hrm->permissions()->detach();

            // update permissions
            if (@$input['permission']) {
                $hrm->permissions()->attach($input['permission']);
                // add tenant-management permissions
                if ($hrm->business->is_main) {
                    $tenantMgt = Permission::whereRaw("name LIKE '%account-service%' or name LIKE '%business-account%'")
                        ->select('permissions.id')
                        ->distinct()
                        ->pluck('permissions.id')
                        ->toArray();
                    $hrm->permissions()->attach($tenantMgt);
                } 
            }
            
            DB::commit();
            return true;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\hrm\Hrm $hrm
     * @return bool
     * @throws GeneralException
     */
    public function delete(Hrm $hrm)
    {
        $errorMsg = '';
        if (auth()->user()->id == $hrm->id) $errorMsg = 'Cannot Delete Self';
        if ($hrm->tenant_customer_id) $errorMsg = 'Cannot Delete Super User';
        if ($errorMsg) throw ValidationException::withMessages([$errorMsg]);

        DB::beginTransaction();

        $hrm->meta()->delete();
        $hrm->roleUser()->delete();
        $hrm->profile()->delete();
        $hrm->permissions()->detach();
        if ($hrm->delete()) {
            DB::commit();
            return true;
        }
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($logo, $path)
    {
        $image_name = time() . $logo->getClientOriginalName();
        $this->storage->put($path . $image_name, file_get_contents($logo->getRealPath()));
        return $image_name;
    }
}
