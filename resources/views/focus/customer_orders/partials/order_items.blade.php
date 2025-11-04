<div class="table-responsive">
    <table id="itemsTbl" class="table table-bordered" width="100%">
        <thead>
            <tr>
                <th style="width: 35%;">Product</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 10%;">Quantity</th>
                <th style="width: 10%;">Rate</th>
                <th style="width: 10%;">VAT</th>
                <th style="width: 15%;">Amount</th>
                <th style="width: 10%;">Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- your first row (index 0) -->
            <tr>
                <td>
                    <select name="product_id[]" id="product_id-0" class="form-control product_id"
                        data-placeholder="Search Product">
                        <option value="">Search Product</option>
                    </select>
                </td>
                <td>
                    <select name="type[]" id="type-0" class="form-control type">
                        <option value="">--select type--</option>
                        <option value="returnable">Returnable</option>
                        <option value="non_returnable">Non Returnable</option>
                    </select>
                </td>
                <td><input type="number" step="0.01" name="qty[]" class="form-control qty" id="qty-0"
                        placeholder="0.00"></td>
                <td><input type="text" name="rate[]" class="form-control rate" id="rate-0"></td>
                <td>
                    <select class="form-control rowtax" name="tax_rate[]" id="rowtax-0">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : '' }}>
                                {{ $tax->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><span class="amt">0</span></td>
                <td>
                    <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                        <i class="fa fa-trash fa-lg text-danger"></i>
                    </button>
                </td>
                <input type="hidden" class="amount" name="amount[]">
                <input type="hidden" class="itemtax" name="itemtax[]">
                <input type="hidden" name="id[]" value="0">
            </tr>
            @isset($customer_order)
                @if (count($customer_order->items) > 0)
                    @foreach ($customer_order->items as $index => $item)
                        <tr>
                            <td>
                                <select name="product_id[]" id="product_id-{{ $index }}"
                                    class="form-control product_id" data-placeholder="Search Product"
                                    data-selected-id="{{ $item->product_id }}"
                                    data-selected-text="{{ $item->product->name }}"
                                    data-selected-rate="{{ $item->rate }}">
                                    <!-- Option left empty, JS will inject selected product -->
                                </select>
                            </td>
                            <td>
                                <select name="type[]" id="type-{{ $index }}" class="form-control type">
                                    <option value="">--select type--</option>
                                    <option value="returnable" {{ $item->type == 'returnable' ? 'selected' : '' }}>
                                        Returnable</option>
                                    <option value="non_returnable" {{ $item->type == 'non_returnable' ? 'selected' : '' }}>
                                        Non Returnable</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="qty[]" class="form-control qty"
                                    id="qty-{{ $index }}" placeholder="0.00" value="{{ $item->qty }}">
                            </td>
                            <td>
                                <input type="text" value="{{ $item->rate }}" name="rate[]" class="form-control rate"
                                    id="rate-{{ $index }}">
                            </td>
                            <td>
                                <select class="form-control rowtax" name="tax_rate[]" id="rowtax-{{ $index }}">
                                    @foreach ($additionals as $tax)
                                        <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->tax_rate ? 'selected' : '' }}>
                                            {{ $tax->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><span class="amt">{{ $item->amount }}</span></td>
                            <td>
                                <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                    <i class="fa fa-trash fa-lg text-danger"></i>
                                </button>
                            </td>
                            <input type="hidden" class="amount" value="{{ $item->amount }}" name="amount[]">
                            <input type="hidden" class="itemtax" value="{{ $item->itemtax }}" name="itemtax[]">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                    @endforeach
                @endif
            @endisset

        </tbody>
    </table>
</div>

<!-- hidden template row -->
<template id="rowTemplate">
    <tr>
        <td>
            <select name="product_id[]" class="form-control product_id" data-placeholder="Search Product">
                <option value="">Search Product</option>
            </select>
        </td>
        <td>
            <select name="type[]" class="form-control type">
                <option value="">--select type--</option>
                <option value="returnable">Returnable</option>
                <option value="non_returnable">Non Returnable</option>
            </select>
        </td>
        <td><input type="number" step="0.01" name="qty[]" class="form-control qty" placeholder="0.00"></td>
        <td><input type="text" name="rate[]" class="form-control rate"></td>
        <td>
            <select class="form-control rowtax" name="tax_rate[]">
                @foreach ($additionals as $tax)
                    <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : '' }}>
                        {{ $tax->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><span class="amt">0</span></td>
        <td>
            <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                <i class="fa fa-trash fa-lg text-danger"></i>
            </button>
        </td>
        <input type="hidden" class="amount" name="amount[]">
        <input type="hidden" name="id[]" value="0">
    </tr>
</template>

<button class="btn btn-success btn-sm ml-2" type="button" id="addRow">
    <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
</button>
