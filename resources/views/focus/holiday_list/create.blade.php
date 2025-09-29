@extends ('core.layouts.app')

@section('title', 'Create | Holiday List')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Holiday Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.holiday_list.partials.holiday-list-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.holiday_list.store', 'method' => 'POST']) }}
                        @include('focus.holiday_list.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
