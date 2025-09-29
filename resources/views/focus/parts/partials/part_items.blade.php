<table id="partsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th>#</th>
            <th>Product Name</th>
            <th>Unit</th>
            <th>Product Code</th>
            <th>Qty Required For Single (Unit)</th>
            <th>Qty</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        
        <tr id="productRow">
            <td><span class="numbering">1</span></td>
            <td><input type="text" name="product_name[]" id="name-p0" class="form-control"></td>
            <td><select name="unit_id[]" id="uom-p0" class="form-control uom" ></select></td> 
            <td><span class="code" id="code-p0"></span></td>
            <td><input type="text" name="qty_for_single[]" id="qty_for_single-p0" class="form-control qty_for_single" value="1"></td>
            <td><input type="text" name="qty[]" id="qty-p0" class="form-control qty" readonly></td>
            <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
            <input type="hidden" name="product_id[]" id="productid-p0">
            <input type="hidden" name="id[]" class="id" value="0">
        </tr>
        @isset($part)
            @foreach ($part->part_items as $k => $item)
                <tr>
                    <td><span class="numbering">{{$k+1}}</span></td>
                    <td><input type="text" name="product_name[]" id="name-p{{$k}}" value="{{@$item->product->name}}" class="form-control"></td>
                    <td><select name="unit_id[]" id="uom-p{{$k}}" class="form-control uom">
                        <option value="{{ $item->unit_id }}" selected>{{ @$item->unit->code }}</option>
                        </select>
                    </td> 
                    <td><span class="code" id="code-p{{$k}}">{{@$item->product->code}}</span></td>
                    <td><input type="text" name="qty_for_single[]" id="qty_for_single-p{{$k}}" value="{{numberClean($item->qty_for_single)}}" class="form-control qty_for_single"></td>
                    <td><input type="text" name="qty[]" id="qty-p{{$k}}" value="{{numberClean($item->qty)}}" class="form-control qty"></td>
                    <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" name="product_id[]" id="productid-p{{$k}}" value="{{$item->product_id}}">
                    <input type="hidden" name="id[]" class="id" value="{{$item->id}}">
                </tr>
            @endforeach
        @endisset
    </tbody>
</table>