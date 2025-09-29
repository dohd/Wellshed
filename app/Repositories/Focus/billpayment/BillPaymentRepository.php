<?php

namespace App\Repositories\Focus\billpayment;

use App\Exceptions\GeneralException;
use App\Models\billpayment\Billpayment;
use App\Models\items\BillpaymentItem;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use App\Repositories\CustomerSupplierBalance;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class BillPaymentRepository extends BaseRepository
{
    use Accounting, CustomerSupplierBalance;
    /**
     * Associated Repository Model.
     */
    const MODEL = Billpayment::class;

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
        });

        // date filters
        $q->when(request('start_date') && request('end_date'), function($q) {
            $q->whereBetween('date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        });

        $q->when(request('user_type'), function($q){
            if(request('user_type') == 'employee')
            {
                $q->where('petty_employee_id', request('employee_id'));
            }
            elseif (request('user_type') == 'casual') {
                # code...
                $q->where('casual_id', request('casual_id'));
            }
            elseif (request('user_type') == 'third_party_user') {
                # code...
                $q->where('third_party_user_id', request('third_party_user_id'));
            }
        });
        
        return $q;
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\billpayment\Billpayment $billpayment
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl'])) $input[$key] = numberClean($val);
            if (in_array($key, ['paid'])) 
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }

        $is_allocation = isset($input['rel_payment_id']);

        // payment line items
        $data_items = Arr::only($input, ['bill_id', 'paid']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['paid'] > 0);
        if (!$data_items && $input['payment_type'] == 'per_invoice') {
            throw ValidationException::withMessages(['Amount allocation on line items required!']);
        }

        // create payment
        $tid = Billpayment::max('tid');
        if ($input['tid'] <= $tid) $input['tid'] = $tid+1;
        $data = array_diff_key($input, array_flip(['balance', 'paid', 'bill_id']));
        $result = Billpayment::create($data);
        foreach ($data_items as $key => $val) {
            $data_items[$key]['bill_payment_id'] = $result->id;
            $data_items[$key]['paid'] = numberClean($val['paid']);
        }
        BillpaymentItem::insert($data_items);

        // update supplier on_account balance
        if ($result->supplier) {
            // non-allocation lumpsome payment 
            if (!$is_allocation && in_array($result->payment_type, ['on_account', 'advance_payment'])) {
                $result->supplier->increment('on_account', $result->amount);
            }
            // allocation payment
            if ($is_allocation && $result->payment_type == 'per_invoice') {
                $lumpsome_pmt = Billpayment::find($result->rel_payment_id);
                if ($lumpsome_pmt) {
                    if ($lumpsome_pmt->payment_type == 'advance_payment') $result->is_advance_allocation = true;
                    $result->supplier->decrement('on_account', $result->allocate_ttl);
                    $lumpsome_pmt->increment('allocate_ttl', $result->allocate_ttl);
                    // check over allocation
                    $diff = round($lumpsome_pmt->amount - $lumpsome_pmt->allocate_ttl);
                    if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
                }
            }
        }
        
        // compute bill balance
        $bill_ids = $result->items()->pluck('bill_id')->toArray();
        $this->supplier_payment_balance($bill_ids);

        /**accounting */
        if (!$is_allocation || $result->is_advance_allocation) {
            $this->post_bill_payment($result);
        }
                
        if ($result) {
            DB::commit();
            return $result;
        }

        DB::rollBack();
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\billpayment\Billpayment $billpayment
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\billpayment\Billpayment $billpayment
     */
    public function update(Billpayment $billpayment, array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl'])) $input[$key] = numberClean($val);
            if (in_array($key, ['paid'])) $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }

        if ($input['amount'] == 0) throw ValidationException::withMessages(['amount is required']);
        // check duplicate Reference No.
        $is_allocation = isset($input['rel_payment_id']);
        if (!$is_allocation && @$input['reference'] && @$input['account_id']) {
            $is_duplicate_ref = Billpayment::where('id', '!=', $billpayment->id)
            ->where('account_id', $input['account_id'])
            ->where('reference', 'LIKE', "%{$input['reference']}%")  
            ->whereNull('rel_payment_id')
            ->exists();            
            if ($is_duplicate_ref) throw ValidationException::withMessages(['Duplicate reference no.']);
        }
    
        // delete invoice_payment having unallocated line items
        $data_items = Arr::only($input, ['id', 'bill_id', 'paid']);
        if (!$data_items && $billpayment->payment_type == 'per_invoice') {
            return $this->delete($billpayment);
        }

        DB::beginTransaction();

        // update payment
        $data = array_diff_key($input, array_flip(['bill_id', 'paid']));
        $result = $billpayment->update($data);
        $data_items = modify_array($data_items);
        foreach ($billpayment->items as $pmt_item) {         
            $is_allocated = false;
            foreach ($data_items as $data_item) {
                if ($data_item['id'] == $pmt_item->id) {
                    $is_allocated = true;
                    $data_item['paid'] = numberClean($data_item['paid']);
                    $pmt_item->update(['paid' => $data_item['paid']]);
                }
            }
            if (!$is_allocated) $pmt_item->delete();
        }

        // compute lumpsome payment balance
        $lumpsome_pmt = Billpayment::find($billpayment->rel_payment_id);
        if ($lumpsome_pmt) {
            $lumpsome_allocated = Billpayment::where('rel_payment_id', $billpayment->rel_payment_id)
            ->where('payment_type', 'per_invoice')
            ->sum('allocate_ttl');
            $lumpsome_pmt->update(['allocate_ttl' => $lumpsome_allocated]);

            if ($lumpsome_pmt->payment_type == 'advance_payment') $billpayment->is_advance_allocation = true;
            // check over allocation
            $diff = round($lumpsome_pmt->amount - $lumpsome_pmt->allocate_ttl);
            if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
        }

        // compute balances
        $this->supplier_credit_balance($billpayment->supplier->id);
        $bill_ids = $billpayment->items()->pluck('bill_id')->toArray();
        $this->supplier_payment_balance($bill_ids);
        
        /** accounting */
        if (!$is_allocation || $billpayment->is_advance_allocation) {
            $billpayment->transactions()->delete();
            $this->post_bill_payment($billpayment);
        }

        if ($result) {
            DB::commit();
            return true;
        }

        DB::rollBack();
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\billpayment\Billpayment $billpayment
     * @throws GeneralException
     * @return bool
     */
    public function delete(Billpayment $billpayment)
    {     
        // check if lumpsome payment has allocations
        $payment_type = $billpayment->payment_type;
        if (in_array($payment_type, ['on_account', 'advance_payment'])) {
            $has_allocations = Billpayment::where('rel_payment_id', $billpayment->id)->exists();
            if ($has_allocations) throw ValidationException::withMessages(['Delete related payment allocations to proceed']);    
        }

        DB::beginTransaction();
        $billpayment_id = $billpayment->id;
        $allocation_id = $billpayment->rel_payment_id;
        $is_allocation = boolval($billpayment->rel_payment_id);
        $supplier = $billpayment->supplier;
        $bill_ids = $billpayment->items()->pluck('bill_id')->toArray();
    
        $billpayment->items()->delete();
        $result =  $billpayment->delete();
        
        // lumpsome payment balances
        $is_advance_allocation = false;
        if (in_array($payment_type, ['on_account', 'advance_payment'])) {
            $lumpsome_pmt = Billpayment::find($allocation_id);
            if ($lumpsome_pmt) {
                $lumpsome_allocated = Billpayment::where('rel_payment_id', $allocation_id)
                ->where('payment_type', 'per_invoice')
                ->sum('allocate_ttl');
                $lumpsome_pmt->update(['allocate_ttl' => $lumpsome_allocated]);
                if ($payment_type == 'advance_payment') $is_advance_allocation = true;
            } 
        }

        // compute balances
        $this->supplier_credit_balance($supplier->id);
        $this->supplier_payment_balance($bill_ids);
        
        /** accounting */
        if (!$is_allocation || $is_advance_allocation) {
            $billpayment->transactions()->delete();
        }

        if ($result) {
            DB::commit(); 
            return true;
        } 

        DB::rollBack();
    }
}