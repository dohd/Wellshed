@extends ('core.layouts.app')

@section ('title', trans('labels.backend.accounts.management') . ' | ' . trans('labels.backend.accounts.edit'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title mb-0">{{ trans('labels.backend.accounts.edit') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.accounts.partials.accounts-header-buttons')
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
                            {{ Form::model($account, ['route' => ['biller.accounts.update', $account], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-account']) }}
                                @include("focus.accounts.form")
                                <div class="edit-form-btn col-4 ml-auto mr-auto">
                                    {{ link_to_route('biller.accounts.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md mr-1']) }}
                                    {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
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