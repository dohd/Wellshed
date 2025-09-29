<div class="tab-pane fade in" id="active3" aria-labelledby="link-tab3" role="tabpanel">
    <div class="table-responsive">
        <table class="table table-borderless tfr my_stripe" id="assetTbl">
            <thead>
                <tr class="item_header bg-gradient-directional-success white text-center">
                    <th width="5%">#</th>
                    <th width="35%">{{trans('general.item_name')}}</th>
                    <th width="8%">{{trans('general.quantity')}}</th>
                    <th width="8%">UoM</th>
                    <th width="10%">{{trans('general.rate')}}</th>
                    <th width="10%">VAT %</th>
                    <th width="10%">VAT</th>
                    <th width="10%">{{trans('general.amount')}}</th>
                    <th width="5%">{{trans('general.action')}}</th>
                </tr>
            </thead>
            <tbody>
                <!-- layout -->
                <tr>
                    <td><input type="text" class="form-control" value="1" id="assetinc-0" disabled></td>
                    <td><input type="text" class="form-control assetname" name="name[]" id="assetname-0" placeholder="Asset Or Equipment" autocomplete="off"></td>
                    <td><input type="text" class="form-control asset_qty" name="qty[]" value="1" id="assetqty-0"></td>
                    <td><input type="text" class="form-control asset_uom" name="uom[]" id="assetuom-0"></td>
                    <td><input type="text" class="form-control asset_price" name="rate[]" id="assetprice-0"></td>
                    <td>
                        <select class="form-control asset_vat custom-select" name="itemtax[]" id="assetvat-0">
                            <option value="">-- VAT --</option>
                            @foreach ($additionals as $tax)
                                <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                    {{ $tax->name }}
                                </option>
                            @endforeach                                                    
                        </select>
                    </td>  
                    <td class="text-center"><span class="asset_tax">0</span></td>
                    <td class="text-center"><b><span class='asset_amount'>0</span></b></td>
                    <td><button type="button" class="btn btn-danger remove d-none"><i class="fa fa-minus-square"></i></button></td>
                    <input type="hidden" id="assetitemid-0" name="item_id[]">
                    <input type="hidden" class="assettaxr" name="taxrate[]">
                    <input type="hidden" class="assetamountr" name="amount[]">
                    <input type="hidden" name="type[]" value="Asset">
                    <input type="hidden" name="id[]" value="0">
                    <input type="hidden" name="warehouse_id[]">
                    <input type="hidden" name="supplier_product_id[]">
                    <input type="hidden" name="import_request_id[]" id="">
                </tr>
                <tr>
                    <td colspan="4">
                        <textarea class="form-control descr" name="description[]" placeholder="Product Description" rows="4" id="assetdescr-0"></textarea>
                    </td>
                    <td colspan="5">
                        <input type="text" class="form-control projectasset" id="projectassettext-0" placeholder="Search Project By Name">
                        <input type="hidden" name="itemproject_id[]" id="projectassetval-0">
                        <input type="hidden" name="budget_line_id[]">
                        <div class="row mt-1">
                            <div class="col-6">
                                <select id="asset-class-budget-0" name="purchase_class_budget[]" class="form-control asset-class-budget round" data-placeholder="Search Non-Project Class">
                                    <option value=""></option>
                                    @foreach ($purchase_classes as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <select id="asset-classlist-id-0" name="item_classlist_id[]" class="form-control round" data-placeholder="Search Branch/ Dept">
                                    <option value=""></option>
                                    @foreach ($classlists as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </td>
                </tr>
                <!-- end layout -->
                <!-- fetched rows -->
                @isset ($purchase)
                    @php ($i = 0)
                    @foreach ($purchase->products as $item)
                        @if ($item->type == 'Asset')
                            <tr>
                                <td><input type="text" class="form-control" value="{{$i+1}}" id="assetinc-{{$i}}" disabled></td>
                                <td><input type="text" class="form-control assetname" name="name[]" value="{{ $item->asset->name }}" id="assetname-{{$i}}" placeholder="Asset Or Equipment"></td>
                                <td><input type="text" class="form-control asset_qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="assetqty-{{$i}}"></td>
                                <td><input type="text" class="form-control asset_uom" name="uom[]" value="{{ $item->uom }}" id="assetuom-{{$i}}"></td>
                                <td><input type="text" class="form-control asset_price" name="rate[]" value="{{ numberFormat($item->rate) }}" id="assetprice-{{$i}}"></td>
                                <td>
                                    <select class="form-control asset_vat custom-select" name="itemtax[]" id="assetvat-{{$i}}">
                                        <option value="">-- VAT --</option>
                                        @foreach ($additionals as $tax)
                                            <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->itemtax ? 'selected' : ''}}>
                                                {{ $tax->name }}
                                            </option>
                                        @endforeach                                                    
                                    </select>
                                </td>
                                <td class="text-center"><span class="asset_tax">{{ +$item->taxrate }}</span></td>
                                <td class="text-center"><b><span class='asset_amount'>{{ +$item->amount }}</span></b></td>
                                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square"></i></button></td>
                                <input type="hidden" id="assetitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                                <input type="hidden" class="assettaxr" name="taxrate[]" value="{{ +$item->taxrate }}">
                                <input type="hidden" class="assetamountr" name="amount[]" value="{{ +$item->amount }}">
                                <input type="hidden" name="type[]" value="Asset">
                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                <input type="hidden" name="warehouse_id[]">
                                <input type="hidden" name="supplier_product_id[]">
                                <input type="hidden" name="import_request_id[]" id="">
                            </tr>
                            <tr>
                                <td colspan="5">
                                    <textarea class="form-control descr" name="description[]" rows="4" placeholder="Product Description" id="assetdescr-{{$i}}">{{ $item->description }}</textarea>
                                </td>
                                <td colspan="5">
                                    <input type="text" class="form-control projectasset" value="{{ $item->project ? $item->project->name : '' }}" id="projectassettext-{{$i}}" placeholder="Search Project By Name">
                                    <input type="hidden" name="itemproject_id[]" value="{{ $item->itemproject_id }}" id="projectassetval-{{$i}}">
                                    <input type="hidden" name="budget_line_id[]">
                                    <div class="row mt-1">
                                        <div class="col-6">
                                            <select id="asset-class-budget-{{ $i }}" name="purchase_class_budget[]" class="form-control asset-class-budget round" data-placeholder="Search Non-Project Class">
                                                <option value=""></option>
                                                @foreach ($purchase_classes as $row)
                                                    <option value="{{ $row->id }}" {{ $item->purchase_class_budget == $row->id? 'selected' : '' }}>{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <select id="asset-classlist-id-{{ $i }}" name="item_classlist_id[]" class="form-control asset-classlist round" data-placeholder="Search Branch/ Dept">
                                                <option value=""></option>
                                                @foreach ($classlists as $row)
                                                    <option value="{{ $row->id }}" {{ $item->classlist_id == $row->id? 'selected' : '' }}>{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @php ($i++)
                        @endif
                    @endforeach
                @endisset
                <!-- end fetched rows -->
                <tr class="bg-white">
                    <td colspan="2">
                        <button type="button" class="btn btn-success btn-sm" aria-label="Left Align" id="addasset">
                            <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                        </button>
                    </td>
                    <td colspan="7"></td>
                </tr>
                <tr class="bg-white">
                    <td colspan="7" align="right"><b>{{trans('general.total_tax')}}</b></td>
                    <td align="left" colspan="2"><span id="assettaxrow">0</span></td>
                </tr>
                <tr class="bg-white">
                    <td colspan="7" align="right">
                        <b>Asset & Equipment Total</b>
                    </td>
                    <td align="left" colspan="2">
                        <input type="text" class="form-control" name="asset_grandttl" value="0.00" id="asset_grandttl" readonly>
                        <input type="hidden" name="asset_tax" value="0.00" id="asset_tax">
                        <input type="hidden" name="asset_subttl" value="0.00" id="asset_subttl">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
