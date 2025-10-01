<div class="table-responsive">
    <table id="itemsTbl" class="table table-bordered" widht="100%">
       <thead>
            <tr>
                <th style="width: 35%;">Product</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 10%;">Quantity</th>
                <th style="width: 10%;">Cost</th>
                <th style="width: 10%;">VAT</th>
                <th style="width: 15%;">Amount</th>
                <th style="width: 10%;">Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- schedule row template -->
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
                    <select class="form-control rowtax" name="itemtax[]" id="rowtax-0">
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
                <input type="hidden" name="doc_id[]" value="0">
            </tr>
        </tbody>
    </table>
</div>
<button class="btn btn-success btn-sm ml-2" type="button" id="addRow">
    <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
</button>
