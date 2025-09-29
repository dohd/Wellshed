<div class="table-responsive mt-2">
    <table class="table table-stripped" id="productTbl" cellspan="2">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>UoM</th>
                <th>Landed Price</th>
                <th>Minimum Selling Price</th>
                <th>Recommended Selling Price</th>
                <th>Minimum Order Qty (MoQ)</th>
                <th>Reorder Level</th>
            </tr>
        </thead>
        <tbody>
           @isset($sell_price)
               @foreach ($sell_price->items as $i => $item)
               <tr>
                <td>{{$i+1}}</td>
                <td>{{$item->import_req_item ? $item->import_req_item->product_name : ''}}</td>
                <td>{{$item->import_req_item ? +$item->import_req_item->qty : ''}}</td>
                <td>{{$item->import_req_item ? $item->import_req_item->unit : ''}}</td>
                <td><input type="text" name="landed_price[]" class="form-control landed_price" value="{{numberFormat($item->landed_price)}}" id="landed_price-{{$i+1}}" readonly></td>
                <td><input type="text" name="minimum_selling_price[]" class="form-control minimum_selling_price" value="{{numberFormat($item->minimum_selling_price)}}" id="minimum_selling_price-{{$i+1}}"></td>
                <td><input type="text" name="recommended_selling_price[]" value="{{numberFormat($item->recommended_selling_price)}}" class="form-control recommended_selling_price" id="recommended_selling_price-{{$i+1}}"></td>
                <td><input type="text" name="moq[]" class="form-control moq" value="{{numberFormat($item->moq)}}"  id="moq-{{$i+1}}"></td>
                <td><input type="text" name="reorder_level[]" class="form-control reorder_level" value="{{numberFormat($item->reorder_level)}}" id="reorder_level-{{$i+1}}"></td>
                <input type="hidden" name="product_id[]" value="{{$item->product_id}}">
                <input type="hidden" name="import_request_item_id[]" value="{{$item->import_request_item_id}}">
                <input type="hidden" name="id[]" value="{{$item->id}}">
            </tr>
               @endforeach
           @endisset
        </tbody>
    </table>
</div>