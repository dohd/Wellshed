<table id="boqTbl" class="table-responsive pb-5 tfr my_stripe_single">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th style="width: 4%;">#No</th>
            <th style="width: 12%;">Description (BOQ)</th>
            <th style="width: 12%;">Description (MTO)</th>
            <th style="width: 7%;">UoM (BOQ)</th>
            <th style="width: 7%;">UoM (MTO)</th>
            <th style="width: 7%;">Qty (BOQ)</th>
            <th style="width: 8%;">Qty (MTO)</th>
            <th style="width: 8%;">Rate (BOQ)</th>
            <th style="width: 8%;">Rate (MTO)</th>
            <th style="width: 10%;">{{ trans('general.rate') }} (VAT Inc MTO)</th>
            <th style="width: 10%;">AMT (MTO | BoQ)</th>
            <th style="width: 7%;">Action</th>
        </tr>
    </thead>
    <tbody>
        <!-- Product Row -->
        <tr id="productRow">
            <td><input type="text" class="form-control" name="numbering[]" id="numbering-p0" value=""></td>
            <td>
                <textarea name="description[]" id="description-p0" cols="35" rows="2" class="form-control" placeholder="Description" readonly></textarea>
            </td>
            {{-- <td></td> --}}
            <td>
                <textarea name="product_name[]" id="name-p0" cols="35" rows="2" class="form-control pname" placeholder="{{trans('general.enter_product')}}" style="font-weight: bold; color:blue;"></textarea>
            </td>
            <td><input type="text" name="uom[]" id="uom-p0" class="form-control" readonly></td>
            <td><input type="text" name="unit[]" id="unit-p0" class="form-control" readonly></td>
            <td><input type="number" class="form-control new_qty" name="new_qty[]" id="new_qty-p0" step="0.1" readonly></td>
            <td><input type="number" class="form-control qty" name="qty[]" id="qty-p0" step="0.1" required></td>
            <td><input type="text" class="form-control boq_rate" name="boq_rate[]" id="boq_rate-p0" readonly></td>
            <td><input type="text" class="form-control rate" name="rate[]" id="rate-p0" required></td>
            <td>
                <div class="row no-gutters">
                    <div class="col-6">
                        <input type="text" class="form-control price" name="product_subtotal[]" value="0" id="price-p0" readonly>
                    </div>
                    <div class="col-6">
                        <select class="custom-select tax_rate" name="tax_rate[]" id="taxrate-p0">
                            @foreach ($additionals as $add_item)
                                <option value="{{ +$add_item->value }}">
                                    {{ $add_item->value == 0? 'OFF' : (+$add_item->value) . '%' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </td>
            <td class='text-center'>
                <span class="amount" id="amount-p0">0</span>&nbsp;&nbsp;
                {{-- <span class="lineprofit text-info" id="lineprofit-p0">0%</span> --}}
            </td>
            <td class="text-center">
                @include('focus.boqs.partials.action-dropdown')
            </td>
            <input type="hidden" name="product_id[]" value="0" id="productid-p0">
            <input type="hidden" name="unit_id[]" value="0" id="unit_id-p0">
            <input type="hidden" name="boq_amount[]" value="0" id="boq_amount-p0">
            <input type="hidden" class="total_amount" name="amount[]" value="0" id="total_amount-p0">
            <input type="hidden" class="index" name="row_index[]" value="0" id="rowindex-p0">
            <input type="hidden" name="type[]" value="product" id="type-p0">
            <input type="hidden" name="id[]" value="0">
            <input type="hidden" name="misc[]" value="0" id="misc-p0">
        </tr>
        <!-- End Product Row -->
        
        <!-- Title Row -->
        <tr id="titleRow">
            <td><input type="text" class="form-control" name="numbering[]" id="numbering-t1" value="" style="font-weight: bold; color:green;"></td>
            <td colspan="10">
                <input type="text"  class="form-control" name="description[]" placeholder="Enter Title Or Heading" id="description-t1" style="font-weight: bold; color:green;" required>
            </td>
            <td class="text-center">
                @include('focus.boqs.partials.action-dropdown')
            </td>
            <input type="hidden" name="misc[]" value="0" id="misc-t1">
            <input type="hidden" name="product_id[]" value="0" id="productid-t1">
            <input type="hidden" name="unit_id[]" value="0" id="unit_id-t1">
            <input type="hidden" name="uom[]">
            <input type="hidden" name="unit[]">
            <input type="hidden" name="product_name[]">
            <input type="hidden" name="qty[]" value="0">
            <input type="hidden" name="boq_rate[]" value="0">
            <input type="hidden" name="boq_amount[]" value="0">
            <input type="hidden" name="product_subtotal[]" value="0">
            <input type="hidden" name="tax_rate[]" value="0">
            <input type="hidden" name="rate[]" value="0">
            <input type="hidden" name="new_qty[]" value="0">
            <input type="hidden" name="amount[]" value="0" id="total_amount-t1">
            <input type="hidden" class="index" name="row_index[]" value="0" id="rowindex-t1">
            <input type="hidden" name="type[]" value="title" id="type-t1">
            <input type="hidden" name="id[]" value="0">
        </tr>
        <!-- End Title Row -->

        <!-- Edit Quote or PI -->
        @if (isset($boq))
            @foreach ($boqs->items as $k => $item)
                @if ($item->type == "product")
                    <!-- Product Row -->
                    <tr class="">
                        <td><input type="text" class="form-control" name="numbering[]" id="numbering-p{{$k}}" value="{{$item->numbering}}"></td>
                        <td>
                            <textarea name="description[]" id="description-p{{$k}}" cols="35" rows="10" class="form-control {{ !$item->misc ?: 'invisible' }}" placeholder="Description" readonly>{{@$item->description}}</textarea>
                        </td>
                        {{-- <td>
                            @if($item->product_id > 0)
                                <i class="fa fa-check text-success"></i>
                            @else
                                <i class="fa fa-times text-danger"></i>
                            @endif
                        </td>                         --}}
                        <td>
                            <textarea name="product_name[]" id="name-p{{$k}}" cols="35" rows="2" class="form-control pname" placeholder="{{trans('general.enter_product')}}" style="font-weight: bold; {{ $item->is_imported == 0 ? 'color: blue;' : '' }}" {{ $item->is_imported == 1 ? 'readonly' : '' }}>{{@$item->product->name}}</textarea>
                        </td>
                        <td><input type="text" name="uom[]" value="{{$item->uom}}" id="uom-p{{$k}}" class="form-control {{ !$item->misc ?: 'invisible' }}" readonly></td>
                        <td><input type="text" class="form-control new_qty {{ !$item->misc ?: 'invisible' }}" name="new_qty[]" value="{{number_format($item->new_qty, 2)}}" id="new_qty-p{{$k}}" step="0.1" required readonly></td>
                        <td><input type="text" name="unit[]" id="unit-p{{$k}}" value="{{$item->unit}}" class="form-control" readonly></td>
                        <td><input type="text" class="form-control boq_rate" name="boq_rate[]" id="boq_rate-p0" value="{{number_format($item->boq_rate, 2)}}" readonly></td>
                        <td><input type="text" class="form-control qty" value="{{number_format($item->qty, 2)}}" name="qty[]" id="qty-p{{$k}}" step="0.1" readonly></td>
                        <td><input type="text" class="form-control rate" value="{{$item->rate}}" name="rate[]" id="rate-p{{$k}}" readonly></td>
                        <td>
                            <div class="row no-gutters">
                                <div class="col-6">
                                    <input type="text" class="form-control price" name="product_subtotal[]" value="{{ number_format($item->product_subtotal, 4) }}" id="price-p{{$k}}" readonly>
                                </div>
                                <div class="col-6">
                                    <select class="custom-select tax_rate" name="tax_rate[]" id="taxrate-p{{$k}}">
                                        @foreach ($additionals as $add_item)
                                            <option value="{{ $add_item->value }}" {{ $add_item->value == $item->tax_rate? 'selected' : ''}}>
                                                {{ $add_item->value == 0? 'OFF' : (+$add_item->value) . '%' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td class='text-center'>
                            <span class="amount" id="amount-p{{$k}}">{{$item->is_imported == 0 ? numberFormat($item->amount) : numberFormat($item->boq_amount) }}</span>&nbsp;&nbsp;
                            {{-- <span class="lineprofit text-info" id="lineprofit-p{{$k}}">0%</span> --}}
                        </td>
                        <td class="text-center">
                            @include('focus.boqs.partials.action-dropdown')
                        </td>
                        <input type="hidden" name="misc[]" value="{{ $item->misc }}" id="misc-p{{$k}}">
                        <input type="hidden" name="product_id[]" value="{{$item->product_id}}" id="productid-p{{$k}}">
                        <input type="hidden" name="unit_id[]" value="{{$item->unit_id}}" id="unit_id-p{{$k}}">
                        <input type="hidden" class="index" name="row_index[]" value="{{ $item->row_index }}" id="rowindex-p{{$k}}">
                        <input type="hidden" class="total_amount" name="amount[]" value="{{$item->amount}}" id="total_amount-p{{$k}}">
                        <input type="hidden" class="" name="boq_amount[]" value="{{$item->boq_amount}}" id="boq_amount-p{{$k}}">
                        <input type="hidden" name="type[]" value="{{$item->type}}" id="type-p{{$k}}">
                        <input type="hidden" name="id[]" value="{{$item->id}}">
                    </tr>
                @else
                    <!-- Title Row  -->
                    <tr>
                        <td><input type="text" class="form-control" name="numbering[]" id="numbering-t{{$k}}" value="{{$item->numbering}}" style="font-weight: bold; {{ $item->is_imported == 0 ? 'color: green;' : '' }}"></td>
                        <td colspan="10">
                            <input type="text" value="{{$item->description}}" class="form-control" name="description[]" placeholder="Enter Title Or Heading" id="description-t{{$k}}" style="font-weight: bold; {{ $item->is_imported == 0 ? 'color: green;' : '' }}" {{ $item->is_imported == 1 ? 'readonly' : '' }} required>
                        </td>
                        <td>
                            {{numberFormat($item->boq_amount)}}
                        </td>
                        <td class="text-center">
                            @include('focus.boqs.partials.action-dropdown')
                        </td>
                        <input type="hidden" name="misc[]" value="{{ $item->misc }}" id="misc-t{{$k}}">
                        <input type="hidden" name="product_id[]" value="0" id="productid-t{{$k}}">
                        <input type="hidden" name="unit_id[]" value="0" id="unit_id-t{{$k}}">
                        <input type="hidden" name="uom[]">
                        <input type="hidden" name="unit[]">
                        <input type="hidden" name="product_name[]">
                        <input type="hidden" name="qty[]" value="0">
                        <input type="hidden" name="boq_rate[]" value="0">
                        <input type="hidden" name="boq_amount[]" value="{{$item->boq_amount}}">
                        <input type="hidden" name="rate[]" value="0">
                        <input type="hidden" name="product_subtotal[]" value="0">
                        <input type="hidden" name="tax_rate[]" value="0">
                        <input type="hidden" name="new_qty[]" value="0">
                        <input type="hidden" name="amount[]" value="{{$item->amount}}" id="total_amount-t{{$k}}">
                        <input type="hidden" class="index" name="row_index[]" value="{{ $item->row_index }}" id="rowindex-t{{$k}}">
                        <input type="hidden" name="type[]" value="{{$item->type}}" id="type-t{{$k}}">
                        <input type="hidden" name="id[]" value="{{$item->id}}">
                    </tr>
                @endif
            @endforeach
        @endif        
    </tbody>
</table>