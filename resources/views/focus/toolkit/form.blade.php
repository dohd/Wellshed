<div class="card">
   <div class="card-content">
    <div class="card-body">
        <div class="row">
            <div class="col-4">
                {{ Form::label( 'name', 'Service Kit Name',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('toolkit_name', null, ['class' => 'form-control box-size', 'id'=>'toolkit_name']) }}
                    </div>
            </div>
            
        </div>
        
    </div>
    <div class="table-responsive">        
        <table id="itemTbl" class="table">
            <thead>
                <tr class="bg-gradient-directional-blue white">
                    <th width="40%">Product Name</th>
                    <th>Quantity</th>
                    <th>Issued Quantity</th>
                    <th width="7%">UOM</th>
                    <th>Code</th>
                    <th>Cost</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" class="form-control toolname col" name="toolname[]" placeholder="Item Name" id="toolname-0"></td>
                    <td><input type="text" class="form-control quantity" name="q[]" placeholder="0.00" id="quantity-0" readonly></td>
                    <td><input type="text" class="form-control quant" name="quantity[]" placeholder="0.00" id="quant-0" required></td>
                    <td width="10%"><select name="uom[]"  id="uom-0" class="form-control uom" required></select></td>
                    <td><input type="text" class="form-control code" name="code[]" required id="code-0" readonly></td>
                    <td><input type="text" class="form-control cost" name="cost[]" placeholder="0.00" id="cost-0"></td>
                    <td><button type="button" class="btn btn-danger remove"><i class="fa fa-trash"></i></button></td>
                    <input type="hidden" class="item_id" name="item_id[]" id="item_id-0">
                    <input type="hidden" class="qty" name="qty[]" id="qty-0">
                    <input type="hidden" name="id[]" value="0">

                    @isset ($toolkit_items)
                    @php ($i = 0)
                    @foreach ($toolkit_items as $item)
                        @if ($item)
                            <tr>
                                <td><input type="text" class="form-control toolname col" name="toolname[]" value="{{$item->toolname}}" id="toolname-{{$i}}"></td>
                                <td><input type="text" class="form-control quantity" value="0" name="q[]" id="quantity-{{$i}}" readonly></td>
                                <td><input type="text" class="form-control quant" value="{{$item->quantity}}" name="quantity[]" id="quant-{{$i}}"></td>
                                <td><input type="text" name="uom[]"  id="uom-{{$i}}" value="{{$item->uom}}" class="form-control uom" readonly></td>
                                <td><input type="text" class="form-control code" value="{{$item->code}}" name="code[]" id="code-{{$i}}" readonly></td>
                                <td><input type="text" class="form-control cost" value="{{$item->cost}}" name="cost[]" id="cost-{{$i}}" readonly></td>
                                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-trash"></i></button></td>
                                <input type="hidden" class="item_id" name="item_id[]" value="{{$item->item_id}}" id="item_id-{{$i}}">
                                <input type="hidden" class="id" name="id[]" value="{{$item->id}}" id="id-{{$i}}">
                                
                            </tr>
                            @php ($i++)
                        @endif
                    @endforeach
                @endisset
                </tr>
            </tbody>
        </table>
    </div>
    <div class="form-group row">
        <div class="col-2 ml-2">
            <button type="button" class="btn btn-success" id="addtool">Add Product</button>
        </div>
    </div>
</div>
</div>