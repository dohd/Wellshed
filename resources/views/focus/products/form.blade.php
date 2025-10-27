{{-- Product --}}
<h4>General Details</h4>
<div class="row form-group">
    <div class="col-6">
        {{ Form::label('name', trans('products.name'),['class' => 'control-label']) }}
        {{ Form::text('name', old('name', @$prefill['name']), ['class' => 'form-control box-size', 'placeholder' => trans('products.name').'*','required'=>'required','id' => 'name']) }}
    </div>
    <div class="col-2">
        {{ Form::label('item-type', 'Item Type', ['class' => 'control-label']) }}
        <select class="custom-select" name="stock_type" id="itemType">
            @foreach (['general', 'consumable', 'service', 'equipment','finished_goods','generic'] as $i => $val)
                <option value="{{ $i+1 }}" {{ @$product->stock_type == $val ? 'selected' : '' }}>
                    {{  ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div> 
    <div class="col-2">
        {{ Form::label('unit', 'Base Unit (UoM)', ['class' => 'control-label']) }}
        @php $isEfris = config('services.efris.base_url') @endphp
        <select class="custom-select" name="unit_id" id="unit" data-placeholder="Search Unit" required>
            <option value=""></option>
            @foreach($productvariables->where('unit_type', 'base') as $item)
                <option value="{{ $item->id }}" {{ $item->id == @$product->unit_id ? 'selected' : '' }}>
                    {{ $item->code }} ({{ $item->title }}) {{ $item->efris_unit_name? " -> {$item->efris_unit_name} ({$item->efris_unit})" : "" }}
                </option>    
            @endforeach
        </select>
    </div>
    <div class="col-2">
        {{ Form::label( 'productcategory_id', trans('products.productcategory_id'),['class' => 'control-label']) }}
        <select class="custom-select" name="productcategory_id" id="product_cat" data-placeholder="Choose Category" required>
            <option value=""></option>
            @foreach($product_categories->where('ctype', 0) as $item)
                <option value="{{$item->id}}" {{ $item->id == @$product->productcategory_id ? 'selected' : '' }}>
                    @php
                        $title = $item->title;
                        $parent = @$item->parent_category->title;
                        $child = @$item->child->title;
                        echo implode(' || ', array_filter([$title, $parent, $child]));
                    @endphp
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row form-group">
    <div class="col-6">
        {{ Form::label('product_des', trans('products.product_des'),['class' => 'control-label']) }}
        {{ Form::textarea('product_des', old('name', @$prefill['name']), ['class' => 'form-control col', 'rows' => '1', 'style' => 'height:3em;', 'placeholder' => trans('products.product_des')]) }}
    </div>

    <div class="col-2">
        {{ Form::label('taxrate', 'Tax %', ['class' => 'control-label']) }}
        {{ Form::text('taxrate', +@$produc->taxrate, ['class' => 'form-control box-size', 'placeholder' => trans('products.taxrate'),'onkeypress'=>"return isNumber(event)"]) }}
    </div>

    <div class="col-2">
        <label for="sku">Stock Keeping Unit (SKU)</label>
        {{ Form::text('sku', null, ['class' => 'form-control']) }}
    </div>

    <div class="col-2">
        {{ Form::label('code_type', trans('products.code_type'), ['class' => 'control-label']) }}
        <select class="custom-select" name="code_type">
            @foreach (['ean13', 'upca', 'ean8', 'issn', 'isbn', 'c128a', 'c39'] as $val) 
                <option value="{{ $val }}" {{ @$product->code_type == $val? 'selected' : '' }}>
                    {{ strtoupper($val) }}
                </option>
            @endforeach       
        </select>       
    </div>
</div>

<div class="form-group row">
    <div class="col-6">
        <div class="row">
            <div class="col-6">
                <div class='form-group'>
                    {{ Form::label('asset_account', 'Inventory Asset Account',['class' => 'control-label']) }}
                    <select name="asset_account_id" class="form-control custom-select" id="asset_account" data-placeholder="Choose Asset Account" required>
                        <option value="">-- Select Account --</option>
                        @foreach ($accounts->where('account_type', 'Asset') as $row)
                            <option value="{{ $row->id }}" {{ $row->id == @$product->standard->asset_account_id? 'selected' : '' }}>
                                {{ $row->holder }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class='form-group'>
                    {{ Form::label('expense_account', 'Inventory Expense Account',['class' => 'control-label']) }}
                    <select name="exp_account_id" class="form-control custom-select" id="exp_account" data-placeholder="Choose Expense Account" required>
                        <option value="">-- Select Account --</option>
                        @foreach ($accounts->where('account_type', 'Expense') as $row)
                            <option value="{{ $row->id }}" {{ $row->id == @$product->standard->exp_account_id? 'selected' : '' }}>
                                {{ $row->holder }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6">
        {{ Form::label('unit', 'Compound Units (UoM)', ['class' => 'control-label']) }}
        <select class="custom-select" name="compound_unit_id[]" id="compound_unit" data-placeholder="Choose Compound Units" multiple>
            @foreach($productvariables->where('unit_type', 'compound') as $item)
                <option  value="{{ $item->id }}" baseUnitId="{{ $item->base_unit_id }}" {{ in_array($item->id, @$compound_unit_ids ?: [])? 'selected' : '' }}>
                    {{ $item->code }} ({{ +$item->base_ratio }} units)
                </option> 
            @endforeach
        </select>
    </div>
</div>
<hr class="mb-1">

<!-- Standard Details -->
<h4>Standard Details</h4>
<div id="main_product">
    <div class="product round pt-2">
        <div class="row no-gutters">
            <div class="col-6">
                <div class="row no-gutters">
                    <div class="col-md-12">
                        <div class='form-group'>
                            {{ Form::label( 'variation_name', 'Variation Description',['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('variation_name[]',@$product->standard['name'] ?? old('name', @$prefill['name']), ['class' => 'form-control box-size var-name', 'placeholder' => 'Variation Description']) }}
                            </div>
                        </div>
                        <div class="old_id"><input type="hidden" name="v_id[]" value="{{@$product->standard['id']}}"><input type="hidden" name="pv_id" value="{{@$product->standard['id']}}"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-6">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'purchase_price', 'Product Buying Price',['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('purchase_price[]', @$product->standard['purchase_price'] > 0 ? numberFormat(@$product->standard['purchase_price']) : numberFormat(old('purchase_price', @$prefill['purchase_price'])), ['class' => 'form-control box-size', 'placeholder' => trans('products.purchase_price'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'selling_price', 'Minimum Selling Price',['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('selling_price[]', @$product->standard['selling_price'] > 0 ? numberFormat(@$product->standard['selling_price']) : numberFormat(old('selling_price', @$prefill['selling_price'])), ['class' => 'form-control box-size', 'placeholder' => 'Recommended Selling Price'.'*','required'=>'required','onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'price', 'Recommended Selling Price',['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('price[]', @$product->standard['price'] > 0 ?  numberFormat(@$product->standard['price']) : numberFormat(old('price', @$prefill['price'])), ['class' => 'form-control box-size', 'placeholder' => 'Product Selling Price'.'*','required'=>'required','onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>           
                </div>
            </div>
        </div>

        <div class="row no-gutters">
            <div class="col-6">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('productcategory_id', 'Item Store Location',['class' => 'col control-label']) }}
                            <div class='col'>
                                <select class="custom-select location" name="warehouse_id[]">
                                    <option value="">-- Item Store Location --</option>
                                    @foreach($warehouses as $item)
                                        <option value="{{$item->id}}" {{ $item->id == @$product->standard->warehouse_id ? "selected" : "" }}>
                                            {{$item->title}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label('code', 'Item Code',['class' => 'col control-label']) }}
                            <div class='col'>
                                <input type="text" class="form-control box-size" name="code[]" value="{{ @$product->standard['code']}}" @if(isset($product->standard['code'])) @endif placeholder="{{trans('products.code')}}" id="" readonly>
                                {{-- {{ Form::text('code[]', @$product->standard['code'], ['class' => 'form-control box-size','readonly', 'placeholder' => trans('products.code')]) }} --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label('barcode', trans('products.barcode'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('barcode[]', @$product->standard['barcode'], ['class' => 'form-control box-size', 'placeholder' => trans('products.barcode')]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class='form-group'>
                            <div class='form-group'>
                                {{ Form::label( 'moq', 'Minimum Order Quantity (MoQ)',['class' => 'col control-label']) }}
                                <div class='col'>
                                    {{ Form::text('moq[]', @$product->standard['moq'] > 0 ? numberFormat(@$product->standard['moq']) : numberFormat(old('moq', @$prefill['moq'])), ['class' => 'form-control box-size', 'placeholder' => '0.00','onkeypress'=>"return isNumber(event)"]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label('alert', 'Qty Alert Limit',['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('alert[]', @$product->standard['alert'] > 0 ? numberFormat(@$product->standard['alert']) : numberFormat(old('alert', @$prefill['alert'])), ['class' => 'form-control box-size', 'placeholder' => trans('products.alert'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label('disrate', 'Discount % Rate ',['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('disrate[]', numberFormat(@$product->standard['disrate']), ['class' => 'form-control box-size', 'placeholder' => trans('products.disrate'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>    
        </div>

        <div class="row no-gutters">
            <div class="col-2">
                <div class='form-group'>
                    {{ Form::label( 'type', 'Type',['class' => 'col control-label']) }}
                    <div class='col'>
                        <select name="type[]" id="type" class="form-control">
                            <option value="">--select type--</option>
                            <option value="full" {{ @$product->standard['type'] == 'full' ? 'selected' : '' }}>Full</option>
                            <option value="empty" {{ @$product->standard['type'] == 'empty' ? 'selected' : '' }}>Empty</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class='form-group'>
                    {{ Form::label( 'ref', 'Search Related Item',['class' => 'col control-label']) }}
                    <div class='col'>
                        <select name="ref_id[]" id="ref" class="form-control">
                            <option value="">--select ref--</option>
                            @foreach ($products as $item)
                                <option value="{{ $item->id }}" {{ $item->id == @$product->standard['ref_id'] ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class='form-group'>
                    {{ Form::label( 'image', trans('products.image'),['class' => 'col control-label']) }}
                    <div class='col'>
                        @if(isset($product) && $product->standard['image'])
                            <img src="{{Storage::disk('public')->url('img/products/' . @$product->standard['image'])}}" alt="Product Image" width="150">
                        @endif
                        {!! Form::file('image[]', array('class'=>'input' )) !!}
                        <input type="hidden" name="existing_image[]" value="{{ @$product->standard['image'] }}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class='form-group'>
                    {{ Form::label( 'image', 'Image Description/Specifications',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::textarea('image_description[]', @$product->standard['image_description'], ['class' => 'form-control box-size', 'placeholder' => 'Image Description/Specifications']) }}
                    </div>
                </div>
            </div>
        </div>
        <span class="col-6 del_b"></span>
        <hr>
    </div>
</div>

@if(isset($product->standard->product_serial))
    @foreach($product->standard->product_serial as $serial)
        <div class="form-group serial"><label for="field_s" class="col-lg-2 control-label">{{trans('products.product_serial')}}</label>
            <div class="col-lg-10"><input class="form-control box-size" placeholder="{{trans('products.product_serial')}}" name="product_serial_e[{{$serial['id']}}]" type="text" value="{{$serial['value']}}" @if($serial['value2']) readonly="" @endif></div>
        </div>
    @endforeach
@endif

<!-- Additional Product Variations -->
@if(isset($product->standard))
    <h4 class="card-title mt-3">{{trans('products.variation')}}</h4>
    <div id="product_sub">
        @foreach($product->variations as $i => $row)
            @php if (!$i) continue @endphp
            <div class="v_product_t border-blue-grey border-lighten-4 round p-1 bg-blue-grey bg-lighten-5" id="pv_{{$row->id}}">
                <input type="hidden" id="" name="v_id[]" value="{{$row->id}}">
                <div class="row mt-3 mb-3">
                    <div class="col-6">{{trans('general.description')}} 
                        <input type="text" class="form-control var-name" name="variation_name[]" value="{{$row->name}}" placeholder="{{trans('general.description')}}">
                    </div>
                    <div class="del_b offset-4 col-1" data-vid="{{$row->id}}">
                        <button class="btn btn-danger v_delete m-1 align-content-end"><i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'price', trans('products.price'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('price[]', numberFormat(@$row->price), ['class' => 'form-control box-size', 'placeholder' => trans('products.price'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'purchase_price', trans('products.purchase_price'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('purchase_price[]', numberFormat(@$row->purchase_price), ['class' => 'form-control box-size', 'placeholder' => trans('products.purchase_price'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'qty', trans('products.qty'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('qty[]', numberFormat(@$row->qty), ['class' => 'form-control box-size','readonly', 'placeholder' => trans('products.qty'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label( 'productcategory_id', trans('products.warehouse_id'),['class' => 'col control-label']) }}
                            <div class='col'>
                                <select class="form-control location" name="warehouse_id[]">
                                    @foreach($warehouses as $item)
                                    <option value="{{$item->id}}" {{ $item->id === @$row->warehouse_id ? " selected" : "" }}>{{$item->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'code', trans('products.code'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('code[]', @$row->code, ['class' => 'form-control box-size','placeholder' => trans('products.code')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'barcode', trans('products.barcode'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('barcode[]', $row->barcode, ['class' => 'form-control box-size', 'placeholder' => trans('products.barcode')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'disrate', trans('products.disrate'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('disrate[]', numberFormat(@$row->disrate), ['class' => 'form-control box-size', 'placeholder' => trans('products.disrate'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'alert', trans('products.alert'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('alert[]', numberFormat(@$row->alert), ['class' => 'form-control box-size', 'placeholder' => trans('products.alert'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'moq', 'Minimum Order Quantity(MoQ)',['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('moq[]', numberFormat(@$row->moq), ['class' => 'form-control box-size', 'placeholder' => '0.00','onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class='form-group'>
                            {{ Form::label( 'image', trans('products.image'),['class' => 'col control-label']) }}
                            <div class='col'>
                                @if(isset($row) && $row->image)
                                    <img src="{{Storage::disk('public')->url('img/products/' . $row->image)}}" alt="Product Image" width="150">
                                @endif
                                {!! Form::file('image[]', array('class'=>'input' )) !!}
                                <input type="hidden" name="existing_image[]" value="{{ @$row->image }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class='form-group'>
                            {{ Form::label( 'image', 'Image Description/Specifications',['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::textarea('image_description[]', @$row->image_description, ['class' => 'form-control box-size', 'placeholder' => 'Image Description/Specifications']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div id="added_product"></div>
<a href="#" class="card-title "><i class="fa fa-plus-circle"></i> {{trans('products.variation')}}</a>
<button class="btn btn-blue add_more btn-sm m-1">{{trans('general.add_row')}}</button>
<button class="btn btn-pink add_serial btn-sm m-1">{{trans('products.add_serial')}}</button>
<div id="remove_variation"></div>

@section("after-styles")
<style>
    #added_product div:nth-child(even) .product {
        background: #FFF
    }

    #added_product div:nth-child(odd) .product {
        background: #eeeeee
    }

    #product_sub div:nth-child(odd) .v_product_t {
        background: #FFF
    }

    #product_sub div:nth-child(even) .v_product_t {
        background: #eeeeee
    }
</style>
{!! Html::style('focus/css/select2.min.css') !!}
@endsection

@section("after-scripts")
@include('focus.products.form_js')
@endsection