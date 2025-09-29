@extends ('core.layouts.app')

@section ('title', trans('labels.backend.miscs.management') . ' | ' . trans('labels.backend.miscs.edit'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.miscs.edit') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.equipmentcategories.partials.equipmentcategories-header-buttons')
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
                            {{ Form::model($equipmentcategory, ['route' => ['biller.equipmentcategories.update', $equipmentcategory], 'method' => 'PATCH']) }}
                            <div class="form-group">
                                @include("focus.equipmentcategories.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.equipmentcategories.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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