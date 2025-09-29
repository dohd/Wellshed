<html>
    <head>
        <title>Tax Report</title>
    </head>
    <style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}
        h5 {
			font-size: 1em;
			font-family: Arial, Helvetica, sans-serif;
			font-weight: bold;
            margin-bottom: .7em;
		}
		p {
			margin: 0pt;
		}
		table.items {
			border: 0.1mm solid #000000;
		}
		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
		}
		td {
			vertical-align: top;
		}
		.items td {
			border-left: 0.1mm solid #000000;
			border-right: 0.1mm solid #000000;
		}
		table thead th {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid #000000;
			font-weight: normal;
		}
		        
        .dotted td {
			border-bottom: dotted 1px black;
		}
		.dottedt th {
			border-bottom: dotted 1px black;
		}

		.footer {
			font-size: 9pt; 
			text-align: center; 
		}
		.table-items {
			font-size: 10pt; 
			border-collapse: collapse;
			height: 700px;
			width: 100%;
		}
        .align-c {
            text-align: center; 
        }
        .mt-3 {
            margin-top: 3em;
        }
	</style>
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div class="footer">Page {PAGENO} of {nb}</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />

    @if ($lang['type'] == 1)
        <div style="text-align: center; line-height: 0">
            <h1>{{ auth()->user()->business->cname }}</h1>
            <h2>Tax Sale Report Generated on {{ date('d-m-Y') }}</h2>
            <h2>Tax Report From {{$lang['month_from']}} to {{$lang['month_to']}}</h2>
            <h5>{{$lang['title2']}}</h5>
        </div>

        <table class="table table-items" cellpadding=8>
            <thead>
                <tr>
                    <th>Pin</th>
                    <th>Customer</th>
                    <th>Invoice Date</th>
                    <th>CU Invoice No.</th>
                    <th>Description</th>
                    <th>Tax</th>
                    <th>Taxable Amount</th>
                    <th>Invoice No.</th>
                    <th>CN Invoice Date</th>
                </tr>
            </thead>
            <tbody>
                {{-- {{dd($account_details)}} --}}
                @php
                    $total = 0;
                    $tax_totals = 0;
                @endphp
                @foreach ($account_details as $item)
                    @php
                    
                        $pin = '';
                        $invoice = '';
                        $credit_note = '';
                        $customer = '';
                        $customer_name = '';
                        $date = '';
                        $cuInvoiceNo = '';
                        $note = '';
                        $tax = 0;
                        $subtotal = 0;
                        $invoice_tid = '';
                        $cn_invoice_date = '';
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
                            $customer_name = Illuminate\Support\Str::limit($customer->company,47);
                        }
                        if ($credit_note) {
                            $date = $credit_note->date;
                            $cuInvoiceNo = $credit_note->cu_invoice_no ?? '';
                            $note = 'Credit Note';
                            $tax = $credit_note->tax;
                            $subtotal = -1* $credit_note->subtotal;
                            $invoice_tid = gen4tid('CN-', $credit_note->tid);
                            $invoice = $credit_note->invoice;
                            if ($invoice) $cn_invoice_date .= dateFormat($invoice->invoicedate, 'd/m/Y');
                        }
                        elseif ($invoice) {
                            $date = $invoice->invoicedate;
                            $cuInvoiceNo = $invoice->cu_invoice_no ?? '';
                            $note = $invoice->notes;
                            $tax = $invoice->tax;
                            $subtotal = $invoice->subtotal;
                            $invoice_tid = gen4tid('INV-', $invoice->tid);
                        }
                        if ($item->type == 'invoice') {
                           
                            $pin = $item->tax_pin;
                            $customer_name = Illuminate\Support\Str::limit($item->customer,47);
                            $date = $item->invoice_date;
                            $cuInvoiceNo = $item->cu_invoice_no ?? '';
                            $note = $item->note;
                            $tax = $item->tax;
                            $subtotal = $item->subtotal;
                            $invoice_tid = gen4tid('INV-', $item->invoice_tid);
                        }
                        if ($item->type == "credit_note") {
                            $pin = $item->tax_pin;
                            $customer_name = Illuminate\Support\Str::limit($item->customer,47);
                            $date = $item->invoice_date;
                            $cuInvoiceNo = $item->cu_invoice_no ?? '';
                            $note = $item->note;
                            $tax = $item->tax;
                            $subtotal = $item->subtotal;
                            $invoice_tid = gen4tid('CN-', $item->credit_note_tid);
                            $cn_invoice_date = dateFormat($item->invoice_date, 'd/m/Y');
                        }
                        if ($date) $date = dateFormat($date, 'd/m/Y');
                        if (!empty($cuInvoiceNo)){
                            $cuInvoiceNo = "|" . $cuInvoiceNo;
                        }
                        $tax_totals += $tax;
                        $total += $subtotal;
                    @endphp
                    <tr class="dotted">
                        <td class="align-c">{{$pin}}</td>
                        <td>{{$customer_name}}</td>
                        <td>{{$date}}</td>
                        <td>{{$cuInvoiceNo}}</td>
                        <td>{{$note}}</td>
                        <td>{{numberFormat($tax)}}</td>
                        <td>{{numberFormat($subtotal)}}</td>
                        <td>{{$invoice_tid}}</td>
                        <td>{{$cn_invoice_date}}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4"></td>
                    <td>Total Sales tax</td>
                    <td>{{numberFormat($tax_totals)}}</td>
                    <td>{{numberFormat($total)}}</td>
                </tr>
            </tfoot>
        </table> 
    @elseif ($lang['type'] == 2)
        <div style="text-align: center; line-height: 0">
            <h1>{{ auth()->user()->business->cname }}</h1>
            <h2>Tax Purchase Report Generated on {{ date('d-m-Y') }}</h2>
            <h2>Tax Report From {{$lang['month_from']}} to {{$lang['month_to']}}</h2>
            <h5>{{$lang['title2']}}</h5>
        </div>

        <table class="table table-items" cellpadding=8>
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Pin</th>
                    <th>Supplier</th>
                    <th>Invoice Date</th>
                    <th>Invoice No.</th>
                    <th>Description</th>
                    <th>Tax</th>
                    <th>Taxable Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                    $tax_totals = 0;
                @endphp
                {{-- {{dd($account_details)}} --}}
                @foreach ($account_details as $item)
                    @php
                    $pin = '';
                        $bill = $item->bill;
                        $suppliername = '';
                        if ($bill && $bill->document_type) {
                            if ($bill->document_type == 'direct_purchase') {
                                $purchase = $bill->purchase;
                                $purchase = $purchase;
                                $pin .= @$purchase->supplier_taxid;
                                $suppliername .= $purchase->suppliername;
                            } elseif ($bill->supplier) {
                                $purchase = null;
                                $pin .= $bill->supplier->taxid;
                            }
                            $bill = $bill;
                            $supplier = $bill->supplier;
                            $suppliername .= @$supplier->name;
                            $debit_note = null;
                        } elseif ($item->debit_note) {
                            $debit_note = $item->debit_note;
                            $pin .= @$debit_note->supplier->taxid;
                            $debit_note = $debit_note;
                            $supplier = $debit_note->supplier;
                            $bill = null;
                            $purchase = null;
                            $suppliername .= @$supplier->name;
                        }
                        

                        $date = '';
                        $tid = '';
                        $note = '';
                        $tax = 0;
                        $subtotal = 0;
                        if (@$debit_note) {
                            $date = $debit_note->date;
                            $tid = $debit_note->tid;
                            $note = 'Credit Note';
                            $tax = $debit_note->tax;
                            $subtotal = $debit_note->subtotal;
                        }
                        elseif ($bill) {
                            $date = $bill->date;
                            $tid = $item->bill->reference;
                            if ($bill->tax_rate == 8) $note = 'Fuel';
                            $note = 'Goods';
                            $tax = $bill->tax;
                            $subtotal = $bill->subtotal;
                        }elseif ($item->type == 'purchase') {
                            # code...
                            // dd($item);
                            $pin .= @$item->tax_pin;
                            $suppliername .= $item->supplier;
                            $date = $item->date;
                            $tid = $item->reference;
                            if ($item->tax_rate == 8) $note = 'Fuel';
                            $note = 'Goods';
                            $tax = $item->tax;
                            $subtotal = $item->subtotal;
                        }
                        $name = Illuminate\Support\Str::limit($suppliername,47);
                        if ($date) $date = dateFormat($date, 'd/m/Y');
                        if ($tid[0] != 0 && is_numeric($tid[0])) $tid =  "'" . $tid;


                        $tax_totals += $tax;
                        $total += $subtotal;
                    @endphp
                    <tr class="dotted">
                        <td class="align-c">Local</td>
                        <td class="align-c">{{$pin}}</td>
                        <td>{{$name}}</td>
                        <td>{{$date}}</td>
                        <td>{{$tid}}</td>
                        <td>{{$note}}</td>
                        <td>{{numberFormat($tax)}}</td>
                        <td>{{numberFormat($subtotal)}}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5"></td>
                    <td>Total Purchases Tax</td>
                    <td>{{numberFormat($tax_totals)}}</td>
                    <td>{{numberFormat($total)}}</td>
                </tr>
            </tfoot>
        </table>
    @elseif($lang['type'] == 3)
    <div style="text-align: center; line-height: 0">
        <h1>{{ auth()->user()->business->cname }}</h1>
        <h2>Combined Tax Report Generated on {{ date('d-m-Y') }}</h2>
        <h2>Tax Report From {{$lang['month_from']}} to {{$lang['month_to']}}</h2>
        <h5>{{$lang['title3']}}</h5>
    </div>
    <h4>Sale Tax</h4>
    <table class="table table-items" cellpadding=8>
        <thead>
            <tr>
                <th>Pin</th>
                <th>Customer</th>
                <th>Invoice Date</th>
                <th>CU Invoice No.</th>
                <th>Description</th>
                <th>Tax</th>
                <th>Taxable Amount</th>
                <th>Invoice No.</th>
                <th>CN Invoice Date</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_sale = 0;
                $tax_sale_totals = 0;
            @endphp
            @foreach ($account_details['sales'] as $item)
                @php
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
                        $customer_name = Illuminate\Support\Str::limit($customer->company,47);
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
                        $subtotal = -1* $credit_note->subtotal;
                        $invoice_tid = gen4tid('CN-', $credit_note->tid);
                        $invoice = $credit_note->invoice;
                        if ($invoice) $cn_invoice_date .= dateFormat($invoice->invoicedate, 'd/m/Y');
                    }
                    elseif ($invoice) {
                        $date = $invoice->invoicedate;
                        $cuInvoiceNo = $invoice->cu_invoice_no ?? '';
                        $note = $invoice->notes;
                        $tax = $invoice->tax;
                        $subtotal = $invoice->subtotal;
                        $invoice_tid = gen4tid('INV-', $invoice->tid);
                    }
                    if ($item->type == 'invoice') {
                           
                        $pin = $item->tax_pin;
                        $customer_name = Illuminate\Support\Str::limit($item->customer,47);
                        $date = $item->invoice_date;
                        $cuInvoiceNo = $item->cu_invoice_no ?? '';
                        $note = $item->note;
                        $tax = $item->tax;
                        $subtotal = $item->subtotal;
                        $invoice_tid = gen4tid('INV-', $item->invoice_tid);
                    }
                    if ($item->type == "credit_note") {
                        $pin = $item->tax_pin;
                        $customer_name = Illuminate\Support\Str::limit($item->customer,47);
                        $date = $item->invoice_date;
                        $cuInvoiceNo = $item->cu_invoice_no ?? '';
                        $note = $item->note;
                        $tax = $item->tax;
                        $subtotal = $item->subtotal;
                        $invoice_tid = gen4tid('CN-', $item->credit_note_tid);
                        $cn_invoice_date = dateFormat($item->invoice_date, 'd/m/Y');
                    }
                    if ($date) $date = dateFormat($date, 'd/m/Y');
                    if (!empty($cuInvoiceNo)){
                        $cuInvoiceNo = "|" . $cuInvoiceNo;
                    }
                    $tax_sale_totals += $tax;
                    $total_sale += $subtotal;
                @endphp
                <tr class="dotted">
                    <td class="align-c">{{$pin}}</td>
                    <td>{{$customer_name}}</td>
                    <td>{{$date}}</td>
                    <td>{{$cuInvoiceNo}}</td>
                    <td>{{$note}}</td>
                    <td>{{numberFormat($tax)}}</td>
                    <td>{{numberFormat($subtotal)}}</td>
                    <td>{{$invoice_tid}}</td>
                    <td>{{$cn_invoice_date}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"></td>
                <td><b>Total Sales tax</b></td>
                <td><b>{{numberFormat($tax_sale_totals)}}</b></td>
                <td><b>{{numberFormat($total_sale)}}</b></td>
            </tr>
        </tfoot>
    </table> 
    <div class="clear-fix"></div>
    <h4 class="mt-3">Purchase Tax</h4>
    <table class="table table-items" cellpadding=8>
        <thead>
            <tr>
                <th>Source</th>
                <th>Pin</th>
                <th>Supplier</th>
                <th>Invoice Date</th>
                <th>Invoice No.</th>
                <th>Description</th>
                <th>Tax</th>
                <th>Taxable Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
                $tax_totals = 0;
            @endphp
            @foreach ($account_details['purchase'] as $item)
                @php
                $pin = '';
                $bill = $item->bill;
                $suppliername = '';
                if ($bill && $bill->document_type) {
                    if ($bill->document_type == 'direct_purchase') {
                        $purchase = $bill->purchase;
                        $purchase = $purchase;
                        $pin .= @$purchase->supplier_taxid;
                        $suppliername .= $purchase->suppliername;
                    } elseif ($bill->supplier) {
                        $purchase = null;
                        $pin .= $bill->supplier->taxid;
                    }
                    $bill = $bill;
                    $supplier = $bill->supplier;
                    $suppliername .= @$supplier->name;
                    $debit_note = null;
                } elseif ($item->debit_note) {
                    $debit_note = $item->debit_note;
                    $pin .= @$debit_note->supplier->taxid;
                    $debit_note = $debit_note;
                    $supplier = $debit_note->supplier;
                    $bill = null;
                    $purchase = null;
                    $suppliername .= @$supplier->name;
                }
                

                $date = '';
                $tid = '';
                $note = '';
                $tax = 0;
                $subtotal = 0;
                if (@$debit_note) {
                    $date = $debit_note->date;
                    $tid = $debit_note->tid;
                    $note = 'Credit Note';
                    $tax = $debit_note->tax;
                    $subtotal = $debit_note->subtotal;
                }
                elseif ($bill) {
                    $date = $bill->date;
                    $tid = $item->bill->reference;
                    if ($bill->tax_rate == 8) $note = 'Fuel';
                    $note = 'Goods';
                    $tax = $bill->tax;
                    $subtotal = $bill->subtotal;
                }elseif ($item->type == 'purchase') {
                    $pin .= @$item->tax_pin;
                    $suppliername .= $item->supplier;
                    $date = $item->date;
                    $tid = $item->reference;
                    if ($item->tax_rate == 8) $note = 'Fuel';
                    $note = 'Goods';
                    $tax = $item->tax;
                    $subtotal = $item->subtotal;
                }
                $name = Illuminate\Support\Str::limit($suppliername,47);


                    $tax_totals += $tax;
                    $total += $subtotal;
                @endphp
                <tr class="dotted">
                    <td class="align-c">Local</td>
                    <td class="align-c">{{$pin}}</td>
                    <td>{{$name}}</td>
                    <td>{{$date}}</td>
                    <td>{{$tid}}</td>
                    <td>{{$note}}</td>
                    <td>{{numberFormat($tax)}}</td>
                    <td>{{numberFormat($subtotal)}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"></td>
                <td colspan="2"><b>Total Purchases Tax</b></td>
                <td><b>{{numberFormat($tax_totals)}}</b></td>
                <td><b>{{numberFormat($total)}}</b></td>
            </tr>
        </tfoot>
    </table>
    <br>
    <table class="subtotal">
        <thead>
        <tbody>
        <tr>
            <td class="myco2" rowspan="2"><br>
            </td>
            <td class="summary"><strong>{{trans('general.summary')}}</strong></td>
            <td class="summary"></td>
        </tr>
        <tr>
            <td><b>Total Tax Difference (Sales - Purchases):</b></td>
            <td><b>{{amountFormat($tax_sale_totals - $tax_totals)}}</b></td>
        </tr>

        </tbody>
    </table>
    @endif
    
</body>
</html>