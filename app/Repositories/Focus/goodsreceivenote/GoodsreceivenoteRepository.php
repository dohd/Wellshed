<?php

namespace App\Repositories\Focus\goodsreceivenote;

use App\Exceptions\GeneralException;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\PurchaseorderItem;
use App\Models\items\UtilityBillItem;
use App\Models\product\ProductVariation;
use App\Models\transaction\Transaction;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class GoodsreceivenoteRepository.
 */
class GoodsreceivenoteRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = Goodsreceivenote::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        });
        // supplier user filter
        $supplier_id = auth()->user()->supplier_id;
        $q->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id));    

        $q->when(request('supplier_id'), function ($q) {
            $q->where('supplier_id', request('supplier_id'));
        })->when(request('invoice_status'), function ($q) {
            switch (request('invoice_status')) {
                case 'with_invoice':
                    $q->whereNotNull('invoice_no');
                    break;
                case 'without_invoice':
                    $q->whereNull('invoice_no');
                    break;
            }
        });
         
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     */
    public function create(array $input)
    {
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'invoice_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['tax_rate', 'subtotal', 'tax', 'total', 'fx_curr_rate'])) 
                $input[$key] = numberClean($val);
            if (in_array($key, ['qty', 'rate'])) 
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }
        
        DB::beginTransaction();
        
        $tid = Goodsreceivenote::max('tid');
        if ($input['tid'] <= $tid) $input['tid'] = $tid+1;
        $result = Goodsreceivenote::create($input);

        // grn items
        $data_items = Arr::only($input, ['qty', 'rate', 'purchaseorder_item_id', 'item_id','warehouse_id', 'itemproject_id','account_id']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => floatval($v['qty']) > 0 && floatval($v['rate']));
        if (!$data_items) throw ValidationException::withMessages(['Line items with qty and rate required']);
        $data_items = array_filter($data_items, fn($v) => $v['warehouse_id'] || $v['itemproject_id'] || $v['account_id']);
        if (!$data_items) throw ValidationException::withMessages(['Cannot generate GRN without project or location or Ledger account!']);

        $total = 0;
        $subtotal = 0;
        $tax = 0;
        $fx_total = 0;
        $fx_subtotal = 0;
        $fx_tax = 0;
        foreach ($data_items as $i => $item) {
            if ($item['warehouse_id'] && $item['itemproject_id']) 
                throw ValidationException::withMessages(['Product #' . strval($i+1) . ' has both location and project!']);
            $po_item = PurchaseorderItem::find($item['purchaseorder_item_id']);
            if (!$po_item) continue;
            
            $item['goods_receive_note_id'] = $result->id;
            $item['tax_rate'] = 0;
            if ($result->tax_rate > 0 && $po_item->itemtax == $result->tax_rate) {
                $item['tax_rate'] = $result->tax_rate;
            }
            $item_subtotal = $item['qty'] * $item['rate'];
            $item_tax = $item_subtotal * $item['tax_rate'] * 0.01;
            $item_taxable = $item['tax_rate'] > 0? $item_subtotal : 0;
            // forex values
            $fx_rate = $result->fx_curr_rate;
            if ($fx_rate > 1) {
                $item = array_replace($item, [
                    'fx_rate' => round($item['rate'] * $fx_rate, 4),
                    'fx_tax' => round($item_tax * $fx_rate, 4),
                    'fx_taxable' => round($item_taxable * $fx_rate, 4),
                    'fx_subtotal' => round($item_subtotal * $fx_rate, 4),
                    'fx_amount' => round(($item_subtotal + $item_tax) * $fx_rate, 4),
                ]);
                $fx_total += $item['fx_amount'];
                $fx_subtotal += $item['fx_subtotal'];
                $fx_tax += $item['fx_tax'];
            }
            $total += $item_subtotal + $item_tax;
            $subtotal += $item_subtotal;
            $tax += $item_tax;
            $data_items[$i] = $item;
        }
        $result->update(compact('total', 'subtotal', 'tax', 'fx_total', 'fx_subtotal', 'fx_tax'));
        GoodsreceivenoteItem::insert($data_items);

        // increase stock qty
        $productvarIds = [];
        foreach ($result->items as $i => $item) {
            $po_item = $item->purchaseorder_item;
            if (!$po_item) throw ValidationException::withMessages(['Line ' . strval($i+1) . ' related purchase order item does not exist!']);
            $receiptQty = $po_item->grn_items()->sum('qty');
            $po_item->update(['qty_received' => $receiptQty]);

            // check if is default product variation or is supplier product 
            $prod_variation = $item->productvariation;    
            if($item->warehouse_id){    
                if ($prod_variation->warehouse_id != $item['warehouse_id']) {   
                    $is_similar = false;
                    $similar_products = ProductVariation::where('id', '!=', $prod_variation->id)
                        ->where('name', 'LIKE', '%'. $prod_variation->name .'%')->get();
                    foreach ($similar_products as $s_product) {
                        if ($prod_variation->warehouse_id == $item['warehouse_id']) {
                            $is_similar = true;
                            $prod_variation = $s_product;
                            break;
                        }
                    }
                    if (!$is_similar) {
                        $new_product = clone $prod_variation;
                        $new_product->warehouse_id = $item['warehouse_id'];
                        unset($new_product->id, $new_product->qty);
                        $new_product->save();
                        $prod_variation = $new_product;
                    }
                }
               
                if ($prod_variation) $productvarIds[] = $prod_variation->id;
                else throw ValidationException::withMessages(['Product on line ' . strval($i+1) . ' may not exist! Please update it from the Purchase Order number ' . $po_item->purchaseorder->tid]);
                // dd($prod_variation);
            }
        }
        updateStockQty($productvarIds);

        // update purchase order status
        if (!$result->purchaseorder) throw ValidationException::withMessages(['Purchase order does not exist!']);
        $order_goods_qty = +$result->purchaseorder->items->sum('qty');
        $received_goods_qty = +$result->purchaseorder->grn_items->sum('qty');
        if ($received_goods_qty == 0) $result->purchaseorder->update(['status' => 'Pending']);
        elseif ($order_goods_qty > $received_goods_qty) $result->purchaseorder->update(['status' => 'Partial']);
        else $result->purchaseorder->update(['status' => 'Complete']);
       
        /** accounting */
        if ($result->invoice_no) {
            // GRN with invoice
            $bill = $this->generate_bill($result);
            $this->post_invoiced_grn_bill($bill);
        } else {
            // GRN without invoice
            $this->post_uninvoiced_grn($result); 
        }

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     */
    public function update(Goodsreceivenote $goodsreceivenote, array $input)
    {
        // sanitize
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'invoice_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['tax_rate', 'subtotal', 'tax', 'total'])) 
                $input[$key] = numberClean($val);
            if (in_array($key, ['qty', 'rate'])) 
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }
        
        if (@$input['invoice_no'] && empty($input['invoice_date'])) {
            throw ValidationException::withMessages(['invoice_date' => 'Invoice date is required.']);
        }

        DB::beginTransaction();
        
        $result = $goodsreceivenote->update($input);
        
        // update goods receive note items
        $data_items = Arr::only($input, ['qty', 'rate', 'id','warehouse_id', 'itemproject_id','account_id']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => floatval($v['qty']) > 0 && floatval($v['rate']));
        if (!$data_items) throw ValidationException::withMessages(['Line items with qty and rate required']);
        $data_items = array_filter($data_items, fn($v) => $v['warehouse_id'] || $v['itemproject_id'] || $v['account_id']);
        if (!$data_items) throw ValidationException::withMessages(['Cannot generate GRN without project or location or ledger account!']);

        $total = 0;
        $subtotal = 0;
        $tax = 0;
        $fx_total = 0;
        $fx_subtotal = 0;
        $fx_tax = 0;
        foreach ($data_items as $i => $item) {
            $grn_item = GoodsreceivenoteItem::find($item['id']);
            if ($item['warehouse_id'] && $item['itemproject_id']) throw ValidationException::withMessages(['Product #' . strval($i+1) . ' has both location and project']);
            if (!$grn_item) throw ValidationException::withMessages(['Product on line #' . strval($i+1) . ' is invalid']);
            $po_item = $grn_item->purchaseorder_item;
            if (!$po_item) throw ValidationException::withMessages(['Product on line #' . strval($i+1) . ' does not have a related purchase order item']);
            
            $item['tax_rate'] = 0;
            if ($goodsreceivenote->tax_rate > 0 && $po_item->itemtax == $goodsreceivenote->tax_rate) {
                $item['tax_rate'] = $goodsreceivenote->tax_rate;
            }
            $item_subtotal = $item['qty'] * $item['rate'];
            $item_tax = $item_subtotal * $item['tax_rate'] * 0.01;
            $item_taxable = $item['tax_rate'] > 0? $item_subtotal : 0;
            // forex values
            $fx_rate = $goodsreceivenote->fx_curr_rate;
            if ($fx_rate > 1) {
                $item = array_replace($item, [
                    'fx_rate' => round($item['rate'] * $fx_rate, 4),
                    'fx_tax' => round($item_tax * $fx_rate, 4),
                    'fx_taxable' => round($item_taxable * $fx_rate, 4),
                    'fx_subtotal' => round($item_subtotal * $fx_rate, 4),
                    'fx_amount' => round(($item_subtotal + $item_tax) * $fx_rate, 4),
                ]);
                $fx_total += $item['fx_amount'];
                $fx_subtotal += $item['fx_subtotal'];
                $fx_tax += $item['fx_tax'];
            }
            $total += $item_subtotal + $item_tax;
            $subtotal += $item_subtotal;
            $tax += $item_tax;
            
            // reverse and update qty
            $grn_item->decrement('qty', $grn_item->qty);
            // update other fields
            foreach ($item as $key1 => $value1) {
                $grn_item->$key1 = $value1;
            }
            $grn_item->save();
            if ($grn_item->qty == 0) {
                $grn_item->delete();
            }
        }
        $goodsreceivenote->update(compact('total', 'subtotal', 'tax', 'fx_total', 'fx_subtotal', 'fx_tax'));

        // increase stock qty with new update 
        $productvarIds = [];
        $grn_items = $goodsreceivenote->items()->get();
        foreach ($grn_items as $i => $item) {
            $po_item = $item->purchaseorder_item;
            if (!$po_item) throw ValidationException::withMessages(['Line ' . strval($i+1) . ' related purchase order item does not exist!']);

            $receiptQty = $po_item->grn_items()->sum('qty');
            $po_item->update(['qty_received' => $receiptQty]);

            // check if is default product variation or supplier product 
            $prod_variation = $item->productvariation;
            if ($prod_variation) $productvarIds[] = $prod_variation->id;
            else throw ValidationException::withMessages(['Product on line ' . strval($i+1) . ' may not exist! Please update it from the Purchase Order number ' . $po_item->purchaseorder->tid]);  
        }
        updateStockQty($productvarIds);

        // update purchase order status
        if (!$goodsreceivenote->purchaseorder) throw ValidationException::withMessages(['Purchase Order does not exist!']);
        $order_goods_qty = +$goodsreceivenote->purchaseorder->items->sum('qty');
        $received_goods_qty = +$goodsreceivenote->purchaseorder->grn_items->sum('qty');
        if ($received_goods_qty == 0) $goodsreceivenote->purchaseorder->update(['status' => 'Pending']);
        elseif ($order_goods_qty > $received_goods_qty) $goodsreceivenote->purchaseorder->update(['status' => 'Partial']);
        else $goodsreceivenote->purchaseorder->update(['status' => 'Complete']); 
        
        /**accounting */
        if ($goodsreceivenote->invoice_no) {
            $bill = $this->generate_bill($goodsreceivenote); 
            $this->post_invoiced_grn_bill($bill);
        } else {
            $goodsreceivenote->transactions()->delete();
            $this->post_uninvoiced_grn($goodsreceivenote);
        }
        
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @throws GeneralException
     * @return bool
     */
    public function delete(Goodsreceivenote $goodsreceivenote)
    {     
        DB::beginTransaction();

        $grn_bill = $goodsreceivenote->bill;
        if ($grn_bill) {
            if ($grn_bill->payments()->exists()) 
                throw ValidationException::withMessages(['Not Allowed! Goods Receive Note is billed on Bill No. '. gen4tid('', $grn_bill->tid). 'with associated payments']);
            $grn_bill->transactions()->delete();
            $grn_bill->items()->delete();
            $grn_bill->delete();
        }

        // decrease inventory stock 
        $productvarIds = [];
        foreach ($goodsreceivenote->items as $item) {
            $prod_variation = $item->productvariation;
            if ($prod_variation) $productvarIds[] = $prod_variation->id;
        }
        
        // expenses moved to COG from WIP via invoice
        $project_items = $goodsreceivenote->items()->whereHas('project')->pluck('id')->toArray();
        Transaction::whereNull('bill_id')->whereIn('grn_item_id', $project_items)->delete(); 

        // delete received items
        $goodsreceivenote->items()->delete();
        // update Stock Qty
        updateStockQty($productvarIds);

        // update purchase order status
        foreach ($goodsreceivenote->items as $item) {
            $po_item = $item->purchaseorder_item;
            if ($po_item) {
                $receiptQty = $po_item->grn_items()->sum('qty');
                $po_item->update(['qty_received' => $receiptQty]);
            }
        }
        if ($goodsreceivenote->purchaseorder) {
            $order_goods_qty = +$goodsreceivenote->purchaseorder->items->sum('qty');
            $received_goods_qty = +$goodsreceivenote->purchaseorder->grn_items->sum('qty');
            if ($received_goods_qty == 0) $goodsreceivenote->purchaseorder->update(['status' => 'Pending']);
            elseif ($order_goods_qty > $received_goods_qty) $goodsreceivenote->purchaseorder->update(['status' => 'Partial']);
            else $goodsreceivenote->purchaseorder->update(['status' => 'Complete']); 
        }
        
        $goodsreceivenote->transactions()->delete();
        if ($goodsreceivenote->delete()) {
            DB::commit(); 
            return true;
        }
    }

    /**
     * Generate Bill For Goods Receive with invoice
     * 
     * @param Goodsreceivenote $grn
     * @return void
     */
    public function generate_bill($grn)
    {
        $grn_items = $grn->items()->get()->map(fn($v) => [
            'ref_id' => $v->id,
            'note' => @$v->purchaseorder_item->description ?: '',
            'qty' => $v->qty,
            'subtotal' => $v->qty * $v->rate,
            'tax' => $v->qty * $v->rate * $v->tax_rate * 0.01,
            'total' => $v->qty * $v->rate * (1 + $v->tax_rate * 0.01),
            'fx_subtotal' => $v->fx_subtotal,
            'fx_taxable' => $v->fx_taxable,
            'fx_tax' => $v->fx_tax,
            'fx_total' => $v->fx_amount,
        ])
        ->toArray();    

        $bill_data = [
            'supplier_id' => $grn->supplier_id,
            'currency_id' => $grn->currency_id,
            'fx_curr_rate' => $grn->fx_curr_rate,
            'reference' => $grn->invoice_no,
            'reference_type' => 'invoice',
            'document_type' => 'goods_receive_note',
            'ref_id' => $grn->id,
            'grn_id' => $grn->id,
            'date' => $grn->invoice_date,
            'due_date' => $grn->invoice_date,
            'subtotal' => $grn->subtotal,
            'tax_rate' => $grn->tax_rate,
            'tax' => $grn->tax,
            'total' => $grn->total,
            'fx_subtotal' => $grn->fx_subtotal,
            'fx_taxable' => array_reduce($grn_items, fn($prev, $curr) => $curr['fx_tax'] > 0? $curr['fx_subtotal'] + $prev : $prev, 0),
            'fx_tax' => $grn->fx_tax,
            'fx_total' => $grn->fx_total,
            'note' => $grn->note,
        ];

        $bill = UtilityBill::where(['ref_id' => $grn->id, 'document_type' => 'goods_receive_note'])->first();
        if ($bill) {
            // update bill
            $bill->update($bill_data);
            foreach ($grn_items as $item) {
                $new_item = UtilityBillItem::firstOrNew(['bill_id' => $bill->id,'ref_id' => $item['ref_id']]);
                $new_item->fill($item);
                $new_item->save();
            }       
            $bill->transactions()->delete();    
            // expenses moved to COG from WIP via invoice
            $project_items = $grn->items()->whereHas('project')->pluck('id')->toArray();
            Transaction::whereNull('bill_id')->whereIn('grn_item_id', $project_items)->delete();             
        } else {
            // create bill
            $bill_data['tid'] = UtilityBill::max('tid')+1;
            $bill = UtilityBill::create($bill_data);
            // bill items
            $bill_items_data = array_map(function ($v) use($bill) {
                $v['bill_id'] = $bill->id;
                return $v;
            }, $grn_items);
            UtilityBillItem::insert($bill_items_data);
        }       
        return $bill; 
    }        
}