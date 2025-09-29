<!DOCTYPE html>
@extends ('core.layouts.app')

@section('title', 'Edit | Advance Payment Management')
@include('tinymce.scripts')


@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Advance Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.advance_payments.partials.advance-payments-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($advance_payment, ['route' => array('biller.advance_payments.update', $advance_payment), 'method' => 'PATCH']) }}
                        @include('focus.advance_payments.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
