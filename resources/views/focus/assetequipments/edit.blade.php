@extends ('core.layouts.app')

@section ('title', 'Edit | Asset Equipment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="mb-0">Edit Asset & Equipments</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">

                <div class="media-body media-right text-right">
                    @include('focus.assetequipments.partials.assetequipments-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::model($assetequipment, ['route' => ['biller.assetequipments.update', $assetequipment], 'method' => 'PATCH']) }}
                            <div class="form-group">                                
                                @include("focus.assetequipments.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.assetequipments.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}                                    
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
