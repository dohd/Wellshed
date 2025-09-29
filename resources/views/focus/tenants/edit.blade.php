@extends ('core.layouts.app')

@section ('title', 'Business Account Management | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="mb-0">Edit Account</h4>
        </div>
        <div class="content-header-right col-md-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.tenants.partials.tenants-header-buttons')
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        {{ Form::model($tenant, ['route' => ['biller.tenants.update', $tenant], 'method' => 'PATCH', 'id' => 'tenantForm']) }}
        <div class="form-group">                                    
            @include("focus.tenants.form")
            <div class="edit-form-btn mb-2 float-right">
                {{ link_to_route('biller.tenants.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md mr-1']) }}
                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
            </div>                                   
        </div>                              
        {{ Form::close() }}
    </div>
</div>
@endsection