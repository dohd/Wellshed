<?php

namespace App\Repositories\Focus\invoice_payment;

use App\Exceptions\GeneralException;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\Company\Company;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\InvoicePaymentItem;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\tenant\Tenant;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use App\Repositories\CustomerSupplierBalance;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use DateInterval;
use DateTime;
use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoicePaymentRepository extends BaseRepository
{
    use Accounting, CustomerSupplierBalance;

    /**
     * Associated Repository Model.
     */
    const MODEL = InvoicePayment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        // date filter
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // 
        $q->when(request('customer_id'), fn ($q) => $q->where('customer_id', request('customer_id')));
        $q->when(request('project_id'), fn ($q) => $q->where('project_id', request('project_id')));
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return InvoicePayment $payment
     */
    public function create(array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        // dd($data);
        foreach ($data as $key => $val) {
            if ($key == 'date') $data[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl', 'fx_curr_rate', 'wh_tax_amount', 'wh_vat_amount'])) 
                $data[$key] = floatval(str_replace(',','',$val));
        }

        if ($data['amount'] == 0) throw ValidationException::withMessages(['amount is required']);
        // check duplicate Reference No.
        $is_allocation = isset($data['rel_payment_id']);
        if (!$is_allocation && @$data['reference'] && @$data['account_id']) {
            $is_duplicate_ref = InvoicePayment::where('account_id', $data['account_id'])
            ->where('reference', 'LIKE', "%{$data['reference']}%")  
            ->whereNull('rel_payment_id')
            ->exists();            
            if ($is_duplicate_ref) throw ValidationException::withMessages(['Duplicate reference no.']);
        }

        // payment line items
        $data_items = $input['data_items'];
        $data_items = array_filter($data_items, fn($v) => $v['paid'] > 0);
        if (!$data_items && @$data['payment_type'] == 'per_invoice') {
            throw ValidationException::withMessages(['amount allocation on line items required!']);
        }

        // create payment
        $tid = InvoicePayment::max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1;

        if (@$data['fx_curr_rate'] > 1) {
            $data['fx_amount'] = round($data['amount'] * $data['fx_curr_rate'],4);
            $data['fx_allocate_ttl'] = round($data['allocate_ttl'] * $data['fx_curr_rate'],4);
        }
        $result = InvoicePayment::create($data);
        
        foreach ($data_items as $key => $item) {
            $item = array_replace($item, [
                'paidinvoice_id' => $result->id,
                'wh_vat' => numberClean($item['wh_vat']),
                'wh_tax' => numberClean($item['wh_tax']),
                'paid' => numberClean($item['paid']),
                'fx_rate' => $result->fx_curr_rate,
                'fx_paid' => round(numberClean($item['paid']) * $result->fx_curr_rate, 4),
            ]);

            $invoice_fx_total = 0;
            $invoice = Invoice::find($item['invoice_id']);
            if ($result->fx_curr_rate > 1) {
                if ($invoice) $invoice_fx_total = round($item['paid'] * $invoice->fx_curr_rate, 4);
                $fx_diff = $invoice_fx_total - $item['fx_paid'];
                if ($fx_diff > 0) $item['fx_loss'] = $fx_diff;
                else $item['fx_gain'] = -$fx_diff;
            }
            $data_items[$key] = $item;
        }
        InvoicePaymentItem::insert($data_items);

        // update customer on_account balance
        if ($result->customer) {
            // non-allocation lumpsome payment 
            if (!$is_allocation && in_array($result->payment_type, ['on_account', 'advance_payment'])) {
                $result->customer->increment('on_account', $result->amount);
            }
            // allocation payment
            if ($is_allocation && $result->payment_type == 'per_invoice') {
                $lumpsome_pmt = InvoicePayment::find($result->rel_payment_id);
                if ($lumpsome_pmt) {
                    if ($lumpsome_pmt->payment_type == 'advance_payment') $result->is_advance_allocation = true;
                    $result->customer->decrement('on_account', $result->allocate_ttl);
                    $lumpsome_pmt->increment('allocate_ttl', $result->allocate_ttl);
                    // check over allocation
                    $diff = round($lumpsome_pmt->amount - $lumpsome_pmt->allocate_ttl);
                    if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
                } 
            }
        }

        // update credit balance
        $this->updateCustomerCredit($result->customer_id);
        // update invoice balances
        $invoice_ids = $result->items()->pluck('invoice_id')->toArray();
        $this->updateInvoiceBalance($invoice_ids);
        
        /**accounting */
        if (!$is_allocation || $result->is_advance_allocation) {
            $this->post_invoice_deposit($result);
        }
        if ($result->payment_type == 'per_invoice') {
            if ($result->wh_vat_amount > 0) $this->journalEntry($result, 'vat');
            if ($result->wh_tax_amount > 0) $this->journalEntry($result, 'tax');
        }
                
        if ($result) {
            // tenant reactivation on payment
            if (optional(auth()->user()->business)->is_main) {
                $this->updateTenantSubscription($data['customer_id'], $data['amount']);
            }

            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param InvoicePayment $invoice_payment
     * @param array $input
     * @throws GeneralException
     * return bool
     */
    public function update($invoice_payment, array $input)
    {

        $oldInvoicePayment = InvoicePayment::find($invoice_payment->id)->toArray();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if ($key == 'date') $data[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl', 'fx_curr_rate', 'wh_vat_amount', 'wh_tax_amount'])) 
                $data[$key] = floatval(str_replace(',','',$val));
        }

        if ($data['amount'] == 0) throw ValidationException::withMessages(['amount is required']);
        // check duplicate Reference No.
        $is_allocation = isset($data['rel_payment_id']);
        if (!$is_allocation && @$data['reference'] && @$data['account_id']) {
            $is_duplicate_ref = InvoicePayment::where('id', '!=', $invoice_payment->id)
            ->where('account_id', $data['account_id'])
            ->where('reference', 'LIKE', "%{$data['reference']}%")  
            ->whereNull('rel_payment_id')
            ->exists();            
            if ($is_duplicate_ref) throw ValidationException::withMessages(['Duplicate reference no.']);
        }
            
        // delete invoice_payment having unallocated line items
        $data_items = $input['data_items'];
        if (!$data_items && $invoice_payment->payment_type == 'per_invoice') {
            return $this->delete($invoice_payment);
        }
            
        DB::beginTransaction(); 


        if (@$data['fx_curr_rate'] > 1) {
            $data['fx_amount'] = round($data['amount'] * $data['fx_curr_rate'],4);
            $data['fx_allocate_ttl'] = round($data['allocate_ttl'] * $data['fx_curr_rate'],4);
        }
        $result = $invoice_payment->update($data);

        foreach ($invoice_payment->items as $pmt_item) {
            $is_allocated = false;
            foreach ($data_items as $item) {
                if ($item['id'] == $pmt_item->id) {
                    $is_allocated = true;
                    $item['paid'] = numberClean($item['paid']);
                    if (@$data['fx_curr_rate'] > 1) {
                        $item['fx_rate'] = $data['fx_curr_rate'];
                        $item['fx_paid'] = round($item['paid'] * $data['fx_curr_rate'], 4);
                        $invoice = Invoice::find($item['invoice_id']);
                        if ($invoice) $invoice_fx_total = round($item['paid'] * $invoice->fx_curr_rate, 4);
                        $fx_diff = $invoice_fx_total - $item['fx_paid'];
                        if ($fx_diff > 0) $item['fx_loss'] = $fx_diff;
                        else $item['fx_gain'] = -$fx_diff;
                    } 
                    $pmt_item->update([
                        'wh_vat' => numberClean($item['wh_vat']),
                        'wh_tax' => numberClean($item['wh_tax']),
                        'paid' => $item['paid'],
                        'fx_rate' => @$data['fx_curr_rate'], 
                        'fx_paid' => @$item['fx_paid'],
                        'fx_loss' => @$item['fx_loss'],
                        'fx_gain' => @$item['fx_gain'],
                    ]);
                }
            }
            if (!$is_allocated) $pmt_item->delete();
        }

        // compute lumpsome payment balance
        $lumpsome_pmt = InvoicePayment::find($invoice_payment->rel_payment_id);
        if ($lumpsome_pmt) {
            $lumpsome_allocated = InvoicePayment::where('rel_payment_id', $invoice_payment->rel_payment_id)
            ->where('payment_type', 'per_invoice')
            ->sum('allocate_ttl');
            $lumpsome_pmt->update(['allocate_ttl' => $lumpsome_allocated]);

            if ($lumpsome_pmt->payment_type == 'advance_payment') $invoice_payment->is_advance_allocation = true;
            // check over allocation
            $diff = round($lumpsome_pmt->amount - $lumpsome_pmt->allocate_ttl);
            if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
        }

        // update credit balance
        $this->updateCustomerCredit($invoice_payment->customer->id);
        // update invoice balances
        $invoice_ids = $invoice_payment->items()->pluck('invoice_id')->toArray();
        $this->updateInvoiceBalance($invoice_ids);
        
        /** accounting */
        if (!$is_allocation || $invoice_payment->is_advance_allocation) {
            $invoice_payment->transactions()->delete();
            $this->post_invoice_deposit($invoice_payment);
        }
        if ($invoice_payment->payment_type == 'per_invoice') {
            if ($invoice_payment->wh_vat_amount > 0) $this->journalEntry($invoice_payment, 'vat');
            if ($invoice_payment->wh_tax_amount > 0) $this->journalEntry($invoice_payment, 'tax');
        }

        if ($result) {
            // tenant reactivation on payment
            if (optional(auth()->user()->business)->is_main) {
                $this->updateTenantSubscription($data['customer_id'], $data['amount'], false, $oldInvoicePayment);                
            }

            DB::commit();
            return true;
        }

        DB::rollBack();
    }

    /**
     * For deleting the respective model from storage
     *
     * @param InvoicePayment $payment
     * @throws GeneralException
     * @return bool
     */
    public function delete(InvoicePayment $invoice_payment)
    {
        // check if lumpsome payment has allocations
        $payment_type = $invoice_payment->payment_type;
        if (in_array($payment_type, ['on_account', 'advance_payment'])) {
            $has_allocations = InvoicePayment::where('rel_payment_id', $invoice_payment->id)->exists();
            if ($has_allocations) throw ValidationException::withMessages(['Delete related payment allocations to proceed']);    
        }

        DB::beginTransaction();

        $allocation_id = $invoice_payment->rel_payment_id;
        $is_allocation = boolval($invoice_payment->rel_payment_id);
        $invoice_ids = $invoice_payment->items->pluck('invoice_id')->toArray();
    
        $invoice_payment->items()->delete();
        $result =  $invoice_payment->delete();
        
        // lumpsome payment balances
        $is_advance_allocation = false;
        if (in_array($payment_type, ['on_account', 'advance_payment'])) {
            $lumpsome_pmt = InvoicePayment::find($allocation_id);
            if ($lumpsome_pmt) {
                $lumpsome_allocated = InvoicePayment::where('rel_payment_id', $allocation_id)
                ->where('payment_type', 'per_invoice')
                ->sum('allocate_ttl');
                $lumpsome_pmt->update(['allocate_ttl' => $lumpsome_allocated]);
                if ($payment_type == 'advance_payment') $is_advance_allocation = true;
            } 
        }

        // update customer credit balance
        if ($invoice_payment->customer) {
            $this->updateCustomerCredit($invoice_payment->customer_id);
        }
        // update invoice balances
        $this->updateInvoiceBalance($invoice_ids);
        
        /** accounting */
        if (!$is_allocation || $is_advance_allocation) {
            $invoice_payment->transactions()->delete();
        }
        $journal = $invoice_payment->journal; 
        if ($payment_type == 'per_invoice' && $journal) {
            $journal->items()->delete();
            $journal->transactions()->delete();
            $journal->delete();
        }

        if ($result) {
            DB::commit(); 
            return true;
        }      
    }

    /**
     * Journal Entry Transaction
     */
    public function journalEntry($receipt, $type)
    {        
        $wh_account = null;
        if ($receipt->wh_tax_amount > 0) {
            $wh_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'withholding_tax_payable'))->first(['id']);
            if (!$wh_account) $this->accountingError('Withholding TAX Payable Account required!');
        } else {
            $wh_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'withholding_vat_payable'))->first(['id']);
            if (!$wh_account) $this->accountingError('Withholding VAT Payable Account required!');
        }
        $data = [
            'paid_invoice_id' => $receipt->id,
            'wh_type' => $type,
            'date' => $receipt->date,
            'note' => $receipt->note,
            'debit_ttl' => $type == 'vat'? $receipt->wh_vat_amount : ($type == 'tax'? $receipt->wh_tax_amount: 0),
            'credit_ttl' => $type == 'vat'? $receipt->wh_vat_amount : ($type == 'tax'? $receipt->wh_tax_amount: 0),
        ];
        $data_items = [
            ['account_id' => $wh_account->id, 'debit' => $data['debit_ttl'], 'credit' => 0, 'customer_id' => $receipt->customer_id],
            ['account_id' => $receipt->customer->ar_account_id, 'debit' => 0, 'credit' => $data['credit_ttl'], 'customer_id' => $receipt->customer_id],
        ];
        
        $journal = Journal::where('paid_invoice_id', $receipt->id)->where('wh_type', $type)->first();
        if ($journal) {
            // update journal entry
            $journal->update($data);
            $journal->items()->delete();
            foreach ($data_items as $key => $item) {
                $data_items[$key]['journal_id'] = $journal->id;
            }
            JournalItem::insert($data_items);
            $journal->transactions()->delete();
            $this->post_journal_entry($journal);
        } else {
            // create journal entry
            $journal = Journal::create($data);
            foreach ($data_items as $key => $item) {
                $data_items[$key]['journal_id'] = $journal->id;
            }
            JournalItem::insert($data_items);
            $this->post_journal_entry($journal);
        }
        return $journal;
    }


    public function calculateSubscriptionValue($customer_id=null, $amount=0)
    {
        try {
            $amount = (float)$amount;
            $nextPayDate = null;
            $customer = Customer::find($customer_id);
            $adminUser = User::withoutGlobalScopes()->where('tenant_customer_id', $customer_id)->first();
            $tenant = Tenant::find($adminUser->ins);
            $tenantPackage = optional(optional(optional($tenant)->package)->service)->package;
            $subscriptionBalance = (float)$tenant->subscription_balance;

            if (!empty($tenantPackage)) {
                $amount = floatval($amount);
                $packagePrice = floatval($tenantPackage->first()->price);
                $monthsPaid = (integer)bcdiv($amount, $packagePrice);
                $partialMonthsPaid = (float)bcsub(bcdiv($amount, $packagePrice, 5), bcdiv($amount, $packagePrice, 0), 5);
                $subscriptionBalance = bcsub($subscriptionBalance, $amount, 2);
                $secondsIn30Days = 30 * 24 * 60 * 60; // 30 days in seconds
                $adjustedSeconds = bcmul($secondsIn30Days, $partialMonthsPaid, 0);
                // Convert seconds to a DateInterval
                $monthsInterval = new DateInterval('P' . $monthsPaid . 'M');
                $overpaidInterval = new DateInterval('PT' . $adjustedSeconds . 'S');
                // Add the interval to the base date
                $totalMonthsPaid = $monthsPaid + $partialMonthsPaid;
                $loyaltyPoints = bcmul($totalMonthsPaid, 2, 2);
                return compact('totalMonthsPaid', 'loyaltyPoints', 'monthsInterval', 'overpaidInterval');
            }
        } catch (\Exception $e) {
            $errorMessage = json_encode([
                'type' => "Subscription Payment Error!!!",
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }

        return false;
    }

    /**
     * Compute Next Payment Date
     */
    public function updateTenantSubscription($customer_id=null, $amount=0, $creating = true, $oldInvoice = null)
    {
        try{
            $adminUser = User::withoutGlobalScopes()->where('tenant_customer_id', $customer_id)->first();
            if (!$adminUser) throw new Exception('Super-user account could not be found');
            $tenant = Tenant::findOrFail($adminUser->ins);
            $amount = (float) $amount;

            if ($creating) {
                $subscriptionValue = $this->calculateSubscriptionValue($customer_id, $amount);
                $baseDate = new DateTime($tenant->billing_date);
                $nextBillingDate = ((clone $baseDate)->add($subscriptionValue['monthsInterval'])->add($subscriptionValue['overpaidInterval']))->format('Y-m-d H:i:s');
                $tenant->subscription_balance = $tenant->subscription_balance - $amount;
                $tenant->billing_date = $nextBillingDate;
                $tenant->loyalty_points = $subscriptionValue['loyaltyPoints'];
                $tenant->save();
            } else if (!$creating && $oldInvoice) {
                $oldSubscriptionValue = $this->calculateSubscriptionValue($customer_id, $oldInvoice['amount']);
                $oldSubscriptionBalance = $tenant->subscription_balance + (float) $oldInvoice['amount'];
                $baseDate = new DateTime($tenant->billing_date);
                $oldBillingDate = ((clone $baseDate)->sub($oldSubscriptionValue['monthsInterval'])->sub($oldSubscriptionValue['overpaidInterval']))->format('Y-m-d H:i:s');
                $oldLoyaltyPoints = $tenant->loyalty_points - $oldSubscriptionValue['loyaltyPoints'];
                // return compact('oldBillingDate', 'oldLoyaltyPoints', 'oldSubscriptionBalance');
                $tenant->subscription_balance = $oldSubscriptionBalance;
                $tenant->billing_date = $oldBillingDate;
                $tenant->loyalty_points = $oldLoyaltyPoints;
                $newSubscriptionValue = $this->calculateSubscriptionValue($customer_id, $amount);
                $baseDate = new DateTime($tenant->billing_date);
                $nextBillingDate = ((clone $baseDate)->add($newSubscriptionValue['monthsInterval'])->add($newSubscriptionValue['overpaidInterval']))->format('Y-m-d H:i:s');
                $tenant->subscription_balance = $tenant->subscription_balance - $amount;
                $tenant->billing_date = $nextBillingDate;
                $tenant->loyalty_points = $newSubscriptionValue['loyaltyPoints'];
                $tenant->save();
            }
            
            // return compact('amount', 'packagePrice', 'monthsPaid', 'partialMonthsPaid', 'subscriptionBalance', 'currentBillingDate', 'nextBillingDate');
        } catch (\Exception $e) {
            $errorMessage = json_encode([
                'type' => "Subscription Payment Error!!!",
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }
        
        return true;
    }

    public function send_payment_link($invoice_payment, $input){
        if ($invoice_payment) {
            $customerName = @$invoice_payment->customer->company;
            $customerEmail = $input['email'];
            $customerPhone = $input['phone_number'];
            $customer_id = $invoice_payment->customer_id;
            
            // Find the company
            $company = Company::find(auth()->user()->ins);
            $companyName = "From " . Str::title($company->sms_email_name) . ":";
        
            // Initialize email and SMS data
            $emailInput = [
                'subject' => 'Invoice Payment Received',
                'mail_to' => $customerEmail,
                'name' => $customerName,
            ];
            
            $smsData = [
                'user_type' => 'customer',
                'delivery_type' => 'now',
                'message_type' => 'single',
                'phone_numbers' => $customerPhone,
                'sent_to_ids' => $customer_id,
            ];
            $secureToken = hash('sha256', $invoice_payment->id . env('APP_KEY'));
            $link = route('payment_received', [
                'invoice_payment_id' => $invoice_payment->id,
                'token' => $secureToken
            ]);
        
            // Handle each status case
            if ($invoice_payment) {
                // For 
                $emailInput['text'] = "Dear $customerName, your payment has been received, your receipt is as follows : {$link}";
                $smsText = $companyName . " Dear $customerName, your payment has been received, your receipt is as follows : {$link}";
            } 
        
            // Only proceed 
            if (isset($smsText)) {
                if($input['send_email_sms'] == 'both' || $input['send_email_sms'] == 'sms'){
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
                    (new RosesmsRepository(auth()->user()->ins))->textlocal($customerPhone, $smsText, $smsResult);
                }
                
                if($input['send_email_sms'] == 'both' || $input['send_email_sms'] == 'email')
                {
                    $email = (new RosemailerRepository(auth()->user()->ins))->send($emailInput['text'], $emailInput);
                    $email_output = json_decode($email);
                    if ($email_output->status === "Success"){
    
                        $email_data = [
                            'text_email' => $emailInput['text'],
                            'subject' => $emailInput['subject'],
                            'user_emails' => $emailInput['mail_to'],
                            'user_ids' => $customer_id,
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

    public function send_sms_and_email(array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        $data['invoice_payment_id'] = $input['invoice_payment_id'];
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data['created_at'] = now();
        $data['updated_at'] = now();
        DB::table('send_invoices')->insert($data);
        $invoice_payment = InvoicePayment::find($input['invoice_payment_id']);

        if ($invoice_payment) {
            $this->send_payment_link($invoice_payment, $data);
            DB::commit(); 
            return true;
        }  
        // dd($data);
    }
}
