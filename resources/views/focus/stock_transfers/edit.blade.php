@extends ('core.layouts.app')

@section('title', 'Edit | Stock Transfer')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Transfer</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_transfers.partials.stock-transfer-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($stock_transfer, ['route' => array('biller.stock_transfers.update', $stock_transfer), 'method' => 'PATCH']) }}
                        @include('focus.stock_transfers.form')
                        <div class="edit-form-btn row">
                            {{ link_to_route('biller.stock_transfers.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
