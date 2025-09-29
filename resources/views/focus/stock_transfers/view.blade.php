@extends ('core.layouts.app')

@section('title', 'View | Stock Transfer')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Transfer</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_transfers.partials.stock-transfer-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header pb-0 mb-0">
                    {{-- <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#Stock TransferStatusModal">
                        <i class="fa fa-pencil" aria-hidden="true"></i> Status
                    </a> --}}
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Date' => dateFormat($stock_transfer->date, 'd-M-Y'),
                                'Reference No' => $stock_transfer->ref_no,
                                'Transfer From' => @$stock_transfer->source->title,
                                'Transfer To' => @$stock_transfer->destination->title,
                                'Note' => $stock_transfer->note,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if ($key == 'Stock Transfer Status')
                                        <span class="text-success">{{ $val }}</span>
                                    @else
                                        {{ $val }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="table-responsive">
                        <table class="table table-sm tfr my_stripe_single">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th width="30%">Stock Item</th>
                                    <th>Unit</th>
                                    <th>Qty On-Hand</th>
                                    <th>Qty Rem</th>
                                    <th>Transf. Qty</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($stock_transfer->items as $i => $item)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $item->productvar->name }}</td>
                                        <td>{{ @$item->productvar->product->unit->code }}</td>
                                        <td>{{ +$item->qty_onhand }}</td>
                                        <td>{{ +$item->qty_rem }}</td>
                                        <td>{{ +$item->qty_transf }}</td>
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
