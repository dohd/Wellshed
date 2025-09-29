<div class="tab-pane fade in" id="active3" aria-labelledby="link-tab3" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="assetTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-success white text-center">
                <th width="10%">#</th>
                <th width="30%">{{trans('general.item_name')}}</th>
                <th width="8%">{{trans('general.quantity')}}</th>
                <th width="7%">UoM</th>
                <th width="10%">{{trans('general.rate')}}</th>
                <th width="10%">{{trans('general.tax_p')}}</th>
                <th width="10%">{{trans('general.tax')}}</th>
                <th width="10%">{{trans('general.amount')}}</th>
                <th width="5%" class="pr-2">{{trans('general.action')}}</th>
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td><input type="text" class="form-control" value="1" id="assetinc-0" disabled></td>
                <td><input type="text" class="form-control assetname" name="name[]" placeholder="Asset Or Equipment" id="assetname-0"></td>
                <td><input type="text" class="form-control asset_qty" name="qty[]" value="1" id="assetqty-0"></td>
                <td><input type="text" class="form-control uom" name="uom[]" id="uom-0"></td>                    
                <td><input type="text" class="form-control asset_price" name="rate[]" id="assetprice-0"></td>
                <td>
                    <select class="form-control asset_vat" name="itemtax[]" id="assetvat-0">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }}
                            </option>
                        @endforeach                                                    
                    </select>
                </td>  
                <td class="text-center"><span class="asset_tax">0</span></td>
                <td class="text-center"><b><span class='asset_amount'>0</span></b></td>
                <td><button type="button" class="btn btn-danger remove d-none"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" id="assetitemid-0" name="item_id[]">
                <input type="hidden" class="assettaxr" name="taxrate[]">
                <input type="hidden" class="assetamountr" name="amount[]">
                <input type="hidden" class="assetitemprojectid" name="itemproject_id[]" value="0">
                <input type="hidden" name="type[]" value="Asset">
                <input type="hidden" name="import_request_id[]" id="">
                <input type="hidden" name="id[]" value="0">
            </tr>
            <tr>
                <td colspan="3"><textarea class="form-control descr" name="description[]" placeholder="Product Description" id="assetdescr-0"></textarea></td>
                <td colspan="6"></td>
            </tr>
            <!-- end layout -->
            
            <!-- fetched rows -->
            @isset ($po)
                @php ($i = 0)
                @foreach ($po->products as $item)
                    @if ($item->type == 'Asset')
                        <tr>
                            <td><input type="text" class="form-control" value="{{$i+1}}" id="assetinc-{{$i}}" disabled></td>
                            <td><input type="text" class="form-control assetname" name="name[]" value="{{ $item->asset->name }}" placeholder="Asset Or Equipment" id="assetname-{{$i}}"></td>
                            <td><input type="text" class="form-control asset_qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="assetqty-{{$i}}"></td>
                            <td><input type="text" class="form-control uom" name="uom[]" value="{{ $item->uom }}" id="uom-0"></td>                    
                            <td><input type="text" class="form-control asset_price" name="rate[]" value="{{ (float) $item->rate }}" id="assetprice-{{$i}}"></td>
                            <td>
                                <select class="form-control asset_vat" name="itemtax[]" id="assetvat-{{$i}}">
                                    @foreach ($additionals as $tax)
                                        <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->itemtax ? 'selected' : ''}}>
                                            {{ $tax->name }}
                                        </option>
                                    @endforeach                                                    
                                </select>
                            </td>
                            <td class="text-center"><span class="asset_tax">{{ (float) $item->taxrate }}</span></td>
                            <td class="text-center"><b><span class='asset_amount'>{{ (float) $item->amount }}</span></b></td>
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                            <input type="hidden" id="assetitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                            <input type="hidden" class="assettaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                            <input type="hidden" class="assetamountr" name="amount[]" value="{{ (float) $item->amount }}">
                            <input type="hidden" class="assetitemprojectid" name="itemproject_id[]" value="0">
                            <input type="hidden" name="type[]" value="Asset">
                            <input type="hidden" name="import_request_id[]" id="">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        <tr>
                            <td colspan="3">
                                <textarea class="form-control descr" name="description[]" placeholder="Product Description" id="assetdescr-{{$i}}">{{ $item->description }}</textarea>
                            </td>
                            <td colspan="6"></td>
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
            <!-- end fetched rows -->

            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addasset">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="8"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="7" align="right"><b>{{trans('general.total_tax')}}</b></td>
                <td align="left" colspan="2"><span id="assettaxrow">0</span></td>
            </tr>
            <tr class="bg-white">
                <td colspan="7" align="right"><b>{{trans('general.grand_total')}}</b></td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="asset_grandttl" value="0.00" id="asset_grandttl" readonly>
                    <input type="hidden" name="asset_tax" value="0.00" id="asset_tax">
                    <input type="hidden" name="asset_subttl" value="0.00" id="asset_subttl">
                </td>
            </tr>
        </tbody>
    </table>
</div>
