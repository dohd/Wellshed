@extends ('core.layouts.app')

@section('title', 'Client Tickets | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Client Tickets Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.client_vendor_tickets.partials.tickets-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.client_vendor_tickets.store', 'method' => 'POST']) }}
                        @include('focus.client_vendor_tickets.form')
                        <div class="edit-form-btn ml-2">
                            {{ link_to_route('biller.client_vendor_tickets.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                        </div> 
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
