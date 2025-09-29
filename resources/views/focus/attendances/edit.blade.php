@extends ('core.layouts.app')

@section('title', 'Edit | Attendance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Attendance Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.attendances.partials.attendances-header-buttons')
            </div>
        </div>
    </div>


    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($attendance, ['route' => array('biller.attendances.update', $attendance), 'method' => 'PATCH']) }}
                        @include('focus.attendances.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
