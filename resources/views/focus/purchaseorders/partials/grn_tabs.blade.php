<!-- tab menu -->
<ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
    <li class="nav-item">
        <a class="nav-link " id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab">
            Inventory / Stock
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
            Expenses
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">
            Asset & Equipments
        </a>
    </li>
</ul>
<!-- stock tab -->
<div class="tab-content px-1 pt-1">
    <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
        <table class="table-responsive tfr" width="100%" id="stockTbl">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white">
                    <th width="6%" class="text-center">#</th>
                    <th width="35%" class="text-center">Product Description</th>
                    <th width="10%" class="text-center">PO Qty</th>
                    <th width="10%">Qty Received</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="10%" class="text-center">DNote</th>                            
                    <th width="16%" class="text-center">Date</th>
                </tr>                                
            </thead>
            <tbody>
                @php ($i = 0)
                @foreach ($po->products as $item)
                    @if ($item->type == 'Stock')
                        <tr>
                            <td class="text-center">{{$i + 1}}</td>
                            <td><textarea name="description" cols="50" rows="3" disabled>{{ $item->description }}</textarea></td>
                            <td><input type="text" class="form-control" value="{{ number_format($item->qty, 1) }}" disabled></td>
                            <td><input type="text" class="form-control" name="grn_qty[]" value="{{ $item->grn_items->sum('qty') }}" readonly></td>
                            <td><input type="number" step=".01" class="form-control qty" name="qty[]"></td>
                            <td><input type="text" class="form-control" name="dnote[]"></td>
                            <td><input type="text" class="form-control datepicker" name="date[]"></td>
                            <input type="hidden" name="poitem_id[]" value="{{ $item->id }}">
                            <input type="hidden" class="porate" name="poitem_rate[]" value="{{ $item->rate }}">
                            <input type="hidden" class="potax" name="poitem_taxrate[]" value="{{ $item->taxrate/$item->qty }}">
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
                <tr>
                    <td colspan="6"></td>
                    <td>
                        <b>Number of Goods</b>
                        <input type="text" class="form-control" name="stock_grn" value="0" id="stock_grn" readonly>
                        <input type="hidden" name="stock_subttl" value="0" id="stock_subttl">
                        <input type="hidden" name="stock_tax" value="0" id="stock_tax">
                        <input type="hidden" name="stock_grandttl" value="0" id="stock_grandttl">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- expense tab -->
    <div class="tab-pane" id="active2" aria-labelledby="equipment-maintained" role="tabpanel">
        <table class="table-responsive tfr" width="100%" id="expTbl">
            <thead>
                <tr class="item_header bg-gradient-directional-danger white">
                    <th width="6%" class="text-center">#</th>
                    <th width="35%" class="text-center">Product Description</th>
                    <th width="10%" class="text-center">PO Qty</th>
                    <th width="10%">Qty Received</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="10%" class="text-center">DNote</th>                            
                    <th width="16%" class="text-center">Date</th>
                </tr>                                
            </thead>
            <tbody>
                @php ($i = 1)
                @foreach ($po->products as $item)
                    @if ($item->type == 'Expense')
                        <tr>
                            <td class="text-center">{{$i}}</td>
                            <td><textarea name="description" cols="50" rows="3" disabled>{{ $item->description }}</textarea></td>
                            <td><input type="text" class="form-control" value="{{ number_format($item->qty, 1) }}" disabled></td>
                            <td><input type="text" class="form-control" name="grn_qty[]" value="{{ $item->grn_items->sum('qty') }}" readonly></td>
                            <td><input type="number" step=".01" class="form-control qty" name="qty[]"></td>
                            <td><input type="text" class="form-control" name="dnote[]"></td>
                            <td><input type="text" class="form-control datepicker" name="date[]"></td>
                            <input type="hidden" name="poitem_id[]" value="{{ $item->id }}">
                            <input type="hidden" class="porate" name="poitem_rate[]" value="{{ $item->rate }}">
                            <input type="hidden" class="potax" name="poitem_taxrate[]" value="{{ $item->taxrate/$item->qty }}">
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
                <tr>
                    <td colspan="6"></td>
                    <td>
                        <b>Number of Goods</b>
                        <input type="text" class="form-control" name="expense_grn" value="0" id="expense_grn" readonly>
                        <input type="hidden" name="expense_subttl" value="0" id="expense_subttl">
                        <input type="hidden" name="expense_tax" value="0" id="expense_tax">
                        <input type="hidden" name="expense_grandttl" value="0" id="expense_grandttl">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- asset tab -->
    <div class="tab-pane" id="active3" aria-labelledby="equipment-maintained" role="tabpanel">
        <table class="table-responsive tfr" width="100%" id="assetTbl">
            <thead>
                <tr class="item_header bg-gradient-directional-success white">
                    <th width="6%" class="text-center">#</th>
                    <th width="35%" class="text-center">Product Description</th>
                    <th width="10%" class="text-center">PO Qty</th>
                    <th width="10%">Qty Received</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="10%" class="text-center">DNote</th>                            
                    <th width="16%" class="text-center">Date</th>
                </tr>                                
            </thead>
            <tbody>
                @php ($i = 1)
                @foreach ($po->products as $item)
                    @if ($item->type == 'Asset')                                    
                        <tr>
                            <td class="text-center">{{$i}}</td>
                            <td><textarea name="description" cols="50" rows="3" disabled>{{ $item->description }}</textarea></td>
                            <td><input type="text" class="form-control" value="{{ number_format($item->qty, 1) }}" disabled></td>
                            <td><input type="text" class="form-control" name="grn_qty[]" value="{{ $item->grn_items->sum('qty') }}" readonly></td>
                            <td><input type="number" step=".01" class="form-control qty" name="qty[]"></td>
                            <td><input type="text" class="form-control" name="dnote[]"></td>
                            <td><input type="text" class="form-control datepicker" name="date[]"></td>
                            <input type="hidden" name="poitem_id[]" value="{{ $item->id }}">
                            <input type="hidden" class="porate" name="poitem_rate[]" value="{{ $item->rate }}">
                            <input type="hidden" class="potax" name="poitem_taxrate[]" value="{{ $item->taxrate/$item->qty }}">
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
                <tr>
                    <td colspan="6"></td>
                    <td>
                        <b>Number of Goods</b>
                        <input type="text" class="form-control" name="asset_grn" value="0" id="asset_grn" readonly>
                        <input type="hidden" name="asset_tax" value="0" id="asset_tax">
                        <input type="hidden" name="asset_subttl" value="0" id="asset_subttl">
                        <input type="hidden" name="asset_grandttl" value="0" id="asset_grandttl">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>