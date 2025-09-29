@extends ('core.layouts.app')

@section('title', 'Stock Adjustment')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Adjustment</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_adjs.partials.stockadjs-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            @permission('approve-stock-adj')
                <div class="card-header pb-0">
                    <a href="#" class="btn btn-warning btn-sm mr-1 float-left" data-toggle="modal" data-target="#statusModal">
                        <i class="fa fa-pencil" aria-hidden="true"></i> Approval Status
                    </a>
                    @if ($stock_adj->approval_status == 'Approved')
                        <span class="badge badge-primary round mr-auto ml-auto d-table">{{ $stock_adj->approval_status }} by {{ auth()->user()->full_name }}</span>
                    @else
                        <span class="badge badge-secondary round mr-auto ml-auto d-table">{{ $stock_adj->approval_status }} Approval</span>
                    @endif
                </div>
            @endauth

            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Adjustment Type' => $stock_adj->adjustment_type,
                                'Date' => dateFormat($stock_adj->date, 'd-M-Y'),
                                'Note' => $stock_adj->note,
                                'Account' => @$stock_adj->account->holder,
                                'Total Amount' => numberFormat($stock_adj->total),
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
                        <table class="table table-sm tfr my_stripe_single" id="invoiceTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th width="30%">Stock Item</th>
                                    <th>Unit</th>
                                    <th>Qty On-Hand</th>
                                    <th>New Qty</th>
                                    <th>Qty Diff</th>
                                    <th width="15%">Cost</th>
                                    <th width="15%">Amount</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($stock_adj->items as $i => $item)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $item->productvar->name }}</td>
                                        <td>{{ @$item->productvar->product->unit->code }}</td>
                                        <td>{{ +$item->qty_onhand }}</td>
                                        <td>{{ +$item->new_qty }}</td>
                                        <td>{{ +$item->qty_diff }}</td>
                                        <td>{{ numberFormat($item->cost) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
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
@include('focus.stock_adjs.partials.status_modal')
@endsection
