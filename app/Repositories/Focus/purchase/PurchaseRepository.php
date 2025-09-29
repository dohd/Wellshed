<?php

namespace App\Repositories\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\items\PurchaseItem;
use App\Models\items\UtilityBillItem;
use App\Models\product\ProductVariation;
use App\Models\project\ProjectMileStone;
use App\Models\queuerequisition\QueueRequisition;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class PurchaseorderRepository.
 */
class PurchaseRepository extends BaseRepository
{
    use Accounting;
    
    /**
     * Associated Repository Model.
     */
    const MODEL = Purchase::class;

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

        $q->when(request('supplier_id'), function($q) {
            $q->where('supplier_id', request('supplier_id'));
        });
        if (!request('supplier_id')) $q->limit(500);

        return $q->latest();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\purchase\Purchase $purchase
     */
    public function create(array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'purchase_class_budget'
            ];
            if (in_array($key, $rate_keys)) $data[$key] = numberClean($val);
            if (in_array($key, ['date', 'due_date'])) $data[$key] = date_for_database($val);
        }

        if (@$data['doc_ref_type'] == 'Invoice') {
            // if ($data['doc_ref'] && $data['tax'] > 1)            
            //     throw ValidationException::withMessages(['invoice_no' => 'Reference No. should Exist']);
            // restrict special characters to only "/" and "-"
            $pattern = "/^[a-zA-Z0-9-\/]+$/i";
            if (!preg_match($pattern, $data['doc_ref']))
                throw ValidationException::withMessages(['Purchase invoice contains invalid characters']);
            $inv_exists = Purchase::where('doc_ref_type', 'Invoice')
                ->where('doc_ref', $data['doc_ref'])->where('tax', $data['tax'])->exists();
            if ($inv_exists) throw ValidationException::withMessages(['Purchase with similar invoice exists']);
        }

        if (@$data['supplier_taxid']) {
            $taxid_exists = Supplier::where('taxid', $data['supplier_taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists && $data['supplier_type'] != 'supplier') throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['supplier_taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);

            if (config('services.efris.base_url')) {
                // 
            } else {
                // Validate Tax PIN
                if (strlen($data['supplier_taxid']) != 11)
                    throw ValidationException::withMessages(['Supplier Tax Pin should contain 11 characters']);
                if (!in_array($data['supplier_taxid'][0], ['P', 'A'])) 
                    throw ValidationException::withMessages(['First character of Tax Pin must be letter "P" or "A"']);
                $pattern = "/^[0-9]+$/i";
                if (!preg_match($pattern, substr($data['supplier_taxid'],1,9))) 
                throw ValidationException::withMessages(['Characters between 2nd and 10th letters must be numbers']);
                $letter_pattern = "/^[a-zA-Z]+$/i";
                if (!preg_match($letter_pattern, $data['supplier_taxid'][-1])) 
                    throw ValidationException::withMessages(['Last character of Tax Pin must be a letter!']);
            }
        }
        if (@$data['tax'] > 0 && @$data['supplier_taxid'] == '')
            throw ValidationException::withMessages(['Tax Pin is Required!!']);
        
        // create walkin supplier if none exists
        if (@$data['supplier_type'] == 'walk-in') {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'payable'))
                ->whereHas('currency', fn($q) => $q->where('rate', 1))
                ->first();
            if (!$account) throw ValidationException::withMessages(['Accounts Payable account is required']);

            $supplier = Supplier::where('name', 'LIKE', '%walk-in%')->orWhere('company', 'LIKE', '%walk-in%')->first();
            if ($supplier) {
                if (!$supplier->ap_account_id) $supplier->update(['ap_account_id' => $account->id]);
            } else {
                $company = Company::find(auth()->user()->ins);
                $supplier = Supplier::create([
                    'ap_account_id' => $account->id,
                    'currency_id' => $account->currency_id,
                    'name' => 'Walk-In',
                    'phone' => 0,
                    'address' => 'N/A',
                    'city' => @$company->city,
                    'region' => @$company->region,
                    'country' => @$company->country,
                    'email' => 'walkin@sample.com',
                    'company' => 'Walk-In',
                    'taxid' => 'N/A',
                    'role_id' => 0,
                ]);
            }
            $data['supplier_id'] = $supplier->id;
        }
        
        $tid = Purchase::where('ins', $data['ins'])->max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1; 
        $result = Purchase::create($data);

        $prod_variation_ids = [];
        $data_items = $input['data_items'];
        foreach ($data_items as $i => $item) {
            // check if warehouse or project is selected
            if ($item['type'] == 'Stock' && !@$item['warehouse_id'] && !@$item['itemproject_id']) {
                throw ValidationException::withMessages(['Item Location or Project required on line: ' . strval($i+1)]);
            }

           

            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount']))
                    $item[$key] = numberClean($val);
                if (@$item['warehouse_id'] || @$item['warehouse_id'] && @$item['itemproject_id']) 
                    $item['itemproject_id'] = null;
                if ($item['type'] == 'Expense' && empty($item['uom'])) $item['uom'] = 'Lot';
            }

            // reset classlist
            if (@$item['item_classlist_id']) {
                $item['classlist_id'] = $item['item_classlist_id'];
                unset($item['item_classlist_id']);
            } else{ 
                $item['classlist_id'] = null;
                unset($item['item_classlist_id']);
            }

            // append modified data_items
            $data_items[$i] = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'bill_id' => $result->id
            ]);

            // increase stock
            if ($item['type'] == 'Stock' && $item['warehouse_id']) {
                $prod_variation = ProductVariation::find($item['item_id']);
                if ($prod_variation->warehouse_id != $item['warehouse_id']) {
                    $similar_prod_variation = ProductVariation::where(['parent_id' => $prod_variation->parent_id, 'warehouse_id' => $item['warehouse_id']])
                        ->where('name', 'LIKE', '%'. $prod_variation->name .'%')
                        ->first();
                    if (!$similar_prod_variation) {
                        // new warehouse product variation
                        $similar_prod_variation = $prod_variation->replicate();
                        $similar_prod_variation->warehouse_id = $item['warehouse_id'];
                        unset($similar_prod_variation->id, $similar_prod_variation->qty);
                        $similar_prod_variation->save();
                        $prod_variation = $similar_prod_variation;
                    }
                }
                if ($prod_variation) $prod_variation_ids[] = $prod_variation->id;
            }
        }
        $data_items = array_filter($data_items, fn($v) => $v['qty'] > 0 && $v['rate'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['qty and rate required for all line items']);
        PurchaseItem::insert($data_items); 
        
        // check if item totals match parent totals
        $items_amount = $result->items->sum('amount');
        if (round($result->grandttl) != round($items_amount)) {
            throw ValidationException::withMessages(['Aggregated totals do not match line item totals']);
        }

        // update stock qty
        updateStockQty($prod_variation_ids);

        // update milestone or budget-line balance
        $purchase = $result;
        $budgetLineIds = $purchase->items()->whereHas('budgetLine')->pluck('budget_line_id')->toArray();
        $budgetExpensesPerLine = PurchaseItem::whereIn('budget_line_id', $budgetLineIds)
            ->groupBy('budget_line_id')
            ->selectRaw('budget_line_id, SUM(qty*rate*(1+itemtax*0.01)) amount')
            ->pluck('amount', 'budget_line_id');
        if (count($budgetExpensesPerLine)) {
            foreach ($budgetExpensesPerLine as $key => $amount) {
                $milestone = ProjectMileStone::where('id', $key)->first();
                if ($milestone) $milestone->update(['balance' => $milestone->amount-$amount]);            
            }
        } elseif ($purchase->budgetLine) {
            $budgetExpense = PurchaseItem::whereHas('purchase', fn($q) => $q->where('project_milestone', $purchase->project_milestone))
            ->sum(DB::raw('qty*rate*(1+itemtax*0.01)'));
            $purchase->budgetLine->update(['balance' => $purchase->budgetLine->amount-$budgetExpense]);
        }

        /** accounting **/
        if (@$data['supplier_type'] == 'walk-in') {
            $result->supplier = $supplier;
        }
        $bill = $this->generate_bill($result);
        $result->bill_id = $bill->id;
        $this->post_purchase_expense($result);
        unset($result->bill_id);

        if ($result) {
            DB::commit();
            return $result;   
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Purchaseorder $purchaseorder
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($purchase, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];

        /** Handling milestone changes */
        $budgetLine = ProjectMileStone::find($purchase->project_milestone);
        $newMilestoneId = $input['data']['project_milestone'] ?? 0;
        $newBudgetLine = ProjectMileStone::find($newMilestoneId);
        $milestoneChanged = intval($purchase->project_milestone) !== intval($newMilestoneId);
        $grandTotalChanged = floatval($purchase->grandttl) !== floatval(str_replace(',', '', $input['data']['grandttl']));
        $newMilestoneZero = intval($newMilestoneId) === 0;
        $oldMilestoneZero = intval($purchase->project_milestone) === 0;
        /** If the milestone HAS CHANGED and grand total HAS CHANGED  */
        if($milestoneChanged && $grandTotalChanged){
            if (!$oldMilestoneZero && $budgetLine) {
                $budgetLine->balance = $budgetLine->balance + $purchase->grandttl;
                $budgetLine->save();
            }
            if (!$newMilestoneZero && $newBudgetLine) {
                $newBudgetLine->balance -= floatval(str_replace(',', '', $input['data']['grandttl']));
                $newBudgetLine->save();
            }
        }
        /** If the milestone has NOT changed but grand total HAS CHANGED */
        else if (!$milestoneChanged && $grandTotalChanged){
            if (!$oldMilestoneZero && $budgetLine) {
                $budgetLine->balance = ($budgetLine->balance + $purchase->grandttl) - floatval(str_replace(',', '', $input['data']['grandttl']));
                $budgetLine->save();
            }
        }
        /** If the milestone HAS CHANGED but grand total HAS NOT CHANGED */
        else if($milestoneChanged && !$grandTotalChanged){
            if (!$oldMilestoneZero && $budgetLine) {
                $budgetLine->balance = $budgetLine->balance + $purchase->grandttl;
                $budgetLine->save();
            }
            if (!$newMilestoneZero && $newBudgetLine) {
                $newBudgetLine->balance -= $purchase->grandttl;
                $newBudgetLine->save();
            }
        }

        foreach ($data as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'purchase_class_budget'
            ];
            if (in_array($key, ['date', 'due_date'])) $data[$key] = date_for_database($val);
            if (in_array($key, $rate_keys)) $data[$key] = numberClean($val);
        }

        if (@$data['doc_ref_type'] == 'Invoice') {
            // if ($data['doc_ref'] && $data['tax'] > 1)
            //     throw ValidationException::withMessages(['invoice_no' => 'Reference No. should Exist']);
            // restrict special characters to only "/" and "-"
            $pattern = "/^[a-zA-Z0-9-\/]+$/i";
            if (!preg_match($pattern, $data['doc_ref']))
                throw ValidationException::withMessages(['Purchase invoice contains invalid characters!']);
            $inv_exists = Purchase::where('id', '!=', $purchase->id)->where('doc_ref_type', 'Invoice')
                ->where('doc_ref', $data['doc_ref'])->where('tax', $data['tax'])->exists();
            if ($inv_exists) throw ValidationException::withMessages(['Purchase with similar invoice exists!']);
        }
        
        if (@$data['supplier_taxid']) {
            $taxid_exists = Supplier::where('taxid', $data['supplier_taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists && $data['supplier_type'] != 'supplier') throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['supplier_taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);

            if (config('services.efris.base_url')) {
                // 
            } else {
                // Validate TAX Pin 
                if (strlen($data['supplier_taxid']) != 11)
                    throw ValidationException::withMessages(['Supplier Tax Pin should contain 11 characters']);
                if (!in_array($data['supplier_taxid'][0], ['P', 'A'])) 
                    throw ValidationException::withMessages(['Initial character of Tax Pin must be letter "P" or "A"']);
                $pattern = "/^[0-9]+$/i";
                if (!preg_match($pattern, substr($data['supplier_taxid'],1,9))) 
                    throw ValidationException::withMessages(['Characters between 2nd and 10th letters must be numbers']);
                $letter_pattern = "/^[a-zA-Z]+$/i";
                if (!preg_match($letter_pattern, $data['supplier_taxid'][-1])) 
                    throw ValidationException::withMessages(['Last character of Tax Pin must be a letter']);
            }
        }

        // create walkin supplier if none exists
        if (@$data['supplier_type'] == 'walk-in') {
            $supplier = Supplier::where('name', 'LIKE', '%walk-in%')->orWhere('company', 'LIKE', '%walk-in%')->first();
            if (!$supplier) {
                $company = Company::find(auth()->user()->ins);
                $supplier = Supplier::create([
                    'name' => 'Walk-In',
                    'phone' => 0,
                    'address' => 'N/A',
                    'city' => @$company->city,
                    'region' => @$company->region,
                    'country' => @$company->country,
                    'email' => 'walkin@sample.com',
                    'company' => 'Walk-In',
                    'taxid' => 'N/A',
                    'role_id' => 0,
                ]);
            }
            $data['supplier_id'] = $supplier->id;
        }
        $result = $purchase->update($data);

        $prod_variation_ids = [];
        $data_items = $input['data_items']; 
        $item_ids = array_map(fn($v) => $v['id'], $data_items);
        $purchase->items()->whereNotIn('id', $item_ids)->delete();
        // create or update purchase item
        foreach ($data_items as $i => $item) {  
            if ($item['type'] == 'Stock' && !@$item['warehouse_id'] && !@$item['itemproject_id']) {
                throw ValidationException::withMessages(['Item Location or Project required on line: ' . strval($i+1)]);
            }
            
        
            if ($item['type'] == 'Expense' && empty($item['uom'])) $item['uom'] = 'Lot';                  
            $purchase_item = PurchaseItem::firstOrNew(['id' => $item['id']]);

            // update classlist_id
            if (@$item['item_classlist_id']) {
                $item['classlist_id'] = $item['item_classlist_id'];
                unset($item['item_classlist_id']);
            } else {
                 $item['classlist_id'] = null;
                unset($item['item_classlist_id']);
            }

            // update product stock
            if ($item['type'] == 'Stock' && $item['warehouse_id']) {
                $prod_variation = $purchase_item->product;
                if (!$prod_variation) $prod_variation = ProductVariation::find($item['item_id']);
                if ($prod_variation->warehouse_id != $item['warehouse_id']) {   
                    $similar_product = ProductVariation::where(['parent_id' => $prod_variation->parent_id, 'warehouse_id' => $item['warehouse_id']])
                        ->where('name', 'LIKE', '%'. $prod_variation->name .'%')->first();
                    if (!$similar_product) {
                        // new product
                        $similar_product = $prod_variation->replicate();
                        $similar_product->warehouse_id = $item['warehouse_id'];
                        unset($similar_product->id, $similar_product->qty);
                        $similar_product->save();
                        $prod_variation = $similar_product;
                    }
                }
                if ($prod_variation) $prod_variation_ids[] = $prod_variation->id;
            }    

            $item = array_replace($item, [
                'ins' => $purchase->ins,
                'user_id' => $purchase->user_id,
                'bill_id' => $purchase->id,
                'rate' => numberClean($item['rate']),
                'taxrate' => numberClean($item['taxrate']),
                'amount' => numberClean($item['amount']),
                // 'purchase_class_budget' => numberClean(@$item['purchase_class_budget']),
            ]);

            if(@$item['budget_line_id'] && !is_numeric($item['budget_line_id'])) 
                $item['budget_line_id'] = null;

            $purchase_item->fill($item);
            if (!$purchase_item->id) unset($purchase_item->id);
            if ($purchase_item->warehouse_id || $purchase_item->warehouse_id && $purchase_item->itemproject_id) {
                $purchase_item->itemproject_id = null;
            }
            $purchase_item->save();
        }

        // update stock qty
        updateStockQty($prod_variation_ids);

        // check if item totals match parent totals
        $items_amount = $purchase->items->sum('amount');
        if (round($purchase->grandttl) != round($items_amount)) {
            throw ValidationException::withMessages(['Aggregated totals do not match line item totals']);
        }
        
        // update milestone or budget-line balance
        $budgetLineIds = $purchase->items()->whereHas('budgetLine')->pluck('budget_line_id')->toArray();
        $budgetExpensesPerLine = PurchaseItem::whereIn('budget_line_id', $budgetLineIds)
            ->groupBy('budget_line_id')
            ->selectRaw('budget_line_id, SUM(qty*rate*(1+itemtax*0.01)) amount')
            ->pluck('amount', 'budget_line_id');
        if (count($budgetExpensesPerLine)) {
            foreach ($budgetExpensesPerLine as $key => $amount) {
                $milestone = ProjectMileStone::where('id', $key)->first();
                if ($milestone) $milestone->update(['balance' => $milestone->amount-$amount]);            
            }
        } elseif ($purchase->budgetLine) {
            $budgetExpense = PurchaseItem::whereHas('purchase', fn($q) => $q->where('project_milestone', $purchase->project_milestone))
            ->sum(DB::raw('qty*rate*(1+itemtax*0.01)'));
            $purchase->budgetLine->update(['balance' => $purchase->budgetLine->amount-$budgetExpense]);
        }

        /** accounting */
        $bill = $this->generate_bill($purchase);
        $purchase->bill_id = $bill->id;
        $this->post_purchase_expense($purchase);
        unset($purchase['bill_id']);

        if ($result) {
            DB::commit();

            // check and highlight related invoices
            $project_items = $purchase->items()->whereHas('project')->pluck('id')->toArray();
            $invoiceNos = Transaction::whereHas('invoice')->whereIn('purchase_item_id', $project_items)
                ->with(['invoice' => fn($q) => $q->select('tid')])
                ->get()
                ->map(fn($v) => gen4tid('', $v->invoice->tid))
                ->unique()
                ->implode(', ');
            $purchase['invoiceNos'] = $invoiceNos;

            return $purchase;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Purchaseorder $purchaseorder
     * @throws GeneralException
     * @return bool
     */
    public function delete($purchase)
    {
        $bill = $purchase->bill;
        if ($bill && $bill->payments()->exists()) {
            throw ValidationException::withMessages(['Not allowed! Purchase is billed and has related payments']);
        }

        DB::beginTransaction();

        if ($bill) {
            $bill->transactions()->delete();
            $bill->items()->delete();
            $bill->delete();
        }
        // expenses moved to COG from WIP via invoice
        $project_items = $purchase->items()->whereHas('project')->pluck('id')->toArray();
        Transaction::whereNull('bill_id')->whereIn('purchase_item_id', $project_items)->delete();

        $variationIds = $purchase->items()->where('type', 'Stock')->whereHas('productvariation')->pluck('item_id')->toArray();
        $budgetLineIds = $purchase->items()->whereHas('budgetLine')->pluck('budget_line_id')->toArray();
        
        $purchase->items()->delete();
        updateStockQty($variationIds);

        // update milestone or budget-line balance
        $budgetExpensesPerLine = PurchaseItem::whereIn('budget_line_id', $budgetLineIds)
            ->groupBy('budget_line_id')
            ->selectRaw('budget_line_id, SUM(qty*rate*(1+itemtax*0.01)) amount')
            ->pluck('amount', 'budget_line_id');
        if (count($budgetExpensesPerLine)) {
            foreach ($budgetExpensesPerLine as $key => $amount) {
                $milestone = ProjectMileStone::where('id', $key)->first();
                if ($milestone) $milestone->update(['balance' => $milestone->amount-$amount]);            
            }
        } elseif ($purchase->budgetLine) {
            $budgetExpense = PurchaseItem::whereHas('purchase', fn($q) => $q->where('project_milestone', $purchase->project_milestone))
            ->sum(DB::raw('qty*rate*(1+itemtax*0.01)'));
            $purchase->budgetLine->update(['balance' => $purchase->budgetLine->amount-$budgetExpense]);
        }
        
        // check and highlight related invoices
        $project_items = $purchase->items()->whereHas('project')->pluck('id')->toArray();
        $invoiceNos = Transaction::whereHas('invoice')->whereIn('purchase_item_id', $project_items)
            ->with(['invoice' => fn($q) => $q->select('tid')])
            ->get()
            ->map(fn($v) => gen4tid('', $v->invoice->tid))
            ->unique()
            ->implode(', ');

        if ($purchase->delete()) {
            DB::commit();

            $purchase = ['invoiceNos' => $invoiceNos];
            if ($purchase['invoiceNos']) return $purchase;

            return true;
        }
    }

    /**
     * Generate Purchase Bill
     * 
     * @param Purchase $purchase
     * @return UtilityBill $bill
     */
    public function generate_bill($purchase)
    {
        $bill_items_data = array_map(fn($v) => [
            'ref_id' => $v['id'],
            'note' => "({$v['type']}) {$v['description']} {$v['uom']}",
            'qty' => $v['qty'],
            'subtotal' => $v['qty'] * $v['rate'],
            'tax' => $v['taxrate'],
            'total' => $v['amount'], 
        ], $purchase->items->toArray());

        $bill_data = [
            'currency_id' => Currency::where('rate', 1)->first()->id,
            'supplier_id' => $purchase->supplier_id,
            'reference' => $purchase->doc_ref,
            'reference_type' => strtolower($purchase->doc_ref_type),
            'document_type' => 'direct_purchase',
            'ref_id' => $purchase->id,
            'purchase_id' => $purchase->id,
            'date' => $purchase->date,
            'due_date' => $purchase->due_date,
            'tax_rate' => $purchase->tax,
            'subtotal' => $purchase->paidttl,
            'tax' => $purchase->grandtax,
            'total' => $purchase->grandttl,
            'note' => $purchase->note,
        ];

        $bill = UtilityBill::where(['document_type' => 'direct_purchase','ref_id' => $purchase->id])->first();
        if ($bill) {
            // update bill
            $bill->update($bill_data);
            foreach ($bill_items_data as $item) {
                $new_item = UtilityBillItem::firstOrNew(['bill_id' => $bill->id, 'ref_id' => $item['ref_id']]);
                $new_item->fill($item);
                $new_item->save();
            }
            $bill->transactions()->delete();
            // expenses moved to COG from WIP via invoice
            $project_items = $purchase->items()->whereHas('project')->pluck('id')->toArray();
            Transaction::whereNull('bill_id')->whereIn('purchase_item_id', $project_items)->delete();
        } else {
            // create bill
            $bill_data['tid'] = UtilityBill::max('tid')+1;
            $bill = UtilityBill::create($bill_data);
            $bill_items_data = array_map(function ($v) use($bill) {
                $v['bill_id'] = $bill->id;
                return $v;
            }, $bill_items_data);
            UtilityBillItem::insert($bill_items_data);
        }
        return $bill;
    }
}
