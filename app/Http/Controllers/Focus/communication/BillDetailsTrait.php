<?php

namespace App\Http\Controllers\Focus\communication;

use App\Models\bank\Bank;
use App\Models\bill\Bill;
use App\Models\Company\Company;
use App\Models\Company\ConfigMeta;
use App\Models\Company\UserGateway;
use App\Models\customer\Customer;
use App\Models\order\Order;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\quote\Quote;
use App\Models\djc\Djc;
use App\Models\invoice\Invoice;
use App\Models\items\VerifiedItem;
use App\Models\payroll\Payroll;
use App\Models\payroll\PayrollItemV2;
use App\Models\rjc\Rjc;
use App\Models\stock_issue\StockIssue;

trait BillDetailsTrait
{
    protected function bill_details($request)
    {
        $flag = false;

        $getAttr = function ($type, $title, $custom, $person, $person_id, $url) { 
            return compact('type', 'title', 'person', 'person_id', 'url'); 
        };
        $getGeneral = function ($bill_type, $lang_bill_number, $lang_bill_date, $lang_bill_due_date, $person, $direction, $prefix, $status_block) {
            return compact('bill_type', 'lang_bill_number', 'lang_bill_date', 'lang_bill_due_date', 'person', 'direction', 'prefix', 'status_block');
        };

        switch ($request->type) {
            case 1:
                // invoice
                $resource = Invoice::find($request->id);
                $attributes = $getAttr(1, 'invoice', 2, 1, $resource->customer_id, route('biller.invoices.show', $resource->id));
                foreach($attributes as $key => $val) {
                    $resource[$key] = $val;
                }
                // assign bank
                $resource['bank'] = Bank::find($resource->bank_id);

                $prefix = 1;
                $title = trans('invoices.invoice_title');
                if ($resource->i_class == 1) {
                    $prefix = 10;
                    $title = trans('invoices.pos');
                } elseif ($resource->i_class > 1) {
                    $prefix = 6;
                    $title = trans('invoices.subscription');
                }

                $flag = token_validator($request->token, 'i' . $resource->id . $resource->tid);
                $general = $getGeneral(
                    $title, 
                    trans('invoices.tid'), 
                    trans('invoices.invoice_date'), 
                    trans('invoices.invoice_due_date'),
                    trans('customers.customer'),
                    'ltr', $prefix, true
                );
                $valid_token = token_validator('', 'i' . $resource->id . $resource->tid, true);
                break;
            case 3:
                // proforma invoice
                $resource = Bill::find($request->id);
                $attributes = $getAttr(3, 'proformer', 2, 1, $resource->customer_id, route('biller.invoices.show', $resource->id));
                foreach($attributes as $key => $val) {
                    $resource[$key] = $val;
                }

                $flag = token_validator($request->token, 'i' . $resource->id . $resource->tid,);
                $general = $getGeneral(
                    trans('invoices.proforma'),
                    trans('invoices.proforma_tid'),
                    trans('invoices.invoice_date'),
                    trans('invoices.invoice_due_date'), 
                    trans('customers.customer'),
                    'ltr', 3, false
                );
                $valid_token = token_validator('', 'i' . $resource->id . $resource->tid, true);
                break;             
            case 4:
                // quotation
                $resource = Quote::find($request->id);
                $attributes = $getAttr(1, 'quote', 2, 1, $resource->customer_id, route('biller.quotes.show', $resource->id));
                foreach($attributes as $key => $value) {
                    $resource[$key] = $value;
                }
                // rehydrate relations
                $resource['client'] = $resource->client ?: $resource->quote_client;
                $resource['bank'] = $resource->bank;
                $resource['products'] = $resource->products()->orderBy('row_index')->get();
                if ($resource->verified == 'Yes') $resource['verified_items'] = $resource->verified_products;
                
                $flag = token_validator($request->token, 'q' . $resource->id . $resource->tid);
                $general = $getGeneral(
                    trans('quotes.quote'),
                    trans('quotes.quote'),
                    trans('quotes.invoicedate'),
                    trans('quotes.invoiceduedate'),
                    trans('customers.customer'),
                    'ltr', 5, false
                );
                $valid_token = token_validator('', 'q' . $resource->id . $resource->tid, true);
                break;                
            case 5:
                // order 
                $resource = Order::find($request->id);
                $attributes = $getAttr(5, 'order', 5, 1, $resource->customer_id, route('biller.orders.show', $resource->id));
                foreach($attributes as $key => $val) {
                    $resource[$key] = $val;
                }

                $title = trans('orders.credit_note');
                $prefix = 7;
                $person = trans('customers.customer');
                $flag = token_validator($request->token, 'o' . $resource->id . $resource->tid);
                if ($resource->i_class == 3) {
                    $prefix = 8;
                    $title = trans('orders.stock_return');
                    $person = trans('suppliers.supplier');
                }
                $general = $getGeneral(
                    $title, 
                    trans('orders.order'),
                    trans('general.date'),
                    trans('orders.invoiceduedate'),
                    $person, 'ltr', $prefix, false
                );
                $valid_token = token_validator('', 'o' . $resource->id . $resource->tid, true);
                break;            
            case 9:
                // purchase order
                $resource = Purchaseorder::find($request->id);
                $attributes = $getAttr(9, 'purchase_order', 9, 1, 0, route('biller.purchaseorders.show', $resource->id));
                foreach($attributes as $key => $val) {
                    $resource[$key] = $val;
                }

                $flag = token_validator($request->token, 'po' . $resource->id, true);
                $general = $getGeneral(
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.invoicedate'),
                    trans('purchaseorders.invoiceduedate'),                    
                    trans('suppliers.supplier'),
                    'ltr', 9, false
                );
                $valid_token = token_validator('', 'po' . $resource->id, true);
                break;            
            case 10:
                // djc
                $resource = Djc::find($request->id);
                $attributes = $getAttr(9, 'djc_report', 9, 1, $resource->customer_id, route('biller.purchaseorders.show', $resource->id));
                foreach($attributes as $key => $val) {
                    $resource[$key] = $val;
                }

                $flag = token_validator($request->token, 'd' . $resource->id);
                $general = $getGeneral(
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.invoicedate'),
                    trans('purchaseorders.invoiceduedate'), 
                    trans('suppliers.supplier'),
                    'ltr', 9, false
                );
                $valid_token = token_validator('', 'd' . $resource->id, true);
                break;
            case 11:
                //rjc
                $resource = Rjc::find($request->id);
                $attributes = $getAttr(9, 'rjc_report', 9, 1, $resource->customer_id, route('biller.purchaseorders.show', $resource->id));
                foreach($attributes as $key => $val) {
                    $resource[$key] = $val;
                }
                // main project quote
                $resource['quote'] = $resource->project->quotes->find($resource->project->main_quote_id);
                // All djcs sharing lead of the main project quote
                $resource['djcs'] = Djc::where('lead_id', $resource->quote->lead->id)->get(['tid', 'report_date']);

                $flag = token_validator($request->token, 'd' . $resource->id);
                $general = $getGeneral(
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.invoicedate'),
                    trans('purchaseorders.invoiceduedate'), 
                    trans('suppliers.supplier'),
                    'ltr', 9, false
                );
                $valid_token = token_validator('', 'd' . $resource->id, true);
                break;
            case 12:
                    // purchase order
                    $resource = StockIssue::find($request->id);
                    $attributes = $getAttr(12, 'stock_issue', 12, 1, 0, route('biller.stock_issues.show', $resource->id));
                    foreach($attributes as $key => $val) {
                        $resource[$key] = $val;
                    }
    
                    $flag = token_validator($request->token, 'si' . $resource->id, true);
                    $general = $getGeneral(
                        trans('purchaseorders.purchaseorder'),
                        trans('purchaseorders.purchaseorder'),
                        trans('purchaseorders.invoicedate'),
                        trans('purchaseorders.invoiceduedate'),                    
                        trans('suppliers.supplier'),
                        'ltr', 9, false
                    );
                    $valid_token = token_validator('', 'si' . $resource->id, true);
                    break;
            case 13:
                $resource = PayrollItemV2::find($request->id);
                $attributes = $getAttr(13, 'payroll', 13, 1, 0, route('biller.payroll.show', $resource->payroll->id));
                foreach($attributes as $key => $val) {
                    $resource[$key] = $val;
                }

                $flag = token_validator($request->token, 'q' . $resource->id, true);
                $general = $getGeneral(
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.invoicedate'),
                    trans('purchaseorders.invoiceduedate'),                    
                    trans('suppliers.supplier'),
                    'ltr', 13, false
                );
                $valid_token = token_validator('', 'q' . $resource->id, true);
                break;

        }

        if ($flag) {
            $company = Company::find($resource->ins);

            $online_payment = ConfigMeta::where(['feature_id' => 5])->first();
            $online_pay_account = ConfigMeta::where(['feature_id' => 6])->first('feature_value');

            $gateway = UserGateway::whereHas('config', function ($q) use ($company) { $q->where('ins', $company->id); })->get();

            config([ 'currency' => ConfigMeta::where(['feature_id' => 2])->first()->currency]);

            $general['tax_string_total'] = trans('general.total_tax');
            $general['tax_id'] = trans('general.tax_id');
            if ($resource->tax_format == 'igst' or $resource->tax_format == 'cgst') {
                $general['tax_string_total'] = trans('general.total_gst');
                $general['tax_id'] = trans('general.gstin');
            }

            $link = array(
                'link' => route('biller.print_bill', [$resource->id, $resource->type, $valid_token, 1]),
                'download' => route('biller.print_bill', [$resource->id, $resource->type, $valid_token, 2]),
                'preview' => route('biller.view_bill', [$resource->id, $resource->type, $valid_token, 0]),
                'bank' => route('biller.view_bank', [$resource->id, $resource->type, $valid_token]),
                'payment' => route('biller.pay_card', [$resource->id, $resource->type, $valid_token])
            );

            $data = compact('general', 'resource', 'company', 'gateway', 'link');
            $data['online_payment'] = $online_payment->feature_value;
            $data['online_pay_account'] = $online_pay_account->feature_value;

            return $data;
        }

        exit('Bill details error');
    }    
}