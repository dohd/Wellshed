<table id="importsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th>#</th>
            <th>Product Name</th>
            <th>Unit</th>
            <th>Qty</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

        <tr id="productRow">
            <td><span class="numbering"></span></td>
            <td><input type="text" name="product_name[]" id="name-p0" value="" class="form-control"></td>
            <td><input name="unit[]" id="uom-p0" class="form-control uom" />
            </td> 
            <td><input type="text" name="qty[]" id="qty-p0" value="" class="form-control qty"></td>
            <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
            <input type="hidden" name="product_id[]" id="productid-p0" value="0">
            <input type="hidden" name="id[]" class="id" value="0"> 
        </tr>
        
        @isset($import_request)
            @foreach ($import_request->items as $k => $item)
                <tr>
                    <td><span class="numbering">{{$k+1}}</span></td>
                    <td><input type="text" name="product_name[]" id="name-p{{$k}}" value="{{$item->product_name}}" class="form-control"></td>
                    <td><input name="unit[]" id="uom-p{{$k+1}}" class="form-control uom" value="{{$item->unit}}" />
                    </td> 
                    <td><input type="text" name="qty[]" id="qty-p{{$k}}" value="{{numberFormat($item->qty)}}" class="form-control qty"></td>
                    <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" name="product_id[]" id="productid-p{{$k}}" value="{{$item->product_id}}">
                    <input type="hidden" name="id[]" class="id" value="{{$item->id}}">
                </tr>
            @endforeach
        @endisset
    </tbody>
</table>