@extends ('core.layouts.app')

@section('title', 'Goods Receive Note')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Goods Receive Note</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.goodsreceivenotes.partials.goodsreceivenotes-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-header pb-0">
                <div id="credit_limit" class="align-center"></div>
            </div>
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($goodsreceivenote, ['route' => array('biller.goodsreceivenote.update', $goodsreceivenote), 'method' => 'PATCH']) }}
                        @include('focus.goodsreceivenotes.form')
                        <div class="edit-form-btn row">
                            {{ link_to_route('biller.goodsreceivenote.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
