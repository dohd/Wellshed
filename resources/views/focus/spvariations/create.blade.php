@extends ('core.layouts.app')

@section ('title', 'Selling Price Variations | Create Selling Price')

@section('page-header')
<h1>
    Selling Price Variations
    <small>Create Selling Price Variations</small>
</h1>
@endsection

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">Create [{{@$pricegroup_name->name}}] Selling Price </h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">

                    <div class="media-body media-right text-right">
                        @include('focus.spvariations.partials.spvariations-header-buttons')
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                {{ Form::open(['route' => 'biller.spvariations.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-productcategory']) }}
                                <div class="form-group">
                                    <div id="saman-row-exp">
                                        <table class="table-responsive tfr my_stripe">
                                            <thead>
                                                <tr class="item_header bg-gradient-directional-danger white">
                                                    <th width="33.33%" class="text-center">Product Name</th>
                                                    <th width="33.33%" class="text-center">Product Code</th>
                                                    <th width="33.33%" class="text-center">{{trans('general.rate')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($products as $product)
                                                <tr>
                                                    <td>{{@$product->product->name}}
                                                    </td>
                                                    <td>{{$product->code}}
                                                    </td>
                                                    <td><input type="text" class="form-control req exp_prc" name="selling_price[]" id="selling_price-{{@$product->id}}" value="{{@$product->v_prices->selling_price}}" onkeypress="return isNumber(event)" autocomplete="off"></td>
                                                    <input type="hidden" name="product_id[]" id="product_id-{{@$product->id}}" value="{{$product->product_id}}">
                                                    <input type="hidden" name="product_variation_id[]" id="product_variation_id-{{@$product->id}}" value="{{@$product->id}}">
                                                    <input type="hidden" name="id[]" id="id-{{@$product->id}}" value="{{@$product->v_prices->id}}">
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div><br />
                                    <input type="hidden" name="pricegroup_id[]" id="pricegroup_id" value="{{$p}}">
                                    {{ link_to_route('biller.equipments.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                </div><!-- form-group -->
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection