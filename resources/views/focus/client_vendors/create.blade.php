@extends ('core.layouts.app')

@section ('title', 'Vendor Management | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>Vendor Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.client_vendors.partials.client-vendors-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::open(['route' => 'biller.client_vendors.store', 'method' => 'post']) }}
            @include("focus.client_vendors.form")
            <div class="edit-form-btn ml-2">
                {{ link_to_route('biller.client_vendors.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
            </div>                            
        {{ Form::close() }}
    </div>
</div>
@endsection
