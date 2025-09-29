<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
   <section class="todo-form">
                <form id="data_form_project" class="todo-input">

      <!-- Modal Header -->
            <div class="modal-header bg-gradient-directional-purple white">

                <h4 class="modal-title" id="myModalLabel">Add Product</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">{{trans('general.close')}}</span>
                </button>
            </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
           {{ Form::label( 'name', trans('products.name'),['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('name', null, ['class' => 'form-control box-size required', 'placeholder' => trans('products.name').'*']) }}
            </div>
          </div>
        </div>
             <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'code', trans('products.code'),['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('code[]', @$products->standard['code'], ['class' => 'form-control box-size required', 'placeholder' => trans('products.code')]) }}
                    </div>
                </div>
            </div>

      
          <div class="col-md-4">
        <div class="form-group">
            {{ Form::label( 'productcategory_id', trans('products.productcategory_id'),['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control required" name="productcategory_id" id="product_cat">
                    @foreach($product_category as $item)
                        @if(!$item->c_type)
                            <option value="{{$item->id}}" {{ $item->id === @$products->productcategory_id ? " selected" : "" }}>{{$item->title}}</option>
                        @endif
                    @endforeach

                </select>
            </div>
        </div>
    </div>

             <div class="col-md-4">
        <div class="form-group">
            {{ Form::label( 'sub_cat_id', trans('products.sub_cat_id'),['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="sub_cat_id" id="sub_cat">
                    <option value="0">--{{ trans('products.sub_cat_id')}}--</option>
                    @foreach($product_category as $item)
                        @if($item->c_type AND $product_category->first()['id']==$item->rel_id)
                            <option value="{{$item->id}}" {{ $item->id === @$products->productcategory_id ? " selected" : "" }}>{{$item->title}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
    </div>
       <div class="col-md-4">
        <div class="form-group">
            {{ Form::label( 'unit', trans('products.unit'),['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="unit">
                    @foreach($product_variable as $item)
                        @if(!$item->type)
                            <option value="{{$item->code}}" {{ $item->code === @$products->unit ? " selected" : "" }}>{{$item->name}}
                                - {{$item->code}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
    </div>

      <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'code_type', trans('products.code_type'),['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control required" name="code_type">
                    @if(@$products->code_type)
                        <option value="{{$products->code_type}}" selected>{{$products->code_type}}</option>
                    @endif
                    <option value="EAN13">EAN13 - Default</option>
                    <option value="UPCA">UPC</option>
                    <option value="EAN8">EAN8</option>
                    <option value="ISSN">ISSN</option>
                    <option value="ISBN">ISBN</option>
                    <option value="C128A">C128A</option>
                    <option value="C39">C39</option>
                </select>
            </div>
        </div>
    </div>

        <div class="col-md-4">
        <div class="form-group">
            {{ Form::label( 'unit', trans('products.stock_type'),['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="stock_type">
                    @if(@$products->stock_type===0)
                        <option value="0" selected>-{{trans('products.service')}}-</option> @endif
                    <option value="1">{{trans('products.material')}}</option>
                    <option value="0">{{trans('products.service')}}</option>
                </select>
            </div>
        </div>
    </div>
      <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'alert', trans('products.alert'),['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('alert[]', numberFormat(@$products->standard['alert']), ['class' => 'form-control box-size', 'placeholder' => trans('products.alert'),'onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>
              <div class="col-md-12">
                <div class='form-group'>
                    {{ Form::label( 'variation_name', trans('general.description'),['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('variation_name[]',@$products->standard['name'], ['class' => 'form-control box-size', 'placeholder' => trans('general.description')]) }}
                    </div>

                </div>
            </div>

             <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label( 'productcategory_id', trans('products.warehouse_id'),['class' => 'col control-label']) }}
                    <div class='col'>
                        <select class="form-control" name="warehouse_id[]">

                            @foreach($warehouses as $item)
                                <option value="{{$item->id}}" {{ $item->id === @$products->warehouse_id ? " selected" : "" }}>{{$item->title}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

      <div class="col-sm-4">
          <div class="form-group">
       {{ Form::label( 'taxrate', trans('products.taxrate'),['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('taxrate', numberFormat(@$products['taxrate']), ['class' => 'form-control box-size', 'placeholder' => trans('products.taxrate'),'onkeypress'=>"return isNumber(event)"]) }}
            </div>
          </div>
        </div>
             <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'disrate', trans('products.disrate'),['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('disrate[]', numberFormat(@$products->standard['disrate']), ['class' => 'form-control box-size', 'placeholder' => trans('products.disrate'),'onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>

                 <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'purchase_price', trans('products.purchase_price'),['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('purchase_price[]', numberFormat(@$products->standard['purchase_price']), ['class' => 'form-control box-size', 'placeholder' => trans('products.purchase_price'),'onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>

             <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'price', 'Default Selling Price',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('price[]', numberFormat(@$products->standard['price']), ['class' => 'form-control box-size', 'placeholder' => trans('products.price').'*','required'=>'required','onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>
      
   

        

       
      </div>
 
    
    </div>
      <div class="modal-footer">
                        <fieldset class="form-group position-relative has-icon-left mb-0">
                            <button type="button" id="submit-data_project" class="btn btn-info add-todo-item"
                                    data-dismiss="modal"><i class="fa fa-paper-plane-o d-block d-lg-none"></i>
                                <span class="d-none d-lg-block">Add Product</span></button>
                                    <input type="hidden" value="{{route('biller.products.store')}}" id="product_action-url">
                        </fieldset>
                    </div>




    </form>
            </section>



  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
   $("#submit-data_project").on("click", function (e) {
           e.preventDefault();
                var form_data = [];
                form_data['form'] = $("#data_form_project").serialize();
                form_data['url'] = $('#product_action-url').val();
                $('.quick_add_product_modal').modal('toggle');
                addObject(form_data, true);
            });


    </script>