<table id="allowanceTbl" class="table-responsive pb-5 tfr my_stripe_single">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th width="20%" class="text-center">#No</th>
            <th width="20%" class="text-center">Name</th>
            <th width="20%" class="text-center">Type</th>
            <th width="20%" class="text-center">Is Taxable</th>
            <th width="20%" class="text-center">Enter AMount</th>
           
        </tr>
    </thead>
    <tbody>
        <!-- product row template-->
        @php
            $i=0;
        @endphp
        @foreach ( $allowances as $allowance )
        @php
        $i++;
          @endphp
            
     
        <tr id="productRow">
            <td class="text-center" >{{ $i }}</td>
            <td class="text-center" >{{ $allowance->name }}</td>
            <td class="text-center" >{{ $allowance->type }}</td>
            <td class="text-center" >{{ $allowance->is_taxable }}</td>  
            <td ><input type="text" class="form-control amount" name="amount[]" id="amount-{{ $allowance->id }}"></td>  
            <input type="hidden" name="allowance_deduction_category_id[]" value="{{ $allowance->id }}" id="allowance_deduction_category_id-{{ $allowance->id }}">
            <input type="hidden" name="is_taxable[]" class="is_taxable" value="{{ $allowance->is_taxable }}" id="is_taxable-{{ $allowance->id }}">
            <input type="hidden" name="type[]" class="type" value="{{ $allowance->type }}" id="type-{{ $allowance->id }}">
        
        </tr>
        @endforeach
        
      

        <!-- edit quote or pi-->
        @if (isset($quote))
            @foreach ($quote->products as $k => $item)
                @if ($item->a_type == 1)
                    <!-- product -->
                    <tr>
                        <td><input type="text" class="form-control" name="numbering[]" value="{{ $item->numbering }}" id="numbering-p{{$k}}" required></td>
                        <td>
                            <textarea name="product_name[]" id="name-p{{ $k }}" cols="35" rows="2" class="form-control pname" placeholder="{{trans('general.enter_product')}}" required>{{ $item->product_name }}</textarea>
                        </td>
                        <td><input type="text" name="unit[]" id="unit-p{{ $k }}" value="{{ $item->unit }}" class="form-control"></td>
                                  
                        <td><input type="number" class="form-control estqty" name="estimate_qty[]" value="{{ number_format($item->estimate_qty, 1) }}" id="estqty-p{{$k}}" step="0.1" required></td>  
                        <td><input type="text" class="form-control buyprice" name="buy_price[]" value="{{ numberFormat($item->buy_price) }}" id="buyprice-p{{$k}}" required></td>          
                        <td><input type="number" class="form-control qty {{ !$item->misc ?: 'invisible' }}" name="product_qty[]" value="{{ number_format($item->product_qty, 1) }}" id="qty-p{{$k}}" step="0.1" required></td>
                        <td><input type="text" class="form-control rate {{ !$item->misc ?: 'invisible' }}" name="product_subtotal[]" value="{{ numberFormat($item->product_subtotal) }}" id="rate-p{{$k}}" required></td>
                        <td><input type="text" class="form-control price {{ !$item->misc ?: 'invisible' }}" name="product_price[]" value="{{ numberFormat($item->product_price) }}" id="price-p{{$k}}" readonly></td>
                        <td class="text-center {{ !$item->misc ?: 'invisible' }}">
                            <span class="amount" id="amount-p{{$k}}">0</span>&nbsp;&nbsp;
                            <span class="lineprofit text-info" id="lineprofit-p{{$k}}">0%</span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Action
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item up" href="javascript:void(0);">Up</a>
                                    <a class="dropdown-item down" href="javascript:void(0);">Down</a>
                                    <a class="dropdown-item text-danger remv" href="javascript:void(0);">Remove</a>
                                </div>
                            </div> 
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
                        <td><input type="text" class="form-control" name="numbering[]" value="{{ $item->numbering }}" id="numbering-t{{$k}}" required></td>
                        <td colspan="8">
                            <input type="text"  class="form-control" name="product_name[]" value="{{ $item->product_name }}" placeholder="Enter Title Or Heading" id="name-t{{$k}}" required>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Action
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item up" href="javascript:">Up</a>
                                    <a class="dropdown-item down" href="javascript:">Down</a>
                                    <a class="dropdown-item text-danger remv" href="javascript:">Remove</a>
                                </div>
                            </div> 
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