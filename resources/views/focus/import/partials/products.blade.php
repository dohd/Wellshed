{{ Form::open(['route' => ['biller.import.general', 'products'], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'files' => true, 'id' => 'import-data']) }}
    <input type="hidden" name="update" value="1">
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
    <div class="row">
        {{-- <div class="col-md-4">
            <div class="form-group">
                {{ Form::label( 'productcategory_id', trans('products.productcategory_id'),['class' => 'col control-label']) }}
                <div class='col'>
                    <select class="form-control" name="category_id" id="product_cat" required>
                        @foreach($data['product_categories'] as $item)
                            <option value="{{$item->id}}">{{$item->title}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div> --}}
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('productcategory_id', 'Product Location',['class' => 'col control-label']) }}
                <div class='col'>
                    <select class="form-control" name="warehouse_id" id="warehouse" required>
                        @foreach($data['warehouses'] as $item)
                            <option value="{{$item->id}}">{{$item->title}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    {{ Form::submit(trans('import.upload_import'), ['class' => 'btn btn-primary btn-md']) }}
{{ Form::close() }}