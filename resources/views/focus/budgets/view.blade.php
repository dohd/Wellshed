@extends('core.layouts.app')

@section('title', 'View | Project Budget')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>E.P Margin Not Met!</strong> Check line item rates.
        </div>
    </div>

    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Budget</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-400 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group">
                        <a href="{{ route('biller.projects.index') }}" class="btn btn-primary">
                            <i class="ft-list"></i> Projects
                        </a>&nbsp;
                        @php
                            $valid_token = token_validator('', 'q' . $quote->id . $quote->tid, true);

                            $technician_url = route('biller.print_technicians_list', [$quote->id, 4, $valid_token, 1]);
                            $stores_url = route('biller.print_stores_list', [$quote->id, 4, $valid_token, 1]);

                            $techniciansTag = '<a href="' . $technician_url . '" class="btn btn-twitter" target="_blank">
                                            <i class="fa fa-print"></i> Technicians
                                        </a>';

                            $storesTag = '<a href="' . $stores_url . '" class="btn btn-secondary" target="_blank">
                                            <i class="fa fa-print"></i> Stores
                                        </a>';
                        @endphp
                        {!! $techniciansTag !!}
                        {!! $storesTag !!}
                        <a href="{{ route('biller.rfq.create', ['budget_id' => @$budget->id]) }}" class="btn btn-pinterest" target="_blank">
                            <i class="fa fa-plane"></i> Generate RFQ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                <div class="row">
                    <div class="col-12 cmp-pnl">
                        <div id="customerpanel" class="inner-cmp-pnl">                        
                            <div class="form-group row"> 
                                <div class="col-5">
                                    <label for="customer" class="caption">Customer</label>                                       
                                    {{ Form::text('customer', $quote->customer? $quote->customer->company : '', ['class' => 'form-control', 'disabled']) }}
                                </div> 
                                <div class="col-3">
                                    <label for="branch" class="caption">Branch</label>                                       
                                    {{ Form::text('branch', $quote->branch? $quote->branch->name : '', ['class' => 'form-control', 'disabled']) }}
                                </div> 
                                <div class="col-2">
                                    <label >Serial No</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                        {{ Form::text('tid', gen4tid($quote->bank_id ? 'PI-' : 'QT-', $quote->tid), ['class' => 'form-control round', 'disabled']) }}
                                    </div>
                                </div>
                                <div class="col-2"><label for="invoicedate" class="caption">{{trans('general.date')}}</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                        {{ Form::text('date', dateFormat($quote->date), ['class' => 'form-control round datepicker', 'id' => 'date', 'disabled']) }}
                                    </div>
                                </div>                                                               
                            </div> 
                        </div>
                    </div>
                </div> 
                <div class="form-group row">
                    <div class="col-10">
                        <label for="subject" class="caption">Subject / Title</label>
                        {{ Form::text('notes', $quote->notes, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
                    </div>
                    <div class="col-2">
                        <label for="client_ref" class="caption">Client Ref / Callout ID</label>                                       
                        {{ Form::text('client_ref', $quote->client_ref, ['class' => 'form-control', 'id' => 'client_ref', 'disabled']) }}
                    </div> 
                </div>
                
            </div>             
        </div>
        <div class="">
            <table id="productsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr class="item_header bg-gradient-directional-blue white">
                        <th class="text-center">#</th>
                        <th class="text-center">Product</th>
                        <th class="text-center">Quoted Qty</th>                                
                        <th class="text-center">UoM</th>
                        <th class="text-center">Qty</th>     
                        <th class="text-center">Buy Price</th>
                        <th class="text-center">Amount</th>                           
                    </tr>
                </thead>
                <tbody>
                    @foreach ($budget->items as $i => $item)
                        <tr>
                            @php
                                $amount = $item->price * $item->new_qty;
                            @endphp
                            <td>{{$i+1}}</td>
                            <td>{{$item->product_name}}</td>
                            <td>{{numberFormat($item->product_qty)}}</td>
                            <td>{{$item->unit}}</td>
                            <td>{{numberFormat($item->new_qty)}}</td>
                            <td>{{numberFormat($item->price)}}</td>
                            <td>{{numberFormat($amount)}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>  
    </div>
    
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            this.drawDataTable();
        },

        drawDataTable() {
            $('#productsTbl').dataTable({
                // processing: true,
                // serverSide: true,
                // responsive: true,
                
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection