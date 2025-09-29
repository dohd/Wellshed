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
            <th width="10%" class="text-center">{{ trans('general.rate') }} (VAT Inc)</th>
            <th width="12%" class="text-center">{{ trans('general.amount') }}</th>
            <th width="5%" class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        <!-- product row template-->
        <tr id="productRow">
            <td><input type="text" class="form-control" name="numbering[]" id="numbering-p0" value=""></td>
            <td>
                <textarea name="product_name[]" id="name-p0" cols="35" rows="2" class="form-control" placeholder="{{trans('general.enter_product')}}" required></textarea>
            </td>
            {{-- <td><input type="text" name="unit[]" id="unit-p0" class="form-control"></td> --}}
            <td><select name="unit[]" id="unit-p0" class="form-control unit"></select></td>
            <td><input type="number" class="form-control estqty" name="estimate_qty[]" id="estqty-p0" step="0.1" required></td>  
            <td><input type="text" class="form-control buyprice" name="buy_price[]" id="buyprice-p0" readonly></td>  
            <td><input type="number" class="form-control qty" name="product_qty[]" id="qty-p0" step="0.1" required></td>
            <td><input type="text" class="form-control rate" name="product_subtotal[]" id="rate-p0" required></td>
            <td><input type="text" class="form-control price" name="product_price[]" id="price-p0" readonly></td>
            <td class='text-center'>
                {{-- <span class="miscexpense d-none">Miscellaneous / Expense</span> --}}
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
        
        <!-- title row template-->
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
            <input type="hidden" name="product_subtotal[]" value="0">
            <input type="hidden" name="estimate_qty[]" value="0">
            <input type="hidden" name="buy_price[]" value="0">
            <input type="hidden" class="index" name="row_index[]" value="0" id="rowindex-t1">
            <input type="hidden" name="a_type[]" value="2" id="atype-t1">
            <input type="hidden" name="id[]" value="0">
        </tr>

        <!-- edit quote or pi-->
        @if (isset($quote))
        {{ browserlog($quote->products) }}
            @foreach ($quote->products as $k => $item)
                @if ($item->a_type == 1)
                    <!-- product -->
                    <tr class="{{ !$item->misc ?: 'misc' }}">
                        <td><input type="text" class="form-control" name="numbering[]" value="{{ $item->numbering }}" id="numbering-p{{$k}}"></td>
                        <td>
                            <textarea name="product_name[]" id="name-p{{ $k }}" cols="35" rows="2" class="form-control pname" placeholder="{{trans('general.enter_product')}}" required>{{ $item->product_name }}</textarea>
                        </td>
                        {{-- <td><input type="text" name="unit[]" id="unit-p{{ $k }}" value="{{ $item->unit }}" class="form-control"></td> --}}
                        <td>
                            @php
                                $units = @$item->variation->product->units ?: [];
                                $variation = @$item->variation;
                            @endphp
                            <select name="unit[]" id="unit-p{{ $k }}" class="form-control unit">
                                @if ($variation)
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->code }}" purchase_price="{{ $unit->base_ratio * $variation->purchase_price }}" product_rate="{{ $unit->base_ratio * $variation->price }}" {{ $unit->code == $item->unit? 'selected' : '' }}>
                                            {{ $unit->code }}
                                        </option>
                                    @endforeach
                                @else
                                     @foreach($productvariables as $i)
                                        @if ($i->unit_type == 'base')
                                            <option value="{{ $i->code }}" >
                                                {{ $i->code }}
                                            </option>    
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </td>
                                  
                        <td><input type="number" class="form-control estqty" name="estimate_qty[]" value="{{ $item->estimate_qty }}" id="estqty-p{{$k}}" step="0.1" required></td>  
                        <td><input type="text" class="form-control buyprice" name="buy_price[]" value="{{ numberFormat($item->buy_price) }}" id="buyprice-p{{$k}}" readonly></td>          
                        <td><input type="number" step="0.1" class="form-control qty {{ !$item->misc ?: 'invisible' }}" name="product_qty[]" value="{{ $item->product_qty }}" id="qty-p{{$k}}" required></td>
                        <td><input type="text" class="form-control rate {{ !$item->misc ?: 'invisible' }}" name="product_subtotal[]" value="{{ numberFormat($item->product_subtotal) }}" id="rate-p{{$k}}" required></td>
                        <td><input type="text" class="form-control price {{ !$item->misc ?: 'invisible' }}" name="product_price[]" value="{{ numberFormat($item->product_price) }}" id="price-p{{$k}}" readonly></td>
                        <td class="text-center {{ !$item->misc ?: 'invisible' }}">
                            <span class="amount" id="amount-p{{$k}}">0</span>&nbsp;&nbsp;
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
                    <!-- group title  -->
                    <tr>
                        <td><input type="text" class="form-control" name="numbering[]" value="{{ $item->numbering }}" id="numbering-t{{$k}}"></td>
                        <td colspan="8">
                            <input type="text"  class="form-control" name="product_name[]" value="{{ $item->product_name }}" placeholder="Enter Title Or Heading" id="name-t{{$k}}" required>
                        </td>
                        <td class="text-center">
                            @include('focus.quotes.partials.action-dropdown')
                        </td>
                        <input type="hidden" name="misc[]" value="{{ $item->misc }}" id="misc-t{{$k}}">
                        <input type="hidden" name="product_id[]" value="0" id="productid-t{{$k}}">
                        <input type="hidden" name="unit[]">
                        <input type="hidden" name="product_qty[]" value="0">
                        <input type="hidden" name="product_price[]" value="0">
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