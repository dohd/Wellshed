<?php

namespace App\Http\Controllers\Focus\supplier_creditnote;

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
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\invoice\Invoice;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\InvoiceItem;
use App\Models\items\UtilityBillItem;
use App\Models\supplier\Supplier;
use App\Models\supplier_creditnote\SupplierCreditNote;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\supplier_creditnote\SupplierCreditNoteRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Storage;

class SupplierCreditNotesController extends Controller
{
  /**
   * variable to store the repository object
   * @var SupplierCreditNoteRepository
   */
  protected $repository;

  /**
   * contructor to initialize repository object
   * @param SupplierCreditNoteRepository $repository ;
   */
  public function __construct(SupplierCreditNoteRepository $repository)
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

    return new ViewResponse('focus.supplier_creditnotes.index', compact('is_debit'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $is_debit = request('is_debit');
    $tid = $is_debit == 1? SupplierCreditNote::where('is_debit', 1)->max('tid')+1 : SupplierCreditNote::where('is_debit', 0)->max('tid')+1;
    $classlists = Classlist::all();
    $tax_rates = Additional::all();
    $accounts = Account::whereNull('system')
      ->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))
      ->get(['id', 'holder']);
    $suppliers = Supplier::whereHas('currency')->get(['id', 'company', 'name']);
    $currencies = Currency::all();

    $cuNo = (new ControlUnitInvoiceNumberController())->retrieveCuInvoiceNumber();
    if (!empty($cuNo)) $newCuInvoiceNo = explode('KRAMW', auth()->user()->business->etr_code)[1] . $cuNo;
    else $newCuInvoiceNo = '';

    return new ViewResponse('focus.supplier_creditnotes.create', compact('currencies', 'suppliers', 'accounts', 'tax_rates', 'classlists', 'tid', 'is_debit', 'newCuInvoiceNo'));
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
      'supplier_id' => 'required',
      'date' => 'required',
      'taxable' => 'required',
      'subtotal' => 'required',
      'tax' => 'required',
      'total' => 'required',
    ]);    

    try {
      $result = $this->repository->create($request->except('_token', 'load_items_from'));
    } catch (\Exception $e) {dd($e);
      $is_debit = $request->is_debit;
      return errorHandler($is_debit? 'Error creating Debit Note' : 'Error creating Credit Note', $e);
    }

    $msg = 'Credit Note created successfully';
    $route = route('biller.supplier_creditnotes.index');
    if ($result['is_debit']) {
      $msg = 'Debit Note created successfully';
      $route = route('biller.supplier_creditnotes.index', 'is_debit=1');
    }
    
    return new RedirectResponse($route, ['flash_success' => $msg]);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($creditnote_id)
  {
    $creditnote = SupplierCreditNote::find($creditnote_id);
    if ($creditnote['efris_qr_code']) {
      $name = 'EfrisCreditNote-' . $creditnote['efris_creditnote_no'];
      $resource = $creditnote['efris_qr_code'];
      $creditnote['qrCodeImage'] = $this->getQrCodeImage($name, $resource);
    }
    
    return view('focus.supplier_creditnotes.view', compact('creditnote'));
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\creditnote\CreditNote  $creditnote
   * @return \Illuminate\Http\Response
   */
  public function edit($creditnote_id)
  {
    $creditnote = SupplierCreditNote::find($creditnote_id);
    $tid = $creditnote['tid'];
    $is_debit = $creditnote['is_debit'];
    $classlists = Classlist::all();
    $tax_rates = Additional::all();
    $accounts = Account::whereNull('system')->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))->get(['id', 'holder']);
    $customers = Supplier::whereHas('currency', fn($q) => $q->where('rate', 1))->get(['id', 'company', 'name']);
    $currencies = Currency::all();

    $is_inv_items = $creditnote->items()->whereHas('grn_item')->exists();
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
    
    return new ViewResponse('focus.supplier_creditnotes.edit', compact('currencies','customers', 'accounts', 'tax_rates', 'classlists', 'tid', 'creditnote', 'is_debit'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update($creditnote_id, Request $request)
  {
    $request->validate([
      'date' => 'required',
      'taxable' => 'required',
      'subtotal' => 'required',
      'tax' => 'required',
      'total' => 'required',
    ]);   
    $creditnote = SupplierCreditNote::find($creditnote_id);

    try {
      $this->repository->update($creditnote, $request->except('_token', '_method', 'load_items_from'));
    } catch (\Throwable $th) {
      $is_debit = $request->is_debit;
      return errorHandler($is_debit? 'Error updating Debit Note' : 'Error updating Credit Note', $th);
    }
    
    $msg = 'Credit Note updated successfully';
    $route = route('biller.supplier_creditnotes.index');
    if ($creditnote['is_debit']) {
      $msg = 'Debit Note updated successfully';
      $route = route('biller.supplier_creditnotes.index', 'is_debit=1');
    }
    return new RedirectResponse($route, ['flash_success' => $msg]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($creditnote_id)
  {
    $creditnote = SupplierCreditNote::find($creditnote_id);
    $is_debit = $creditnote['is_debit'];
    try {
      $this->repository->delete($creditnote);
    } catch (\Throwable $th) {
      return errorHandler($is_debit? 'Error deleting Debit Note' : 'Error deleting Credit Note', $th);
    }
    
    $msg = 'Credit Note deleted successfully';
    $route = route('biller.supplier_creditnotes.index');
    if ($is_debit) {
      $msg = 'Debit Note deleted successfully';
      $route = route('biller.supplier_creditnotes.index', 'is_debit=1');
    }
    return new RedirectResponse($route, ['flash_success' => $msg]);
  }

  /**
   * Invoices overdue by up to 6 months
   */
  public function search_grn(Request $request)
  {
      $w = $request->search;
      $currency_id = $request->currency_id;
      $supplier_id = $request->supplier_id;
      $grn_type = $request->grn_type;
      // dd($grn_type,$supplier_id);

      $grns = Goodsreceivenote::query()
          ->when($currency_id, fn($q) => $q->where('currency_id', $currency_id))
          ->when(!$currency_id, fn($q) => $q->whereHas('currency'))
          ->when($grn_type === 'grn_invoiced', function ($q) {
              $q->whereNotNull('invoice_no')
                ->whereHas('bill', fn($q) => $q->whereIn('status', ['due', 'partial']));
          })
          ->when($grn_type === 'grn_not_invoiced', fn($q) => $q->whereNull('invoice_no'))
          ->where('supplier_id', $supplier_id)
          ->where(function ($q) use ($w) {
              $q->where('tid', 'LIKE', "%{$w}%")
                ->orWhere('note', 'LIKE', "%{$w}%");
          })
          ->limit(6)
          ->get();

      return response()->json($grns);
  }

  public function search_bill(Request $request)
  {
    $w = $request->search;
    $currency_id = $request->currency_id;
    $supplier_id = $request->supplier_id;

    $bills = UtilityBill::when($currency_id, fn($q) => $q->where('currency_id', $currency_id))
      ->when(!$currency_id, fn($q) => $q->whereHas('currency'))
      ->where('supplier_id', $supplier_id)
      ->whereIn('status', ['due', 'partial'])
      ->where(fn($q) => $q->where('tid', 'LIKE', "%{$w}%")
      ->orWhere('note', 'LIKE', "%{$w}%"))
      ->limit(6)
      ->get();

    return response()->json($bills);
  }


  /**
   * Load Invoice Items
   */
  public function load_grn_items(Request $request)
  {
    $grn_items = GoodsreceivenoteItem::where('goods_receive_note_id', request('grn_id'))->get()
    ->map(function($v) {
      // account for missing subtotal
      if (!boolval($v['rate'])) $v['subtotal'] = $v['rate'];
      $v['unit'] = $v->purchaseorder_item ? $v->purchaseorder_item->uom : '';
      $v['note'] = $v->purchaseorder_item ? $v->purchaseorder_item->description : '';
      return $v;
    });
    
    return response()->json($grn_items);
  }

  /**
   * Print Credit Note or Debit Note
   */
  public function print_creditnote($creditnote_id)
  {
    $creditnote = SupplierCreditNote::find($creditnote_id);
    $company = Company::find(auth()->user()->ins) ?: new Company;
    $viewPath = 'focus.supplier_creditnotes.print_creditnote';
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
  
  public function getQrCodeImage($name, $resource)
  {
    $path = Storage::disk('public')->path('qr/' . $name . '.png');
    if (is_file($path)) return $path;

    $qrCode = new \Endroid\QrCode\QrCode($resource);
    $qrCode->writeFile($path);

    return $path;
  }
}
