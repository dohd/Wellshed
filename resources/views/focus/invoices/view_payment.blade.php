@extends ('core.layouts.app')

@section('title', 'Invoice Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.payments-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table id="payment-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>   
                            @php
                                $payment_details = [
                                    'Payment No' => $payment->tid,
                                    'Customer' => $payment->customer ? $payment->customer->company : '',
                                    'Date' => dateFormat($payment->date),
                                    'Amount' => numberFormat($payment->amount),
                                    'Allocated Amount' => numberFormat($payment->allocate_ttl),
                                    'Payment Mode' => $payment->payment_mode,
                                    'Reference' => $payment->reference,
                                    'Payment Account' => $payment->account? $payment->account->holder : '',
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
                    <!-- invoices -->
                    <div class="table-responsive">
                        <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Invoice No</th>
                                    <th>Note</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>                                    
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($payment->items as $i => $item)
                                    @if ($item->invoice)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ dateFormat($item->invoice->invoiceduedate) }}</td>
                                            <td>{{ gen4tid('Inv-', $item->invoice->tid) }}</td>
                                            <td>{{ $item->invoice->notes }}</td>
                                            <td>{{ $item->invoice->status }}</td>
                                            <td>{{ numberFormat($item->invoice->total) }}</td>
                                            <td>{{ numberFormat($item->invoice->amountpaid) }}</td>
                                            <td>{{ numberFormat($item->invoice->total - $item->invoice->amountpaid) }}</td>                                            
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>                
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
