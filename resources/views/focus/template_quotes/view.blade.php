@extends('core.layouts.app')
@php
    $quote_type = $quote->bank_id ? 'Proforma Invoice' : 'Quote';
    $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead'], $quote->ins);
@endphp

@section('title', $quote_type . ' Approval')

@section('after-styles')
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
@endsection

@section('content')
<div class="app-content">
    <div class="content-wrapper">
       

        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Template Quote</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.template_quotes.partials.quotes-header-buttons')
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-body">
            <section class="card">
                <div id="invoice-template" class="card-body">                    
                    <div class="row">
                        <div class="col">
                            <p class="text-bold h4">Subject : {{ $quote->notes }}</p>
                            <hr>

                        </div>
                    </div>

                    <div id="invoice-items-details" class="pt-2">
                        <div class="row">
                            <div class="table-responsive col-sm-12">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{trans('products.product_des')}}</th>
                                            <th class="text-right">{{trans('products.qty')}}</th>                                          
                                            <th class="text-right">Product Rate</th>
                                            <th class="text-right">{{trans('general.tax')}}</th>
                                            <th class="text-right">Product Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quote->products as $item)
                                            @if ($item->a_type == 1)                                               
                                                <tr class="{{ !$item->misc ?: 'text-danger' }}">
                                                    <td scope="row">{{ $item['numbering'] }}</td>
                                                    <td>
                                                        <p>{{$item['product_name']}}</p>
                                                        <p class="text-muted"> {!! $item['product_des'] !!} </p>
                                                    </td>
                                                    <td class="text-right">{{ $item->misc? +$item->estimate_qty : +$item['product_qty'] }} {{$item['unit']}}</td>
                                                    @if ($quote->currency)
                                                        <td class="text-right">{{ amountFormat($item->product_subtotal, $quote->currency->id) }}</td>
                                                        <td class="text-right">
                                                            {{ amountFormat(($item->product_price - $item->product_subtotal) * $item->product_qty, $quote->currency->id) }}
                                                            <span class="font-size-xsmall">({{ +$item->tax_rate }}%)</span>
                                                        </td>
                                                        <td class="text-right">{{ amountFormat($item->product_qty * $item->product_price, $quote->currency->id) }}</td>
                                                    @else
                                                        <td class="text-right">{{ numberFormat($item->product_subtotal) }}</td>
                                                        <td class="text-right">
                                                            {{ numberFormat(($item->product_price - $item->product_subtotal) * $item->product_qty) }}
                                                            <span class="font-size-xsmall">({{ +$item->tax_rate }}%)</span>
                                                        </td>
                                                        <td class="text-right">{{ numberFormat($item->product_qty * $item->product_price) }}</td>
                                                    @endif
                                                </tr>
                                            @else
                                                <tr>
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
    .datepicker('setDate', new Date());

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