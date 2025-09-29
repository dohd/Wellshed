@extends ('core.layouts.app')

@section('title', 'Edit | Refill Service Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Edit Refill Service</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.product_refills.partials.refill-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($product_refill, ['route' => ['biller.product_refills.update', $product_refill], 'method' => 'PATCH']) }}
                        <div class="form-group">
                            @include('focus.product_refills.form')
                            <div class="edit-form-btn">
                                {{ link_to_route('biller.product_refills.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                            </div><!--edit-form-btn-->
                        </div><!-- form-group -->
                    {{ Form::close() }}
                </div>
            </div
        </div>
    </div>
</div>
@endsection

