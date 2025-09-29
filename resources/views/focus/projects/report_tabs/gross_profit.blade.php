<div class="tab-pane" id="tab_data10" aria-labelledby="tab10" role="tabpanel">
    <div class="card-body">
        <h3 style="font-size: 24px;">1. Quotation / Proforma Invoice</h3>
        <div class="table-responsive mb-4">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th style="font-size: 20px;">Quote / PI</th>
                    <th style="font-size: 20px;">Quoted Amount</th>
                    <th style="font-size: 20px;">Est. Cost Amount</th>
                    <th style="font-size: 20px;">Gross Profit (Quoted - Est. Cost)</th>
                    <th style="font-size: 20px;">% Gross Profit</th>
                </tr>
                </thead>
                <tbody>
                @php
                    // aggregate
                    use App\Models\quote\Quote;$total_actual = 0;
                    $total_estimate = 0;
                    $total_balance = 0;
                @endphp
                @foreach ($project->quotes as $quote)
                    @php
                        $estimated_amount = $quote->subtotal;
                        $actual_amount = 0;
                        foreach ($quote->products as $item) {
                            $actual_amount += $item->estimate_qty * $item->buy_price;
                        }
                        $balance = $estimated_amount - $actual_amount;
                        // aggregate
                        $total_estimate += $estimated_amount;
                        $total_actual += $actual_amount;
                        $total_balance += $balance;
                    @endphp
                    <tr>
                        <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                        <td>{{ numberFormat($estimated_amount) }}</td>
                        <td>{{ numberFormat($actual_amount) }}</td>
                        <td>{{ numberFormat($balance) }}</td>
                        <td>{{ round(div_num($balance, $estimated_amount) * 100) }} %</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-size: 20px;"><b>Totals</b></td>
                    <td style="font-size: 18px;"><b>{{ numberFormat($total_estimate) }}</b></td>
                    <td style="font-size: 18px;"><b>{{ numberFormat($total_actual) }}</b></td>
                    <td style="font-size: 18px;"><b>{{ numberFormat($total_balance) }}</b></td>
                    <td style="font-size: 18px;"><b>{{ round(div_num($total_balance, $total_estimate) * 100) }} %</b>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!--  budgeting -->
        <h3 class="mb-1" style="font-size: 24px;">2. Budgeting</h3>
        <div class="table-responsive mb-3">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th style="font-size: 20px;">Quote / PI (Budget)</th>
                    <th style="font-size: 20px;">Quoted Amount</th>
                    <th style="font-size: 20px;">Budget</th>
                    <th style="font-size: 20px;">Gross Profit (Quoted - Budget)</th>
                    <th style="font-size: 20px;">% Gross Profit</th>
                </tr>
                </thead>
                <tbody>
                @php
                    // aggregate
                    $total_actual = 0;
                    $total_estimate = 0;
                    $total_balance = 0;
                @endphp
                @foreach ($project->quotes as $quote)
                    @php
                        $actual_amount = $quote->subtotal;
                        $estimated_amount = 0;
                        if ($quote->budget) $estimated_amount = $quote->budget->items()->sum(DB::raw('round(new_qty*price)'));
                        $balance = $actual_amount - $estimated_amount;
                        // aggregate
                        $total_actual += $actual_amount;
                        $total_estimate += $estimated_amount;
                        $total_balance += $balance;
                    @endphp
                    <tr>
                        <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                        <td>{{ numberFormat($actual_amount) }}</td>
                        <td>{{ numberFormat($estimated_amount) }}</td>
                        <td>{{ numberFormat($balance) }}</td>
                        <td>{{ round(div_num($balance, $actual_amount) * 100) }} %</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-size: 20px;"><b>Totals</b></td>
                    <td style="font-size: 18px;"><b>{{ numberFormat($total_actual) }}</b></td>
                    <td style="font-size: 18px;"><b>{{ numberFormat($total_estimate) }}</b></td>
                    <td style="font-size: 18px;"><b>{{ numberFormat($total_balance) }}</b></td>
                    <td style="font-size: 18px;"><b>{{ round(div_num($total_balance, $total_actual) * 100) }} %</b></td>
                </tr>
                </tbody>
            </table>
        </div>

        <h4>2.1 Budget Lines</h4>
        <div class="table-responsive mb-4">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    {{--                    <th style="font-size: 20px;">#</th>--}}
                    <th style="font-size: 20px;">Budget Line</th>
                    <th style="font-size: 20px;">Amount</th>
                </tr>
                </thead>

                <tbody>
                @php

                    $projectBudgetLines = \App\Models\project\ProjectMileStone::where('project_id', $project->id)->select('id', 'name', 'amount')->get();
                    $i = 0
                @endphp

                @foreach($projectBudgetLines as $pbl)
                    {{--                        @php $i += 1; @endphp--}}
                    <tr>
                        {{--                            <td>{{ $i }}</td>--}}
                        <td>{{ $pbl['name'] }}</td>
                        <td>{{ numberFormat($pbl['amount']) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-size: 20px;"><b>Total</b></td>
                    <td style="font-size: 18px;">
                        <b>{{ numberFormat(sprintf("%.2f", $projectBudgetLines->pluck('amount')->sum())) }}</b></td>
                </tr>


                </tbody>

            </table>
        </div>


        <!-- direct purchase and purchase order expense -->
        <h3 class="mt-2" style="font-size: 24px;">3. Job Expense</h3>
        <div class="table-responsive mb-3">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th style="font-size: 20px;">Quote / PI (Budget)</th>
                    <th style="font-size: 20px;">Quoted Amount</th>
                    <th style="font-size: 20px;">Actual Cost</th>
                    <th style="font-size: 20px;">Gross Profit (Quoted - Cost)</th>
                    <th style="font-size: 20px;">% Gross Profit</th>
                </tr>
                </thead>
                <tbody>
                @php
                    // aggregate
                    $total_actual = 0;
                    $total_estimate = 0;
                    $total_balance = 0;



                @endphp
                @foreach ($project->quotes as $quote)
                    @php

                        $siQuote = Quote::where('id', $quote->id)->with('stockIssues')->get()->toArray();//->pluck('stock_issues');
                        $stock_issues_arrays = array_column($siQuote, 'stock_issues');
                        // Flatten the array of arrays into a single array
                        $stock_issues = array_merge(...$stock_issues_arrays);

                        $stockIssuesValue = array_reduce($stock_issues, function($carry, $stock_issue) {
                            return $carry + $stock_issue['total'];
                        }, 0);


                        $actual_amount = $quote->subtotal;

                        $dir_purchase_amount = $project->purchase_items->sum('amount') / $project->quotes->count();
                        $proj_grn_amount = $project->grn_items()->sum(DB::raw('round(rate*qty)')) / $project->quotes->count();
                        $labour_amount = $project->labour_allocations()->sum(DB::raw('hrs * 500')) / $project->quotes->count();
                        $expense_amount = $dir_purchase_amount + $proj_grn_amount + $labour_amount + $stockIssuesValue;
                        if ($quote->projectstock) $expense_amount += $quote->projectstock->sum('total');

                        $balance = $actual_amount - $expense_amount;
                        // aggregate
                        $total_actual += $actual_amount;
                        $total_estimate += $expense_amount;
                        $total_balance += $balance;
                    @endphp
                    <tr>
                        <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                        <td>{{ numberFormat($actual_amount) }}</td>
                        <td>{{ numberFormat($expense_amount) }}</td>
                        <td>{{ numberFormat($balance) }}</td>
                        <td>{{ round(div_num($balance, $actual_amount) * 100) }} %</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-size: 20px;"><b>Totals</b></td>
                    <td style="font-size: 20px;"><b>{{ numberFormat($total_actual) }} </b></td>
                    <td style="font-size: 20px;"><b>{{ numberFormat($total_estimate) }} </b></td>
                    <td style="font-size: 20px;"><b>{{ numberFormat($total_balance) }} </b></td>
                    <td style="font-size: 20px;"><b>{{ round(div_num($total_balance, $total_actual) * 100) }} % </b>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <h4>3.1 Expenditure per Budget Line</h4>
        <div class="table-responsive mb-1">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    {{--                    <th style="font-size: 20px;">#</th>--}}
                    <th style="font-size: 20px;">Budget Line</th>
                    <th style="font-size: 20px;">Expenditure</th>
                </tr>
                </thead>

                <tbody>
                @foreach($expensesByMilestone as $epm => $expenditure)
                    {{--                        @php $i += 1; @endphp--}}
                    <tr>
                        {{--                            <td>{{ $i }}</td>--}}
                        <td>{{ $epm }}</td>
                        <td>{{ numberFormat($expenditure) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-size: 20px;"><b>Total</b></td>
                    <td style="font-size: 18px;">
                        <b>{{ numberFormat(sprintf("%.2f", array_sum($expensesByMilestone))) }}</b></td>
                </tr>


                </tbody>

            </table>
        </div>


        <!-- verification -->
        <h5 class="mt-4" style="font-size: 24px;">4. Job Verification</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th style="font-size: 20px;">Quote / PI (Budget)</th>
                    <th style="font-size: 20px;">Verified Amount</th>
                    <th style="font-size: 20px;">Actual Cost</th>
                    <th style="font-size: 20px;">Profit (Verified - Cost)</th>
                    <th style="font-size: 20px;">% Gross Profit</th>
                </tr>
                </thead>
                <tbody>
                @php
                    // aggregate
                    $total_actual = 0;
                    $total_estimate = 0;
                    $total_balance = 0;
                @endphp
                @foreach ($project->quotes as $quote)
                    @php

                        $siQuote = Quote::where('id', $quote->id)->with('stockIssues')->get()->toArray();//->pluck('stock_issues');
                        $stock_issues_arrays = array_column($siQuote, 'stock_issues');
                        // Flatten the array of arrays into a single array
                        $stock_issues = array_merge(...$stock_issues_arrays);

                        $stockIssuesValue = array_reduce($stock_issues, function($carry, $stock_issue) {
                            return $carry + $stock_issue['total'];
                        }, 0);

                        $actual_amount = $quote->verified_amount;

                        $dir_purchase_amount = $project->purchase_items->sum('amount') / $project->quotes->count();
                        $proj_grn_amount = $project->grn_items()->sum(DB::raw('round(rate*qty)')) / $project->quotes->count();
                        $labour_amount = $project->labour_allocations()->sum(DB::raw('hrs * 500')) / $project->quotes->count();
                        $expense_amount = $dir_purchase_amount + $proj_grn_amount + $labour_amount + $stockIssuesValue;
                        if ($quote->projectstock) $expense_amount += $quote->projectstock->sum('total');

                        $balance = $actual_amount - $expense_amount;
                        // aggregate
                        $total_actual += $actual_amount;
                        $total_estimate += $expense_amount;
                        $total_balance += $balance;
                    @endphp
                    <tr>
                        <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                        <td>{{ numberFormat($actual_amount) }}</td>
                        <td>{{ numberFormat($expense_amount) }}</td>
                        <td>{{ numberFormat($balance) }}</td>
                        <td>{{ round(div_num($balance, $actual_amount) * 100) }} %</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-size: 20px;"><b>Totals</b></td>
                    <td style="font-size: 18px;"><b>{{ numberFormat($total_actual) }} </b></td>
                    <td style="font-size: 18px;"><b>{{ numberFormat($total_estimate) }} </b></td>
                    <td style="font-size: 18px;"><b>{{ numberFormat($total_balance) }} </b></td>
                    <td style="font-size: 18px;"><b>{{ round(div_num($total_balance, $total_actual) * 100) }} % </b>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
