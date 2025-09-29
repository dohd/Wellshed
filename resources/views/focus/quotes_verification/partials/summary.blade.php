<div class="row mb-1">
    <!-- Order Items summary -->    
    <div class="col-6">
        <div class="table-responsive">
            <table id="summaryTbl" class="table table-bordered text-center">
                <thead>
                    <th width="30%">&nbsp;</th>
                    <th>Taxable</th>
                    <th>Subtotal</th>
                    <th>Tax</th>
                    <th>Total</th>                    
                </thead>
                <tbody>
                    <tr>
                        <td class="quote-row"><b>Verified Totals</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <input type="hidden" name="taxable" id="taxable">
                    <input type="hidden" name="subtotal" id="subtotal">
                    <input type="hidden" name="tax" id="tax">
                    <input type="hidden" name="total" id="total">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expenses summary -->
    <div class="col-6">
        <div class="table-responsive">
            <table id="expSummaryTbl" class="table table-bordered text-center">
                <thead>
                    <th width="30%">&nbsp;</th>
                    <th>Total</th>
                </thead>
                <tbody>
                    @php
                        $expenseTtl = $serviceExpenses->sum('amount') 
                            + $materialExpenses->sum('total_expense');
                    @endphp
                    <tr>
                        <td><b>Expenses Total</b></td>
                        <td>{{ numberFormat($expenseTtl) }}</td>  
                        <input type="hidden" name="expense" value="{{ $expenseTtl }}" id="expense">                      
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
