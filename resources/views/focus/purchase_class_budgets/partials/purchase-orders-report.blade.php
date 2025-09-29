<div class="card-content">
    <div class="card-body">
        <div class="mb-1">

            <div class="mb-1">

                <div class="row mb-2">
                    <div class="col-9 col-lg-6">
                        <label for="purchaseOrderMonth" >Filter by Month</label>
                        <select class="form-control box-size po-filter" id="poMonth" name="poMonth" data-placeholder="Filter by Month">

                            <option value=""></option>

                            @php
                                $months = ['January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12];
    
                                if ($purchaseClassBudget->financialYear){
    
                                    $fY =( new DateTime($purchaseClassBudget->financialYear->start_date))->format('Y');
                                }
                                else {
    
                                    $months = [];
                                    $fY = '';
                                }

                            @endphp

                            @foreach ($months as $m => $val)
                                <option value="{{ $val }}">
                                    {{ $m . " " . $fY }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <button id="clearPurchaseOrderFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                    </div>
                </div>

                <div class="row mt-1">
                    <p class="col-6 col-lg-4" style="font-size: 16px;">Filtered Month Budget: <span id="purchaseOrdersMonthBudget" style="font-size: 25px; font-weight: bold"></span></p>
                    <p class="col-6 col-lg-4" style="font-size: 16px;">Purchase Order Items: <span id="purchaseOrdersCount" style="font-size: 25px; font-weight: bold"></span></p>
                    <p class="col-6 col-lg-4" style="font-size: 16px;">Total Expenses for the Month: <span id="purchaseOrdersValue" style="font-size: 25px; font-weight: bold"></span></p>
                </div>

            </div>


        </div>
        <table id="purchaseOrdersTbl"
            class="table table-striped table-bordered zero-configuration" cellspacing="0"
            width="100%">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Items</th>
                    <th>Month</th>
                    <th>Date</th>
                    <th>Value</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>