<div class="table-responsive">
    <table id="itemsTbl" class="table table-bordered" width="100%">
        <thead>
            <tr>
                <th style="width: 40%;">Product</th>
                <th style="width: 15%;">Planned Qty</th>
                <th style="width: 15%;">Delivered Qty</th>
                <th style="width: 15%;">Returned Qty</th>
                <th style="width: 15%;">Remaining Qty</th>
            </tr>
        </thead>
        <tbody>
            @isset($delivery)
                @if (count($delivery->items) > 0)
                   @foreach ($delivery->items as $item)
                       <tr>
                            <td>
                                <select name="product_id[]" id="product_id-0" class="form-control product_id"
                                    data-placeholder="Search Product">
                                    <option value="{{ $item->product_id }}">{{ $item->product ? $item->product->name : '' }}</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="planned_qty[]" class="form-control planned_qty" id="planned_qty-0"
                                value="{{ $item->planned_qty }}" placeholder="0.00" readonly></td>
                            <td><input type="number" step="0.01" name="delivered_qty[]" class="form-control delivered_qty" id="delivered_qty-0"
                                    placeholder="0.00" value="{{ $item->delivered_qty }}"></td>
                            <td><input type="number" step="0.01" name="returned_qty[]" class="form-control returned_qty" id="returned_qty-0"
                                    placeholder="0.00" value="{{ $item->returned_qty }}"></td>
                            <td><input type="number" step="0.01" name="remaining_qty[]" class="form-control remaining_qty" id="remaining_qty-0"
                                    placeholder="0.00" value="{{ $item->remaining_qty }}"></td>
                            
                            {{-- <td><span class="amt">0</span></td> --}}
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="cost_of_bottle[]" class="cost_of_bottle" value="{{ $item->cost_of_bottle }}">
                        <input type="hidden" name="rate[]" class="rate" value="{{ $item->rate }}">
                        </tr>
                   @endforeach 
                @endif
            @endisset
        </tbody>
    </table>
</div>

