<div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="stockTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white text-center">
                <th width="10%">#</th>
                <th width="35%">{{trans('general.item_name')}}</th>
                <th width="7%">{{trans('general.quantity')}}</th>
                <th width="7%">UoM</th>
                <th width="10%">{{trans('general.rate')}}</th>
                <th width="10%">{{trans('general.tax_p')}}</th>
                <th width="10%">Tax</th>
                <th width="12%">{{trans('general.amount')}}</th>
                {{-- <th width="5%" class="pr-2">Action</th>                    --}}
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <!-- end layout -->

            <!-- fetched rows -->
            @isset ($po)
                @php ($i = 0)
                @foreach ($po->products as $item)
                    @if ($item->type == 'Stock')
                        <tr>
                            <td><input type="text" class="form-control increment" value="{{$i+1}}" id="increment-0" disabled></td>
                            <td>
                                <input type="text" class="form-control stockname" name="name[]" value="{{ $item->description }}" placeholder="Product Name" id='stockname-{{$i}}' readonly>
                                <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                            </td>
                            <td><input type="text" class="form-control qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="qty-{{$i}}"></td>                    
                            <td>
                                <select name="uom[]" id="uom-{{ $i }}" class="form-control uom">
                                    <option value="{{ $item->uom }}" selected>{{ $item->uom }}</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control price" name="rate[]" value="{{ (float) $item->rate }}" id="price-{{$i}}" readonly></td>
                            <td>
                                <select class="form-control rowtax" name="itemtax[]" id="rowtax-{{$i}}" @readonly(true)>
                                    @foreach ($additionals as $tax)
                                        <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->itemtax ? 'selected' : ''}}>
                                            {{ $tax->name }}
                                        </option>
                                    @endforeach                                                    
                                </select>
                            </td>
                            <td><input type="text" class="form-control taxable" value="{{ (float) $item->taxrate }}" readonly></td>
                            <td class="text-center"><b><span class='amount' id="result-{{$i}}">{{ (float) $item->amount }}</span></b></td>              
                            {{-- <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td> --}}
                            {{-- <input type="hidden" class="rowtax" name="itemtax[]" id="rowtax-{{$i}}" value="{{ (int) $tax->value }}"> --}}
                            <input type="hidden" class="stocktaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                            <input type="hidden" class="stockamountr" name="amount[]" value="{{ (float) $item->amount }}">
                            <input type="hidden" name="type[]" value="Stock">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        <tr>
                            <td colspan=2>
                                <textarea id="stockdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Product Description" readonly>{{ $item->description }}</textarea>
                            </td>
                            <td><input type="text" class="form-control product_code" value="{{$item->product_code}}" name="product_code[]" id="product_code-{{$i}}" readonly></td>
                            <td>
                                <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid-{{$i}}" disabled>
                                    <option value="">-- Warehouse --</option>
                                    @foreach ($warehouses as $row)
                                        <option value="{{ $row->id }}" {{ $row->id == $item->warehouse_id? 'selected' : '' }}>
                                            {{ $row->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td colspan="5">
                                <input type="text" class="form-control projectstock" value="{{ $item->project_name }}" id="projectstocktext-{{$i}}" placeholder="Search Project By Name" disabled>

                                <div class="mt-1">
                                    <select id="stock_purchase_class-{{$i}}" name="purchase_class_budget[]" class="custom-select round purchase-class" data-placeholder="Select a Non-Project Class" disabled>
                                        <option value=""></option>

                                        @foreach ($purchaseClasses as $pc)
                                            <option value="{{ $pc->id }}"
                                                    @if($item->purchaseClassBudget)
                                                        @if($item->purchaseClassBudget->purchase_class_id == $pc->id) selected @endif
                                                    @endif
                                            >
                                                {{ $pc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="{{ $item->itemproject_id ? $item->itemproject_id : '0' }}" id="projectstockval-{{$i}}">
                                <input type="hidden" class="prod_id" name="product_id[]" id="product_id-{{$i}}" value="{{$item->product_id}}">
                                <input type="hidden" class="supplier_product_id" name="supplier_product_id[]" value="{{$item->supplier_product_id}}" id="supplier_product_id-{{$i}}">
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
                    {{-- <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button> --}}
                </td>
                <td colspan="7"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="7" align="right"><b>{{trans('general.total_tax')}}</b></td>                   
                <td align="left" colspan="2"><span id="invtax" class="lightMode">0</span></td>
            </tr>
            <tr class="bg-white">
                <td colspan="7" align="right"><b>Inventory Total</b></td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="stock_grandttl" value="0.00" id="stock_grandttl" readonly>
                    <input type="hidden" name="stock_subttl" value="0.00" id="stock_subttl">
                    <input type="hidden" name="stock_tax" value="0.00" id="stock_tax">
                </td>
            </tr>
        </tbody>
    </table>
</div>
