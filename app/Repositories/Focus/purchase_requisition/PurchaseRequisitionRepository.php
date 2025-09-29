<?php

namespace App\Repositories\Focus\purchase_requisition;

use App\Exceptions\GeneralException;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\purchase_request\PurchaseRequestItem;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\purchase_requisition\PurchaseRequisitionItem;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequisitionRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = PurchaseRequisition::class;

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
     * @return PurchaseRequisition $purchase_requisition
     */
    public function create(array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'expect_date'])) 
                $data[$key] = date_for_database($val);
        }

        $tid = PurchaseRequisition::where('ins', auth()->user()->ins)->max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1;
        
        $result = PurchaseRequisition::create($data);
        //line items
        $data_items = $input['data_items'];
        $data_items = array_map(function($item) use($result){
            return array_replace($item, [
                'purchase_requisition_id' => $result['id'],
                'project_id' => $result['project_id'],
                'ins' => $result['ins'],
                'user_id' => $result['user_id'],
                'qty' =>  floatval(str_replace(',', '', $item['qty'])),
                'price' =>  floatval(str_replace(',', '', $item['price'])),
            ]);
        },$data_items);
        // dd($data_items, PurchaseRequisitionItem::insert($data_items));
        PurchaseRequisitionItem::insert($data_items);
        if ($result){
            DB::commit();
            foreach($result->items as $item)
            {
                $mr_item = $item->mr_items;
                $sum_of_pr = $item->stock_qty;
                // $sum_of_pr = $item->stock_qty + $item->purchase_qty;
                if($mr_item){
                    $mr_item->total_qty_to_pr += $sum_of_pr;
                    $mr_item->update();
                }
            }
            return $result;
        } 
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param PurchaseRequisition $purchase_requisition
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(PurchaseRequisition $purchase_requisition, array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'expect_date'])) 
                $data[$key] = date_for_database($val);
        }
        $purchase_requisition->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $purchase_requisition->items()->whereNotIn('id', $item_ids)->delete();

        foreach ($data_items as $item){
            foreach ($item as $key => $val) {
                if (in_array($key, ['price', 'qty']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $purchase_requisition_item = PurchaseRequisitionItem::firstOrNew(['id' => $item['id']]);
            $previous_qty = $purchase_requisition_item->stock_qty;
            // $previous_qty = $purchase_requisition_item->stock_qty + $purchase_requisition_item->purchase_qty;
            $purchase_requisition_item->fill(array_replace($item, ['purchase_requisition_id' => $purchase_requisition['id'], 'ins' => $purchase_requisition['ins']]));
            if (!$purchase_requisition_item->id) unset($purchase_requisition_item->id);
            $current_qty = $purchase_requisition_item->stock_qty;
            // $current_qty = $purchase_requisition_item->stock_qty + $purchase_requisition_item->purchase_qty;
            $diff = $previous_qty - $current_qty;
            $mr_item = PurchaseRequestItem::find($item['purchase_request_item_id']);
            $mr_item->total_qty_to_pr -= $diff;
            $mr_item->update();
            $purchase_requisition_item->save();
        }

        if ($purchase_requisition) {
            DB::commit();
            return $purchase_requisition;
        }

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param PurchaseRequisition $purchase_requisition
     * @throws GeneralException
     * @return bool
     */
    public function delete(PurchaseRequisition $purchase_requisition)
    {
        try {
            if ($purchase_requisition->purchaseOrder()->exists()) {
                throw ValidationException::withMessages(['Purchase Order has been attached!!']);
            }

            if ($purchase_requisition->stockIssue()->exists()) {
                throw ValidationException::withMessages(['Stock Issuance has been attached!!']);
            }

            foreach ($purchase_requisition->items as $item) {
                if($purchase_requisition->pr_parent_id) continue;
                if ($item) {
                    $mr_item = $item->mr_items;
                    $sum_of_pr = $item->stock_qty + $item->purchase_qty;

                    $mr_item->total_qty_to_pr -= $sum_of_pr;

                    if ($mr_item->total_qty_to_pr < 0) {
                        $mr_item->total_qty_to_pr = 0;
                    }

                    $mr_item->update();
                }
            }

            if ($purchase_requisition->items()->delete() && $purchase_requisition->delete()) {
                return true;
            }

            throw new GeneralException('Error Deleting Purchase Requisition');

        } catch (\Throwable $e) {
            // Optional: Log the error for troubleshooting
            \Log::error('Failed to delete Purchase Requisition', [
                'error' => $e->getMessage(),
                'requisition_id' => $purchase_requisition->id
            ]);

            throw $e instanceof ValidationException ? $e : new GeneralException('Error deleting Purchase Requisition: ' . $e->getMessage());
        }
    }

    public function create_pr_copy($purchase_requisition)
    {
        DB::beginTransaction();
        $pr = clone $purchase_requisition;
        $data = $pr->toArray();
        $data['pr_parent_id'] = $purchase_requisition->id;
        unset($data['id'], $data['tid'], $data['created_at'], $data['updated_at']);
        $tid = PurchaseRequisition::where('ins', auth()->user()->ins)->max('tid');
        // dd($data);
        $data['tid'] = $tid+1;
        $result = PurchaseRequisition::create($data);
        // dd($result);
        $pr_items = clone $purchase_requisition->items;
        $data_items = $pr_items->toArray();
        // dd($data_items);
        foreach ($data_items as $key => $item) {
            unset($item['id'],$item['created_at'], $item['updated_at']);
            $purchase_qty = $item['purchase_qty'];
            if($purchase_qty < 1) continue;
            $item['purchase_qty'] = 0;
            $item['stock_qty'] = $purchase_qty;
            $item['user_id'] = auth()->user()->id;
            $item['ins'] = auth()->user()->ins;
            $item['purchase_requisition_id'] = $result->id;
            // dd($item);
            PurchaseRequisitionItem::create($item);
        }
        if ($result) {
            DB::commit();
            return $result;
        }
    }

}
