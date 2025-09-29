<?php

namespace App\Repositories\Focus\sale_return;

use DB;
use App\Exceptions\GeneralException;
use App\Models\sale_return\SaleReturn;
use App\Models\sale_return\SaleReturnItem;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class SaleReturnRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = SaleReturn::class;
    
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
     * @return SaleReturn $sale_return
     */
    public function create(array $input)
    {  
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['return_qty', 'qty_onhand', 'new_qty', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // create sale return
        $data = Arr::only($input, ['tid', 'customer_id', 'date', 'invoice_id', 'note', 'total', 'reference', 'quote_id']);
        $sale_return = SaleReturn::create($data);

        $data_items = array_diff_key($input, $data);
        $data_items['sale_return_id'] = array_fill(0, count($data_items['return_qty']), $sale_return->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['warehouse_id'] && $v['return_qty'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['return qty field and location field are required!']);
        SaleReturnItem::insert($data_items);
        
        // update stock Qty
        $productvar_ids = array_map(fn($v) => $v['productvar_id'], $data_items);
        updateStockQty($productvar_ids);

        /** accounting */
        $this->post_sale_return($sale_return);

        if ($sale_return) {
            DB::commit();
            return $sale_return;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param SaleReturn $sale_return
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(SaleReturn $sale_return, array $input)
    {   
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['return_qty', 'qty_onhand', 'new_qty', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // update sale return
        $data = Arr::only($input, ['tid', 'customer_id', 'date', 'invoice_id', 'note', 'total', 'reference', 'quote_id']);
        $result = $sale_return->update($data);

        $data_items = array_diff_key($input, $data);
        $data_items['sale_return_id'] = array_fill(0, count($data_items['return_qty']), $sale_return->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['warehouse_id'] && $v['return_qty'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['return qty field and location field are required!']);
        $sale_return->items()->delete();
        SaleReturnItem::insert($data_items);
        
        // update stock Qty
        $productvar_ids = array_map(fn($v) => $v['productvar_id'], $data_items);
        updateStockQty($productvar_ids);

        /** accounting */
        $sale_return->transactions()->delete();
        $this->post_sale_return($sale_return);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param SaleReturn $sale_return
     * @throws GeneralException
     * @return bool
     */
    public function delete(SaleReturn $sale_return)
    { 
        DB::beginTransaction();
        $productvar_ids = $sale_return->items->pluck('productvar_id')->toArray();
        
        $sale_return->transactions()->delete();
        $sale_return->items()->delete();
        // update stock Qty
        updateStockQty($productvar_ids);

        if ($sale_return->delete()) {
            DB::commit();
            return true;
        }
    }
}
