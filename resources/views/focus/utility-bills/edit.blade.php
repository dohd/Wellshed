@extends ('core.layouts.app')

@section('title', 'Edit | Bill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Bill Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.utility-bills.partials.utility-bills-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($utility_bill, ['route' => array('biller.utility_bills.update', $utility_bill), 'method' => 'PATCH']) }}
                        @include('focus.utility-bills.form')
                        <div class="edit-form-btn row">
                            {{ link_to_route('biller.utility_bills.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
