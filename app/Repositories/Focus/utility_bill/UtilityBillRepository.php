<?php

namespace App\Repositories\Focus\utility_bill;

use App\Exceptions\GeneralException;
use App\Models\items\UtilityBillItem;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;


/**
 * Class UtilityBillRepository.
 */
class UtilityBillRepository extends BaseRepository
{
    use Accounting;

    /**
     * Associated Repository Model.
     */
    const MODEL = UtilityBill::class;

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
        if (request('start_duedate') && request('end_duedate')) {
            $q->whereBetween('due_date', [
                date_for_database(request('start_duedate')), 
                date_for_database(request('end_duedate'))
            ]);
        }

        // supplier and status filter
        $q->when(request('supplier_id'), function ($q) {
            $q->where('supplier_id', request('supplier_id'));
        })
        ->when(request('bill_type'), function ($q) {
            $q->where('document_type', request('bill_type'));      
        })
        ->when(request('bill_status'), function ($q) {
            // bill due status
            switch (request('bill_status')) {
                case 'not yet due': 
                    $q->where('due_date', '>', date('Y-m-d'));
                    break;
                case 'due':  
                    $q->where('due_date', '<=', date('Y-m-d'));
                    break; 
            }
        })
        ->when(request('payment_status'), function ($q) {
            // payment status
            switch (request('payment_status')) {
                case 'unpaid':
                    $q->where('amount_paid', 0);
                    break; 
                case 'partially paid':
                    $q->whereColumn('amount_paid', '<', 'total')->where('amount_paid', '>', 0);
                    break;
                case 'paid':
                    $q->whereColumn('amount_paid', '>=', 'total');
                    break;
            }         
        });
        return $q;
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\utility_bill\UtilityBill $utility_bill
     */
    public function create(array $input)
    {   
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'due_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total', 'fx_curr_rate'])) $input[$key] = numberClean($val);
            if (in_array($key, ['item_qty', 'item_subtotal', 'item_tax', 'item_total'])) $input[$key] = array_map(fn ($v) => numberClean($v), $val);
            // fx values
            if (in_array($key, ['fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_total'])) $input[$key] = floatval(str_replace(',', '', $val));
            if (in_array($key, ['rfx_rate', 'rfx_subtotal', 'rfx_taxable', 'rfx_tax', 'rfx_total'])) 
                $input[$key] = array_map(fn($v) => floatval(str_replace(',', '', $v)), $val);
        }

        DB::beginTransaction();

        // create bill
        $result = UtilityBill::create($input);
        
        // create bill items
        $data_items = Arr::only($input, [
            'item_ref_id', 'item_note', 'item_qty', 'item_subtotal', 'item_tax', 'item_total', 
            'rfx_rate', 'rfx_subtotal', 'rfx_taxable', 'rfx_tax', 'rfx_total'
        ]);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => floatval($v['item_qty']) && floatval($v['item_total']));
        if (!$data_items) throw ValidationException::withMessages(['Line quantities items required!']);
        $data_items = array_map(function ($v) use($result) {
            return [
                'bill_id' => $result->id,
                'ref_id' => $v['item_ref_id'],
                'note' => $v['item_note'],
                'qty' => $v['item_qty'],
                'subtotal' => $v['item_subtotal'],
                'tax' => $v['item_tax'],
                'total' => $v['item_total'],
                // fx values
                'fx_rate' => $v['rfx_rate'],
                'fx_subtotal' => $v['rfx_subtotal'],
                'fx_taxable' => $v['rfx_taxable'],
                'fx_tax' => $v['rfx_tax'],
                'fx_total' => $v['rfx_total'],
            ];
        }, $data_items);
        UtilityBillItem::insert($data_items);

        /**accounting */
        if ($result->document_type == 'goods_receive_note') {
            $this->post_grn_bill($result);
        } elseif ($result->document_type == 'contract') {
            $this->post_contract_expense($result);
        }
            
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\utility_bill\UtilityBill $utility_bill
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\utility_bill\UtilityBill $utility_bill
     */
    public function update(UtilityBill $utility_bill, array $input)
    {
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'due_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'])) $input[$key] = numberClean($val);
            if (in_array($key, ['item_qty', 'item_subtotal', 'item_tax', 'item_total']))
                $input[$key] = array_map(fn ($v) => numberClean($v), $val);
            // fx values
            if (in_array($key, ['fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_total'])) $input[$key] = floatval(str_replace(',', '', $val));
            if (in_array($key, ['rfx_rate', 'rfx_subtotal', 'rfx_taxable', 'rfx_tax', 'rfx_total']))
                $input[$key] = array_map(fn ($v) => floatval(str_replace(',', '', $v)), $val);
        }
        DB::beginTransaction();

        // update bill
        $result = $utility_bill->update($input);

        // update bill items
        $data_items = Arr::only($input, [
            'id', 'item_ref_id', 'item_note', 'item_qty', 'item_subtotal', 'item_tax', 'item_total',
            'rfx_rate', 'rfx_subtotal', 'rfx_taxable', 'rfx_tax', 'rfx_total'
        ]);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => floatval($v['item_qty']) && floatval($v['item_total']));
        if (!$data_items) throw ValidationException::withMessages(['Line items quantities required!']);
        $data_items = array_map(function ($v) {
            return [
                'id' => $v['id'],
                'ref_id' => $v['item_ref_id'],
                'note' => $v['item_note'],
                'qty' => $v['item_qty'],
                'subtotal' => $v['item_subtotal'],
                'tax' => $v['item_tax'],
                'total' => $v['item_total'],
                // fx values
                'fx_rate' => $v['rfx_rate'],
                'fx_subtotal' => $v['rfx_subtotal'],
                'fx_taxable' => $v['rfx_taxable'],
                'fx_tax' => $v['rfx_tax'],
                'fx_total' => $v['rfx_total'],
            ];
        }, $data_items);
        Batch::update(new UtilityBillItem, $data_items, 'id');

        /**accounting */
        $utility_bill->transactions()->delete();
        if ($utility_bill->document_type == 'goods_receive_note') {
            $this->post_grn_bill($utility_bill);
        }
            
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\utility_bill\UtilityBill $utility_bill
     * @throws GeneralException
     * @return bool
     */
    public function delete(UtilityBill $utility_bill)
    {     
        if ($utility_bill->payments()->exists()) {
            foreach ($utility_bill->payments as $key => $pmt_item) {
                $tids[] = @$pmt_item->bill_payment->tid ?: '';
            }
            throw ValidationException::withMessages(['Bill is linked to payments: (' . implode(', ', $tids) . ')']);
        }
        
        DB::beginTransaction();

        $utility_bill->transactions()->delete();
        $utility_bill->items()->delete();

        if ($utility_bill->delete()) {
            DB::commit(); 
            return true;
        }
    }   
}