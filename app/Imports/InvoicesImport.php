<?php

namespace App\Imports;

use App\Models\account\Account;
use App\Models\bank\Bank;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use DB;
use Error;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InvoicesImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
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
        $columns = [
            'Invoice No','Date','Due Date','Note','VAT Amount','Net Amount','Taxable Amount', 'Total Amount','Currency','Currency Rate',
            'Withholding Tax Number','Withholding Amount'
        ];

        $row_count = 0;
        $label_count = count($columns);
        foreach ($rows as $i => $row) {
            $row = array_slice($row->toArray(), 0, $label_count);
            
            if ($i == 0) {
                $omitted_cols = array_diff($columns, $row);
                if ($omitted_cols) throw new Error('Column label mismatch: ' . implode(', ',$omitted_cols));
                continue;
            }

            $row_data = array_combine($columns, $row);
            // $customer = Customer::where('name', $row_data['Customer Name'])->orwhere('company', $row_data['Customer Name'])->first();
            $currency = Currency::where('code', $row_data['Currency'])->first();
            // $bank = Bank::where('bank', $row_data['Bank Account Ledger Name'])->first();
            // $account = Account::where('holder', $row_data['Income/Revenue Account'])->first();
            // $status = '';
            // if ($row_data['Paid Amount'] > 0 && $row_data['Paid Amount'] < $row_data['Total Amount']){
            //     $status = 'partial';
            // }else if($row_data['Paid Amount'] == $row_data['Total Amount']){
            //     $status = 'paid';
            // }
            $rate = $row_data['Currency Rate'] > 0 ? $row_data['Currency Rate'] : ($currency ? $currency->rate : 0);
            if(empty($row_data['Invoice No'])){
                continue;
            }
            $row_data = array_replace($row_data, [
                'customer_id' => $customer_id,
                // 'bank_id' => $bank ? $bank->id : 0,
                'account_id' => $account_id,
                'tid' => $row_data['Invoice No'],
                // 'cu_invoice_no' => $row_data['CU Invoice Number'],
                'invoicedate' => date_for_database($row_data['Date']),
                'invoiceduedate' => date_for_database($row_data['Due Date']),
                'notes' => $row_data['Note'],
                'tax' => $row_data['VAT Amount'],
                'subtotal' => $row_data['Net Amount'],
                'taxable' => $row_data['Taxable Amount'],
                'total' => $row_data['Total Amount'],
                'fx_curr_rate' => $rate,
                // 'payment_no' => $row_data['Payment Number'], //add
                'amountpaid' => 0,
                // 'status' => $status,
                'withholding_tax_no' => $row_data['Withholding Tax Number'], //add
                'withholding_amount' => $row_data['Withholding Amount'], //add
                'is_imported' => 1, //Add
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ]);
            $keys_to_unset = [
                 'Invoice No','Date','Due Date','Note','VAT Amount','Net Amount','Taxable Amount', 'Total Amount','Currency','Currency Rate',
                'Withholding Tax Number','Withholding Amount'
            ];
            
            // Unsetting the specified keys
            foreach ($keys_to_unset as $key) {
                unset($row_data[$key]);
            }
            foreach ($row_data as $key => $val) {
                if ($key == 'rate') $row_data[$key] = numberClean($row_data['rate']);
                if ($key == 'amountpaid') $row_data[$key] = numberClean($row_data['amountpaid']);
                if ($key == 'tax') $row_data[$key] = numberClean($row_data['tax']);
                if ($key == 'subtotal') $row_data[$key] = numberClean($row_data['subtotal']);
                if ($key == 'taxable') $row_data[$key] = numberClean($row_data['taxable']);
                if ($key == 'total') $row_data[$key] = numberClean($row_data['total']);
                if ($key == 'fx_curr_rate') $row_data[$key] = numberClean($row_data['fx_curr_rate']);
                if ($key == 'withholding_amount') $row_data[$key] = numberClean($row_data['withholding_amount']);
                if (strcasecmp($val, 'null') == 0) $row_data[$key] = null;
            }
           
            // dd($row_data);
            // $result = Invoice::create($row_data);
            try {
                DB::beginTransaction();
                // dd($row_data['tid']);
                if(Invoice::where('tid', $row_data['tid'])->exists()) throw ValidationException::withMessages(['Invoice' .$row_data['tid'].  'Exists!']);
                $result = new Invoice();
                $result->fill($row_data);
                $result->save();
                // dd($result);
                if($result){
                    DB::commit();
                }
            } catch (\Throwable $th) {
                // dd($row_data);
                if ($th instanceof ValidationException) throw $th;
                //throw $th;
                DB::rollback();
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
        return 1;
    }
}
