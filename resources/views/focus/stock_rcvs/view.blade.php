@extends ('core.layouts.app')

@section('title', 'View | Stock Receiving')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Receiving</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_rcvs.partials.stock-rcv-header-buttons')
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
                                'Transfer NO.' => gen4tid('STR-', @$stock_rcv->stock_transfer->tid),
                                'Received By' => @$stock_rcv->receiver->full_name,
                                'Date' => dateFormat($stock_rcv->date, 'd-M-Y'),
                                'Reference No' => $stock_rcv->ref_no,
                                'Note' => $stock_rcv->note,
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
                                    <th width="30%">Stock Item</th>
                                    <th>Unit</th>
                                    <th>Transf. Qty</th>
                                    <th>Qty Rem</th>
                                    <th width="10%">Received. Qty</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($stock_rcv->items as $i => $item)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $item->productvar->name }}</td>
                                        <td>{{ @$item->productvar->product->unit->code }}</td>
                                        <td>{{ +$item->qty_transf }}</td>
                                        <td>{{ +$item->qty_rem }}</td>
                                        <td>{{ +$item->qty_rcv }}</td>
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
