@extends ('core.layouts.app')

@section('title', 'Create | Sale Return')

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
        {{ Form::open(['route' => 'biller.sale_returns.store', 'method' => 'POST']) }}
            @include('focus.sale_returns.form')
        {{ Form::close() }}
    </div>
</div>
@endsection
