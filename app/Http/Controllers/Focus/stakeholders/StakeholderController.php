<?php

namespace App\Http\Controllers\Focus\stakeholders;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\Access\Permission\Permission;
use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\Role\Role;
use App\Models\Company\Company;
use App\Models\Company\EmailSetting;
use App\Models\employee\RoleUser;
use App\Models\hrm\Hrm;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\stakeholder\Stakeholder;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StakeholderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (!access()->allow('view-stakeholders')) return redirect()->back();

        $mail_server = EmailSetting::withoutGlobalScopes()->where('ins',Auth::user()->ins)->first();

        $companies = Stakeholder::all()->pluck('sh_company');
        $shAuthorizerIds = Stakeholder::all()->pluck('sh_authorizer_id')->toArray();
        $authorizers = Hrm::whereIn('id', $shAuthorizerIds)
            ->get()
            ->map(fn($au) => (object) ['name' => $au->fullname, 'id' => $au->id]);


        if ($request->ajax()) {

            $stakeholders = Stakeholder::orderBy('first_name')
                ->when(request('companyFilter'), function ($q) {
                    $q->where('sh_company', request('companyFilter'));
                })
                ->when(request('authorizerFilter'), function ($q) {
                    $q->where('sh_authorizer_id', request('authorizerFilter'));
                })
                ->get()
                ->map(function ($sh) {
                    return (object)[
                        'id' => $sh->id,
                        'name' => $sh->first_name . ' ' . $sh->last_name,
                        'email' => $sh->email,
                        'sh_authorizer' => $sh->authorizer ? $sh->authorizer->first_name . ' ' . $sh->authorizer->last_name : null,
                        'sh_primary_contact' => $sh->sh_primary_contact,
                        'sh_secondary_contact' => $sh->sh_secondary_contact,
                        'sh_gender' => $sh->sh_gender,
                        'sh_id_number' => $sh->sh_id_number,
                        'sh_company' => $sh->sh_company,
                        'sh_designation' => $sh->sh_designation,
                        'sh_access_reason' => $sh->sh_access_reason,
                        'sh_access_start' => $sh->sh_access_start,
                        'sh_access_end' => $sh->sh_access_end,
                    ];
                });

            return Datatables::of($stakeholders)
                ->editColumn('sh_access_start', function ($sh) {

                    return (new DateTime($sh->sh_access_start))->format('d/m/Y H:i');
                })
                ->editColumn('sh_access_end', function ($sh) {

                    return (new DateTime($sh->sh_access_end))->format('d/m/Y H:i');
                })
                ->addColumn('action', function ($sh) {

                    $view = '<a href="' . route('biller.stakeholders.show', $sh->id) . '" class="btn btn-twitter round mr-1">View</a>';

                    $edit = '<a href="' . route('biller.stakeholders.edit', $sh->id) . '" class="btn btn-secondary round mr-1">Edit</a>';

                    $delete = '<form action="' . route('biller.stakeholders.destroy', $sh->id) . '" method="POST" style="display:inline-block;">' .
                        csrf_field() .
                        method_field("DELETE") .
                        '<button type="submit" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this Stakeholder?\')">Delete</button>' .
                        '</form>';

                    //            if (!access()->allow('view-employee-notice')) $view = '';
                    //            if (!access()->allow('edit-employee-notice')) $edit = '';
                    //            if (!access()->allow('delete-employee-notice')) $delete = '';

                    return $view . $edit . $delete;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('focus.stakeholders.index', compact('authorizers', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!access()->allow('create-stakeholders')) return redirect()->back();

        $roles=Role::where('status',1)
            ->where('ins', auth()->user()->ins)
            ->get();

        $general['create'] = 1;

        $employees = Hrm::select('id', 'first_name', 'last_name')->get();

        return view('focus.stakeholders.create', compact('roles', 'general', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!access()->allow('create-stakeholders')) return redirect()->back();

        $validated = $request->validate([
            'status' => ['required', 'in:0,1'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'sh_id_number' => ['required', 'string', 'max:20'],
            'sh_gender' => ['required', 'in:Male,Female'],
            'sh_primary_contact' => ['required'],
            'sh_secondary_contact' => ['nullable'],
            'email' => ['required', 'email', 'max:255'],
            'sh_company' => ['nullable', 'string', 'max:200'],
            'sh_designation' => ['nullable', 'string', 'max:300'],
            'sh_access_reason' => ['nullable', 'string', 'max:500'],
            'sh_authorizer_id' => ['required', 'integer'],
            'sh_access_start' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'sh_access_end' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'login_access' => ['required', 'in:0,1'],
            'permission' => ['nullable', 'array'],
            'permission.*' => ['integer'],
        ]);


        try{
            DB::beginTransaction();

            $stakeholder = new Stakeholder();
            $stakeholder->is_stakeholder = 1;
            $stakeholder->fill($validated);


            $userPassword = random_password();
            $stakeholder->password = bcrypt($userPassword);
            $stakeholder->confirmed = 1;

            $stakeholder->save();

            RoleUser::create(['user_id' => $stakeholder->id, 'role_id' => $request->role]);
            $stakeholder->permissions()->attach($validated['permission']);

            if ($stakeholder) {
                $company = Company::find(auth()->user()->ins);
                $link = request()->getSchemeAndHttpHost();
                $text = "From: " . $company->sms_email_name . " Account Created Successfully. Email: {$stakeholder['email']}, Password: {$userPassword}, link is: {$link}";
                // $this->messageUtil->sendMessage($input['meta']['primary_contact'], $email_input['text']);
                $email_input = [
                    'text' => "Account Created Successfully. Email: {$stakeholder['email']}, Password: {$userPassword}, link is: {$link}",
                    'subject' => 'Login Credentials',
                    'mail_to' => $stakeholder['email'],
                    'customer_name' => @$stakeholder['first_name'] . ' ' . @$stakeholder['last_name'],
                ];
                $cost_per_160 = 0.6;
                $charCount = strlen($text);
                $data = [
                    'subject' => $text,
                    'user_type' => 'employee',
                    'delivery_type' => 'now',
                    'message_type' => 'single',
                    'phone_numbers' => $stakeholder['sh_primary_contact'],
                    'sent_to_ids' => $stakeholder->id,
                    'characters' => $charCount,
                    'cost' => $cost_per_160,
                    'user_count' => 1,
                    'total_cost' => $cost_per_160 * ceil($charCount / 160),

                ];
                $result = SendSms::create($data);
                (new RosesmsRepository(auth()->user()->ins))->textlocal($stakeholder['sh_primary_contact'], $text, $result);

                $email = (new RosemailerRepository(auth()->user()->ins))->send($email_input['text'], $email_input);
                $email_output = json_decode($email);

                if ($email_output->status === "Success") {

                    $email_data = [
                        'text_email' => $email_input['text'],
                        'subject' => $email_input['subject'],
                        'user_emails' => $email_input['mail_to'],
                        'user_ids' => $stakeholder->id,
                        'user_type' => 'employee',
                        'delivery_type' => 'now',
                        'status' => 'sent'
                    ];
                    SendEmail::create($email_data);
                }
            }

            DB::commit();
        }
        catch (\Exception $e) {

            DB::rollBack();

            return [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return new RedirectResponse(route('biller.stakeholders.index'), ['flash_success' => "Stakeholder Saved Successfully"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!access()->allow('view-stakeholders')) return redirect()->back();

        $stakeholder = Stakeholder::where('id', $id)
            ->get()
            ->map(function ($sh) {
                return [
                    'id' => $sh->id,
                    'name' => $sh->first_name . ' ' . $sh->last_name,
                    'email' => $sh->email,
                    'sh_authorizer' => $sh->authorizer ? $sh->authorizer->first_name . ' ' . $sh->authorizer->last_name : null,
                    'sh_primary_contact' => $sh->sh_primary_contact,
                    'sh_secondary_contact' => $sh->sh_secondary_contact,
                    'sh_gender' => $sh->sh_gender,
                    'sh_id_number' => $sh->sh_id_number,
                    'sh_company' => $sh->sh_company,
                    'sh_designation' => $sh->sh_designation,
                    'sh_access_reason' => $sh->sh_access_reason,
                    'sh_access_start' => $sh->sh_access_start,
                    'sh_access_end' => $sh->sh_access_end,
                    'role' => $sh->role,
                    'permissions' => $sh->permissions,
                ];
            })
            ->first();


        return view('focus.stakeholders.view', compact('stakeholder'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!access()->allow('edit-stakeholders')) return redirect()->back();


        $stakeholder = Stakeholder::where('id', $id)->with(['role','permissions'])->first();

        $roles = Role::where('status',1)
            ->where('ins', auth()->user()->ins)
            ->get();

        $general['create'] = $id;
        $permissions_all = Permission::whereHas('roles', function ($q) use ($stakeholder) {
            $q->where('role_id', $stakeholder->role->id);
        })->get()->toArray();
        $permissions = PermissionUser::all()->keyBy('id')->where('user_id', $general['create'])->toArray();

        $employees = Hrm::select('id', 'first_name', 'last_name')->get();


        return view('focus.stakeholders.edit', compact('stakeholder', 'roles', 'permissions', 'permissions_all', 'general', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id)
    {
        if (!access()->allow('edit-stakeholders')) return redirect()->back();

        $validated = $request->validate([
             'status' => ['required', 'in:0,1'],
             'first_name' => ['required', 'string', 'max:255'],
             'last_name' => ['nullable', 'string', 'max:255'],
             'sh_id_number' => ['required', 'string', 'max:20'],
             'sh_gender' => ['required', 'in:Male,Female'],
             'sh_primary_contact' => ['required'],
             'sh_secondary_contact' => ['nullable'],
             'email' => ['required', 'email', 'max:255'],
             'sh_company' => ['nullable', 'string', 'max:200'],
             'sh_designation' => ['nullable', 'string', 'max:300'],
             'sh_access_reason' => ['nullable', 'string', 'max:500'],
             'sh_authorizer_id' => ['required', 'integer'],
             'sh_access_start' => ['nullable', 'date_format:Y-m-d\TH:i'],
             'sh_access_end' => ['nullable', 'date_format:Y-m-d\TH:i'],
             'login_access' => ['required', 'in:0,1'],
             'permission' => ['nullable', 'array'],
             'permission.*' => ['integer'],
         ]);

        try{
            DB::beginTransaction();

            $stakeholder = Stakeholder::find($id);
            $stakeholder->fill($validated);

            $stakeholder->save();

            $role_user = RoleUser::where('user_id', $id)->first();
            if ($role_user) $role_user->update(['role_id' => $request->role]);

            PermissionUser::where('user_id', $id)->delete();
            $stakeholder->permissions()->attach($validated['permission']);

            DB::commit();
        }
        catch (\Exception $e) {

            DB::rollBack();

            return [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return new RedirectResponse(route('biller.stakeholders.index'), ['flash_success' => "Stakeholder Updated Successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!access()->allow('delete-stakeholders')) return redirect()->back();

        try{
            DB::beginTransaction();

            $stakeholder = Stakeholder::find($id);
            $stakeholder->delete();

            DB::commit();
        }
        catch (\Exception $e) {

            DB::rollBack();

            return [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return new RedirectResponse(route('biller.stakeholders.index'), ['flash_success' => "Stakeholder Deleted successfully"]);
    }
}
