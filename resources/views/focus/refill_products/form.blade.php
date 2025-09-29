<div class="row form-group">
    <div class="col-md-6">
        {{ Form::label( 'name', 'Product Name',['class' => 'control-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Product Name', 'required'=>'required']) }}
    </div>

    <div class="col-md-2">
        {{ Form::label('unit', 'Unit of Measure (UoM)', ['class' => 'control-label']) }}
        {{ Form::text('unit', null, ['class' => 'form-control box-size', 'placeholder' => 'Unit of Measure', 'required'=>'required']) }}
    </div>
    
    <div class="col-md-2">
        {{ Form::label('productcategory_id', trans('products.productcategory_id'),['class' => 'control-label']) }}
        <select class="custom-select" name="productcategory_id" id="product_cat">
            <option value="">-- Select Category --</option>
            @foreach($productcategories as $item)
                <option value="{{$item->id}}" {{ $item->id == @$refill_product->productcategory_id? 'selected' : '' }}>
                    {{$item->title}}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label for="sku">Unit Price</label>
        {{ Form::text('unit_price', numberFormat(@$refill_product->unit_price), ['class' => 'form-control', 'placeholder' => 'Unit Price']) }}
    </div>
</div>

<div class="row form-group">
    <div class="col-md-12">
        {{ Form::label('note', 'Product Description',['class' => 'control-label']) }}
        {{ Form::textarea('note', null, ['class' => 'form-control', 'rows'=> 2, 'placeholder' => 'Product Description']) }}
    </div>
</div>
