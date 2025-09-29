@extends ('core.layouts.app')

@section('title', 'Create | Stock Adjustment')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Adjustment</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_adjs.partials.stockadjs-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.stock_adjs.store', 'method' => 'POST']) }}
                        @include('focus.stock_adjs.form')
                        <div class="edit-form-btn row">
                            {{ link_to_route('biller.stock_adjs.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
