@extends ('core.layouts.app')

@section('title', 'Edit | Sale Return')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Sale Return</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.sale_returns.partials.salereturn-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($sale_return, ['route' => array('biller.sale_returns.update', $sale_return), 'method' => 'PATCH']) }}
                        @include('focus.sale_returns.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
