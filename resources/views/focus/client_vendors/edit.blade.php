@extends ('core.layouts.app')

@section ('title', 'Vendor Management | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6">
            <h4 class="mb-0">Vendor Management</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.client_vendors.partials.client-vendors-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::model($client_vendor, ['route' => ['biller.client_vendors.update', $client_vendor], 'method' => 'PATCH']) }}
        <div class="form-group">                                    
            @include("focus.client_vendors.form")
            <div class="edit-form-btn">
                {{ link_to_route('biller.client_vendors.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
            </div>                                   
        </div>                              
        {{ Form::close() }}
    </div>
</div>
@endsection