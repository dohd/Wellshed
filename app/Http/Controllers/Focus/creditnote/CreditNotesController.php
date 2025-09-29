<?php

namespace App\Http\Controllers\Focus\creditnote;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\cuInvoiceNumber\ControlUnitInvoiceNumberController;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\additional\Additional;
use App\Models\classlist\Classlist;
use App\Models\Company\Company;
use App\Models\creditnote\CreditNote;
use App\Models\creditnote\CreditNoteItem;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Repositories\Focus\creditnote\CreditNoteRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Storage;

class CreditNotesController extends Controller
{
  /**
   * variable to store the repository object
   * @var CreditNoteRepository
   */
  protected $repository;

  /**
   * contructor to initialize repository object
   * @param CreditNoteRepository $repository ;
   */
  public function __construct(CreditNoteRepository $repository)
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
    $is_debit = request('is_debit');

    return new ViewResponse('focus.creditnotes.index', compact('is_debit'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $is_debit = request('is_debit');
    $tid = $is_debit == 1? CreditNote::where('is_debit', 1)->max('tid')+1 : CreditNote::where('is_debit', 0)->max('tid')+1;
    $classlists = Classlist::all();
    $tax_rates = Additional::all();
    $accounts = Account::whereNull('system')
      ->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))
      ->get(['id', 'holder']);
    $customers = Customer::whereHas('currency')->get(['id', 'company', 'name']);
    $currencies = Currency::all();

    $cuNo = (new ControlUnitInvoiceNumberController())->retrieveCuInvoiceNumber();
    if (!empty($cuNo)) $newCuInvoiceNo = explode('KRAMW', auth()->user()->business->etr_code)[1] . $cuNo;
    else $newCuInvoiceNo = '';

    return new ViewResponse('focus.creditnotes.create', compact('currencies', 'customers', 'accounts', 'tax_rates', 'classlists', 'tid', 'is_debit', 'newCuInvoiceNo'));
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
      'invoice_id' => 'required',
      'date' => 'required',
      'taxable' => 'required',
      'subtotal' => 'required',
      'tax' => 'required',
      'total' => 'required',
    ]);    

    try {
      $result = $this->repository->create($request->except('_token', 'load_items_from'));
    } catch (\Exception $e) {
      $is_debit = $request->is_debit;
      return errorHandler($is_debit? 'Error creating Debit Note' : 'Error creating Credit Note', $e);
    }

    $msg = 'Credit Note created successfully';
    $route = route('biller.creditnotes.index');
    if ($result['is_debit']) {
      $msg = 'Debit Note created successfully';
      $route = route('biller.creditnotes.index', 'is_debit=1');
    }
    
    return new RedirectResponse($route, ['flash_success' => $msg]);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show(CreditNote $creditnote)
  {
    if ($creditnote['efris_qr_code']) {
      $name = 'EfrisCreditNote-' . $creditnote['efris_creditnote_no'];
      $resource = $creditnote['efris_qr_code'];
      $creditnote['qrCodeImage'] = $this->getQrCodeImage($name, $resource);
    }
    
    return view('focus.creditnotes.view', compact('creditnote'));
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\creditnote\CreditNote  $creditnote
   * @return \Illuminate\Http\Response
   */
  public function edit(CreditNote $creditnote)
  {
    $tid = $creditnote['tid'];
    $is_debit = $creditnote['is_debit'];
    $classlists = Classlist::all();
    $tax_rates = Additional::all();
    $accounts = Account::whereNull('system')->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))->get(['id', 'holder']);
    $customers = Customer::whereHas('currency', fn($q) => $q->where('rate', 1))->get(['id', 'company', 'name']);
    $currencies = Currency::all();

    $is_inv_items = $creditnote->items()->whereHas('invoice_item')->exists();
    $creditnote['is_inv_items'] = $is_inv_items? 1 : 0;

    // line structure for old credit notes for compatibility
    if (!$creditnote->items()->exists()) {
      $item = new CreditNoteItem([
        'creditnote_id' => $creditnote['id'],
        'numbering' => 1,
        'name' => $creditnote['note'],
        'unit' => 'Item',
        'qty' => 1,
        'rate' => $creditnote['subtotal'],
      ]);
      $creditnote['items'] = collect([$item]);
    }
    
    return new ViewResponse('focus.creditnotes.edit', compact('currencies','customers', 'accounts', 'tax_rates', 'classlists', 'tid', 'creditnote', 'is_debit'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(CreditNote $creditnote, Request $request)
  {
    $request->validate([
      'date' => 'required',
      'taxable' => 'required',
      'subtotal' => 'required',
      'tax' => 'required',
      'total' => 'required',
    ]);   

    try {
      $this->repository->update($creditnote, $request->except('_token', '_method', 'load_items_from'));
    } catch (\Throwable $th) {
      $is_debit = $request->is_debit;
      return errorHandler($is_debit? 'Error updating Debit Note' : 'Error updating Credit Note', $th);
    }
    
    $msg = 'Credit Note updated successfully';
    $route = route('biller.creditnotes.index');
    if ($creditnote['is_debit']) {
      $msg = 'Debit Note updated successfully';
      $route = route('biller.creditnotes.index', 'is_debit=1');
    }
    return new RedirectResponse($route, ['flash_success' => $msg]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy(CreditNote $creditnote)
  {
    $is_debit = $creditnote['is_debit'];
    try {
      $this->repository->delete($creditnote);
    } catch (\Throwable $th) {
      return errorHandler($is_debit? 'Error deleting Debit Note' : 'Error deleting Credit Note', $th);
    }
    
    $msg = 'Credit Note deleted successfully';
    $route = route('biller.creditnotes.index');
    if ($is_debit) {
      $msg = 'Debit Note deleted successfully';
      $route = route('biller.creditnotes.index', 'is_debit=1');
    }
    return new RedirectResponse($route, ['flash_success' => $msg]);
  }

  /**
   * Invoices overdue by up to 6 months
   */
  public function search_invoice(Request $request)
  {
    $w = $request->search;
    $currency_id = $request->currency_id;
    $customer_id = $request->customer_id;
    // dd($currency_id);
    // $start_date = Carbon::now()->subMonths(6)->format('Y-m-d');

    $invoices = Invoice::when($currency_id, fn($q) => $q->where('currency_id', $currency_id))
      ->when(!$currency_id, fn($q) => $q->whereHas('currency'))
      ->where('customer_id', $customer_id)
      ->whereIn('status', ['due', 'partial'])
      ->where(fn($q) => $q->where('tid', 'LIKE', "%{$w}%")
      ->orWhere('notes', 'LIKE', "%{$w}%"))
      ->limit(6)
      ->get();

    return response()->json($invoices);
  }

  /**
   * Load Invoice Items
   */
  public function load_invoice_items(Request $request)
  {
    $invoiceItems = InvoiceItem::where('invoice_id', request('invoice_id'))->get()
    ->map(function($v) {
      // account for missing subtotal
      if (!boolval($v['product_subtotal'])) $v['product_subtotal'] = $v['product_price'];
      return $v;
    });
    
    return response()->json($invoiceItems);
  }

  /**
   * Print Credit Note or Debit Note
   */
  public function print_creditnote(CreditNote $creditnote)
  {
    $company = Company::find(auth()->user()->ins) ?: new Company;
    $viewPath = 'focus.creditnotes.print_creditnote';
    if (config('services.efris.base_url')) {
      $viewPath = 'focus.creditnotes.print_efris_creditnote';
    }

    $html = view($viewPath, ['resource' => $creditnote, 'company' => $company])->render();
    $pdf = new \Mpdf\Mpdf(config('pdf'));
    $pdf->WriteHTML($html);
    $headers = array(
      "Content-type" => "application/pdf",
      "Pragma" => "no-cache",
      "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
      "Expires" => "0"
    );

    return Response::stream($pdf->Output('creditnote.pdf', 'I'), 200, $headers);
  }

  
  /**
   * EFRIS Credit Note Upload
   */
  public function efrisCreditNoteUpload(Request $request)
  {
    $request->validate(['creditnote_id' => 'required']);        
    try {
      $creditNote = CreditNote::findOrFail($request->creditnote_id);
      $creditNote['items'] = $creditNote->items()
        ->where(function($q) {
          $q->whereHas('productvar', fn($q) => $q->whereHas('product')->whereHas('efris_good'))
          ->orWhereHas('invoice_item', function($q) {
            $q->whereHas('product_variation', fn($q) => $q->whereHas('product')->whereHas('efris_good'));
          });
        })
        ->with([
          'productvar.product', 'productvar.efris_good', 
          'invoice_item.product_variation.product', 'invoice_item.product_variation.efris_good'
        ])
        ->get();
      
      $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
      $contentData = $controller->creditNoteUpload($creditNote);

      $creditNote->refresh();
      $creditNote->update(['efris_reference_no' => @$contentData['referenceNo']]);

      return  response()->json(['status' => 'Success', 'message' => 'Credit Note Posted Successfully', 'data' => $contentData]);
    } catch (\Exception $e) {
      Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
      return response()->json(['status' => 'Error', 'message' => $e->getMessage()], 500);
    }
  }

  /**
   * EFRIS Query Credit Note Status
   */
  public function efrisQueryCreditNoteStatus(Request $request)
  {
    $request->validate(['creditnote_id' => 'required']);  
    try {      
      $creditNote = CreditNote::findOrFail($request->creditnote_id);
      $referenceNo = $creditNote->efris_reference_no;
      
      // query credit note using reference number
      $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
      $contentData = $controller->queryCreditNoteStatus($referenceNo);
      if (@$contentData['records']) {
        $record = $contentData['records'][0];
        $invoiceNo = @$record['invoiceNo'];
        $key = $record['approveStatus'];
        $approvalStatuses = [
          "101" => "approved",
          "102" => "submitted",
          "103" => "rejected",
          "104" => "voided",
        ];
        $creditNote->update([
          'efris_creditnote_id' => $record['id'],
          'efris_creditnote_no' => $invoiceNo,
          'efris_approval_status' => $key, 
          'efris_approval_status_name' => $approvalStatuses[$key],
        ]);
        
        // if approved credit note
        if ($invoiceNo) {
          // query invoice details
          $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
          $contentData = $controller->queryInvoices($invoiceNo);
          $qrCode = @$contentData['summary']['qrCode'];
          if ($qrCode) {
            $creditNote->update([
              'efris_qr_code' => $qrCode,
              'efris_ori_invoice_no' => @$contentData['basicInformation']['oriInvoiceNo'],
              'efris_antifakecode' => @$contentData['basicInformation']['antifakeCode'],
              'efris_issued_date' => @$contentData['basicInformation']['issuedDate'],
            ]);
            return redirect()->back()->with('flash_success', 'Credit note is approved');
            // return  response()->json(['status' => 'Success', 'message' => 'Credit note approved', 'data' => $contentData]);
          }
        }
      }

      return redirect()->back()->with('flash_success', 'Credit note submitted, awaiting approval');
      // return  response()->json(['status' => 'Success', 'message' => 'Credit note submitted, awaiting approval', 'data' => $contentData]);
    } catch (\Exception $e) {
      Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
      return redirect()->back()->with('flash_error', $e->getMessage());
      // return response()->json(['status' => 'Error', 'message' => $e->getMessage()], 500);
    }
  }

  public function getQrCodeImage($name, $resource)
  {
    $path = Storage::disk('public')->path('qr/' . $name . '.png');
    if (is_file($path)) return $path;

    $qrCode = new \Endroid\QrCode\QrCode($resource);
    $qrCode->writeFile($path);

    return $path;
  }
}
