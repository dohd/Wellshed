<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use App\Models\supplier\Supplier;
use Closure;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\purchase\CreateResponse;
use App\Http\Responses\Focus\purchase\EditResponse;
use App\Repositories\Focus\purchase\PurchaseRepository;
use App\Http\Requests\Focus\purchase\ManagePurchaseRequest;
use App\Http\Requests\Focus\purchase\StorePurchaseRequest;
use App\Http\Responses\RedirectResponse;
use App\Models\account\Account;
use Illuminate\Validation\ValidationException;

class PurchasesController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseRepository
     */
    public $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseRepository $repository ;
     */
    public function __construct(PurchaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManagePurchaseRequest $request)
    {
        $suppliers = Supplier::whereHas('bills')->get();

        // if (auth()->user()->ins == 85) {
        //     $purchases = Purchase::whereHas('items', function($q) {
        //         $q->whereHas('project', function($q) {
        //             $q->where('customer_id', 368);
        //         });
        //     })
        //     ->get();
        //     $errRecords = collect();
        //     $errors = collect();
        //     foreach ($purchases as $purchase) {
        //         try {
        //             \DB::beginTransaction();
        //             $bill = $this->repository->generate_bill($purchase);
        //             $purchase->bill_id = $bill['id'];
        //             $this->repository->post_purchase_expense($purchase);
        //             \DB::commit();
        //             // dd($purchase->bill->transactions->map->only('account_id', 'bill_id', 'credit', 'debit'));
        //         } catch (\Throwable $th) {
        //             $errRecords->push($purchase);
        //             $errors->push($th);
        //         }
        //     }
        //     if ($errRecords->count()) {
        //         dd($errRecords->toArray(), $errors->toArray());
        //     }
        // }

        return new ViewResponse('focus.purchases.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatePurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\CreateResponse
     */
    public function create(StorePurchaseRequest $request)
    {
        return new CreateResponse('focus.purchases.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StorePurchaseRequest $request)
    {
        $data = $request->only([
            'supplier_type', 'supplier_id', 'suppliername', 'supplier_taxid', 'transxn_ref', 'date', 'due_date', 'doc_ref_type', 'doc_ref',
            'tax', 'tid', 'project_id', 'note', 'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'is_tax_exc', 'project_milestone', 'purchase_class',
            'cu_invoice_no', 'classlist_id','purchase_requisition_id'
        ]);
        $data_items = $request->only([
            'item_id', 'description', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 
            'type', 'warehouse_id', 'uom', 'asset_purchase_class','supplier_product_id', 'purchase_class_budget',
            'item_classlist_id', 'budget_line_id','import_request_id'
        ]);

        // purchase class validation
        if (@$data['purchase_class']) {
            $pcBudget = PurchaseClassBudget::where('purchase_class_id', $data['purchase_class'])
                ->whereHas('financialYear', function ($q) use ($data) {
                    $q->whereDate('start_date', '<=', date_for_database($data['date']))
                    ->whereDate('end_date', '>=', date_for_database($data['date']));
                })->first();
            $request->validate([
                'purchase_class' => ['nullable',
                    function (string $attribute, $value, Closure $fail) use ($pcBudget) {
                        if (!$pcBudget) {
                            $fail("The selected Non-Project Class has no Budget for the year wherein lies the purchase date...");
                        }
                    },
                ],
            ]);
            $data = array_merge($data, ['purchase_class_budget' => $pcBudget->id]);
            unset($data['purchase_class']);
        } else {
            $data = array_merge($data, ['purchase_class_budget' => null]);
            unset($data['purchase_class']);
        }
       
        // purchase class item validation
        if (@$data_items['purchase_class_budget']) {
            for ($u = 0; $u < count($data_items['purchase_class_budget']); $u++){
                if (!empty($data_items['purchase_class_budget'][$u])) {
                    $purchaseDate = (new DateTime($data['date']))->format('Y-m-d');
                    $pcBudget = PurchaseClassBudget::where('purchase_class_id', $data_items['purchase_class_budget'][$u])
                        ->whereHas('financialYear', function ($query) use ($purchaseDate) {
                            $query->whereDate('start_date', '<=', $purchaseDate)
                                ->whereDate('end_date', '>=', $purchaseDate);
                        })
                        ->first();
                    $request->validate([
                        'purchaseClass' => [
                            'nullable',
                            function (string $attribute, $value, Closure $fail) use ($pcBudget, $u) {
                                if (!is_null($value) && !$pcBudget) {
                                    $fail("The selected Non-Project Class for item " . ($u+1) . " has no Budget for the year wherein lies the purchase date...");
                                }
                            },
                        ],
                    ]);
                    if ($pcBudget) {
                        $data_items['purchase_class_budget'][$u] = $pcBudget->id;
                    }
                } else {
                    $data_items['purchase_class_budget'][$u] = null;
                }                
            }
        }

        if (!empty($data['cu_invoice_no'])){
            $refBackup = ['doc_ref_backup' => $data['doc_ref']];
            $data['doc_ref'] = $data['cu_invoice_no'];
            $data = array_merge($data, $refBackup);
        }
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['item_id'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Please use suggested options for input within a row!']);

        try {
            $purchase = $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Expense '.$th->getMessage(), $th);
        }
        
        $msg = 'Expense Created Successfully. <span class="pl-5 font-weight-bold h5"><a href="'. route('biller.billpayments.create', ['src_id' => $purchase->id, 'src_type' => 'direct_purchase']) .'" target="_blank" class="btn btn-purple">
            <i class="fa fa-money"></i> Direct Payment</a></span>';
        return new RedirectResponse(route('biller.purchases.index'), ['flash_success' => $msg]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @param EditPurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\EditResponse
     */
    public function edit(Purchase $purchase, StorePurchaseRequest $request)
    {
        return new EditResponse($purchase);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StorePurchaseRequest $request, Purchase $purchase)
    {
        // extract input details
        $data = $request->only([
            'supplier_type', 'supplier_id', 'suppliername', 'supplier_taxid', 'transxn_ref', 'date', 'due_date', 'doc_ref_type', 'doc_ref',
            'tax', 'tid', 'project_id', 'note', 'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'is_tax_exc', 'project_milestone', 'purchase_class',
            'cu_invoice_no', 'classlist_id','purchase_requisition_id'
        ]);
        $data_items = $request->only([
            'id', 'item_id', 'description', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 
            'type', 'warehouse_id', 'uom', 'asset_purchase_class','supplier_product_id', 'purchase_class_budget',
            'item_classlist_id', 'budget_line_id','import_request_id'
        ]);

        if (@$data['purchase_class']) {
            $pcBudget = PurchaseClassBudget::where('purchase_class_id', $data['purchase_class'])
                ->whereHas('financialYear', function ($query) use ($data) {
                    $query->whereDate('start_date', '<=', date_for_database($data['date']))
                        ->whereDate('end_date', '>=', date_for_database($data['date']));
                })
                ->first();
            $request->validate([
                'purchase_class' => ['nullable',
                    function (string $attribute, $value, Closure $fail) use ($pcBudget) {
                        if (!$pcBudget) {
                            $fail("The selected Non-Project Class has no Budget for the year wherein lies the purchase date...");
                        }
                    },
                ],
            ]);
            $data = array_merge($data, ['purchase_class_budget' => $pcBudget->id]);
            unset($data['purchase_class']);
        } else {
            $data = array_merge($data, ['purchase_class_budget' => null]);
            unset($data['purchase_class']);
        }
        
        if (@$data_items['purchase_class_budget'])
        for ($u = 0; $u < count($data_items['purchase_class_budget']); $u++){
            if (!empty($data_items['purchase_class_budget'][$u])) {
                $pcBudget = PurchaseClassBudget::where('purchase_class_id', $data_items['purchase_class_budget'][$u])
                    ->whereHas('financialYear', function ($query) use ($data) {
                        $query->whereDate('start_date', '<=', date_for_database($data['date']))
                            ->whereDate('end_date', '>=', date_for_database($data['date']));
                    })
                    ->first();
                $request->validate([
                    'purchase_class' => ['nullable',
                        function (string $attribute, $value, Closure $fail) use ($pcBudget, $u) {
                            if (!is_null($value) && !$pcBudget) {
                                $fail("The selected Non-Project Class for item " . ($u+1) . " has no Budget for the year wherein lies the purchase date...");
                            }
                        },
                    ],
                ]);
                if ($pcBudget) {
                    $data_items['purchase_class_budget'][$u] = $pcBudget->id;
                }
            } else {
                $data_items['purchase_class_budget'][$u] = null;
            }
        }


        if (@$data['cu_invoice_no']){
            $refBackup = ['doc_ref_backup' => $data['doc_ref']];
            $data['doc_ref'] = $data['cu_invoice_no'];
            $data = array_merge($data, $refBackup);
        }

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['item_id'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Please use suggested options for input within a row!']);

        try {
            $purchase = $this->repository->update($purchase, compact('data', 'data_items'));
            $payment_params = "src_id={$purchase->id}&src_type=direct_purchase";
        } catch (\Exception $ex) {
            return errorHandler('Error Updating Expense '.$ex->getMessage(), $ex);
        }
        
        $msg = 'Expense Updated Successfully.';
        if ($purchase->invoiceNos) $msg .= ' Repost these invoices: ' . $purchase->invoiceNos;

        $msg .= ' <span class="pl-5 font-weight-bold h5"><a href="'. route('biller.billpayments.create', $payment_params) .'" target="_blank" class="btn btn-purple"><i class="fa fa-money"></i> Direct Payment</a></span>';

        return new RedirectResponse(route('biller.purchases.index'), ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Purchase $purchase)
    {
        try {
            $result = $this->repository->delete($purchase);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Expense', $th);
        }

        $msg = 'Expense deleted successfully';
        if (@$result['invoiceNos']) {
            $msg .= ' Repost these invoices: ' . $result['invoiceNos'];
        }

        return new RedirectResponse(route('biller.purchases.index'), ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Purchase $purchase)
    {
        return new ViewResponse('focus.purchases.view', compact('purchase'));
    }

    public function customer_load(Request $request)
    {
        $q = $request->get('id');
        $suppliers = array();
        if ($q == 'supplier') $suppliers = Supplier::select('id', 'suppliers.company AS name')->get();
            
        return response()->json($suppliers);
    }

    /**
     * Search Expense/Asset accounts 
     */
    public function accounts_select(Request $request)
    {
        $kw = $request->keyword;
        $account_types = ['fixed_asset', 'current_asset', 'other_asset', 'other_current_asset', 'expense', 'other_expense', 'cogs'];
        $detail_types = ['foreign_currency_loss', 'wip', 'inventory_asset'];
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system_rel', $account_types)->whereNotIn('system', $detail_types))
        ->where(fn($q) => $q->where('holder', 'LIKE', '%' . $kw . '%')->orWhere('number', 'LIKE', '%' . $kw . '%'))
        ->limit(6)
        ->get(['id', 'number', 'holder', 'account_type']);
        
        return response()->json($accounts);
    }
}
