@extends ('core.layouts.app')

@section ('title', 'RFQ Management')

@section('content')
@php $rfq = $rfq; @endphp
@php $purchaseorder = $rfq; @endphp
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>RFQ Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.rfq.partials.rfq-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">

            {{-- <a href="{{ route('biller.print-rfq', $rfq->id) }}" class="btn btn-purple btn-sm" target="_blank">
                <i class="fa fa-print" aria-hidden="true"></i> Print
            </a> --}}
            <a href="#" class="btn btn-purple btn-sm mr-1" data-toggle="modal" data-target="#rfqPrintModal">
                <i class="fa fa-print" aria-hidden="true"></i> Print
            </a>

            <a href="{{ route('biller.rfq.edit', $rfq->id) }}" class="btn btn-blue btn-sm" target="_blank">
                <i class="fa fa-pencil" aria-hidden="true"></i> Edit
            </a>

            <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#rfqStatusModal">
                <i class="fa fa-pencil" aria-hidden="true"></i> Status
            </a>
            <a href="#" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#sendRfqModal">
                <i class="fa fa-mail" aria-hidden="true"></i> Send RFQ
            </a>

        </div>
        <div class="card-body">            
            <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
                        RFQ Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
                        Inventory / Stock
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">
                        Services
                    </a>
                </li>
            </ul>

            <div class="tab-content px-1 pt-1">
                <!-- PO details -->
                <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                    {{-- @if ($rfq->closure_status)
                        <div class="badge text-center white d-block m-1">
                            <span class="bg-danger round p-1"><b>Purchase Order Closed</b></span>
                        </div>
                        <h6 class="text-center">
                            {{ $rfq->closure_reason }}
                        </h6>
                    @endif   --}}
                    <br>
                    <table id="customer-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>  
                            @php   
                                $details = [
                                    'RFQ NO' => gen4tid('RFQ-', $rfq->tid),
                                    'Subject' => $rfq->subject,
                                    'Date' => dateFormat($rfq->date),
                                    'Due Date' => dateFormat($rfq->due_date),
                                    'Status' => $rfq->status,
                                    'Project' => $rfq->project ? gen4tid('Prj-', $rfq->project->tid) . '; ' . $rfq->project->name : '',
                                ];
                            @endphp
                            @foreach ($details as $key => $val)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                            @endforeach                            
                            {{-- <tr>
                                <th>Order Items Cost</th>
                                <td>
                                    <b>Stock:</b>   {{ amountFormat($rfq->stock_grandttl) }}<br>
                                    <b>Expense:</b> {{ amountFormat($rfq->expense_grandttl) }}<br>
                                    <b>Asset:</b> {{ amountFormat($rfq->asset_grandttl) }}<br>
                                    <b>Total:</b> {{ amountFormat($rfq->grandttl) }}<br>
                                </td>
                            </tr>                               --}}
                        </tbody>
                    </table>
                    <br>
                    <div class="row">
                        @php
                           $purchase_requisition_ids = array_filter(explode(',', $rfq->purchase_requisition_ids));
                           $prs = [];
                           $rfq_analysis_id = '';
                           $rfq_analysis_tid = '';
                           if(count($purchase_requisition_ids) > 0){
                            foreach ($purchase_requisition_ids as $i => $val) {
                                $pr = App\Models\purchase_requisition\PurchaseRequisition::find($val);
                                $prs[] = ['id' => $pr->id, 'tid' => gen4tid('PR-',$pr->tid)];
                            }
                           } 
                           if(count($rfq->rfq_analysis) > 0){
                                $rfq_analysis = $rfq->rfq_analysis()->first();
                                $rfq_analysis_id = $rfq_analysis->id;
                                $rfq_analysis_tid = gen4tid('RFQA-',$rfq_analysis->tid);
                            }
                        //    dd($prs);

                        @endphp
                        @if (count($purchase_requisition_ids) > 0)
                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                            <p>PR Numbers.</p>
                        </div>
                        <div class="col border-blue-grey border-lighten-5 p-1 font-weight-bold">
                            @foreach ($prs as $item)
                            <a href="{{ route('biller.purchase_requisitions.show', $item['id']) }}" target="_blank">
                                <p>{{ $item['tid'] }}</p>
                            </a>
                            @endforeach
                            
                        </div>
                        @endif
                       
                        @if (count($rfq->rfq_analysis) > 0)
                            <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                <p>RFQ Analysis No.</p>
                            </div>
                            <div class="col border-blue-grey border-lighten-5 p-1 font-weight-bold">
                                <a href="{{ route('biller.rfq_analysis.show', $rfq_analysis_id) }}" target="_blank">
                                    <p>{{ $rfq_analysis_tid }}</p>
                                </a>
                            </div>
                        @endif
                        
                    </div>       
                </div>

                <!-- Inventory/stock -->
                <div class="tab-pane" id="active2" aria-labelledby="equipment-maintained" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Quantity</th>
                            <th>UoM</th>
                            <th>Additional Product Specifications</th>
                            {{-- <th>Price</th> --}}
                            {{-- <th>Tax Rate</th>                             --}}
                            {{-- <th>Amount</th> --}}
                        </tr>
                        <tbody>
                            @foreach ($rfq->items as $key => $item)
                                @if ($item->type === 'STOCK')
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $item['product']['name'] }}</td>
                                        <td>{{ $item->product->code }}</td>
                                        <td>{{ number_format($item->quantity, 1) }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ $item->description }}</td>
                                        {{-- <td>{{ number_format($item->rate, 2) }}</td>                                         --}}
                                        {{-- <td>{{ number_format($item->taxrate, 2) }}</td> --}}
                                        {{-- <td>{{ number_format($item->amount, 2) }}</td> --}}
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Expense -->
                <div class="tab-pane" id="active3" aria-labelledby="other-details" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Quantity</th>
                            <th>UoM</th>
                            <th>Additional Specifications</th>
                        </tr>
                        <tbody>
                            @foreach ($rfq->items as $key => $item)
                                @if ($item->type === 'EXPENSE')
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $item->account->holder }}</td>
                                        <td>{{ number_format($item->qty, 1) }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ $item->description }}</td>
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
@include('focus.rfq.partials.change_status')
@include('focus.rfq.partials.print_rfq_modal')
@include('focus.rfq.partials.send_rfq')
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const configs = {

    };
    const Index = {
        init(){
            $('#supplier_ids').select2({allowClear: true});
            $('#suppliers').select2({allowClear: true});
        },
     
    };
    $(() => Index.init());
</script>
@endsection