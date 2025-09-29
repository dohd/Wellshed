@extends ('core.layouts.app')

@section ('title', 'Create | Repair Job Card Report')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Rjc Report Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.rjcs.partials.rjcs-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.rjcs.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'POST', 'files' => true ]) }}
                        @include('focus.rjcs.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
@include('focus.rjcs.form_js')
@endsection
