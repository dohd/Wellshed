@extends ('core.layouts.app')

@section('title', 'Receive | Invoice Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoice_payments.partials.invoice-payment-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::open(['route' => 'biller.invoice_payments.store', 'method' => 'POST', 'id' => 'invoicePay']) }}
            @include('focus.invoice_payments.form')
        {{ Form::close() }}
    </div>
</div>
@endsection

@section('after-scripts')
@include('focus/invoice_payments/form_js')
@endsection
