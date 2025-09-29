@extends('core.layouts.app')
@php
    $quote_type = $quote->bank_id ? 'Proforma Invoice' : 'Quote / ∑MTO';
    $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead'], $quote->ins);
@endphp

@section('title', $quote_type . ' Approval')

@section('after-styles')
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
@endsection

@section('content')
<div class="app-content">
    <div class="content-wrapper">
        <div class="alert alert-danger alert-dismissible fade show d-none approve-alert" role="alert">
            <strong>Forbidden!</strong> Update customer details
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="content-header row mb-1">
            <div class="content-header-left col-12 col-md-6">
                <h4 class="content-header-title">{{ $quote_type }} Approval</h4>
            </div>
            <div class="content-header-right col-12 col-md-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.quotes.partials.quotes-header-buttons')
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-body">
            <section class="card">
                <div id="invoice-template" class="card-body">                    
                    @include('focus.quotes.partials.view_menu')
                    @if ($quote->verified == "Yes" || $quote->status == 'approved')
                        @php
                            $text = "{$quote_type} is approved";
                            if ($quote->verified == 'Yes') $text .= ' and verified';
                            $approved_verified = ($quote->verified == "Yes" && $quote->status == 'approved');
                        @endphp
                        <div class="badge text-center white d-block m-1">
                            <span class="{{ $approved_verified ? 'bg-primary' : 'bg-success' }} round p-1">
                                <b>{{ $text }}</b>
                            </span>
                        </div>
                    @endif                    

                    <div id="invoice-customer-details" class="row pt-2">                        
                        <div class="col-6 col-md-6 text-left">
                            @php
                                $clientname = $quote->lead? $quote->lead->client_name : '';
                                $branch = '';
                                $address = $quote->lead? $quote->lead->client_address : '';
                                $email = $quote->lead? $quote->lead->client_email : '';
                                $cell = $quote->lead? $quote->lead->client_contact : '';
                                if ($quote->customer) {
                                    $clientname = $quote->customer->company;						
                                    $address = $quote->customer->address;
                                    $email = $quote->customer->email;
                                    $cell = $quote->customer->phone;
                                    $branch = $quote->branch? $quote->branch->name : '';
                                }					
                            @endphp
                            <span class="text-muted"><b>{{ trans('invoices.bill_to') }}</b></span>
                            <ul class="px-0 list-unstyled">
                                <li><i>{{ $clientname }},</i></li>
                                <li><i>{{ $branch }},</i></li>
                                <li><i>{{ $address }},</i></li>
                                <li><i>{{ $email }},</i></li>
                                <li><i>{{ $cell }}</i></li>                                
                            </ul>
                            Client Ref: {{ $quote->customer_ref }} <br>
                            Prepared By: <b>{{ @$quote->preparedBy->fullname }}</b> <br>
                            <span>Quote Type: <b>{{ucfirst($quote->quote_type)}}</b></span> <br>
                            <span>Revision: <b>{{$quote->revision}}</b></span>
                        </div>
                        <div class="col-6 col-md-6  text-right">
                            <h2>
                                {{ gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid)}}{{ $quote->revision }}
                                {{ !$quote->is_repair? 'Maintenance' : '' }}
                            </h2>
                            <h3>{{ $quote->lead? gen4tid("{$prefixes[2]}-", $quote->lead->reference) : '' }}</h3>
                            <div class="row">
                                <div class="col">
                                    <hr>
                                    <p class="text-primary h4">{{ $quote->notes }}</p>
                                    <p>Date: {{ dateFormat($quote->date) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="invoice-items-details" class="pt-2">
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{trans('products.product_des')}}</th>
                                            <th>Product Code</th>
                                            <th class="text-right">{{trans('products.qty')}}</th>                                          
                                            <th class="text-right">Product Rate</th>
                                            <th class="text-right">{{trans('general.tax')}}</th>
                                            <th class="text-right">Product Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quote->products as $item)
                                            @if ($item->a_type == 1)   
                                                @php
                                                    $text_color = "";
                                                    if($item->misc && $item->variation)
                                                    {
                                                        $text_color = "#800080";
                                                    }elseif($item->misc && !$item->variation)
                                                    {
                                                        $text_color = "#DC4C64";
                                                    }
                                                @endphp                                            
                                                <tr style="color: {{ $text_color }}">
                                                    <td scope="row">{{ $item['numbering'] }}</td>
                                                    <td>
                                                        <p>{{$item['product_name']}}</p>
                                                        <p class="text-muted"> {!! $item['product_des'] !!} </p>
                                                    </td>
                                                    <td>{{$item->variation ? $item->variation->code : ''}}</td>
                                                    <td class="text-right">{{ $item->misc? +$item->estimate_qty : +$item['product_qty'] }} {{$item['unit']}}</td>
                                                    @if ($quote->currency)
                                                        <td class="text-right">{{ $item->misc? amountFormat($item->buy_price, $quote->currency->id) : amountFormat($item->product_subtotal, $quote->currency->id) }}</td>
                                                        <td class="text-right">
                                                            {{ amountFormat(($item->product_price - $item->product_subtotal) * $item->product_qty, $quote->currency->id) }}
                                                            <span class="font-size-xsmall">({{ +$item->tax_rate }}%)</span>
                                                        </td>
                                                        <td class="text-right">{{ $item->misc? amountFormat($item->estimate_qty * $item->buy_price, $quote->currency->id) : amountFormat($item->product_qty * $item->product_price, $quote->currency->id) }}</td>
                                                    @else
                                                        <td class="text-right">{{ $item->misc? numberFormat($item->buy_price) : numberFormat($item->product_subtotal) }}</td>
                                                        <td class="text-right">
                                                            {{ numberFormat(($item->product_price - $item->product_subtotal) * $item->product_qty) }}
                                                            <span class="font-size-xsmall">({{ +$item->tax_rate }}%)</span>
                                                        </td>
                                                        <td class="text-right">{{ $item->misc? numberFormat($item->estimate_qty * $item->buy_price) : numberFormat($item->product_qty * $item->product_price) }}</td>
                                                    @endif
                                                </tr>
                                            @else
                                                <tr style="color: #00008B;">
                                                    <td scope="row">{{ $item['numbering'] }}</td>
                                                    <td><p>{{ $item['product_name'] }}</p></td>
                                                    @for ($i = 0; $i < 4; $i++)
                                                        <td class="text-right"></td>                                                    
                                                    @endfor                                                    
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Approvals -->
                            <div class="col-12 col-md-7">
                                <p class="lead">Approval Details</p><hr>
                                @if ($quote->status != 'pending')
                                    @if ($quote->status == 'cancelled')
                                        <p>
                                            Cancelled By : <span class="text-danger mr-1">{{ $quote->approved_by }}</span><br>
                                            Cancelled On : <span class=" text-danger mr-1">{{ dateFormat($quote->approved_date) }}</span><br> 
                                            Cancel Note:
                                        </p>
                                    @else
                                        <p>
                                            Approved By : <span class="text-danger mr-1">{{ $quote->approved_by }}</span><br>
                                            Approved On : <span class=" text-danger mr-1">{{ dateFormat($quote->approved_date) }}</span><br>
                                            Approval Method : <span class=" text-danger">{{ $quote->approved_method }}</span><br>
                                            Approval Note: <span class=" text-danger">{!! $quote->approval_note !!}</span>                                 
                                        </p>
                                    @endif
                                @endif                             
                            </div>

                            <!-- Summary Totals -->
                            <div class="col-12 col-md-5">
                                <p class="lead">{{trans('general.summary')}}</p>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td>Taxable</td>
                                                <td class="text-right">{{ +$quote['taxable']? numberFormat($quote['taxable']) : 0 }}</td>
                                            </tr>
                                            <tr>
                                                <td>Subtotal</td>
                                                <td class="text-right">{{numberFormat($quote['subtotal'])}}</td>
                                            </tr>
                                            <tr>
                                                <td>VAT</td>
                                                <td class="text-right">{{numberFormat($quote['tax'])}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-bold-800">{{trans('general.total')}}</td>
                                                <td class="text-bold-800 text-right">{{numberFormat($quote['total'])}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center">
                                    <p>{{trans('general.authorized_person')}}</p>
                                    <img src="{{ Storage::disk('public')->url('app/public/img/signs/' . @$quote->user->signature) }}" alt="signature" class="height-100 m-2" />
                                    <h6>{{ @$quote->user->first_name }} {{ @$quote->user->last_name }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Invoice Footer -->
                    <div id="invoice-footer">
                        <div class="row">
                            <!-- LPO Details -->
                            <div class="col-12 col-md-7">
                                @isset($quote->lpo)
                                    <h3>LPO Details</h3>
                                    <p>
                                        LPO Date : <span class="text-danger mr-1">{{ dateFormat($quote->lpo->date) }}</span><br>
                                        LPO Number : <span class="text-danger mr-1">{{ $quote->lpo->lpo_no }}</span><br>                                     
                                        LPO Amount : <span class="text-danger">{{ numberFormat($quote->lpo->amount) }}</span><br>                                                                    
                                    </p> 
                                    <p>LPO Remark : <span class="text-danger">{{ $quote->lpo->remark }}</span></p>
                               @endisset
                            </div>
                            <div class="col-12 col-md-5 text-center">
                                @if ($quote->status !== 'cancelled') 
                                    <a href="#sendEmail" data-toggle="modal" data-remote="false" data-type="6" data-type1="proposal" class="btn btn-primary btn-lg my-1 send_bill">
                                        <i class="fa fa-paper-plane-o"></i> {{trans('general.send')}}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="files" class="files table table-striped mt-2">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Caption</th>
                                    <th>Attachment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            @foreach($quote->quote_files as $i => $row)
                                <tr>
                                    <td>
                                        {{ $i+1 }}
                                    </td>
                                    <td>
                                        {{ $row['caption'] }}
                                    </td>
                                    <td>
                                        <a href="{{ asset('storage/app/public/img/quote_files/' . $row['document_name']) }}" target="_blank" class="purple">
                                            <i class="btn-sm fa fa-eye"></i> {{ $row['document_name'] }}
                                        </a>
                                    </td>
                                    <td width="5%">
                                            {{ Form::open(['route' => ['biller.quotes.delete_quote_file', $row['id']], 'method' => 'post']) }}
                                            <button type="submit" class="file-del red" onclick="return confirm('Are you Sure?')">
                                                <i class="btn-sm fa fa-trash"></i>
                            
                                            </button>
                                             {{ Form::close() }}               
                                    </td>
                                   
                                </tr>
                            @endforeach
                        </table>                            
                    </div>
                    <br>
                </div>
            </section>
        </div>
    </div>
</div>
@php 
    $invoice = $quote; 
@endphp
@include("focus.modal.quote_status_model")
@include("focus.quotes.partials.attachment_modal")
@include("focus.modal.lpo_model")
@include('focus.modal.sms_model', ['category' => 4])
@include('focus.modal.email_model', ['category' => 4])
@endsection

@section('extra-scripts')
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script type="text/javascript">
    // initialize editor
    editor();

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
    });
    
    // initialize datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})

    // on delete Quote
    $('.quote-delete').click(function() {
        const form = $(this).children('form');
        swal({
            title: 'Are You  Sure?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => form.submit());
    });

    // on cancel Quote
    $('.quote-cancel').click(function() {
        $(this).children('form').submit();
    });

    // On Approve Quote
    $('.quote-approve').click(function(e) {
        const customerId = @json($quote->customer_id);
        if (!customerId) {
            $(this).attr('href', '#');
            $('.approve-alert').removeClass('d-none');
        }
    });

    // On Add LPO modal
    const lpos = @json($lpos);
    $('#pop_model_4').on('shown.bs.modal', function() { 
        const $modal = $(this);
        // on selecting lpo option set default values
        $modal.find("#lpo_id").change(function() {
            lpos.forEach(v => {
                if (v.id == $(this).val()) {
                    $modal.find('input[name=lpo_date]').val(v.date);
                    $modal.find('input[name=lpo_amount]').val(v.amount);
                    $modal.find('input[name=lpo_number]').val(v.lpo_no);
                }                
            });
        });
    });

    // On showing Approval Model
    $('#pop_model_1').on('shown.bs.modal', function() { 
        form = $(this).find('#form-approve');
        $('.aprv-status').click(function() {
            form.find('label[for=approved-by]').text('Approved By');
            form.find('label[for=approval-date]').text('Approval Date');
            if ($(this).val() == 'cancelled') {
                form.find('label[for=approved-by]').text('Cancelled By');
                form.find('label[for=approval-date]').text('Cancel Date');
                form.find('.aprv-by').attr('placeholder', 'Cancelled By');
                form.find('.aprv-method').val('').attr('disabled', true);
            } else {
                form.find('.aprv-by').attr('placeholder', 'Approved By');
                form.find('.aprv-method').attr('disabled', false);
                $('#btn_approve').text('Approve');
            }
        });
    });
</script>
@endsection