@extends ('core.layouts.app')

@section('title', 'Tenant Services | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tenant Service Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tenant_services.partials.tenant-services-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::model($tenant_service, ['route' => array('biller.tenant_services.update', $tenant_service), 'method' => 'PATCH']) }}
            @include('focus.tenant_services.form')
            <div class="edit-form-btn ml-2">
                {{ link_to_route('biller.tenant_services.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
            </div> 
        {{ Form::close() }}
    </div>
</div>
@endsection
