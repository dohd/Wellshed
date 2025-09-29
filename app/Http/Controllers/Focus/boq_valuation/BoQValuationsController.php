<?php

namespace App\Http\Controllers\Focus\boq_valuation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\additional\Additional;
use App\Models\boq\BoQ;
use App\Models\boq\BoQSheet;
use App\Models\boq\BoQWorkSheet;
use App\Models\boq_valuation\BoQValuation;
use App\Models\boq_valuation\BoQValuationExp;
use App\Models\boq_valuation\BoQValuationItem;
use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\items\PurchaseItem;
use App\Models\project\BudgetItem;
use App\Models\project\Project;
use App\Models\project\ProjectQuote;
use App\Repositories\Focus\boq_valuation\BoQValuationRepository;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

/**
 * BoQValuationsController
 */
class BoQValuationsController extends Controller
{
    /**
     * variable to store the repository object
     * @var BoQValuationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BoQValuationRepository $repository ;
     */
    public function __construct(BoQValuationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        $customers = Customer::whereHas('boqValuations')->get(['id', 'company', 'name']);
        return new ViewResponse('focus.boq_valuations.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\CreateResponse
     */
    public function create()
    {
        $tid = BoQValuation::max('tid')+1;
        $customers = Customer::whereHas('invoices')->get(['id', 'company', 'name']);
        $additionals = Additional::get();

        $boq = BoQ::find(request('boq_id')) ?: new BoQ;
        $boq->boqItems = $this->getBoQItems($boq);
        $BoQValuations = BoQValuation::where('boq_id', $boq->id)->latest()->get();
        $users = Hrm::all();
        $boq_worksheet = BoQWorkSheet::where('boq_id',request('boq_id'))->get(['boq_sheet_id'])->toArray();
        $boq_sheets = BoQSheet::whereIn('id', $boq_worksheet)->get();
        // dd($boq_sheets, $boq_worksheet);

        // valuation expenses
        $materialExpenses = $this->materialExpense($boq->id);
        $serviceExpenses = $this->serviceExpense($boq->id);

        return view('focus.boq_valuations.create', compact('materialExpenses', 'serviceExpenses', 'tid','boq_sheets','customers','additionals','boq','users','BoQValuations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDepartmentRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {dd($th);
            return errorHandler('Error Creating BoQ Valuation', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.boq_valuations.index'), ['flash_success' => 'BoQ Valuation Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\department\Department $boq_valuation
     * @param EditDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\EditResponse
     */
    public function edit(BoQValuation $boq_valuation)
    {
        return view('focus.boq_valuations.edit', compact('boq_valuation'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDepartmentRequestNamespace $request
     * @param App\Models\department\Department $boq_valuation
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, BoQValuation $boq_valuation)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($boq_valuation, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.boq_valuations.index'), ['flash_success' => 'BoQ Valuation Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $boq_valuation
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(BoQValuation $boq_valuation)
    {
        try {
            $this->repository->delete($boq_valuation);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting BoQ Valuation', $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.boq_valuations.index'), ['flash_success' => 'BoQ Valuation Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $boq_valuation
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(BoQValuation $boq_valuation)
    {

        //returning with successfull message
        return new ViewResponse('focus.boq_valuations.view', compact('boq_valuation'));
    }

    public function boq_index()
    {
        $ins = auth()->user()->ins;

        $customers = Customer::withoutGlobalScopes()
            ->whereRaw('rose_customers.ins = ?', [$ins]) // qualify explicitly
            ->whereNotNull('currency_id')
            ->whereHas('boqs', function ($q) use ($ins) {
                $q->withoutGlobalScopes()
                ->whereRaw('rose_boqs.ins = ?', [$ins]) // qualify BoQ
                ->where(function ($q) use ($ins) {
                    $q->whereHas('boq_valuations', function ($q) use ($ins) {
                        $q->withoutGlobalScopes()
                            ->whereRaw('rose_boq_valuations.ins = ?', [$ins]); // qualify Valuation
                    })
                    ->orWhereDoesntHave('boq_valuations', function ($q) use ($ins) {
                        $q->withoutGlobalScopes()
                            ->whereRaw('rose_boq_valuations.ins = ?', [$ins]); // qualify Valuation
                    });
                });
            })
            ->get(['id', 'company', 'name']);

    

        return new ViewResponse('focus.boq_valuations.boq_index', compact('customers'));
    }

    /**
     * Fetch boqs for Valuation
     */
    public function boqsDatatable(Request $request)
    {
        $q = BoQ::query()->where('lead_id','>',0)->whereHas('lead.quotes');
        $q->when(request('customer_id'), 
        fn($q) => $q->whereHas('lead', fn($q) => $q->where('client_id', request('customer_id'))));

        if (in_array(request('status'), ['complete', 'partial'])) {
            $q->whereHas('boq_valuations', function ($q) {
                $q->selectRaw('boq_id, SUM(valued_subtotal) as valued_subtotal');
                $q->groupBy('boq_id');
                if (request('status') == 'complete') {
                    $q->havingRaw('valued_subtotal >= rose_boqs.total_boq_amount');
                } 
                if (request('status') == 'partial') {
                    $q->havingRaw('valued_subtotal > 0');
                    $q->havingRaw('valued_subtotal < rose_boqs.total_boq_amount');
                }
            });
        }
        elseif (request('status') == 'pending') {
            $q
            // ->doesntHave('invoice_item')
            ->doesntHave('boq_valuations');
        } else {
            $q->where(function($q) {
                $q->whereHas('boq_valuations')
                ->orWhere(function($q) {
                    // $q->doesntHave('invoice_item')
                    $q->doesntHave('boq_valuations');
                });
            });
        }
        $boqs = $q->with('boq_valuations')->get();

        return DataTables::of($boqs)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($boq) {
                // add condition to disable exhausted valuations
                // 
                $amountValued = $boq->boq_valuations->sum('valued_subtotal');
                $balance = $boq->total_boq_amount - $amountValued;  
                if ($balance <= 0 && $boq->total_boq_amount > 0) {
                    return '<input checked disabled type="checkbox" class="select-row" value="'. $boq->id .'">';
                }
                return '<input type="checkbox" class="select-row" value="'. $boq->id .'">';
            })
            ->addColumn('tid', function ($boq) {
                $tid = gen4tid("BoQ-", $boq->tid);
                return '<a class="font-weight-bold" href="'. route('biller.boqs.show',$boq) .'">'. $tid. '</a>';
            })
            ->addColumn('customer', function ($boq) {
                $lead = $boq->lead;
            
                // Default fallback: use the lead's client_name
                $customer = (string) optional($lead)->client_name;
            
                if ($lead && $lead->customer) {
                    $company = $lead->customer->company ?? '';
                    $branch = optional($lead->branch)->name;
            
                    $customer = $company;
                    if ($branch) {
                        $customer .= " - {$branch}";
                    }
                }
            
                return $customer;
            })
            ->addColumn('subtotal', function ($boq) {
                return numberFormat($boq->total_boq_amount);
            })
            ->addColumn('perc_valuated', function ($boq) {
                $amountValued = $boq->boq_valuations->sum('valued_subtotal');
                $percValued = round(div_num($amountValued,$boq->total_boq_amount) * 100, 2);
                $percValued = $percValued > 100? 100 : ($percValued < 0? 0 : $percValued);
                return numberFormat($percValued);
            })
            ->addColumn('valuated', function ($boq) {
                $valuated = $boq->boq_valuations->sum('valued_subtotal');
                return numberFormat($valuated);
            })
            ->addColumn('balance', function ($boq) {
                $valuated = $boq->boq_valuations->sum('valued_subtotal');
                $balance = $boq->total_boq_amount - $valuated; 
                if ($balance < 0) $balance = 0;
                return numberFormat($balance);
            })
            ->make(true);    
    }

    public function getBoQItems($boq)
    {
        $latestValuedItems = BoQValuationItem::whereHas('boq_valuation', fn($q) => $q->where('boq_id', $boq->id))
            ->latest()
            ->get(['id', 'boq_item_id', 'product_name', 'product_valued_bal']);

        $orderItems = $boq->items()
            ->where('misc', '!=', 1)
            ->where('type', 'product')
            // ->with(['productVariation' => fn($q) => $q->select('id', 'name', 'code')])
            ->get()
            ->map(function($v) use($latestValuedItems) {
                $v['valued_bal'] = round($v->new_qty * $v->boq_rate,4);

                // balance from the previous valuation
                $valuedItem = $latestValuedItems
                    ->where('boq_item_id', $v['id'])
                    // ->where('productvar_id', $v['product_id'])
                    ->first();
                if ($valuedItem) $v['valued_bal'] = $valuedItem->product_valued_bal;

                return $v;
            }); 
            
        return $orderItems;
    }

    /** 
     * Project Material Expense
     * */
    public function materialExpense($boqId)
    {
        $project = Project::whereHas('quotes', function($q) use($boqId) {
            $q->whereHas('lead', function($q) use($boqId) {
                $q->whereHas('boqs', fn($q) => $q->where('boqs.id', $boqId));
            });
        })
        ->first(['id']);
        $projectId = @$project->id;

        // previous valuations
        $prevBoqValuationExp = BoQValuationExp::where('boq_id', $boqId)
            ->whereNotIn('category', ['dir_purchase_service', 'labour_service'])
            ->selectRaw('origin_id, category, SUM(total_valuated) total_valuated')
            ->groupBy('origin_id', 'category')
            ->get()
            ->keyBy(fn($v) => "{$v->category}_{$v->origin_id}");

        // actual expenses
        request()->merge(['project_id' => $projectId]);
        $repository = new \App\Repositories\Focus\project\ProjectRepository;
        $controller = new \App\Http\Controllers\Focus\project\ExpensesTableController($repository);
        $expenses = $controller->get_expenses()
        ->filter(fn($v) => !in_array($v->exp_category, ['dir_purchase_service', 'labour_service']))
        ->map(function($v) use($prevBoqValuationExp, $projectId) {
            $v->project_id = $projectId;
            $v->unit = $v->uom;
            $v->total_expense = $v->amount;
            $v->valued_bal = $v->amount;
            // reduce valued balance based on total valuated
            $key = "{$v->exp_category}_{$v->origin_id}";
            $valuation = @$prevBoqValuationExp[$key];
            if ($valuation) $v->valued_bal -= $valuation->total_valuated;
            return $v;
        });

        return $expenses;
    }

    /** 
     * Project Service Expenses
     * */
    public function serviceExpense($boqId)
    {
        $project = Project::whereHas('quotes', function($q) use($boqId) {
            $q->whereHas('lead', function($q) use($boqId) {
                $q->whereHas('boqs', fn($q) => $q->where('boqs.id', $boqId));
            });
        })
        ->first(['id']);
        $projectId = @$project->id;

        // previous valuations
        $prevBoqValuationExp = BoQValuationExp::where('boq_id', $boqId)
            ->whereIn('category', ['dir_purchase_service', 'labour_service'])
            ->selectRaw('origin_id, category, SUM(total_valuated) total_valuated')
            ->groupBy('origin_id', 'category')
            ->get()
            ->keyBy(fn($v) => "{$v->category}_{$v->origin_id}");
            
        // actual expenses
        request()->merge(['project_id' => $projectId]);
        $repository = new \App\Repositories\Focus\project\ProjectRepository;
        $controller = new \App\Http\Controllers\Focus\project\ExpensesTableController($repository);
        $expenses = $controller->get_expenses()
        ->filter(fn($v) => in_array($v->exp_category, ['dir_purchase_service', 'labour_service']))
        ->map(function($v) use($prevBoqValuationExp, $projectId) {
            $v->project_id = $projectId;
            $v->valued_bal = $v->amount;
            // reduce valued balance based on total valuated
            $key = "{$v->exp_category}_{$v->origin_id}";
            $valuation = @$prevBoqValuationExp[$key];
            if ($valuation) $v->valued_bal -= $valuation->total_valuated;
            return $v;
        });
        
        return $expenses;
    }

    /** 
     * Boq Valuation Service Expense
     * */
    public function serviceExpense_()
    {
        $boqId = request('boq_id');
        $boq = BoQ::find($boqId);
        if (!$boq || !$boq->lead || !$boq->lead->quotes->isNotEmpty()) {
            return response()->json([]);
        }
    
        $quoteId = $boq->lead->quotes()->first()->id;
        $projectId = ProjectQuote::where('quote_id', $quoteId)->value('project_id');

        // expenses without milestones
        $expsTotals = BoQValuationExp::where('boq_id', $boqId)
            ->whereNotNull('expitem_id')
            ->whereNull('budget_line_id')
            ->selectRaw('expitem_id, SUM(total_valuated) total_valuated')
            ->groupBy('expitem_id')
            ->get();
        $expenses = PurchaseItem::where([
            'type' => 'Expense',
            'itemproject_id' => $projectId,
        ])
        ->whereNull('budget_line_id')
        ->selectRaw('id, description, uom, qty, rate, itemproject_id project_id, budget_line_id')
        ->get()
        ->map(function($item) use($expsTotals) {
            $item['exp_item_id'] = $item['id'];
            $item['amount'] = round($item->qty * $item->rate,4);
            $item['valued_bal'] = $item->amount;

            // reduce valued balance
            $expsTotal = $expsTotals->where('expitem_id', $item['id'])->first();
            if ($expsTotal) $item['valued_bal'] -= $expsTotal->total_valuated;

            return $item;
        });

        // expenses with milestones
        $expsTotals2 = BoQValuationExp::where('boq_id', $boqId)
            ->whereNotNull('expitem_id')
            ->whereNotNull('budget_line_id')
            ->selectRaw('expitem_id, SUM(total_valuated) total_valuated')
            ->groupBy('expitem_id')
            ->get();
        $milestoneExpenses = PurchaseItem::where([
            'type' => 'Expense',
            'itemproject_id' => $projectId,
        ])
        ->whereHas('budgetLine')
        ->selectRaw('id, description, uom, qty, rate, itemproject_id project_id, budget_line_id')
        ->with('budgetLine')
        ->get()
        ->map(function($item) use($expsTotals2) {
            $item['exp_item_id'] = $item['id'];
            $item['amount'] = round($item->qty * $item->rate,4);
            $item['valued_bal'] = $item->amount;
            $item['budget_line_name'] = $item->budgetLine->name;

            // reduce valued balance
            $expsTotal = $expsTotals2->where('expitem_id', $item['id'])->first();
            if ($expsTotal) $item['valued_bal'] -= $expsTotal->total_valuated;

            return $item;
        });


        // casual wages expenses
        $casualWageTotals = BoQValuationExp::where('boq_id', $boqId)
            ->whereNotNull('casual_remun_id')
            ->selectRaw('casual_remun_id, SUM(total_valuated) total_valuated')
            ->groupBy('casual_remun_id')
            ->get();
        $casualWages = CasualLabourersRemuneration::whereHas('bill')
        ->whereHas('labourAllocations', fn($q) =>  $q->where('project_id', $projectId))
        ->get()
        ->map(function($item) use($casualWageTotals, $projectId) {
            $newItem = new CasualLabourersRemuneration;
            $newItem->forceFill([
                'id' => $item->clr_number,
                'casual_remun_id' => $item->clr_number,
                'project_id' => $projectId,
                'description' => $item->title,
                'uom' => 'Lot',
                'qty' => 1,
                'amount' => +$item->total_amount,
                'valued_bal' => +$item->total_amount,
            ]);

            // reduce valued balance
            $casualWageTotal = $casualWageTotals->where('casual_remun_id', $newItem->id)->first();
            if ($casualWageTotal) $newItem['valued_bal'] -= $casualWageTotal->total_valuated;

            return $newItem;
        });

        $expenses = collect()
        ->merge($expenses)
        ->merge($milestoneExpenses)
        ->merge($casualWages);

        return response()->json($expenses);
    }

    /** 
     * Job Valuation Material Expense
     * */
    public function materialExpense_()
    {
        $boqId = request('boq_id');
        $boq = BoQ::find($boqId);
        if (!$boq || !$boq->lead || !$boq->lead->quotes->isNotEmpty()) {
            return response()->json([]);
        }
    
        $quoteId = $boq->lead->quotes()->first()->id;
        $projectId = ProjectQuote::where('quote_id', $quoteId)->value('project_id');

        // non-milestone
        $expTotals = BoQValuationExp::where('boq_id', $boqId)
            ->whereNotNull('budget_item_id')
            ->whereNull('expitem_id')
            ->selectRaw('budget_item_id, SUM(total_valuated) total_valuated')
            ->groupBy('budget_item_id')
            ->get();
        $materials = BudgetItem::where('misc', 1)
            ->whereHas('budget', fn($q) => $q->where('quote_id', $quoteId))
            ->whereHas('productVariation')
            ->with([
                'productVariation.purchase_items' => function($q) use($projectId) {
                    $q->where('itemproject_id', $projectId)->doesntHave('budgetLine');
                },
                'productVariation.stockIssueItems' => function($q) use($projectId) {
                    $q->whereHas('stock_issue', fn($q) => $q->where('project_id', $projectId));
                },
                'productVariation.purchaseorder_items.grn_items' => fn($q) => $q->where('itemproject_id', $projectId),
            ])
            ->get(['id', 'budget_id', 'product_id', 'product_name', 'unit'])
            ->map(function($item) use($quoteId, $projectId, $expTotals) {
                $item['quote_id'] = $quoteId;
                $item['project_id'] = $projectId;
                $item['budget_line_id'] = null;
                $item['budget_line_name']= '';
                $item['product_code'] = $item->productVariation->code;

                $purchaseAmount = $item->productVariation->purchase_items
                    ->sum(fn($v) => $v->rate * $v->qty);
                $grnAmount = $item->productVariation->purchaseorder_items
                    ->map(fn($orderItem) =>  $orderItem->grn_items->sum(fn($v) => $v->rate * $v->qty))
                    ->sum(fn($v) => $v);            
                $issueAmount = $item->productVariation->stockIssueItems
                    ->sum(fn($v) => $v->cost * $v->issue_qty);


                $item['purchase_amount'] = $purchaseAmount;
                $item['grn_amount'] = $grnAmount;
                $item['stockissued_amount'] = $issueAmount;
                $item['total_expense'] = $purchaseAmount + $grnAmount + $issueAmount;
                $item['valued_bal'] = $item->total_expense;

                // reduce valued balance
                $expTotal = $expTotals->where('budget_item_id', $item['id'])->first();
                if ($expTotal) $item['valued_bal'] -= $expTotal->total_valuated;
                
                return $item;
            })
            ->filter(fn($v) => $v['valued_bal'] > 0);
        $materials = $materials->makeHidden('productVariation');

        // milestone
        $expTotals2 = BoQValuationExp::where('boq_id', $boqId)
            ->whereNotNull('budget_item_id')
            ->whereNotNull('budget_line_id') // milestone-id
            ->whereNull('expitem_id')
            ->selectRaw('budget_item_id, budget_line_id, SUM(total_valuated) total_valuated')
            ->groupBy('budget_item_id', 'budget_line_id')
            ->get();
        $milestoneMaterials = BudgetItem::where('misc', 1)
            ->whereHas('budget', fn($q) => $q->where('quote_id', $quoteId))
            ->whereHas('productVariation.purchase_item.budgetLine')
            ->with([
                'productVariation.purchase_item' => function($q) use($projectId) {
                    $q->where('itemproject_id', $projectId)->whereHas('budgetLine');
                },
            ])
            ->get(['id', 'budget_id', 'product_id', 'product_name', 'unit'])
            ->map(function($item) use($quoteId, $projectId, $expTotals2) {
                $item['quote_id'] = $quoteId;
                $item['project_id'] = $projectId;
                $item['product_code'] = $item->productVariation->code;
                $item['grn_amount'] = 0;
                $item['stockissued_amount'] = 0;

                $purchaseItem = $item->productVariation->purchase_item;
                $purchaseAmount = round($purchaseItem->rate * $purchaseItem->qty, 4);

                $item['budget_line_id'] = $purchaseItem->budget_line_id;
                $item['budget_line_name'] = $purchaseItem->budgetLine->name;
                $item['budget_purchase_amount'] = $purchaseAmount;
                $item['total_expense'] = $purchaseAmount;
                $item['valued_bal'] = $item->total_expense;

                // reduce valued balance
                $expTotal = $expTotals2->where('budget_item_id', $item['id'])
                    ->where('budget_line_id', $item['budget_line_id'])
                    ->first();
                if ($expTotal) $item['valued_bal'] -= $expTotal->total_valuated;
                
                return $item;
            })
            ->filter(fn($v) => $v['valued_bal'] > 0);
        $milestoneMaterials = $milestoneMaterials->makeHidden('productVariation');

        $materials = collect()->merge($materials)->merge($milestoneMaterials);
        // dd($materials);

        return response()->json($materials);
    }
}
