@extends ('core.layouts.app')

@section('title', 'Project Stock Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Stock Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.projectstock.partials.projectstock-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($projectstock, ['route' => array('biller.projectstock.update', $projectstock), 'method' => 'PATCH']) }}
                        @include('focus.projectstock.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
