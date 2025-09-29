<?php

namespace App\Http\Controllers\Focus\invoice_payment;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\Company\Company;
use App\Models\customer\Customer;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\project\Project;
use App\Repositories\Focus\invoice_payment\InvoicePaymentRepository;
use Illuminate\Http\Request;
use Response;

class InvoicePaymentsController extends Controller
{
    /**
     * variable to store the repository object
     * @var InvoicePayment
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param InvoicePayment $repository ;
     */
    public function __construct(InvoicePaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::whereHas('deposits')->get(['id', 'name', 'company']);
        $customerIds = $customers->pluck('id')->toArray();
        $projects = Project::whereIn('customer_id', $customerIds)
            ->whereHas('deposits', fn($q) => $q->whereIn('customer_id', $customerIds))
            ->get(['id', 'tid', 'name']);

        return new ViewResponse('focus.invoice_payments.index', compact('projects', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = InvoicePayment::max('tid')+1;
        $customers = Customer::whereHas('currency')
            ->with(['currency' => fn($q) => $q->select('id', 'code', 'rate')->get()])
            ->get(['id', 'company', 'name', 'currency_id','phone', 'email']);
        $accounts = Account::whereHas('accountType', fn($q) => $q->where('system', 'bank'))
            ->get(['id', 'holder', 'currency_id']);

        return new ViewResponse('focus.invoice_payments.create', compact('customers', 'accounts', 'tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'account_id' => 'required',
            'amount' => 'required',
            'payment_type' => 'required',
            'date' => 'required',
            'fx_curr_rate' => 'required',
        ]);

        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl', 'payment_mode', 'reference', 
            'payment_id', 'payment_type', 'rel_payment_id', 'note', 'currency_id', 'fx_curr_rate', 'wh_vat_amount', 'wh_tax_amount',
            'project_id'
        ]);
        $data_items = $request->only(['invoice_id', 'paid', 'wh_tax', 'wh_vat']); 
        $data_items = modify_array($data_items);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $result = $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Invoice Payment '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.invoice_payments.index'), ['flash_success' => 'Invoice Payment updated successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(InvoicePayment $invoice_payment)
    {
        return new ViewResponse('focus.invoice_payments.view', compact('invoice_payment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoicePayment $invoice_payment)
    {   
        $invoice_payment->load('currency');
        $tid = $invoice_payment->tid;
        $customers = Customer::whereHas('currency')
            ->with(['currency' => fn($q) => $q->select('id', 'code', 'rate')->get()])
            ->get(['id', 'company', 'name', 'currency_id']);
        $accounts = Account::whereHas('accountType', fn($q) => $q->where('system', 'bank'))
            ->get(['id', 'holder', 'currency_id']);

        return new ViewResponse('focus.invoice_payments.edit', compact('invoice_payment', 'customers', 'accounts', 'tid'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  InvoicePayment $invoice_payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoicePayment $invoice_payment)
    {
        // extract request input
        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl', 'payment_mode', 'reference', 
            'payment_id', 'payment_type', 'rel_payment_id', 'note', 'currency_id', 'fx_curr_rate', 'wh_vat_amount', 'wh_tax_amount',
            'project_id'
        ]);
        $data_items = $request->only(['id', 'invoice_id', 'paid', 'wh_tax', 'wh_vat']); 

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);

        if ($invoice_payment->reconciliation_items()->where('checked', 1)->exists())
            return errorHandler('Not Allowed! Deposit has been reconciled');
        
        try {
            $result = $this->repository->update($invoice_payment, compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Invoice Payment '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.invoice_payments.index'), ['flash_success' => 'Invoice Payment updated successfully']);
    }


    /**
     * Remove resource from storage
     */
    public function destroy(InvoicePayment $invoice_payment)
    {
        if ($invoice_payment->reconciliation_items()->where('checked', 1)->exists())
            return errorHandler('Not Allowed! Deposit has been reconciled');

        try {
            $this->repository->delete($invoice_payment);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Invoice Payment', $th);
        }

        return new RedirectResponse(route('biller.invoice_payments.index'), ['flash_success' => 'Invoice Payment deleted successfully']);
    }

    /**
     * Unallocated Invoice Payments
     */
    public function select_unallocated_payments(Request $request)
    {
        $payments = InvoicePayment::where('customer_id', request('customer_id'))
        ->whereIn('payment_type', ['on_account', 'advance_payment'])
        ->whereColumn('amount', '!=', 'allocate_ttl')
        ->orderBy('date', 'asc')
        ->get();

        return response()->json($payments);
    }

    public function payment_received($invoice_payment_id, $token)
    {
        $invoice_payment = InvoicePayment::withoutGlobalScopes()->where('id',$invoice_payment_id)->first();
        $expected_token = hash('sha256', $invoice_payment_id . env('APP_KEY'));
        if ($token !== $expected_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.'
            ], 403);
        }
        $company = Company::find($invoice_payment->ins) ?: new Company;
        
        $html = view('focus.invoices.print_payment', ['resource' => $invoice_payment, 'company' => $company])->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        return Response::stream($pdf->Output('payment.pdf', 'I'), 200, $headers);
    }

    public function send_sms_and_email(Request $request, $invoice_payment_id)
    {
        try {
            $data = $request->only(['send_email_sms','phone_number','email']);
            $this->repository->send_sms_and_email(compact('data','invoice_payment_id'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Sending Invoice Payment Receipt', $th);
        }
        return back()->with('flash_success','Sending Invoice Payment Successfully!!');
    }
}
