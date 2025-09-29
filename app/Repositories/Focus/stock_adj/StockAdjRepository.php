<?php

namespace App\Repositories\Focus\stock_adj;

use DB;
use App\Exceptions\GeneralException;
use App\Models\stock_adj\StockAdj;
use App\Models\stock_adj\StockAdjItem;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class StockAdjRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = StockAdj::class;
    
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
     * @return StockAdj $stock_adj
     */
    public function create(array $input)
    {  
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['new_qty', 'qty_diff', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // create stock adj
        $data = Arr::only($input, ['date', 'adj_type', 'account_id', 'note', 'total']);
        $stock_adj = StockAdj::create($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_adj_id'] = array_fill(0, count($data_items['new_qty']), $stock_adj->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function($v) use($stock_adj) {
            if ($stock_adj->adj_type == 'Qty') return $v['qty_diff'] != 0;
            if ($stock_adj->adj_type == 'Qty-Cost') return ($v['qty_diff'] != 0 && $v['cost'] != 0);
            return false;
        });
        if (!$data_items) throw ValidationException::withMessages(['Qty or Cost fields are required!']);
        StockAdjItem::insert($data_items);
        $adj_total = $stock_adj->items->sum('amount');
        if (round($adj_total) != round($stock_adj->total))
            $stock_adj->update(['total' => $adj_total]);
        
        // stock qty is updated on approval

        if ($stock_adj) {
            DB::commit();
            return $stock_adj;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param StockAdj $stock_adj
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(StockAdj $stock_adj, array $input)
    {
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['new_qty', 'qty_diff', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // update stock adj
        $data = Arr::only($input, ['date', 'adj_type', 'account_id', 'note', 'total']);
        $result = $stock_adj->update($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_adj_id'] = array_fill(0, count($data_items['new_qty']), $stock_adj->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function($v) use($stock_adj) {
            if ($stock_adj->adj_type == 'Qty') return $v['qty_diff'] != 0;
            if ($stock_adj->adj_type == 'Qty-Cost') return ($v['qty_diff'] != 0 && $v['cost'] != 0);
            return false;
        });
        if (!$data_items) throw ValidationException::withMessages(['Qty or Cost fields are required!']);
        $stock_adj->items()->delete();
        StockAdjItem::insert($data_items);
        $adj_total = $stock_adj->items->sum('amount');
        if (round($adj_total) != round($stock_adj->total)) {
            $stock_adj->update(['total' => $adj_total]);
        }

        if ($stock_adj->approval_status == 'Approved') {
            $productvarIds = $stock_adj->items->pluck('productvar_id')->toArray();
            updateStockQty($productvarIds);

            /** accounting */
            $stock_adj->transactions()->delete();
            $this->post_stock_adjustment($stock_adj);
        }

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param StockAdj $stock_adj
     * @throws GeneralException
     * @return bool
     */
    public function delete(StockAdj $stock_adj)
    { 
        DB::beginTransaction();

        $productvarIds = $stock_adj->items->pluck('productvar_id')->toArray();
        $stock_adj->items()->delete();
        $stock_adj->transactions()->delete();
        // update stock Qty
        updateStockQty($productvarIds);

        if ($stock_adj->delete()) {
            DB::commit();
            return true;
        }
    }

    /**
     * Approve Stock Adjustment
     */
    public function approveStockAdjustment($stock_adj, $input) 
    {
        $stock_adj->update(['approval_status' => $input['approval_status'], 'approved_by' => auth()->user()->id]);

        // update stock Qty
        $productvarIds = $stock_adj->items->pluck('productvar_id')->toArray();
        updateStockQty($productvarIds);

        /** accounting */
        $stock_adj->transactions()->delete();
        if ($stock_adj->approval_status == 'Approved') {
            $this->post_stock_adjustment($stock_adj);
        }
    }
}
