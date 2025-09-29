{{ Form::open(['route' => 'biller.products.efris_goods_adjustment', 'method' => 'POST', 'id' => 'goodsAdjForm']) }}
    <input type="hidden" name="purchase_id" value="{{ @$purchase->id }}">
    <input type="hidden" name="grn_id" value="{{ @$grn->id }}">

    <div class="form-inline mb-1">
        <label for="operationType" class="mr-1 h5 font-weight-bold">Operation Type:</label>
        <select name="operation_type" class="custom-select" style="height:2em;">
            <option value="101">Increase</option>
            <option value="102">Decrease</option>
        </select>
    </div>
    <div class="row mb-1">
        <div class="col-md-4">
            <fieldset class="border p-0 pl-1 pr-1">
                <legend class="w-auto float-none h5">Supplier Details</legend>
                <h5>
                    @php
                        if ($purchase->id) {
                            $supplier = $purchase->supplier;
                            $supplierName = $purchase->suppliername;
                        } else if ($grn->id) {
                            $supplier = $grn->supplier;
                        }
                    @endphp
                    <b>Supplier Name: </b> {{ @$supplierName ?: $supplier->name }}<br>
                    <b>Supplier TIN: </b> {{ $supplier->taxid }} <br>
                    <b>Stock-In Type: </b> Local Purchase<br>   
                </h5>
            </fieldset>
        </div>
    </div>

    <fieldset class="border p-0 pl-1 pr-1 mb-1">
        <legend class="w-auto float-none h5">Goods Details</legend>
        <div class="table-responsive">
            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%" style="max-height: 550px; overflow-y: auto;">
                <thead>
                    <tr style="background: #F6F9FD">
                        <th>Item</th>
                        <th>Item Code</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                    </tr>
                </thead>
                @if ($purchase->id)
                    <tbody>
                        @foreach ($purchase->items as $key => $item)
                            @php
                                $productVar = $item->productvariation;
                                $efrisGood = $productVar->efris_good;
                                $unitPrice = round(($item->rate * (1 + ($item->itemtax * 0.01))), 4);
                            @endphp
                            <tr>
                                <td style="min-width: 30rem">{{ $productVar->name }}</td>
                                <td>{{ $efrisGood->goods_code }}</td>
                                <td><input type="text" name="qty[]" value="{{ +$item->qty }}" class="form-control"></td>
                                <td><input type="text" name="unit_price[]" value="{{ $unitPrice }}" class="form-control"></td>
                                <input type="hidden" name="goods_code[]" value="{{ $efrisGood->goods_code }}">
                                <input type="hidden" name="productvar_id[]" value="{{ $productVar->id }}">
                                <input type="hidden" name="purchase_item_id[]" value="{{ $item->id }}">
                            </tr>
                        @endforeach
                    </tbody>
                @elseif ($grn->id)
                    <tbody>
                        @foreach ($grn->items as $key => $item)
                            @php
                                $poItem = $item->purchaseorder_item;
                                $productVar = $poItem->productvariation;
                                $efrisGood = $productVar->efris_good;
                                $unitPrice = round(($poItem->rate * (1 + ($poItem->itemtax * 0.01))), 4);
                            @endphp
                            <tr>
                                <td style="min-width: 30rem">{{ $productVar->name }}</td>
                                <td>{{ $efrisGood->goods_code }}</td>
                                <td><input type="text" name="qty[]" value="{{ +$item->qty }}" class="form-control"></td>
                                <td><input type="text" name="unit_price[]" value="{{ $unitPrice }}" class="form-control"></td>
                                <input type="hidden" name="goods_code[]" value="{{ $efrisGood->goods_code }}">
                                <input type="hidden" name="productvar_id[]" value="{{ $productVar->id }}">
                                <input type="hidden" name="grn_item_id[]" value="{{ $item->id }}">
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            </table>
        </div>
    </fieldset>

    <div class="modal-footer">                        
        <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans('general.close')}}</button>
        <button type="button" id="submitStockAdjustment" class="btn btn-vimeo"><i class="fa fa-exclamation-circle"></i> Submit</button>
    </div>
{{ Form::close() }}
