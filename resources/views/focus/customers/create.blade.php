@extends ('core.layouts.app')

@section ('title', trans('labels.backend.customers.management') . ' | ' . trans('labels.backend.customers.create'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>{{ trans('labels.backend.customers.create') }}   
                @if (@$input['rel_type']) 
                    <span class="purple font-size-small">{{ trans('customers.contact_for') }}</span> {{$customer->name}} 
                @endif
            </h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.customers.partials.customers-header-buttons')
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
                            {{ Form::open(['route' => 'biller.customers.store', 'method' => 'post', 'files' => true, 'id' => 'create-customer']) }}
                                <div class="form-group">
                                    @include('focus.customers.form')
                                    @if (@$input['rel_type'])
                                        {{ Form::hidden('rel_id', @$input['rel_id']) }}
                                        {{ Form::hidden('main', 0) }}
                                    @endif
                                    <div class="edit-form-btn ml-2">
                                        {{ link_to_route('biller.customers.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                        {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
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