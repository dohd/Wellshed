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
namespace App\Http\Controllers\Focus\report;

use App\Http\Requests\Focus\report\ManageReports;
use App\Models\Company\ConfigMeta;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\transaction\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\items\PurchaseItem;
use App\Models\items\PurchaseorderItem;
use DB;
use Illuminate\Support\Facades\Response;

class SummaryController extends Controller
{
    public function index(ManageReports $summary)
    {

    }

    public function summary(ManageReports $summary)
    {
        switch ($summary->section) {
            case 'income':
                $lang['title'] = trans('meta.income_summary');
                $lang['module'] = 'income';
                if ($summary->calculate) {
                    $income_category = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 8)->first('feature_value');
                    $income = Transaction::where('trans_category_id', $income_category['feature_value'])->whereBetween('tr_date', [datetime_for_database($summary->from_date), datetime_for_database($summary->to_date)])->sum('credit');
                    $lang['calculate'] = trans('meta.income_summary') . ' &nbsp; &nbsp; &nbsp;' . dateFormat($summary->from_date) . ' - ' . dateFormat($summary->to_date) . ' &nbsp; &nbsp; &nbsp;' . trans('general.total') . ' ' . amountFormat($income);
                }
                return view('focus.summary.summary', compact('lang'));
                break;
            case 'expense':
                $lang['title'] = trans('meta.expense_summary');
                $lang['module'] = 'expense';
                if ($summary->calculate) {
                    $income_category = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 10)->first('feature_value');
                    $income = Transaction::where('trans_category_id', $income_category['feature_value'])->whereBetween('tr_date', [datetime_for_database($summary->from_date), datetime_for_database($summary->to_date)])->sum('debit');
                    $lang['calculate'] = trans('meta.expense_summary') . ' &nbsp; &nbsp; &nbsp;' . dateFormat($summary->from_date) . ' - ' . dateFormat($summary->to_date) . ' &nbsp; &nbsp; &nbsp;' . trans('general.total') . ' ' . amountFormat($income);
                }
                return view('focus.summary.summary', compact('lang'));
                break;
            case 'sale':
                $lang['title'] = trans('meta.sale_summary');
                $lang['module'] = 'sale';
                if ($summary->calculate) {
                    $sales = InvoiceItem::whereBetween('created_at', [datetime_for_database($summary->from_date), datetime_for_database($summary->to_date)])
                        ->whereHas('invoice', function ($query) {
                            $query->where('ins', auth()->user()->ins);
                        })
                        ->whereHas('variation')
                        ->orWhereHas('quote.verified_products.product_variation')
                        ->with(['variation', 'quote.verified_products.product_variation'])
                        ->get();

                    $directProducts = InvoiceItem::select(
                            'product_id',
                            'description as product_name',
                            DB::raw('DATE(created_at) as date'),
                            'product_qty as product_qty',
                            'product_price as product_price',
                        )
                        ->whereNotNull('product_id')
                        ->whereBetween('created_at', [datetime_for_database($summary->from_date), datetime_for_database($summary->to_date)])
                        ->whereHas('invoice', function ($query) {
                            $query->where('ins', auth()->user()->ins);
                        })
                        ->get();
                    
                    // Step 2: Retrieve products via Quotes and VerifiedProduct
                    $quoteProducts = InvoiceItem::join('quotes', 'invoice_items.quote_id', '=', 'quotes.id')
                        ->join('verified_items', 'quotes.id', '=', 'verified_items.quote_id')
                        ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                        ->select(
                            'verified_items.product_id',
                            'verified_items.product_name as product_name',
                            'invoice_items.created_at as date',
                            'verified_items.product_qty as product_qty',
                            'verified_items.product_subtotal as product_price',
                            // DB::raw('SUM(rose_invoice_items.product_amount) as amount')
                            )
                        ->whereNotNull('invoice_items.quote_id')
                        ->where('verified_items.product_id', '>', 0)
                        ->whereBetween('invoice_items.created_at', [datetime_for_database($summary->from_date), datetime_for_database($summary->to_date)])
                        ->where('invoices.ins', auth()->user()->ins)
                        ->get();
                    
                    // Step 3: Combine and Group by product_id
                    $sale_items = $directProducts->concat($quoteProducts);

                    
                    $html = view('focus.summary.pdf.sales', compact('sale_items', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output('sales_summary.pdf', 'I'), 200, $headers);
                    $lang['calculate'] = trans('meta.sale_summary') . ' &nbsp; &nbsp; &nbsp;' . dateFormat($summary->from_date) . ' - ' . dateFormat($summary->to_date) . ' &nbsp; &nbsp; &nbsp;' . trans('general.total') . ' ' . amountFormat($sales);
                }
                
                return view('focus.summary.summary', compact('lang'));
                break;

            case 'purchase':
                $lang['title'] = trans('meta.purchase_summary');
                $lang['module'] = 'purchase';
                if ($summary->calculate) {
                    $poitems = PurchaseorderItem::whereBetween('created_at', [datetime_for_database($summary->from_date), datetime_for_database($summary->to_date)])
                    ->select(
                        'description as product_name', 
                        'rate as product_price', 
                        'qty as product_qty',
                        'amount as amount',
                    )
                    ->with(['grn_items.goodsreceivenote.bill'])
                    ->whereHas('grn_items.goodsreceivenote.bill')
                    ->get();
                    $purchase_items = PurchaseItem::whereBetween('created_at', [datetime_for_database($summary->from_date), datetime_for_database($summary->to_date)])
                    ->select(
                        'description as product_name', 
                        'rate as product_price', 
                        'qty as product_qty',
                        'amount as amount',
                    )
                    ->get();
                    $sale_items = $poitems->concat($purchase_items);
                    $html = view('focus.summary.pdf.sales', compact('sale_items', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output('purchase_summary.pdf', 'I'), 200, $headers);
                    $lang['calculate'] = trans('meta.purchase_summary') . ' &nbsp; &nbsp; &nbsp;' . dateFormat($summary->from_date) . ' - ' . dateFormat($summary->to_date) . ' &nbsp; &nbsp; &nbsp;' . trans('general.total') . ' ' . amountFormat($sales);
                }
                return view('focus.summary.summary', compact('lang'));
                break;

            case 'products':
                $lang['title'] = trans('meta.products_summary');
                $lang['module'] = 'products';
                if ($summary->calculate) {
                    $fromDate = datetime_for_database($summary->from_date);
                    $toDate = datetime_for_database($summary->to_date);

                    $product_id = $summary->product_id;

                    $invoiceItems = InvoiceItem::whereBetween('created_at', [$fromDate, $toDate])
                        ->where(function ($query) use ($product_id) {
                            $query->whereHas('variation', function ($q) use ($product_id) {
                                return $q->where('id', '=', $product_id);
                            })
                            ->orWhereHas('quote.verified_products', function ($q) use ($product_id) {
                                return $q->whereHas('product_variation', function ($q) use ($product_id) {
                                    return $q->where('id', '=', $product_id);
                                });
                            });
                        })
                        ->where('invoices.ins', auth()->user()->ins)
                        ->with(['variation', 'quote.verified_products.product_variation'])
                        ->get();

                    $totalSales = 0;
                    $sale_items = [];

                    // Process each invoice item
                    foreach ($invoiceItems as $item) {
                        // Check if the item is directly from inventory
                        if ($item->variation && $item->variation->id == $product_id) {
                            
                            $sale_items[] = [
                                'product_id' => $item->variation->id,
                                'product_name' => $item->variation->name,
                                'product_qty' => $item->product_qty,
                                'date' => $item->created_at->format('Y-m-d'),
                                'product_price' => $item->product_price,
                                'product_amount' => $item->product_amount
                            ];
                        }
                        // Check if the item is from a quote and get the verified product inventories
                        elseif ($item->quote) {
                            foreach ($item->quote->verified_products as $verifiedProduct) {
                                if ($verifiedProduct->product_variation && $verifiedProduct->product_variation->id == $product_id) {
                                    
                                    $sale_items[] = [
                                        'product_id' => $verifiedProduct->product_variation->id,
                                        'product_name' => $verifiedProduct->product_variation->name,
                                        'product_qty' => $item->product_qty,
                                        'date' => $item->created_at->format('Y-m-d'),
                                        'product_price' => $verifiedProduct->product_subtotal,
                                        'product_amount' => $item->product_amount
                                    ];
                                }
                            }
                        }
                    }
                    // dd($sale_items);
                    $html = view('focus.summary.pdf.sales', compact('sale_items', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output('sales_summary.pdf', 'I'), 200, $headers);
                
                    $lang['calculate'] = trans('meta.products_summary') . ' &nbsp; &nbsp; &nbsp;' . dateFormat($summary->from_date) . ' - ' . dateFormat($summary->to_date) . ' &nbsp; &nbsp; &nbsp;' . trans('general.total') . ' ' . numberFormat($totalSales);
                }
                return view('focus.summary.summary', compact('lang'));
                break;
        }
    }
}
