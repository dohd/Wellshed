@extends ('core.layouts.app')

@section('title', 'Purchase Requisition Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Purchase Requisition Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.purchase_requisitions.partials.purchase-requisition-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header">

                    <div class="btn-group">
                        @permission('purchase_requisition_approval')  
                        <a href="#" class="btn btn-warning" data-toggle="modal" data-target="#statusModal">
                            <i class="fa fa-pencil" aria-hidden="true"></i> Status
                        </a>&nbsp;
                        @endauth
                        

                        @if(@$purchase_requisition->status === 'approved')
                            {{-- <a href="{{ route('biller.rfq.create', ['purchase_requisition_id' => @$purchase_requisition->id]) }}" class="btn btn-pinterest" target="_blank">
                                <i class="fa fa-plane"></i> Generate RFQ
                            </a> --}}
                        @endif
                    </div>

                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php 
                            $req = $purchase_requisition;                        
                            $details = [
                                'Requisition No.' => gen4tid('PR-', $req->tid),
                                'Material Requisition No.' => gen4tid('REQ-', @$req->purchase_request->tid),
                                'Status' => $req->status,
                                'Priority' => $req->priority,
                                'Date' => dateFormat($req->date),
                                'Employee' => $req->employee? $req->employee->full_name : '',
                                'Expected Delivery Date' => dateFormat($req->expect_date),
                                'Remark' => $req->note,
                                'Approved By' => $req->approved ? $req->approved->fullname : '',
                                'Status Note' => $req->status_note,
                                // 'Item List Description' => $req->item_descr,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if (in_array($key, ['Status', 'Priority']))
                                        <span class="font-weight-bold">{{ $val }}</span>
                                    @elseif ($key == 'Item List Description')
                                        {!! $val !!}
                                    @else
                                        {{ $val }}    
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">Related Requisitions</h4>

                    @if ($purchase_requisition)
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Purchase Order</th>
                                        <th>Stock Issue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            @if ($purchase_requisition->purchaseOrder)
                                                <a href="{{ route('biller.purchaseorders.show', $purchase_requisition->purchaseOrder->id) }}" class="text-success">
                                                    {{ gen4tid('PO-', $purchase_requisition->purchaseOrder->tid) }} - 
                                                </a>{{ $purchase_requisition->purchaseOrder->note }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($purchase_requisition->stockIssue)
                                                <a href="{{ route('biller.stock_issues.show', $purchase_requisition->stockIssue->id) }}" class="text-warning">
                                                    {{ gen4tid('ISS-', $purchase_requisition->stockIssue->tid) }} - 
                                                </a>{{ $purchase_requisition->stockIssue->note }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No Purchase Requisitions linked to this MR.</p>
                    @endif
                </div>
                <div class="card-body">
                    <table id="requisitionsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr class="bg-gradient-directional-blue white">
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Unit</th>
                                <th>Product Code</th>
                                <th>Qty In Stock</th>
                                <th>Qty to Purchase</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @isset($purchase_requisition)
                            @php
                                $total_qty_stock = 0;
                                $total_qty_purchase = 0;
                                $total_qty = 0;
                            @endphp
                                @foreach ($purchase_requisition->items as $k => $item)
                                @php
                                   $total_qty_stock += $item->stock_qty; 
                                   $total_qty_purchase += $item->purchase_qty; 
                                   $total_qty += $item->qty; 
                                @endphp
                                    <tr>
                                        <td><span class="numbering">{{$k+1}}</span></td>
                                        <td>{{$item->product_name}}</td>
                                        <td>{{ @$item->unit->code }}
                                        </td> 
                                        <td><span class="code" id="code-p{{$k}}">{{$item->product->code}}</span></td>
                                       
                                        <td>{{ numberFormat($item->stock_qty) }}</td>
                                        <td>{{ numberFormat($item->purchase_qty)}}</td>
                                        <td>{{numberFormat($item->qty)}}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4">Totals</td>
                                    <td>{{$total_qty_stock}}</td>
                                    <td>{{$total_qty_purchase}}</td>
                                    <td>{{$total_qty}}</td>
                                </tr>
                            @endisset
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('focus.purchase_requisitions.partials.status-modal')
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        };
        const Index = {
            init(){
                $.ajaxSetup(config.ajax);
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            },
        };
        $(()=>Index.init());
    </script>
@endsection