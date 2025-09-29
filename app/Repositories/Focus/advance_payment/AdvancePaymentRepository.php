<?php

namespace App\Repositories\Focus\advance_payment;

use App\Exceptions\GeneralException;
use App\Models\advance_payment\AdvancePayment;
use App\Models\Company\Company;
use App\Models\items\UtilityBillItem;
use App\Models\send_sms\SendSms;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdvancePaymentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = AdvancePayment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        if (!access()->allow('manage-advance-payment'))
            $q->where('employee_id', auth()->user()->id);
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return AdvancePayment $advance_payment
     */
    public function create(array $input)
    {
        // dd($input);
        $input['amount'] = numberClean($input['amount']);
        $input['date'] = date_for_database($input['date']);
        
        $result = AdvancePayment::create($input);
        if ($result) return $result;
            
        throw new GeneralException(trans('exceptions.backend.advance_payment.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param AdvancePayment $advance_payment
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(AdvancePayment $advance_payment, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'approve_date'])) 
                $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'approve_amount'])) 
                $input[$key] = numberClean($val);
        }

        if (isset($input['status'])) {
            if ($input['approve_amount'] == 0) 
                throw ValidationException::withMessages(['Amount is required!']);
        } 

        $result = $advance_payment->update($input);

        if ($advance_payment->status == 'approved')
            $this->generate_bill($advance_payment);
        
        if ($result) {
            $this->send_sms($advance_payment);
            DB::commit();
            return $result;   
        }

        throw new GeneralException(trans('exceptions.backend.advance_payment.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param AdvancePayment $advance_payment
     * @throws GeneralException
     * @return bool
     */
    public function delete(AdvancePayment $advance_payment)
    {
        DB::beginTransaction();

        UtilityBill::where(['document_type' => 'advance_payment', 'ref_id' => $advance_payment->id])->delete();
        if ($advance_payment->delete()) {
            DB::commit();
            return true;
        }
            
        throw new GeneralException(trans('exceptions.backend.advance_payment.delete_error'));
    }

    /**
     * Generate Advance Payment Bill
     */
    public function generate_bill($payment)
    {
        $bill_data = [
            'employee_id' => $payment->employee_id,
            'document_type' => 'advance_payment',
            'ref_id' => $payment->id,
            'date' => $payment->date,
            'due_date' => $payment->date,
            'subtotal' => $payment->approve_amount,
            'total' => $payment->approve_amount,
            'note' => $payment->approve_note,
        ];

        $bill_items_data = [
            'ref_id' => $payment->id,
            'note' => $payment->approve_note,
            'qty' => 1,
            'subtotal' => $payment->approve_amount,
            'total' => $payment->approve_amount, 
        ];

        $bill = UtilityBill::where([
            'document_type' => $bill_data['document_type'], 
            'ref_id' => $bill_data['ref_id']
        ])->first();

        if ($bill) {
            // update bill
            $bill->update($bill_data);
            foreach ($bill_items_data as $item) {
                $new_item = UtilityBillItem::firstOrNew([
                    'bill_id' => $bill->id,
                    'ref_id' => $item['ref_id']
                ]);
                $new_item->save();
            }
        } else {
            // create bill
            $bill_data['tid'] = UtilityBill::where('ins', auth()->user()->ins)->max('tid') + 1;
            $bill = UtilityBill::create($bill_data);
            $bill_items_data['bill_id'] = $bill->id;
            UtilityBillItem::insert($bill_items_data);
        }
    }

    public function send_sms($advance_payment)
    {
        $employeeName = @$advance_payment->employee->fullname;
        $employeeEmail = @$advance_payment->employee->email;
        $employeePhone = @$advance_payment->employee->meta->primary_contact;
        $employeeId = $advance_payment->employee->id;
        
        // Find the company
        $company = Company::find(auth()->user()->ins);
        $companyName = "From " . Str::title($company->sms_email_name) . ":";
    
        // Initialize email and SMS data
        $emailInput = [
            'subject' => 'Advance Application Update',
            'mail_to' => $employeeEmail,
            'name' => $employeeName,
        ];
        
        $smsData = [
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'single',
            'phone_numbers' => $employeePhone,
            'sent_to_ids' => $employeeId,
        ];
    
        // Handle each status case
        if ($advance_payment->status == 'approved') {
            // For approval status
            $approved_amount = amountFormat($advance_payment->approve_amount);
            $emailInput['text'] = "Dear $employeeName, your advance of $approved_amount has been approved.";
            $smsText = $companyName . " Dear $employeeName, your advance of $approved_amount has been approved.";
        } elseif ($advance_payment['status'] == 'rejected') {
            // For rejection status
            $emailInput['text'] = "Dear $employeeName, your advance has been rejected. Contact HR for further information.";
            $smsText = $companyName . " Dear $employeeName, your advance has been rejected. Contact HR for further information.";
        } elseif ($advance_payment['status'] == 'review') {
            // For under review status
            $emailInput['text'] = "Dear $employeeName, your advance is currently under review. You will be informed of the final decision soon.";
            $smsText = $companyName . " Dear $employeeName, your advance is under review. We will update you soon.";
        }
    
        // Only proceed if the status is one of the above (approved, rejected, or under review)
        if (isset($smsText)) {
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
            (new RosemailerRepository(auth()->user()->ins))->send($emailInput['text'], $emailInput);
            (new RosesmsRepository(auth()->user()->ins))->textlocal($employeePhone, $smsText, $smsResult);
        }
    }
}
