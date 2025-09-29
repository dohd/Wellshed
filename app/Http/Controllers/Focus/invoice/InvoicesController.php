<?php

namespace App\Http\Controllers\Focus\invoice;

use App\Http\Controllers\Focus\cuInvoiceNumber\ControlUnitInvoiceNumberController;
use App\Http\Controllers\Focus\printer\RegistersController;
use App\Http\Requests\Focus\invoice\ManagePosRequest;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\invoice\CreateResponse;
use App\Http\Responses\Focus\invoice\EditResponse;
use App\Repositories\Focus\invoice\InvoiceRepository;
use App\Http\Requests\Focus\invoice\ManageInvoiceRequest;
use App\Http\Requests\Focus\invoice\CreateInvoiceRequest;
use App\Http\Requests\Focus\invoice\EditInvoiceRequest;
use App\Http\Responses\RedirectResponse;
use App\Models\additional\Additional;
use Illuminate\Support\Facades\Response;
use App\Models\quote\Quote;
use App\Models\project\Project;
use App\Models\bank\Bank;
use App\Models\boq_valuation\BoQValuation;
use App\Models\boq_valuation\BoQValuationItem;
use App\Models\classlist\Classlist;
use App\Models\Company\Company;
use App\Models\Company\EmailSetting;
use App\Models\currency\Currency;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\PurchaseItem;
use App\Models\job_valuation\JobValuation;
use App\Models\job_valuation\JobValuationItem;
use App\Models\lpo\Lpo;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\term\Term;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use App\Repositories\Focus\invoice_payment\InvoicePaymentRepository;
use App\Repositories\Focus\pos\PosRepository;
use DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Log;
use Storage;

/**
 * InvoicesController
 */
class InvoicesController extends Controller
{
    /**
     * variable to store the repository object
     * @var InvoiceRepository
     */
    protected $repository;
    protected $pos_repository;
    protected $inv_payment_repository;

    /**
     * contructor to initialize repository object
     * @param InvoiceRepository $repository ;
     */
    public function __construct()
    {
        $this->repository = new InvoiceRepository;
        $this->pos_repository = new PosRepository;
        $this->inv_payment_repository = new InvoicePaymentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\invoice\ManageInvoiceRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageInvoiceRequest $request)
    {
        $customers = Customer::whereHas('invoices')->get(['id', 'company']);
        $accounts = Account::whereHas('accountType', fn($q) => $q->whereIn('name', ['Income', 'Other Income']))->get();        
        $projects = Project::whereHas('invoices')->orWhereHas('quotes', fn($q) => $q->whereHas('invoice'))->get(['id', 'tid', 'name']);

        if (auth()->user()->ins == 85) {
            $invoiceIds = collect();
            $wipGrnItems = GoodsreceivenoteItem::whereHas('project', function($q) {
                $q->whereHas('quotes', fn($q) => $q->whereHas('invoice'));
            })
            ->whereHas('transactions', fn($q) => $q->where('debit', '>', 0))
            ->whereDoesntHave('transactions', fn($q) => $q->where('credit', '>', 0))
            ->with('project.quotes.invoice')
            ->get();
            foreach ($wipGrnItems as $grnItem) {
                foreach ($grnItem->project->quotes as $quote) {
                    $invoiceIds->push($quote->invoice->id);
                }
            }

            $wipPurchaseItems = PurchaseItem::whereHas('project', function($q) {
                $q->whereHas('quotes', fn($q) => $q->whereHas('invoice'));
            })
            ->whereHas('transactions', fn($q) => $q->where('debit', '>', 0))
            ->whereDoesntHave('transactions', fn($q) => $q->where('credit', '>', 0))
            ->with('project.quotes.invoice')
            ->get();
            foreach ($wipPurchaseItems as $purchaseItem) {
                foreach ($purchaseItem->project->quotes as $quote) {
                    $invoiceIds->push($quote->invoice->id);
                }
            }

            $wipBills = UtilityBill::whereHas('transactions', function($q) {
                $q->where('debit', '>', 0);
                $q->whereHas('project', function($q) {
                    $q->whereHas('quotes', fn($q) => $q->whereHas('invoice'));
                });
            })
            ->whereDoesntHave('transactions', fn($q) => $q->where('credit', '>', 0))
            ->with([
                'transactions' => function($q) {
                    $q->where('debit', '>', 0);
                    $q->whereHas('project', function($q) {
                        $q->whereHas('quotes', fn($q) => $q->whereHas('invoice'));
                    });
                },
                'transactions.project.quotes.invoice'
            ])
            ->get();

            // dd($wipBills->toArray(), optional($wipBills->first()->toArray()));
            foreach ($wipBills as $wipBill) {
                foreach ($wipBill->transactions as $tr) {
                    foreach ($tr->project->quotes as $quote) {
                        $invoiceIds->push($quote->invoice->id);
                    }
                }
            }

            $wipGrns = Goodsreceivenote::whereHas('transactions', function($q) {
                $q->where('debit', '>', 0);
                $q->whereHas('project', function($q) {
                    $q->whereHas('quotes', fn($q) => $q->whereHas('invoice'));
                });
            })
            ->whereDoesntHave('transactions', fn($q) => $q->where('credit', '>', 0))
            ->with([
                'transactions' => function($q) {
                    $q->where('debit', '>', 0);
                    $q->whereHas('project', function($q) {
                        $q->whereHas('quotes', fn($q) => $q->whereHas('invoice'));
                    });
                },
                'transactions.project.quotes.invoice'
            ])
            ->get();
            foreach ($wipGrns as $wipGrn) {
                foreach ($wipGrn->transactions as $tr) {
                    foreach ($tr->project->quotes as $quote) {
                        $invoiceIds->push($quote->invoice->id);
                    }
                }
            }

            $invoiceIds = $invoiceIds->unique()->toArray();

            $failedInvoices = collect();
            $errors = collect();
            $invoices = Invoice::whereIn('id', $invoiceIds)->get();
            // dd($invoices->count(), $invoices->first());
            $invoices = [];
            foreach ($invoices as $invoice) {
                try {
                    DB::transaction(function () use($invoice) {
                        $controller = new InvoicesController(new InvoiceRepository, new PosRepository, new InvoicePaymentRepository);
                        $invoice->transactions()->delete();
                        $invoice['is_update'] = true;
                        $controller->repository->post_invoice($invoice);
                    });
                } catch (\Throwable $th) {
                    $failedInvoices->push($invoice);
                    $errors->push($th);
                }
            }
            if ($failedInvoices->count()) {
                dd($failedInvoices->toArray(), $errors->toArray());
            } 
        }
    
        return new ViewResponse('focus.invoices.index', compact('projects', 'customers', 'accounts'));
    }

    /**
     * Sales Variance Report
     * 
     * */
    public function salesVariance(Request $request)
    {
        $customers = Customer::whereHas('invoices')->get(['id', 'company']);
        $projects = Project::whereHas('invoices')
            ->orWhereHas('quotes', fn($q) => $q->whereHas('invoice'))
            ->get(['id', 'tid', 'name']);
        $accounts = Account::whereHas('accountType', fn($q) => $q->whereIn('name', ['Income', 'Other Income']))
            ->whereNotIn('holder', ['Stock Gain', 'Others', 'Point of Sale', 'Loan Penalty Receivable', 'Loan Interest Receivable', 'Foreign Currency Gain'])
            ->get();        

        return new ViewResponse('focus.invoices.reports.sales_variance', compact('projects', 'customers', 'accounts'));
    }

    /**
     * IPC Retention
     * 
     * */
    public function ipcRetention(Request $request)
    {
        $customers = Customer::whereHas('invoices')->get(['id', 'company']);
        $projects = Project::whereHas('invoices')
            ->orWhereHas('quotes', fn($q) => $q->whereHas('invoice'))
            ->get(['id', 'tid', 'name']);

        return new ViewResponse('focus.invoices.reports.ipc_retention', compact('projects', 'customers'));
    }


    /**
     * Uninvoiced Quotes Index Page
     */
    public function uninvoiced_quote(ManageInvoiceRequest $request)
    {
        $customers = Customer::whereNotNull('currency_id')
            ->whereHas('quotes', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
            ->get(['id', 'company']);
        $lpos = Lpo::whereHas('quotes', fn($q) =>  $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
            ->get(['id', 'lpo_no', 'customer_id']);
        $projects = Project::whereHas('quote', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
            ->get(['id', 'name', 'customer_id']);

        return new ViewResponse('focus.invoices.uninvoiced_quote', compact('customers', 'lpos', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateInvoiceRequestNamespace $request
     * @return \App\Http\Responses\Focus\invoice\CreateResponse
     */
    public function create(CreateInvoiceRequest $request)
    {
        return new CreateResponse('focus.invoices.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateInvoiceRequest $request)
    {
        //dd($request->all());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\invoice\Invoice $invoice
     * @param EditInvoiceRequestNamespace $request
     * @return \App\Http\Responses\Focus\invoice\EditResponse
     */
    public function edit(Invoice $invoice, EditInvoiceRequest $request)
    {
        return new EditResponse($invoice);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteInvoiceRequestNamespace $request
     * @param App\Models\invoice\Invoice $invoice
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Invoice $invoice)
    {
        try {
            $this->repository->delete($invoice);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Invoice', $th);
        }
        
        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => trans('alerts.backend.invoices.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteInvoiceRequestNamespace $request
     * @param App\Models\invoice\Invoice $invoice
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Invoice $invoice, ManageInvoiceRequest $request)
    {
        // update invoice line items
        $prod_count = $invoice->products->count();
        $invoice['products'] = $invoice->products->map(function($item) use($invoice) {
            if (!+$item['product_subtotal']) $item['product_subtotal'] = $item['product_price'];
            if ($item->product_tax > 0 && !+$item->tax_rate) $item['tax_rate'] = $invoice->tax_id;
            if (!+$item->product_tax && $item->tax_rate > 0) 
                $item['product_tax'] = $item->product_subtotal * $item->product_qty * $invoice->tax_id * 0.01;
            return $item;
        });
        $has_no_tax = $invoice->products->where('product_tax', 0)->where('tax_rate', 0)->count();
        if ($invoice->tax_id &&  $prod_count == $has_no_tax) {
            $invoice['products'] = $invoice->products->map(function($item) use($invoice) {
                $item['tax_rate'] = $invoice->tax_id;
                $item['product_tax'] = $item->product_subtotal * $item->product_qty * $invoice->tax_id * 0.01;
                $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $invoice->tax_id * 0.01);
                return $item;
            });
        } 
        
        $accounts = Account::all();
        $features = ConfigMeta::where('feature_id', 9)->first();
        $invoice['bill_type'] = 1;
        $words = [
            'prefix' => '',
            'paynote' => trans('invoices.payment_for_invoice') . ' '. '#' . $invoice->tid
        ];

        if ($invoice['efris_qr_code']) {
            $name = 'EfrisInvoice-' . $invoice['efris_invoice_no'];
            $resource = $invoice['efris_qr_code'];
            $invoice['qrCodeImage'] = $this->getQrCodeImage($name, $resource);
        }
        
        return new ViewResponse('focus.invoices.view', compact('invoice', 'accounts', 'features', 'words'));
    }    

    /**
     * Invoice Creation Form
     */
    public function filter_invoice_quotes(Request $request)
    { 
        // Job Valuation
        if (request('job_valuation_id')) {
            $job_valuation = JobValuation::findOrFail(request('job_valuation_id'));
            if (!$job_valuation->quote) throw ValidationException::withMessages(['Corresponding Quote / PI could not be found']);

            $quote = $job_valuation->quote;
            $customer_id = $quote->customer_id;
            $quote_ids[] = $quote->id;
            $quote->job_valuation_id = $job_valuation->id;
            $quote->tax_id = $job_valuation->tax_id;
            $quote->notes = $job_valuation->note ?: $quote->notes;

            // add retention line item
            $jvItem = $job_valuation->items->last();
            if ($jvItem && round($job_valuation->retention)) {
                $job_valuation->items->push(JobValuationItem::make([
                    'id' => null,
                    'ins' => $jvItem->ins,
                    'job_valuation_id' => $job_valuation->id,
                    'productvar_id' => null,
                    'quote_item_id' => null,
                    'row_type' => 1,
                    'row_index' => $jvItem->row_index+1,
                    'perc_valuated' => $job_valuation->perc_retention,
                    'total_valuated' => -$job_valuation->retention,
                    'numbering' => '*',
                    'product_name' => $job_valuation->retention_note ?: 'Retention',
                    'unit' => 'Lot',
                    'product_qty' => 1,
                    'tax_rate' => +$job_valuation->tax_id,
                    'product_subtotal' => -$job_valuation->retention,
                    'product_price' => -$job_valuation->retention,
                ]));
            }
            $quote->verified_products = $job_valuation->items->map(function($item) use($quote) {
                $item['product_qty'] = round($item->total_valuated) != 0? 1 : 0;
                $item['product_subtotal'] = $item->total_valuated;
                $item['product_price'] = $item->total_valuated;
                $item['product_tax'] = $item->product_subtotal * $item->product_qty * $item->tax_rate * 0.01;
                $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $item->tax_rate * 0.01);    
                $item['verified_tax'] = $item['product_tax'];
                return $item;
            });
            $quotes = collect([$quote]);
        } 
        // BoQ Valuation
        else if (request('boq_valuation_id')) {
            $boq_valuation = BoQValuation::findOrFail(request('boq_valuation_id'));
            if (!$boq_valuation->boq) throw ValidationException::withMessages(['Corresponding BoQ could not be found']);

            $quote = $boq_valuation->boq;
            $quote->currency = $quote->lead ? $quote->lead->customer->currency: '';
            $customer_id = @$quote->lead->client_id;
            $quote_ids[] = $quote->id;
            $quote->boq_valuation_id = $boq_valuation->id;
            $quote->tax_id = $boq_valuation->tax_id;
            $quote->notes = $boq_valuation->note ?: $quote->notes;
            $quote->verified_jcs = $boq_valuation->job_cards;

            // add retention line item
            $jvItem = $boq_valuation->items->last();
            if ($jvItem && round($boq_valuation->retention)) {
                $boq_valuation->items->push(BoQValuationItem::make([
                    'id' => null,
                    'ins' => $jvItem->ins,
                    'boq_valuation_id' => $boq_valuation->id,
                    'productvar_id' => null,
                    'quote_item_id' => null,
                    'row_type' => 1,
                    'row_index' => $jvItem->row_index+1,
                    'perc_valuated' => $boq_valuation->perc_retention,
                    'total_valuated' => -$boq_valuation->retention,
                    'numbering' => '*',
                    'product_name' => $boq_valuation->retention_note ?: 'Retention',
                    'unit' => 'Lot',
                    'product_qty' => 1,
                    'tax_rate' => +$boq_valuation->tax_id,
                    'product_subtotal' => -$boq_valuation->retention,
                    'product_price' => -$boq_valuation->retention,
                ]));
            }
            $quote->verified_products = $boq_valuation->items->map(function($item) use($quote) {
                $item['product_qty'] = round($item->total_valuated) != 0 ? 1 : 0;
                $item['product_subtotal'] = $item->total_valuated;
                $item['product_price'] = $item->total_valuated;
                $item['product_tax'] = $item->product_subtotal * $item->product_qty * $item->tax_rate / 100;
                $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $item->tax_rate / 100);    
                $item['verified_tax'] = $item['product_tax'];
                return $item;
            });

            $quotes = collect([$quote]);
        } 
        else {
            $customer_id = $request->customer;
            $quote_ids = explode(',', $request->selected_products);
            if (!$customer_id || !$quote_ids) {
                $customers = Customer::whereHas('quotes', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))->get(['id', 'company']);
                $lpos = Lpo::whereHas('quotes', fn($q) =>  $q->where(['verified' => 'Yes', 'invoiced' => 'No']))->get(['id', 'lpo_no', 'customer_id']);
                $projects = Project::whereHas('quote', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))->get(['id', 'name', 'customer_id']);
    
                return view($request->is_part_verification? 'focus.verifications.index' : 'focus.invoices.uninvoiced_quote', compact('customers', 'lpos', 'projects'))
                    ->with(['flash_error' => 'Filter and select customer records']);
            }

            // Quote/PI in order of selection (main verification)
            $quotes = Quote::whereIn('id', $quote_ids)
            ->orderByRaw("FIELD(id,{$request->selected_products})")
            ->with(['verified_products' => fn($q) =>$q->orderBy('row_index', 'ASC')])
            ->get();

            // update quote line items totals
            $quotes->each(function($quote) {
                $prod_count = $quote->verified_products->count();
                $verifiedProducts = $quote->verified_products;
                $quote['verified_products'] = $verifiedProducts->map(function($item) use($quote) {
                    if (!+$item['product_subtotal']) $item['product_subtotal'] = $item['product_price'];
                    if ($item->product_tax > 0 && !+$item->tax_rate) $item['tax_rate'] = $quote->tax_id;
                    if (!+$item->product_tax && $item->tax_rate > 0) 
                        $item['product_tax'] = $item->product_subtotal * $item->product_qty * $quote->tax_id * 0.01;
                    return $item;
                });
                
                $has_no_tax = $quote->verified_products->where('product_tax', 0)->where('tax_rate', 0)->count();
                if ($quote->tax_id &&  $prod_count == $has_no_tax) {
                    $quote['products'] = $quote->verified_products->map(function($item) use($quote) {
                        $item['tax_rate'] = $quote->tax_id;
                        $item['product_tax'] = $item->product_subtotal * $item->product_qty * $quote->tax_id * 0.01;
                        $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $quote->tax_id * 0.01);
                        return $item;
                    });
                } 
            });
        }
        
        // check if quotes are of same currency
        $currency_ids = $quotes->pluck('currency_id')->toArray();
        if (count(array_unique($currency_ids)) > 1) throw ValidationException::withMessages(['Selected items must be of same currency!']);
        $currency = $quotes->first()? $quotes->first()->currency : new Currency;
        if(!$currency) $currency = $quote->currency;
        // dd($currency);

        $customer = Customer::find($customer_id) ?: new Customer;
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['sale_product_income', 'service_income']))->get();
        // invoice term type is 1
        $terms = Term::where('type', 1)->get();  
        $banks = Bank::all();
        $additionals = Additional::all();
        $classlists = Classlist::all();
        
        $last_tid = Invoice::where('is_imported', 0)->whereNull('man_journal_id')->max('tid');
        $prefixes = prefixesArray(['invoice', 'quote', 'proforma_invoice', 'purchase_order', 'delivery_note', 'jobcard'], auth()->user()->ins);

        $cuNo = (new ControlUnitInvoiceNumberController())->retrieveCuInvoiceNumber();
        $newCuInvoiceNo = $cuNo? explode('KRAMW', auth()->user()->business->etr_code)[1] . $cuNo : '';

        return new ViewResponse('focus.invoices.create_project_invoice',
            compact('classlists', 'quotes', 'customer', 'last_tid', 'banks', 'accounts', 'terms', 'quote_ids', 'additionals', 'currency', 'prefixes', 'newCuInvoiceNo'),
        );
    }

    /**
     * Store newly created project invoice
     */
    public function store_project_invoice(Request $request)
    {   
        $request->validate([
            'tax_id' => 'required',
        ]);
        
        $bill = $request->only([
            'customer_id', 'bank_id', 'tax_id', 'tid', 'invoicedate', 'validity', 'notes', 'term_id', 'account_id',
            'taxable', 'subtotal', 'tax', 'total', 'job_valuation_id', 'fx_curr_rate', 'cu_invoice_no', 'classlist_id','boq_valuation_id'
        ]);
        $bill_items = $request->only([
            'cstm_project_type', // custom col for Epicenter Africa
            'numbering', 'row_index', 'description', 'reference', 'unit', 'quote_id', 'project_id', 'branch_id', 'verification_id', 
            'product_qty', 'product_subtotal', 'product_price', 'tax_rate', 'product_tax', 'product_amount','boq_id'
        ]);

        $bill['user_id'] = auth()->user()->id;
        $bill['ins'] = auth()->user()->ins;
        $bill_items = modify_array($bill_items);

        try {
            $result = $this->repository->create_project_invoice(compact('bill', 'bill_items'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Project Invoice '.$th->getMessage(), $th);
        }

        // print preview
        $valid_token = token_validator('', 'i' . $result->id . $result->tid, true);
        $msg = ' <a href="'. route( 'biller.print_bill',[$result->id, 1, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>'; 
        
        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => 'Project Invoice created successfully' . $msg]);
    }

    /**
     * Edit Project Invoice Form
     */
    public function edit_project_invoice(Invoice $invoice)
    {

        // non-valuation invoice
        if (!$invoice['job_valuation_id']) {            
            $prod_count = $invoice->products()->count();
            $invoice['products'] = $invoice['products']->map(function($item) use($invoice) {
                if (!+$item['product_subtotal']) $item['product_subtotal'] = $item['product_price'];
                if ($item->product_tax > 0 && !+$item->tax_rate) $item['tax_rate'] = $invoice['tax_id'];
                if (!+$item->product_tax && $item->tax_rate > 0) 
                    $item['product_tax'] = $item->product_subtotal * $item->product_qty * $invoice['tax_id'] * 0.01;
                return $item;
            });
            $has_no_tax = $invoice['products']->where('product_tax', 0)->where('tax_rate', 0)->count();
            if ($invoice['tax_id'] &&  $prod_count == $has_no_tax) {
                $invoice['products'] = $invoice['products']->map(function($item) use($invoice) {
                    $item['tax_rate'] = $invoice['tax_id'];
                    $item['product_tax'] = $item->product_subtotal * $item->product_qty * $invoice['tax_id'] * 0.01;
                    $item['product_amount'] = $item->product_subtotal * $item->product_qty * (1 + $invoice['tax_id'] * 0.01);
                    return $item;
                });
            } 
        }

        $banks = Bank::all();
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['sale_product_income', 'service_income']))->get();
        // invoice type 1
        $terms = Term::where('type', 1)->get(); 
        $additionals = Additional::all();
        $prefixes = prefixesArray(['invoice'], $invoice['ins']);
        $currency = $invoice['currency'] ?: new Currency;
        $classlists = Classlist::all();

        return new ViewResponse('focus.invoices.edit_project_invoice', compact('classlists', 'invoice', 'banks', 'accounts', 'terms', 'additionals', 'prefixes', 'currency'));
    }

    /**
     * Edit Project Invoice Form
     */
    public function update_project_invoice(Invoice $invoice, Request $request)
    {
        // extract request input fields
        $bill = $request->only([
            'customer_id', 'bank_id', 'tax_id', 'tid', 'invoicedate', 'validity', 'notes', 'term_id', 'account_id',
            'taxable', 'subtotal', 'tax', 'total', 'estimate_id', 'fx_curr_rate', 'cu_invoice_no', 'classlist_id'
        ]);
        $bill_items = $request->only([
            'cstm_project_type', // custom col for Epicenter Africa
            'id', 'numbering', 'row_index', 'description', 'reference', 'unit', 'quote_id', 'project_id', 'branch_id', 'verification_id', 
            'product_qty', 'product_subtotal', 'product_price', 'tax_rate', 'product_tax', 'product_amount',
        ]);

        $bill['user_id'] = auth()->user()->id;
        $bill['ins'] = auth()->user()->ins;

        $bill_items = modify_array($bill_items);

        try {
            $result = $this->repository->update_project_invoice($invoice, compact('bill', 'bill_items'));
        } catch (\Throwable $th) { 
            return errorHandler('Error Updating Project Invoice '.$th->getMessage(), $th);
        }

        // print preview
        $valid_token = token_validator('', 'i' . $result->id . $result->tid, true);
        $msg = ' <a href="'. route( 'biller.print_bill',[$result->id, 1, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>'; 
        
        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => 'Project Invoice Updated successfully' . $msg]);
    }

    /**
     * Nullify Invoice
     * 
     */
    public function nullify_invoice(Request $request, Invoice $invoice)
    {
        try {
            DB::beginTransaction();
            $invoice->update(['is_cancelled' => 1]);
            $invoice->transactions()->delete();
            DB::commit();
        } catch (\Throwable $th) {
            return errorHandler($th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.invoices.show', $invoice), ['flash_success' => 'Invoice nullified successfully']);
    }

    /**
     * Create invoice payment
     */
    public function index_payment(Request $request)
    {
        $customers = Customer::get(['id', 'company']);

        return new ViewResponse('focus.invoices.index_payment', compact('customers'));
    }    

    /**
     * Create invoice payment
     */
    public function create_payment(Request $request)
    {
        $tid = InvoicePayment::where('ins', auth()->user()->ins)->max('tid');

        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);

        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return new ViewResponse('focus.invoices.create_payment', compact('accounts', 'tid', 'unallocated_pmts'));
    }

    /**
     * Store invoice payment
     */
    public function store_payment(Request $request)
    {
        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl',
            'payment_mode', 'reference', 'payment_id', 'payment_type'
        ]);
        $data_items = $request->only(['invoice_id', 'paid']); 
        $data_items = modify_array($data_items);
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $result = $this->inv_payment_repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Payment', $th);
        }

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment updated successfully']);
    }

    /**
     * Edit invoice payment
     */
    public function edit_payment(InvoicePayment $payment)
    {   
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);
        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return new ViewResponse('focus.invoices.edit_payment', compact('payment', 'accounts', 'unallocated_pmts'));
    }    

    /**
     * Show invoice payment
     */
    public function show_payment(InvoicePayment $payment)
    {
        return new ViewResponse('focus.invoices.view_payment', compact('payment'));
    }   

    /**
     * Update invoice payment
     */
    public function update_payment(InvoicePayment $payment, Request $request)
    {
        // extract request input
        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl',
            'payment_mode', 'reference', 'payment_id', 'payment_type'
        ]);
        $data_items = $request->only(['id', 'invoice_id', 'paid']); 

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);

        try {
            $result = $this->inv_payment_repository->update($payment, compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Payment', $th);
        }

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment updated successfully']);
    }    

    /**
     * Delete payment from storage
     */
    public function delete_payment($id)
    {
        $payment = InvoicePayment::find($id);
        try {
            $this->inv_payment_repository->delete($payment);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Payment', $th);
        }

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment deleted successfully']);
    }

    /**
     * Fetch client invoices
     */
    public function client_invoices(Request $request)
    {
        $w = $request->search; 
        $currency_id = $request->currency_id;
        $query = Invoice::query()->whereHas('currency', function($q) use($currency_id) {
            if ($currency_id) $q->where('currency_id', $currency_id);
            else $q->where('rate', 1);
        })
        ->where('customer_id', $request->customer_id)->whereIn('status', ['due', 'partial']);

        if ($w) $invoices = $query->where('notes', 'LIKE', "%{$w}%")->orderBy('invoiceduedate', 'ASC')->limit(6)->get();
        else $invoices = $query->orderBy('invoiceduedate', 'ASC')->get();
            
        return response()->json($invoices);
    }

    /**
     * Fetch unallocated payments
     */
    public function unallocated_payment(Request $request)
    {
        $pmt = InvoicePayment::where(['customer_id' => $request->customer_id, 'is_allocated' => 0])
            ->with(['account' => function ($q) {
                $q->select(['id', 'holder']);
            }])->first();

        return response()->json($pmt);
    }

    /**
     * Print Customer Payment Receipt
     */
    public function print_payment(InvoicePayment $paidinvoice)
    {
        $company = Company::find(auth()->user()->ins) ?: new Company;
        
        $html = view('focus.invoices.print_payment', ['resource' => $paidinvoice, 'company' => $company])->render();
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

    /**
     * POS Sale Receipt Create Page 
     */
    public function pos(ManagePosRequest $request, RegistersController $register)
    {
        if (!$register->status()) return view('focus.invoices.pos.open_register');

        $tid = Invoice::where('ins', auth()->user()->ins)->max('tid');
        $customer = Customer::first();
        $currencies = Currency::all();
        $terms = Term::all();
        $additionals = Additional::all();
        $defaults = ConfigMeta::get()->groupBy('feature_id');
        
        $pos_account = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['sale_product_income', 'service_income']))
            ->first(['id', 'holder']);
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'bank'))
            ->get(['id', 'holder', 'number']);
        
        $params = compact('customer', 'accounts', 'pos_account', 'tid', 'currencies', 'terms', 'additionals', 'defaults');
        return view('focus.invoices.pos.create', $params)->with(product_helper());
    }

    /**
     * POS Sale Receipt Store Resource
     */
    public function pos_store(CreateInvoiceRequest $request)
    {
        $request->validate(['customer_id' => 'required']);
        if (count(array_filter($request->only('is_pay', 'pmt_reference', 'p_account'))) < 3) {
            throw ValidationException::withMessages(['Payment Reference and Payment Account required']);
        }
        
        try {
            $result = $this->pos_repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'Error', 
                    'message' => 'Error Processing POS Transaction! Try again later',
                    'error_message' => $th->getMessage(),
                ]);
            }
            return errorHandler('Error Creating POS Transaction', $th);
        }
        
        return response()->json([
            'status' => 'Success', 
            'message' => 'POS Transaction Done Successfully',
            'invoice' => $result,
        ]);
    }

    public function send_sms_and_email(Request $request, $invoice_id)
    {
        // dd($request->all(),$invoice_id);
        try {
            DB::beginTransaction();
            $invoice = Invoice::find($invoice_id);
            $input = $request->only(['send_email_sms','phone_number','email']);
            $input['invoice_id'] = $invoice->id;
            $input['ins'] = auth()->user()->ins;
            $input['user_id'] = auth()->user()->id;
            $input['created_at'] = now();
            $input['updated_at'] = now();
            DB::table('send_invoices')->insert($input);
            $this->send_payment_link($invoice, $input);
            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            return errorHandler('Error Sending Invoice',$th);
        }
        return back()->with('flash_success', 'Sending Invoice Successfully!!');
    }
    public function send_payment_link($invoice, $input){
        if ($invoice) {
            $customerName = @$invoice->customer->company;
            $customerEmail = $input['email'];
            $customerPhone = $input['phone_number'];
            $customer_id = $invoice->customer_id;
            
            // Find the company
            $company = Company::find(auth()->user()->ins);
            $email_setting = EmailSetting::find(auth()->user()->ins);
            $companyName = "From " . Str::title($company->sms_email_name) . ":";
            $invoice_no = gen4tid('INV-',$invoice->tid);
        
            // Initialize email and SMS data
            $emailInput = [
                'subject' => "Invoice #{{ $invoice_no }} from {$company->cname}",
                'mail_to' => $customerEmail,
                'name' => $customerName,
            ];
            
            $smsData = [
                'user_type' => 'customer',
                'delivery_type' => 'now',
                'message_type' => 'single',
                'phone_numbers' => $customerPhone,
                'sent_to_ids' => $customer_id,
            ];
            $secureToken = hash('sha256', $invoice->id . env('APP_KEY'));
            $link = route('invoice', [
                'invoice_id' => $invoice->id,
                'token' => $secureToken
            ]);
        
            // Handle each status case
            if ($invoice) {
                // For 
                $emailInput['text'] = "Dear $customerName, 
                We hope this message finds you well. Please find your invoice attached for your recent transaction with us.
                Invoice Details:
                    - Invoice Number: {$invoice_no}
                    - Invoice Date: {$invoice->invoicedate}
                    - Total Amount: " . number_format($invoice->total, 2) . "
                Thank you for choosing {$company->cname}. If you have any questions regarding this invoice or need assistance, feel free to reach out to us at {$email_setting->customer_statement_email_to}.
                We appreciate your prompt attention to this matter.
                Best regards,
                {$company->cname}. 
                {$link}";
                $message = $companyName . " Dear $customerName, We hope this message finds you well,\n\n";
                $message .= "Your invoice is ready. Please view it using the link below:\n\n";
                $message .= "{$link}\n\n";
                $message .= "Thank you for choosing us!\n\n";
                $message .= "Best regards,\n";
                $smsText = $message;
            } 
        
            // Only proceed 
            if (isset($smsText)) {
                if($input['send_email_sms'] == 'both' || $input['send_email_sms'] == 'sms')
                {
                    // Prepare SMS data
                    $smsData['subject'] = $smsText;
                    $cost_per_160 = 0.6;
                    $charCount = strlen($smsText);
                    $blocks = ceil($charCount / 160);
                    $smsData['characters'] = $charCount;
                    $smsData['cost'] = $cost_per_160;
                    $smsData['user_count'] = 1;
                    $smsData['total_cost'] = $cost_per_160*$blocks;
                    // Send SMS and email
                    $smsResult = SendSms::create($smsData);
                    (new RosesmsRepository(auth()->user()->ins))->textlocal($customerPhone, $smsText, $smsResult);
                }

                if($input['send_email_sms'] == 'both' || $input['send_email_sms'] == 'email')
                {
                    $email = (new RosemailerRepository(auth()->user()->ins))->send($emailInput['text'], $emailInput);
                    $email_output = json_decode($email);
                    if ($email_output->status === "Success"){

                        $email_data = [
                            'text_email' => $emailInput['text'],
                            'subject' => $emailInput['subject'],
                            'user_emails' => $emailInput['mail_to'],
                            'user_ids' => $customer_id,
                            'ins' => auth()->user()->ins,
                            'user_id' => auth()->user()->id,
                            'status' => 'sent'
                        ];
                        SendEmail::create($email_data);
                    }
                }
            }
        }
    }

    /**
     * Invoice PDF Print
     */
    public function invoicePDF($invoice_id, $token)
    {
        $validToken = hash('sha256', $invoice_id . config('app.key'));
        if ($token !== $validToken) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access'], 403);
        }
        $invoice = Invoice::withoutGlobalScopes()->find($invoice_id);
        $company = Company::find($invoice->ins) ?: new Company;
        $viewPath = 'focus.bill.print_invoice';
        if (config('services.efris.base_url')) {
            $viewPath = 'focus.bill.print_efris_invoice';
        }

        $html = view($viewPath, ['resource' => $invoice, 'company' => $company])->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        return Response::stream($pdf->Output('invoice.pdf', 'I'), 200, $headers);
    }

    /**
     * EFRIS Invoice Upload
     */
    public function efrisInvoiceUpload(Request $request)
    {
        $request->validate(['invoice_id' => 'required']);        
        try {
            $invoice = Invoice::findOrFail($request->invoice_id);
            $invoice['products'] = $invoice->products()
                ->whereHas('product_variation', fn($q) => $q->whereHas('product')->whereHas('efris_good'))
                ->with(['product_variation.product', 'product_variation.efris_good'])
                ->orderBy('id', 'ASC')
                ->get();
            
            $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
            $contentData = $controller->invoiceUpload($invoice);
            
            $invoice->refresh();
            $invoice->update([
                'efris_invoice_id' => @$contentData['basicInformation']['invoiceId'],
                'efris_invoice_no' => @$contentData['basicInformation']['invoiceNo'], 
                'efris_qr_code' => @$contentData['summary']['qrCode'], 
                'efris_antifakecode' => @$contentData['basicInformation']['antifakeCode'],
                'efris_issued_date' => @$contentData['basicInformation']['issuedDate'],
                'efris_reference_no' => @$contentData['sellerDetails']['referenceNo'],
            ]);

            return  response()->json(['status' => 'Success', 'message' => 'Sale Invoice Posted Successfully', 'data' => $contentData]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['status' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getQrCodeImage($name, $resource)
    {
        try {
            $path = Storage::disk('public')->path('qr/' . $name . '.png');
            if (is_file($path)) return $path;
    
            $qrCode = new \Endroid\QrCode\QrCode($resource);
            $qrCode->writeFile($path);
    
            return $path;
        } catch (\Throwable $th) {
            return '';
        }
    }

    /**
     * Query EFRIS Invoice
     */
    public function queryInvoice()
    {
        $invoiceNo = request('invoice_no');
        try {
            $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
            $result = $controller->queryInvoices($invoiceNo);
            return response()->json($result);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
