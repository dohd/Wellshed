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
                                    <option value="{{ $item->product_id }}">{{ $item->product ? $item->product->name : '' }}
                                    </option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="planned_qty[]" class="form-control planned_qty"
                                    id="planned_qty-0" value="{{ $item->planned_qty }}" placeholder="0.00" readonly></td>
                            <td><input type="number" step="0.01" name="delivered_qty[]"
                                    class="form-control delivered_qty" id="delivered_qty-0" placeholder="0.00"
                                    value="{{ $item->delivered_qty }}"></td>
                            <td><input type="number" step="0.01" name="returned_qty[]" class="form-control returned_qty"
                                    id="returned_qty-0" placeholder="0.00" value="{{ $item->returned_qty }}"></td>
                            <td><input type="number" step="0.01" name="remaining_qty[]"
                                    class="form-control remaining_qty" id="remaining_qty-0" placeholder="0.00"
                                    value="{{ $item->remaining_qty }}"></td>

                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="cost_of_bottle[]" class="cost_of_bottle"
                                value="{{ $item->cost_of_bottle }}">
                            <input type="hidden" name="rate[]" class="rate" value="{{ $item->rate }}">
                        </tr>
                    @endforeach
                @endif
            @endisset
        </tbody>
    </table>
</div>
<template id="rowTemplate">
    <tr>
        <td>
            <select name="product_id[]" class="form-control product_id" data-placeholder="Search Product">
                <option value="">Search Product</option>
            </select>
        </td>
        <td><input type="number" step="0.01" name="planned_qty[]" class="form-control planned_qty"
                id="planned_qty-0" value="0" placeholder="0.00" readonly></td>
        <td><input type="number" step="0.01" name="delivered_qty[]" class="form-control delivered_qty"
                id="delivered_qty-0" placeholder="0.00" value="0" readonly></td>
        <td><input type="number" step="0.01" name="returned_qty[]" class="form-control returned_qty"
                id="returned_qty-0" placeholder="0.00" value="0"></td>
        <td><input type="number" step="0.01" name="remaining_qty[]" class="form-control remaining_qty"
                id="remaining_qty-0" placeholder="0.00" value="0"></td>

        <input type="hidden" name="id[]" value="0">
        <input type="hidden" name="amount[]" class="amount" value="0">
        <input type="hidden" name="cost_of_bottle[]" class="cost_of_bottle" value="0">
        <input type="hidden" name="rate[]" class="rate" value="0">
        <input type="hidden" name="delivery_schedule_item_id[]" class="delivery_schedule_item_id" value="0">
    </tr>
</template>

<button class="btn btn-success btn-sm ml-2" type="button" id="addRow">
    <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
</button>
