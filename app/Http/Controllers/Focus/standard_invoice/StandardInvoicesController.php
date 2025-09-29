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

namespace App\Http\Controllers\Focus\standard_invoice;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\cuInvoiceNumber\ControlUnitInvoiceNumberController;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\additional\Additional;
use App\Models\bank\Bank;
use App\Models\classlist\Classlist;
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\term\Term;
use App\Repositories\Focus\standard_invoice\StandardInvoiceRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * BanksController
 */
class StandardInvoicesController extends Controller
{
    /**
     * variable to store the repository object
     * @var StandardInvoiceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StandardInvoiceRepository $repository ;
     */
    public function __construct(StandardInvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\bank\ManageBankRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.invoices.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\CreateResponse
     */
    public function create(Request $request)
    {

        $tid = Invoice::where('is_imported', 0)->whereNull('man_journal_id')->max('tid')+1;
        $customers = Customer::where(fn($q) => $q->where('company', 'NOT LIKE', '%walk-in%')
            ->orWhere('name', 'NOT LIKE', '%walk-in%'))
            ->get(['id', 'company']);
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['sale_product_income', 'service_income']))->get();
        $terms = Term::where('type', 1)->get();  // invoice term type is 1
        $banks = Bank::all();
        $tax_rates = Additional::all();
        $classlists = Classlist::all();
        
        $cuNo = (new ControlUnitInvoiceNumberController())->retrieveCuInvoiceNumber();
        if($cuNo) $newCuInvoiceNo = explode('KRAMW', auth()->user()->business->etr_code)[1] . $cuNo;
        else $newCuInvoiceNo = '';

        return new ViewResponse('focus.standard_invoices.create', compact('classlists','tid', 'customers', 'banks', 'accounts', 'terms', 'tax_rates', 'newCuInvoiceNo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBankRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $request->validate([
            'tax_id' => 'required',
        ]);

        $data = $request->only([
            'customer_id', 'tid', 'invoicedate', 'tax_id', 'bank_id', 'validity', 'account_id', 'currency_id', 'term_id', 'notes', 
            'taxable', 'subtotal', 'tax', 'total', 'cu_invoice_no', 'fx_curr_rate', 'reservation', 'total_promo_discount', 'total_promo_discounted_tax'
        ]);

        $data_items = $request->only([
            'cstm_project_type', // custom col for Epicenter Africa
            'product_id', 'numbering', 'description', 'unit', 'product_qty', 'product_price', 
            'tax_rate', 'product_tax', 'product_subtotal', 'product_amount', 
        ]);

        $promoDiscountData = json_decode($request->promo_discount_data);

        try {
            $this->repository->create(compact('data', 'data_items', 'promoDiscountData'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Invoice', $th);
        }

        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => 'Invoice successfully created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\bank\Bank $bank
     * @param EditBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\EditResponse
     */
    public function edit(Invoice $invoice, Request $request)
    {
        return new ViewResponse('focus.standard_invoices.edit');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Invoice $invoice, Request $request)
    {
        return new ViewResponse('focus.invoices.view', compact('charge'));
    }

    /**
     * Create Customer
     */
    public function create_customer(Request $request)
    {
        $request->validate([
            'company' => 'required',
        ]);
        $input = $request->only(['company', 'name', 'email', 'phone', 'address', 'tax_pin']);

        // email, company, taxid validation
        $email_exists = Customer::whereNotNull('email')->where('email', $input['email'])->exists();
        if ($email_exists) throw ValidationException::withMessages(['Email already exists!']);
        $is_company = Customer::where('company', $input['company'])->exists();
        if ($is_company) throw ValidationException::withMessages(['Company already exists!']);
        if (isset($input['tax_pin'])) {
            $pinCustomer = Customer::where('taxid', $input['tax_pin'])->first(['id', 'company', 'name', 'taxid']);
            if ($pinCustomer) {
                $customer = $pinCustomer->company ?: $pinCustomer->name;
                throw ValidationException::withMessages(["Tax ID: {$pinCustomer->taxid} already used by {$customer}"]);
            }
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $input['tax_pin']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax ID cannot be used']);
        } 
        $input['taxid'] = $input['tax_pin'];
        unset($input['tax_pin']);

        $customer = Customer::create($input);
        return redirect()->back()->with('flash_success', 'Customer Created Successfully');
    }
}
