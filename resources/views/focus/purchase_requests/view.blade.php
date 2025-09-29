@extends ('core.layouts.app')

@section('title', 'Material Requisition Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Material Requisition Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.purchase_requests.partials.purchase-request-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header">

                    <div class="btn-group">
                        @permission('material_request_approval')  
                        <a href="#" class="btn btn-warning" data-toggle="modal" data-target="#statusModal">
                            <i class="fa fa-pencil" aria-hidden="true"></i> Status
                        </a>&nbsp;
                        @endauth

                        {{-- @if(@$purchase_request->status === 'approved')
                            <a href="{{ route('biller.rfq.create', ['purchase_request_id' => @$purchase_request->id]) }}" class="btn btn-pinterest" target="_blank">
                                <i class="fa fa-plane"></i> Generate RFQ
                            </a>
                        @endif --}}
                    </div>

                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php 
                            $req = $purchase_request;                        
                            $details = [
                                'Requisition No.' => gen4tid('REQ-', $req->tid),
                                'Status' => $req->status,
                                'Priority' => $req->priority,
                                'Date' => dateFormat($req->date),
                                'Employee' => $req->employee? $req->employee->full_name : '',
                                'Expected Delivery Date' => dateFormat($req->expect_date),
                                'Remark' => $req->note,
                                'Project' => $req->project ? gen4tid('PRJ-',@$req->project->tid) .' '. @$req->project->name : '',
                                'Item List Description' => $req->item_descr,
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
              <div class="card-body">
                    <h4 class="mb-3">Related Requisitions</h4>

                    @if ($purchase_request->purchaseRequisitions->count())
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Purchase Requisition</th>
                                        <th>Purchase Order</th>
                                        <th>Stock Issue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchase_request->purchaseRequisitions as $pr)
                                        <tr>
                                            <td>
                                                <a href="{{ route('biller.purchase_requisitions.show', $pr->id) }}" class="text-primary">
                                                    {{ gen4tid('PR-', $pr->tid) }} - 
                                                </a>{{ $pr->note }}
                                            </td>
                                            <td>
                                                @if ($pr->purchaseOrder)
                                                    <a href="{{ route('biller.purchaseorders.show', $pr->purchaseOrder->id) }}" class="text-success">
                                                        {{ gen4tid('PO-', $pr->purchaseOrder->tid) }} - 
                                                    </a>{{ $pr->purchaseOrder->note }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($pr->stockIssue)
                                                    <a href="{{ route('biller.stock_issues.show', $pr->stockIssue->id) }}" class="text-warning">
                                                        {{ gen4tid('ISS-', $pr->stockIssue->tid) }} - 
                                                    </a>{{ $pr->stockIssue->note }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
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
                                <th>Previous Requested Qty</th>
                                <th>Milestone/Budgeted Qty</th>
                                <th>Requested Qty</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @isset($purchase_request)
                                @foreach ($purchase_request->items as $k => $item)
                                    <tr>
                                        <td><span class="numbering">{{$k+1}}</span></td>
                                        <td>{{$item->product_name}}</td>
                                        <td>{{ @$item->unit->code }}
                                        </td> 
                                        <td><span class="code" id="code-p{{$k}}">{{$item->product->code}}</span></td>
                                        @php
                                            $requested_qty = 0;
                                            $budget_qty = 0;
                                            if($item->budget_item){
                                                $requested_qty = numberFormat($item->budget_item->qty_requested);
                                                $budget_qty = numberFormat($item->budget_item->new_qty);
                                            }
                                            if($item->milestone_item && empty($item->budget_item)){
                                                $requested_qty = numberFormat($item->milestone_item->qty);
                                                $budget_qty = numberFormat($item->milestone_item->qty_requested);
                                            }
                                        @endphp
                                        <td>{{ $requested_qty }}</td>
                                        <td>{{ $budget_qty}}</td>
                                        <td>{{numberFormat($item->qty)}}</td>
                                        <td>{{$item->remark}}</td>
                                    </tr>
                                @endforeach
                            @endisset
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('focus.purchase_requests.partials.status-modal')
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