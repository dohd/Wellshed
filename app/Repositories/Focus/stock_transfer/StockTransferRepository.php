<?php

namespace App\Repositories\Focus\stock_transfer;

use App\Exceptions\GeneralException;
use App\Models\items\StockTransferItem;
use App\Models\stock_transfer\StockTransfer;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class StockTransferRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = StockTransfer::class;

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
     * @return StockTransfer $stock_transfer
     */
    public function create(array $input)
    {  
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['qty_transf', 'qty_rem', 'qty_onhand', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // create stock transfer
        $data = Arr::only($input, ['date', 'ref_no', 'source_id', 'dest_id', 'note', 'total', 'project_id', 'project_milestone', 'employee_id','lead_id']);
        $stock_transfer = StockTransfer::create($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_transfer_id'] = array_fill(0, count($data_items['qty_transf']), $stock_transfer->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['qty_transf'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Qty Transfer fields are required!']);
        StockTransferItem::insert($data_items);

        // update Stock Qty
        $productvar_ids = $stock_transfer->items->pluck('productvar_id')->toArray();
        updateStockQty($productvar_ids);
        
        if ($stock_transfer) {
            DB::commit();
            return $stock_transfer;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param StockTransfer $stock_transfer
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(StockTransfer $stock_transfer, array $input)
    {   
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['qty_transf', 'qty_rem', 'qty_onhand', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // update stock transfer
        $data = Arr::only($input, ['date', 'ref_no', 'source_id', 'dest_id', 'note', 'total', 'project_id', 'project_milestone', 'employee_id','lead_id']);
        $result =  $stock_transfer->update($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_transfer_id'] = array_fill(0, count($data_items['qty_transf']), $stock_transfer->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['qty_transf'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Qty Transfer fields are required!']);
        $stock_transfer->items()->delete();
        StockTransferItem::insert($data_items);

        // update Stock Qty
        $productvar_ids = $stock_transfer->items->pluck('productvar_id')->toArray();
        updateStockQty($productvar_ids);
        
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param StockTransfer $stock_transfer
     * @throws GeneralException
     * @return bool
     */
    public function delete(StockTransfer $stock_transfer)
    { 
        DB::beginTransaction();
        $productvar_ids = $stock_transfer->items->pluck('productvar_id')->toArray();

        $stock_transfer->items()->delete();
        // Update Stock Qty 
        updateStockQty($productvar_ids);

        if ($stock_transfer->delete()) {
            DB::commit();
            return true;
        }
    }
}
