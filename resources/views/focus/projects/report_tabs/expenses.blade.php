<div class="tab-pane" id="tab_data4" aria-labelledby="tab4" role="tabpanel">
    <div class="card-body">
        <div class="row mb-1">
            <div class="col-2">
                <label for="category">Expense Category</label>
                @php
                    $categories=[
                        'labour_service' => 'Labour Service',
                        'inventory_stock' => 'Inventory Stock',
                        'dir_purchase_stock' => 'Direct Purchase Stock',
                        'purchase_order_stock' => 'Purchase Order Stock',
                        'dir_purchase_service' => 'Direct Purchase Service',
                    ];
                @endphp
                <select class="custom-select" id="expCategory">
                    <option value="">-- Select Expense --</option>
                    @foreach ($categories as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-3">
                <label for="account">Accounting Ledger</label>
                <select class="custom-select" id="accountLedger" data-placeholder="Search Accounting Ledger">
                    <option value=""></option>
                    @foreach ($exp_accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->number }}-{{ $account->holder }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-3">
                <label for="supplier">Supplier</label>
                <select class="custom-select" id="supplier" data-placeholder="Choose Supplier">
                    <option value=""></option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- <div class="col-3">
                <label for="product_name">Product</label>
                <select class="custom-select" id="product_name" data-placeholder="Choose Product">
                    <option value=""></option>
                    @foreach ($productNames as $pName)
                        <option value="{{ $pName }}">{{ $pName }}</option>
                    @endforeach
                </select>
            </div> --}}

        </div>

        <table id="expTotals" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Inventory Stock</th>
                    <th>Labour Service</th>
                    <th>Direct Purchase Stock</th>
                    <th>Direct Purchase Service</th>
                    <th>Purchase Order Stock</th>
                    <th>Expense Grand Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="font-weight-bold"></td>
                </tr>
            </tbody>
        </table>

        <table id="expItems" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Expense Category</th>
                    <th>Budget Line</th>
                    <th>Source</th>
                    <th>Item Description</th>
                    <th>Date</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                {{-- <tr>
                    <td colspan="100%" class="text-center text-success font-large-1">
                        <i class="fa fa-spinner spinner"></i>
                    </td>
                </tr> --}}
            </tbody>
        </table>
    </div>
</div>
