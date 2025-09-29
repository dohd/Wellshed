@extends ('core.layouts.app')

@section('title', 'Create | Ticket Categories Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Ticket Categories Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.ticket_categories.partials.ticketcategories-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.ticket_categories.store', 'method' => 'POST']) }}
                        @include('focus.ticket_categories.form')
                        <div class="edit-form-btn ml-2">
                            {{ link_to_route('biller.ticket_categories.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                        </div> 
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
