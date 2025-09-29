<?php

namespace App\Repositories\Focus\purchase_request;

use App\Exceptions\GeneralException;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\hrm\Hrm;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\purchase_request\PurchaseRequestItem;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseRequestRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = PurchaseRequest::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return PurchaseRequest $purchase_request
     */
    public function create(array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'expect_date'])) 
                $data[$key] = date_for_database($val);
        }

        $tid = PurchaseRequest::where('ins', auth()->user()->ins)->max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1;
        
        $result = PurchaseRequest::create($data);
        //line items
        $data_items = $input['data_items'];
        $data_items = array_map(function($item) use($result){
            return array_replace($item, [
                'purchase_request_id' => $result['id'],
                'ins' => $result['ins'],
                'user_id' => $result['user_id'],
                'qty' =>  floatval(str_replace(',', '', $item['qty'])),
                'price' =>  floatval(str_replace(',', '', $item['price'])),
            ]);
        },$data_items);
        PurchaseRequestItem::insert($data_items);
        if ($result){
            DB::commit();
            $this->get_users($data['reviewer_ids'], $result);
            return $result;
        } 
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param PurchaseRequest $purchase_request
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(PurchaseRequest $purchase_request, array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'expect_date'])) 
                $data[$key] = date_for_database($val);
        }
        $purchase_request->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $purchase_request->items()->whereNotIn('id', $item_ids)->delete();

        foreach ($data_items as $item){
            foreach ($item as $key => $val) {
                if (in_array($key, ['price', 'qty']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $purchase_request_item = PurchaseRequestItem::firstOrNew(['id' => $item['id']]);
            $purchase_request_item->fill(array_replace($item, ['purchase_request_id' => $purchase_request['id'], 'ins' => $purchase_request['ins']]));
            if (!$purchase_request_item->id) unset($purchase_request_item->id);
            $purchase_request_item->save();
        }

        if ($purchase_request) {
            DB::commit();
            return $purchase_request;
        }

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param PurchaseRequest $purchase_request
     * @throws GeneralException
     * @return bool
     */
    public function delete(PurchaseRequest $purchase_request)
    {
        try {
            if ($purchase_request->purchaseRequisitions()->exists()) {
                throw ValidationException::withMessages(['Purchase Requisition has been attached!!']);
            }

            if ($purchase_request->status == 'approved' && $purchase_request->item_type == 'project') {
                foreach ($purchase_request->items as $item) {

                    if ($item->budget_item) {
                        $item->budget_item->qty_requested -= $item->qty;

                        if ($item->budget_item->qty_requested < 0) {
                            $item->budget_item->qty_requested = 0;
                        }

                        $item->budget_item->save();
                    }

                    if ($item->milestone_item) {
                        $item->milestone_item->qty_requested -= $item->qty;

                        if ($item->milestone_item->qty_requested < 0) {
                            $item->milestone_item->qty_requested = 0;
                        }

                        $item->milestone_item->save();
                    }
                }
            }

            // Delete items and then the request
            if ($purchase_request->items()->delete() && $purchase_request->delete()) {
                return true;
            }

            throw new GeneralException('Error Deleting Material Requisition!!');

        } catch (\Throwable $e) {
            // Optionally log the error
            \Log::error('Purchase Request Deletion Failed', [
                'error' => $e->getMessage(),
                'purchase_request_id' => $purchase_request->id
            ]);

            throw $e instanceof ValidationException ? $e : new GeneralException('Error deleting Material Request: ' . $e->getMessage());
        }
    }


    public function get_users($user_ids, $result)
    {
        $ids = explode(',', $user_ids);
        $users = Hrm::whereIn('id', $ids)->get();
        
        $company = Company::find(auth()->user()->ins);
        $setting = RecipientSetting::where('type', 'mr_notification')
                    ->where('ins', $company->id)
                    ->first();

        foreach ($users as $user) {
            $this->notify_user($user, $result, $company, $setting);
        }
    }

    private function notify_user($user, $mr, $company, $setting)
    {
        if (!$user || !$user->meta) {
            return;
        }

        $phone_number = $this->formatPhoneNumber($user->meta->secondary_contact);
        if (!$phone_number) {
            return;
        }

        $mr_no = gen4tid('REQ-', $mr->tid);
        $companyName = "From " . Str::title($company->sms_email_name) . ":";
        $userName = $user->fullname ?? '';
        $userEmail = $user->personal_email;

        $emailText = "Dear $userName, 
        
        {$company->cname}";

        $smsText = $companyName . " Dear $userName, a Material Requisition {$mr_no} has been created for you to review. Please log in and review the document. If you have questions, contact us.";

        if ($setting->sms === 'yes') {
            $this->sendSms($phone_number, $smsText, $user->id);
        }

        if ($setting->email === 'yes') {
            $this->sendEmail($userEmail, "New Material Requisition (MR) Created for Your Review", $emailText, $user->id);
        }
    }

    private function formatPhoneNumber($number)
    {
        $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';
        $cleanedNumber = preg_replace('/\D/', '', $number);

        if (!preg_match($pattern, $cleanedNumber)) {
            return null;
        }

        return preg_match('/^01\d{8}$/', $cleanedNumber) 
            ? '254' . substr($cleanedNumber, 1) 
            : $cleanedNumber;
    }

    private function sendSms($phone_number, $message, $user_id)
    {
        $cost_per_160 = 0.6;
        $charCount = strlen($message);
        $blocks = ceil($charCount / 160);

        $smsData = [
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'single',
            'phone_numbers' => $phone_number,
            'sent_to_ids' => $user_id,
            'subject' => $message,
            'characters' => $charCount,
            'cost' => $cost_per_160,
            'user_count' => 1,
            'total_cost' => $cost_per_160 * $blocks,
        ];

        $smsResult = SendSms::create($smsData);
        (new RosesmsRepository(auth()->user()->ins))->textlocal($phone_number, $message, $smsResult);
    }

    private function sendEmail($to, $subject, $text, $user_id)
    {
        $email = (new RosemailerRepository(auth()->user()->ins))->send($text, ['subject' => $subject, 'mail_to' => $to]);
        $email_output = json_decode($email);

        if ($email_output->status === "Success") {
            SendEmail::create([
                'text_email' => $text,
                'subject' => $subject,
                'user_emails' => $to,
                'user_ids' => $user_id,
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
                'status' => 'sent'
            ]);
        }
    }

}
