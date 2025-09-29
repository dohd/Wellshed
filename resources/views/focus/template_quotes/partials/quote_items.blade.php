<table id="quoteTbl" class="table-responsive pb-5 tfr my_stripe_single">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th width="5%" class="text-center">#No</th>
            <th width="20%" class="text-center">Product</th>
            <th width="6%" class="text-center">UoM</th>
            <th width="7%" class="text-center">Est. Qty</th>
            <th width="10%" class="text-center">Est. Buy Price</th>
            <th width="7%" class="text-center">Qty</th>
            <th width="10%" class="text-center">{{ trans('general.rate') }}</th>
            <th width="12%" class="text-center">{{ trans('general.rate') }} (VAT Inc)</th>
            <th width="12%" class="text-center">{{ trans('general.amount') }}</th>
            <th width="5%" class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        <!-- Product Row -->
        <tr id="productRow">
            <td><input type="text" class="form-control" name="numbering[]" id="numbering-p0" value=""></td>
            <td>
                <textarea name="product_name[]" id="name-p0" cols="35" rows="2" class="form-control pname" placeholder="{{trans('general.enter_product')}}" required></textarea>
            </td>
            <td><input type="text" name="unit[]" id="unit-p0" class="form-control"></td>
            <td ><input type="number" class="form-control estqty" name="estimate_qty[]" id="estqty-p0" step="0.1" style="border:solid #f5a8a2;" required></td>  
            <td ><input type="text" class="form-control buyprice" name="buy_price[]" id="buyprice-p0"  style="border:solid #f5a8a2;" readonly></td>  
            <td><input type="number" class="form-control qty" name="product_qty[]" id="qty-p0" step="0.1" required></td>
            <td><input type="text" class="form-control rate" name="product_subtotal[]" id="rate-p0" required></td>
            <td>
                <div class="row no-gutters">
                    <div class="col-6">
                        <input type="text" class="form-control price" name="product_price[]" id="price-p0" readonly>
                    </div>
                    <div class="col-6">
                        <select class="custom-select tax_rate" name="tax_rate[]" id="taxrate-p0">
                            @foreach ($additionals as $item)
                                <option value="{{ +$item->value }}">{{ $item->value == 0? 'OFF' : (+$item->value) . '%' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </td>
            <td class='text-center'>
                <span class="amount" id="amount-p0">0</span>&nbsp;&nbsp;
                <span class="lineprofit text-info" id="lineprofit-p0">0%</span>
            </td>
            <td class="text-center">
                @include('focus.quotes.partials.action-dropdown')
            </td>
            <input type="hidden" name="misc[]" value="0" id="misc-p0">
            <input type="hidden" name="product_id[]" value="0" id="productid-p0">
            <input type="hidden" class="index" name="row_index[]" value="0" id="rowindex-p0">
            <input type="hidden" name="a_type[]" value="1" id="atype-p0">
            <input type="hidden" name="id[]" value="0">
        </tr>
        <!-- End Product Row -->
        
        <!-- Title Row -->
        <tr id="titleRow">
            <td><input type="text" class="form-control" name="numbering[]" id="numbering-t1" value="" style="font-weight: bold;"></td>
            <td colspan="8">
                <input type="text"  class="form-control" name="product_name[]" placeholder="Enter Title Or Heading" id="name-t1" style="font-weight: bold;" required>
            </td>
            <td class="text-center">
                @include('focus.quotes.partials.action-dropdown')
            </td>
            <input type="hidden" name="misc[]" value="0" id="misc-t1">
            <input type="hidden" name="product_id[]" value="0" id="productid-t1">
            <input type="hidden" name="unit[]">
            <input type="hidden" name="product_qty[]" value="0">
            <input type="hidden" name="product_price[]" value="0">
            <input type="hidden" name="tax_rate[]" value="0">
            <input type="hidden" name="product_subtotal[]" value="0">
            <input type="hidden" name="estimate_qty[]" value="0">
            <input type="hidden" name="buy_price[]" value="0">
            <input type="hidden" class="index" name="row_index[]" value="0" id="rowindex-t1">
            <input type="hidden" name="a_type[]" value="2" id="atype-t1">
            <input type="hidden" name="id[]" value="0">
        </tr>
        <!-- End Title Row -->

        <!-- Edit Quote or PI -->
        @if (isset($quote))
            @foreach ($quote->products as $k => $item)
                @if ($item->a_type == 1)
                    <!-- Product Row -->
                    @if ($item->misc == 0)
                    <tr class="{{ !$item->misc ?: 'misc' }}" style = "{{$item->misc == 1 ? "background-color:rgba(229, 241, 101, 0.4);" : '' }}">
                        <td><input type="text" class="form-control" name="numbering[]" value="{{ $item->numbering }}" id="numbering-p{{$k}}"></td>
                        <td>
                            <textarea name="product_name[]" id="name-p{{ $k }}" cols="35" rows="2" class="form-control pname" placeholder="{{trans('general.enter_product')}}" required>{{ $item->product_name }}</textarea>
                        </td>
                        <td><input type="text" name="unit[]" id="unit-p{{ $k }}" value="{{ $item->unit }}" class="form-control"></td>
                        <td><input type="number" class="form-control estqty" name="estimate_qty[]" value="{{ number_format($item->estimate_qty, 1) }}" id="estqty-p{{$k}}" step="0.1" style="border:solid #f5a8a2;" required></td>  
                        <td><input type="text" class="form-control buyprice" name="buy_price[]" value="{{ number_format($item->buy_price, 4) }}" id="buyprice-p{{$k}}" style="border:solid #f5a8a2;" readonly></td>          
                        <td><input type="number" class="form-control qty {{ !$item->misc ?: 'invisible' }}" name="product_qty[]" value="{{ number_format($item->product_qty, 1) }}" id="qty-p{{$k}}" step="0.1" required></td>
                        <td>
                            <input type="text" class="form-control rate {{ !$item->misc ?: 'invisible' }}" name="product_subtotal[]" value="{{ number_format($item->product_subtotal, 4) }}" id="rate-p{{$k}}" required>
                        </td>
                        <td>
                            <div class="row no-gutters">
                                <div class="col-6">
                                    <input type="text" class="form-control price {{ !$item->misc ?: 'invisible' }}"  name="product_price[]" value="{{ number_format($item->product_price, 4) }}" id="price-p{{$k}}" readonly>
                                </div>
                                <div class="col-6">
                                    <select class="custom-select tax_rate {{ !$item->misc ?: 'invisible' }}" name="tax_rate[]" id="taxrate-p{{$k}}">
                                        @foreach ($additionals as $add_item)
                                            <option value="{{ +$add_item->value }}" {{ $add_item->value == $item->tax_rate? 'selected' : ''}}>
                                                {{ $add_item->value == 0? 'OFF' : (+$add_item->value) . '%' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td class="text-center {{ !$item->misc ?: 'invisible' }}">
                            <span class="amount" id="amount-p{{$k}}">{{ number_format($item->product_amount, 4) }}</span>&nbsp;&nbsp;
                            <span class="lineprofit text-info" id="lineprofit-p{{$k}}">0%</span>
                        </td>
                        <td class="text-center">
                            @include('focus.quotes.partials.action-dropdown')
                        </td>
                        <input type="hidden" name="misc[]" value="{{ $item->misc }}" id="misc-p{{$k}}">
                        <input type="hidden" name="product_id[]" value="{{ $item->product_id }}" id="productid-p{{$k}}">
                        <input type="hidden" class="index" name="row_index[]" value="{{ $item->row_index }}" id="rowindex-p{{$k}}">
                        <input type="hidden" name="a_type[]" value="1" id="atype-p{{$k}}">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                    </tr>
                    @else
                    <tr class="{{ !$item->misc ?: 'misc' }}" style = "{{$item->misc == 1 ? "background-color:rgba(229, 241, 101, 0.4);" : '' }}">
                        <td><input type="text" class="form-control" name="numbering[]" value="{{ $item->numbering }}" id="numbering-p{{$k}}"></td>
                        <td>
                            <textarea name="product_name[]" id="name-p{{ $k }}" cols="35" rows="2" class="form-control pname" placeholder="{{trans('general.enter_product')}}" required>{{ $item->product_name }}</textarea>
                        </td>
                        <td><input type="text" name="unit[]" id="unit-p{{ $k }}" value="{{ $item->unit }}" class="form-control"></td>
                        <td><input type="number" class="form-control estqty" name="estimate_qty[]" value="{{ number_format($item->estimate_qty, 1) }}" id="estqty-p{{$k}}" step="0.1" style="border:solid #f5a8a2;" required></td>  
                        <td><input type="text" class="form-control buyprice" name="buy_price[]" value="{{ number_format($item->buy_price, 4) }}" id="buyprice-p{{$k}}" style="border:solid #f5a8a2;" readonly></td>          
                        <td><input type="number" class="form-control qty {{ !$item->misc ?: 'invisible' }}" name="product_qty[]" value="{{ number_format($item->product_qty, 1) }}" id="qty-p{{$k}}" step="0.1" required></td>
                        <td>
                            <input type="text" class="form-control rate {{ !$item->misc ?: 'invisible' }}" name="product_subtotal[]" value="{{ number_format($item->product_subtotal, 4) }}" id="rate-p{{$k}}" required>
                        </td>
                        <td>
                            <div class="row no-gutters">
                                <div class="col-6">
                                    <input type="text" class="form-control price "  name="product_price[]" value="{{ number_format($item->product_price, 4) }}" id="price-p{{$k}}" readonly>
                                </div>
                                <div class="col-6">
                                    <select class="custom-select tax_rate " name="tax_rate[]" id="taxrate-p{{$k}}">
                                        @foreach ($additionals as $add_item)
                                            <option value="{{ +$add_item->value }}" {{ $add_item->value == $item->tax_rate? 'selected' : ''}}>
                                                {{ $add_item->value == 0? 'OFF' : (+$add_item->value) . '%' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td class="text-center ">
                            <span class="amount" id="amount-p{{$k}}">{{ number_format($item->product_amount, 4) }}</span>&nbsp;&nbsp;
                            <span class="lineprofit text-info" id="lineprofit-p{{$k}}">0%</span>
                        </td>
                        <td class="text-center">
                            @include('focus.quotes.partials.action-dropdown')
                        </td>
                        <input type="hidden" name="misc[]" value="{{ $item->misc }}" id="misc-p{{$k}}">
                        <input type="hidden" name="product_id[]" value="{{ $item->product_id }}" id="productid-p{{$k}}">
                        <input type="hidden" class="index" name="row_index[]" value="{{ $item->row_index }}" id="rowindex-p{{$k}}">
                        <input type="hidden" name="a_type[]" value="1" id="atype-p{{$k}}">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                    </tr>
                    @endif
                @else
                    <!-- Title Row  -->
                    <tr>
                        <td><input type="text" class="form-control" name="numbering[]" value="{{ $item->numbering }}" style="font-weight: bold;" id="numbering-t{{$k}}"></td>
                        <td colspan="8">
                            <input type="text"  class="form-control" name="product_name[]" value="{{ $item->product_name }}" style="font-weight: bold;" placeholder="Enter Title Or Heading" id="name-t{{$k}}" required>
                        </td>
                        <td class="text-center">
                            @include('focus.quotes.partials.action-dropdown')
                        </td>
                        <input type="hidden" name="misc[]" value="{{ $item->misc }}" id="misc-t{{$k}}">
                        <input type="hidden" name="product_id[]" value="0" id="productid-t{{$k}}">
                        <input type="hidden" name="unit[]">
                        <input type="hidden" name="product_qty[]" value="0">
                        <input type="hidden" name="product_price[]" value="0">
                        <input type="hidden" name="tax_rate[]" value="0">
                        <input type="hidden" name="product_subtotal[]" value="0">
                        <input type="hidden" name="estimate_qty[]" value="0">
                        <input type="hidden" name="buy_price[]" value="0">
                        <input type="hidden" class="index" name="row_index[]" value="{{ $item->row_index }}" id="rowindex-t{{$k}}">
                        <input type="hidden" name="a_type[]" value="2" id="atype-t{{$k}}">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                    </tr>
                @endif
            @endforeach
        @endif        
    </tbody>
</table>