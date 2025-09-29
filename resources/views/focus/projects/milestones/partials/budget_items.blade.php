<table id="budgetsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>#</th>
            <th>Product Name</th>
            <th>Unit</th>
            <th>Prev. Budgeted Qty</th>
            <th>Total Budged Qtys</th>
            <th>Qty Per Milestone</th>
            <th>Amount</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @isset($milestone)
            @php $row = 1; @endphp

            {{-- Existing milestone items --}}
            @foreach ($milestone->items as $item)
            <tr>
                <td>{{ $row++ }}</td>
                <td>{{ $item->product_variation->name }}</td>
                <td>{{ $item->unit_of_measure->code }}</td>
                <td><span class="budget_qty">{{ +$item->budget_item->new_qty }}</span></td>  
                <td><span class="qty_allocated_to_milestones">{{ +$item->budget_item->qty_allocated_to_milestones }}</span></td>  
                <td>
                    <input type="text" class="form-control qty" name="qty[]" value="{{ +$item->qty }}" id="qty-p{{ $row }}" step="0.1"
                     required @if ($item->material_request_item) readonly @endif>
                </td>
                <td><span class="amount">{{ $item->qty * $item->price }}</span></td>
                <td class="text-center">
                    @if (!$item->material_request_item) {{-- or: !$item->is_material_requisitioned --}}
                        <input type="checkbox" value="1" id="check" {{ $item->product_id ? 'checked' : '' }} class="btn btn-sm form-control check">
                    @endif
                </td>

                {{-- Hidden fields --}}
                <input type="hidden" name="product_id[]" value="{{ $item->product_id }}" class="product_id" id="productid-p{{ $row }}">
                <input type="hidden" name="budget_item_id[]" class="id" value="{{ $item->budget_item_id }}">
                <input type="hidden" name="unit_id[]" class="unit_id" value="{{ $item->unit_id }}">
                <input type="hidden" name="id[]" class="id" value="{{ $item->id }}">
                <input type="hidden" class="previous_qty" value="{{ +$item->qty }}">
                <input type="hidden" name="price[]" class="price" value="{{ $item->price }}">
            </tr>
            @endforeach

            {{-- Remaining budget items not in milestones --}}
            @foreach ($budget_items as $item)
            <tr>
                <td>{{ $row++ }}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->unit }}</td>
                <td><span class="budget_qty">{{ +$item->new_qty }}</span></td>  
                <td><span class="qty_allocated_to_milestones">{{ +$item->qty_allocated_to_milestones }}</span></td>  
                <td>
                    <input type="text" class="form-control qty" name="qty[]" value="{{ $item->remaining_qty }}" id="qty-p{{ $row }}" step="0.1" disabled required>
                </td>
                <td><span class="amount">0</span></td>
                <td class="text-center">
                    <input type="checkbox" value="1" id="check" class="btn btn-sm form-control check">
                </td>

                {{-- Hidden fields --}}
                <input type="hidden" name="product_id[]" value="{{ $item->product_id }}" class="product_id" id="productid-p{{ $row }}" disabled>
                <input type="hidden" name="budget_item_id[]" class="id" value="{{ $item->id }}" disabled>
                <input type="hidden" name="unit_id[]" class="unit_id" value="{{ $item->unit_id }}" disabled>
                <input type="hidden" name="id[]" class="id" value="0" disabled>
                <input type="hidden" class="previous_qty" value="{{ +$item->qty }}">
                <input type="hidden" name="price[]" class="price" value="{{ $item->price }}" disabled>
            </tr>
            @endforeach
        @endisset

    </tbody>
</table>