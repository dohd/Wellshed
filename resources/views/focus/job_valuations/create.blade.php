@extends ('core.layouts.app')

@section('title', 'Create | Job Valuation')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Job Valuation</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.job_valuations.partials.jobvaluation-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::open(['route' => 'biller.job_valuations.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
            @include('focus.job_valuations.form')
        {{ Form::close() }}
    </div>
</div>
@endsection
