@extends ('core.layouts.app')
@section ('title', 'LPO Review')

@section('content')
@php $review = $lpo_review; @endphp
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>Purchase Order Review</h4>
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
            
            <a href="#" class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#approvalModal">
                <i class="fa fa-status" aria-hidden="true"></i> Status
            </a>
        </div>  
        <div class="card-body">            
            <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                

                <div class="row">
                    <div class="col-6">
                        <table id="customer-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                            <tbody>  
                                @php   
                                    $details = [
                                        'LPO Review No' => gen4tid('REV-', $review->tid),
                                        'Review Date' => dateFormat($review->review_date),
                                        'General Comments' => $review->general_comment,
                                        'Reviewed By' => $review->review_by ? $review->review_by->fullname : '',
                                        'Order No' => gen4tid('PO-', @$review->purchaseorder->tid),
                                        'Supplier' => @$review->purchaseorder ? @$review->purchaseorder->supplier->company : '',
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
            </div>

            <legend>Upload Docs</legend><hr>
            <div class="form-group row">
                <div class="col-10">
                    <div class="table-responsive">
                        <table id="docTbl" class="table">
                            <thead>
                                <tr>
                                    <th>Caption</th>
                                    <th width="30%">Document</th>
                                    {{-- <th>Action</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @isset($review)
                                    @if(count($review->review_docs) > 0)
                                        @foreach($review->review_docs as $index => $row)
                                            <tr>
                                                <td>
                                                    {{ $row->caption }}
                                                </td>
                                                <td>
                                                  
                                                    @isset($row->file_name)
                                                        @if($row->file_name)
                                                            <p>
                                                                <a href="{{ Storage::disk('public')->url('img/pm_documents/' . $row->file_name) }}" target="_blank">{{ $row->file_name }}</a>
                                                            </p>
                                                            
                                                        @endif
                                                    @endisset
                                                </td>
                                                {{-- <td>
                                                    <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                                        <i class="fa fa-trash fa-lg text-danger"></i>
                                                    </button>
                                                </td> --}}
                                            </tr>
                                        @endforeach
                                    @endif
                                @endisset

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Inventory/stock -->
            @if ($review->items)
                <fieldset class="border p-1 mb-3">
                    <legend class="w-auto float-none h3">Inventory Items</legend>
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>#</th>
                            <th width="40%">Product Description</th>
                            <th>Product Code</th>
                            <th>Quantity</th>
                            <th>UoM</th>
                            <th>Price</th>                          
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($review->items as $item)
                                <tr>
                                    <th>{{ $loop->iteration }}</th>
                                    <td>{{ $item->po_items->description }}</td>
                                    <td>{{ $item->po_items->product_code }}</td>
                                    <td>{{ number_format($item->qty, 1) }}</td>
                                    <td>{{ $item->po_items->uom }}</td>
                                    <td>{{ number_format($item->po_items->rate, 2) }}</td>                                        
                                    <td>{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </fieldset>
            @endif
            
            
        </div>
    </div>
</div>
@endsection