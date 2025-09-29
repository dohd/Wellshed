<div class="form-group row">
    <legend class="mt-2"></legend><hr>
    <div class="col-12">
        <div class="table-responsive">
            <table id="stockTbl" class="table" widht="50%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>UoM</th>
                        <th>Qty</th>
                        <th>Vat</th>
                        <th>Price</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="checkbox" class="check"></td>
                        <td><input type="text" name="product_name[]" value="" id="product_name-0" class="form-control product_name"></td>
                        <td>
                            <select name="uom[]" id="uom-0" class="form-control uom">
                                {{-- <option value="{{$item->uom}}">{{$item->uom}}</option> --}}
                            </select>
                        </td>
                        <td><input type="text" name="qty[]" value="" id="qty-0" class="form-control qty"></td>
                        <td>
                            <select class="form-control rowtax" name="itemtax[]" id="rowtax-0">
                                @foreach ($additionals as $tax)
                                    <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                        {{ $tax->name }}
                                    </option>
                                @endforeach                                                    
                            </select>
                        </td>
                        <td><input type="text" name="price[]" value="" id="price-0" class="form-control price"></td>
                        <td><span class="amount">0</span></td>
                        <td>
                            <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                <i class="fa fa-trash fa-lg text-danger"></i>
                            </button>
                        </td>
                        <input type="hidden" class="rate_tax" name="tax_rate[]">
                        <input type="hidden" class="row_amount" name="amount[]">
                        <input type="hidden" class="form-control taxable" value="0">
                        <input type="hidden" name="id[]" value="0">
                        <input type="hidden" class="form-control product_id" id="productid-0" name="product_id[]" value="">
                    </tr>
                   @isset($petty_cash)
                       @foreach ($petty_cash->items as $i => $item)
                           <tr>
                            <td>{{$i+1}}</td>
                            <td><input type="text" name="product_name[]" value="{{$item->product_name}}" id="product_name-{{$i+1}}" class="form-control product_name"></td>
                            <td>
                                <select name="uom[]" id="uom-{{$i+1}}" class="form-control uom">
                                    <option value="{{$item->uom}}">{{$item->uom}}</option>
                                </select>
                            </td>
                            <td><input type="text" name="qty[]" value="{{$item->qty}}" id="qty-{{$i+1}}" class="form-control qty"></td>
                            <td>
                                <select class="form-control rowtax" name="itemtax[]" id="rowtax-{{$i+1}}">
                                    @foreach ($additionals as $tax)
                                        <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->tax ? 'selected' : ''}}>
                                            {{ $tax->name }}
                                        </option>
                                    @endforeach                                                    
                                </select>
                            </td>
                            <td><input type="text" name="price[]" value="{{$item->price}}" id="price-{{$i+1}}" class="form-control price"></td>
                            <td><span class="amount">{{$item->amount}}</span></td>
                            <td>
                                <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                    <i class="fa fa-trash fa-lg text-danger"></i>
                                </button>
                            </td>
                            <input type="hidden" class="rate_tax" name="tax_rate[]" value="{{$item->tax_rate}}">
                            <input type="hidden" class="row_amount" name="amount[]" value="{{$item->amount}}">
                            <input type="hidden" class="form-control taxable" value="0">
                            <input type="hidden" name="id[]" value="{{$item->id}}">
                            <input type="hidden" class="form-control product_id" name="product_id[]" value="{{$item->product_id}}">
                           </tr>
                       @endforeach
                   @endisset
                </tbody>
            </table>
        </div>
        <button class="btn btn-success btn-sm ml-2 mb-3" type="button" id="addDoc">
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
        </button>
    </div>
</div>