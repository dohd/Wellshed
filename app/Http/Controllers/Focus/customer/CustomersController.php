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
use App\Models\orders\Orders;
use App\Models\payment_receipt\PaymentReceipt;
use App\Models\project\Project;
use App\Models\subpackage\SubPackage;
use App\Models\target_zone\TargetZone;
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

        // redirect to subscription expiry page
        // $subExists = optional(auth()->user()->customer->subscriptions())->exists();
        // if ($subExists) {
        //     return redirect()
        //         ->route('subscription.expired')
        //         ->with('message', 'Your subscription has expired');
        // }
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
        $subpackages = SubPackage::all();
        $targetzones = TargetZone::with('items')->get();
        return view('focus.customers.create', compact('subpackages', 'targetzones'));
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
            'sub_package_id' => 'required',
            'segment' => 'required',
            'company' => 'required_if:segment,office',
            'first_name' => 'required_if:segment,household',
            'last_name' => 'required_if:segment,household',
            'email' => 'required_without:phone_no',
            'phone_no' => 'required_without:email',
            'password' => 'required',
            'target_zone_id' => 'required',
            'target_zone_item_id' => ['required', 'array', 'min:1'],
            'building_name' => 'required',
            'floor_no' => 'required',
            'door_no' => 'required',
        ], [
            'sub_package_id' => 'package is required',
            'target_zone_id' => 'delivery zone is required',
            'target_zone_item_id' => 'location is required',
        ]);

        $input = $request->all();
        $input['full_name'] = $input['first_name']? "{$input['first_name']} {$input['last_name']}" : '';

        try {
            $result = $this->repository->create($input);
            return new RedirectResponse(route('biller.customers.index'), ['flash_success' => trans('alerts.backend.customers.created')]);
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Customer', $th);
        }            
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
        $subpackages = SubPackage::all();
        $targetzones = TargetZone::with('items')->get();
        $projects = collect();

        $customer->load(['package', 'hrm', 'customer_zones', 'mainAddress']);
        $customerZone = optional($customer->customer_zones->first());

        return view('focus.customers.edit', compact('customerZone', 'customer', 'subpackages', 'targetzones', 'projects'));
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
            // 'sub_package_id' => 'required',
            'segment' => 'required',
            'company' => 'required_if:segment,office',
            'first_name' => 'required_if:segment,household',
            'last_name' => 'required_if:segment,household',
            'email' => 'required_without:phone_no',
            'phone_no' => 'required_without:email',
            // 'password' => 'required',
            'target_zone_id' => 'required',
            'target_zone_item_id' => ['required', 'array', 'min:1'],
            'building_name' => 'required',
            'floor_no' => 'required',
            'door_no' => 'required',
        ], [
            'sub_package_id' => 'package is required',
            'target_zone_id' => 'delivery zone is required',
            'target_zone_item_id' => 'location is required',
        ]);

        $input = $request->all();
        $input['full_name'] = $input['first_name']? "{$input['first_name']} {$input['last_name']}" : '';

        try {
            $this->repository->update($customer, $input);
            return new RedirectResponse(route('biller.customers.show', $customer), ['flash_success' => trans('alerts.backend.customers.updated')]);
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Customers', $th);
        }
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
            return new RedirectResponse(route('biller.customers.index'), ['flash_success' => trans('alerts.backend.customers.deleted')]);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Customers', $th);
        }       
    }

    public function customerAgingCluster()
    {
        return [];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ManageCustomerRequest $request, Customer $customer)
    {
        $projects = collect();
        $aging_cluster = [];
        $account_balance = $customer->paymentReceipts->sum(fn($v) => $v->debit - $v->credit);
        
        return view('focus.customers.view', compact('customer', 'projects', 'aging_cluster', 'account_balance'));
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

    public function show_form($customer_id){
        $customer = Customer::findOrFail($customer_id);
        $balance = PaymentReceipt::where('customer_id', $customer->id)->selectRaw('SUM(credit-debit) total')->value('total');
        $receipts = PaymentReceipt::where('customer_id', $customer->id)->latest()->get();

        $subscription = $customer->subscription;
        $subscrPlan = optional($customer->subscription->package);
        // if ($subscription->status !== 'active') $subscrPlan = null;

        $charges = PaymentReceipt::where('customer_id', $customer->id)
        ->where('entry_type', 'debit')
        ->get(['id', 'tid', 'notes', 'amount'])
        ->map(function($v) {
            $v->tid = gen4tid('RCPT-', $v->tid);
            return $v;
        });

        $isRecur = Orders::where('customer_id', $customer->id)
            ->where('order_type', 'recurring')
            ->doesntExist();
        return view('focus.customers.show_form', compact('isRecur', 'balance', 'receipts', 'customer', 'subscrPlan', 'charges', 'subscription'));
    }
}
