<fieldset class="border p-1 mb-3">
    <legend class="w-auto float-none h5">Order Items</legend>
    <div class="row mb-1">
        <div class="col-2">
            <select class="custom-select" name="tax_id" id="tax-id" autocomplete="off" required>
                <option value="">-- VAT Rate --</option>
                @foreach ($additionals as $item)
                    <option value="{{ +$item->value }}">{{ $item->value == 0 ? 'OFF' : +$item->value . '%' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-2">
            {{ Form::text('', null, ['class' => 'form-control', 'id' => 'orderItemsPerc', 'placeholder' => '% Value']) }}
        </div>
    </div>
    <div class="table-responsive mb-2 pb-2" style="max-height: 80vh">                            
        <table id="productsTbl" class="table tfr my_stripe_single pb-2 text-center">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white">
                    <th>&nbsp;</th>
                    <th>#</th>
                    <th>Item Description</th>
                    <th>Item Code</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                    <th>Value Balance</th>
                    <th width="15%" style="min-width: 200px;">VAT</th>
                    <th width="5%">% Value</th>
                    <th width="5%">Amount Valued</th>
                </tr>
            </thead>
            <tbody>
                <!-- Product Row Template -->
                <tr class="product-templ">
                    <td>@include('focus.job_valuations.partials.macro', ['elem' => 'dropdown'])</td>
                    <td><span class="num"></span></td>
                    <td class="text-left"><span class="descr"></span></td>
                    <td class="item-code"></td>
                    <td><span class="unit"></span></td>
                    <td><span class="qty"></span></td>         
                    <td><span class="price"></span></td>   
                    <td><span class="amount"></span></td>   
                    <td><span class="valued-bal"></span></td>   
                    <td>@include('focus.job_valuations.partials.macro', ['elem' => 'vat'])</td>
                    <td><input type="text" class="form-control perc-val" name="perc_valuated[]"></td>                
                    <td><input type="text" class="form-control amount-val" name="total_valuated[]"></td>
                    <input type="hidden" name="id[]" class="item-id">
                    <input type="hidden" name="numbering[]" class="num-inp">
                    <input type="hidden" name="row_type[]" class="type-inp">
                    <input type="hidden" name="row_index[]" class="index-inp">
                    <input type="hidden" name="product_name[]" class="descr-inp">
                    <input type="hidden" name="unit[]" class="unit-inp">
                    <input type="hidden" name="product_qty[]" class="qty-inp">
                    <input type="hidden" name="product_price[]" class="price-inp">
                    <input type="hidden" name="product_subtotal[]" class="subtotal-inp">
                    <input type="hidden" name="product_amount[]" class="amount-inp">
                    <input type="hidden" name="product_valued_bal[]" class="valued-bal-inp">
                    <input type="hidden" name="productvar_id[]" class="prodvar-id">
                    <input type="hidden" name="quote_item_id[]" class="quoteitem-id">
                </tr>
                <!-- Title Row Template -->
                <tr class="title-templ">
                    <td>@include('focus.job_valuations.partials.macro', ['elem' => 'dropdown'])</td>
                    <td><span class="num font-weight-bold"></span></td>
                    <td colspan="10" class="text-left font-weight-bold"><span class="descr"></span></td>
                    <input type="hidden" name="id[]" class="item-id">
                    <input type="hidden" name="quote_item_id[]" class="quoteitem-id">
                    <input type="hidden" name="numbering[]" class="num-inp">
                    <input type="hidden" name="row_type[]" class="type-inp">
                    <input type="hidden" name="row_index[]" class="index-inp">
                    <input type="hidden" name="product_name[]" class="descr-inp">
                    @php 
                        $keys = [
                            'unit', 'product_qty', 'tax_rate', 'product_tax', 'product_price', 'product_subtotal', 
                            'product_amount', 'product_valued_bal', 'productvar_id', 'perc_valuated', 'total_valuated',
                        ];
                    @endphp
                    @foreach($keys as $key)
                        <input type="hidden" name="{{ $key }}[]">
                    @endforeach
                </tr>

                <!-- Product-Add Row Template -->
                <tr class="product-templ-add">
                    <td>@include('focus.job_valuations.partials.macro', ['elem' => 'dropdown'])</td>
                    <td><input type="text" name="numbering[]" class="form-control num-inp"></td>
                    <td><textarea rows="1" name="product_name[]" class="form-control descr-inp"></textarea></td>
                    <td class="item-code"></td>
                    <td><span class="unit"></span></td>
                    <td><input type="text" name="product_qty[]" class="form-control qty-inp"></td>         
                    <td><input type="text" name="product_price[]" class="form-control price-inp"></td>   
                    <td><span class="amount"></span></td>   
                    <td><span class="valued-bal"></span></td>   
                    <td>@include('focus.job_valuations.partials.macro', ['elem' => 'vat'])</td>
                    <td><input type="text" class="form-control perc-val" name="perc_valuated[]"></td>                
                    <td><input type="text" class="form-control amount-val" name="total_valuated[]"></td>
                    <input type="hidden" name="id[]" class="item-id">
                    <input type="hidden" name="row_type[]" value="1" class="type-inp">
                    <input type="hidden" name="row_index[]" class="index-inp">
                    <input type="hidden" name="unit[]" class="unit-inp">
                    <input type="hidden" name="product_subtotal[]" class="subtotal-inp">
                    <input type="hidden" name="product_amount[]" class="amount-inp">
                    <input type="hidden" name="product_valued_bal[]" class="valued-bal-inp">
                    <input type="hidden" name="productvar_id[]" class="prodvar-id">
                    <input type="hidden" name="quote_item_id[]" class="quoteitem-id">
                </tr>

                <!-- Title-Add Row Template -->
                <tr class="title-templ-add">
                    <td>@include('focus.job_valuations.partials.macro', ['elem' => 'dropdown'])</td>
                    <td><input type="text" name="numbering[]" class="form-control num-inp"></td>
                    <td colspan="6"><input type="text" name="product_name[]" class="form-control descr-inp"></td>
                    <td colspan="4"></td>
                    <input type="hidden" name="id[]" class="item-id">
                    <input type="hidden" name="quote_item_id[]" class="quoteitem-id">                
                    <input type="hidden" name="row_type[]" value="2" class="type-inp">
                    <input type="hidden" name="row_index[]" class="index-inp">
                    @php 
                        $keys = [
                            'unit', 'product_qty', 'tax_rate', 'product_tax', 'product_price', 'product_subtotal', 
                            'product_amount', 'product_valued_bal', 'productvar_id', 'perc_valuated', 'total_valuated',
                        ];
                    @endphp
                    @foreach($keys as $key)
                        <input type="hidden" name="{{ $key }}[]">
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</fieldset>