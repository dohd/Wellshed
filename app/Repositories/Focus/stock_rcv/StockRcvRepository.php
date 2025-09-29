<?php

namespace App\Repositories\Focus\stock_rcv;

use App\Exceptions\GeneralException;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\stock_rcv\StockRcv;
use App\Models\stock_rcv\StockRcvItem;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class StockRcvRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = StockRcv::class;

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
     * @return StockRcv $stock_rcv
     */
    public function create(array $input)
    {  
        DB::beginTransaction();
        
        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['qty_rcv', 'qty_rem', 'qty_transf', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // create stock receiving
        $data = Arr::only($input, ['stock_transfer_id', 'date', 'ref_no', 'receiver_id', 'note', 'total']);
        $stock_rcv = StockRcv::create($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_rcv_id'] = array_fill(0, count($data_items['qty_rcv']), $stock_rcv->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['qty_rcv'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Qty Received fields are required!']);
        StockRcvItem::insert($data_items);

        // update status
        $this->updateTransferStatus($stock_rcv->stock_transfer);
        
        // update Stock Qty
        foreach ($stock_rcv->items as $key => $item) {
            // dd($item->productvar, $stock_rcv, $item);
            $product_variation = ProductVariation::where(['warehouse_id'=> $stock_rcv->receiver_id, 'parent_id' =>$item->productvar->parent_id, 'name'=>$item->productvar->name])->first();
            if($product_variation){
                $product_variation->qty += $item->qty_rcv;
                $product_variation->update();
                $item->item_id = $product_variation->id;
                $item->update();
                // $item->productvar->qty -= $item->qty_rcv;
                // $item->productvar->update();
            }else{
                $product_variation = $item->productvar->replicate();
                $product_variation->warehouse_id = $stock_rcv->receiver_id;
                $product_variation->qty = $item->qty_rcv;
                $productcategory = Productcategory::where('id',$product_variation['productcategory_id'])->first();
                $prefix = $productcategory->code_initials;
                $codes = ProductVariation::where('productcategory_id', $product_variation['productcategory_id'])->where('code', '!=','')->get(['code'])->toArray();
                $product_variation->code = addMissingOrNextCode($codes, $prefix);
                $product_variation->save();
                $item->item_id = $product_variation->id;
                $item->update();
            }
        }
        
        if ($stock_rcv) {
            DB::commit();
            return $stock_rcv;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param StockRcv $stock_rcv
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(StockRcv $stock_rcv, array $input)
    {   
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['qty_rcv', 'qty_rem', 'qty_transf', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // update stock transfer
        $data = Arr::only($input, ['stock_transfer_id', 'date', 'ref_no', 'receiver_id', 'note', 'total']);
        $result =  $stock_rcv->update($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_rcv_id'] = array_fill(0, count($data_items['qty_rcv']), $stock_rcv->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['qty_rcv'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Qty Received fields are required!']);
        $stock_rcv->items()->delete();
        StockRcvItem::insert($data_items);

        // update transfer status
        $this->updateTransferStatus($stock_rcv->stock_transfer);

        // update Stock Qty
        $productvar_ids = $stock_rcv->items->pluck('productvar_id')->toArray();
        updateStockQty($productvar_ids);
        
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param StockRcv $stock_rcv
     * @throws GeneralException
     * @return bool
     */
    public function delete(StockRcv $stock_rcv)
    { 
        DB::beginTransaction();

        $productvar_ids = $stock_rcv->items->pluck('productvar_id')->toArray();
        $stock_rcv->items()->delete();
        // update transfer status
        $this->updateTransferStatus($stock_rcv->stock_transfer);
        // Update Stock Qty 
        updateStockQty($productvar_ids);

        if ($stock_rcv->delete()) {
            DB::commit();
            return true;
        }
    }

    /**
     * Update Stock Transfer Status
     */
    public function updateTransferStatus($stock_transfer)
    {
        if (!$stock_transfer) return false;
        foreach ($stock_transfer->items as $key => $item) {
            $qty_transf = round($item->qty_transf);
            $qty_rcv_total = round($item->rcv_items()->sum('qty_rcv'));
            if ($qty_rcv_total == 0) $stock_transfer->update(['status' => 'Pending']);
            elseif ($qty_transf > $qty_rcv_total) $stock_transfer->update(['status' => 'Partial']);
            else $stock_transfer->update(['status' => 'Complete']);
        }
        return true;
    }
}
