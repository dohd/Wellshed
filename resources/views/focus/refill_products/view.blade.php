@extends ('core.layouts.app')

@section('title', 'Product Refill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Product Refill Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.refill_products.partials.refill-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    @php
                        $details = [
                            'Product Name' => $refill_product->name,
                            'UoM' => $refill_product->unit,
                            'Product Category' => @$refill_product->product_category->title,
                            'Unit Price' => numberFormat($refill_product->unit_price),
                            'Product Description' => $refill_product->note,
                        ];
                    @endphp
                    @foreach ($details as $key => $value)
                        <div class="row">
                            <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                <p>{{ $key }}</p>
                            </div>
                            <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                <p>{{ $value }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
