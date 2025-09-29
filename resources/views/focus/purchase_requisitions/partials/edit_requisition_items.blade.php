<table id="requisitionsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th style="width:4%;">#</th>
            <th style="width:20%;">Product Name</th>
            <th style="width:6%;">Unit</th>
            <th style="width:7%;">Code</th>
            <th style="width:12%;">Remark</th>
            <th style="width:8%;">Current Stock</th>
            <th style="width:8%;">Qty Req.</th>
            <th style="width:8%;">Qty Remaining</th>
            <th style="width:8%;">Qty to Issue</th>
            <th style="width:8%;">Qty to Purchase</th>
            <th style="width:6%;">Actions</th>
        </tr>
    </thead>
    <tbody>
        
        @isset($purchase_requisition)
            @foreach ($purchase_requisition->items as $k => $item)
                <tr>
                    @php
                        $budgeted_qty = 0;
                        $qty_requested = 0;
                        if($item->milestone_item){
                            $budgeted_qty = $item->milestone_item->qty;
                            $qty_requested = $item->milestone_item->qty_requested;
                        }else if($item->budget_item){
                            $budgeted_qty = $item->budget_item->new_qty - $item->budget_item->issue_qty;
                        }
                    @endphp
                    <td><span class="numbering">{{$k+1}}</span></td>
                    <td><input type="text" name="product_name[]" id="name-p{{$k}}" value="{{$item->product_name}}" class="form-control"></td>
                    <td><select name="unit_id[]" id="uom-p{{$k}}" class="form-control uom">
                        <option value="{{ $item->unit_id }}" selected>{{ $item->unit ? $item->unit->code : '' }}</option>
                        </select>
                    </td> 
                    <td><span class="code" id="code-p{{$k}}">{{$item->product->code}}</span></td>
                    <td><input type="text" name="remark[]" id="remark-p{{$k}}" value="{{$item->remark}}" class="form-control remark" readonly></td>
                    <td><span>{{@$item->product->qty}}</span></td>
                    <td><span>{{numberFormat(@$item->mr_items->qty)}}</span></td>
                    <td><input type="text" name="qty[]" id="qty-p{{$k}}" value="{{numberFormat($item->qty)}}" class="form-control qty" readonly></td>
                    <td><input type="text" name="stock_qty[]" id="stock_qty-p{{$k}}" value="{{numberFormat($item->stock_qty)}}" class="form-control stock_qty"></td>
                    <td><input type="text" name="purchase_qty[]" id="purchase_qty-p{{$k}}" value="{{numberFormat($item->purchase_qty)}}" class="form-control purchase_qty"></td>
                    <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" name="product_id[]" id="productid-p{{$k}}" value="{{$item->product_id}}">
                    <input type="hidden" name="price[]" class="price" id="price-p{{$k}}" value="{{$item->price}}">
                    <input type="hidden" name="milestone_item_id[]" class="milestone_item" id="milestone_item-p{{$k}}" value="{{$item->milestone_item_id}}">
                    <input type="hidden" name="budget_item_id[]" class="budget_item_id" id="budget_item_id-p{{$k}}" value="{{$item->budget_item_id}}">
                    <input type="hidden" name="purchase_request_item_id[]" class="purchase_request_item_id" value="{{$item->purchase_request_item_id}}">
                    <input type="hidden" name="id[]" class="id" value="{{$item->id}}">
                </tr>
            @endforeach
        @endisset
    </tbody>
</table>