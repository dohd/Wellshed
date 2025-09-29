@extends ('core.layouts.app')

@section ('title', 'Business Account Management | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>Create Account</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.tenants.partials.tenants-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::open(['route' => 'biller.tenants.store', 'method' => 'POST', 'id' => 'tenantForm']) }}
            @include("focus.tenants.form")
            <div class="edit-form-btn mb-2 float-right">
                {{ link_to_route('biller.tenants.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md mr-1']) }}
                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
            </div>                              
        {{ Form::close() }}    
    </div>
</div>
@endsection