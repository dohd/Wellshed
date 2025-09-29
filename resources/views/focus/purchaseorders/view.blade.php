@extends ('core.layouts.app')
@section ('title', 'Purchase Order Management')

@section('content')
@php $po = $purchaseorder; @endphp
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>Purchase Order Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchaseorders.partials.purchaseorders-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header pb-0">
            @php
               $approvers = @$purchaseorder->statuses()->where('approval_status','approved')->get();
               $no_of_approvers = count(explode(',',$purchaseorder->user_ids)); 
               $alreadyApprovedByCurrentUser = $approvers->pluck('approved_by')->contains(auth()->id());
            @endphp
           @if ($approvers && $approvers->count() === $no_of_approvers)
                <a href="{{ route('biller.print_purchaseorder', [$purchaseorder->id, 9, token_validator('', 'po' . $purchaseorder->id, true), 1]) }}" class="btn btn-purple btn-sm" target="_blank">
                    <i class="fa fa-print" aria-hidden="true"></i> Print
                </a>&nbsp;
            @endif
            @permission('delete-purchase')
                <a href="#" class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                    <i class="fa fa-times" aria-hidden="true"></i> Close Order
                </a>
            @endauth
            @if ($approvers && $approvers->count() === $no_of_approvers)
            <a href="#" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#smsModal">
                <i class="fa fa-status" aria-hidden="true"></i> Send SMS
            </a>
            @endif
            @permission('lpo_approval') 
                @if (!$alreadyApprovedByCurrentUser)
                    <a href="#" class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#approvalModal">
                        <i class="fa fa-status" aria-hidden="true"></i> Status
                    </a>
                @endif
            @endauth
        </div>  
        <div class="card-body">            
            <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                @if ($po->closure_status)
                    <div class="badge text-center white d-block m-1">
                        <span class="bg-danger round p-1"><b>Purchase Order Closed</b></span>
                    </div>
                    <h6 class="text-center">
                        {{ $po->closure_reason }}
                    </h6>
                @endif  

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="table-responsive">
                            <table id="customer-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                                <tbody>  
                                    @php   
                                        $details = [
                                            'Order NO' => gen4tid('PO-', $po->tid),
                                            'Supplier' => @$po->supplier->name,
                                            'Date' => dateFormat($po->date),
                                            'Due Date' => dateFormat($po->due_date),
                                            'Document' => $po->doc_ref_type && $po->doc_ref? "{$po->doc_ref_type} - {$po->doc_ref}" : '',
                                            'Project' => $po->project ? gen4tid('Prj-', $po->project->tid) . '; ' . $po->project->name : '',
                                            'Note' => $po->note,
                                        ];                       
                                    @endphp
                                    @foreach ($details as $key => $val)
                                        <tr>
                                            <th width="50%">{{ $key }}</th>
                                            <td>{{ $val }}</td>
                                        </tr>
                                    @endforeach                                                        
                                </tbody>
                            </table>                             
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                                <tbody>  
                                    @php   
                                        $grnRef = $po->grns->map(function($v) {
                                            return '<a href="'. route('biller.goodsreceivenote.show', $v) .'">'. gen4tid('GRN-', $v->tid) .'</a>';
                                        });
                                        $details = [
                                            'Currency' => $po->currency? "{$po->currency->code} / " . floatval(@$po->currency->rate) : '',
                                            'Stock Cost' => numberFormat($po->stock_grandttl),
                                            'Expense Cost' => numberFormat($po->expense_grandttl),
                                            'Asset Cost' => numberFormat($po->asset_grandttl),
                                            'Total Cost' => numberFormat($po->grandttl),
                                            'Note to Approvers' => $po->approval_note,
                                            'Goods Receipt Reference' => $grnRef->implode(', '),
                                        ];                       
                                    @endphp
                                    @foreach ($details as $key => $val)
                                        <tr>
                                            <th width="50%">{{ $key }}</th>
                                            <td>{!! $val !!}</td>
                                        </tr>
                                    @endforeach                                                        
                                </tbody>
                            </table>                            
                        </div>
                    </div>
                </div>
                <div class="row">
                    @if ($po->requisition_type == 'rfq')
                        @php
                            $rfq_analysis_tid = '';
                            $rfq_analysis_id = '';
                            if($po->rfq){
                                $rfq = $po->rfq;
                                if(count($rfq->rfq_analysis) > 0){
                                    $rfq_analysis = $rfq->rfq_analysis()->first();
                                    $rfq_analysis_id = $rfq_analysis->id;
                                    $rfq_analysis_tid = gen4tid('RFQA-',$rfq_analysis->tid);
                                }
                            }
                        @endphp
                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                            <p>RFQ No.</p>
                        </div>
                        <div class="col border-blue-grey border-lighten-5 p-1 font-weight-bold">
                            @if (@$po->rfq)
                                <a href="{{ route('biller.rfq.show', @$po->rfq['id']) }}" target="_blank">
                                    <p>{{ gen4tid('RFQ-', @$po->rfq['tid']) }}</p>
                                </a>
                            @endif
                        </div>
                        @if ($rfq_analysis_id)
                            <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                <p>RFQ Analysis No.</p>
                            </div>
                            <div class="col border-blue-grey border-lighten-5 p-1 font-weight-bold">
                                <a href="{{ route('biller.rfq_analysis.show', $rfq_analysis_id) }}" target="_blank">
                                    <p>{{ $rfq_analysis_tid }}</p>
                                </a>
                            </div>
                        @endif
                    @elseif ($po->requisition_type == 'purchase_requisition')
                        @php
                            $mr_tid = '';
                            $mr_id = '';
                            if($po->purchase_requisition){
                                $pr = $po->purchase_requisition;
                                if($pr->purchase_request){
                                    $mr_tid = $pr->purchase_request ? gen4tid('REQ-',$pr->purchase_request->tid) : '';
                                    $mr_id = $pr->purchase_request ? $pr->purchase_request->id : '';
                                }
                            }
                        @endphp
                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                            <p>PR No.</p>
                        </div>
                        <div class="col border-blue-grey border-lighten-5 p-1 font-weight-bold">
                            @if (@$po->purchase_requisition)
                                <a href="{{ route('biller.rfq.show', @$po->purchase_requisition['id']) }}" target="_blank">
                                    <p>{{ gen4tid('PR-', @$po->purchase_requisition['tid']) }}</p>
                                </a>
                            @endif
                        </div>
                        @if ($mr_id)
                            <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                <p>Material Requisition (MR) No.</p>
                            </div>
                            <div class="col border-blue-grey border-lighten-5 p-1 font-weight-bold">
                                <a href="{{ route('biller.purchase_requests.show', @$mr_id) }}" target="_blank">
                                    <p>{{ $mr_tid }}</p>
                                </a>
                            </div>
                        @endif                   
                    @endif                    
                </div>
            </div>
            <div class="table-responsive">
                <table id="stateTbl" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                    <tr>
                        <th>#</th>                           
                        <th>Status</th>
                        <th>Date</th>
                        <th>Approved By</th>
                        <th>Reason</th>
                        <th>Action</th>
                    </tr>
                    <tbody>
                        @foreach ($po->statuses as $item)
                            <tr>
                                <th>{{ $loop->iteration }}</th>
                                <td>{{ ucfirst($item->approval_status) }}</td>
                                <td>{{ dateFormat($item->approved_date) }}</td>
                                <td>{{ @$item->approved_user->fullname }}</td>
                                <td>{{ $item->status_note }}</td>
                                <td>
                                    <input type="hidden" class="id" value="{{ $item->id }}">
                                    <input type="hidden" class="status" value="{{ $item->approval_status }}">
                                    <input type="hidden" class="approved_date" value="{{ $item->approved_date }}">
                                    <input type="hidden" class="status_note" value="{{ $item->status_note }}">
                                    <button type="button" class="btn btn-sm btn-primary edit" data-toggle="modal" data-target="#editStatusModal" @if ($item->user_id != auth()->id()) disabled @endif> 
                                        <i class="fa fa-pencil" aria-hidden="true"></i> Status
                                    </button>                                                            
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>                    
            </div>                    
            <div class="row">
                <div class="col">
                </div>
            </div>

            <!-- Inventory/stock -->
            @if ($po->products->where('type', 'Stock')->count())
                <fieldset class="border p-1 mb-3">
                    <legend class="w-auto float-none h3">Inventory Items</legend>
                    <div class="table-responsive">
                        <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                            <tr>
                                <th>#</th>
                                <th width="40%">Product Description</th>
                                <th>Product Code</th>
                                <th>Order Qty</th>
                                <th>Receipt Qty</th>
                                <th>UoM</th>
                                <th>Price</th>
                                <th>Tax Rate</th>                            
                                <th>Amount</th>
                            </tr>
                            <tbody>
                                @foreach ($po->products->where('type', 'Stock') as $item)
                                    <tr>
                                        <th>{{ $loop->iteration }}</th>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->product_code }}</td>
                                        <td>{{ +$item->qty }}</td>
                                        <td>{{ +$item->grn_items->sum('qty') }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ numberFormat($item->rate) }}</td>                                        
                                        <td>{{ numberFormat($item->taxrate) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>                        
                    </div>
                </fieldset>
            @endif
            
            <!-- Expense -->
            @if ($po->products->where('type', 'Expense')->count())
                <fieldset class="border p-1 mb-3">
                    <legend class="w-auto float-none h3">Expense Items</legend>
                    <div class="table-responsive">
                        <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                            <tr>
                                <th>#</th>
                                <th width="40%">Product Description</th>
                                <th>Quantity</th>
                                <th>UoM</th>
                                <th>Price</th>
                                <th>Tax</th>
                                <th>Amount</th>
                                <th>Ledger Account</th>
                                <th>Project</th>
                            </tr>
                            <tbody>
                                @foreach ($po->products->where('type', 'Expense') as $item)
                                    <tr>
                                        <th>{{ $loop->iteration }}</th>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->qty, 1) }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ numberFormat($item->rate) }}</td>
                                        <td>{{ numberFormat($item->taxrate) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
                                        <td>{{ $item->account? $item->account->holder : '' }}</td>
                                        <td>
                                            @isset($item->project->tid)
                                                {{ gen4tid('Prj-', $item->project->tid) }}; {{ $item->project->name }}
                                            @endisset
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>                        
                    </div>
                </fieldset>
            @endif
            
            <!-- Asset -->
            @if ($po->products->where('type', 'Asset')->count())
                <fieldset class="border p-1 mb-3">
                    <legend class="w-auto float-none h3">Asset Items</legend>
                    <div class="table-responsive">
                        <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                            <tr>
                                <th>#</th>
                                <th width="40%">Product Description</th>
                                <th>Quantity</th>
                                <th>UoM</th>
                                <th>Price</th>
                                <th>Tax</th>
                                <th>Amount</th>
                            </tr>
                            <tbody>
                                @foreach ($po->products->where('type', 'Asset') as $item)
                                    <tr>
                                        <th>{{ $loop->iteration }}</th>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->qty, 1) }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ numberFormat($item->rate) }}</td>
                                        <td>{{ numberFormat($item->taxrate) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>                        
                    </div>
                </fieldset>
            @endif
        </div>
    </div>
</div>
@include('focus.purchaseorders.partials.status_modal')
@include('focus.purchaseorders.partials.sms_modal')
@include('focus.purchaseorders.partials.approve_modal')
@include('focus.purchaseorders.partials.edit_status')
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
                // $('#stateTbl').on('click','.edit', Index.editCLick)
                $('form').on('submit', function () {
                    var $submitBtn = $(this).find('.btn_submit');
                    $submitBtn.prop('disabled', true).text('Submitting...');
                });
                $('.edit').on('click', function () {
                    const row = $(this).closest('tr');

                    // Extract values from hidden fields
                    const id = row.find('.id').val();
                    const status = row.find('.status').val();
                    const approvedDate = row.find('.approved_date').val();
                    const note = row.find('.status_note').val();

                    setTimeout(() => {
                        $('#state_id').val(id);
                        $('#status').val((status || '').toLowerCase());
                        $('#approved_date').datepicker('setDate', approvedDate);
                        $('#status_note').val(note);
                    }, 200);
                });
            },
        };
        $(()=>Index.init());
    </script>
@endsection