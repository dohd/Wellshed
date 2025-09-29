@extends('core.layouts.app')

@section('title', 'Create | Project Budget')

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
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group">
                        <a href="{{ route('biller.projects.index') }}" class="btn btn-primary">
                            <i class="ft-list"></i> Projects
                        </a>&nbsp;
                        @php
                            $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);
                            $quote_url = route('biller.print_budget_quote', [$quote->id, 4, $valid_token, 1]);
                        @endphp
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
                {{ Form::model($quote, ['route' => ['biller.budgets.store'], 'method' => 'POST' ]) }}
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