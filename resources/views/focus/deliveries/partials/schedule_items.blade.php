<div class="form-group">
    <div class="table-responsive">
        <table id="itemsTbl" class="table table-bordered table-sm text-center align-middle" width="100%">
            <thead class="thead-light">
                <tr>
                    <th style="min-width: 180px;">Product</th>
                    <th style="min-width: 110px;">Planned Qty</th>
                    <th style="min-width: 110px;">Delivered Qty</th>
                    <th style="min-width: 110px;">Returned Qty</th>
                    <th style="min-width: 110px;">Remaining Qty</th>
                </tr>
            </thead>
            <tbody>
                @isset($delivery)
                    @if (count($delivery->items) > 0)
                        @foreach ($delivery->items as $item)
                            <tr>
                                <td>
                                    <select name="product_id[]" class="form-control product_id select2" data-placeholder="Search Product">
                                        <option value="{{ $item->product_id }}">
                                            {{ $item->product ? $item->product->name : '' }}
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="planned_qty[]" class="form-control planned_qty" 
                                        value="{{ $item->planned_qty }}" placeholder="0.00" readonly>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="delivered_qty[]" class="form-control delivered_qty"
                                        value="{{ $item->delivered_qty }}" placeholder="0.00">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="returned_qty[]" class="form-control returned_qty"
                                        value="{{ $item->returned_qty }}" placeholder="0.00">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="remaining_qty[]" class="form-control remaining_qty"
                                        value="{{ $item->remaining_qty }}" placeholder="0.00">
                                </td>

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

    <!-- Add Row Button -->
    <div class="text-right mt-2">
        <button class="btn btn-success btn-sm" type="button" id="addRow">
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
        </button>
    </div>
</div>

<!-- Row Template -->
<template id="rowTemplate">
    <tr>
        <td>
            <select name="product_id[]" class="form-control product_id select2" data-placeholder="Search Product">
                <option value="">Search Product</option>
            </select>
        </td>
        <td><input type="number" step="0.01" name="planned_qty[]" class="form-control planned_qty" value="0" placeholder="0.00" readonly></td>
        <td><input type="number" step="0.01" name="delivered_qty[]" class="form-control delivered_qty" value="0" placeholder="0.00"></td>
        <td><input type="number" step="0.01" name="returned_qty[]" class="form-control returned_qty" value="0" placeholder="0.00"></td>
        <td><input type="number" step="0.01" name="remaining_qty[]" class="form-control remaining_qty" value="0" placeholder="0.00" readonly></td>

        <input type="hidden" name="id[]" value="0">
        <input type="hidden" name="amount[]" class="amount" value="0">
        <input type="hidden" name="cost_of_bottle[]" class="cost_of_bottle" value="0">
        <input type="hidden" name="rate[]" class="rate" value="0">
        <input type="hidden" name="delivery_schedule_item_id[]" class="delivery_schedule_item_id" value="0">
    </tr>
</template>

<!-- Mobile-friendly adjustments -->
<style>
    @media (max-width: 767.98px) {
        #itemsTbl th,
        #itemsTbl td {
            font-size: 0.85rem;
            padding: 6px;
        }
        .table-responsive {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }
        .btn-sm {
            font-size: 0.85rem;
            padding: 6px 10px;
        }
    }
</style>
