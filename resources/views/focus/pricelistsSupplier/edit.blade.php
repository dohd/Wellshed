@extends ('core.layouts.app')

@section ('title', 'Edit | Price List Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Price List Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.pricelistsSupplier.partials.pricelists-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#changeModal">
                            <i class="fa fa-pencil" aria-hidden="true"></i> Change Product
                        </a>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::model($supplier_product, ['route' => array('biller.pricelistsSupplier.update', $supplier_product), 'method' => 'PATCH']) }}
                                @include('focus.pricelistsSupplier.form')
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.pricelistsSupplier.partials.change_product')
    </div>
</div>
@endsection
