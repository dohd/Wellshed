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

namespace App\Http\Controllers\Focus\project;

use App\Models\Company\Company;
use App\Models\Company\ConfigMeta;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\PurchaseItem;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\note\Note;
use App\Models\account\Account;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectMileStone;
use App\Models\stock_issue\StockIssue;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\project\EditResponse;
use App\Repositories\Focus\project\ProjectRepository;
use App\Http\Requests\Focus\project\ManageProjectRequest;
use App\Http\Requests\Focus\project\CreateProjectRequest;
use App\Http\Requests\Focus\project\UpdateProjectRequest;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\invoice\Invoice;
use App\Models\misc\Misc;
use App\Models\project\Budget;
use App\Models\project\Project;
use App\Models\project\ProjectQuote;
use App\Models\project\ProjectRelations;
use App\Models\quote\Quote;
use App\Models\supplier\Supplier;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Log;
use Mpdf\Mpdf;
use Yajra\DataTables\Facades\DataTables;
use App\Models\project\ProjectInvoice;
use App\Models\project\Task;
use App\Models\quote\QuoteInvoice;
use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosesmsRepository;
use Error;
use Illuminate\Support\Facades\Response;

/**
 * ProjectsController
 */
class ProjectsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProjectRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProjectRepository $repository ;
     */
    public function __construct(ProjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\project\ManageProjectRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageProjectRequest $request)
    {
        $customerLoginId = auth()->user()->customer_id;
        $customers = Customer::when($customerLoginId, fn($q) => $q->where('id', $customerLoginId))
            ->whereHas('quotes')
            ->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get()->unique('name');
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::where('ins', auth()->user()->ins)->get();
        $project = new Project;

        // wip accounts
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))
            ->get(['id', 'number', 'holder', 'account_type', 'parent_id'])
            ->filter(fn($v) => !$v->has_sub_accounts);

        return new ViewResponse('focus.projects.index', compact('accounts', 'customers', 'accounts', 'last_tid', 'project', 'mics', 'employees', 'statuses', 'tags'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProjectRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateProjectRequest $request)
    {
        try {
            if (!$request->wip_account_id) 
                throw new Error('WIP Account required!');

            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            $msg = $th->getMessage() .' {user_id: '. auth()->user()->id . '}' . ' at ' . $th->getFile() . ':' . $th->getLine();
            \Illuminate\Support\Facades\Log::error($msg);

            $msg = 'Error Updating Project';
            if ($th instanceof \Illuminate\Validation\ValidationException) {
                $firstError = $th->validator->errors()->first();
                $msg .= ": {$firstError}";
            }
            return response()->json(['status' => 'Error', 'message' => $msg],500);
        }
        return response()->json(['status' => 'Success', 'message' => 'Project Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\project\Project $project
     * @param EditProjectRequestNamespace $request
     * @return \App\Http\Responses\Focus\project\EditResponse
     */
    public function edit(Project $project)
    {
        return new EditResponse($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProjectRequestNamespace $request
     * @param App\Models\project\Project $project
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $request->validate([
            'wip_account_id' => 'required',
        ]);

        try {
            $this->repository->update($project, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Project', $th);
        }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => 'Project Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProjectRequestNamespace $request
     * @param Project $project
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Project $project)
    {
        try {
            $this->repository->delete($project);
        }
        catch (ValidationException $e) {
            // Return validation errors
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
         catch (\Throwable $th) {dd($th);
            return errorHandler('Error Deleting Project', $th);
         }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => trans('alerts.backend.projects.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProjectRequestNamespace $request
     * @param App\Models\project\Project $project
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Project $project, ManageProjectRequest $request)
    {
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $exp_accounts = Account::where('account_type', 'Expense')->get(['id', 'holder', 'number']);
        $suppliers = Supplier::get(['id', 'name']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        $projectQuotes = Project::where('id', $project->id)->first()->quotes->pluck('id');
        $stockIssues = StockIssue::whereIn('quote_id', $projectQuotes)->with('items.productvar.product.unit')->get();

        $productNames = collect($stockIssues)->map(function ($stockIssue) {
            return collect($stockIssue['items'])->map(function ($item) {
                return $item['productvar']['name'] . ' | ' . $item['productvar']['code'];
            });
        })->flatten();

        // temp properties
        $project->customer = $project->customer_project;
        $project->creator = auth()->user();


        $stockIssues = Project::find($project->id)
            ->join('stock_issues', 'stock_issues.quote_id', 'projects.main_quote_id')
            ->select(
                "stock_issues.*"
            )
            ->get();


        $mics = Misc::all();
        // dd($mics);
        $employees = Hrm::where('ins', auth()->user()->ins)->get();
        $expensesByMilestone = $this->getExpensesByMilestone($project->id);
        $budgetLines = ProjectMileStone::orderBy('name')
        ->where('project_id', $project->id)
        ->where('name', '!=', '')
        ->get(['name', 'id']);

        // actual expenses
        request()->merge(['project_id' => $project->id]);
        $repository = new \App\Repositories\Focus\project\ProjectRepository;
        $controller = new \App\Http\Controllers\Focus\project\ExpensesTableController($repository);
        $expenses = $controller->get_expenses();
        $totalExpense = $expenses->sum(fn($v) => $v->qty * $v->rate);

        // graphs
        $quoteVsJobExpense = $this->getQuoteVsJobExpenseData($project, $totalExpense);
        $verifiedVsActualExpense = $this->getVerifiedAmountVsActualExpenseData($project, $totalExpense);
        $gpTable4Data = $this->getGpTable4Data($project, $totalExpense);

        return new ViewResponse('focus.projects.view', 
            compact('expenses', 'totalExpense', 'budgetLines', 'project', 'accounts', 'exp_accounts', 'suppliers', 'last_tid', 'mics', 'employees', 'expensesByMilestone', 'productNames', 'stockIssues', 'quoteVsJobExpense', 'verifiedVsActualExpense', 'gpTable4Data')
        );
    }


    public function printGrossProfit(Project $project, ManageProjectRequest $request)
    {
        try {
            $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
            $exp_accounts = Account::where('account_type', 'Expense')->get(['id', 'holder', 'number']);
            $suppliers = Supplier::get(['id', 'name']);
            $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

            $projectQuotes = Project::where('id', $project->id)->first()->quotes->pluck('id');
            $stockIssues = StockIssue::whereIn('quote_id', $projectQuotes)->with('items.productvar.product.unit')->get();

            $productNames = collect($stockIssues)->map(function ($stockIssue) {
                return collect($stockIssue['items'])->map(function ($item) {
                    return $item['productvar']['name'] . ' | ' . $item['productvar']['code'];
                });
            })->flatten();

            // temp properties
            $project->customer = $project->customer_project;
            $project->creator = auth()->user();


            $stockIssues = Project::find($project->id)
                ->join('stock_issues', 'stock_issues.quote_id', 'projects.main_quote_id')
                ->select(
                    "stock_issues.*"
                )
                ->get();


            $mics = Misc::all();
            // dd($mics);
            $employees = Hrm::where('ins', auth()->user()->ins)->get();
            $expensesByMilestone = $this->getExpensesByMilestone($project->id);
            $budgetLines = ProjectMileStone::orderBy('name')->where('project_id', $project->id)->where('name', '!=', '')->get(['name', 'id']);

            // actual expenses
            request()->merge(['project_id' => $project->id]);
            $repository = new \App\Repositories\Focus\project\ProjectRepository;
            $controller = new \App\Http\Controllers\Focus\project\ExpensesTableController($repository);
            $expenses = $controller->get_expenses();
            $totalExpense = $expenses->sum(fn($v) => $v->qty * $v->rate);

            $quoteVsJobExpense = $this->getQuoteVsJobExpenseData($project, $totalExpense);
            $verifiedVsActualExpense = $this->getVerifiedAmountVsActualExpenseData($project, $totalExpense);

            $company = Company::find(Auth::user()->ins);

            $htmlContent = view('focus.projects.printGrossProfit', 
                compact('totalExpense', 'company','budgetLines', 'project', 'accounts', 'exp_accounts', 'suppliers', 'last_tid', 'mics', 'employees', 'expensesByMilestone', 'productNames', 'stockIssues', 'quoteVsJobExpense', 'verifiedVsActualExpense')
            )->render();

            $mpdf = new \Mpdf\Mpdf(array_replace(config('pdf'), [
                'format' => 'A3',
                'margin_bottom' => 20,  // Space for footer
                'margin_left' => 15,
                'margin_right' => 15,
                'setAutoTopMargin' => 'stretch',
            ]));
            $mpdf->SetAutoPageBreak(true, 10);
            $mpdf->WriteHTML($htmlContent);

            return Response::stream($mpdf->Output(gen4tid('PRJ-', $project->tid) . ' - Project Gross Profit.pdf', 'I'), 200, [
                "Content-type" => "application/pdf",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ]);  

        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }
    }


    private function getQuoteVsJobExpenseData($project, $totalExpense){
        $quoteVsJobExpense = $project->quotes
            ->map(function ($quote) use ($project, $totalExpense) {                
                $budgetedAmount = ($quote->budget)? $quote->budget->items()->sum(DB::raw('round(new_qty*price)')) : 0;
                $expense_amount = div_num($totalExpense, $project->quotes->count());
                $balance = $budgetedAmount - $expense_amount;
                return [
                    'tid' => gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid),
                    'budget' => floatval($budgetedAmount),
                    'expense' => floatval($expense_amount),
                    'balance' => floatval($balance),
                    'profit' => empty($budgetedAmount) || $budgetedAmount == 0 ? 0.00 : floatval(bcmul(bcdiv($balance, $budgetedAmount, 4), 100, 2)),
                ];
            });

        return [
            'tid' => $quoteVsJobExpense->pluck('tid'),
            'budget' => $quoteVsJobExpense->pluck('budget'),
            'expense' => $quoteVsJobExpense->pluck('expense'),
            'balance' => $quoteVsJobExpense->pluck('balance'),
            'profit' => $quoteVsJobExpense->pluck('profit'),
        ];
    }

    private function getVerifiedAmountVsActualExpenseData($project, $totalExpense) {
        $verifiedVsActualExpense = $project->quotes
            ->map(function ($quote) use ($project, $totalExpense) {
                $expense_amount = div_num($totalExpense, $project->quotes->count());
                $actual_amount = $quote->verified_amount;
                $balance = $actual_amount - $expense_amount;
                return [
                    'tid' => gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid),
                    'verified' => floatval($actual_amount),
                    'expense' => floatval($expense_amount),
                    'balance' => floatval($balance),
                    'profit' => empty($actual_amount) || $actual_amount == 0 ? 0.00 : floatval(bcmul(bcdiv($balance, $actual_amount, 4), 100, 2)),
                ];
            });

        return [

            'tid' => $verifiedVsActualExpense->pluck('tid'),
            'verified' => $verifiedVsActualExpense->pluck('verified'),
            'expense' => $verifiedVsActualExpense->pluck('expense'),
            'balance' => $verifiedVsActualExpense->pluck('balance'),
            'profit' => $verifiedVsActualExpense->pluck('profit'),
        ];

    }


    private function getGpTable4Data($project, $totalExpense){
        $gpTable4Data = $project->quotes->map(function ($quote) use ($project, $totalExpense) {
            $expense_amount = div_num($totalExpense, $project->quotes->count());
            $actual_amount = $quote->subtotal;
            $balance = $actual_amount - $expense_amount;
            return [
                "tid" => gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid),
                "actual" => $actual_amount,
                "expense" => $expense_amount,
                "profit" => $balance,
                "perc_profit" => round(div_num($balance, $actual_amount) * 100),
            ];
        });

        return [
            'tid' => $gpTable4Data->pluck('tid'),
            'actual' => $gpTable4Data->pluck('actual'),
            'expense' => $gpTable4Data->pluck('expense'),
            'profit' => $gpTable4Data->pluck('profit'),
            'perc_profit' => $gpTable4Data->pluck('perc_profit'),
        ];
    }

    /**
     * Update issuance tools and requisition
     */
    public function update_budget_tool(Request $request, Budget $budget)
    {
        try {
            $budget->update(['tool' => $request->tool, 'tool_reqxn' => $request->tool_reqxn]);
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Project Budget Tool', $th);
        }

        return redirect()->back();
    }

    /**
     * Project autocomplete search
     */
    public function project_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        $kw = $request->post('keyword') ?: $request->post('search');

        $projects = Project::when(request('is_expense'), fn($q) => $q->whereHas('misc', fn($q) => $q->where('name', '!=', 'Completed')))
            ->when(request('customer_id'), fn($q) => $q->where('customer_id', request('customer_id')))
            ->where(function($q) use($kw) {
                $q->where('tid', 'LIKE', "%{$kw}%")
                ->orWhere('name', 'LIKE', "%{$kw}%")
                ->orWhereHas('quote', fn ($q) => $q->whereHas('budget')->where('tid', 'LIKE', "%{$kw}%"))
                ->orWhereHas('branch', fn ($q) => $q->where('name', 'LIKE', "%{$kw}%"))
                ->orWhereHas('customer_project', fn ($q) =>  $q->where('company', 'LIKE', "%{$kw}%"));
            })
            ->with('budget')
            ->limit(6)
            ->orderBy('id','desc')
            ->get();

        $output = [];
        foreach ($projects as $project) {
            $quote_tids = [];
            foreach ($project->quotes as $quote) {
                if ($quote->bank_id) $quote_tids[] = gen4tid('PI-', $quote->tid);
                else $quote_tids[] = gen4tid('QT-', $quote->tid);
            }
            $quote_tids = implode(', ', $quote_tids);
            $quote_tids = "[{$quote_tids}]";

            $customer = @$project->customer_project->company;
            $branch = @$project->branch_id;
            $project_tid = gen4tid('Prj-', $project->tid);
            $output[] = [
                'id' => $project->id,
                'name' => implode(' - ', [$quote_tids, $customer, $branch, $project_tid, $project->name]),
                'client_id' => @$project->customer_project->id,
                'branch_id' => @$project->branch->id,
                'budget' => $project->budget
            ];
        }

        return response()->json($output);
    }

    public function getProjectMileStones(Request $request){

        $milestones = ProjectMileStone::where('project_id', $request->projectId)->select('id', 'name', 'amount', 'balance', 'end_date as due_date','end_date')->get();

        return json_encode($milestones);
    }

    public function getBudgetLinesByQuote(Request $request){

        $milestones = [];

        try {

            $quote = Quote::where('id', $request->quoteId)->with('project.milestones')->first();

            if ($quote)
                $milestones = $quote->project->milestones;

        }
        catch (Exception $e){
            return "Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . "  | & Line: " . $e->getLine();
        }

        return json_encode($milestones);
    }

    public function search(Request $request)
    {
        $q = $request->post('keyword');

        $projects = Project::where('tid', 'LIKE', '%' . $q . '%')
            ->orWhereHas('customer', function ($query) use ($q) {
                $query->where('company', 'LIKE', '%' . $q . '%');
                return $query;
            })->orWhereHas('branch', function ($query) use ($q) {
                $query->where('name', 'LIKE', '%' . $q . '%');
                return $query;
            })->limit(6)->get();


        if (count($projects) > 0) return view('focus.projects.partials.search')->with(compact('projects'));
    }

    /**
     * Projects select dropdown options
     */
    public function loadSelect(Request $request)
    {
        if(request('customer_id')){
            $kw = $request->search;
            $projects = Project::where('customer_id', request('customer_id'))
                ->where(fn($q) => $q->where('name', 'LIKE', '%'.$kw.'%')->orwhere('tid', 'LIKE', '%'.$kw.'%'))
                ->limit(6)
                ->get()
                ->map(fn($v) => [
                    'id' => $v->id,
                    'name' => gen4tid('Prj-', $v->tid) . ' - ' . $v->name,
                ]);
        } else {
            $q = $request->post('q');
            $projects = Project::where('name', 'LIKE', '%' . $q . '%')->limit(6)->get();    
        }
        return response()->json($projects);
    }

    /**
     * Project Quotes select
     */
    public function quotes_select()
    {
        $quotes = Quote::where(['customer_id' => request('customer_id'), 'status' => 'approved'])
            ->doesntHave('project')
            ->doesntHave('invoice')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => gen4tid($v->bank_id? 'PI-' : 'QT-', $v->tid) . ' - ' . $v->notes,
            ]);

        return response()->json($quotes);
    }
    public function invoices_select()
    {
        $invoice = Invoice::where(['customer_id' => request('customer_id')])
            ->where('is_standard', 1)
            ->doesntHave('quote')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => gen4tid('INV-', $v->tid) . ' - ' . $v->notes,
            ]);

        return response()->json($invoice);
    }
    public function select_detached_invoices()
    {
        $invoice = ProjectInvoice::where(['project_id' => request('project_id')])
            ->get()
            ->map(fn($v) => [
                'id' => $v->invoice_id,
                'name' => gen4tid('INV-', @$v->invoice->tid) . ' - ' . @$v->invoice->notes,
            ]);

        return response()->json($invoice);
    }
    public function store_quote_invoice(Request $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();
            QuoteInvoice::create([
                'invoice_id' => $request->invoice_id,
                'quote_id' => $request->quote_id
            ]);
            $project_invoice = ProjectInvoice::where(['invoice_id'=> $request->invoice_id, 'project_id' => $request->project_id])->first();
            if($project_invoice){
                DB::commit();
                return redirect()->back()->with('flash_success', 'Already Attached Successfully!!');
            }
            ProjectInvoice::create([
                'invoice_id' => $request->invoice_id,
                'project_id' => $request->project_id
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Attaching Quote to Detached Invoice', $th);
        }
        return redirect()->back()->with('flash_success', 'Attached Successfully!!');
    }


    /**
     * Update Project Status
     */
    public function update_status(ManageProjectRequest $request)
    {
        $response = [];
        switch ($request->r_type) {
            case 1:
                $project = Project::find($request->project_id);
                $project->progress = $request->progress;
                if ($request->progress == 100) {
                    $status_code = ConfigMeta::where('feature_id', '=', 16)->first();
                    $project->status = $status_code->feature_value;
                }
                // $project->save();
                $response = ['status' => $project->progress];
                break;
            case 2:
                $project = Project::find($request->project_id);
                $project->status = $request->sid;
                $project->save();
                $task_back = task_status($project->status);
                $status = '<span class="badge" style="background-color:' . $task_back['color'] . '">' . $task_back['name'] . '</span> ';
                $response = compact('status');
                break;
        }

        return response()->json($response);
    }

    /**
     * Project Meta Data
     */
    public function store_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);
        $response = ['status' => 'Error', 'message' => $input['obj_type'] === '2' ? "IKWOO" : "NOT IKWOO" . " TYPE NI: " . gettype($input['obj_type'])];

        DB::beginTransaction();

        try {

            if ($input['obj_type'] == 2){

                $data = Arr::only($input, ['project_id','amount', 'name', 'description', 'color', 'duedate', 'time_to']);
                $data = array_replace($data, [
                    'due_date' => date_for_database("{$data['duedate']} {$data['time_to']}:00"),
                    'note' => $data['description'],
                    'amount' => numberClean($data['amount']),
                    'balance' => numberClean($data['amount']),
                ]);
                unset($data['duedate'], $data['time_to'], $data['description']);
                $milestone =( new ProjectMileStone)->fill($data);
                $milestone->save();
                ProjectRelations::create(['project_id' => $milestone->project_id, 'milestone_id' => $milestone->id]);

                // log
                $data = ['project_id' => $milestone->project_id, 'value' => '['. trans('projects.milestone') .']' .'['. trans('general.new') .'] '. $input['name'], 'user_id' => auth()->user()->id];
                ProjectLog::create($data);

                $result = '
                        <li id="m_'. $milestone->id .'">
                            <div class="timeline-badge" style="background-color:'. $milestone->color .';">*</div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h4 class="timeline-title">'. $milestone->name .'</h4>
                                    <p><small class="text-muted">['. trans('general.due_date') .' '. dateTimeFormat($milestone->due_date) .']</small></p>
                                </div>
                                <div class="timeline-body mb-1">
                                    <p> '. $milestone->note .'</p>
                                    <p> Milestone Amount: '. numberFormat($milestone->amount) .'</p>
                                </div>
                                <small class="text-muted">
                                    <i class="fa fa-user"></i><strong>'. @$milestone->creator->fullname . '</strong>
                                    <i class="fa fa-clock-o"></i> '. trans('general.created') . '  ' . dateTimeFormat($milestone->created_at) . '
                                </small>
                                <div class="btn-group">
                                    <button class="btn btn-link milestone-edit" obj-type="2" data-id="'. $milestone->id .'" data-url="'. route('biller.projects.edit_meta') .'">
                                        <i class="ft ft-edit" style="font-size: 1.2em"></i>
                                    </button>
                                    <button class="btn btn-link milestone-del" obj-type="2" data-id="'. $milestone->id .'" data-url="'. route('biller.projects.delete_meta') .'">
                                        <i class="fa fa-trash fa-lg danger"></i>
                                    </button>
                                </div>                             
                            </div>
                        </li>
                    ';
                    $project = Project::find($data['project_id']); 
                updateCompletionPercentage($milestone, $project);
                $response = array_replace($response, ['status' => 'Success', 't_type' => 2, 'meta' => $result,  'refresh' => 1]);
            }
            else if ($input['obj_type'] == 5){

                $data = ['project_id' => $request->project_id, 'value' => $request->name];
                $project_log = ProjectLog::create($data);

                $log_text = '<tr><td>*</td><td>'. dateTimeFormat($project_log->created_at) .'</td><td>'
                    .auth()->user()->first_name .'</td><td>'. $project_log->value .'</td></tr>';

                $response = array_replace($response, ['status' => 'Success', 't_type' => 5, 'meta' => $log_text]);
            }
            else if ($input['obj_type'] == 6) {
                $data = Arr::only($input, ['title', 'content']);
                $data['section'] = 1;
                $note = Note::create($data);
                $user = Hrm::find(auth()->user()->id);
                $note->project_id = $request->project_id;
                $note->ins = auth()->user()->ins;
                $note->creator_id = auth()->user()->id;
                $note->user_type = $user->customer_id ? 'customer' :'employee';
                $note->save();

                
                if($user->customer_id){
                    $this->notify_user($note);
                }else{
                    $this->notify_customer($note);
                }

                ProjectLog::create(['project_id' => $input['project_id'], 'value' => '[Project Note][New]' . $note->title]);

                ProjectRelations::create(['project_id' => $input['project_id'], 'note_id' => $note->id]);

                $log_text = '<tr>
                        <td>*</td>
                        <td>'. $note->title .'</td>
                        <td>'. $note->content .'</td>
                        <td>'. auth()->user()->first_name . '</td>
                        <td>'. dateTimeFormat($note->created_at) .'</td>
                        <td>
                            <a href="'. route('biller.notes.edit', [$note->id]) .'" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fa fa-pencil "></i> </a>
                            <a class="btn btn-danger round" table-method="delete" data-trans-button-cancel="Cancel" data-trans-button-confirm="Delete" data-trans-title="Are you sure you want to do this?" data-toggle="tooltip" data-placement="top" title="Delete" style="cursor:pointer;" onclick="$(this).find(&quot;form&quot;).submit();">
                            <i class="fa fa-trash"></i> <form action="' . route('biller.notes.show', [$note->id]) . '" method="POST" name="delete_table_item" style="display:none"></form></a>
                        </td>
                    </tr>';

                $response = array_replace($response, ['status' => 'Success', 't_type' => 6, 'meta' => $log_text, 'refresh' => 1]);
            }
            else if ($input['obj_type'] == 7){

                $project = Project::find($input['project_id']);
                if (!$project->main_quote_id)
                    $project->update(['main_quote_id' => @$input['quote_ids'][0]]);

                foreach($input['quote_ids'] as $val) {
                    $item = ProjectQuote::firstOrCreate(
                        ['project_id' => $project->id, 'quote_id' => $val],
                        ['project_id' => $project->id, 'quote_id' => $val]
                    );
                    $item->quote->update(['project_quote_id' => $item->id]);
                }

                $response = array_replace($response, ['status' => 'Success', 't_type' => 7, 'meta' => '', 'refresh' => 1]);
            }
            else if ($input['obj_type'] == 9){

                $project = Project::find($input['project_id']);

                foreach($input['invoice_ids'] as $val) {
                    $item = ProjectInvoice::firstOrCreate(
                        ['project_id' => $project->id, 'invoice_id' => $val],
                        ['project_id' => $project->id, 'invoice_id' => $val]
                    );
                    // $item->quote->update(['project_quote_id' => $item->id]);
                }

                $response = array_replace($response, ['status' => 'Success', 't_type' => 9, 'meta' => '', 'refresh' => 1]);
            }


        } catch (\Throwable $th) {
            \Log::error($th->getMessage() . ' ' . $th->getFile() . ' : ' . $th->getLine());
        }

        if ($response['status'] == 'Success') {
            DB::commit();
            $response['message'] = 'Resource Updated Successfully';
            return response()->json($response);
        }

        return response()->json($response);
    }

    public function notify_customer($note){

        $company = Company::find(auth()->user()->ins);
        $project = Project::find($note->project_id);
        $tid = gen4tid('PRJ-',$project->tid);
        $text = "Notification from {$company->sms_email_name} Received on Project: {$tid}";
        $cost_per_160 = 0.6;
        $charCount = strlen($text);
        $blocks = ceil($charCount / 160); // Round up to account for partial 160-character blocks

        $data = [
            'subject' => $text,
            'user_type' => 'customer',
            'delivery_type' => 'now',
            'message_type' => 'single',
            'phone_numbers' => @$project->customer->phone,
            'sent_to_ids' => $project->customer->id,
            'characters' => $charCount,
            'cost' => $cost_per_160,
            'user_count' => 1,
            'total_cost' => $cost_per_160 * $blocks, // Calculate total cost based on blocks
        ];
        $result = SendSms::create($data);
        (new RosesmsRepository(auth()->user()->ins))->textlocal($project->customer->phone, $text, $result);
    }
    public function notify_user($note){

        $project = Project::find($note->project_id);
        $tid = gen4tid('PRJ-',$project->tid);
        $text = "Notification From Customer Received on Project: {$tid}";
        $cost_per_160 = 0.6;
        $charCount = strlen($text);
        $blocks = ceil($charCount / 160); // Round up to account for partial 160-character blocks
        $company = Company::find(auth()->user()->ins);

        $data = [
            'subject' => $text,
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'single',
            'phone_numbers' => @$company->notification_number,
            'sent_to_ids' => $company->id,
            'characters' => $charCount,
            'cost' => $cost_per_160,
            'user_count' => 1,
            'total_cost' => $cost_per_160 * $blocks, // Calculate total cost based on blocks
        ];
        $result = SendSms::create($data);
        (new RosesmsRepository(auth()->user()->ins))->textlocal($company->notification_number, $text, $result);
    }

    /**
     * Edit Meta Data
     */
    public function edit_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);
        // milestone
        if ($input['obj_type'] == 2) {

            $milestone = ProjectMileStone::where('id', intval($input['object_id']))->first();
            $project = $milestone->project;

            return view('focus.projects.modal.milestone_new', compact('milestone', 'project'));
        }
        // note
        else if($input['obj_type'] == 6){
            $note = Note::find(intval($input['object_id']));
            $project = $note->proj;
            return view('focus.projects.modal.note_new', compact('note', 'project'));
        }
    }

    /**
     * Delete meta
     */
    public function delete_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);
        $resp_payload = ['status' => 'Error', 'message' => json_encode($request)];
        
        try {
            DB::beginTransaction();

            // milestone
            if ($input['obj_type'] == 2) {
                $milestone = ProjectMileStone::findOrFail($input['object_id']);
                if ($milestone->requisitions()->exists()) {
                    return response()->json([
                        'status' => 'Error',
                        'message' => 'Cannot delete milestone: associated material requisitions exist.',
                        't_type' => 0,
                        'meta' => $input['object_id']
                    ], 400);
                }
                if ($milestone->purchase_items()->exists()) {
                    return response()->json([
                        'status' => 'Error',
                        'message' => 'Cannot delete milestone: associated Expenses exist.',
                        't_type' => 0,
                        'meta' => $input['object_id']
                    ], 400);
                }
                ProjectLog::create(['project_id' => $milestone->project_id, 'value' => '['. trans('projects.milestone') .']' .'['. trans('general.deleted') .'] '. $milestone['name'], 'user_id' => auth()->user()->id]);
                $milestone->purchases()->update(['project_milestone' => null]);
                $milestone->purchase_items()->update(['budget_line_id' => null]);
                foreach ($milestone->items as $item) {
                    $budget_item = $item->budget_item;

                    if ($budget_item) {
                        $budget_item->qty_allocated_to_milestones -= $item->qty;
                        // Optional: ensure it doesn’t go negative
                        if ($budget_item->qty_allocated_to_milestones < 0) {
                            $budget_item->qty_allocated_to_milestones = 0;
                        }
                        $budget_item->save();
                    }

                    $item->delete();
                }
                $milestone->delete();

                $project = Project::findOrFail($milestone->project_id);
                updateCompletionPercentage(0, $project);
                $resp_payload = ['status' => 'Success', 'message' => 'Milestone Deleted Successfully', 't_type' => 1, 'meta' => $input['object_id']];
            }
            // note
            else if($input['obj_type'] == 6){
                $note = Note::find($input['object_id']);
                ProjectLog::create(['project_id' => $note['project_id'], 'value' => '[Project Note][New]' . $note->title]);
                ProjectRelations::where(['project_id' => $note['project_id'], 'note_id' => $note->id])->delete();
                $note->delete();
                $resp_payload = ['status' => 'Success', 'message' => 'Milestone Deleted Successfully', 't_type' => 1, 'meta' => $input['object_id']];
            }

            DB::commit();
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error($th->getMessage() .' {user_id: '. auth()->user()->id . '}' . ' at ' . $th->getFile() . ':' . $th->getLine());
            return response()->json(['status' => 'Error', 'message' => 'Milestone or Note could not be deleted!', 't_type' => 1], 500);
        }

        return response()->json($resp_payload);
    }

    /**
     * Update Meta
     */
    public function update_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);

        DB::beginTransaction();

        try {
            switch ($input['obj_type']) {
                case 2 :
                    // dd($input);
                    $data = Arr::only($input, ['project_id','amount', 'name', 'description', 'color', 'duedate', 'time_to']);
                    $data = array_replace($data, [
                        'due_date' => date_for_database("{$data['duedate']} {$data['time_to']}:00"),
                        'note' => $data['description'],
                        'amount' => numberClean($data['amount']),
                    ]);
                    unset($data['duedate'], $data['time_to'], $data['description']);
                    $milestone = ProjectMileStone::find($input['object_id']);
                    $milestone->update($data);

                    // log
                    $data = ['project_id' => $milestone->project_id, 'value' => '['. trans('projects.milestone') .']' .'['. trans('general.update') .'] '. $input['name'], 'user_id' => auth()->user()->id];
                    ProjectLog::create($data);


                    $data = ['status' => 'Success', 'message' => trans('general.update'), 't_type' => 1, 'meta' => $input['object_id'], 'refresh' => 1];
                    break;
                case 6:
                    // dd($input);
                    $data = Arr::only($input, ['project_id','title', 'content']);

                    $note = Note::find($input['object_id']);
                    $note->update($data);

                    // log
                    ProjectLog::create(['project_id' => $input['project_id'], 'value' => '[Project Note][New]' . $note->title]);

                    ProjectRelations::create(['project_id' => $input['project_id'], 'note_id' => $note->id]);

                    $data = ['status' => 'Success', 'message' => 'Note Updated Successfully', 't_type' => 1, 'meta' => $input['object_id'], 'refresh' => 1];
                    break;
            }
            DB::commit();
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $data = ['status' => 'Error', 'message' => 'Internal server error!'];
        }

        return response()->json($data);
    }

    /**
     * Remove Project Quote
     */
    public function detach_quote(Request $request)
    {
        $input = $request->except('_token');
        $error_data = [];

        DB::beginTransaction();

        try {
            $project = Project::find($input['project_id']);
            $quote = Quote::find($input['quote_id']);

            $expense_amount = $project->purchase_items->sum('amount');
            $issuance_amount = 0;
            foreach ($project->quotes as $quote) {
                $issuance_amount += $quote->projectstock->sum('total');
            }
            $expense_total = $expense_amount + $issuance_amount;
            $project_budget = $project->quotes->sum('total');

            if ($expense_total >= $project_budget - $quote->total && $expense_total > 0) {
                $error_data = ['status' => 'Error', 'message' => "Not allowed! Project has been expensed."];
                trigger_error($error_data['message']);
            } elseif ($quote->invoice) {
                $doc = $quote->bank_id? 'Proforma Invoice' : 'Quote';
                $inv_tid = @$quote->invoice->tid ?: '';
                $error_data = ['status' => 'Error', 'message' => "Not allowed! {$doc} is attached to Invoice no. {$inv_tid}"];
                trigger_error($error_data['message']);
            }

            ProjectQuote::where(['project_id' => $input['project_id'], 'quote_id' => $input['quote_id']])->delete();
            if ($project->main_quote_id == $input['quote_id']) {
                $other_project_quote = ProjectQuote::where(['project_id' => $input['project_id']])->first();
                if ($other_project_quote) $project->update(['main_quote_id' => $other_project_quote->quote_id]);
                else $project->update(['main_quote_id' => null]);
            }

            DB::commit();
            return response()->json(['status' => 'Success', 'message' => 'Resource Detached Successfully', 't_type' => 7]);
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
            if (!$error_data) $error_data = ['status' => 'Error', 'message' => 'Something went wrong!'];
            return response()->json($error_data, 500);
        }
    }
    public function detach_invoice(Request $request)
    {
        $input = $request->except('_token');
        $error_data = [];

        DB::beginTransaction();

        try {
            $project = Project::find($input['project_id']);
            $invoice = Invoice::find($input['invoice_id']);           


            ProjectInvoice::where(['project_id' => $input['project_id'], 'invoice_id' => $input['invoice_id']])->delete();
            QuoteInvoice::where('invoice_id', $input['invoice_id'])->delete();

            DB::commit();
            return response()->json(['status' => 'Success', 'message' => 'Resource Detached Successfully', 't_type' => 7, 'refresh' => 1]);
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
            if (!$error_data) $error_data = ['status' => 'Error', 'message' => 'Something went wrong!'];
            return response()->json($error_data, 500);
        }
    }

    /**
     * DataTable Project Activity Log
     */
    public function log_history(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);

        $core = collect();
        $project = Project::find($input['project_id']);
        if ($project) $core = $project->history;

        return DataTables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('created_at', function ($project) {
                return dateTimeFormat($project->created_at);
            })
            ->addColumn('user', function ($project) {
                return user_data($project->user_id)['first_name'];

            })
            ->make(true);
    }

    /**
     * Milestone budget limit
     */
    public function budget_limit(Project $project)
    {
        $project_budget = 0;
        foreach ($project->quotes as $quote) {
            if ($quote->budget) $project_budget += $quote->budget->budget_total;
        }
        if ($project_budget == 0 && $project->quotes->count())
            $project_budget = $project->quotes->sum('total');
        elseif ($project_budget == 0) $project_budget = $project->worth;

        $milestone_budget = $project_budget;
        foreach ($project->milestones as $milestone) {
            $milestone_budget -= $milestone->amount;
        }

        return response()->json(['status' => 'Success', 'data' => compact('project_budget', 'milestone_budget')]);
    }

    /**
     * Project Status tag update
     */
    public function status_tag_update(Request $request)
    {

        try {
            $project = Project::findOrFail($request->project_id);
            $project->update(['status' => $request->status, 'end_note' => $request->end_note]);
        } catch (\Throwable $th) {
            return errorHandler('Error updating project status tag', $th);
        }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => 'Status tag successfully updated']);
    }


    public function getExpensesByMilestone(int $projectId) {

        try {

            $mStone = ProjectMileStone::where('project_id', $projectId)->get();

            if (!empty($mStone->toArray())) {

                $milestones = $mStone->pluck('name');

                $mstoneArray = $milestones->toArray();
                array_push($mstoneArray, 'No Budget Line Selected');

                $totals = array_fill(0, count($mstoneArray), 0);
                $milestoneTotals = array_combine($mstoneArray, $totals);

                /** Getting Direct Purchase Totals */
                $dir_purchase_items = PurchaseItem::whereHas('project', fn($q) => $q->where('projects.id', $projectId))
                    ->with('purchase', 'account')
                    ->get();

                foreach ($dir_purchase_items as $dpi) {

                    if ($dpi->purchase->project_milestone !== 0) {

                        $projectMilestone = ProjectMileStone::where('id', $dpi->purchase->project_milestone)->first();

                        if (empty($projectMilestone))
                            continue;

                        $milestoneTotals[$projectMilestone->name] += $dpi->amount;
                    } else {

                        $milestoneTotals['No Budget Line Selected'] += $dpi->amount;
                    }
                }

                /** Getting Purchase Order totals */
                $goods_receive_items = GoodsreceivenoteItem::whereHas('project', fn($q) => $q->where('itemproject_id', $projectId))->get();

                foreach ($goods_receive_items as $grnItem) {

                    $projectMilestone = ProjectMileStone::where('id', $grnItem->purchaseorder_item->purchaseorder->project_milestone)->first();

                    if ($grnItem->purchaseorder_item->purchaseorder->project_milestone !== 0 && !empty($projectMilestone)) {

                        $milestoneTotals[$projectMilestone->name] += $grnItem->rate * $grnItem->qty;
                    } else {

                        $milestoneTotals['No Budget Line Selected'] += $grnItem->rate * $grnItem->qty;
                    }
                }

                /** Getting Labour Totals */
                $labour = LabourAllocation::whereHas('project', fn($q) => $q->where('project_id', $projectId))->get();
                foreach ($labour as $lab) {

                    if(!empty($lab->project_milestone)){
                        $projectMilestone = ProjectMileStone::where('id', $lab->project_milestone)->first();

                        if ($lab->project_milestone !== 0 && !empty($projectMilestone)) {

                            $milestoneTotals[$projectMilestone->name] += $lab->hrs * 500;
                        } else {

                            $milestoneTotals['No Budget Line Selected'] += $lab->hrs * 500;
                        }
                    }
                }

                return $milestoneTotals;
            }

            return [];


        }
        catch(Exception $ex){

//            if (Auth::user()->roles()->first()->id === 17)
                return [
                    'trace' => $ex->getTrace() ,
                    'message' => $ex->getMessage(),
                    'code' => $ex->getCode(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine(),
                ];

//            else
//                return ['An error occurred. Please await support'];
        }

    }

    public function get_project_report()
    {
        $customers = Customer::all();
        return view('focus.projects.all_round_report', compact('customers'));
    }
    public function get_all_report(Request $request)
    {
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $exp_accounts = Account::where('account_type', 'Expense')->get(['id', 'holder', 'number']);
        $suppliers = Supplier::get(['id', 'name']);
        $data = $request->only(['project_id','customer_id']);
        $project = Project::find($data['project_id']);
        $leads = [];
        $djcs = [];
        $budgets = [];
        foreach ($project->quotes as $quote){
            $leads[] = $quote->lead;
            $djcs[] = $quote->lead ? $quote->lead->djcs : '';
            $budgets[] = $quote->budget;
            
        }
        // $tasks = [];
        // foreach ($project->milestones as $milestone){
        //     $tasks[] = $milestone->tasks;
        // }
        $tasks = Task::whereHas('milestone', fn($q) => $q->where('project_milestones.project_id', $project->id))->get();
        $expensesByMilestone = $this->getExpensesByMilestone($project->id);
        $invoices = Invoice::whereHas('quotes', function($q)  use ($project) {
            $q->whereHas('project', function($q) use ($project){
                $q->where('projects.id',$project->id);
            });
        })->orWhereHas('project', fn($q) => $q->where('projects.id',$project->id))
        ->get();
        $rjc = $project->rjc;
        return view('focus.projects.view_all', compact('project', 'leads', 'djcs', 'budgets','accounts','exp_accounts', 'suppliers', 'tasks','expensesByMilestone','invoices', 'rjc'));
    }



    public function projectStatusMetrics(){

        $stIds = Misc::where('section', 2)->pluck('id');

        $statuses = Misc::where('section', 2)->pluck('name')->toArray();
        $projects = array_fill(0, count($statuses), 0);

        for ($u = 0; $u < count($statuses); $u++){

            $p = count(Project::where('status', $stIds[$u])->get());
            $projects[$u] += $p;
        }

        return compact('statuses', 'projects');
    }
}
