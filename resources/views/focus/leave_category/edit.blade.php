@extends ('core.layouts.app')

@section('title', 'Create | Leave Category')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Leave Category Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.leave_category.partials.leave-category-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($leave_category, ['route' => array('biller.leave_category.update', $leave_category), 'method' => 'PATCH']) }}
                        @include('focus.leave_category.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
