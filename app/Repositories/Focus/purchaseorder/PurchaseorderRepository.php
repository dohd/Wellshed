<?php

namespace App\Repositories\Focus\purchaseorder;

use App\Models\project\ProjectMileStone;
use App\Models\purchaseorder\Purchaseorder;
use App\Exceptions\GeneralException;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\hrm\Hrm;
use App\Models\items\PurchaseorderItem;
use App\Repositories\BaseRepository;
use App\Models\queuerequisition\QueueRequisition;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Class PurchaseorderRepository.
 */
class PurchaseorderRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Purchaseorder::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('supplier_id'), function ($q) {
            $q->where('supplier_id', request('supplier_id'));
        })->when(request('status'), function ($q) {
            if (request('status') == 'Closed') $q->where('closure_status', 1);   
            else $q->where('status', request('status'))->where('closure_status', 0);         
        });

        return $q;
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {   
        $order = $input['order'];
        foreach ($order as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'fx_curr_rate',
            ];
            if (in_array($key, ['date', 'due_date'], 1))
                $order[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $order[$key] = numberClean($val);
        }
        
        DB::beginTransaction();
        
        $tid = Purchaseorder::where('ins', $order['ins'])->max('tid');
        if ($order['tid'] <= $tid) $order['tid'] = $tid+1;
        $result = Purchaseorder::create($order);

        $order_items = $input['order_items'];
        foreach ($order_items as $item) {
            if (@$item['type'] == 'Stock' && !$item['uom'])
            throw ValidationException::withMessages(['Unit of Measure (uom) required for Inventory Items']);
        }
        $order_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'purchaseorder_id' => $result->id,
                'product_code' => @$v['product_code'],
                'rate' => numberClean($v['rate']),
                'taxrate' => numberClean($v['taxrate']),
                'amount' => numberClean($v['amount'])
            ]);
        }, $order_items);
        
        foreach ($order_items as $order_item) {
            if ($order_item['type'] == 'Requisit') {
                $queuerequisition = QueueRequisition::where('product_code', $order_item['product_code'])->where('status', '1')->update(['status'=>$order['tid']]);
                $order_item['type'] = 'Stock';
            }
        }
        PurchaseorderItem::insert($order_items);

        /** Updating Budget Line Balance **/
        $budgetLine = ProjectMileStone::find($input['order']['project_milestone']);
        if(!empty($budgetLine)){
            $budgetLine->balance -= floatval(str_replace(',', '', $input['order']['grandttl']));
            $budgetLine->save();
        }

        if ($result) {
            DB::commit();
            $this->get_users($order['user_ids'], $result);
            return $result;   
        }
    }

    public function get_users($user_ids, $result)
    {
        $ids = explode(',', $user_ids);
        $users = Hrm::whereIn('id', $ids)->get();
        foreach($users as $user)
        {
            $this->notify_users($user, $result);
        }
    }

    public function notify_users($user, $po)
    {
        if ($user) {

            $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';

            if ($user->meta) {
                $cleanedNumber = preg_replace('/\D/', '', $user->meta->secondary_contact);
                
                if (preg_match($pattern, $cleanedNumber)) {
                    // Convert 01xxxxxxxx to 2541xxxxxxxx format
                    if (preg_match('/^01\d{8}$/', $cleanedNumber)) {
                        $phone_number = '254' . substr($cleanedNumber, 1); // Replace '0' with '254'
                    } else {
                        $phone_number = $cleanedNumber;
                    }

                    $user_id = $user->id;
                }
            }

            $userName = @$user->fullname;
            $userEmail = $user['personal_email'];
            $userPhone = $phone_number;
            $user_id = $user->id;
            
            // Find the company
            $company = Company::find(auth()->user()->ins);
            $setting = RecipientSetting::where('type', 'lpo_notification')->where('ins', $company->id)->first();
            $companyName = "From " . Str::title($company->sms_email_name) . ":";
            $po_no = gen4tid('PO-',$po->tid);
        
            // Initialize email and SMS data
            $emailInput = [
                'subject' => "New LPO Created for Your Review",
                'mail_to' => $userEmail,
                'name' => $userName,
            ];
            
            $smsData = [
                'user_type' => 'employee',
                'delivery_type' => 'now',
                'message_type' => 'single',
                'phone_numbers' => $userPhone,
                'sent_to_ids' => $user_id,
            ];
            
        
            // Handle each status case
            if ($user) {
                // For 
                $emailInput['text'] = "Dear $userName, 
                We are pleased to inform you that a new Local Purchase Order (LPO) has been created for you. Kindly take a moment to review the details of the LPO at your earliest convenience.
                LPO Details:
                    - LPO Number: {$po_no}
                    - LPO Date: {$po->date}

                Should you have any questions or need further assistance, feel free to reach out to us.
                Thank you,
                {$company->cname}";
                $message = $companyName . " Dear $userName, an LPO {$po_no} has been created for you to review. Please log in to your account and review the document at your earliest convenience. If you have any questions, please contact us.\n\n";
                $smsText = $message;
            } 
        
            // Only proceed 
            if (isset($smsText)) {
                if($setting->sms == 'yes')
                {
                    // Prepare SMS data
                    $smsData['subject'] = $smsText;
                    $cost_per_160 = 0.6;
                    $charCount = strlen($smsText);
                    $blocks = ceil($charCount / 160);
                    $smsData['characters'] = $charCount;
                    $smsData['cost'] = $cost_per_160;
                    $smsData['user_count'] = 1;
                    $smsData['total_cost'] = $cost_per_160*$blocks;
                    // Send SMS and email
                    $smsResult = SendSms::create($smsData);
                    (new RosesmsRepository(auth()->user()->ins))->textlocal($userPhone, $smsText, $smsResult);
                }

                if($setting->email == 'yes')
                {
                    $email = (new RosemailerRepository(auth()->user()->ins))->send($emailInput['text'], $emailInput);
                    $email_output = json_decode($email);
                    if ($email_output->status === "Success"){

                        $email_data = [
                            'text_email' => $emailInput['text'],
                            'subject' => $emailInput['subject'],
                            'user_emails' => $emailInput['mail_to'],
                            'user_ids' => $user_id,
                            'ins' => auth()->user()->ins,
                            'user_id' => auth()->user()->id,
                            'status' => 'sent'
                        ];
                        SendEmail::create($email_data);
                    }
                }
            }
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Purchaseorder $purchaseorder
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($purchaseorder, array $input)
    {
        $order = $input['order'];

        /** Handling milestone changes */
        $budgetLine = ProjectMileStone::find($purchaseorder->project_milestone);
        $newBudgetLine = ProjectMileStone::find($input['order']['project_milestone']);

        $milestoneChanged = intval($purchaseorder->project_milestone) !== intval($input['order']['project_milestone']);
        $grandTotalChanged = floatval($purchaseorder->grandttl) !== floatval(str_replace(',', '', $input['order']['grandttl']));
        $newMilestoneZero = intval($input['order']['project_milestone']) === 0;
        $oldMilestoneZero = intval($purchaseorder->project_milestone) === 0;

        DB::beginTransaction();

        /** If the milestone HAS CHANGED and grand total HAS CHANGED  */
        if($milestoneChanged && $grandTotalChanged){
            if (!$oldMilestoneZero) {
                $budgetLine->balance = $budgetLine->balance + $purchaseorder->grandttl;
                $budgetLine->save();
            }
            if (!$newMilestoneZero){

                $newBudgetLine->balance -= floatval(str_replace(',', '', $input['order']['grandttl']));
                $newBudgetLine->save();
            }
        }
        /** If the milestone has NOT changed but grand total HAS CHANGED */
        else if (!$milestoneChanged && $grandTotalChanged){
            if (!$oldMilestoneZero) {
                $budgetLine->balance = ($budgetLine->balance + $purchaseorder->grandttl) - floatval(str_replace(',', '', $input['order']['grandttl']));
                $budgetLine->save();
            }
        }
        /** If the milestone HAS CHANGED but grand total HAS NOT CHANGED */
        else if($milestoneChanged && !$grandTotalChanged){
            if (!$oldMilestoneZero) {
                $budgetLine->balance = $budgetLine->balance + $purchaseorder->grandttl;
                $budgetLine->save();
            }
            if (!$newMilestoneZero) {
                $newBudgetLine->balance -= $purchaseorder->grandttl;
                $newBudgetLine->save();
            }
        }

        foreach ($order as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'fx_curr_rate',
            ];    
            if (in_array($key, ['date', 'due_date'], 1)) 
                $order[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $order[$key] = numberClean($val);
        }
        $purchaseorder->update($order);

        $order_items = $input['order_items'];
        foreach ($order_items as $item) {
            if (@$item['type'] == 'Stock' && !$item['uom'])
            throw ValidationException::withMessages(['Unit of Measure (uom) required for Inventory Items']);
        }
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $order_items);
        $purchaseorder->products()->whereNotIn('id', $item_ids)->delete();
        
        // update or create new items
        foreach ($order_items as $item) {
            $item = array_replace($item, [
                'ins' => $order['ins'],
                'user_id' => $order['user_id'],
                'purchaseorder_id' => $purchaseorder->id,
                'product_code' => @$item['product_code'],
                'rate' => numberClean($item['rate']),
                'taxrate' => numberClean($item['taxrate']),
                'amount' => numberClean($item['amount']),
            ]);
            $order_item = PurchaseorderItem::firstOrNew(['id' => $item['id']]);
            $order_item->fill($item);
            if (!$order_item->id) unset($order_item->id);
            $order_item->save();                
        }

        if ($purchaseorder) {
            DB::commit();
            return true;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Purchaseorder $purchaseorder
     * @throws GeneralException
     * @return bool
     */
    public function delete($purchaseorder)
    {
        if ($purchaseorder->grn_items->count()) throw ValidationException::withMessages(['Purchase order has attached Goods Receive Note']);
            
        DB::beginTransaction();
        $purchaseorder->items()->delete();
        if ($purchaseorder->delete()) {
            DB::commit();
            return true;
        }
    }
}