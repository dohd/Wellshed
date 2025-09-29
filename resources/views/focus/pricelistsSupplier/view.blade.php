@extends ('core.layouts.app')

@section ('title', 'Price List Management')

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
                        <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#changeStatusModal">
                            <i class="fa fa-pencil" aria-hidden="true"></i> Change Status
                        </a>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <table class="table table-bordered table-sm">
                                @php
                                    $details = [ 
                                        'Supplier' => $supplier_product->supplier? $supplier_product->supplier->company : '',
                                        'Product Code' => $supplier_product->product_code,
                                        'System Description' => $supplier_product->products? $supplier_product->products->name : '',
                                        'Contract' => $supplier_product->contract,
                                        'Status' => $supplier_product->status == 'active' ? 'Active' : 'In Active',
                                        'Row Number' => $supplier_product->row_num,
                                        'Supplier Description' => $supplier_product->descr,
                                        'UoM' => $supplier_product->uom,
                                        'Rate' => numberFormat($supplier_product->rate),
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
                @include('focus.pricelistsSupplier.partials.change_status')
            </div>
        </div>
    </div>
</div>
@endsection