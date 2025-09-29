@extends('core.layouts.app')
@section('title', 'Expense | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Expense Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchases.partials.purchases-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body"> 
        {{ Form::open(['route' => 'biller.purchases.store', 'method' => 'POST']) }}
            @include('focus.purchases.form')
        {{ Form::close() }}
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
@include('focus.purchases.form-js')
@endsection
