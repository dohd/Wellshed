<?php

namespace App\Repositories\Focus\stock_issue;

use App\Models\product\ProductVariation;
use DB;
use App\Exceptions\GeneralException;
use App\Models\stock_issue\StockIssue;
use App\Models\stock_issue\StockIssueItem;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class StockIssueRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = StockIssue::class;

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
     * @return StockIssue $stock_issue
     */
    public function create(array $input)
    {
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        // dd($input);
        foreach ($input as $key => $val) {
            if (in_array($key, ['issue_qty', 'qty_onhand', 'qty_rem', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }
        if (@$input['employee_id'] && !isset($input['account_id']))
            throw ValidationException::withMessages(['Expense account required!']);

        // create stock issue
        $data = Arr::only($input, [
            'date', 'ref_no', 'issue_to', 'employee_id', 'customer_id', 'project_id', 'note', 'quote_id', 'budget_line', 'total','account_id',
            'invoice_id','reference','purchase_requisition_id','finished_good_id'
        ]);
        $user_ids = @$input['assign_to_ids']? implode(',', $input['assign_to_ids']) : '';
        $data['assign_to_ids'] = $user_ids;
        $stock_issue = StockIssue::create($data);
        $stock_issue->issue_to_third_party = request('issue_to_third_party');
        $stock_issue->save();

        $data_items = array_diff_key($input, $data);
        $data_items['stock_issue_id'] = array_fill(0, count($data_items['issue_qty']), $stock_issue->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['warehouse_id'] && floatval($v['issue_qty']) > 0 && floatval($v['amount']));
        if (!$data_items) throw ValidationException::withMessages(['issue-qty and location are required']);
        StockIssueItem::insert($data_items);

        if ($stock_issue) {
            DB::commit();
            return $stock_issue;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param StockIssue $stock_issue
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(StockIssue $stock_issue, array $input)
    {
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['issue_qty', 'qty_onhand', 'qty_rem', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }
        if (@$input['employee_id'] && !isset($input['account_id']))
            throw ValidationException::withMessages(['Expense account required!']);

        // update stock issue
        $data = Arr::only($input, ['date', 'ref_no', 'issue_to', 'employee_id', 'customer_id', 'project_id', 'note', 'quote_id', 'budget_line', 'total','account_id','invoice_id','reference','requisition_id','finished_good_id']);
        $user_ids = @$input['assign_to_ids'] ? implode(',', $input['assign_to_ids']) : '';
        $data['assign_to_ids'] = $user_ids;
        $result = $stock_issue->update($data);
        $stock_issue->issue_to_third_party = request('issue_to_third_party');
        $stock_issue->save();

        $data_items = array_diff_key($input, $data);
        $data_items['stock_issue_id'] = array_fill(0, count($data_items['issue_qty']), $stock_issue->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['warehouse_id'] && floatval($v['issue_qty']) > 0 && floatval($v['amount']));
        if (!$data_items) throw ValidationException::withMessages(['issue-qty and location are required']);

        $stock_issue->items()->delete();
        StockIssueItem::insert($data_items);

        if ($stock_issue->status == 'APPROVED') {
            // update stock count
            $productvarIds = $stock_issue->items->pluck('productvar_id')->toArray();
            updateStockQty($productvarIds);
            
            /** accounting */
            $stock_issue->transactions()->delete();
            $this->post_stock_issue($stock_issue);
        }

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param StockIssue $stock_issue
     * @throws GeneralException
     * @return bool
     */
    public function delete(StockIssue $stock_issue)
    {
        DB::beginTransaction();
        $productvarIds = $stock_issue->items->pluck('productvar_id')->toArray();
        
        $stock_issue->items()->delete();
        $stock_issue->transactions()->delete();

        // update stock Qty
        updateStockQty($productvarIds);
        if ($stock_issue->delete()) {
            DB::commit();
            return true;
        }
    }

    public function approve_stock_issue($stock_issue, array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        

        if($data['status'] == 'REJECTED' || $data['status'] == 'ON HOLD'){
            $stock_issue->update($data);
        }else{
            if ($data['status'] == 'APPROVED' && $stock_issue->status != 'APPROVED') {
                $productvarIds = [];
                foreach ($stock_issue->items as $item) {
                    # code...
                    $productvarIds[] = $item->productvar_id;
                    if($item->item_id > 0 && $item->booked_qty > 0) {
                        $item->product_item_id = $item->productvar_id;
                        $item->productvar_id = $item->item_id;
                        $productvarIds[] = $item->item_id;
                        $item->update();
                        $product = ProductVariation::where('id', $item->productvar_id)->first();
                        $product->qty -= $item->issue_qty;
                        $product->fifo_cost = $item['cost'];
                        $product->update();
                        //Update Requisition issued Qty
                        if($item->requisition_item){
                            $requisition = $item->requisition_item;
                
                            $requisition->issued_qty += $item->issue_qty;
                            $requisition->update();
                        }
                        if($item->budget_item){
                            $budget = $item->budget_item;
                            $budget->issue_qty += $item->issue_qty;
                            $budget->update();
                        }
                        //update budget issue quantity
                    }else{
                        $product = ProductVariation::where('id', $item->productvar_id)->first();
                        $product->qty -= $item->issue_qty;
                        $product->fifo_cost = $item['cost'];
                        $product->update();
                        //Update Requisition issued Qty
                        if($item->requisition_item){
                            $requisition = $item->requisition_item;
                            $requisition->issued_qty += $item->issue_qty;
                            $requisition->update();
                        }
                        if($item->budget_item){
                            $budget = $item->budget_item;
                            $budget->issue_qty += $item->issue_qty;
                            $budget->update();
                        }
                    }
                }
                
                $stock_issue->update($data);
                
                // update stock qty
                updateStockQty($productvarIds);

                /** accounting */
                $stock_issue->transactions()->delete();
                $this->post_stock_issue($stock_issue);
            }
        }

        
        if($stock_issue){
            DB::commit();
            return true;
        }
    }
}
