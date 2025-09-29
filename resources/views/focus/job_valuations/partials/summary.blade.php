<div class="row mb-1">
    <!-- Order Items summary -->    
    <div class="col-6">
        <div class="table-responsive">
            <table id="summaryTbl" class="table table-bordered text-center">
                <thead>
                    <th width="30%">&nbsp;</th>
                    <th>Taxable</th>
                    <th>Tax</th>
                    <th>Subtotal</th>
                    <th>% Valued</th>
                    <th>Balance</th>
                </thead>
                <tbody>
                    <tr>
                        <td class="quote-row"><b>{{ $quote->bank_id? 'PI' : 'Quote' }}</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr class="valx-row">
                        <td><b>Current Valuation</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <input type="hidden" name="taxable" id="taxable">
                        <input type="hidden" name="subtotal" id="subtotal">
                        <input type="hidden" name="tax" id="tax">
                        <input type="hidden" name="total" id="total">
                        <!-- valuation -->
                        <input type="hidden" name="balance" id="balance">
                        <input type="hidden" name="valued_taxable" id="valTaxable">
                        <input type="hidden" name="valued_subtotal" id="valSubtotal">
                        <input type="hidden" name="valued_tax" id="valTax">
                        <input type="hidden" name="valued_total" id="valTotal">
                        <input type="hidden" name="valued_perc" id="valPerc">
                    </tr>
                    <!-- valuation history -->
                    @foreach ($jobValuations as $i => $jv)
                        <tr>
                            <td><b>Valuation {{ $jobValuations->count() - $i }}</b></td>
                            <td>{{ numberFormat($jv->valued_taxable) }}</td>
                            <td>{{ numberFormat($jv->valued_tax) }}</td>
                            <td>{{ numberFormat($jv->valued_subtotal) }}</td>
                            <td>{{ +$jv->valued_perc }}</td>
                            <td>{{ numberFormat($jv->balance) }}</td>                        
                        </tr>
                    @endforeach
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
                    <th>Amount</th>
                    <th>% Valued</th>
                    <th>Balance</th>
                </thead>
                <tbody>
                    <tr>
                        <td><b>Total Expenses</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><b>Current Valuation</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <input type="hidden" name="exp_total" id="expTotal">
                        <input type="hidden" name="exp_valuated" id="expValuated">
                        <input type="hidden" name="exp_balance" id="expBalance">
                        <input type="hidden" name="exp_valuated_perc" id="expValuatedPerc">
                    </tr>
                    <!-- valuation history -->
                    @foreach ($jobValuations as $i => $jv)
                        <tr>
                            <td><b>Valuation {{ $jobValuations->count() - $i }}</b></td>
                            <td>{{ numberFormat($jv->exp_valuated) }}</td>
                            <td>{{ +$jv->exp_valuated_perc }}</td>
                            <td>{{ numberFormat($jv->exp_balance) }}</td>                            
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>