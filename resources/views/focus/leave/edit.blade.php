@extends ('core.layouts.app')

@section('title', 'Edit | Leave Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Leave Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.leave.partials.leave-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($leave, ['route' => array('biller.leave.update', $leave), 'method' => 'PATCH']) }}
                        @include('focus.leave.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
