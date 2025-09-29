<?php

namespace App\Http\Controllers\Focus\job_valuation;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\additional\Additional;
use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\items\PurchaseItem;
use App\Models\job_valuation\JobValuation;
use App\Models\job_valuation\JobValuationExp;
use App\Models\job_valuation\JobValuationItem;
use App\Models\product\ProductVariation;
use App\Models\project\BudgetItem;
use App\Models\project\ProjectQuote;
use App\Models\quote\Quote;
use App\Repositories\Focus\job_valuation\JobValuationRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class JobValuationsController extends Controller
{
    /**
     * variable to store the repository object
     * @var JobValuationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param JobValuationRepository $repository ;
     */
    public function __construct(JobValuationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::whereHas('jobValuations')->get(['id', 'company', 'name']);
        return new ViewResponse('focus.job_valuations.index', compact('customers'));
    }

    public function quote_index()
    {
        $customers = Customer::whereHas('quotes', function($q) {
            $q->whereHas('job_valuations')
            ->orWhere(function($q) {
                $q->where('status', 'approved')
                ->doesntHave('verified_products') 
                ->doesntHave('invoice_product')
                ->doesntHave('job_valuations');
            });
        })
        ->get(['id', 'company', 'name']);

        return new ViewResponse('focus.job_valuations.quote_index', compact('customers'));
    }

    /**
     * Fetch Quotes for Valuation
     */
    public function quotesDatatable(Request $request)
    {
        $q = Quote::query();
        $q->when(request('customer_id'), fn($q) => $q->where('customer_id', request('customer_id')));

        if (in_array(request('status'), ['complete', 'partial'])) {
            $q->whereHas('job_valuations', function ($q) {
                $q->selectRaw('quote_id, SUM(valued_subtotal) as valued_subtotal');
                $q->groupBy('quote_id');
                if (request('status') == 'complete') {
                    $q->havingRaw('valued_subtotal >= rose_quotes.subtotal');
                } 
                if (request('status') == 'partial') {
                    $q->havingRaw('valued_subtotal > 0');
                    $q->havingRaw('valued_subtotal < rose_quotes.subtotal');
                }
            });
        }
        elseif (request('status') == 'pending') {
            $q->where('status', 'approved')
            ->doesntHave('invoice_product')
            ->doesntHave('verified_products')
            ->doesntHave('job_valuations');
        } else {
            $q->where(function($q) {
                $q->whereHas('job_valuations')
                ->orWhere(function($q) {
                    $q->where('status', 'approved')
                    ->doesntHave('verified_products') 
                    ->doesntHave('invoice_product')
                    ->doesntHave('job_valuations');
                });
            });
        }
        $quotes = $q->with('job_valuations')->get();
        
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'project'], auth()->user()->ins);
        return Datatables::of($quotes)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($quote) {
                // add condition to disable exhausted valuations
                // 
                $boq_valuation = $quote->bom->boq->boq_valuations ?? collect();
                $amountValued = $quote->job_valuations->sum('valued_subtotal');
                $balance = $quote->subtotal - $amountValued;  
                if (($balance <= 0 && $quote->subtotal > 0) || $boq_valuation->isNotEmpty()) {
                    return '<input checked disabled type="checkbox" class="select-row" value="'. $quote->id .'">';
                }
                return '<input type="checkbox" class="select-row" value="'. $quote->id .'">';
            })
            ->addColumn('tid', function ($quote) use($prefixes) {
                $tid = gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                return '<a class="font-weight-bold" href="'. route('biller.quotes.show',$quote) .'">'. $tid . $quote->revision .'</a>';
            })
            ->addColumn('customer', function ($quote) {
                $customer = (string) @$quote->lead->client_name;
                if ($quote->customer) {
                    $customer = "{$quote->customer->company}";
                    if ($quote->branch) $customer .= " - {$quote->branch->name}";
                }
                return $customer;
            })
            ->addColumn('subtotal', function ($quote) {
                return numberFormat($quote->subtotal);
            })
            ->addColumn('perc_valuated', function ($quote) {
                $amountValued = $quote->job_valuations->sum('valued_subtotal');
                $percValued = round(div_num($amountValued,$quote->subtotal) * 100, 2);
                $percValued = $percValued > 100? 100 : ($percValued < 0? 0 : $percValued);
                return numberFormat($percValued);
            })
            ->addColumn('valuated', function ($quote) {
                $valuated = $quote->job_valuations->sum('valued_subtotal');
                return numberFormat($valuated);
            })
            ->addColumn('balance', function ($quote) {
                $valuated = $quote->job_valuations->sum('valued_subtotal');
                $balance = $quote->subtotal - $valuated; 
                if ($balance < 0) $balance = 0;
                return numberFormat($balance);
            })
            ->make(true);    
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = JobValuation::max('tid')+1;
        $customers = Customer::whereHas('invoices')->get(['id', 'company', 'name']);
        $additionals = Additional::get();

        $quote = Quote::find(request('quote_id')) ?: new Quote;
        $quote->orderItems = $this->getOrderItems($quote);
        $jobValuations = JobValuation::where('quote_id', $quote->id)->latest()->get();
        $users = Hrm::all();

        // valuation expenses
        $materialExpenses = $this->materialExpense($quote->id);
        $serviceExpenses = $this->serviceExpense($quote->id);

        return view('focus.job_valuations.create', compact('materialExpenses', 'serviceExpenses', 'tid','users', 'customers', 'quote', 'jobValuations', 'additionals'));
    }


    /**
     * Store a newly created resource in storage.
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    { 
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Job Valuation', $th);
        }
    
        return new RedirectResponse(route('biller.job_valuations.index'), ['flash_success' => 'Job Valuation Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  JobValuation $job_valuation
     * @return \Illuminate\Http\Response
     */
    public function edit(JobValuation $job_valuation)
    {
        $tid = $job_valuation->tid;
        $customers = Customer::whereHas('invoices')->get(['id', 'company', 'name']);
        $additionals = Additional::get();

        return view('focus.job_valuations.edit', compact('job_valuation', 'tid', 'customers', 'additionals'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  JobValuation $job_valuation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JobValuation $job_valuation)
    {
        try {
            $this->repository->update($job_valuation, $request->except('_token', '_method'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Job Valuation', $th);
        }

        return new RedirectResponse(route('biller.job_valuations.index'), ['flash_success' => 'Job Valuation Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  JobValuation $job_valuation
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobValuation $job_valuation)
    {
        try {
            $this->repository->delete($job_valuation);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Job Valuation', $th);
        }

        return new RedirectResponse(route('biller.job_valuations.index'), ['flash_success' => 'Job Valuation Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  JobValuation $job_valuation
     * @return \Illuminate\Http\Response
     */
    public function show(JobValuation $job_valuation)
    {
        return view('focus.job_valuations.view', compact('job_valuation'));
    }


    /** 
     * Job Valuation Order Items
     * **/
    public function getOrderItems($quote)
    {
        $latestValuedItems = JobValuationItem::whereHas('jobValuation', fn($q) => $q->where('quote_id', $quote->id))
            ->latest()
            ->get(['id', 'quote_item_id', 'product_name', 'productvar_id', 'product_valued_bal']);

        $orderItems = $quote->products()
            ->where('misc', '!=', 1)
            ->with(['productVariation' => fn($q) => $q->select('id', 'name', 'code')])
            ->get()
            ->map(function($v) use($latestValuedItems) {
                $v['valued_bal'] = round($v->product_qty * $v->product_subtotal,4);

                // balance from the previous valuation
                $valuedItem = $latestValuedItems
                    ->where('quote_item_id', $v['id'])
                    ->where('productvar_id', $v['product_id'])
                    ->first();
                if ($valuedItem) $v['valued_bal'] = $valuedItem->product_valued_bal;

                return $v;
            }); 
            
        return $orderItems;
    }

    /** 
     * Project Material Expense
     * */
    public function materialExpense($quoteId)
    {
        $projectQuote = ProjectQuote::where('quote_id', $quoteId)->first(['project_id']);
        $projectId = @$projectQuote->project_id;

        // previous valuations
        $prevJobValuationExp = JobValuationExp::where('quote_id', $quoteId)
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
        ->map(function($v) use($prevJobValuationExp) {
            $v->unit = $v->uom;
            $v->total_expense = $v->amount;
            $v->valued_bal = $v->amount;
            // reduce valued balance based on total valuated
            $key = "{$v->exp_category}_{$v->origin_id}";
            $valuation = @$prevJobValuationExp[$key];
            if ($valuation) $v->valued_bal -= $valuation->total_valuated;
            return $v;
        });

        return $expenses;
    }

    /** 
     * Project Service Expenses
     * */
    public function serviceExpense($quoteId)
    {
        $projectQuote = ProjectQuote::where('quote_id', $quoteId)->first(['project_id']);
        $projectId = @$projectQuote->project_id;

        // previous valuations
        $prevJobValuationExp = JobValuationExp::where('quote_id', $quoteId)
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
        ->map(function($v) use($prevJobValuationExp) {
            $v->valued_bal = $v->amount;
            // reduce valued balance based on total valuated
            $key = "{$v->exp_category}_{$v->origin_id}";
            $valuation = @$prevJobValuationExp[$key];
            if ($valuation) $v->valued_bal -= $valuation->total_valuated;
            return $v;
        });
        
        return $expenses;
    }
}
