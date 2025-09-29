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

namespace App\Http\Controllers\Focus\tax_report;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\additional\Additional;
use App\Models\Company\Company;
use App\Models\creditnote\CreditNote;
use App\Models\invoice\Invoice;
use App\Models\tax_report\TaxReport;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\tax_report\TaxReportRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaxReportsController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaxReportRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TaxReportRepository $repository ;
     */
    public function __construct(TaxReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.tax_reports.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $additionals = Additional::all();
        $taxRange = $additionals->pluck('value')->unique()->toArray();

        $month = date('m')-1? date('m')-1 : 12;
        $year = date('m')-1? date('Y') : date('Y')-1;
        $prev_month = strlen($month) == 1? "0{$month}-{$year}" : "{$month}-{$year}";
        
        return view('focus.tax_reports.create', compact('additionals', 'taxRange', 'prev_month'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate(['record_month' => 'required', 'return_month' => 'required']);
        
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Exception $e){

            return "Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . "  | & Line: " . $e->getLine();
        }

        return new RedirectResponse(route('biller.tax_reports.index'), ['flash_success' => 'Tax Filing Report Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  TaxReport $tax_report
     * @return \Illuminate\Http\Response
     */
    public function edit(TaxReport $tax_report)
    {
        $additionals = Additional::all();
        $taxRange = $additionals->pluck('value')->unique()->toArray();
        
        return view('focus.tax_reports.edit', compact('tax_report', 'additionals', 'taxRange'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  TaxReport $tax_report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TaxReport $tax_report)
    {
        $request->validate(['record_month' => 'required', 'return_month' => 'required']);

        try {
            $this->repository->update($tax_report, $request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Tax Filing Report', $th);
        }

        return new RedirectResponse(route('biller.tax_reports.index'), ['flash_success' => 'Tax Filing Report Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  TaxReport $tax_report
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaxReport $tax_report)
    {
        try {
            $this->repository->delete($tax_report);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Tax Report', $th);
        }

        return new RedirectResponse(route('biller.tax_reports.index'), ['flash_success' => 'Tax Report Deleted Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  TaxReport $tax_report
     * @return \Illuminate\Http\Response
     */
    public function show(TaxReport $tax_report)
    {
        return view('focus.tax_reports.view', compact('tax_report'));
    }

    /**
     * Display filed report
     */
    public function filed_report()
    {
        $tax_reports = TaxReport::get(['id', 'note']);
        $company = Company::find(auth()->user()->ins, ['id', 'etr_code']);
        $additionals = Additional::all();

        $month = date('m')-1? date('m')-1 : 12;
        $year = date('m')-1? date('Y') : date('Y')-1;
        $prev_month = strlen($month) == 1? "0{$month}-{$year}" : "{$month}-{$year}";

        return view('focus.tax_reports.filed_report', compact('tax_reports', 'company', 'additionals', 'prev_month'));
    }

    /**
     * Sales to be filed (Tax output)
     * 
     */
    public function get_sales()
    {
        $sale_month = explode('-', request('sale_month', '0-0'));
        $month = current($sale_month);
        $year = end($sale_month);
        
        // invoices
        $invoices = Invoice::when($month, fn($q) => $q->whereMonth('invoicedate', $month)->whereYear('invoicedate', $year))
            ->where('tid', '>', 0)
            ->where(function ($q) {
                $q->doesntHave('invoice_tax_reports');
                $q->orWhereHas('invoice_tax_reports', fn($q) =>  $q->where('is_filed', 0));
            })
            ->get()
            ->map(function ($v) {
                $v_mod = clone $v;
                $isFx = +$v->fx_curr_rate && +$v->fx_curr_rate != 1;
                $attr = [
                    'id' => $v->id,
                    'invoice_tid' => $v->tid,
                    'cu_invoice_no' => $v->cu_invoice_no ?: '',
                    'invoice_date' => $v->invoicedate,
                    'tax_pin' => @$v->customer->taxid ?: '',
                    'customer' => @$v->customer->company ?: '',
                    'note' => $v->notes,
                    'subtotal' => $isFx? $v->fx_subtotal : $v->subtotal,
                    'total' => $isFx? $v->fx_total : $v->total,
                    'tax' => $isFx? $v->fx_tax : $v->tax,
                    'tax_rate' => $v->tax_id,
                    'type' => 'invoice',
                    'credit_note_date' => '',
                    'credit_note_tid' => '',
                    'is_tax_exempt' => @$v->customer->is_tax_exempt ?: 0,
                ];
                foreach ($attr as $key => $value) {
                    $v_mod[$key] = $value;
                }
                return $v_mod;
            });

        // credit notes
        $credit_notes = CreditNote::when($month, fn($q) => $q->whereMonth('date', $month)->whereYear('date', $year))
        ->whereHas('invoice')
        ->where(function ($q) {
            $q->doesntHave('credit_note_tax_reports');
            $q->orWhereHas('credit_note_tax_reports', function ($q) {
                $q->where('is_filed', 0);
            });
        })
        ->whereNull('supplier_id')->get()
        ->map(function($v) {
            $v_mod = clone $v;
            $invoice = $v->invoice;
            $isFx = $v->fx_curr_rate && $v->fx_curr_rate != 1;
            $attr = [
                'id' => $v->id,
                'credit_note_tid' => $v->tid,
                'invoice_date' => $v->date,
                'tax_pin' => @$v->customer->taxid ?: '',
                'customer' => @$v->customer->company ?: '',
                'note' => 'Credit Note',
                'subtotal' =>  $isFx? -$v->fx_subtotal : -$v->subtotal,
                'total' => $isFx? -$v->fx_total : -$v->total,
                'tax' =>  $isFx? -$v->fx_tax : -$v->tax,
                'tax_rate' => $v->subtotal > 0? round($v->tax/$v->subtotal * 100) : 0,
                'type' => 'credit_note',
                'credit_note_date' => @$invoice->invoicedate,
                'invoice_tid' => @$invoice->tid ?: '',
                'cu_invoice_no' => @$invoice->cu_invoice_no ?: '',
                'is_tax_exempt' => @$v->customer->is_tax_exempt ?: 0,
            ];
            foreach ($attr as $key => $value) {
                $v_mod[$key] = $value;
            }
            return $v_mod;
        }); 
            
        $sales = $invoices->merge($credit_notes);

        return response()->json($sales->toArray());
    }

    /**
     * Purchases to be filed (Tax Input)
     */
    public function get_purchases()
    {
        $purchase_month = explode('-', request('purchase_month', '0-0'));
        $month = current($purchase_month);
        $year = end($purchase_month);
        
        // bills
        $bills = UtilityBill::when($month, fn($q) => $q->whereMonth('date', $month)->whereYear('date', $year))
            ->where('tid', '>', 0)
            ->whereIn('document_type', ['direct_purchase', 'goods_receive_note'])
            ->where(function ($q) {
                $q->doesntHave('purchase_tax_reports');
                $q->orWhereHas('purchase_tax_reports', fn($q) => $q->where('is_filed', 0));
            })
            ->get()
            ->map(function ($v) {
                $v_mod = clone $v;
                $note = '';
                $suppliername = '';
                $supplier_taxid = '';
                if ($v->document_type == 'direct_purchase') {         
                    $purchase = $v->purchase;
                    if ($purchase) {
                        if ($v->tax_rate == 8) {
                            $note .= gen4tid('DP-', $purchase->tid) . ' Fuel';
                        } else $note .= gen4tid('DP-', $purchase->tid) . ' Goods';
                        $suppliername .= $purchase->suppliername;
                        $supplier_taxid .= $purchase->supplier_taxid;
                    } else {
                        if ($v->tax_rate == 8) {
                            $note .= gen4tid('BILL-', $v->tid) . ' Fuel';
                        } else $note .= gen4tid('BILL-', $v->tid) . ' Goods';
                    }                   
                } elseif ($v->document_type == 'goods_receive_note') {
                    $grn = $v->grn;
                    if ($grn) {
                        if ($v->tax_rate == 8) {
                            $note .= gen4tid('Grn-', $grn->tid) . ' Fuel';
                        } else $note .= gen4tid('Grn-', $grn->tid) . ' Goods';
                        $suppliername .= @$grn->supplier->name ?: '';
                        $supplier_taxid .= @$grn->supplier->taxid ?: '';
                    } else {
                        if ($v->tax_rate == 8) {
                            $note .= gen4tid('BILL-', $v->tid) . ' Fuel';
                        } else $note .= gen4tid('BILL-', $v->tid) . ' Goods';
                    }
                }
                
                $isFx = +$v->fx_curr_rate && +$v->fx_curr_rate != 1;
                $attr = [
                    'id' => $v->id,
                    'purchase_date' => $v->date,
                    'tax_pin' => $supplier_taxid ?: @$v->supplier->taxid,
                    'supplier' => $suppliername ?: @$v->supplier->name,
                    'invoice_no' => $v->reference,
                    'note' => $note,
                    'subtotal' => $isFx? $v->fx_subtotal : $v->subtotal,
                    'total' => $isFx? $v->fx_total : $v->total,
                    'tax' => $isFx? $v->fx_tax : $v->tax,
                    'tax_rate' => $v->tax_rate,
                    'type' => 'purchase',
                    'debit_note_date' => '',
                ];
                foreach ($attr as $key => $value) {
                    $v_mod[$key] = $value;
                }
                return $v_mod;
            });
        
        return response()->json($bills->toArray());
    }    
}
