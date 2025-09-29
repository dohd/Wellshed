@extends ('core.layouts.app')

@section ('title', trans('labels.backend.terms.management') . ' | ' . trans('labels.backend.terms.edit'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.terms.edit') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.terms.partials.terms-header-buttons')
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
                            {{ Form::model($term, ['route' => ['biller.terms.update', $term], 'method' => 'PATCH', 'id' => 'edit-term']) }}
                                @include("focus.terms.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.terms.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                    <div class="clearfix"></div>
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