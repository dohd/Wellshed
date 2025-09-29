@extends ('core.layouts.app')

@section ('title', 'Tickets Management | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tickets Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <a href="{{ route('biller.leads.index') }}" class="btn btn-info  btn-lighten-2">
                            <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
                        </a>
                    </div>
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
                            {{ Form::open(['route' => 'biller.leads.store', 'method' => 'POST' ]) }}
                                @include("focus.leads.form")
                                <div class="edit-form-btn row">
                                    {{ link_to_route('biller.leads.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
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