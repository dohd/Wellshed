@extends ('core.layouts.app')

@section('title', 'Invoice Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoice_payments.partials.invoice-payment-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-header">
                <a href="#" class="btn btn-primary mb-1" data-toggle="modal" data-target="#sendInvoicePaymentModal">
                    <i class="fa fa-paper-plane-o"></i> Send SMS & Email
                </a>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <table id="payment-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                                <tbody>   
                                    @php
                                        $payment_details = [
                                            'Payment No' => gen4tid('RCPT-', $invoice_payment->tid),
                                            'Customer' => (@$invoice_payment->customer->company ?:  @$invoice_payment->customer->name),
                                            'Payment Type' => $invoice_payment->payment_type,
                                            'Payment Date' => dateFormat($invoice_payment->date),
                                            'Payment Account' => @$invoice_payment->account->holder,
                                            'Payment Mode' => $invoice_payment->payment_mode,
                                            'Reference' => $invoice_payment->reference,
                                        ];
                                    @endphp   
                                    @foreach ($payment_details as $key => $val)
                                        <tr>
                                            <th>{{ $key }}</th>
                                            <td>{{ $val }}</td>
                                        </tr>
                                    @endforeach                           
                                </tbody>
                            </table>
                        </div>
                        <div class="col-6">
                            <table id="payment-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                                <tbody>   
                                    @php
                                        $payment_details = [
                                            
                                            'Amount' => numberFormat($invoice_payment->amount),
                                            'Allocated Amount' => numberFormat($invoice_payment->allocate_ttl),
                                            'WH VAT Amount' => numberFormat($invoice_payment->wh_vat_amount),
                                            'WH TAX Amount' => numberFormat($invoice_payment->wh_tax_amount),
                                        ];
                                    @endphp   
                                    @foreach ($payment_details as $key => $val)
                                        <tr>
                                            <th>{{ $key }}</th>
                                            <td>{{ $val }}</td>
                                        </tr>
                                    @endforeach                           
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Invoice No</th>
                                    <th width="20%">Note</th>
                                    <th>Status</th>
                                    <th>Original Amt</th>
                                    <th>Receipt Amt</th>
                                    <th>WH VAT Amt</th>
                                    <th>WH Tax Amt</th>
                                    <th>Amount Due</th>                                    
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($invoice_payment->items()->whereHas('invoice')->get() as $i => $item)
                                    <tr>
                                        <th>{{ $i+1 }}</th>
                                        <td>{{ dateFormat($item->invoice->invoiceduedate) }}</td>
                                        <td>{{ gen4tid('Inv-', $item->invoice->tid) }}</td>
                                        <td>{{ $item->invoice->notes }}</td>
                                        <td>{{ $item->invoice->status }}</td>
                                        <td>{{ numberFormat($item->invoice->total) }}</td>
                                        <td>{{ numberFormat($item->paid) }}</td>
                                        <td>{{ numberFormat($item->wh_vat) }}</td>
                                        <td>{{ numberFormat($item->wh_tax) }}</td>
                                        <td>{{ numberFormat($item->invoice->total - $item->invoice->amountpaid) }}</td>                                            
                                    </tr>
                                @endforeach
                            </tbody>                
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.invoice_payments.partials.send_invoice_payments')
    </div>
</div>
@endsection
