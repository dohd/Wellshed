@extends ('core.layouts.app')
@section ('title', 'Edit | Casuals Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title mb-0">Edit Casuals</h4>
        </div>
        <div class="content-header-right col-md-6 col-12 mb-2">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.casuals.partials.casuals-header-buttons')
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
                            {{ Form::model($casual, ['route' => ['biller.casuals.update', $casual], 'class' => 'form-horizontal', 'role' => 'form', 'files' => true, 'method' => 'PATCH', 'id' => 'edit-department']) }}
                            <div class="form-group">
                                @include("focus.casuals.form")
                                <div class="edit-form-btn ml-2">
                                    {{ link_to_route('biller.casuals.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                </div><!--edit-form-btn-->
                            </div><!--form-group-->
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection