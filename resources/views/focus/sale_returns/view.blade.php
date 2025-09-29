@extends ('core.layouts.app')

@section('title', 'Sale Returns')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Sale Returns</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.sale_returns.partials.salereturn-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Serial' => gen4tid('SR-', $sale_return->tid),
                                'Date' => dateFormat($sale_return->date),
                                'Customer' => (@$sale_return->customer->company ?: @$sale_return->customer->name),
                                'Invoice' => $sale_return->invoice? gen4tid('INV-', $sale_return->invoice->tid) . ' ' . $sale_return->invoice->notes : '',
                                'Quote / PI' => $sale_return->quote? gen4tid($sale_return->quote->bank_id? 'PI-' : 'QT-', $sale_return->quote->tid) . ' ' . $sale_return->quote->notes : '',
                                'Stock Total' => numberFormat($sale_return->total),
                                'Note' => $sale_return->note,
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
                                    <th width="25%">Stock Item</th>
                                    <th>Item Code</th>
                                    <th>UoM</th>
                                    <th>Qty On-Hand</th>
                                    <th>New Qty</th>
                                    <th>Return Qty</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($sale_return->items as $i => $item)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ @$item->productvar->name }}</td>
                                        <td><b>{{ @$item->productvar->code }}</b></td>
                                        <td>{{ @$item->productvar->product->unit->code }}</td>
                                        <td>{{ +$item->qty_onhand }}</td>
                                        <td>{{ +$item->new_qty }}</td>
                                        <td><b>{{ +$item->return_qty }}</b></td>
                                        <td>{{ @$item->warehouse->title }}</td>
                                        <td>{{ ucfirst($item->status) }}</td>
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
