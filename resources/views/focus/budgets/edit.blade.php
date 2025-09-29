@extends('core.layouts.app')

@section('title', 'Edit | Budget Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>E.P Margin Not Met!</strong> Check line item rates.
        </div>
    </div>

    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Budget Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group">
                        @php
                            $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);
                            $budget_url = route('biller.print_budget', [$quote->id, 4, $valid_token, 1]);
                            $quote_url = route('biller.print_budget_quote', [$quote->id, 4, $valid_token, 1]);
                        @endphp
                        <a href="{{ $budget_url }}" class="btn btn-purple" target="_blank">
                            <i class="fa fa-print"></i> Store
                        </a>&nbsp;
                        <a href="{{ $quote_url }}" class="btn btn-secondary" target="_blank">
                            <i class="fa fa-print"></i> Technician
                        </a> 
                    </div>                    
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                {{ Form::model($quote, ['route' => ['biller.budgets.update', $budget], 'method' => 'PATCH']) }}
                    @include('focus.budgets.form')
                {{ Form::close() }}
            </div>             
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
@include('focus.budgets.form_js')
@endsection