<?php

namespace App\Http\Controllers\Focus\dailyBusinessMetrics;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\Access\Permission\Permission;
use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\banktransfer\Banktransfer;
use App\Models\billpayment\Billpayment;
use App\Models\boq\BoQ;
use App\Models\boq_valuation\BoQValuation;
use App\Models\branch\Branch;
use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\casual_labourer_remuneration\CLRAllocation;
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\customer_complain\CustomerComplain;
use App\Models\dailyBusinessMetric\DailyBusinessMetric;
use App\Models\dailyBusinessMetric\DbmDisplayOption;
use App\Models\department\Department;
use App\Models\documentManager\DocumentManager;
use App\Models\employeeDailyLog\EmployeeDailyLog;
use App\Models\employeeDailyLog\EmployeeTasks;
use App\Models\environmentalTracking\EnvironmentalTracking;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\health_and_safety\HealthAndSafetyTracking;
use App\Models\hrm\Hrm;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\job_valuation\JobValuation;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\lead\AgentLead;
use App\Models\lead\Lead;
use App\Models\lead\LeadSource;
use App\Models\lead\OmniChat;
use App\Models\leave\Leave;
use App\Models\leave_category\LeaveCategory;
use App\Models\misc\Misc;
use App\Models\product\ProductVariation;
use App\Models\project\Budget;
use App\Models\project\Project;
use App\Models\project\ProjectQuote;
use App\Models\projectstock\Projectstock;
use App\Models\purchase\Purchase;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\quality_tracking\QualityTracking;
use App\Models\quote\Quote;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsCallback;
use App\Models\sms_response\SmsResponse;
use App\Models\stock_issue\StockIssue;
use App\Models\supplier\Supplier;
use App\Models\tenant\GraceDaysRequest;
use App\Models\tenant\Tenant;
use App\Models\tenant\TenantActivation;
use App\Models\tenant\TenantDeactivation;
use App\Models\tenant\TenantLoyaltyPointsRedemption;
use App\Models\tender\Tender;
use App\Repositories\DbmPayloadTrait;
use App\Models\transaction\Transaction;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class DailyBusinessMetricController extends Controller
{
    use DbmPayloadTrait;


    public $pheight;

    // pdf print request headers
    protected $headers = [
        "Content-type" => "application/pdf",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];


    // income, expense, profit
    protected $income = 0;
    protected $expense = 0;
    protected $profit = 0;
    protected $total_profit = 0;


    public function __construct()
    {
        $this->pheight = 0;
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \DateMalformedStringException
     */
    public function index($dbmUuid)
    {
        try {
            // IMPORTANT: if dbm_uuid is NOT the PK, use where+firstOrFail
            $dbm = DailyBusinessMetric::withoutGlobalScopes()->where('dbm_uuid', $dbmUuid)->firstOrFail();
            $dateToday = new DateTime($dbm->date);

            $payload = $this->buildDbmPayload($dbm, $dateToday);
            $pdf     = $this->renderDbmPdf($dbm, $dateToday, $payload);

            $filename = 'Comprehensive-Operational-Summary-'. $dbm->dbm_uuid .'.pdf';

            return response($pdf, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage(),
                'code'    => $ex->getCode(),
                'file'    => $ex->getFile(),
                'line'    => $ex->getLine(),
            ], 500);
        }
    }


    /**
     * @throws \DateMalformedStringException
     * @throws \DateMalformedIntervalStringException
     */
    public function get7DaysLabourMetrics($ins, $dbmDate){

        $hoursTotals = array_fill(0, 7, 0);
        $labourDates = array_fill(0, 7, 'N/A');
        for ($i = 1; $i <= 7; $i++){

            $date = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');

            $labourAllocations = LabourAllocation::withoutGlobalScopes()
                ->where('ins', $ins)
                ->where('date', $date)
                ->pluck('hrs');
            foreach ($labourAllocations as $alloc){
                $hoursTotals[$i-1] += $alloc;
            }

            $labourDates[$i-1] = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $labourDates = array_reverse($labourDates);
        $hoursTotals = array_reverse($hoursTotals);


        $startDate = (new DateTime($dbmDate))->sub(new DateInterval('P7D'))->format('jS F');
        $endDate = (new DateTime($dbmDate))->sub(new DateInterval('P1D'))->format('jS F');


        $chartTitle = 'Daily Labour Hours from ' . $startDate . ' to ' . $endDate . ', ' . (new DateTime($dbmDate))->format('Y');

        return compact('hoursTotals', 'labourDates', 'chartTitle');
    }


    public function get7DaysSalesExpensesMetrics($ins, $dbmDate){

        //SALES DATA
        $salesTotals = array_fill(0, 7, 0);
        $salesDates = array_fill(0, 7, 'N/A');

        for ($i = 1; $i <= 7; $i++){

            $date = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');

            $salesValues = Invoice::withoutGlobalScopes()
                ->where('ins', $ins)
                ->where('invoicedate', $date)
                ->pluck('total');
            foreach ($salesValues as $sale){
                $salesTotals[$i-1] += $sale;
            }

            $salesDates[$i-1] = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $salesDates = array_reverse($salesDates);
        $salesTotals = array_reverse($salesTotals);


        //EXPENSES DATA
        $expensesTotals = array_fill(0, 7, 0);
        $expensesDates = array_fill(0, 7, 'N/A');

        for ($i = 1; $i <= 7; $i++){

            $date = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');

            $expensesValues = Purchase::withoutGlobalScopes()
                ->where('ins', $ins)
                ->where('date', $date)
                ->pluck('grandttl');
            foreach ($expensesValues as $expense){
                $expensesTotals[$i-1] += $expense;
            }

            $expensesDates[$i-1] = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $expensesDates = array_reverse($expensesDates);
        $expensesTotals = array_reverse($expensesTotals);


        $startDate = (new DateTime($dbmDate))->sub(new DateInterval('P7D'))->format('jS F');
        $endDate = (new DateTime($dbmDate))->sub(new DateInterval('P1D'))->format('jS F');

        $chartTitle = 'Daily Sales and Expenses from ' . $startDate . ' to ' . $endDate . ', ' . (new DateTime($dbmDate))->format('Y');

        return compact('salesTotals', 'salesDates', 'expensesTotals', 'expensesDates', 'chartTitle');
    }


    public function edlDashboard($ins, $dbmDate){

        $today = (new DateTime($dbmDate))->format('Y-m-d');
        //Logs Filled Today
        $filledToday = EmployeeDailyLog::withoutGlobalScopes()
            ->where('ins', $ins)
            ->where('date', $today)
            ->get()
            ->count();

        //Logs not Filled Today
        $employees = Hrm::withoutGlobalScopes()
            ->where('ins', $ins)
            ->get();
        $noOfLoggers = 0;
        foreach ($employees as $emp){

            $user = User::where('id', $emp['id'])->first();

            $perm = Permission::where('name', 'create-daily-logs')->first();
            $permUser = PermissionUser::where('user_id', $user->id)->where('permission_id', $perm->id)->first();


            if ($permUser){
                $noOfLoggers++;
            }

        }
        $notFilledToday = $noOfLoggers - $filledToday;

        //Hours Logged today
        $tasksLoggedToday = 0;
        $hoursLoggedToday = 0;
        $todayLogs = EmployeeDailyLog::withoutGlobalScopes()
            ->where('ins', $ins)
            ->where('date', $today)
            ->get();
        foreach ($todayLogs as $log){

            $edlTasks = EmployeeTasks::withoutGlobalScopes()
                ->where('ins', $ins)
                ->where('edl_number', $log['edl_number'])
                ->get();

            $tasksLoggedToday += $edlTasks->count();

            foreach ($edlTasks as $task){

                $hoursLoggedToday += $task['hours'];
            }
        }

        $todayLogs = EmployeeDailyLog::withoutGlobalScopes()
            ->where('ins', $ins)
            ->where('date', $today)
            ->get();
        $todayUnreviewedLogs = 0;

        foreach ($todayLogs as $log){

            if (empty($log['rating']) && empty($log['remarks'])){
                $todayUnreviewedLogs++;
            }
        }


        return compact('filledToday', 'notFilledToday', 'tasksLoggedToday', 'hoursLoggedToday', 'todayUnreviewedLogs');
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function setOptions()
    {

        if (!access()->allow('comprehensive-operational-summary-report-recipient') && !access()->allow('comprehensive-operational-summary-report-manager'))
            return new RedirectResponse(route('biller.dashboard'), ['flash_error' => "You Don't Have the Rights for that page..."]);

        $dbmDisplayOptions = DbmDisplayOption::where('ins', Auth::user()->ins)->first() ?? new DbmDisplayOption();

        $options = json_decode($dbmDisplayOptions->options);

        $dbm = DailyBusinessMetric::where('ins', Auth::user()->ins)
            ->select('date', 'dbm_uuid')
            ->groupBy('date')
            ->get()
            ->map(function ($d){

                return [
                    'date' => (new DateTime($d->date))->format('l, jS F, Y'),
                    'uuid' => $d->dbm_uuid,
                ];
            });


        return view('focus.dailyBusinessMetrics.setOptions', compact('options', 'dbm'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateOptions(Request $request)
    {

        if (!access()->allow('comprehensive-operational-summary-report-manager'))
            return new RedirectResponse(route('biller.dashboard'), ['flash_error' => "You Don't Have the Rights for That..."]);
        
        $validated = $request->validate([

            'options' => ['required', 'array'],
            'options.*' => ['required', 'string'],
        ]);

        $dbmDisplayOptions = DbmDisplayOption::where('ins', Auth::user()->ins)->first() ?? new DbmDisplayOption();

        $dbmDisplayOptions->options = json_encode($validated['options']);
        $dbmDisplayOptions->ins = Auth::user()->ins;
        $dbmDisplayOptions->save();


//        return redirect()->route('biller.dashboard')->with('success', 'Comprehensive Operational Summary Report Options Updated Successfully');
        return new RedirectResponse(route('biller.dashboard'), ['flash_success' => 'Comprehensive Operational Summary Report Options Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function redirectToMetrics($dbmUuid)
    {

        return redirect()->route('daily-business-metrics', ['dbmUuid' => $dbmUuid]);
    }


    public function getProjectGrossProfitData($ins, $projectEndDate = null, $fromDate = null, $toDate = null, $status = null, $customerId = null)
    {

        $q = Project::query();

        $q->withoutGlobalScopes()->where('ins', $ins);

        if ($projectEndDate) $q->whereDate('end_date', $projectEndDate);
        if ($fromDate) $q->whereDate('start_date', '>=', $fromDate);
        if ($toDate) $q->whereDate('end_date', '<=', $toDate);
        if ($status) $q->where('status', $status);
        if ($customerId) $q->where('customer_id', $customerId);

        // date filter
//        $q->when(request('start_date') && request('end_date'), function ($q) {
//            $q->whereBetween('start_date', array_map(fn ($v) => date_for_database($v), [request('start_date'), request('end_date')]));
//        });

        // status filter
        switch (request('status')) {
            case 'active':
                $q->whereHas('quotes', fn ($q) =>  $q->whereHas('budget')->where('verified', 'No'));
                break;
            case 'complete':
                // $q->whereHas('quotes', fn ($q) =>  $q->whereHas('budget')->where('verified', 'Yes'));
                $q->whereHas('misc', fn ($q) =>  $q->where('name', 'Completed'));
                break;
            case 'expense':
                $q->where(function ($query) {
                    $query->whereHas('purchase_items', function ($query) {
                        $query->where('amount', '>', 0);  // Check for purchases
                    })
                        ->orWhereHas('grn_items', function ($query) {
                            $query->whereRaw('round(rate * qty) > 0');  // Check for GRN items
                        })
                        ->orWhereHas('labour_allocations', function ($query) {
                            $query->whereRaw('hrs * 500 > 0');  // Check for labour allocations
                        })
                        ->orWhereHas('labour_allocations.clrPivot')
                        ->orWhereHas('quotes.stockIssues', function ($query) {
                            $query->where('total', '>', 0);  // Check for stock issues in quotes
                        })
                        ->orWhereHas('quotes.projectstock', function ($query) {
                            $query->where('total', '>', 0);  // Check for project stock
                        });
                });
                break;
            case 'verified':
                $q->whereHas('quotes', fn ($q) =>  $q->whereNotNull('verified_by'));
                break;
            case 'invoiced':
                $q->where(function ($q){
                    $q->whereHas('quotes', fn ($q) =>  $q->whereHas('invoice_items', fn ($q) => $q->where('product_price','>', 0)))
                        ->orWhereHas('invoices', fn ($q) => $q->where('subtotal', '>', 0));
                });
                break;
        }

        if (request('customer_id')) {
            $q->where('customer_id', request('customer_id'));
            if (request('branch_id')) $q->where('branch_id', request('branch_id'));
        } else $q->limit(500);

        $core = $q->with(['customer_project', 'quotes', 'purchase_items'])->get();

        return $core->map(function ($project) {

            $customer = '';
            if ($project->customer_project) {
                $customer = $project->customer_project->company;

                $branch = Branch::withoutGlobalScopes()->find($project->branch_id);

                if ($branch) {
                    $customer .= " - {$branch->name}";
                }
            }

            $tid = '<a href="' . route('biller.projects.show', $project) . '">' . gen4tid('Prj-', $project->tid) . '</a>';

            $quotes = '';
            foreach ($project->quotes as $quote) {
                $tidPrefix = $quote->bank_id ? 'PI-' : 'QT-';
                $tid = gen4tid($tidPrefix, $quote->tid);
                $quotes .= '<span>' . $tid . '</span>' . ' : ' . numberFormat($quote->subtotal) . '<br>';
            }

            $verification_dates = '';
            foreach ($project->quotes as $quote) {
                if ($quote->verified_amount > 0) {
                    $verification_dates .= dateFormat($quote->verification_date) . '<br>';
                }
            }

            $directInvoices = Invoice::withoutGlobalScopes()->select('invoices.invoicedate')
                ->join('project_invoices', 'invoices.id', '=', 'project_invoices.invoice_id')
                ->where('project_invoices.project_id', $project->id);

            $indirectInvoices = Invoice::withoutGlobalScopes()->select('invoices.invoicedate')
                ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->join('quotes', 'invoice_items.quote_id', '=', 'quotes.id')
                ->join('project_quotes', 'quotes.id', '=', 'project_quotes.quote_id')
                ->where('project_quotes.project_id', $project->id)
                ->groupBy('project_quotes.project_id');

            $latestInvoiceDate = $directInvoices
                ->union($indirectInvoices)
                ->orderByDesc('invoicedate')
                ->first();

            $invoice_date = $latestInvoiceDate ? dateFormat($latestInvoiceDate->invoicedate) : null;

            $netTotal = 0;
            $invoiceIds = [];
            $invoices = collect()->merge($project->invoices);
            foreach ($project->quotes as $quote) {
                if ($quote->invoice && !in_array($quote->invoice->id, $invoiceIds)) {
                    $invoiceIds[] = $quote->invoice->id;
                    $invoices->add($quote->invoice);
                }
            }
            foreach ($invoices as $invoice) {
                $isFx = +$invoice->fx_curr_rate && +$invoice->fx_curr_rate != 1;
                if ($isFx) $netTotal += floatval($invoice->fx_subtotal);
                else $netTotal += floatval($invoice->subtotal);

                if (count($invoice->creditnotes)) {
                    foreach ($invoice->creditnotes as $cnote) {
                        $isFx = +$cnote->fx_curr_rate && +$cnote->fx_curr_rate != 1;
                        if ($isFx) $netTotal -= floatval($cnote->fx_subtotal);
                        else $netTotal -= floatval($cnote->subtotal);
                    }
                }

                if (count($invoice->debitnotes)) {
                    foreach ($invoice->debitnotes as $dnote) {
                        $isFx = +$dnote->fx_curr_rate && +$dnote->fx_curr_rate != 1;
                        if ($isFx) $netTotal += floatval($dnote->fx_subtotal);
                        else $netTotal += floatval($dnote->subtotal);
                    }
                }
            }
            $this->income = $netTotal;
            $income = numberFormat($netTotal);

            $total_estimate = 0;

            foreach ($project->quotes as $quote) {

                $stockIssues = StockIssue::withoutGlobalScopes()->where('quote_id', $quote->id)->get();

                $stockIssuesValue = $stockIssues->reduce(function($carry, $stock_issue) {
                    return $carry + $stock_issue->total;
                }, 0);

                $dir_purchase_amount = $project->purchase_items->sum('amount') / $project->quotes->count();

                $proj_grn_amount = $project->grn_items()->sum(DB::raw('round(rate*qty)')) / $project->quotes->count();

                $labourAllocations = LabourAllocation::withoutGlobalScopes()->where('project_id', $project->id)->get();
                $labour_amount = $labourAllocations->sum(DB::raw('hrs * 500')) / $project->quotes->count();

                $labourAllocationIds = $labourAllocations->pluck('id');

                $projectQuoteIds = ProjectQuote::withoutGlobalScopes()->where('project_id', $project->id)->get()->pluck('quote_id');
                $projectQuotes = Quote::withoutGlobalScopes()->whereIn('id', $projectQuoteIds)->get();


                $remunerationIds = CLRAllocation::withoutGlobalScopes()->whereIn('labour_allocation_id', $labourAllocations)->get()->pluck('clr_number');

                $casuals_remunerations_amount = CasualLabourersRemuneration::withoutGlobalScopes()->whereIn('clr_number', $remunerationIds)
                        ->sum('total_amount') / $projectQuotes->count();

                $expense_amount = $dir_purchase_amount + $proj_grn_amount + $labour_amount + $casuals_remunerations_amount + $stockIssuesValue;

                $projectStock = Projectstock::withoutGlobalScopes()->where('quote_id', $quote->id)->get();

                if ($projectStock) $expense_amount += $projectStock->sum('total');

                $total_estimate += $expense_amount;
            }

            $this->expense = $total_estimate;
            $expense = numberFormat($total_estimate);

            $profit = 0;
            if ($this->income > 0) {
                $profit = $this->income - $this->expense;
            }
            $this->profit = $profit;
            $this->total_profit += $profit;
            $gross_profit = numberFormat($profit);

            $total_profit = numberFormat($this->total_profit);

            $percent_profit = round(div_num($this->profit, $this->income) * 100);

            return [
                'tid' => gen4tid('PRJ-', $project->tid),
                'title' => $project->name,
                'customer' => $customer,
                'quote' => $tid,
                'status' => 'Active',
                'quote_amount' => $quotes,
                'verify_date' => $verification_dates,
                'invoice_date' => $invoice_date,
                'income' => str_replace(',', '', $income),
                'expense' => str_replace(',', '', $expense),
                'gross_profit' => str_replace(',', '', $gross_profit),
                'perc_profit' => str_replace(',', '', $income) == 0 ? 0 : bcmul(bcdiv(str_replace(',', '', $gross_profit), str_replace(',', '', $income), 6), 100, 2) . '%',
                'total_profit' => $total_profit,
                'percent_profit' => $percent_profit,
            ];
        });
    }




}
