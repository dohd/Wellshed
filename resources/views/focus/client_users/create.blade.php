@extends ('core.layouts.app')

@section('title', 'Create | User Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">User Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.client_users.partials.clientuser-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.client_users.store', 'method' => 'POST']) }}
                        @include('focus.client_users.form')
                        <div class="edit-form-btn ml-2">
                            {{ link_to_route('biller.client_users.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                        </div> 
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
