@extends ('core.layouts.app')

@php
    $header_title = 'Quote / ∑MTO Management';
    $is_pi = request('page') == 'pi';
    $task = request('task');
    if ($is_pi) {
        $header_title = 'Proforma Invoice Management';
    }
@endphp

@section('title', $header_title)

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
                <strong>E.P Margin Not Met!</strong> Check line item rates.
            </div>
            <div class="content-header-left col-6">
                <h4 class="content-header-title">{{ $header_title }}</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    @include('focus.quotes.partials.quotes-header-buttons')
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div id="credit_limit" class="align-center"></div>
            </div>
            <div class="card-body">
                @if ($task)
                    {{ Form::model($quote, ['route' => ['biller.quotes.store', $quote], 'method' => 'post']) }}
                    @include('focus.quotes.form')
                    {{ Form::close() }}
                @else
                    {{ Form::model($quote, ['route' => ['biller.quotes.update', $quote], 'method' => 'patch']) }}
                    @include('focus.quotes.form')
                    {{ Form::close() }}
                @endif
            </div>
        </div>
    </div>
@endsection

@section('extra-scripts')
@include('focus.quotes.edit_js')
@endsection
