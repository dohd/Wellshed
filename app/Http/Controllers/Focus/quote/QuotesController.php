<?php

namespace App\Http\Controllers\Focus\quote;

use App\Models\lead\LeadSource;
use App\Models\quote\Quote;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\omniconvo\OmniController;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\quote\CreateResponse;
use App\Http\Responses\Focus\quote\EditResponse;
use App\Repositories\Focus\quote\QuoteRepository;
use App\Http\Requests\Focus\quote\ManageQuoteRequest;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Http\Requests\Focus\quote\CreateQuoteRequest;
use App\Http\Requests\Focus\quote\EditQuoteRequest;
use App\Jobs\NotifyReferrer;
use App\Models\additional\Additional;
use App\Models\classlist\Classlist;
use App\Models\Company\Company;
use App\Models\Company\CompanyCommissionDetail;
use App\Models\customer\Customer;
use App\Models\customer_enrollment\CustomerEnrollmentItem;
use App\Models\items\VerifiedItem;
use App\Models\lpo\Lpo;
use App\Models\verifiedjcs\VerifiedJc;
use App\Models\fault\Fault;
use App\Models\hrm\Hrm;
use App\Models\project\ProjectQuote;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Models\quote\QuoteFile;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Response;
use DB;

/**
 * QuotesController
 */
class QuotesController extends Controller
{

    protected $headers = [
        "Content-type" => "application/pdf",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    protected $file_path = 'img' . DIRECTORY_SEPARATOR . 'quote_files' . DIRECTORY_SEPARATOR;
    /**
     * variable to store the repository object
     * @var QuoteRepository
     */
    protected $repository;
    protected $storage;

    /**
     * contructor to initialize repository object
     * @param QuoteRepository $repository ;
     */
    public function __construct(QuoteRepository $repository)
    {
        $this->repository = $repository;
        $this->storage = Storage::disk('public');
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\quote\ManageQuoteRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageQuoteRequest $request)
    {
        $customerLoginId = auth()->user()->customer_id;
        $customers = Customer::when($customerLoginId, fn($q) => $q->where('id', $customerLoginId))
            ->get(['id', 'company']);
        $accounts = Account::whereHas('accountType', fn($q) => $q->where('system', 'income'))->get();

        $siQuotes = Quote::where('id', 398)->with('stockIssues')->get()->toArray();//->pluck('stock_issues');

        $stock_issues_arrays = array_column($siQuotes, 'stock_issues');

        // Flatten the array of arrays into a single array
        $stock_issues = array_merge(...$stock_issues_arrays);

        $stockIssuesValue = array_reduce($stock_issues, function($carry, $stock_issue) {
            return $carry + $stock_issue['total'];
        }, 0);

        $leadSources = LeadSource::select('id', 'name')->get();
        $classlists = Classlist::get();


        return view('focus.quotes.index', compact('classlists', 'customers', 'accounts', 'leadSources'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateQuoteRequestNamespace $request
     * @return \App\Http\Responses\Focus\quote\CreateResponse
     */
    public function create()
    {
        return new CreateResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateQuoteRequest $request)
    {
        $request->validate([
            'lead_id' => 'required',
        ]);

        // extract request input fields
        $data = $request->only([
            'client_ref', 'tid', 'date', 'template_quote_id','notes', 'taxable', 'subtotal', 'tax', 'total',
            'currency_id', 'term_id', 'tax_id', 'lead_id', 'pricegroup_id', 'attention', 'classlist_id',
            'reference', 'reference_date', 'validity', 'prepared_by_user', 'print_type','template_type','bom_id',
            'customer_id', 'branch_id', 'bank_id', 'is_repair', 'quote_type','extra_header', 'extra_footer' , 'account_id',
            'productvar_id','appear_image','uom_status','unapproved_reminder_date'
        ]);
        $data_items = $request->only([
            'numbering', 'product_id', 'product_name', 'product_qty', 'product_subtotal', 'product_price', 'tax_rate',
            'unit', 'estimate_qty', 'buy_price', 'row_index', 'a_type', 'misc','product_type','client_product_id'
        ]);

        $skill_items = $request->only(['skill', 'charge', 'hours', 'no_technician' ]);

        $equipments = $request->only(['unique_id','equipment_tid','equip_serial','make_type','item_id','capacity','location','fault','row_index_id']);

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;

        $data_items = modify_array($data_items);
        $skill_items = modify_array($skill_items);
        $equipments = modify_array($equipments);

        try {
            $result = $this->repository->create(compact('data', 'data_items', 'skill_items','equipments'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Record', $th);
        }

        $route = route('biller.quotes.index');
        $msg = trans('alerts.backend.quotes.created');
        if ($result['bank_id']) {
            $route = route('biller.quotes.index', 'page=pi');
            $msg = 'Proforma Invoice created successfully';
        }

        // print preview url
        $valid_token = token_validator('', 'q'.$result->id .$result->tid, true);
        $msg .= ' <a href="'. route('biller.print_quote', [$result->id, 4, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>';

        return new RedirectResponse($route, ['flash_success' => $msg]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\quote\Quote $quote
     * @param EditQuoteRequestNamespace $request
     * @return \App\Http\Responses\Focus\quote\EditResponse
     */
    public function edit(Quote $quote)
    {
        // update product_tax
        if ($quote->tax_id && $quote->products->where('tax_rate', 0)->count() == $quote->products->count()) {
            $quote['products'] = $quote->products->map(function ($item) use($quote) {
                $item['tax_rate'] = $quote->tax_id;
                $item['product_tax'] = $item->product_subtotal * $item->product_qty * $item['tax_rate'] * 0.01;
                $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $item['tax_rate'] * 0.01);    
                return $item;
            });
        }
        if (!$quote->taxable) {
            $quote['taxable'] = 0;
            foreach ($quote->products as $key => $item) {
                if ($item->tax_rate) $quote['taxable'] += $item->product_qty * $item->product_subtotal;
            }
        }
        
        return new EditResponse($quote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateQuoteRequestNamespace $request
     * @param App\Models\quote\Quote $quote
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(EditQuoteRequest $request, Quote $quote)
    {
        $request->validate([
            'lead_id' => 'required',
        ]);

        // extract request input fields
        $data = $request->only([
            'client_ref', 'date', 'notes', 'taxable', 'subtotal', 'tax', 'total', 'classlist_id',
            'currency_id', 'term_id', 'tax_id', 'lead_id', 'pricegroup_id', 'attention',
            'reference', 'reference_date', 'validity', 'prepared_by_user', 'print_type','template_type','bom_id',
            'customer_id', 'branch_id', 'bank_id', 'revision', 'is_repair', 'quote_type','extra_header', 'extra_footer', 'account_id',
            'productvar_id','appear_image','uom_status','unapproved_reminder_date'
        ]);
        $data_items = $request->only([
            'id', 'numbering', 'product_id', 'product_name', 'product_qty', 'product_subtotal', 'product_price', 'tax_rate',
            'unit', 'estimate_qty', 'buy_price', 'row_index', 'a_type', 'misc','product_type','client_product_id'
        ]);
        $skill_items = $request->only(['skill_id', 'skill', 'charge', 'hours', 'no_technician']);
        $equipments = $request->only(['eqid','unique_id','equipment_tid','equip_serial','make_type','item_id','capacity','location','fault','row_index_id']);

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;

        $data_items = modify_array($data_items);
        $skill_items = modify_array($skill_items);
        $equipments = modify_array($equipments);
        try {
            $result = $this->repository->update($quote, compact('data', 'data_items', 'skill_items','equipments'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Record', $th);
        }
        
        $route = route('biller.quotes.index');
        $msg = trans('alerts.backend.quotes.updated');
        if ($result['bank_id']) {
            $route = route('biller.quotes.index', 'page=pi');
            $msg = 'Proforma Invoice updated successfully';
        }

        // print preview url
        $valid_token = token_validator('', 'q'.$result->id .$result->tid, true);
        $msg .= ' <a href="'. route('biller.print_quote', [$result->id, 4, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>';

        return new RedirectResponse($route, ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteQuoteRequestNamespace $request
     * @param App\Models\quote\Quote $quote
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Quote $quote)
    {

        try {
            $type = $quote->bank_id > 0? 'pi' : 'quote';

            $this->repository->delete($quote);

            $link = route('biller.quotes.index');
            $msg = trans('alerts.backend.quotes.deleted');
            if ($type == 'pi') {
                $link = route('biller.quotes.index', 'page=pi');
                $msg = 'Proforma Invoice Successfully Deleted';
            }
        }
        catch (ValidationException $e) {
            // Return validation errors
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
         catch (\Throwable $th) {
            return errorHandler('Error Deleting Quote', $th);
        }
        

        return new RedirectResponse($link, ['flash_success' => $msg]);
    }


    /**
     * Show the form for viewing the specified resource.
     *
     * @param DeleteQuoteRequestNamespace $request
     * @param App\Models\quote\Quote $quote
     * @return \App\Http\Responses\ViewResponse ViewResponse
     */
    public function show(Quote $quote)
    {
        $quote['bill_type'] = 4;
        $accounts = Account::all();
        $features = ConfigMeta::where('feature_id', 9)->first();
        $lpos = Lpo::where('customer_id', $quote->customer_id)->get();

        // update product_tax
        if ($quote->tax_id && $quote->products->where('product_tax', 0)->count() == $quote->products->count()) {
            $quote['products'] = $quote->products->map(function ($item) use($quote) {
                $item['tax_rate'] = $quote->tax_id;
                $item['product_tax'] = $item->product_subtotal * $item->product_qty * $item['tax_rate'] * 0.01;
                $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $item['tax_rate'] * 0.01);    
                return $item;
            });
        }
        if (!$quote->taxable) {
            $quote['taxable'] = 0;
            foreach ($quote->products as $key => $item) {
                if ($item->tax_rate) $quote['taxable'] += $item->product_qty * $item->product_subtotal;
            }
        }

        return new ViewResponse('focus.quotes.view', compact('quote', 'accounts', 'features', 'lpos'));
    }

    /**
     *  Quotes Verification Index Page
     */
    public function verificationIndex(ManageQuoteRequest $request)
    {
        $customers = Customer::get(['id', 'company']);
        return new ViewResponse('focus.quotes_verification.index', compact('customers'));
    }

    /**
     * Quotes Verification Form
     *
     * @param string $id
     * @return \App\Http\Responses\Focus\quote\EditResponse
     */
    public function verify_quote(Quote $quote)
    {
        $faults = Fault::all(['name']);
        $jobcards = VerifiedJc::where('quote_id', $quote->id)->with('equipment')->get();
        $employees = Hrm::all();
        $additionals = Additional::all();

        /**
         * product_subtotal == rate
         * product_price == rateInc (VAT) 
         * 
         */
        if ($quote->verified == 'Yes') {
            $products = VerifiedItem::where('quote_id', $quote->id)->get();
            // update product_tax
            if ($quote->tax_id && $products->where('tax_rate', 0)->count() == $products->count()) {
                $products = $products->map(function ($item) use($quote) {
                    $item['tax_rate'] = $quote->tax_id;
                    $item['product_tax'] = $item->product_subtotal * $item->product_qty * $item['tax_rate'] * 0.01;
                    $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $item['tax_rate'] * 0.01);    
                    return $item;
                });
            } else {
                $products = $products->map(function ($item) use($quote) {
                    $item['product_tax'] = $item->product_subtotal * $item->product_qty * $item['tax_rate'] * 0.01;
                    $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $item['tax_rate'] * 0.01);    
                    return $item;
                });
            }
        } else {
            $products = $quote->products()->where('misc', 0)->get();
            if ($quote->tax_id && $products->where('tax_rate', 0)->count() == $products->count()) {
                $products = $products->map(function ($item) use($quote) {
                    $item['tax_rate'] = $quote->tax_id;
                    $item['product_tax'] = $item->product_subtotal * $item->product_qty * $item['tax_rate'] * 0.01;
                    $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $item['tax_rate'] * 0.01);    
                    return $item;
                });
            }
        }

        // Project Expenses
        $materialExpenses = $this->materialExpense($quote->id);
        $serviceExpenses = $this->serviceExpense($quote->id);

        return new ViewResponse('focus.quotes_verification.create', 
            compact('materialExpenses', 'serviceExpenses', 'additionals', 'employees', 'quote', 'products', 'jobcards','faults') + bill_helper(2, 4)
        );
    }

    /**
     * Store verified resource in storage.
     *
     * @param \App\Http\Requests\Focus\quote\ManageQuoteRequest $request;
     * @return \App\Http\Responses\RedirectResponse
     */
    public function storeverified(ManageQuoteRequest $request)
    {
        $data = $request->only(['id', 'verify_no', 'gen_remark', 'project_closure_date', 'taxable', 'subtotal', 'total', 'tax', 'subtotal','user_id','expense']);
        $data_items = $request->only([
            'remark', 'row_index', 'item_id', 'a_type', 'numbering', 'product_id', 'product_tax', 'tax_rate',
            'product_name', 'product_qty', 'product_price', 'product_subtotal', 'unit'
        ]);
        $job_cards = $request->only(['type', 'jcitem_id', 'reference', 'date', 'technician', 'equipment_id', 'fault']);
        $labour_items = $request->only(['job_date', 'job_type', 'job_employee', 'job_ref_type', 'job_jobcard_no', 'job_hrs', 'job_is_payable', 'job_note']);

        try {
            $data_items = modify_array($data_items);
            $job_cards = modify_array($job_cards);
            $labour_items = modify_array($labour_items);

            $result = $this->repository->verify(compact('data', 'data_items', 'job_cards', 'labour_items'));

            $tid = $result->tid;
            if ($result->bank_id) $tid = gen4tid('PI-', $tid);
            else $tid = gen4tid('QT-', $tid);
            return new RedirectResponse(route('biller.quotes.verification'), ['flash_success' => $tid . ' verified successfully']);
        } catch (\Throwable $th) {dd($th);
            return errorHandler('Error Verifying', $th);
        }
    }

    /** 
     * Reset verified Quote 
     * */
    public function reset_verified($id)
    {
        $quote = Quote::findOrFail($id);
        DB::transaction(function () use($quote) {
            $quote->verified_jcs()->delete();
            $quote->verified_products()->delete();
            $quote->update([
                'verified' => 'No',
                'verification_date' => null,
                'verified_by' => null,
                'gen_remark' => null,
                'project_closure_date' => null
            ]);
            return true;
        });

        return response()->noContent();
    }

    /** 
     * Project Material Expense
     * */
    public function materialExpense($quoteId)
    {
        $projectQuote = ProjectQuote::where('quote_id', $quoteId)->first(['project_id']);
        $projectId = @$projectQuote->project_id;

        // actual expenses
        request()->merge(['project_id' => $projectId]);
        $repository = new \App\Repositories\Focus\project\ProjectRepository;
        $controller = new \App\Http\Controllers\Focus\project\ExpensesTableController($repository);
        $expenses = $controller->get_expenses()
        ->filter(fn($v) => !in_array($v->exp_category, ['dir_purchase_service', 'labour_service']))
        ->map(function($v) {
            $v->unit = $v->uom;
            $v->total_expense = $v->amount;
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

        // actual expenses
        request()->merge(['project_id' => $projectId]);
        $repository = new \App\Repositories\Focus\project\ProjectRepository;
        $controller = new \App\Http\Controllers\Focus\project\ExpensesTableController($repository);
        $expenses = $controller->get_expenses()
        ->filter(fn($v) => in_array($v->exp_category, ['dir_purchase_service', 'labour_service']))
        ->map(function($v) {
            $v->description = $v->product_name;
            return $v;
        });

        return $expenses;
    }


    /**
     * Approved Customer Quotes not in any project
     */
    public function customer_quotes()
    {
        $quotes = Quote::with('branch')
            ->where(['customer_id' => request('id'), 'status' => 'approved'])
            ->whereNull('project_quote_id')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($quotes);
    }

    /**
     * Update Quote Approval Status
     */
    public function approve_quote(ManageQuoteRequest $request, Quote $quote)
    {
        $request->validate([
            'approved_date' => 'required',
            'approved_by' => 'required',
        ]);

        // extract request input fields
        $input = $request->only(['status', 'approved_method', 'approved_by', 'approval_note', 'approved_date']);

        // update
        $previous_status = $quote->status;
        $input['approved_date'] = date_for_database($input['approved_date']);
        if($input['status'] == 'cancelled'){
            if($quote->project()->exists()) throw ValidationException::withMessages(['Quote has attached Project']);
        }
        // if(+$quote->total < 1) throw ValidationException::withMessages(['Please add Tender value / Contract value/ Quotation value']);
        $quote->update($input);
        if($quote->status == "approved" && $previous_status !== 'approved'){
            if($quote->lead->reservation_uuid){
                $lead = $quote->lead;
                $reservation = ThirdPartiesPromoCodeReservation::where('uuid', $lead['reservation_uuid'])->first() ??
                CustomersPromoCodeReservation::where('uuid', $lead['reservation_uuid'])->first() ??
                ReferralsPromoCodeReservation::where('uuid', $lead['reservation_uuid'])->first();
                $this->reservation_type($reservation, $quote);
            }
        }

        return new RedirectResponse(route('biller.quotes.show', [$quote]), ['flash_success' => 'Approval status updated successfully']);
    }

    public function reservation_type($reservation, $quote)
    {
        if($reservation->tier == 3){
            $tier_3 = $reservation;
            $tier_2_referral = $reservation->referralReferer;
            $tier_1_referral = '';
            if($tier_2_referral->customerReferer)
            {
                $tier_1_referral = $tier_2_referral->customerReferer;
            }
            else if($tier_2_referral->thirdPartyReferer)
            {
                $tier_1_referral = $tier_2_referral->thirdPartyReferer;
            }
            $this->notify_tier1($tier_1_referral, $tier_2_referral, $tier_3, 3, $quote);
            $this->notify_tier2($tier_2_referral, $tier_3, $quote, $tier_1_referral);
        }else if($reservation->tier == 2){
             $tier_1_referral = '';
             $tier_3 = '';
             $tier_2 = $reservation;
            if($reservation->customerReferer)
            {
                $tier_1_referral = $reservation->customerReferer;
            }
            else if($reservation->thirdPartyReferer)
            {
                $tier_1_referral = $reservation->thirdPartyReferer;
            }
            $this->notify_tier1($tier_1_referral, $tier_2, $tier_3, 2, $quote);
        }
    }

    private function notify_tier1($tier_1_referral, $tier_2_referral, $tier_3, $tier, $quote)
    {
        //send email and sms to tier 1
        $company = Company::find(auth()->user()->ins);
        $subject = "Notification to Referrer";
        $message = '';
        $data = [];
        $quoteAmount = $quote->subtotal;
        if($tier == 2){

            $commission = $this->formatCommission($tier_1_referral->promoCode, 'cash_back_1', $quoteAmount);
             $data[] = $this->prepareEnrollmentData($tier_1_referral, $commission, $quote, 'cash_back_1');
            // $message = "Your referral, {$tier_2_referral->name}, has redeemed their referral code at {$company->cname}. We will notify you once invoicing is complete so you can expect your commission.";
            $message = "From {$company->cname}: Well done {$tier_1_referral->name}. Your referral, {$tier_2_referral->name}, has redeemed their referral code for the offer - {$tier_2_referral->promoCode->code} under redeemable code {$tier_2_referral->redeemable_code}. We will notify you once invoicing is complete so you can expect your commission. Keep referring!";
        }else if($tier == 3)
        {
            $commission = $this->formatCommission($tier_1_referral->promoCode, 'cash_back_3', $quoteAmount);
            $data[] = $this->prepareEnrollmentData($tier_1_referral, $commission, $quote, 'cash_back_3');
            // $message = "Your referral, {$tier_3->name}, referred by {$tier_2_referral->name}, has redeemed their referral code at {$company->cname}. We will notify you once invoicing is complete so you can expect your commission.";
            $message = "From {$company->cname}: Well done {$tier_1_referral->name}. Your referral, {$tier_3->name}, referred by {$tier_2_referral->name}, has redeemed their referral code for the offer - {$tier_1_referral->promoCode->code} under redeemable code {$tier_3->redeemable_code}. We will notify you once invoicing is complete so you can expect your commission. Keep referring!";
        }
        if($tier_1_referral->promoCode->company_commission > 0){
            $isPercentage = $tier_1_referral->promoCode->total_commission_type === 'percentage';
            $actualCommission = $isPercentage
            ? ($tier_1_referral->promoCode->company_commission_percent / 100) * $quote->subtotal
            : $tier_1_referral->promoCode->company_commission_amount;
            $company_commission_details = CompanyCommissionDetail::find(1);
            $data[] = [
                'customer_enrollment_id' => '',
                'name' => $company_commission_details->name,
                'email' => $company_commission_details->email,
                'phone' => $company_commission_details->phone,
                'redeemable_code' => 'COMPANY',
                'promo_code_id' => $tier_1_referral->promoCode->id,
                'reservation_uuid' => '',
                'quote_id' => $quote->id,
                'quote_amount' => $quote->subtotal,
                'invoice_id' => '',
                'raw_commission' => $actualCommission,   // raw configured value
                'commission' => $actualCommission,                                  // computed commission
                'actual_commission' => $actualCommission,                     // NEW: based on quote_amount
                'commission_type' => $tier_1_referral->promoCode->total_commission_type,
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ];
        }
        CustomerEnrollmentItem::insert($data);
        NotifyReferrer::dispatch(auth()->user()->ins, $tier_1_referral, $message, $subject);

    }
    private function notify_tier2($tier_2_referral, $tier_3, $quote, $tier_1_referral)
    {
        $quoteAmount = $quote->subtotal;
        $commission = $this->formatCommission($tier_2_referral->promoCode, 'cash_back_2',$quoteAmount);
        $data = $this->prepareEnrollmentData($tier_2_referral, $commission, $quote, 'cash_back_2');

        CustomerEnrollmentItem::create($data);
        //send email and sms to tier 2
        // must have refered by
        $company = Company::find(auth()->user()->ins);
        $subject = "Notification to Referrer";
        $message = "From {$company->cname}: Well done {$tier_1_referral->name}. Your referral, {$tier_3->name}, has redeemed their referral code for the offer - {$tier_3->promoCode->code} under redeemable code {$tier_3->redeemable_code}. We will notify you once invoicing is complete so you can expect your commission. Keep referring!";
        // $message = "Your referral, {$tier_3->name}, has redeemed their referral code at {$company->cname}. We will notify you once invoicing is complete so you can expect your commission.";
        NotifyReferrer::dispatch(auth()->user()->ins, $tier_2_referral, $message, $subject);
    }

    private function formatCommission($promoCode, $field, $quoteAmount)
    {
        if ($promoCode->total_commission_type === 'percentage') {
            $fieldName = $field . '_percent';
            $percentValue = $promoCode->$fieldName;

            // If quote amount is provided, calculate actual commission from it
            if ($quoteAmount !== null) {
                return ($percentValue / 100) * $quoteAmount;
            }

            // Otherwise calculate from total_commission
            return ($percentValue / 100) * $promoCode->total_commission;
        }

        if ($promoCode->total_commission_type === 'fixed') {
            $fieldName = $field . '_amount';
            return $promoCode->$fieldName;
        }

        return 0;
    }

     /**
     * Prepare enrollment item data
     */
    private function prepareEnrollmentData($referral, $commission, $quote, $field)
    {
        $isPercentage = $referral->promoCode->total_commission_type === 'percentage';
        $commissionField = $isPercentage ? $field . '_percent' : $field . '_amount';

        // Calculate actual commission based on enrollment quote amount if percentage
        $actualCommission = $isPercentage
            ? ($referral->promoCode->$commissionField / 100) * $quote->subtotal
            : $referral->promoCode->$commissionField;

        return [
            'customer_enrollment_id' => '',
            'name' => $referral->name,
            'email' => $referral->email,
            'phone' => $referral->phone,
            'redeemable_code' => $referral->redeemable_code,
            'promo_code_id' => $referral->promo_code_id,
            'quote_id' => $quote->id,
            'quote_amount' => $quote->subtotal,
            'invoice_id' => '',
            'reservation_uuid' => $referral->uuid,
            'raw_commission' => $referral->promoCode->$commissionField,   // raw configured value
            'commission' => $commission,                                  // computed commission
            'actual_commission' => $actualCommission,                     // NEW: based on quote_amount
            'commission_type' => $referral->promoCode->total_commission_type,
            'ins' => auth()->user()->ins,
            'user_id' => auth()->user()->id,
        ];
    }

    /**
     * Update Quote LPO Details
     */
    public function update_lpo(ManageQuoteRequest $request)
    {
        // extract input fields
        $input = $request->only(['bill_id', 'lpo_id']);

        Quote::find($input['bill_id'])->update(['lpo_id' => $input['lpo_id']]);

        return response()->json(['status' => 'Success', 'message' => 'LPO added successfully', 'refresh' => 1 ]);
    }
     public function turn_around()
    {
        $customerLoginId = auth()->user()->customer_id;
        $customers = Customer::when($customerLoginId, fn($q) => $q->where('id', $customerLoginId))
            ->get(['id', 'company']);

        return new ViewResponse('focus.turn_around.index', compact('customers'));
    }

    public function send_single_sms(Request $request)
    {
        try {
            DB::beginTransaction();
            $company = Company::find(auth()->user()->ins);
            $token = hash('sha256', $request->id . env('APP_KEY'));
            $quote_link = route('print_quotation', [
                'quote_id' => $request->id,
                'token' => $token
            ]);
            $quote = Quote::find($request->id);
            $text = 'From '.$company->cname. ': '.$request->subject .' ' .$quote_link;
            $phone = $request->sms_to;
            $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';
            $cleanedNumber = preg_replace('/\D/', '', $phone);

            if (preg_match($pattern, $cleanedNumber)) {
                if (preg_match('/^0[17]\d{8}$/', $cleanedNumber)) {
                    // Replace leading 0 with 254
                    $phone_number = '254' . substr($cleanedNumber, 1);
                } else {
                    // Already in 254 format
                    $phone_number = $cleanedNumber;
                }
            }
            $cost_per_160 = 0.6;
            $charCount = strlen($text);
            $blocks = ceil($charCount / 160);
            $data = [
                'subject' =>$text,
                'user_type' =>'customer',
                'delivery_type' => 'now',
                'message_type' => 'single',
                'phone_numbers' => $phone,
                'sent_to_ids' => $quote->customer_id,
                'characters' => $charCount,
                'cost' => $cost_per_160,
                'user_count' => 1,
                'total_cost' => $cost_per_160 * $blocks,
    
            ];
            $result = SendSms::create($data);
            $sms = (new RosesmsRepository(auth()->user()->ins))->textlocal($phone, $text, $result);
            $sms_response = $sms->getData(true);
            $new_request_data = [
                'fb_id' => $phone_number,
                'user_type' => 'whatsapp',
                'message' => $text,
            ];
            $new_request = new Request($new_request_data);
            $omni = new OmniController;
            $omni->sendUserMessage($new_request);
            if($sms_response['status'] == 'success'){
                DB::commit();
                return redirect()->back()->with('flash_success', 'Message Sent Successfully');
            }else if($sms_response['status'] == 'error'){
                return redirect()->back()->with('flash_error', $sms_response['message']);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler("Error sending Sms ".$th->getMessage(), $th);
        }
        return redirect()->back()->with('flash_success', 'Message Sent Successfully');
    }
    
    public function send_email(Request $request)
    {
        try {
            $data = $request->only(['mail_to','subject','text','quote_id']);
            $quote = Quote::find($data['quote_id']);
            $token = hash('sha256', $data['quote_id'] . env('APP_KEY'));
            $quote_link = route('print_quotation', [
                'quote_id' => $data['quote_id'],
                'token' => $token
            ]);
            $data['text'] .= " \n\n The Quote link is given below: {$quote_link}";
            $email_input = [
                'text' => $data['text'],
                'subject' => $data['subject'],
                'mail_to' => $data['mail_to']
            ];
            $email = (new RosemailerRepository(auth()->user()->ins))->send($email_input['text'], $email_input);
            $email_output = json_decode($email);
            if ($email_output->status === "Success"){

                $email_data = [
                    'text_email' => $email_input['text'],
                    'subject' => $email_input['subject'],
                    'user_emails' => $email_input['mail_to'],
                    'user_ids' => $quote->customer_id,
                    'ins' => auth()->user()->ins,
                    'user_id' => auth()->user()->id,
                    'status' => 'sent'
                ];
                SendEmail::create($email_data);
            }
        } catch (\Throwable $th) {
            return errorHandler('Error Sending Quotation via Email', $th);
        }
        return back()->with('flash_success','Email Sent Successfully!!');
    }

    public function quote_generate($quote_id, $token)
    {
        $quote = Quote::withoutGlobalScopes()->where('id',$quote_id)->first();
        $company = Company::find($quote->ins);
        $expected_token = hash('sha256', $quote->id . env('APP_KEY'));
        if ($token !== $expected_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.'
            ], 403);
        }
        $data = [
            'resource' => $quote,
            'company' => $company
        ];
        $html = view('focus.bill.print_quote', $data)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);

        $tid = $data['resource']['tid'];
        $name = 'QT-' . sprintf('%04d', $tid);
        if ($data['resource']['bank_id']) {
            $name = 'PI-' . sprintf('%04d', $tid);
        }

        return Response::stream($pdf->Output($name . '.pdf', 'I'), 200, $this->headers);
    }
    public function quote_download(Request $request)
    {
        $quote_id = $request->quote_id;
        $quote = Quote::where('id', $quote_id)->first();
        $company = Company::find($quote->ins);
        $data = [
            'resource' => $quote,
            'company' => $company
        ];
        $html = view('focus.bill.print_quote', $data)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        
        $tid = $data['resource']['tid'];
        $name = 'QT-' . sprintf('%04d', $tid);
        if ($data['resource']['bank_id']) {
            $name = 'PI-' . sprintf('%04d', $tid);
        }
        return $pdf->Output($name . '.pdf', 'D');
    }

    public function store_attachment(Request $request, $quote_id)
    {
        // dd($request->all(), $quote_id);
        $data = $request->only(['caption', 'document_name']);
        $document_name = $this->uploadFile($data['document_name']);
        $data['document_name'] = $document_name;
        $data['quote_id'] = $quote_id;
        try {
            DB::beginTransaction();
            $result = QuoteFile::create($data);
            if($result){
                DB::commit();
            }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Attaching Quote File',$th);
        }
        return back()->with('flash_success', 'File Attached Successfully!!');
    }

    public function uploadFile($file)
    {
        $file_name = time() . $file->getClientOriginalName();

        $this->storage->put($this->file_path . $file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }

    public function delete_quote_file($quote_file_id)
    {
        try {
            $quote_file = QuoteFile::find($quote_file_id);
            $quote_file->delete();
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Deleting Quote File',$th);
        }
        return back()->with('flash_success', 'File Deleted Successfully!!');
    }
    
    public function calculateExpensesForQuotes($quotes)
    {
        $results = [];

        foreach ($quotes as $quote) {
            $materialExpenses = $this->materialExpense($quote->id);
            $serviceExpenses = $this->serviceExpense($quote->id);

            $expenseTtl = $serviceExpenses->sum('amount') + $materialExpenses->sum('total_expense');
            $subtotal = floatval($quote->verified_amount);

            $profit = $subtotal - $expenseTtl;
            $percent_profit = $subtotal > 0 ? ($profit / $subtotal) * 100 : 0;

            $results[$quote->id] = [
                'profit' => $profit,
                'percent_profit' => $percent_profit,
            ];
        }

        return $results;
    }
}
