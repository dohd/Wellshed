@extends ('core.layouts.app')

@section ('title', 'Create | ' . trans('labels.backend.products.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.products.management') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.products.partials.products-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.products.store', 'method' => 'post', 'files' => true, 'id' => 'create-product']) }}
                        @include("focus.products.form")
                        <div class="edit-form-btn mt-2">
                            {{ link_to_route('biller.products.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                        </div><!--edit-form-btn-->
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection