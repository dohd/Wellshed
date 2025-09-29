@extends ('core.layouts.app')

@section('title', 'View | Invoice Estimate')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Estimate</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.estimates.partials.estimate-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $quote = $estimate->quote;
                            $invoice = $estimate->invoice;
                            $details = [
                                'Estimate No.' => gen4tid('EST-', $estimate->tid),
                                'Date' => dateFormat($estimate->date, 'd-M-Y'),
                                'Customer' => @$estimate->customer->company ?: @$estimate->customer->name,
                                'Quote / PI' => $quote? gen4tid($quote->bank_id? 'PI-': 'QT-', $quote->tid) : '',
                                'Total Estimated Amount' => numberFormat($estimate->est_total),
                                'Balance' => numberFormat($estimate->balance),
                                'Total Amount' => numberFormat($estimate->total),
                                'Note' => $estimate->note,
                                'Invoice No' => $invoice? gen4tid('INV-', $invoice->tid) : '',
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="table-responsive">
                        <table class="table table-sm tfr my_stripe_single">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th width="25%">Product</th>
                                    <th>UoM</th>
                                    <th>Qty</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                    <th>Est. Qty</th>
                                    <th>Est. Rate</th>
                                    <th>Est. Amount</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($estimate->items as $i => $item)
                                    <tr>
                                        <td>{{ @$item->vrf_item->numbering }}</td>
                                        <td>{{ @$item->vrf_item->product_name }}</td>
                                        <td>{{ @$item->vrf_item->unit }}</td>
                                        <td>{{ +$item->qty }}</td>
                                        <td>{{ numberFormat($item->rate) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
                                        <td class="font-weight-bold">{{ +$item->est_qty }}</td>
                                        <td class="font-weight-bold">{{ numberFormat($item->est_rate) }}</td>
                                        <td class="font-weight-bold">{{ numberFormat($item->est_amount) }}</td>
                                    </tr>
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
