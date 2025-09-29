@extends ('core.layouts.app')

@section('title', 'Edit | Refill Customer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Edit Refill Customer</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.refill_customers.partials.refill-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($refill_customer, ['route' => ['biller.refill_customers.update', $refill_customer], 'method' => 'PATCH']) }}
                        <div class="form-group">
                            @include('focus.refill_customers.form')
                            <div class="edit-form-btn">
                                {{ link_to_route('biller.refill_customers.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                <div class="clearfix"></div>
                            </div><!--edit-form-btn-->
                        </div><!-- form-group -->
                    {{ Form::close() }}
                </div>
            </div
        </div>
    </div>
</div>
@endsection

