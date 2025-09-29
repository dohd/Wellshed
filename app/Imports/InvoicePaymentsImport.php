<?php

namespace App\Imports;

use App\Models\account\Account;
use App\Models\bank\Bank;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\InvoicePaymentItem;
use DB;
use Error;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InvoicePaymentsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     *
     * @var int $row_count
     */
    private $row_count = 0;

    /**
     *
     * @var array $data
     */
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 
     * @param Illuminate\Support\Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {    
        if (empty($this->data['customer_id']))
        throw ValidationException::withMessages(['Customer is required!']);
        
        $account_id = $this->data['account_id'];
        $customer_id = $this->data['customer_id'];    
        $payment_type = $this->data['payment_type'];    
        $columns = [
            'Invoice Number','Amount','Reference','Date','Due Date','Note','Currency','Currency Rate','PaymentMode','Paid Amount'
        ];
        
        $row_count = 0;
        $label_count = count($columns);
        foreach ($rows as $i => $row) {
            $row = array_slice($row->toArray(), 0, $label_count);
    
            if ($i == 0) {
                if (count($row) !== $label_count) {
                    // dd($row, $columns); // Print the first row and columns for debugging
                    throw new Error('Column count mismatch. Expected ' . $label_count . ' columns, got ' . count($row));
                }
                $omitted_cols = array_diff($columns, $row);
                // dd($row,$i, $columns, $label_count, $omitted_cols);
                if ($omitted_cols) {
                    // Debug print to inspect the first row and columns
                    throw new Error('Column label mismatch: ' . implode(', ', $omitted_cols));
                }
                continue;
            }
        
            $row_data = array_combine($columns, $row);
        
            $currency = Currency::where('code', $row_data['Currency'])->first();
            $rate = $row_data['Currency Rate'] > 0 ? $row_data['Currency Rate'] : ($currency ? $currency->rate : 0);
            $payment = InvoicePayment::where(['reference'=> $row_data['Reference'], 'customer_id'=>$customer_id,'payment_type'=>$payment_type])->first();
            // $invoice = Invoice::where('tid', $row_data['Invoice Number'])->where('is_imported', 1)->first();

            try {
                DB::beginTransaction();
                if($payment){
                    if($payment_type == 'per_invoice'){
                        if (empty($row_data['Invoice Number']))
                        throw ValidationException::withMessages(['Invoice Number is required!']);
                        $invoice = Invoice::where('tid', $row_data['Invoice Number'])->where('is_imported', 1)->first();
                        $row_data = array_replace($row_data, [
                            'customer_id' => $customer_id,
                            'account_id' => $account_id,
                            'date' => date_for_database($row_data['Date']),
                            'note' => $row_data['Note'],
                            'reference' => $row_data['Reference'],
                            'amount' => numberClean($row_data['Amount']),
                            'allocate_ttl' => numberClean($row_data['Amount']),
                            'fx_curr_rate' => numberClean($rate),
                            'payment_type' => $payment_type,
                            'payment_mode' => strtolower($row_data['PaymentMode']),
                            'ins' => auth()->user()->ins,
                            'user_id' => auth()->user()->id
                        ]);
                        if (@$row_data['fx_curr_rate'] > 1) {
                            $row_data['fx_amount'] = round($row_data['amount'] * $row_data['fx_curr_rate'],4);
                            $row_data['fx_allocate_ttl'] = round($row_data['allocate_ttl'] * $row_data['fx_curr_rate'],4);
                        }
                        $paid_amount = numberClean($row_data['Paid Amount']);
                        foreach ($columns as $key) {
                            unset($row_data[$key]);
                        }
                        // $result = InvoicePayment::create($row_data);
                        $data = [
                            'invoice_id' => $invoice ? $invoice->id : 0,
                            'paidinvoice_id' => $payment ? $payment->id : 0,
                            'paid' => $paid_amount,
                        ];
                        $invoice->amountpaid += $paid_amount;
                        $status = '';
                        if ($invoice->amountpaid > 0 && $invoice->amountpaid < $invoice->total){
                            $status = 'partial';
                        }else if($invoice->amountpaid == $invoice->total){
                            $status = 'paid';
                        }
                        $invoice->status = $status;
                        $invoice->update();
                        if (@$payment->fx_curr_rate > 1) {
                            $data['fx_rate'] = $payment['fx_curr_rate'];
                            $data['fx_paid'] = round($data['paid'] * $payment['fx_curr_rate'], 4);
                            $invoice_fx_total = 0;
                            // $invoice = Invoice::find($data['invoice_id']);
                            if ($invoice) $invoice_fx_total = round($data['paid'] * $invoice->fx_curr_rate, 4);
                            $fx_diff = $invoice_fx_total - $data['fx_paid'];
                            if ($fx_diff > 0) $data['fx_loss'] = $fx_diff;
                            else $data['fx_gain'] = -$fx_diff;
                        }
                        InvoicePaymentItem::create($data);
                    }
    
                }else{
                    if($payment_type == 'per_invoice'){
                        if (empty($row_data['Invoice Number']))
                        throw ValidationException::withMessages(['Invoice Number is required!']);
                        $invoice = Invoice::where('tid', $row_data['Invoice Number'])->where('is_imported', 1)->first();
                        $tid = InvoicePayment::max('tid')+1;
                        $row_data = array_replace($row_data, [
                            'tid' => $tid,
                            'customer_id' => $customer_id,
                            'account_id' => $account_id,
                            'date' => date_for_database($row_data['Date']),
                            'note' => $row_data['Note'],
                            'reference' => $row_data['Reference'],
                            'amount' => numberClean($row_data['Amount']),
                            'allocate_ttl' => numberClean($row_data['Amount']),
                            'fx_curr_rate' => numberClean($rate),
                            'payment_type' => $payment_type,
                            'payment_mode' => strtolower($row_data['PaymentMode']),
                            'ins' => auth()->user()->ins,
                            'user_id' => auth()->user()->id
                        ]);
                        if (@$row_data['fx_curr_rate'] > 1) {
                            $row_data['fx_amount'] = round($row_data['amount'] * $row_data['fx_curr_rate'],4);
                            $row_data['fx_allocate_ttl'] = round($row_data['allocate_ttl'] * $row_data['fx_curr_rate'],4);
                        }
                        $paid_amount = numberClean($row_data['Paid Amount']);
                        foreach ($columns as $key) {
                            unset($row_data[$key]);
                        }
                        $result = InvoicePayment::create($row_data);
                        $data = [
                            'invoice_id' => $invoice ? $invoice->id : 0,
                            'paidinvoice_id' => $result ? $result->id : 0,
                            'paid' => $paid_amount,
                        ];
                        $invoice->amountpaid += $paid_amount;
                        $status = '';
                        if ($invoice->amountpaid > 0 && $invoice->amountpaid < $invoice->total){
                            $status = 'partial';
                        }else if($invoice->amountpaid == $invoice->total){
                            $status = 'paid';
                        }
                        $invoice->status = $status;
                        $invoice->update();
                        if (@$result->fx_curr_rate > 1) {
                            $data['fx_rate'] = $result['fx_curr_rate'];
                            $data['fx_paid'] = round($data['paid'] * $result['fx_curr_rate'], 4);
                            $invoice_fx_total = 0;
                            // $invoice = Invoice::find($data['invoice_id']);
                            if ($invoice) $invoice_fx_total = round($data['paid'] * $invoice->fx_curr_rate, 4);
                            $fx_diff = $invoice_fx_total - $data['fx_paid'];
                            if ($fx_diff > 0) $data['fx_loss'] = $fx_diff;
                            else $data['fx_gain'] = -$fx_diff;
                        }
                        InvoicePaymentItem::create($data);
                    }else{
                        $tid = InvoicePayment::max('tid')+1;
                        $row_data = array_replace($row_data, [
                            'tid' => $tid,
                            'customer_id' => $customer_id,
                            'account_id' => $account_id,
                            'date' => date_for_database($row_data['Date']),
                            'note' => $row_data['Note'],
                            'reference' => $row_data['Reference'],
                            'amount' => numberClean($row_data['Amount']),
                            'allocate_ttl' => 0,
                            'fx_curr_rate' => numberClean($rate),
                            'payment_type' => $payment_type,
                            'payment_mode' => strtolower($row_data['PaymentMode']),
                            'ins' => auth()->user()->ins,
                            'user_id' => auth()->user()->id
                        ]);
                        if (@$row_data['fx_curr_rate'] > 1) {
                            $row_data['fx_amount'] = round($row_data['amount'] * $row_data['fx_curr_rate'],4);
                            $row_data['fx_allocate_ttl'] = round($row_data['allocate_ttl'] * $row_data['fx_curr_rate'],4);
                        }
                        foreach ($columns as $key) {
                            unset($row_data[$key]);
                        }
                        $result = InvoicePayment::create($row_data);
                    }
                }
    
                if($result->customer){
                    if (in_array($result->payment_type, ['on_account', 'advance_payment'])) {
                        $result->customer->increment('on_account', $result->amount);
                    }
                }
                DB::commit();
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                return errorHandler($th);
            }
        
            if ($result) $row_count++;
        }
        
        if (!$row_count) throw new Error('Please fill template with required data');
        $this->row_count = $row_count;
        
    }

    public function rules(): array
    {
        return [
            // '0' => 'required|string',
            // '1' => 'required',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function getRowCount(): int
    {
        return $this->row_count;
    }

    public function startRow(): int
    {
        return 2;
    }
}
