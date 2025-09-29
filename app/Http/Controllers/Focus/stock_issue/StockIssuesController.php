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
namespace App\Http\Controllers\Focus\stock_issue;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\Company\Company;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\invoice\Invoice;
use App\Models\part\Part;
use App\Models\product\ProductVariation;
use App\Models\project\BudgetItem;
use App\Models\project\Project;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\quote\Quote;
use App\Models\sale_return\SaleReturnItem;
use App\Models\stock_adj\StockAdjItem;
use App\Models\stock_issue\StockIssue;
use App\Models\stock_issue\StockIssueItem;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\stock_issue\StockIssueRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

class StockIssuesController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockIssueRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockIssueRepository $repository ;
     */
    public function __construct(StockIssueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.stock_issues.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = StockIssue::max('tid')+1;
        $customers = Customer::whereHas('quotes')
            ->get(['id', 'company', 'name']);

        $employees = Hrm::where([
            'customer_id' => null,
            'supplier_id' => null,
            'client_vendor_id' => null,
            'client_user_id' => null,
        ])->get(['id', 'first_name', 'last_name']);

        // project status - 16 (continuing)
        $projects = Project::whereHas('misc', fn($q) => $q->where('name','!=','Completed'))
        ->with('quotes')
        ->orderBy('id','desc')
        ->get(['id', 'tid', 'name'])
        ->map(function($v) {
            $v['quote_ids'] = $v->quotes->pluck('id')->toArray();
            unset($v['quotes']);
            return $v;
        });

        $quotes = Quote::whereNotNull('approved_date')
            ->whereNotNull('approved_method')
            ->whereNotNull('approved_by')
            ->get(['id', 'notes', 'tid', 'bank_id', 'customer_id','quote_type']);
        
        // Consumables
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['office_supplies', 'consumable_asset_expense']))
            ->get(['id', 'number', 'holder', 'account_type']);

        $purchase_requisitions = PurchaseRequisition::where('status','approved')->get();
        $finished_goods = Part::all();

        return view('focus.stock_issues.create', compact('finished_goods','tid', 'customers', 'employees', 'projects', 'quotes', 'accounts','purchase_requisitions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token', 'issue_to_third_party'));
        } catch (\Exception $error) {dd($error);
            return errorHandler('Error Saving Stock Issue', $error);
        }

        return new RedirectResponse(route('biller.stock_issues.index'), ['flash_success' => 'Stock Issue Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StockIssue $stock_issue
     * @return \Illuminate\Http\Response
     */
    public function edit(StockIssue $stock_issue)
    {
        $customers = Customer::whereHas('quotes')->get(['id', 'company', 'name']);
        $employees = Hrm::where([
            'customer_id' => null,
            'supplier_id' => null,
            'client_vendor_id' => null,
            'client_user_id' => null,
        ])->get(['id', 'first_name', 'last_name']);
        // project status - continuing
        $projects = Project::whereHas('misc')
            ->with('quotes')
            ->get(['id', 'tid', 'name'])
            ->map(function($v) {
                $v['quote_ids'] = $v->quotes->pluck('id')->toArray();
                return $v;
            });

        $quotes = Quote::whereNotNull('approved_date')
            ->whereNotNull('approved_method')
            ->whereNotNull('approved_by')
            ->get(['id', 'notes', 'tid', 'bank_id', 'customer_id','quote_type']);

        // Consumables
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['office_supplies', 'consumable_asset_expense']))
            ->get(['id', 'number', 'holder', 'account_type']);

        $qt = Quote::find($stock_issue->quote_id);
        $budgetDetails = [];
        $budget = $qt ? $qt->budget : [];
        if ($budget) {
            foreach ($stock_issue->items as $item) {
                $budgetItem = BudgetItem::where('budget_id', $qt->budget->id)
                    ->where('product_id', $item['productvar_id'])
                    ->first();
                $bi = [$item['id'] => $budgetItem];
                $budgetDetails = array_merge($budgetDetails, $bi);
            }
        }

        $stock_issue['items'] = $stock_issue->items->map(function($v) {
            if ($v->productvar) $v['cost'] = fifoCost($v->productvar->id) ?: $v->productvar->purchase_price;
            $v['amount'] = $v->issue_qty * $v->cost;
            return $v;
        });

        $purchase_requisitions = PurchaseRequisition::where('status','approved')->get();
        $finished_goods = Part::all();

        return view('focus.stock_issues.edit', compact('finished_goods','stock_issue', 'customers', 'employees', 'projects', 'quotes', 'budgetDetails','accounts','purchase_requisitions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  StockIssue $stock_issue
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockIssue $stock_issue)
    {

        try {
            $this->repository->update($stock_issue, $request->except('_token', '_method', 'issue_to_third_party'));
        } catch (\Exception $exception) {
//            return errorHandler('Error Updating Stock Issue', $exception);

            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return new RedirectResponse(route('biller.stock_issues.index'), ['flash_success' => 'Stock Issue Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  StockIssue $stock_issue
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockIssue $stock_issue)
    {
        try {
            $this->repository->delete($stock_issue);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Stock Issue', $th);
        }

        return new RedirectResponse(route('biller.stock_issues.index'), ['flash_success' => 'Stock Issue Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  StockIssue $stock_issue
     * @return \Illuminate\Http\Response
     */
    public function show(StockIssue $stock_issue)
    {
        return view('focus.stock_issues.view', compact('stock_issue'));
    }

    /**
     * Quote/PI Stock Items
     */
    public function quote_pi_products(Request $request, $quoteId=null)
    {
        try {
            $quote = Quote::find($quoteId ?: request('quote_id'));
            $productvar_ids = $quote->products->pluck('product_id')->toArray();
            if ($quote->budget) $productvar_ids = $quote->budget->items->pluck('product_id')->toArray();

            $productvars = ProductVariation::whereIn('id', $productvar_ids)
            ->whereHas('product', fn($q) => $q->where('stock_type', '!=', 'service'))
            ->get()
            ->map(function ($v) use($quote){
                // set fifo cost
                $v->purchase_price = fifoCost($v->id) ?: $v->purchase_price;
                $v->unit = @$v->product->unit;
                unset($v->product);
                $v->budget_qty = $v->budget_qty =  BudgetItem::where('budget_id', @$quote->budget->id)
                    ->where('product_id', $v->id)
                    ->sum('new_qty');
                $v->requested_qty = $v->budget_qty;
                $v->requisition_id = 0;
                $v->booked_qty = 0;
                $v->issued_qty = BudgetItem::where('budget_id', @$quote->budget->id)
                ->where('product_id', $v->id)
                ->sum('issue_qty');
                // set warehouses
                $v->warehouses = Warehouse::whereHas('products', fn($q) => $q->where('name', 'LIKE', "%{$v->name}%"))
                    ->with(['products' => fn($q) => $q->where('name', 'LIKE', "%{$v->name}%")])
                    ->get()
                    ->map(function($wh) {
                        $wh->products_qty = $wh->products->sum('qty');
                        unset($wh->product);
                        return $wh;
                    });
                return $v;
            });
        
            return response()->json(compact('productvars'));
        } catch (\Exception $ex) {
            return [
                'request' => $request->toArray(),
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];
        }
    }

    /**
     * Fetch client invoices
     */
    public function select_invoices(Request $request)
    {
        $w = $request->search; 
        $invoices = Invoice::whereHas('currency', fn($q) => $q->where('rate', 1))
        ->where('customer_id', $request->customer_id)
        ->where(fn($q) => $q->where('notes', 'LIKE', "%{$w}%")->orWhere('tid', 'LIKE', "%{$w}%"))
        ->limit(6)
        ->get()
        ->map(function($v) {
            $v->notes = gen4tid('INV-', $v->tid) . ' ' . $v->notes;
            return $v;
        });
            
        return response()->json($invoices);
    }

    /**
     * Invoice inventory items
     */
    public function issue_invoice_items()
    {
        $productvars = collect();
        $invoice = Invoice::find(request('invoice_id'));
        if ($invoice && $invoice->products) {
            $quote_ids = $invoice->products->pluck('quote_id')->toArray();
            $quote_ids = array_unique($quote_ids);
            foreach ($invoice->products as $inv_product) {
                // verification invoice
                if ($inv_product->quote_id) {
                    $quote = $inv_product->quote;
                    if ($quote) {
                        foreach ($quote->verified_products as $verified_prod) {
                            $productvar = $verified_prod->product_variation;
                            if ($productvar) {
                                $productvar['purchase_price'] = fifoCost($productvar->id) ?: $productvar['purchase_price'];
                                $productvar['verified_item_id'] = $verified_prod->id;
                                $productvar['uom'] = @$productvar->product->unit->code;
                                $productvars->add($productvar);
                            }
                        }
                    }
                    if (count($quote_ids) == 1) break;
                } 
                // non-verification invoice (detached)
                elseif ($inv_product->product_id) {
                    $productvar = $inv_product->product_variation;
                    if ($productvar) {
                        $productvar['purchase_price'] = fifoCost($productvar->id) ?: $productvar['purchase_price'];
                        $productvar['verified_item_id'] = null;
                        $productvar['uom'] = @$productvar->product->unit->code;
                        $productvars->add($productvar);
                    }
                }
            }
        }
        $productvars = $productvars->map(function($v) {
            $v->warehouses = Warehouse::whereHas('products', fn($q) => $q->where('name', 'LIKE', "%{$v->name}%"))
                ->with(['products' => fn($q) => $q->where('name', 'LIKE', "%{$v->name}%")])
                ->get()
                ->map(function($wh) {
                    $wh->products_qty = $wh->products->sum('qty');
                    unset($wh->products);
                    return $wh;
                });
            return $v;
        });

        return response()->json($productvars);
    }

    public function get_issuance_report(){
        $products = ProductVariation::all();
        return view('focus.stock_issues.issuance_report', compact('products'));
    }

    public function products_movement_items(Request $request)
    {
        $q = StockIssueItem::query();

        $q->when(request('product_id'), fn($q) => $q->where('productvar_id', request('product_id')));

        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereHas('stock_issue', function ($q) {
                $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
            });
        });

        $results = $q->get();

        $grouped = $results->groupBy('productvar_id')->map(function ($items) {
            $firstItem = $items->first();

            $stockAdjItems = StockAdjItem::where('productvar_id', $firstItem->productvar_id);
            $stockAdjItems->when(request('start_date') && request('end_date'), function ($q) {
                $q->whereHas('stock_adj', function ($q) {
                    $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
                });
            });
            $stockAdjItems->get();
            $sale_return_item = SaleReturnItem::where('productvar_id', $firstItem->productvar_id);
            $sale_return_item->when(request('start_date') && request('end_date'), function ($q) {
                $q->whereHas('sale_return', function ($q) {
                    $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
                });
            });
            $sale_return_item->get();

            $stock_adj_qty = $stockAdjItems->sum('qty_diff') ?? 0;
            $sale_return_qty = $sale_return_item->sum('return_qty') ?? 0;
            return (object) [
                'product_name' => $firstItem->productvar ? $firstItem->productvar->name : '',
                'code' => $firstItem->productvar ? $firstItem->productvar->code : '',
                'unit' => $firstItem->productvar ? @$firstItem->productvar->product->unit->code : '',
                'date' => '',
                'issue_qty' => $items->sum('issue_qty') ? $items->sum('issue_qty'): 0,
                'return_qty' => $sale_return_qty,
                'stock_adj_qty' => $stock_adj_qty,
                'warehouse' => @$firstItem->productvar->warehouse->title ?? ''
            ];
        });
        return DataTables::of($grouped)
        ->escapeColumns(['id'])
        ->addIndexColumn()    
        ->editColumn('date', function ($item) {
            return $item->date;
        })
        ->addColumn('name', function ($item) {
            return @$item->product_name;
        })
        ->addColumn('code', function ($item) {
            return @$item->code;
        })
        ->addColumn('unit', function ($item) {
            return @$item->unit;
        })
        ->addColumn('issue_qty', function ($item) {
            return numberFormat($item->issue_qty);
        })
        ->addColumn('return_qty', function ($item) {
            return numberFormat($item->return_qty);
        })
        ->addColumn('stock_adj_qty', function ($item) {
            return numberFormat($item->stock_adj_qty);
        })
        ->addColumn('warehouse', function ($item) {
            return  @$item->warehouse;
        })
        ->make(true);
    }

    public function print_stock_movement()
    {
        $q = StockIssueItem::query();

        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereHas('stock_issue', function ($q) {
                $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
            });
        });

        $results = $q->get();

        $products = $results->groupBy('productvar_id')->map(function ($items) {
            $firstItem = $items->first();
           if($firstItem->productvar){
                return (object) [
                    'product_name' => $firstItem->productvar ? $firstItem->productvar->name : '',
                    'code' => $firstItem->productvar ? $firstItem->productvar->code : '',
                    'unit' => $firstItem->productvar ? @$firstItem->productvar->product->unit->code : '',
                    'date' => '',
                    'issue_qty' => $items->sum('issue_qty') ? $items->sum('issue_qty'): 0,
                    'amount' => $items->sum('amount') ? $items->sum('amount'): 0,
                    'return_qty' => 0,
                    'warehouse' => @$firstItem->productvar->warehouse->title ?? ''
                ];
           }
        });

        $start_date = request('start_date');
        $end_date = request('end_date');
        $company = Company::find(auth()->user()->ins);

        $params = compact('products', 'start_date', 'end_date','company');
        $html = view('focus.report.pdf.print_stock_movement', $params)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        return Response::stream($pdf->Output('print_stock_movement' . '.pdf', 'I'), 200, $headers);
    }


    public function updateApproval($id, Request $request) {


        if (!access()->allow('approve-issuance')) return redirect()->back();

        $stock_issue = StockIssue::find($id);
        $data = $request->only(['status','approval_note']);
        $data['approved_by'] = auth()->user()->id;
        try {
            $this->repository->approve_stock_issue($stock_issue, compact('data'));
        } catch (\Throwable $th) {
           return errorHandler("Error updating stock issue status ".$th->getMessage(), $th);
        }

        return redirect()->route('biller.stock_issues.show', $id)->with('flash_success', 'Stock Issue Status Updated successfully.');
    }

}
