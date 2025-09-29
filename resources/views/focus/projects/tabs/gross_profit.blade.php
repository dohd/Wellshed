<head>
    <!-- Latest CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
</head>


<div class="tab-pane" id="tab_data12" aria-labelledby="tab12" role="tabpanel">
    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('biller.projects.print-gross-profit', $project) }}" target="_blank"  class="btn btn-info  btn-lighten-2">
                <i class="fa fa-print"></i> Print Gross Profit Report
            </a>
        </div>

        @php
            $creator = App\Models\Access\User\User::withoutGlobalScopes()->find($project->user_id);
        @endphp

        <h3 class="mb-3">Project Created By: <b>{{ optional($creator)->first_name . ' ' . optional($creator)->last_name }}</b></h3>

        <h3 style="font-size: 24px;">1. Quotation / Proforma Invoice Amount vs. Estimated Expense</h3>
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
                    use App\Models\Access\User\User;use App\Models\CasualLabourersRemunerations\CasualLabourersRemuneration;use App\Models\quote\Quote;$total_actual = 0;
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
        <h3 class="mb-1" style="font-size: 24px;">2. Quotation / Proforma Invoice Amount vs. Budgeted Expense</h3>
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

        <h4>2.1 Budget Lines <span>(<b>% of Project: {{numberFormat($project->progress)}}%</b>)</span></h4>
        <div class="table-responsive mb-4">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    {{--                    <th style="font-size: 20px;">#</th>--}}
                    <th style="font-size: 20px;">Budget Line</th>
                    <th style="font-size: 20px;">Amount</th>
                    <th style="font-size: 20px;">% of Milestone</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $projectBudgetLines = \App\Models\project\ProjectMileStone::where('project_id', $project->id)->select('id', 'name', 'amount', 'milestone_expected_percent')->get();
                    $i = 0
                @endphp
                @foreach($projectBudgetLines as $pbl)
                    <tr>
                        <td>{{ $pbl['name'] }}</td>
                        <td>{{ numberFormat($pbl['amount']) }}</td>
                        <td>{{ $pbl['milestone_expected_percent'] }}%</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-size: 20px;"><b>Total</b></td>
                    <td style="font-size: 18px;">
                        <b>{{ numberFormat(sprintf("%.2f", $projectBudgetLines->pluck('amount')->sum())) }}</b>
                    </td>
                </tr>
                @php
                    $budgets = \App\Models\project\Project::where('id', $project->id)->with('quotes.budget')->get();
                    $budgetValues = $budgets->map(function ($b) {
                        return isset($b->budget) ? $b->budget->budget_total : 0;
                    });
                    $totalBudget = array_sum($budgetValues->toArray());
                    $projectBudgetLines = \App\Models\project\ProjectMileStone::where('project_id', $project->id)->get(['amount']);
                    $pbTotals = $projectBudgetLines->map(function($pB){
                        return $pB['amount'];
                    })->toArray();
                    $budgetLinesTotal = array_sum($pbTotals);
                    $unMilestoned = $totalBudget - $budgetLinesTotal;
                @endphp
                @if($unMilestoned > 0)
                    <tr>
                        <td> Non-budgeted</td>
                        <td>{{ numberFormat($unMilestoned) }}</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>


        <h3 class="mt-2" style="font-size: 24px;">3. Budgeted Amount vs. Actual Expense <span>(<b>% of Project: {{numberFormat($project->progress)}}%</b>)</span>
        </h3>
        <div class="table-responsive mb-3">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th style="font-size: 20px;">Quote / PI (Budget)</th>
                    <th style="font-size: 20px;">Budgeted Amount</th>
                    <th style="font-size: 20px;">Actual Cost</th>
                    <th style="font-size: 20px;">Gross Profit (Quoted - Cost)</th>
                    <th style="font-size: 20px;">% Gross Profit</th>
                </tr>
                </thead>
                <tbody>
                @php
                    // aggregate
                    $total_estimate = 0;
                    $total_balance = 0;
                    $expenseTotalBudget = 0;
                @endphp
                @foreach ($project->quotes as $quote)
                    @php
                        $expenseAmount = div_num($totalExpense, $project->quotes->count());
                        $actual_amount = $quote->subtotal;
                        $budgetedAmount = $quote->budget? $quote->budget->items()->sum(DB::raw('round(new_qty*price)')) : 0;
                        $balance = $budgetedAmount - $expenseAmount;
                        // aggregate
                        $total_estimate += $expenseAmount;
                        $total_balance += $balance;
                        $expenseTotalBudget += $budgetedAmount;
                    @endphp
                    <tr>
                        <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                        <td>{{ numberFormat($budgetedAmount) }}</td>
                        <td>{{ numberFormat($expenseAmount) }}</td>
                        <td>{{ numberFormat($balance) }}</td>
                        <td>{{ round(div_num($balance, $budgetedAmount) * 100) }} %</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-size: 20px;"><b>Totals</b></td>
                    <td style="font-size: 20px;"><b>{{ numberFormat($totalBudget) }} </b></td>
                    <td style="font-size: 20px;"><b>{{ numberFormat($total_estimate) }} </b></td>
                    <td style="font-size: 20px;"><b>{{ numberFormat($total_balance) }} </b></td>
                    <td style="font-size: 20px;"><b>{{ round(div_num($total_balance, $expenseTotalBudget) * 100) }}
                            % </b>
                    </td>
                </tr>
                </tbody>
            </table>


            <div class="card radius-8 col-lg-12">
                <div class="card-content">
                    <div class="card-body">
                        <div class="bar-chart-container row">

                            <div class="col-12 col-lg-6">
                                <p class="ml-6 card-title"> Budgeted Amount vs. Actual Expense </p>
                                <canvas id="quoteVsJobExpenseChart"></canvas>
                            </div>

                            <div class="col-12 col-lg-6">
                                <p class="ml-6 card-title"> Budgeted Amount vs. Actual Expense </p>
                                <canvas id="quoteVsJobExpensePieChart" style="max-height: 400px;"></canvas>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <h4>3.1 Actual Expense per Budget Line <span>(<b>% of Project: {{numberFormat($project->progress)}}%</b>)</span></h4>
        <div class="table-responsive mb-1">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th style="font-size: 20px;">Budget Line</th>
                    <th style="font-size: 20px;">Expenditure</th>
                    <th style="font-size: 20px;">Expenditure Percentage</th>
                    <th style="font-size: 20px;">Work Progress (%)</th>
                    <th style="font-size: 20px;">Difference (%)</th>
                </tr>
                </thead>
                <tbody>
                @foreach($expensesByMilestone as $epm => $expenditure)
                    @php
                        $milestone = \App\Models\project\ProjectMileStone::where('project_id', $project->id)
                            ->where('name', $epm)
                            ->where('name', '!=', 'No Budget Line Selected')
                            ->first();
                        $expense_amount = optional($milestone)->amount - optional($milestone)->balance;

                        // Ensure `amount` is not zero or null to avoid division by zero
                        if (optional($milestone)->amount > 0) {
                            $expense_percent = ($expense_amount / optional($milestone)->amount) * 100;
                        } else {
                            $expense_percent = 0; // Default value when division isn't possible
                        }

                        $diff = $expense_percent - floatval(@$milestone->milestone_completion);
                    @endphp
                    <tr>
                        <td>{{ $epm }}</td>
                        <td>{{ numberFormat($expenditure) }}</td>
                        <td>{{ numberFormat($expense_percent) }}%</td>
                        <td>{{ optional($milestone)->milestone_completion }}</td>
                        <td>{{ numberFormat($diff) }}%</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-size: 20px;"><b>Total</b></td>
                    <td style="font-size: 18px;">
                        <b>{{ numberFormat(sprintf("%.2f", array_sum($expensesByMilestone))) }}</b></td>
                    <td style="font-size: 18px;"><b> {{numberFormat($project->progress)}}% </b></td>
                </tr>
                </tbody>
            </table>
        </div>



        <!-- direct purchase and purchase order expense -->
        <h3 class="mt-2" style="font-size: 24px;">4. Quotation/Proforma Invoice Amount vs. Actual Expense <span>(<b>% of Project: {{numberFormat($project->progress)}}%</b>)</span>
        </h3>
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
                        $expense_amount = div_num($totalExpense, $project->quotes->count());
                        $actual_amount = $quote->subtotal;
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


            <div class="card radius-8 col-lg-12">
                <div class="card-content">
                    <div class="card-body">
                        <div class="bar-chart-container row">

                            <div class="col-12 col-lg-6">
                                <p class="ml-6 card-title"> Quotation/Proforma Invoice Amount vs. Actual Expense </p>
                                <canvas id="gpTable4Chart"></canvas>
                            </div>

                            <div class="col-12 col-lg-6">
                                <p class="ml-6 card-title"> Quotation/Proforma Invoice Amount vs. Actual Expense </p>
                                <canvas id="gpTable4PieChart" style="max-height: 400px;"></canvas>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- verification -->
        <h5 class="mt-4" style="font-size: 24px;">5. Verified Quote Amount vs. Actual Expense</h5>
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
                        $expense_amount = div_num($totalExpense, $project->quotes->count());
                        $actual_amount = +$quote->verified_amount;
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

            <div class="card radius-8 col-lg-12">
                <div class="card-content">
                    <div class="card-body">
                        <div class="bar-chart-container row">

                            <div class="col-12 col-lg-6">
                                <p class="ml-6 card-title"> Verified Quote Amount vs. Actual Expense </p>
                                <canvas id="verifiedVsActualExpenseChart"></canvas>
                            </div>

                            <div class="col-12 col-lg-6">
                                <p class="ml-6 card-title"> Verified Quote Amount vs. Actual Expense </p>
                                <canvas id="verifiedVsActualExpensePieChart" style="max-height: 400px;"></canvas>
                            </div>

                        </div>
                        {{--                                    </div>--}}
                    </div>
                </div>
            </div>
        </div>


        @php
            $invoices = \App\Models\invoice\Invoice::whereHas('quotes', function($q) use ($project) {
                $q->whereHas('project', function($q) use ($project) {
                    $q->where('projects.id', $project->id);
                });
            })->orWhereHas('project', fn($q) => $q->where('projects.id', $project->id))
            ->get()
            ->map(function($inv){
                return [
                    'id' => $inv->id,
                    'tid' => gen4tid('INV-', $inv->tid),
                    'subtotal' => $inv->subtotal,
                    'total' => $inv->total
                ];
            });
        @endphp

                <!-- verification -->
        <h5 class="mt-4" style="font-size: 24px;">6. Invoiced Amount vs Actual Expense</h5>
        <div class="table-responsive mb-3">
            <table class="table table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th style="font-size: 20px;">Invoiced Amounts</th>
                    <th style="font-size: 20px;">Expense Amounts</th>
                </tr>
                </thead>
                <tbody>

                <tr>
                    <td>
                        <table class="table table-striped table-bordered zero-configuration" cellspacing="0"
                               width="100%">
                            <thead>
                            <tr>
                                <th style="font-size: 20px;">Invoice Number</th>
                                <th style="font-size: 20px;">Net Amount (P&L)</th>
                                <th style="font-size: 20px;">Gross Amount</th>
                                <th style="font-size: 20px;">Gross Profit</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($invoices as $inv)
                                <tr>
                                    <td>
                                        <a class="font-weight-bold" href="{{route('biller.invoices.show', $inv['id'])}}"
                                           target="_blank">{{$inv['tid']}}</a>
                                    </td>
                                    <td>{{numberFormat($inv['subtotal'])}}</td>
                                    <td>{{numberFormat($inv['total'])}}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td style="font-size: 20px;"><b>Totals</b></td>
                                <td style="font-size: 20px;"><b>{{ numberFormat($invoices->sum('subtotal')) }}</b></td>
                                <td style="font-size: 20px;"><b>{{ numberFormat($invoices->sum('total')) }}</b></td>
                                <td style="font-size: 20px;">
                                    <!-- <b>{{ empty($invoices->sum('total')) || $invoices->sum('total') == 0 ? '' : numberFormat(bcmul(bcdiv($invoices->sum('total') - $total_estimate, $invoices->sum('total'), 4), 100, 2)) }}%</b> -->
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>

                    <td>
                        <table class="table table-striped table-bordered zero-configuration" cellspacing="0"
                               width="100%">
                            <thead>
                            <tr>
                                <th style="font-size: 20px;">Quote / PI</th>
                                <th style="font-size: 20px;">Actual Cost</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->quotes as $quote)
                                    @php
                                        $expense_amount = div_num($totalExpense, $project->quotes->count());
                                    @endphp
                                    <tr>
                                        <td>{{  gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                                        <td>{{ numberFormat($expense_amount) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="font-size: 20px;"><b>Totals</b></td>
                                    <td style="font-size: 20px;"><b>{{ numberFormat($total_estimate) }} </b></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


@section('after-styles')
    {!! Html::style('core/app-assets/vendors/css/charts/morris.css') !!}
@endsection

@section('extra-scripts')
    {{ Html::script('core/app-assets/vendors/js/charts/raphael-min.js') }}
    {{ Html::script('core/app-assets/vendors/js/charts/morris.min.js') }}
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        Chart.register(ChartDataLabels);


        $(document).ready(function () {

            drawQuoteVsJobExpenseChart();
            drawQuoteVsJobExpensePieChart();

            drawVerifiedVsActualExpenseChart();
            drawVerifiedVsActualExpensePieChart();

            drawGpTable4Chart();
            drawGpTable4PieChart();
        });

        const drawGpTable4Chart = () => {

            let chartData = @json($gpTable4Data);

            var ctx = $("#gpTable4Chart");

            var chart1 = new Chart(ctx, {
                type: 'bar',

                data: {
                    labels: chartData.tid,
                    datasets: [
                        {
                            label: "Quoted Amount",
                            data: chartData.actual,
                            // tension: 0.1,
                            backgroundColor: 'rgba(54, 162, 235)',
                            borderColor: 'rgba(54, 162, 235)',
                            // type: 'line',
                            order: 1
                        },
                        {
                            label: "Expense",
                            data: chartData.expense,
                            tension: 0.1,
                            backgroundColor: 'rgba(255, 99, 132)',
                            borderColor: 'rgba(255, 99, 132)',
                            order: 2
                        },
                        {
                            label: chartData.profit > 0 ? "Profit" : "Loss",
                            data: chartData.profit,
                            tension: 0.1,
                            backgroundColor: chartData.profit > 0 ? 'rgb(82,178,56)' : 'red',
                            borderColor: chartData.profit > 0 ? 'rgb(82,178,56)' : 'red',
                            order: 3
                        },
                    ]
                },
                options: {
                    datasets: {
                        bar: {
                            borderRadius: 6,
                            borderSkipped: 'bottom',
                        }
                    },
                    scales: {
                        x: {  // Use 'x' instead of 'xAxes'
                            title: {  // Use 'title' instead of 'scaleLabel'
                                display: true,
                                text: 'Quote Number'
                            }
                        },
                        y: {  // Use 'y' instead of 'yAxes'
                            title: {  // Use 'title' instead of 'scaleLabel'
                                display: true,
                                text: 'Value'
                            },
                            ticks: {
                                beginAtZero: true,
                                callback: function (value, index, values) {  // Use 'callback' instead of 'userCallback'
                                    value = value.toString();
                                    value = value.split(/(?=(?:...)*$)/);
                                    value = value.join(',');
                                    return value;
                                }
                            }
                        }
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            title: function (tooltipItems, data) {
                                //Return value for title
                                return tooltipItems[0].xLabel;
                            },
                            label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                                //Return value for label
                                return tooltipItems.yLabel;
                            }
                        }
                    },
                    responsive: true,
                    transitions: {
                        show: {
                            animations: {
                                x: {
                                    from: 0
                                },
                                y: {
                                    from: 0
                                }
                            }
                        },
                        hide: {
                            animations: {
                                x: {
                                    to: 0
                                },
                                y: {
                                    to: 0
                                }
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            color: 'black',
                            anchor: 'top',
                            align: 'center',
                            labels: {
                                title: {
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                }
                            },
                            formatter: (value, context) => {
                                // Use Intl.NumberFormat to format the value with commas
                                return new Intl.NumberFormat('en-US').format(value);
                            },
                        }
                    }
                }
            });


        }


        const drawGpTable4PieChart = () => {


            let chartData = @json($gpTable4Data);

            var ctx = $("#gpTable4PieChart");

            let profit = 0;
            let loss = 0;

            if (chartData.profit > 0) profit = Math.abs(chartData.profit);
            else loss = Math.abs(chartData.profit);

            var chart1 = new Chart(ctx, {
                type: 'pie',

                data: {
                    labels: ["Quoted Amount", 'Actual Expense', 'Profit', 'Loss'],
                    datasets: [
                        {
                            data: [chartData.actual, chartData.expense, profit, loss],
                            backgroundColor: ['rgba(54, 162, 235)', 'rgba(255, 99, 132)', 'rgb(82,178,56)', 'red'],
                        },
                    ]
                },
                options: {
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            title: function (tooltipItems, data) {
                                //Return value for title
                                return tooltipItems[0].xLabel;
                            },
                            label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                                //Return value for label
                                return tooltipItems.yLabel;
                            }
                        }
                    },
                    responsive: true,
                    transitions: {
                        show: {
                            animations: {
                                x: {
                                    from: 0
                                },
                                y: {
                                    from: 0
                                }
                            }
                        },
                        hide: {
                            animations: {
                                x: {
                                    to: 0
                                },
                                y: {
                                    to: 0
                                }
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            color: 'black',
                            anchor: 'top',
                            align: 'center',
                            labels: {
                                title: {
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                }
                            },
                            formatter: (value, context) => {
                               
                                // Get total sum of the dataset
                                const total = parseInt(chartData.actual) + parseInt(chartData.expense) + Math.abs(parseInt(chartData.profit));

                                console.table({total: total});

                                // Calculate the percentage
                                const percentage = ((value / total) * 100).toFixed(1);

                                // Format the value with commas
                                const formattedValue = new Intl.NumberFormat('en-US').format(value);

                                // Return formatted value with percentage
                                return `${formattedValue} (${percentage}%)`;
                            },
                        }
                    }
                }
            });



        }



        const drawQuoteVsJobExpenseChart = () => {

            let qtVsExpData = @json($quoteVsJobExpense);

            var ctx = $("#quoteVsJobExpenseChart");

            var chart1 = new Chart(ctx, {
                type: 'bar',

                data: {
                    labels: qtVsExpData.tid,
                    datasets: [
                        {
                            label: "Quote Budget",
                            data: qtVsExpData.budget,
                            // tension: 0.1,
                            backgroundColor: 'rgba(54, 162, 235)',
                            borderColor: 'rgba(54, 162, 235)',
                            // type: 'line',
                            order: 1
                        },
                        {
                            label: "Expense",
                            data: qtVsExpData.expense,
                            tension: 0.1,
                            backgroundColor: 'rgba(255, 99, 132)',
                            borderColor: 'rgba(255, 99, 132)',
                            order: 2
                        },
                        {
                            label: (qtVsExpData.balance > 0) ? "Profit" : "Loss",
                            data: qtVsExpData.balance,
                            tension: 0.1,
                            backgroundColor: (qtVsExpData.balance > 0) ? 'rgb(82,178,56)' : 'red',
                            borderColor: (qtVsExpData.balance > 0) ? 'rgb(82,178,56)' : 'red',
                            order: 3
                        },
                    ]
                },
                options: {
                    datasets: {
                        bar: {
                            borderRadius: 6,
                            borderSkipped: 'bottom',
                        }
                    },
                    scales: {
                        x: {  // Use 'x' instead of 'xAxes'
                            title: {  // Use 'title' instead of 'scaleLabel'
                                display: true,
                                text: 'Quote Number'
                            }
                        },
                        y: {  // Use 'y' instead of 'yAxes'
                            title: {  // Use 'title' instead of 'scaleLabel'
                                display: true,
                                text: 'Value'
                            },
                            ticks: {
                                beginAtZero: true,
                                callback: function (value, index, values) {  // Use 'callback' instead of 'userCallback'
                                    value = value.toString();
                                    value = value.split(/(?=(?:...)*$)/);
                                    value = value.join(',');
                                    return value;
                                }
                            }
                        }
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            title: function (tooltipItems, data) {
                                //Return value for title
                                return tooltipItems[0].xLabel;
                            },
                            label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                                //Return value for label
                                return tooltipItems.yLabel;
                            }
                        }
                    },
                    responsive: true,
                    transitions: {
                        show: {
                            animations: {
                                x: {
                                    from: 0
                                },
                                y: {
                                    from: 0
                                }
                            }
                        },
                        hide: {
                            animations: {
                                x: {
                                    to: 0
                                },
                                y: {
                                    to: 0
                                }
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            color: 'black',
                            anchor: 'top',
                            align: 'center',
                            labels: {
                                title: {
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                }
                            },
                            formatter: (value, context) => {
                                // Use Intl.NumberFormat to format the value with commas
                                return new Intl.NumberFormat('en-US').format(value);
                            },
                        }
                    }
                }
            });


        }


        const drawQuoteVsJobExpensePieChart = () => {

            let qtVsExpData = @json($quoteVsJobExpense);

            var ctx = $("#quoteVsJobExpensePieChart");

            let profit = 0;
            let loss = 0;

            if (qtVsExpData.balance > 0) profit = Math.abs(qtVsExpData.balance);
            else loss = Math.abs(qtVsExpData.balance);

            var chart1 = new Chart(ctx, {
                type: 'pie',

                data: {
                    labels: ["Budgeted Amount", 'Actual Expense', 'Profit', 'Loss'],
                    datasets: [
                        {
                            data: [qtVsExpData.budget, qtVsExpData.expense, profit, loss],
                            backgroundColor: ['rgba(54, 162, 235)', 'rgba(255, 99, 132)', 'rgb(82,178,56)', 'red'],
                        },
                    ]
                },
                options: {
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            title: function (tooltipItems, data) {
                                //Return value for title
                                return tooltipItems[0].xLabel;
                            },
                            label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                                //Return value for label
                                return tooltipItems.yLabel;
                            }
                        }
                    },
                    responsive: true,
                    transitions: {
                        show: {
                            animations: {
                                x: {
                                    from: 0
                                },
                                y: {
                                    from: 0
                                }
                            }
                        },
                        hide: {
                            animations: {
                                x: {
                                    to: 0
                                },
                                y: {
                                    to: 0
                                }
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            color: 'black',
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            formatter: (value, context) => {
                                
                                // Get total sum of the dataset
                                const total = parseInt(qtVsExpData.budget) + parseInt(qtVsExpData.expense) + Math.abs(parseInt(qtVsExpData.balance));

                                console.table({total: total});

                                // Calculate the percentage
                                const percentage = ((value / total) * 100).toFixed(1);

                                // Format the value with commas
                                const formattedValue = new Intl.NumberFormat('en-US').format(value);

                                // Return formatted value with percentage
                                return `${formattedValue} (${percentage}%)`;
                            }
                        }
                    }
                }
            });


        }


        const drawVerifiedVsActualExpenseChart = () => {

            let verifiedVsExpData = @json($verifiedVsActualExpense);

            var ctx = $("#verifiedVsActualExpenseChart");

            var chart1 = new Chart(ctx, {
                type: 'bar',

                data: {
                    labels: verifiedVsExpData.tid,
                    datasets: [
                        {
                            label: "Verified Amount",
                            data: verifiedVsExpData.verified,
                            // tension: 0.1,
                            backgroundColor: 'rgba(54, 162, 235)',
                            borderColor: 'rgba(54, 162, 235)',
                            // type: 'line',
                            order: 1
                        },
                        {
                            label: "Expense",
                            data: verifiedVsExpData.expense,
                            tension: 0.1,
                            backgroundColor: 'rgba(255, 99, 132)',
                            borderColor: 'rgba(255, 99, 132)',
                            order: 2
                        },
                        {
                            label: verifiedVsExpData.balance > 0 ? "Profit" : "Loss",
                            data: verifiedVsExpData.balance,
                            tension: 0.1,
                            backgroundColor: verifiedVsExpData.balance > 0 ? 'rgb(82,178,56)' : 'red',
                            borderColor: verifiedVsExpData.balance > 0 ? 'rgb(82,178,56)' : 'red',
                            order: 3
                        },
                    ]
                },
                options: {
                    datasets: {
                        bar: {
                            borderRadius: 6,
                            borderSkipped: 'bottom',
                        }
                    },
                    scales: {
                        x: {  // Use 'x' instead of 'xAxes'
                            title: {  // Use 'title' instead of 'scaleLabel'
                                display: true,
                                text: 'Quote Number'
                            }
                        },
                        y: {  // Use 'y' instead of 'yAxes'
                            title: {  // Use 'title' instead of 'scaleLabel'
                                display: true,
                                text: 'Value'
                            },
                            ticks: {
                                beginAtZero: true,
                                callback: function (value, index, values) {  // Use 'callback' instead of 'userCallback'
                                    value = value.toString();
                                    value = value.split(/(?=(?:...)*$)/);
                                    value = value.join(',');
                                    return value;
                                }
                            }
                        }
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            title: function (tooltipItems, data) {
                                //Return value for title
                                return tooltipItems[0].xLabel;
                            },
                            label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                                //Return value for label
                                return tooltipItems.yLabel;
                            }
                        }
                    },
                    responsive: true,
                    transitions: {
                        show: {
                            animations: {
                                x: {
                                    from: 0
                                },
                                y: {
                                    from: 0
                                }
                            }
                        },
                        hide: {
                            animations: {
                                x: {
                                    to: 0
                                },
                                y: {
                                    to: 0
                                }
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            color: 'black',
                            anchor: 'top',
                            align: 'center',
                            labels: {
                                title: {
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                }
                            },
                            formatter: (value, context) => {
                                // Use Intl.NumberFormat to format the value with commas
                                return new Intl.NumberFormat('en-US').format(value);
                            },
                        }
                    }
                }
            });


        }


        const drawVerifiedVsActualExpensePieChart = () => {

            let verifiedVsExpData = @json($verifiedVsActualExpense);

            var ctx = $("#verifiedVsActualExpensePieChart");

            let profit = 0;
            let loss = 0;

            if (verifiedVsExpData.balance > 0) profit = Math.abs(verifiedVsExpData.balance);
            else loss = Math.abs(verifiedVsExpData.balance);

            var chart1 = new Chart(ctx, {
                type: 'pie',

                data: {
                    labels: ["Budgeted Amount", 'Actual Expense', 'Profit', 'Loss'],
                    datasets: [
                        {
                            data: [verifiedVsExpData.verified, verifiedVsExpData.expense, profit, loss],
                            backgroundColor: ['rgba(54, 162, 235)', 'rgba(255, 99, 132)', 'rgb(82,178,56)', 'red'],
                        },
                    ]
                },
                options: {
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            title: function (tooltipItems, data) {
                                //Return value for title
                                return tooltipItems[0].xLabel;
                            },
                            label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                                //Return value for label
                                return tooltipItems.yLabel;
                            }
                        }
                    },
                    responsive: true,
                    transitions: {
                        show: {
                            animations: {
                                x: {
                                    from: 0
                                },
                                y: {
                                    from: 0
                                }
                            }
                        },
                        hide: {
                            animations: {
                                x: {
                                    to: 0
                                },
                                y: {
                                    to: 0
                                }
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            color: 'black',
                            anchor: 'top',
                            align: 'center',
                            labels: {
                                title: {
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                }
                            },
                            formatter: (value, context) => {
                               
                                // Get total sum of the dataset
                                const total = parseInt(verifiedVsExpData.verified) + parseInt(verifiedVsExpData.expense) + Math.abs(parseInt(verifiedVsExpData.balance));

                                console.table({total: total});

                                // Calculate the percentage
                                const percentage = ((value / total) * 100).toFixed(1);

                                // Format the value with commas
                                const formattedValue = new Intl.NumberFormat('en-US').format(value);

                                // Return formatted value with percentage
                                return `${formattedValue} (${percentage}%)`;
                            },
                        }
                    }
                }
            });


        };


    </script>

@endsection