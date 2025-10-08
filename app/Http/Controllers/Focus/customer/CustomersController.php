<?php

namespace App\Http\Controllers\Focus\customer;

use App\Models\customer\Customer;
use DateInterval;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\customer\CreateResponse;
use App\Http\Responses\Focus\customer\EditResponse;
use App\Repositories\Focus\customer\CustomerRepository;
use App\Http\Requests\Focus\customer\ManageCustomerRequest;
use App\Http\Requests\Focus\customer\CreateCustomerRequest;
use App\Http\Requests\Focus\customer\EditCustomerRequest;
use App\Jobs\SendCustStatementJob;
use App\Models\Company\Company;
use App\Models\manualjournal\Journal;
use App\Models\project\Project;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * CustomersController
 */
class CustomersController extends Controller
{
    /**
     * variable to store the repository object
     * @var CustomerRepository
     */
    public $repository;

    /**
     * contructor to initialize repository object
     * @param CustomerRepository $repository ;
     */
    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\customer\ManageCustomerRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.customers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateCustomerRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\CreateResponse
     */
    public function create(CreateCustomerRequest $request)
    {
        return new CreateResponse('focus.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCustomerRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateCustomerRequest $request)
    {
        $request->validate([
            // 'ar_account_id' => 'required',
            'company' => 'required',
            'password' => request('password') ? 'required_with:user_email | min:7' : '',
            'password_confirmation' => 'required_with:password | same:password'
        ]);
        if (request('password')) {
            if (!preg_match("/[a-z][A-Z]|[A-Z][a-z]/i", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain Upper and Lowercase letters']);
            if (!preg_match("/[0-9]/", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain At Least One Number']);
            if (!preg_match("/[^A-Za-z 0-9]/", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain A Symbol']);
        }
            
        try {
            $result = $this->repository->create($request->except(['_token', 'ins', 'balance']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Customers '.$th->getMessage(), $th);
        }
            
        return new RedirectResponse(route('biller.customers.index'), ['flash_success' => trans('alerts.backend.customers.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\customer\Customer $customer
     * @param EditCustomerRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\EditResponse
     */
    public function edit(Customer $customer, EditCustomerRequest $request)
    {
        return new EditResponse($customer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(EditCustomerRequest $request, Customer $customer)
    {
        $request->validate([
            // 'ar_account_id' => 'required',
            'company' => 'required',
            'password' => request('password') ? 'required_with:user_email | min:7' : '',
            'password_confirmation' => 'required_with:password | same:password'
        ]);
        if (request('password')) {
            if (!preg_match("/[a-z][A-Z]|[A-Z][a-z]/i", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain Upper and Lowercase letters']);
            if (!preg_match("/[0-9]/", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain At Least One Number']);
            if (!preg_match("/[^A-Za-z 0-9]/", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain A Symbol']);
        }
    
        try {
            $this->repository->update($customer, $request->except(['_token', 'ins', 'balance']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Customers', $th);
        }

        return new RedirectResponse(route('biller.customers.show', $customer), ['flash_success' => trans('alerts.backend.customers.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Customer $customer)
    {
        try {
            $this->repository->delete($customer);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Customers', $th);
        }
       
        return new RedirectResponse(route('biller.customers.index'), ['flash_success' => trans('alerts.backend.customers.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Customer $customer, ManageCustomerRequest $request)
    {
        $projects = Project::whereHas('deposits', fn($q) => $q->where('customer_id', $customer->id))
            ->orWhereHas('invoices', fn($q) => $q->where('customer_id', $customer->id))
            ->orWhereHas('quotes', fn($q) => $q->whereHas('invoice', fn($q) => $q->where('customer_id', $customer->id)))
            ->get(['id', 'tid', 'name']);

        $startDate = date('Y-m-d');
        $endDate = "2000-01-01";
        $customerBills = $this->repository->agingFilteredBills($customer->id, $startDate);      
        $aging_cluster = $this->customerAgingCluster($customerBills, $startDate, $endDate);
        // journal adjustment on aging
        $aging_cluster = $this->agingJournalAdjustment($customer, $aging_cluster);

        $customer->on_account = $customer->unallocated_amount;
        $account_balance = array_sum($aging_cluster);

        return new ViewResponse('focus.customers.view', compact('customer', 'aging_cluster', 'account_balance', 'projects'));
    }


    public function agingJournalAdjustment($customer, $aging_cluster) 
    {
        return $aging_cluster;

        $journals = Journal::doesntHave('invoice')
            ->whereHas('items', fn($q) => $q->where('customer_id', $customer->id))
            ->with(['items' => fn($q) => $q->where('customer_id', $customer->id)])
            ->get(['id', 'date', 'note', 'debit_ttl', 'credit_ttl']);
        foreach ($journals as $journal) {
            $debit = +optional($journal->items->first())->debit;
            $credit = +optional($journal->items->first())->credit;
            $secondsDiff = abs(strtotime(date('Y-m-d')) - strtotime($journal->date));
            $daysDiff = floor($secondsDiff / (60*60*24));
            if ($debit > 0) {
                if ($daysDiff >= 0 && $daysDiff <= 30) {
                    $aging_cluster[0] += $debit;
                } else if ($daysDiff >= 31 && $daysDiff <= 60) {
                    $aging_cluster[1] += $debit;
                } else if ($daysDiff >= 61 && $daysDiff <= 90) {
                    $aging_cluster[2] += $debit;
                } else if ($daysDiff >= 91 && $daysDiff <= 120) {
                    $aging_cluster[3] += $debit;
                } else if ($daysDiff > 120) {
                    $aging_cluster[4] += $debit;
                }
            } else if ($credit > 0) {
                $n = count($aging_cluster)-1;
                while ($n > -1) {
                    $value = $aging_cluster[$n];
                    if ($credit <= $value) {
                        $aging_cluster[$n] -= $credit;
                        $credit = 0;
                    } else {
                        $aging_cluster[$n] -= $value;
                        $credit -= $value;
                    }
                    $n--;
                }
            }
        }

        return $aging_cluster;
    }


    /**
     * Customer Aging Summary Index Page
     */
    public function aging_report()
    {
        $customers = Customer::query()
        ->where(fn($q) => $q->whereHas('invoices')->orWhereHas('deposits'))
        // ->whereIn('id', [95])
        ->orderBy('id', 'ASC')
        ->get()
        // ;dd($customers->toArray());
        ->map(function($customer) {
            $startDate = date('Y-m-d');
            $endDate = "2000-01-01";
            $customerBills = $this->repository->agingFilteredBills($customer->id, $startDate);      
            $agingCluster = $this->customerAgingCluster($customerBills, $startDate, $endDate);
            // journal adjustment on aging
            $agingCluster = $this->agingJournalAdjustment($customer, $agingCluster);

            return [
                'customer' => $customer,
                'aging_cluster' => $agingCluster,
                'total_aging' => round(array_sum($agingCluster), 4),
            ];
        })
        ->filter(fn($data) => (bool) round($data['total_aging']));

        // $agingArr = [];
        // foreach ($customers as $key => $data) {
        //     $agingArr[$data['customer']['id']] = numberFormat($data['total_aging']);
        // }
        // dd($agingArr);

        return new ViewResponse('focus.customers.customer_aging_report', ['customers_data' => $customers]);
    }

    /**
     * Customer Aging Summary Filtered
     */
    public function get_aging_report(Request $request) {
        $request->validate(['start_date' => 'required', 'end_date' => 'required']);

        $customers = Customer::query()
        ->where(fn($q) => $q->whereHas('invoices')->orWhereHas('deposits'))
        // ->whereIn('id', [214])
        ->orderBy('id', 'ASC')
        ->get()
        // ;dd($customers->toArray());
        ->map(function($customer) {
            $startDate = date_for_database(request('start_date'));
            $endDate = date_for_database(request('end_date'));   
            $customerBills = $this->repository->agingFilteredBills($customer->id, $startDate);      
            $agingCluster = $this->customerAgingCluster($customerBills, $startDate, $endDate);
            // journal adjustment on aging
            $agingCluster = $this->agingJournalAdjustment($customer, $agingCluster);

            return [
                'customer' => '<a href="'. route('biller.customers.show', $customer) .'">'.($customer->company ?: $customer->name).'</a>',
                'aging_cluster' => $agingCluster,
                'total_aging' => round(array_sum($agingCluster), 4),
            ];
        })
        ->filter(fn($data) => (bool) round($data['total_aging']));

        return response()->json([
            'customers_data' => $customers->values(),
        ]);
    }

    /**
     * Aging By Customer method
     */
    public function customerAgingCluster($bills, $startDate, $endDate)
    {
        // 5 date intervals of between 0 - 120+ days prior 
        $intervals = array();
        for ($i = 0; $i < 5; $i++) {
            $from = $startDate;
            $to = date('Y-m-d', strtotime($from . ' - 30 days'));
            if ($i > 0) {
                $prev = $intervals[$i - 1][1];
                $from = date('Y-m-d', strtotime($prev . ' - 1 day'));
                $to = date('Y-m-d', strtotime($from . ' - 28 days'));
            }
            $intervals[] = [$from, $to];
        }

        $aging_cluster = array_fill(0, 5, 0);
        foreach ($bills as $bill) {
            $due_date = new DateTime($bill->date);
            $balance = $bill->debit - $bill->credit;

            // Check if due_date is within the given date range
            if (strtotime($due_date->format('Y-m-d')) <= strtotime($startDate) && strtotime($due_date->format('Y-m-d')) >= strtotime($endDate)) {
                // Check due_date against each interval
                foreach ($intervals as $i => $dates) {
                    // dd($dates[0], $due_date);
                    $start = $dates[0];
                    $end = $dates[1];
                    if (strtotime($start) >= strtotime($due_date->format('Y-m-d')) && strtotime($end) <= strtotime($due_date->format('Y-m-d'))) {
                        $aging_cluster[$i] += $balance;
                        break;
                    }
                }
                // If due_date is older than the last interval, categorize it as 120+ days
                if (strtotime($due_date->format('Y-m-d')) < strtotime($intervals[4][1])) {
                    $aging_cluster[4] += $balance;
                }
            }
        }
        return $aging_cluster;
    }


    public function check_limit(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        $invoices = $this->statement_invoices($customer);
        $aging_cluster = $this->aging_cluster($customer, $invoices);
        $total_aging = 0;         
        for ($i = 0; $i < count($aging_cluster); $i++) {
            $total_aging += $aging_cluster[$i];
        } 
        return response()->json([
            'total_aging' => floatval($total_aging),
            'outstanding_balance' => floatval($customer->on_account),
            'credit_limit' => floatval($customer->credit_limit),
        ]);
    }

    /**
     * Customer Statement Invoices
     */
    public function statement_invoices($customer)
    {
        $invoices = collect();
        if (!$customer) return $invoices;

        $statement = $this->repository->getStatementForDataTable($customer->id);
        foreach ($statement as $row) {
            if ($row->type == 'invoice') $invoices->add($row);
            else {
                $last_invoice = $invoices->last();
                if ($last_invoice->invoice_id == $row->invoice_id) {
                    $last_invoice->credit += $row->credit;
                }
            }
        }

        return $invoices;
    }
    public function statement_invoices_for_mail($customer)
    {
        $invoices = collect();
        $statement = $this->repository->getStatementForMail($customer->id);
        foreach ($statement as $row) {
            if ($row->type == 'invoice') $invoices->add($row);
            else {
                $last_invoice = $invoices->last();
                if ($last_invoice->invoice_id == $row->invoice_id) {
                    $last_invoice->credit += $row->credit;
                }
            }
        }

        return $invoices;
    }


    /**
     * Aging report from customer statement invoices
     */
    public function aging_cluster($customer, $invoices)
    {
        // 5 date intervals of between 0 - 120+ days prior 
        $intervals = array();
        for ($i = 0; $i < 5; $i++) {
            $from = date('Y-m-d');
            $to = date('Y-m-d', strtotime($from . ' - 30 days'));
            if ($i > 0) {
                $prev = $intervals[$i-1][1];
                $from = date('Y-m-d', strtotime($prev . ' - 1 day'));
                $to = date('Y-m-d', strtotime($from . ' - 28 days'));
            }
            $intervals[] = [$from, $to];
        }

        // aging balance from extracted invoices
        $aging_cluster = array_fill(0, 5, 0);
        $agingClusterArr = [];
        foreach ($invoices as $invoice) {
            $invoiceDate = new DateTime($invoice->date);
            $invoiceBalance = floatval($invoice->debit) - floatval($invoice->credit);
            // over payment
            if ($invoiceBalance < 0) {
                // $customer->on_account += $invoiceBalance * -1;
                $invoiceBalance = 0;
            }
            // due_date between 0 - 120 days
            foreach ($intervals as $i => $dates) {
                $start  = new DateTime($dates[0]);
                $end = new DateTime($dates[1]);
                if ($start >= $invoiceDate && $end <= $invoiceDate) {
                    $aging_cluster[$i] += $invoiceBalance;
                    // $dateKey = "{{$dates[1]} to $dates[0]}";
                    // $agingClusterArr[$dateKey][] = $invoice;
                    break;
                }
            }
            // due_date in 120+ days
            if ($invoiceDate < new DateTime($intervals[4][1])) {
                $aging_cluster[4] += $invoiceBalance;
                // $agingClusterArr["< {$intervals[4][1]}"][] = $invoice;
            }
        }
        // dd($aging_cluster, $agingClusterArr);
        return $aging_cluster;
    }

    /**
     * Customer search options
     */
    public function search(Request $request)
    {
        if (!access()->allow('crm')) return false;
        
        $k = $request->post('keyword');
        $user = Customer::with('primary_group')->where('active', 1)->where(function ($q) use($k) {
            $q->where('name', 'LIKE', '%' . $k . '%')
            ->orWhere('email', 'LIKE', '%' . $k . '')
            ->orWhere('company', 'LIKE', '%' . $k . '');
        })->limit(6)->get(['id', 'name', 'phone', 'address', 'city', 'email','company']);
            
        return view('focus.customers.partials.search')->with(compact('user'));
    }

    /**
     * Fetch cutomers for dropdown select options
     */
    public function select(Request $request)
    {
        if (!access()->allow('crm')) 
            return response()->json(['message' => 'Insufficient privileges'], 403);

        $q = $request->search;
        $customers = Customer::where('name', 'LIKE', '%'.$q.'%')
            ->orWhere('company', 'LIKE', '%'.$q.'%')
            ->limit(6)
            ->get();

        return response()->json($customers);
    }

    /**
     * Print customer statements
     */
    public function print_statement(Request $request, $customer_id)
    {   
        $page = '';
        $params = [];
        $start_date = request('start_date', date('Y-m-d'));
        $company = auth()->user()->business;
        $customer = Customer::withoutGlobalScopes()->find($customer_id);
        // statement on account
        if ($request->type == 1) {
            $page = 'focus.customers.statements.print_statement_on_account';
            $transactions = $this->repository->getTransactionsForDataTable($customer_id)->sortBy('tr_date');

            $startDate = request('start_date', date('Y-m-d'));
            $endDate = "2000-01-01";
            $customerBills = $this->repository->agingFilteredBills($customer_id, $startDate);      
            $aging_cluster = $this->customerAgingCluster($customerBills, $startDate, $endDate);

            $params = compact('transactions', 'start_date', 'company', 'customer', 'aging_cluster');
        } 
        // statement on invoice
        elseif ($request->type == 2) {
            $page = 'focus.customers.statements.print_statement_on_invoice';
            $inv_statements = $this->repository->getStatementForDataTable($customer_id);
            
            $startDate = request('start_date', date('Y-m-d'));
            $endDate = "2000-01-01";
            $customerBills = $this->repository->agingFilteredBills($customer_id, $startDate);      
            $aging_cluster = $this->customerAgingCluster($customerBills, $startDate, $endDate);

            $params = compact('inv_statements', 'start_date', 'company', 'customer', 'aging_cluster');
        }
        
        $html = view($page, $params)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        return Response::stream($pdf->Output('statement_on_account' . '.pdf', 'I'), 200, $headers);
    }

    public function generatePdf($customer_id, $company)
    {
        $page = '';
        $params = [];
        // statement on account
        $page = 'focus.customers.statements.customer_statement';

        $now_date = Carbon::now();
        $dateOneMonthAgo = $now_date->subMonth();
        $start_date = $dateOneMonthAgo->format('Y-m-d');
        $transactions = $this->repository->getTransactionsForMail($customer_id, $start_date)->sortBy('tr_date');
        $customer = Customer::withoutGlobalScopes()->where('ins', $company->id)->find($customer_id);

        $statement_invoices = $this->statement_invoices_for_mail($customer);
        $aging_cluster = $this->aging_cluster($customer, $statement_invoices);

        $inv_statements = $this->repository->getStatementForMail($customer_id);
        $params = compact('transactions','inv_statements', 'start_date', 'company', 'customer', 'aging_cluster');
        
        $html = view($page, $params)->render(); // Load a view file as HTML

        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $pdfOutput = $pdf->Output('', 'S'); // Output as a string

        return $pdfOutput;
        // return Response::stream($pdf->Output('statement_on_account' . '.pdf', 'I'), 200, $headers);
    }

    public function sendMonthlyStatements()
    {
        $company = Company::where('id', auth()->user()->ins)->first();
        $customers = Customer::withoutGlobalScopes()->where('ins',$company->id)->get();
        foreach ($customers as $customer) {
            SendCustStatementJob::dispatch($customer, $company, auth()->user()->ins);
        }
        
        return response()->json(['message' => 'Monthly statements sent.']);
    }


    /**
     * @throws \Exception
     */
    public function newCustomersMetrics(){

        $newCustomers = array_fill(0, 6, 0);
        $months = array_fill(0, 6, 'N/A');


        for ($u = 5; $u >= 0; $u--){

            $date = (new DateTime())->sub(new DateInterval('P' . $u . 'M'));
            $customers = count(Customer::whereDate('created_at', $date->format('Y-m-d'))->get());

            $newCustomers[count($months)-1 - $u] += $customers;
            $months[count($months)-1 - $u] = $date->format("M");
        }

        $title = "New Customers For The Period " . (new DateTime($months[0]))->format('F') . " to " . (new DateTime($months[count($months)-1]))->format('F') . " " . (new DateTime())->format('Y');

        return compact('title','newCustomers', 'months');
    }
    public function send_statement()
    {
        $this->sendMonthlyStatements();
        return redirect()->back()->with('flash_success', 'Customer Statement Generated Successfully!!');
    }

    /**
     * EFRIS Query TaxPayer TIN
     */
    public function queryTaxPayerInfo()
    {
        $tin = request('tin');
        try {
            $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
            $result = $controller->infoByTinOrNinBrn($tin);
            return response()->json($result);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
