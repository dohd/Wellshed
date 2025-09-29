@extends ('core.layouts.app')

@section ('title', 'Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="content-header-title mb-0">Payment Management</h3>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="card-body">
                <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
                            Payment Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
                            Payments
                        </a>
                    </li>
                </ul>

                <div class="tab-content px-1 pt-1">
                    <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                        <table id="" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                            <tbody> 
                                @php
                                    $payment_details = [
                                        'Payment Type' => $payment->supplier_id ? 'BILL' : 'INVOICE',
                                        'Transaction ID' => $payment->tid,
                                        'Date & Due Date' => $payment->date . ' : ' . $payment->due_date,
                                        'Reference' => $payment->doc_ref_type . ' - ' . $payment->doc_ref,
                                        'Debt' => numberFormat($payment->amount_ttl),
                                        'Paid' => numberFormat($payment->deposit),
                                        'Settled' => numberFormat($payment->deposit_ttl)
                                    ];
                                @endphp   
                                @foreach ($payment_details as $key => $value)
                                    <tr>
                                        <th>{{ $key }}</th>
                                        <td>{{ $value }}</td>
                                    </tr>
                                @endforeach                                                           
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane" id="active2" aria-labelledby="equipment-maintained" role="tabpanel">
                        <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                            <tr>
                                <th>{{ $payment->supplier_id ? 'Supplier' : 'Invoice' }}</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Document</th>
                                <th>Date</th>
                                <th>Due Date</th>
                            </tr>
                            <tbody>
                                @foreach ($payment->items as $item)
                                    @if ($payment->supplier_id)
                                        <tr>
                                            <td>{{ $item->bill->supplier->name }}</td>
                                            <td>{{ number_format($item->bill->grandttl, 2) }}</td>
                                            <td>{{ number_format($item->bill->amountpaid, 2) }}</td>
                                            <td>{{ $item->bill->doc_ref_type }}-{{ $item->bill->doc_ref }}</td>
                                            <td>{{ $item->bill->date }}</td>
                                            <td>{{ $item->bill->due_date }}</td>
                                        </tr>
                                    {{-- 
                                    @else
                                        <tr>
                                            <td>{{ $item->invoice->customer->name }}</td>
                                            <td>{{ number_format($item->invoice->total, 2) }}</td>
                                            <td>{{ number_format($item->invoice->amountpaid, 2) }}</td>
                                            <td>Invoice</td>
                                            <td>{{ $item->invoice->invoicedate }}</td>
                                            <td>{{ $item->invoice->invoiceduedate }}</td>
                                        </tr>
                                    --}}
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
