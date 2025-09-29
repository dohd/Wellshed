@extends ('core.layouts.app')

@section('title', 'Edit | Job Valuation')

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
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($job_valuation, ['route' => array('biller.job_valuations.update', $job_valuation), 'method' => 'PATCH']) }}
                        @include('focus.job_valuations.form')
                        <div class="edit-form-btn row">
                            {{ link_to_route('biller.job_valuations.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                            {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
