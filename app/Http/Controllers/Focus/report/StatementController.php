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

use App\Http\Responses\RedirectResponse;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Models\items\PurchaseItem;
use App\Models\items\Register;
use App\Models\product\ProductMeta;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Models\warehouse\Warehouse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\report\ManageReports;
use App\Models\creditnote\CreditNote;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\PurchaseorderItem;
use App\Models\items\StockTransferItem;
use App\Models\items\TaxReportItem;
use App\Models\opening_stock\OpeningStock;
use App\Models\sale_return\SaleReturnItem;
use App\Models\stock_adj\StockAdjItem;
use App\Models\stock_issue\StockIssueItem;
use App\Models\utility_bill\UtilityBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class StatementController extends Controller
{
    public function index(ManageReports $reports) {}

    public function account(ManageReports $reports) {}

    /**
     * Show the Form for Generating Statement
     */
    public function statement(ManageReports $reports)
    {
        switch ($reports->section) {
            case 'account':
                $lang['title'] = trans('meta.account_statement');
                $lang['module'] = 'account_statement';
                $accounts = Account::all();
                return view('focus.report.general_statement', compact('accounts', 'lang'));
                break;
            case 'income':
                $lang['title'] = trans('meta.income_statement');
                $lang['module'] = 'income_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'expense':
                $lang['title'] = trans('meta.expense_statement');
                $lang['module'] = 'expense_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'customer':
                $lang['title'] = trans('meta.customer_statement');
                $lang['module'] = 'customer_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'supplier':
                $lang['title'] = trans('meta.supplier_statement');
                $lang['module'] = 'supplier_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'tax':
                $lang['title'] = 'Sales / Purchase Tax Statement';
                $lang['module'] = 'tax_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'stock_transfer':
                $lang['title'] = trans('meta.stock_transfer_statement_warehouse');
                $lang['module'] = 'stock_transfer_statement';
                $warehouses = Warehouse::all();
                return view('focus.report.general_statement', compact('warehouses', 'lang'));
                break;
            case 'stock_transfer_product':
                $lang['title'] = trans('meta.stock_transfer_statement_product');
                $lang['module'] = 'product_transfer_statement';
                $warehouses = Warehouse::all();
                return view('focus.report.general_statement', compact('warehouses', 'lang'));
                break;
            case 'product_statement':
                $lang['title'] = trans('meta.stock_transfer_statement_product');
                $lang['module'] = 'product_statement';
                $warehouses = Warehouse::all();
                return view('focus.report.general_statement', compact('warehouses', 'lang'));
                break;
            case 'product_movement_statement':
                $lang['title'] = 'Product Movement Statement';
                $lang['module'] = 'product_movement_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'product_category_statement':
                $lang['title'] = trans('meta.product_category_statement');
                $lang['module'] = 'product_category_statement';
                $product_categories = Productcategory::all();
                return view('focus.report.general_statement', compact('product_categories', 'lang'));
                break;
            case 'product_warehouse_statement':
                $lang['title'] = trans('meta.product_warehouse_statement');
                $lang['module'] = 'product_warehouse_statement';
                $warehouses = Warehouse::all();
                return view('focus.report.general_statement', compact('warehouses', 'lang'));
                break;
            case 'product_customer_statement':
                $lang['title'] = trans('meta.product_customer_statement');
                $lang['module'] = 'product_customer_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'product_supplier_statement':
                $lang['title'] = trans('meta.product_supplier_statement');
                $lang['module'] = 'product_supplier_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;

            case 'pos_statement':
                $lang['title'] = trans('meta.pos_statement');
                $lang['module'] = 'pos_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
        }
    }

    public function generate_statement(ManageReports $reports)
    {

        switch ($reports->section) {
            case 'account':
                if (!$reports->account) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $account_details = Account::where('id', '=', $reports->account)->first();
                // dd($account_details, $reports->account);
                $lang['title'] = trans('meta.account_statement');
                $lang['title2'] = trans('accounts.account');
                $lang['module'] = 'account_statement';
                $lang['party'] = $account_details->holder . ' (' . trans('accounts.' . $account_details->account_type) . ')' . '<br>' . $account_details->number . '<br>' . $account_details->type;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $account_details->holder);
                break;

            case 'income':
                $account_details = Account::where('id', '=', $reports->account)->first();
                $lang['title'] = trans('meta.income_statement');
                $lang['title2'] = trans('meta.income_statement');
                $lang['module'] = 'income_statement';
                $default_category = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 8)->first('feature_value');
                $category = Transactioncategory::withoutGlobalScopes()->find($default_category['feature_value']);
                $lang['party'] = $category->name;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $category->name);
                $transactions = Transaction::whereBetween('tr_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->where('trans_category_id', '=', $category->id)->get();


                //new code
                $data = [
                    'start_date' => $reports->from_date,
                    'end_date' => $reports->to_date,
                ];
                $dates = $data;
                $dates = array_map(function ($v) {
                    return date_for_database($v);
                }, $dates);

                $q = Account::whereHas('transactions', function ($q) use ($dates) {
                    $q->when($dates, function ($q) use ($dates) {
                        $q->whereBetween('tr_date', $dates);
                    });
                });

                $accounts = $q->get();
                $cog_material = 0;
                $cog_labour = 0;
                $cog_transport = 0;
                foreach ($accounts->where('system', 'cog') as $account) {
                    // project invoice COGs (material, labour, transport)
                    $invoice_trs = $account->transactions()
                        ->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates))
                        ->where('tr_type', 'inv')
                        ->get();
                    $purchase_item_ids = [];
                    foreach ($invoice_trs as $invoice_tr) {
                        if ($invoice_tr->invoice) {
                            foreach ($invoice_tr->invoice->quotes as $quote) {
                                $project = @$quote->project_quote->project;
                                if (!$project) continue;
                                foreach ($project->purchase_items as $i => $item) {
                                    if (in_array($item->id, $purchase_item_ids)) continue;
                                    $purchase_item_ids[] = $item->id;
                                    if ($item->itemproject_id) {
                                        $subtotal = $item->amount - $item->taxrate;
                                        if ($item->type == 'Expense') {
                                            if (preg_match("/transport/i", @$item->account->holder)) {
                                                $cog_transport += $subtotal;
                                            }
                                            if (preg_match("/labour/i", @$item->account->holder)) {
                                                $cog_labour += $subtotal;
                                            }
                                        }
                                        if ($item->type == 'Stock') {
                                            $cog_material += $subtotal;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // stock-issue, sale-return, stock-adj COGs (material)
                    $stock_trs = $account->transactions()
                        ->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates))
                        ->where('tr_type', 'stock')
                        ->get();
                    $stock_bal = $stock_trs->sum('debit') - $stock_trs->sum('credit');
                    // direct expense on COG
                    $bill_trs = $account->transactions()
                        ->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates))
                        ->where('tr_type', 'bill')
                        ->get();
                    $expense_bal = $bill_trs->sum('debit') - $bill_trs->sum('credit');
                    $cog_material += $stock_bal + $expense_bal;
                }

                return $this->print_document('profit_and_loss', $accounts, $dates, 0, $cog_material, $cog_labour, $cog_transport);

                $bg_styles = [
                    'bg-gradient-x-info',
                    'bg-gradient-x-purple',
                    'bg-gradient-x-grey-blue',
                    'bg-gradient-x-danger',
                ];

                break;
            case 'expenses':
                // $account_details = Account::where('id', '=', $reports->account)->first();
                // dd($account_details, $reports->account);
                $lang['title'] = trans('meta.expense_statement');
                $lang['title2'] = trans('meta.expense_statement');
                $lang['module'] = 'expense_statement';
                $default_category = ConfigMeta::where('feature_id', '=', 10)->first('feature_value');
                $category = Transactioncategory::find($default_category['feature_value']);
                // dd($category, $default_category['feature_value']);
                $lang['party'] = $category->name;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $category->name);
                $transactions = Transaction::whereBetween('tr_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->where('trans_category_id', '=', $category->id)->get();

                $purchase_items = PurchaseItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])
                    ->where('type', 'Expense')
                    ->select('id', 'description as product_name', 'rate as product_price', 'qty as product_qty', 'amount', 'created_at')
                    ->get();
                $purchase_order_items = PurchaseorderItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])
                    ->where('type', 'Expense')
                    ->select('id', 'description as product_name', 'rate as product_price', 'qty as product_qty', 'amount', 'created_at')
                    ->get();
                $account_details = $purchase_items->merge($purchase_order_items);
                // dd($account_details, datetime_for_database($reports->from_date));
                $html = view('focus.report.pdf.product', compact('account_details', 'lang'))->render();
                $headers = array(
                    "Content-type" => "application/pdf",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);

                break;

            case 'customer':
                if (!$reports->account) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $account_details = Customer::where('id', '=', $reports->account)->first();
                $lang['title'] = trans('meta.customer_statement');
                $lang['title2'] = trans('customers.customer');
                $lang['module'] = 'customer_statement';
                $lang['party'] = $account_details->name . '<br>' . $account_details->email . '<br>' . $account_details->phone;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $account_details->name);
                break;
            case 'supplier':
                if (!$reports->account) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $account_details = Supplier::where('id', '=', $reports->account)->first();
                $lang['title'] = trans('meta.supplier_statement');
                $lang['title2'] = trans('suppliers.supplier');
                $lang['module'] = 'supplier_statement';
                $lang['party'] = $account_details->name . '<br>' . $account_details->email . '<br>' . $account_details->phone;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $account_details->name);
                break;
        }

        switch ($reports->trans_type) {
            case 'credit':
                $transactions = $account_details->transactions->whereBetween('tr_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->where('credit', '>', 0);
                break;
            case 'debit':
                $transactions = $account_details->transactions->whereBetween('tr_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->where('debit', '>', 0);
                break;
            case 'all':
                $transactions = $account_details->transactions->whereBetween('tr_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                break;
        }


        switch ($reports->output_format) {

            case 'pdf_print':
                $html = view('focus.report.pdf.account', compact('account_details', 'transactions', 'lang'))->render();
                $headers = array(
                    "Content-type" => "application/pdf",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                break;
            case 'pdf':
                $html = view('focus.report.pdf.account', compact('account_details', 'transactions', 'lang'))->render();
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return $pdf->Output($file_name . '.pdf', 'D');
                break;
            case 'csv':
                $headers = array(
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=$file_name.csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $columns = array(trans('transactions.tr_date'), trans('general.description'), trans('transactions.debit'), trans('transactions.credit'), trans('accounts.balance'));
                $callback = function () use ($transactions, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    $balance = 0;

                    foreach ($transactions as $row) {
                        $balance += $row['credit'] - $row['debit'];
                        fputcsv($file, array(dateFormat($row['tr_date']), $row['note'], amountFormat($row['debit']), amountFormat($row['credit']), amountFormat($balance)));
                    }
                    fclose($file);
                };
                return Response::stream($callback, 200, $headers);
                break;
        }
    }

    /**
     * Print document
     */
    public function print_document(string $name, $accounts, array $dates, float $net_profit, $cog_material, $cog_labour, $cog_transport)
    {
        $account_types = ['Assets', 'Equity', 'Expenses', 'Liabilities', 'Income'];
        $params = compact('accounts', 'account_types', 'dates', 'net_profit', 'cog_material', 'cog_labour', 'cog_transport');
        $html = view('focus.accounts.print_' . $name, $params)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        return Response::stream($pdf->Output($name . '.pdf', 'I'), 200, $headers);
    }

    public function generate_tax_statement(ManageReports $reports)
    {
        if (!$reports->from_date)
            return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);

        switch ($reports->tax_type) {
            case 'tax_sales':
                $q = TaxReportItem::query()->where(function ($q) {
                    $q->whereHas('invoice')->orWhereHas('credit_note');
                })->where('is_filed', request('is_filed', 1));

                $q->when(request('tax_report_id'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('id', request('tax_report_id')));
                })->when(request('record_month'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->whereBetween('record_month', [request('record_month'), request('month_to')]));
                })->when(request('return_month'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('return_month', request('return_month')));
                })->when(request('tax_group') != '', function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('tax_group', request('tax_group')));
                });

                $sale_month = explode('-', request('record_month', '0-0'));
                $month = current($sale_month);
                $year = end($sale_month);

                $month_to = explode('-', request('month_to', '0-0'));
                $endMonth = current($month_to);
                $endYear = end($month_to);

                // Construct start and end date
                $startDate = "$year-$month-01";
                $endDate = date("Y-m-t", strtotime("$endYear-$endMonth-01"));

                // invoices
                $invoices = Invoice::whereBetween('invoicedate', [$startDate, $endDate])
                    ->where('tid', '>', 0)
                    ->where(function ($q) {
                        $q->doesntHave('invoice_tax_reports');
                        $q->orWhereHas('invoice_tax_reports', fn($q) =>  $q->where('is_filed', 0));
                    })
                    ->when(request('tax_group') != '', function ($q) {
                        $q->where('tax_id', request('tax_group'));
                    })
                    ->get()
                    ->map(function ($v) {
                        $v_mod = clone $v;
                        $attr = [
                            'id' => $v->id,
                            'invoice_tid' => $v->tid,
                            'cu_invoice_no' => $v->cu_invoice_no ?: '',
                            'invoice_date' => $v->invoicedate,
                            'tax_pin' => @$v->customer->taxid ?: '',
                            'customer' => @$v->customer->company ?: '',
                            'note' => $v->notes,
                            'subtotal' => $v->subtotal,
                            'total' => $v->total,
                            'tax' => $v->tax,
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
                $credit_notes = CreditNote::whereBetween('date', [$startDate, $endDate])
                    ->whereHas('invoice')
                    ->where(function ($q) {
                        $q->doesntHave('credit_note_tax_reports');
                        $q->orWhereHas('credit_note_tax_reports', function ($q) {
                            $q->where('is_filed', 0);
                        });
                    })
                    ->when(request('tax_group') != '', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('tax_id', request('tax_group'));
                        });
                    })
                    ->whereNull('supplier_id')->get()
                    ->map(function ($v) {
                        $v_mod = clone $v;
                        $invoice = $v->invoice;
                        $attr = [
                            'id' => $v->id,
                            'credit_note_tid' => $v->tid,
                            'invoice_date' => $v->date,
                            'tax_pin' => @$v->customer->taxid ?: '',
                            'customer' => @$v->customer->company ?: '',
                            'note' => 'Credit Note',
                            'subtotal' => -1 * $v->subtotal,
                            'total' => -1 * $v->total,
                            'tax' =>  -1 * $v->tax,
                            'tax_rate' => $v->subtotal > 0 ? round($v->tax / $v->subtotal * 100) : 0,
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
                $tax_sales = $q->with(['invoice', 'credit_note'])->get();
                $all_sales = $tax_sales->merge($sales);

                // $account_details = $q->with(['invoice', 'credit_note'])->get();
                $account_details = $all_sales;

                $lang['title'] = 'Sales / Purchase Tax Statement';
                $lang['title2'] = trans('meta.tax_statement');
                $lang['module'] = 'tax_statement';
                $lang['month_from'] = request('record_month');
                $lang['month_to'] = request('month_to');
                $lang['party'] = config('core.cname');
                $lang['party_2'] = trans('customers.customer');
                $lang['type'] = 1;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'tax_purchase':
                $q = TaxReportItem::query()->where(function ($q) {
                    $q->whereHas('purchase')->orWhereHas('debit_note');
                })->where('is_filed', request('is_filed', 1));

                $q->when(request('tax_report_id'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('id', request('tax_report_id')));
                })->when(request('record_month'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->whereBetween('record_month', [request('record_month'), request('month_to')]));
                })->when(request('return_month'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('return_month', request('return_month')));
                })->when(request('tax_group') != '', function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('tax_group', request('tax_group')));
                });

                $purchase_month = explode('-', request('record_month', '0-0'));
                $month = current($purchase_month);
                $year = end($purchase_month);

                $month_to = explode('-', request('month_to', '0-0'));
                $endMonth = current($month_to);
                $endYear = end($month_to);

                // Construct start and end date
                $startDate = "$year-$month-01";
                $endDate = date("Y-m-t", strtotime("$endYear-$endMonth-01"));

                // bills
                $bills = UtilityBill::whereBetween('date', [$startDate, $endDate])
                    ->where('tid', '>', 0)
                    ->whereIn('document_type', ['direct_purchase', 'goods_receive_note'])
                    ->where(function ($q) {
                        $q->doesntHave('purchase_tax_reports');
                        $q->orWhereHas('purchase_tax_reports', fn($q) => $q->where('is_filed', 0));
                    })
                    ->when(request('tax_group') != '', function ($q) {
                        $q->where('tax_rate', request('tax_group'));
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

                        $attr = [
                            'id' => $v->id,
                            'purchase_date' => $v->date,
                            'tax_pin' => $supplier_taxid ?: @$v->supplier->taxid,
                            'supplier' => $suppliername ?: @$v->supplier->name,
                            'invoice_no' => $v->reference,
                            'note' => $note,
                            'subtotal' => $v->subtotal,
                            'total' => $v->total,
                            'tax' => $v->tax,
                            'tax_rate' => $v->tax_rate,
                            'type' => 'purchase',
                            'debit_note_date' => '',
                        ];
                        foreach ($attr as $key => $value) {
                            $v_mod[$key] = $value;
                        }
                        return $v_mod;
                    });

                $purchases = $q->with(['purchase', 'debit_note'])->get();
                $total_purchases = $purchases->merge($bills);

                $account_details = $total_purchases;

                $lang['title'] = 'Sales / Purchase Tax Statement';
                $lang['title2'] = trans('meta.tax_statement_purchase');
                $lang['module'] = 'tax_statement';
                $lang['month_from'] = request('record_month');
                $lang['month_to'] = request('month_to');
                $lang['party'] = config('core.cname');
                $lang['party_2'] = trans('suppliers.supplier');
                $lang['type'] = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'tax_sale_purchase':

                $sale = TaxReportItem::query()->where(function ($q) {
                    $q->whereHas('invoice')->orWhereHas('credit_note');
                })->where('is_filed', request('is_filed', 1));

                $sale->when(request('tax_report_id'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('id', request('tax_report_id')));
                })->when(request('record_month'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->whereBetween('record_month', [request('record_month'), request('month_to')]));
                })->when(request('return_month'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('return_month', request('return_month')));
                })->when(request('tax_group') != '', function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('tax_group', request('tax_group')));
                });

                $sale_month = explode('-', request('record_month', '0-0'));
                $month = current($sale_month);
                $year = end($sale_month);

                $month_to = explode('-', request('month_to', '0-0'));
                $endMonth = current($month_to);
                $endYear = end($month_to);

                // Construct start and end date
                $startDate = "$year-$month-01";
                $endDate = date("Y-m-t", strtotime("$endYear-$endMonth-01"));

                // invoices
                $invoices = Invoice::whereBetween('invoicedate', [$startDate, $endDate])
                    ->where('tid', '>', 0)
                    ->where(function ($q) {
                        $q->doesntHave('invoice_tax_reports');
                        $q->orWhereHas('invoice_tax_reports', fn($q) =>  $q->where('is_filed', 0));
                    })
                    ->when(request('tax_group') != '', function ($q) {
                        $q->where('tax_id', request('tax_group'));
                    })
                    ->get()
                    ->map(function ($v) {
                        $v_mod = clone $v;
                        $attr = [
                            'id' => $v->id,
                            'invoice_tid' => $v->tid,
                            'cu_invoice_no' => $v->cu_invoice_no ?: '',
                            'invoice_date' => $v->invoicedate,
                            'tax_pin' => @$v->customer->taxid ?: '',
                            'customer' => @$v->customer->company ?: '',
                            'note' => $v->notes,
                            'subtotal' => $v->subtotal,
                            'total' => $v->total,
                            'tax' => $v->tax,
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
                $credit_notes = CreditNote::whereBetween('date', [$startDate, $endDate])
                    ->whereHas('invoice')
                    ->where(function ($q) {
                        $q->doesntHave('credit_note_tax_reports');
                        $q->orWhereHas('credit_note_tax_reports', function ($q) {
                            $q->where('is_filed', 0);
                        });
                    })
                    ->when(request('tax_group') != '', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('tax_id', request('tax_group'));
                        });
                    })
                    ->whereNull('supplier_id')->get()
                    ->map(function ($v) {
                        $v_mod = clone $v;
                        $invoice = $v->invoice;
                        $attr = [
                            'id' => $v->id,
                            'credit_note_tid' => $v->tid,
                            'invoice_date' => $v->date,
                            'tax_pin' => @$v->customer->taxid ?: '',
                            'customer' => @$v->customer->company ?: '',
                            'note' => 'Credit Note',
                            'subtotal' => -1 * $v->subtotal,
                            'total' => -1 * $v->total,
                            'tax' =>  -1 * $v->tax,
                            'tax_rate' => $v->subtotal > 0 ? round($v->tax / $v->subtotal * 100) : 0,
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
                $tax_sales = $sale->with(['invoice', 'credit_note'])->get();
                $all_sales = $tax_sales->merge($sales);

                $purchase = TaxReportItem::query()->where(function ($q) {
                    $q->whereHas('purchase')->orWhereHas('debit_note');
                })->where('is_filed', request('is_filed', 1));

                $purchase->when(request('tax_report_id'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('id', request('tax_report_id')));
                })->when(request('record_month'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->whereBetween('record_month', [request('record_month'), request('month_to')]));
                })->when(request('return_month'), function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('return_month', request('return_month')));
                })->when(request('tax_group') != '', function ($q) {
                    $q->whereHas('tax_report', fn($q) => $q->where('tax_group', request('tax_group')));
                });
                $purchase_month = explode('-', request('record_month', '0-0'));
                $month = current($purchase_month);
                $year = end($purchase_month);

                $month_to = explode('-', request('month_to', '0-0'));
                $endMonth = current($month_to);
                $endYear = end($month_to);

                // Construct start and end date
                $startDate = "$year-$month-01";
                $endDate = date("Y-m-t", strtotime("$endYear-$endMonth-01"));

                // bills
                $bills = UtilityBill::whereBetween('date', [$startDate, $endDate])
                    ->where('tid', '>', 0)
                    ->whereIn('document_type', ['direct_purchase', 'goods_receive_note'])
                    ->where(function ($q) {
                        $q->doesntHave('purchase_tax_reports');
                        $q->orWhereHas('purchase_tax_reports', fn($q) => $q->where('is_filed', 0));
                    })
                    ->when(request('tax_group') != '', function ($q) {
                        $q->where('tax_rate', request('tax_group'));
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

                        $attr = [
                            'id' => $v->id,
                            'purchase_date' => $v->date,
                            'tax_pin' => $supplier_taxid ?: @$v->supplier->taxid,
                            'supplier' => $suppliername ?: @$v->supplier->name,
                            'invoice_no' => $v->reference,
                            'note' => $note,
                            'subtotal' => $v->subtotal,
                            'total' => $v->total,
                            'tax' => $v->tax,
                            'tax_rate' => $v->tax_rate,
                            'type' => 'purchase',
                            'debit_note_date' => '',
                        ];
                        foreach ($attr as $key => $value) {
                            $v_mod[$key] = $value;
                        }
                        return $v_mod;
                    });

                $purchases = $purchase->with(['purchase', 'debit_note'])->get();
                $total_purchases = $purchases->merge($bills);

                $account_details =  ['sales' => $all_sales, 'purchase' => $total_purchases,];

                $lang['title'] = 'Combined Tax Statement';
                $lang['title3'] = 'Combined Tax Statement';
                $lang['module'] = 'tax_statement';
                $lang['month_from'] = request('record_month');
                $lang['month_to'] = request('month_to');
                $lang['party'] = config('core.cname');
                $lang['party_2'] = trans('suppliers.supplier');
                $lang['type'] = 3;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
        }


        switch ($reports->output_format) {
            case 'pdf_print':
                $html = view('focus.report.pdf.tax_report', compact('account_details', 'lang'))->render();
                $headers = array(
                    "Content-type" => "application/pdf",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                break;
            case 'pdf':
                $html = view('focus.report.pdf.tax_report', compact('account_details', 'lang'))->render();
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return $pdf->Output($file_name . '.pdf', 'D');
                break;

            case 'csv':
                $headers = array(
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=$file_name.csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                if ($lang['type'] == 1) {
                    $columns = array('Pin', 'Customer', 'Invoice Date', 'CU Invoice No', 'Description', 'Tax', 'Taxable Amount', 'Invoice No');
                    $callback = function () use ($account_details, $columns) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $columns);
                        $tax_sales_total = 0;
                        $total_sales = 0;
                        foreach ($account_details as $item) {
                            $pin = '';
                            $invoice = '';
                            $credit_note = '';
                            $customer = '';
                            $customer_name = '';
                            if ($item->invoice) {
                                $invoice = $item->invoice;
                                $invoice = $invoice;
                                $credit_note = null;
                                $customer = $invoice->customer;
                            } elseif ($item->credit_note) {
                                $credit_note = $item->credit_note;
                                $credit_note = $credit_note;
                                $invoice = null;
                                $customer = $credit_note->customer;
                            }
                            if ($customer) {
                                $pin .= $customer->taxid;
                                $customer_name = Str::limit($customer->company, 47);
                            }
                            $date = '';
                            $cuInvoiceNo = '';
                            $note = '';
                            $tax = 0;
                            $subtotal = 0;
                            $invoice_tid = '';
                            $cn_invoice_date = '';
                            if ($credit_note) {
                                $date = $credit_note->date;
                                $cuInvoiceNo = $credit_note->cu_invoice_no ?? '';
                                $note = 'Credit Note';
                                $tax = $credit_note->tax;
                                $subtotal = -1 * $credit_note->subtotal;
                                $invoice_tid = gen4tid('CN-', $credit_note->tid);
                                $invoice = $credit_note->invoice;
                                if ($invoice) $cn_invoice_date .= dateFormat($invoice->invoicedate, 'd/m/Y');
                            } elseif ($invoice) {
                                $date = $invoice->invoicedate;
                                $cuInvoiceNo = $invoice->cu_invoice_no ?? '';
                                $note = $invoice->notes;
                                $tax = $invoice->tax;
                                $subtotal = $invoice->subtotal;
                                $invoice_tid = gen4tid('INV-', $invoice->tid);
                            }
                            if ($item->type == 'invoice') {

                                $pin = $item->tax_pin;
                                $customer_name = Str::limit($item->customer, 47);
                                $date = $item->invoice_date;
                                $cuInvoiceNo = $item->cu_invoice_no ?? '';
                                $note = $item->note;
                                $tax = $item->tax;
                                $subtotal = $item->subtotal;
                                $invoice_tid = gen4tid('INV-', $item->invoice_tid);
                            }
                            if ($item->type == "credit_note") {
                                $pin = $item->tax_pin;
                                $customer_name = Str::limit($item->customer, 47);
                                $date = $item->invoice_date;
                                $cuInvoiceNo = $item->cu_invoice_no ?? '';
                                $note = $item->note;
                                $tax = $item->tax;
                                $subtotal = $item->subtotal;
                                $invoice_tid = gen4tid('CN-', $item->credit_note_tid);
                                $cn_invoice_date = dateFormat($item->invoice_date, 'd/m/Y');
                            }
                            if ($date) $date = dateFormat($date, 'd/m/Y');
                            if (!empty($cuInvoiceNo)) {
                                $cuInvoiceNo = "|" . $cuInvoiceNo;
                            }
                            $tax_sales_total += $tax;
                            $total_sales += $subtotal;
                            fputcsv($file, array($pin, $customer_name, $date, $cuInvoiceNo, $note, amountFormat($tax), amountFormat($subtotal), $invoice_tid));
                        }
                        fputcsv($file, array('', '', '', '', 'Total Sales tax', amountFormat($tax_sales_total), amountFormat($total_sales), ''));
                        fclose($file);
                    };
                } elseif ($lang['type'] == 2) {
                    $columns = array('Source', 'Pin', 'Supplier', 'Invoice Date', 'CU Invoice No', 'Description', 'Tax', 'Taxable Amount');
                    $callback = function () use ($account_details, $columns) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $columns);
                        $tax_total = 0;
                        $total = 0;
                        foreach ($account_details as $item) {
                            $pin = '';
                            $bill = $item->bill;
                            if ($bill && $bill->document_type) {
                                if ($bill->document_type == 'direct_purchase') {
                                    $purchase = $bill->purchase;
                                    $purchase = $purchase;
                                    $pin .= $purchase->supplier_taxid;
                                } elseif ($bill->supplier) {
                                    $purchase = null;
                                    $pin .= $bill->supplier->taxid;
                                }
                                $bill = $bill;
                                $supplier = $bill->supplier;
                                $debit_note = null;
                            } elseif ($item->debit_note) {
                                $debit_note = $item->debit_note;
                                $pin .= @$debit_note->supplier->taxid;
                                $debit_note = $debit_note;
                                $supplier = $debit_note->supplier;
                                $bill = null;
                                $purchase = null;
                            }
                            $suppliername = '';
                            if ($purchase) {
                                $suppliername .= $purchase->suppliername;
                            } else {
                                $suppliername .= $supplier->name;
                            }
                            $name = Str::limit($suppliername, 47);

                            $date = '';
                            $tid = '';
                            $note = '';
                            $tax = 0;
                            $subtotal = 0;
                            if ($debit_note) {
                                $date = $debit_note->date;
                                $tid = $debit_note->tid;
                                $note = 'Credit Note';
                                $tax = $debit_note->tax;
                                $subtotal = $debit_note->subtotal;
                            } elseif ($bill) {
                                $date = $bill->date;
                                $tid = $item->bill->reference;
                                if ($bill->tax_rate == 8) $note = 'Fuel';
                                $note = 'Goods';
                                $tax = $bill->tax;
                                $subtotal = $bill->subtotal;
                            }
                            if ($date) $date = dateFormat($date, 'd/m/Y');
                            if ($tid[0] != 0 && is_numeric($tid[0])) $tid =  "'" . $tid;


                            $tax_total += $tax;
                            $total += $subtotal;
                            fputcsv($file, array('Local', $pin, $name, $date, $tid, $note, amountFormat($tax), amountFormat($subtotal)));
                        }
                        fputcsv($file, array('', '', '', '', '', 'Total Purchases Tax', amountFormat($tax_total), amountFormat($total)));
                        fclose($file);
                    };
                } elseif ($lang['type'] == 3) {
                    return $this->generateCsv($account_details);
                }

                return Response::stream($callback, 200, $headers);
                break;
        }
    }

    public function generateCsv($account_details)
    {
        $accountDetails = $account_details;

        $csvData = [];

        // Sale Tax Data
        $csvData[] = ['Pin', 'Customer', 'Invoice Date', 'CU Invoice No.', 'Description', 'Tax', 'Taxable Amount', 'Invoice No.', 'CN Invoice Date'];
        $totalSale = 0;
        $taxSaleTotals = 0;

        foreach ($accountDetails['sales'] as $item) {
            $pin = '';
            $customerName = '';
            $date = '';
            $cuInvoiceNo = '';
            $note = '';
            $tax = 0;
            $subtotal = 0;
            $invoiceTid = '';
            $cnInvoiceDate = '';

            if ($item->invoice) {
                $invoice = $item->invoice;
                $customer = $invoice->customer;
            } elseif ($item->credit_note) {
                $creditNote = $item->credit_note;
                $customer = $creditNote->customer;
            }

            if ($customer ?? false) {
                $pin = $customer->taxid;
                $customerName = \Illuminate\Support\Str::limit($customer->company, 47);
            }

            if ($creditNote ?? false) {
                $date = $creditNote->date;
                $cuInvoiceNo = $creditNote->cu_invoice_no ?? '';
                $note = 'Credit Note';
                $tax = $creditNote->tax;
                $subtotal = -1 * $creditNote->subtotal;
                $invoiceTid = gen4tid('CN-', $creditNote->tid);
                if ($creditNote->invoice) {
                    $cnInvoiceDate = dateFormat($creditNote->invoice->invoicedate, 'd/m/Y');
                }
            } elseif ($invoice ?? false) {
                $date = $invoice->invoicedate;
                $cuInvoiceNo = $invoice->cu_invoice_no ?? '';
                $note = $invoice->notes;
                $tax = $invoice->tax;
                $subtotal = $invoice->subtotal;
                $invoiceTid = gen4tid('INV-', $invoice->tid);
            }
            if ($item->type == 'invoice') {

                $pin = $item->tax_pin;
                $customerName = Str::limit($item->customer, 47);
                $date = $item->invoice_date;
                $cuInvoiceNo = $item->cu_invoice_no ?? '';
                $note = $item->note;
                $tax = $item->tax;
                $subtotal = $item->subtotal;
                $invoiceTid = gen4tid('INV-', $item->invoice_tid);
            }
            if ($item->type == "credit_note") {
                $pin = $item->tax_pin;
                $customerName = Str::limit($item->customer, 47);
                $date = $item->invoice_date;
                $cuInvoiceNo = $item->cu_invoice_no ?? '';
                $note = $item->note;
                $tax = $item->tax;
                $subtotal = $item->subtotal;
                $invoiceTid = gen4tid('CN-', $item->credit_note_tid);
                $cnInvoiceDate = dateFormat($item->invoice_date, 'd/m/Y');
            }

            if ($date) {
                $date = dateFormat($date, 'd/m/Y');
            }

            if (!empty($cuInvoiceNo)) {
                $cuInvoiceNo = "|" . $cuInvoiceNo;
            }

            $taxSaleTotals += $tax;
            $totalSale += $subtotal;

            $csvData[] = [$pin, $customerName, $date, $cuInvoiceNo, $note, numberFormat($tax), numberFormat($subtotal), $invoiceTid, $cnInvoiceDate];
        }

        $csvData[] = ['', '', '', '', 'Total Sales Tax', numberFormat($taxSaleTotals), numberFormat($totalSale)];

        // Purchase Tax Data
        $csvData[] = [];
        $csvData[] = ['Source', 'Pin', 'Supplier', 'Invoice Date', 'Invoice No.', 'Description', 'Tax', 'Taxable Amount'];

        $total = 0;
        $taxTotals = 0;

        foreach ($accountDetails['purchase'] as $item) {
            $pin = '';
            $supplierName = '';
            $date = '';
            $tid = '';
            $note = '';
            $tax = 0;
            $subtotal = 0;

            if ($item->bill && $item->bill->document_type) {
                if ($item->bill->document_type == 'direct_purchase') {
                    $purchase = $item->bill->purchase;
                    $pin = $purchase->supplier_taxid;
                } elseif ($item->bill->supplier) {
                    $pin = $item->bill->supplier->taxid;
                }
                $supplier = $item->bill->supplier;
            } elseif ($item->debit_note) {
                $pin = @$item->debit_note->supplier->taxid;
                $supplier = $item->debit_note->supplier;
            }

            if ($purchase ?? false) {
                $supplierName = $purchase->suppliername;
            } elseif ($supplier ?? false) {
                $supplierName = $supplier->name;
            }



            if ($item->debit_note) {
                $date = $item->debit_note->date;
                $tid = $item->debit_note->tid;
                $note = 'Credit Note';
                $tax = $item->debit_note->tax;
                $subtotal = $item->debit_note->subtotal;
            } elseif ($item->bill) {
                $date = $item->bill->date;
                $tid = $item->bill->reference;
                $note = $item->bill->tax_rate == 8 ? 'Fuel' : 'Goods';
                $tax = $item->bill->tax;
                $subtotal = $item->bill->subtotal;
            } elseif ($item->type == 'purchase') {
                $pin .= @$item->tax_pin;
                $supplierName .= $item->supplier;
                $date = $item->date;
                $tid = $item->reference;
                if ($item->tax_rate == 8) $note = 'Fuel';
                $note = 'Goods';
                $tax = $item->tax;
                $subtotal = $item->subtotal;
            }
            // dd($tid);
            $supplierName = \Illuminate\Support\Str::limit($supplierName, 47);

            if ($date) {
                $date = dateFormat($date, 'd/m/Y');
            }

            // if ($tid[0] != 0 && is_numeric($tid[0])) {
            $tid = "[" . $tid . "]";
            // }

            $taxTotals += $tax;
            $total += $subtotal;

            $csvData[] = ['Local', $pin, $supplierName, $date, $tid, $note, numberFormat($tax), numberFormat($subtotal)];
        }

        $csvData[] = ['', '', '', '', '', 'Total Purchases Tax', numberFormat($taxTotals), numberFormat($total)];

        // Summary
        $csvData[] = [];
        $csvData[] = ['Summary', ''];
        $csvData[] = ['Total Tax Difference (Sales - Purchases):', amountFormat($taxSaleTotals - $taxTotals)];

        // Generating the CSV
        $filename = "combined_tax_report_" . date('d_m_Y') . ".csv";
        $handle = fopen($filename, 'w+');

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/csv',
        );

        return Response::download($filename, $filename, $headers)->deleteFileAfterSend(true);
    }

    /**
     * Generate Stock Statement Report Templates
     */
    public function generate_stock_statement(ManageReports $reports)
    {
        switch ($reports->stock_action) {
            case 'warehouse':
                $reports->validate(['from_warehouse' => 'required']);

                $account_details = StockTransferItem::whereHas('stock_transfer', function ($q) use ($reports) {
                    $q->whereBetween('date', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)]);
                    $q->when($reports->from_warehouse > 0, fn($q) => $q->where('source_id', $reports->from_warehouse));
                    $q->when($reports->to_warehouse > 0, fn($q) => $q->where('dest_id', $reports->to_warehouse));
                })
                    ->with('rcv_items')
                    ->get();
                foreach ($account_details as $i => $item) {
                    $item['qty_rcv'] = $item->rcv_items->sum('qty_rcv');
                    $item['qty_onhand'] = $item->qty_transf - $item->qty_rcv;
                    $item['amount'] = $item->qty_onhand * $item->cost;
                    $account_details[$i] = $item;
                }

                $lang['from_date'] = $reports->from_date;
                $lang['to_date'] = $reports->to_date;
                $lang['title'] = trans('meta.stock_transfer_statement');
                $lang['module'] = 'warehouse';
                $transfer = 1;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product':
                if (!$reports->product_name) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $account_details = ProductMeta::where('rel_id', '=', $reports->product_name)->where('rel_type', '=', 1)->when($reports->to_warehouse != 'all', function ($q) use ($reports) {
                    return $q->where('value2', '=', $reports->to_warehouse);
                })->whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->get();
                $lang['title'] = trans('meta.stock_transfer_statement');
                $lang['title2'] = trans('meta.stock_transfer_statement_product');
                $lang['module'] = 'product';
                $lang['party'] = config('core.cname');
                $lang['party_2'] = trans('customers.customer');
                $transfer = 1;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;

            case 'product_statement':
                if (!$reports->product_name) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                if ($reports->type_p == 'sales') {
                    $lang['title2'] = trans('meta.product_statement_sales');

                    $account_details = InvoiceItem::where('product_id', '=', $reports->product_name)->whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->get();
                } elseif ($reports->type_p == 'purchase') {
                    $lang['title2'] = trans('meta.product_statement_purchase');
                    $account_details = PurchaseItem::where('product_id', '=', $reports->product_name)->whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->get();
                }
                $product = ProductVariation::where('id', '=', $reports->product_name)->first();
                $lang['title'] = trans('meta.product_statement');

                $lang['module'] = 'product_statement';
                $lang['party'] = $product->product['name'] . ' ' . $product['name'];
                $lang['party_2'] = trans('products.product');
                $transfer = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product_movement_statement':
                $reports->validate(['from_date' => 'required', 'to_date' => 'required']);
                $priorQty = $this->productMovementPriorQty();
                $account_details = $this->productMovement($priorQty);
                $lang['title'] = 'Product Movement Statement';
                $lang['module'] = 'warehouse';
                $lang['from_date'] = $reports->from_date;
                $lang['to_date'] = $reports->to_date;
                $transfer = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product_category_statement':
                if (!$reports->product_category) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $cat_id = $reports->product_category;
                if ($reports->type_p == 'sales') {
                    $lang['title2'] = trans('meta.product_statement_sales');
                    $account_details = InvoiceItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])
                        ->where(function ($query) use ($cat_id) {
                            $query->whereHas('variation.product', function ($q) use ($cat_id) {
                                return $q->where('productcategory_id', '=', $cat_id);
                            })
                                ->orWhereHas('quote.verified_products', function ($q) use ($cat_id) {
                                    return $q->whereHas('product_variation.product', function ($q) use ($cat_id) {
                                        return $q->where('productcategory_id', '=', $cat_id);
                                    });
                                });
                        })
                        ->with(['variation.product' => function ($q) {
                            $q->select('id', 'name', 'price'); // Assuming 'name' is the column for product name
                        }])
                        ->with(['quote.verified_products.product_variation.product' => function ($q) {
                            $q->select('id', 'name', 'price'); // Assuming 'name' is the column for product name
                        }])
                        ->get();
                    // dd($account_details, $reports->from_date);
                } elseif ($reports->type_p == 'purchase') {
                    $lang['title2'] = trans('meta.product_statement_purchase');
                    $purchase_items = PurchaseItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])
                        ->where('type', 'Stock')
                        ->whereHas('variation', function ($q) use ($cat_id) {
                            return $q->whereHas('product', function ($q) use ($cat_id) {
                                return $q->where('productcategory_id', '=', $cat_id);
                            });
                        })
                        ->with(['variation.product' => function ($q) {
                            $q->select('id', 'name'); // Assuming 'name' is the column for product name
                        }])
                        ->select('id', 'description as product_name', 'rate as product_price', 'qty as product_qty', 'amount', 'created_at')
                        ->get();
                    $purchase_order_items = PurchaseorderItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])
                        ->where('type', 'Stock')->where('qty_received', '>', 0)
                        ->whereHas('variation', function ($q) use ($cat_id) {
                            return $q->whereHas('product', function ($q) use ($cat_id) {
                                return $q->where('productcategory_id', '=', $cat_id);
                            });
                        })
                        ->with(['variation.product' => function ($q) {
                            $q->select('id', 'name'); // Assuming 'name' is the column for product name
                        }])
                        ->select('id', 'description as product_name', 'rate as product_price', 'qty_received as product_qty', 'amount', 'created_at')
                        ->get();
                    $account_details = $purchase_items->merge($purchase_order_items);
                }
                // dd($account_details);
                $product = Productcategory::where('id', '=', $reports->product_category)->first();
                $lang['title'] = trans('meta.product_category_statement');
                $lang['module'] = 'product_statement';
                $lang['party'] = $product['title'];
                $lang['party_2'] = trans('products.product');
                $transfer = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;

            case 'product_warehouse_statement':
                if (!$reports->warehouse) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $warehouse_id = $reports->warehouse;
                if ($reports->type_p == 'sales') {
                    $account_details = InvoiceItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])
                        ->where(function ($query) use ($warehouse_id) {
                            $query->whereHas('variation', function ($q) use ($warehouse_id) {
                                $q->where('warehouse_id', '=', $warehouse_id);
                            })
                                ->orWhereHas('quote.verified_products', function ($q) use ($warehouse_id) {
                                    $q->whereHas('product_variation', function ($q) use ($warehouse_id) {
                                        $q->where('warehouse_id', '=', $warehouse_id);
                                    });
                                });
                        })
                        ->with(['variation' => function ($q) {
                            $q->select('id', 'name', 'price'); // Assuming 'name' is the column for product name
                        }])
                        ->with(['quote.verified_products.product_variation' => function ($q) {
                            $q->select('id', 'name', 'price'); // Assuming 'name' is the column for product name
                        }])
                        ->get();
                    // dd($account_details, datetime_for_database($reports->from_date));
                    $lang['title2'] = trans('meta.product_statement_sales');
                } elseif ($reports->type_p == 'purchase') {

                    $purchase_items = PurchaseItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])
                        ->where('type', 'Stock')
                        ->whereHas('variation', function ($q) use ($warehouse_id) {
                            return $q->where('warehouse_id', '=', $warehouse_id);
                        })
                        ->with(['variation' => function ($q) {
                            $q->select('id', 'name'); // Assuming 'name' is the column for product name
                        }])
                        ->select('id', 'description as product_name', 'rate as product_price', 'qty as product_qty', 'amount', 'created_at')
                        ->get();
                    $purchase_order_items = PurchaseorderItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])
                        ->where('type', 'Stock')->where('qty_received', '>', 0)
                        ->whereHas('variation', function ($q) use ($warehouse_id) {
                            return $q->where('warehouse_id', '=', $warehouse_id);
                        })
                        ->with(['variation' => function ($q) {
                            $q->select('id', 'name'); // Assuming 'name' is the column for product name
                        }])
                        ->select('id', 'description as product_name', 'rate as product_price', 'qty_received as product_qty', 'amount', 'created_at')
                        ->get();
                    $account_details = $purchase_items->merge($purchase_order_items);
                    // dd($account_details);
                    $lang['title2'] = trans('meta.product_statement_purchase');
                }
                // dd($account_details);
                $product = Warehouse::where('id', '=', $reports->warehouse)->first();
                $lang['title'] = trans('meta.product_warehouse_statement');
                $lang['module'] = 'product_statement';
                $lang['party'] = $product['title'];
                $lang['party_2'] = trans('products.product');
                $transfer = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product_customer_statement':
                if (!$reports->person) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);

                if ($reports->type_p == 'sales') {
                    $account_details = Invoice::where('customer_id', '=', $reports->person)->whereBetween('invoicedate', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->with('products')->get()->pluck('products');
                    $lang['title2'] = trans('customers.customer');
                    $lang['title'] = trans('meta.product_customer_statement');
                    $customer = Customer::find($reports->person)->first();
                    $lang['party'] = $customer->name;
                }

                $lang['module'] = 'product_statement';
                $lang['party_2'] = trans('products.product');
                $transfer = 3;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product_supplier_statement':
                if (!$reports->person) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);


                $account_details = Purchaseorder::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->with('products')->get()->pluck('products');
                // dd($account_details);
                $lang['title2'] = trans('suppliers.supplier');
                $lang['title'] = trans('meta.product_supplier_statement');
                $supplier = Supplier::find($reports->person)->first();
                $lang['party'] = $supplier->name;


                $lang['module'] = 'product_statement';
                $lang['party_2'] = trans('products.product');
                $transfer = 3;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
        }

        // product movement statement
        if ($transfer == 2 && $reports->stock_action == 'product_movement_statement') {
            $pdfConfig = array_replace(config('pdf'), [
                'orientation' => 'L', 
            ]);
            switch ($reports->output_format) {
                case 'pdf_print':
                    $html = view('focus.report.pdf.product_stock_movement', compact('priorQty', 'account_details', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf($pdfConfig);
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                case 'pdf':
                    $html = view($item_id > 0 ? 'focus.report.pdf.product_stock_movement' : 'focus.report.pdf.stock_movement', compact('account_details', 'lang'))->render();
                    $pdf = new \Mpdf\Mpdf($pdfConfig);
                    $pdf->WriteHTML($html);
                    return $pdf->Output($file_name . '.pdf', 'D');
                case 'csv':
                    $headers = array(
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=$file_name.csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $callback = function () use ($account_details, $item_id) {
                        $file = fopen('php://output', 'w');
                        if ($item_id > 0) {
                            fputcsv($file, ['Date', 'Type', 'Product', 'Location', 'Qty', 'On-Hand', 'Avg Cost', 'Asset Value']);
                            foreach ($account_details as $item) {
                                fputcsv($file, [
                                    $item->date ? dateFormat($item->date) : 'NULL',
                                    $item->type,
                                    $item->name,
                                    $item->location,
                                    $item->qty,
                                    $item->qty_onhand,
                                    numberFormat($item->avg_cost),
                                    numberFormat($item->amount),
                                ]);
                            }
                        } else {
                            fputcsv($file, ['Location', 'Product', 'Opening Qty', 'Qty In', 'Qty Out', 'Qty On-Hand', 'Avg Cost', 'Asset Value']);
                            foreach ($account_details as $item) {
                                fputcsv($file, [
                                    @$item->warehouse->title ? $item->warehouse->title : 'NULL',
                                    $item->name,
                                    +$item->op_stock_qty,
                                    +$item->qty_in,
                                    +$item->qty_out,
                                    +$item->qty_onhand,
                                    numberFormat($item->avg_cost),
                                    numberFormat($item->amount),
                                ]);
                            }
                        }
                        fclose($file);
                    };
                    return Response::stream($callback, 200, $headers);
            }
        }

        if ($transfer == 1) {
            switch ($reports->output_format) {
                case 'pdf_print':
                    $html = view('focus.report.pdf.stock_transfer', compact('account_details', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                case 'pdf':
                    $html = view('focus.report.pdf.stock_transfer', compact('account_details', 'lang'))->render();
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return $pdf->Output($file_name . '.pdf', 'D');
                case 'csv':
                    $headers = array(
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=$file_name.csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $callback = function () use ($account_details) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, [trans('general.date'), trans('products.product'), 'From Location', 'Qty Out', 'To Destination', 'Qty In', 'On Hand', 'Asset Value']);
                        foreach ($account_details as $item) {
                            fputcsv($file, [
                                $item->stock_transfer ? dateFormat($item->stock_transfer->date) : 'NULL',
                                @$item->productvar->name ?: 'NULL',
                                @$item->stock_transfer->source->title ?: 'NULL',
                                +$item->qty_transf,
                                @$item->stock_transfer->destination->title ?: 'NULL',
                                +$item->qty_rcv,
                                +$item->qty_onhand,
                                numberFormat($item->amount),
                            ]);
                        }
                        fclose($file);
                    };
                    return Response::stream($callback, 200, $headers);
            }
        }
        if ($transfer == 2) {
            switch ($reports->output_format) {
                case 'pdf_print':
                    $html = view('focus.report.pdf.product', compact('account_details', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                case 'pdf':
                    $html = view('focus.report.pdf.product', compact('account_details', 'lang'))->render();
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return $pdf->Output($file_name . '.pdf', 'D');
                case 'csv':
                    $headers = array(
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=$file_name.csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $columns = array(trans('general.date'), trans('products.product'), trans('products.price'), trans('products.qty'), trans('general.total'));
                    $callback = function () use ($account_details, $columns) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $columns);
                        $balance = 0;

                        foreach ($account_details as $row) {
                            $balance += $row['product_qty'];
                            fputcsv($file, array(dateFormat($row['created_at']), $row['product_name'], amountFormat($row['product_price']), numberFormat($row['product_qty']) . ' ' . $row['unit'], numberFormat($balance)));
                        }
                        fclose($file);
                    };
                    return Response::stream($callback, 200, $headers);
                    break;
            }
        }
        if ($transfer == 3) {
            switch ($reports->output_format) {
                case 'pdf_print':
                    $html = view('focus.report.pdf.product_person', compact('account_details', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                    break;
                case 'pdf':
                    $html = view('focus.report.pdf.product_person', compact('account_details', 'lang'))->render();
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return $pdf->Output($reports->section . '.pdf', 'D');
                    break;

                case 'csv':
                    $headers = array(
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=$file_name.csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $columns = array(trans('general.date'), trans('products.product'), trans('products.price'), trans('products.qty'), trans('general.total'));
                    $callback = function () use ($account_details, $columns) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $columns);
                        $balance = 0;
                        foreach ($account_details as $account_detail) {
                            foreach ($account_detail as $row) {
                                $balance += $row['product_qty'];
                                fputcsv($file, array(dateFormat($row['created_at']), @$row['product_name'], amountFormat($row['product_price']), numberFormat($row['product_qty']), numberFormat($balance) . ' ' . $row['unit']));
                            }
                        }
                        fclose($file);
                    };
                    return Response::stream($callback, 200, $headers);
                    break;
            }
        }
    }

    public function productMovement($priorQty = 0)
    {
        $itemId = request('product_id');
        $fromDate = date_for_database(request('from_date'));
        $toDate = date_for_database(request('to_date'));
        $tranxs = collect();

        $productvar = ProductVariation::with('warehouse')->find($itemId, ['id', 'name', 'purchase_price', 'warehouse_id']);
        if (!$productvar) return $tranxs;

        $struct = [
            'date' => '',
            'name' => $productvar->name,
            'supplier' => '',
            'dnote_refno' => '',
            'invoice_quote_no' => '',
            'project_no' => '',
            'type' => '',
            'uom' => (string) @$productvar->product->unit->code,
            'qty' => 0,
            'avg_cost' => $productvar->purchase_price,
        ];

        $openingStock = OpeningStock::with('items')->latest()->first();
        $isBtwnDates = Carbon::parse($openingStock->date)->gte(Carbon::parse($fromDate)) && 
            Carbon::parse($openingStock->date)->lte(Carbon::parse($toDate));
        if ($openingStock && $isBtwnDates) {
            $openingStockItem = $openingStock->items->where('productvar_id', $itemId)->first();
            if ($openingStockItem) {
                $tranxs->push((object) array_replace($struct, [
                    'date' => $openingStock->date,
                    'type' => 'opening-stock',
                    'uom' => (string) @$openingStockItem->product->unit->code,
                    'qty' => $openingStockItem->qty,
                ]));
            }
        }

        $purchaseItems = PurchaseItem::where('item_id', $itemId)->where('type', 'Stock')
            ->whereHas('purchase', fn ($q) =>  $q->whereBetween('date', [$fromDate, $toDate]))
            ->with(['project', 'warehouse', 'productvariation.product.unit'])
            ->get();
        foreach ($purchaseItems as $item) {
            if ($item->productvariation && $item->uom) {
                $item->qty = convertUnitQty($item->productvariation->product, $item->qty, $item->uom);
            }
            $tranxs->push((object) array_replace($struct, [
                'date' => @$item->purchase->date,
                'type' => $item->warehouse ? 'inventory-stock-purchase' : ($item->project? "project-stock-purchase" : 'Missing Project/Location'),
                'supplier' => @$item->purchase_suppliername,
                'project_no' => $item->project ? gen4tid('PRJ-', $item->project->tid) : '',
                'uom' => $item->uom,
                'qty' => $item->qty,
            ]));
        }

        $grnItems = GoodsreceivenoteItem::whereHas('purchaseorder_item', fn($q) => $q->where('product_id', $itemId))
            ->whereHas('goodsreceivenote', fn($q) => $q->whereBetween('date', [$fromDate, $toDate]))
            ->with(['project', 'warehouse', 'purchaseorder_item.productvariation.product.unit'])
            ->get();
        foreach ($grnItems as $item) {
            $supplierName = @$item->goodsreceivenote->supplier->name ?: '';
            $dnote = @$item->goodsreceivenote->dnote ?: '';
            $invoice_no = @$item->goodsreceivenote->invoice_no ?: '';
            $projectNo = $item->project ? gen4tid('PRJ-', $item->project->tid) : '';
            $product = @$item->purchaseorder_item->productvariation->product;
            $uom = @$item->purchaseorder_item->uom;
            if ($product && $uom) $item->qty = convertUnitQty($product, $item->qty, $uom);
            $tranxs->push((object) array_replace($struct, [
                'date' => @$item->goodsreceivenote->date ?: '',
                'type' => $item->warehouse ? 'inventory-stock-grn' : ($item->project? "project-stock-grn" : 'Missing Location/Project'),
                'supplier' => $supplierName,
                'dnote_refno' => $dnote,
                'invoice_quote_no' => $invoice_no,
                'project_no' => $projectNo,
                'uom' => $uom,
                'qty' => $item->qty,
            ]));
        }

        $adjItems = StockAdjItem::where('productvar_id', $itemId)
            ->whereHas('stock_adj', fn($q) => $q->where('approval_status', 'Approved'))
            ->whereHas('stock_adj', fn ($q) =>  $q->whereBetween('date', [$fromDate, $toDate]))
            ->with(['stock_adj', 'productvar.product.unit'])
            ->get();
        foreach ($adjItems as $item) {
            $tranxs->push((object) array_replace($struct, [
                'date' => @$item->stock_adj->date,
                'type' => $item->qty_diff > 0 ? '(+)stock-adj' : '(-)stock-adj',
                'uom' => @$item->productvar->product->unit->code,
                'qty' => $item->qty_diff,
            ]));
        }

        $issueItems = StockIssueItem::where('productvar_id', $itemId)
            ->whereHas('stock_issue', fn($q) => $q->whereBetween('date', [$fromDate, $toDate]))
            ->with(['stock_issue.quote.project', 'productvar.product.unit'])
            ->get();
        foreach ($issueItems as $item) {
            $issue = $item->stock_issue;
            $issueNo = gen4tid('', $issue->tid);
            $invoiceNo = $issue->quote ? gen4tid($issue->quote->bank_id ? "PI-" : "QT-", $issue->quote->tid) : "";
            $projectNo = @$issue->quote->project ? gen4tid('PRJ-', $issue->quote->project->tid) : '';
            $tranxs->push((object) array_replace($struct, [
                'date' => $issue->date,
                'type' => "stock-issue-{$issueNo}",
                'dnote_refno' => $issue->ref_no,
                'invoice_quote_no' => $invoiceNo,
                'project_no' => $projectNo,
                'uom' => @$item->productvar->product->unit->code,
                'qty' => -$item->issue_qty,
            ]));
        }

        $saleReturnItems = SaleReturnItem::where('productvar_id', $itemId)
            ->whereHas('sale_return', fn($q) => $q->whereBetween('date', [$fromDate, $toDate]))
            ->with(['sale_return', 'productvar.product.unit'])
            ->get();
        foreach ($saleReturnItems as $item) {
            $saleReturn = $item->sale_return;
            $saleReturnNo = gen4tid('', $saleReturn->tid);
            $tranxs->push((object) array_replace($struct, [
                'date' => $saleReturn->date,
                'type' => "sale-return-{$saleReturnNo}",
                'uom' => @$item->productvar->product->unit->code,
                'qty' => $item->return_qty,
            ]));
        }

        $tranxs = $tranxs->sortBy('date')->values();
        $qtyOnHand = $priorQty;
        foreach ($tranxs as $index => $item) {
            if (in_array($item->type, ['project-stock-grn', 'project-stock-purchase'])) {
                if ($index) $item->qty_onhand = $tranxs[$index-1]->qty_onhand;
                else $item->qty_onhand = $qtyOnHand;
                $item->amount = 0;
            } else {
                $qtyOnHand += $item->qty;
                $item->qty_onhand = $qtyOnHand;
                $item->amount = $qtyOnHand * $item->avg_cost;
            }
            $tranxs[$index] = $item;
        }
        
        return $tranxs;
    }

    public function productMovementPriorQty()
    {
        $itemId = request('product_id');
        $fromDate = date_for_database(request('from_date'));
        $tranxs = collect();

        $productvar = ProductVariation::with('warehouse')->find($itemId, ['id', 'name', 'purchase_price', 'warehouse_id']);
        if (!$productvar) return $tranxs;

        $struct = [
            'date' => '',
            'type' => '',
            'qty' => 0,
            'avg_cost' => $productvar->purchase_price,
        ];

        $openingStock = OpeningStock::with('items')->latest()->first();
        if (Carbon::parse($openingStock->date)->lt(Carbon::parse($fromDate))) {
            $openingStockItem = $openingStock->items->where('productvar_id', $itemId)->first();
            if ($openingStockItem) {
                $tranxs->push((object) array_replace($struct, [
                    'date' => $openingStock->date,
                    'type' => 'opening-stock',
                    'qty' => $openingStockItem->qty,
                ]));
            }
        }

        $purchaseItems = PurchaseItem::where('item_id', $itemId)->where('type', 'Stock')
            ->whereHas('purchase', fn ($q) =>  $q->whereDate('date', '<', $fromDate))
            ->with(['project', 'warehouse', 'productvariation.product.unit'])
            ->get();
        foreach ($purchaseItems as $item) {
            if ($item->productvariation && $item->uom) {
                $item->qty = convertUnitQty($item->productvariation->product, $item->qty, $item->uom);
            }
            $tranxs->push((object) array_replace($struct, [
                'date' => @$item->purchase->date,
                'type' => $item->warehouse ? 'inventory-stock-purchase' : ($item->project? "project-stock-purchase" : 'Missing Project/Location'),
                'qty' => $item->qty,
            ]));
        }

        $grnItems = GoodsreceivenoteItem::whereHas('purchaseorder_item', fn($q) => $q->where('product_id', $itemId))
            ->whereHas('goodsreceivenote', fn($q) => $q->whereDate('date', '<', $fromDate))
            ->with(['project', 'purchaseorder_item.productvariation.product'])
            ->get();
        foreach ($grnItems as $item) {
            $product = @$item->purchaseorder_item->productvariation->product;
            $uom = @$item->purchaseorder_item->uom;
            if ($product && $uom) $item->qty = convertUnitQty($product, $item->qty, $uom);
            $tranxs->push((object) array_replace($struct, [
                'date' => @$item->goodsreceivenote->date ?: '',
                'type' => $item->warehouse ? 'inventory-stock-grn' : ($item->project? "project-stock-grn" : 'Missing Location/Project'),
                'qty' => $item->qty,
            ]));
        }

        $adjItems = StockAdjItem::where('productvar_id', $itemId)
            ->whereHas('stock_adj', function ($q) use($fromDate) {
                $q->whereDate('date', '<', $fromDate);
                $q->where('approval_status', 'Approved');
            })
            ->get();
        foreach ($adjItems as $item) {
            $tranxs->push((object) array_replace($struct, [
                'date' => @$item->stock_adj->date,
                'type' => $item->qty_diff > 0 ? '(+)stock-adj' : '(-)stock-adj',
                'qty' => $item->qty_diff,
            ]));
        }

        $issueItems = StockIssueItem::where('productvar_id', $itemId)
            ->whereHas('stock_issue', fn($q) => $q->whereDate('date', '<', $fromDate))
            ->get();
        foreach ($issueItems as $item) {
            $issue = $item->stock_issue;
            $issueNo = gen4tid('', $issue->tid);
            $tranxs->push((object) array_replace($struct, [
                'date' => $issue->date,
                'type' => "stock-issue-{$issueNo}",
                'qty' => -$item->issue_qty,
            ]));
        }

        $saleReturnItems = SaleReturnItem::where('productvar_id', $itemId)
            ->whereHas('sale_return', fn($q) => $q->whereDate('date', '<', $fromDate))
            ->with(['sale_return', 'productvar.product.unit'])
            ->get();
        foreach ($saleReturnItems as $item) {
            $saleReturn = $item->sale_return;
            $saleReturnNo = gen4tid('', $saleReturn->tid);
            $tranxs->push((object) array_replace($struct, [
                'date' => $saleReturn->date,
                'type' => "sale-return-{$saleReturnNo}",
                'qty' => $item->return_qty,
            ]));
        }

        $tranxs = $tranxs->sortBy('date')->values();
        $qtyOnHand = 0;
        foreach ($tranxs as $item) {
            if (!in_array($item->type, ['project-stock-grn', 'project-stock-purchase'])) {
                $qtyOnHand += $item->qty;
            } 
        }

        return $qtyOnHand;
    }

    public function pos_statement(ManageReports $reports)
    {
        if (!$reports->from_date) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);

        $register_entries = Register::whereBetween('created_at', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->get();
        $lang['title'] = trans('meta.pos_statement');
        $lang['title2'] = trans('meta.pos_statement');
        $lang['module'] = 'pos_statement';
        $lang['party'] = config('core.cname');
        $lang['party_2'] = '';
        $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);


        switch ($reports->output_format) {

            case 'pdf_print':

                $html = view('focus.report.pdf.pos_register', compact('register_entries', 'lang'))->render();
                $headers = array(
                    "Content-type" => "application/pdf",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                break;
            case 'pdf':
                $html = view('focus.report.pdf.pos_register', compact('register_entries', 'lang'))->render();
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return $pdf->Output($file_name . '.pdf', 'D');
                break;

            case 'csv':
                $headers = array(
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=$file_name.csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $columns = array(trans('pos.opened_on'), trans('pos.closed_on'), trans('general.employee'), trans('general.description'));
                $callback = function () use ($register_entries, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    foreach ($register_entries as $row) {
                        $bal = '';
                        $balance = json_decode($row->data, true);
                        foreach ($balance as $key => $amount_row) {
                            $bal .= $key . ' : ' . amountFormat($amount_row) . '<br>';
                        }
                        fputcsv($file, array(dateFormat($row['created_at']), dateFormat($row['closed_at']), $row->user->first_name . ' ' . $row->user->last_name, $bal));
                    }
                    fclose($file);
                };
                return Response::stream($callback, 200, $headers);
                break;
        }
    }
}
