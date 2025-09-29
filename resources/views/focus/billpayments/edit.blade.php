@extends ('core.layouts.app')

@section('title', 'Edit Payment | Bill Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Bill Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.billpayments.partials.billpayments-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body filter">
                    {{ Form::model($billpayment, ['route' => array('biller.billpayments.update', $billpayment), 'method' => 'PATCH']) }}
                        @include('focus.billpayments.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
