@extends ('core.layouts.app')
@section ('title', 'Payment Receipts')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    {{-- @include('focus.branches.partials.branches-header-buttons') --}}
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                {{ Form::open(['route' => 'biller.payment_receipts.store', 'method' => 'post', 'id' => 'entryForm']) }}
                    @include("focus.payment_receipts.form")
                    {{-- <div class="edit-form-btn ml-2">
                        {{ link_to_route('biller.payment_receipts.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                        {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                    </div>   --}}                          
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection