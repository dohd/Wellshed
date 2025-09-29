@php 
	use App\Models\CasualLabourersRemunerations\CasualLabourersRemuneration;
	use App\Models\quote\Quote; 
@endphp
<html>
<head>
	<title>Project Gross Profit</title>
	<style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}

		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
			width: 100%;
			border-collapse: collapse;
		}

		table thead td {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid black;
			font-variant: small-caps;
			padding: 8px; /* Add padding for better spacing */
		}

		td {
			vertical-align: top;
			padding: 8px; /* Add padding for better spacing */
		}

		.bullets {
			width: 8px;
		}

		.items {
			border-bottom: 0.1mm solid black;
			font-size: 10pt;
		}

		.items td {
			border-left: 0.1mm solid black;
			border-right: 0.1mm solid black;
		}

		.items tr:hover {
			background-color: #f2f2f2; /* Add hover effect */
		}

		.align-r {
			text-align: right;
		}

		.align-c {
			text-align: center;
		}

		.bd {
			border: 1px solid black;
		}

		.bd-t {
			border-top: 1px solid;
		}

		.ref {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
			border-collapse: collapse;
		}

		.ref tr td {
			border: 0.1mm solid #888888;
			padding: 8px; /* Add padding for better spacing */
		}

		.ref tr:nth-child(2) td {
			width: 50%;
		}

		.customer-dt {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
		}

		.customer-dt tr td:nth-child(1) {
			border: 0.1mm solid #888888;
		}

		.customer-dt tr td:nth-child(3) {
			border: 0.1mm solid #888888;
		}

		.customer-dt-title {
			font-size: 7pt;
			color: #555555;
			font-family: sans;
		}

		.doc-title-td {
			text-align: center;
			width: 100%;
		}

		.doc-title {
			font-size: 15pt;
			color: #0f4d9b;
		}

		.doc-table {
			font-size: 10pt;
			margin-top: 5px;
			width: 100%;
		}

		.header-table {
			width: 100%;
			border-bottom: 0.8mm solid #0f4d9b;
		}

		.header-table tr td:first-child {
			color: #0f4d9b;
			font-size: 9pt;
			width: 60%;
			text-align: left;
		}

		.address {
			color: #0f4d9b;
			font-size: 10pt;
			width: 40%;
			text-align: right;
		}

		.header-table-text {
			color: #0f4d9b;
			font-size: 9pt;
			margin: 0;
		}

		.header-table-child {
			color: #0f4d9b;
			font-size: 8pt;
		}

		.header-table-child tr:nth-child(2) td {
			font-size: 9pt;
			padding-left: 50px;
		}

		.footer {
			font-size: 9pt;
			text-align: center;
			margin-top: 20px; /* Add margin for spacing */
		}

		p {
			text-align: justify;
		}

		h2 {

			margin-top: 30px;
		}


		table tbody tr:nth-child(odd) {
			background-color: #f2f2f2; /* Light gray for odd rows */
		}

		table tbody tr:nth-child(even) {
			background-color: #ffffff; /* White for even rows */
		}
	</style>
</head>

<body>
<htmlpagefooter name="myfooter">
	<div class="footer">
        @php $image = "img/company/{$company->footer}" @endphp
        <img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->path($image) : '' }}" style="object-fit:contain" width="100%">
		Page {PAGENO} of {nb}
	</div>
</htmlpagefooter>
<sethtmlpagefooter name="myfooter" value="on"/>

<table class="header-table">
	<tr>
		<td>
	        @php $image = "img/company/{$company->logo}"; //dd(is_file(Storage::disk('public')->path($image)), Storage::disk('public')->path($image)); @endphp
	        <img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->path($image) : '' }}" style="object-fit:contain" width="100%">
		</td>
	</tr>
</table>

<div class="container">
	<h1 style="padding-bottom: 16px; font-size: 30px"> {{ gen4tid('PRJ-', $project->tid) . ' | Project Gross Profit Report' }}</h1>
	<h1 style="padding-bottom: 16px; font-size: 30px"> {{ @$project->customer->company }} - {{ @$project->branch->name }}</h1>

	@php
		$creator = App\Models\Access\User\User::withoutGlobalScopes()->find($project->user_id);
	@endphp

	<h3>Project Created By: <span
				style="font-size: 20px;"> <b>{{ optional($creator)->first_name . ' ' . optional($creator)->last_name }}</b></span>
	</h3>


	<div>
		<h3 style="font-size: 24px;">1. Quotation / Proforma Invoice Amount vs. Estimated Expense</h3>
		<div class="mb-4">
			<table class="items">
				<thead class="header-table">
				<tr>
					<th style="font-size: 20px; border: 1px solid black;">Quote / PI</th>
					<th style="font-size: 20px; border: 1px solid black;">Quoted Amount</th>
					<th style="font-size: 20px; border: 1px solid black;">Est. Cost Amount</th>
					<th style="font-size: 20px; border: 1px solid black;">Gross Profit (Quoted - Est. Cost)</th>
					<th style="font-size: 20px; border: 1px solid black;">% Gross Profit</th>
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
					<td style="font-size: 20px; border: 1px solid black;"><b>Totals</b></td>
					<td style="font-size: 18px; border: 1px solid black;"><b>{{ numberFormat($total_estimate) }}</b>
					</td>
					<td style="font-size: 18px; border: 1px solid black;"><b>{{ numberFormat($total_actual) }}</b></td>
					<td style="font-size: 18px; border: 1px solid black;"><b>{{ numberFormat($total_balance) }}</b></td>
					<td style="font-size: 18px; border: 1px solid black;">
						<b>{{ round(div_num($total_balance, $total_estimate) * 100) }} %</b>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<!--  budgeting -->
		<h3 class="mb-1" style="font-size: 24px; padding-top: 20px">2. Quotation / Proforma Invoice Amount vs. Budgeted
			Expense</h3>
		<div class="mb-3">
			<table class="items">
				<thead>
				<tr>
					<th style="font-size: 20px; border: 1px solid black;">Quote / PI (Budget)</th>
					<th style="font-size: 20px; border: 1px solid black;">Quoted Amount</th>
					<th style="font-size: 20px; border: 1px solid black;">Budget</th>
					<th style="font-size: 20px; border: 1px solid black;">Gross Profit (Quoted - Budget)</th>
					<th style="font-size: 20px; border: 1px solid black;">% Gross Profit</th>
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
					<td style="font-size: 20px; border: 1px solid black;"><b>Totals</b></td>
					<td style="font-size: 18px; border: 1px solid black;"><b>{{ numberFormat($total_actual) }}</b></td>
					<td style="font-size: 18px; border: 1px solid black;"><b>{{ numberFormat($total_estimate) }}</b>
					</td>
					<td style="font-size: 18px; border: 1px solid black;"><b>{{ numberFormat($total_balance) }}</b></td>
					<td style="font-size: 18px; border: 1px solid black;">
						<b>{{ round(div_num($total_balance, $total_actual) * 100) }} %</b></td>
				</tr>
				</tbody>
			</table>
		</div>

		<h3 style="padding-top: 15px">2.1 Budget Lines <span>(<b>% of Project: {{numberFormat($project->progress)}}%</b>)</span>
		</h3>
		<div>
			<table class="items">
				<thead>
				<tr>
					{{--                    <th style="font-size: 20px;">#</th>--}}
					<th style="font-size: 20px; border: 1px solid black;">Budget Line</th>
					<th style="font-size: 20px; border: 1px solid black;">Amount</th>
					<th style="font-size: 20px; border: 1px solid black;">% of Milestone</th>
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
					<td style="font-size: 20px; border: 1px solid black;"><b>Total</b></td>
					<td style="font-size: 18px; border: 1px solid black;">
						<b>{{ numberFormat(sprintf("%.2f", $projectBudgetLines->pluck('amount')->sum())) }}</b>
					</td>
					<td style="font-size: 18px; border: 1px solid black;"></td>


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
						<td style="font-size: 18px; border: 1px solid black;"> Non-budgeted</td>
						<td colspan="2"
							style="font-size: 18px; border: 1px solid black;">{{ numberFormat($unMilestoned) }}</td>
					</tr>
				@endif
				</tbody>
			</table>
		</div>


		<h3 class="mt-2" style="font-size: 24px; padding-top: 20px">3. Budgeted Amount vs. Actual Expense</h3>
		<div class="">
			<table class="items">
				<thead>
				<tr>
					<th style="font-size: 20px; border: 1px solid black;">Quote / PI (Budget)</th>
					<th style="font-size: 20px; border: 1px solid black;">Budgeted Amount</th>
					<th style="font-size: 20px; border: 1px solid black;">Actual Cost</th>
					<th style="font-size: 20px; border: 1px solid black;">Gross Profit (Quoted - Cost)</th>
					<th style="font-size: 20px; border: 1px solid black;">% Gross Profit</th>
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
					<td style="font-size: 20px; border: 1px solid black;"><b>Totals</b></td>
					<td style="font-size: 20px; border: 1px solid black;"><b>{{ numberFormat($totalBudget) }} </b></td>
					<td style="font-size: 20px; border: 1px solid black;"><b>{{ numberFormat($total_estimate) }} </b>
					</td>
					<td style="font-size: 20px; border: 1px solid black;"><b>{{ numberFormat($total_balance) }} </b>
					</td>
					<td style="font-size: 20px; border: 1px solid black;">
						<b>{{ round(div_num($total_balance, $expenseTotalBudget) * 100) }} % </b>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<h3 style="padding-top: 15px">3.1 Expenditure per Budget Line <span>(<b>% of Project: {{numberFormat($project->progress)}}%</b>)</span>
		</h3>

		<div>
			<table class="items">
				<thead>
				<tr>
					{{--                    <th style="font-size: 20px;">#</th>--}}
					<th style="font-size: 20px; border: 1px solid black;">Budget Line</th>
					<th style="font-size: 20px; border: 1px solid black;">Expenditure</th>
					<th style="font-size: 20px; border: 1px solid black;">Work Progress (%)</th>
				</tr>
				</thead>
				<tbody>


				@foreach($expensesByMilestone as $epm => $expenditure)

					@php

						$milestone = \App\Models\project\ProjectMileStone::where('project_id', $project->id)
                            ->where('name', $epm)
                            ->where('name', '!=', 'No Budget Line Selected')
                            ->first();

					@endphp

					<tr>

						<td>{{ $epm }}</td>
						<td>{{ numberFormat($expenditure) }}</td>
						<td>{{ optional($milestone)->milestone_completion }}</td>
					</tr>
				@endforeach
				<tr>
					<td style="font-size: 20px; border: 1px solid black;"><b>Total</b></td>
					<td style="font-size: 18px; border: 1px solid black;">
						<b>{{ numberFormat(sprintf("%.2f", array_sum($expensesByMilestone))) }}</b></td>
					<td style="font-size: 18px; border: 1px solid black;"><b> {{numberFormat($project->progress)}}% </b>
					</td>
				</tr>
				</tbody>
			</table>
		</div>


		<!-- direct purchase and purchase order expense -->
		<h3 class="mt-2" style="font-size: 24px; padding-top: 20px">4. Quotation/Proforma Invoice Amount vs. Actual Expense
			<span>(<b>% of Project: {{numberFormat($project->progress)}}%</b>)</span></h3>
		<div class="">
			<table class="items">
				<thead>
				<tr>
					<th style="font-size: 20px; border: 1px solid black;">Quote / PI (Budget)</th>
					<th style="font-size: 20px; border: 1px solid black;">Quoted Amount</th>
					<th style="font-size: 20px; border: 1px solid black;">Actual Cost</th>
					<th style="font-size: 20px; border: 1px solid black;">Gross Profit (Quoted - Cost)</th>
					<th style="font-size: 20px; border: 1px solid black;">% Gross Profit</th>
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
					<td style="font-size: 20px; border: 1px solid black;"><b>Totals</b></td>
					<td style="font-size: 20px; border: 1px solid black;"><b>{{ numberFormat($total_actual) }} </b></td>
					<td style="font-size: 20px; border: 1px solid black;"><b>{{ numberFormat($total_estimate) }} </b></td>
					<td style="font-size: 20px; border: 1px solid black;"><b>{{ numberFormat($total_balance) }} </b></td>
					<td style="font-size: 20px; border: 1px solid black;"><b>{{ round(div_num($total_balance, $total_actual) * 100) }} %</b></td>
				</tr>
				</tbody>
			</table>
		</div>


		<!-- verification -->
		<h5 class="mt-4" style="font-size: 24px;">5. Verified Quoted Amount vs. Actual Expense</h5>
		<div>
			<table class="items">
				<thead>
				<tr>
					<th style="font-size: 20px; border: 1px solid black;">Quote / PI (Budget)</th>
					<th style="font-size: 20px; border: 1px solid black;">Verified Amount</th>
					<th style="font-size: 20px; border: 1px solid black;">Actual Cost</th>
					<th style="font-size: 20px; border: 1px solid black;">Profit (Verified - Cost)</th>
					<th style="font-size: 20px; border: 1px solid black;">% Gross Profit</th>
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
					<td style="font-size: 20px; border: 1px solid black;"><b>Totals</b></td>
					<td style="font-size: 18px; border: 1px solid black;"><b>{{ numberFormat($total_actual) }} </b></td>
					<td style="font-size: 18px; border: 1px solid black;"><b>{{ numberFormat($total_estimate) }} </b>
					</td>
					<td style="font-size: 18px; border: 1px solid black;"><b>{{ numberFormat($total_balance) }} </b>
					</td>
					<td style="font-size: 18px; border: 1px solid black;">
						<b>{{ round(div_num($total_balance, $total_actual) * 100) }} % </b>
					</td>
				</tr>
				</tbody>
			</table>

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
		<h5 class="mt-4" style="font-size: 24px; padding-top: 15px">6. Invoiced Amount vs Actual Expense</h5>
		<div>
			<table class="items">
				<thead>
				<tr>
					<th style="font-size: 20px; border: 1px solid black;">Invoiced Amounts</th>
					<th style="font-size: 20px; border: 1px solid black;">Expense Amounts</th>
				</tr>
				</thead>
				<tbody>

				<tr>
					<td style="border: 1px solid black;">
						<table class="table table-striped table-bordered zero-configuration" cellspacing="0"
							   width="100%">
							<thead>
							<tr>
								<th style="font-size: 20px; border: 1px solid black;">Invoice Number</th>
								<th style="font-size: 20px; border: 1px solid black;">Net Amount (P&L)</th>
								<th style="font-size: 20px; border: 1px solid black;">Gross Amount</th>
								<th style="font-size: 20px; border: 1px solid black;">Gross Profit</th>
							</tr>
							</thead>
							<tbody>
							@foreach($invoices as $inv)
								<tr>
									<td>{{ $inv['tid'] }}</td>
									<td>{{ numberFormat($inv['subtotal']) }}</td>
									<td>{{ numberFormat($inv['total']) }}</td>
									<td></td>
								</tr>
							@endforeach
							<tr>
								<td style="font-size: 20px; border: 1px solid black;"><b>Totals</b></td>
								<td style="font-size: 20px; border: 1px solid black;"><b>{{ numberFormat($invoices->sum('subtotal')) }}</b></td>
								<td style="font-size: 20px; border: 1px solid black;"><b>{{ numberFormat($invoices->sum('total')) }}</b></td>
								<td style="font-size: 20px; border: 1px solid black;">
									<!-- <b>{{ empty($invoices->sum('total')) || $invoices->sum('total') == 0 ? '' : numberFormat(bcmul(bcdiv($invoices->sum('total') - $total_estimate, $invoices->sum('total'), 4), 100, 2)) }}%</b> -->
								</td>
							</tr>
							</tbody>
						</table>
					</td>
					<td style="border: 1px solid black;">
						<table class="items">
							<thead>
							<tr>
								<th style="font-size: 20px; border: 1px solid black;">Quote / PI</th>
								<th style="font-size: 20px; border: 1px solid black;">Actual Cost</th>
							</tr>
							</thead>
							<tbody>
								@foreach ($project->quotes as $quote)
									@php
	                                    $expense_amount = div_num($totalExpense, $project->quotes->count());
	                                @endphp
									<tr>
										<td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>									
										<td>{{ numberFormat($expense_amount) }}</td>
									</tr>
								@endforeach
								<tr>
									<td style="font-size: 20px; border: 1px solid black;"><b>Totals</b></td>
									<td style="font-size: 20px; border: 1px solid black;"><b>{{ numberFormat($total_estimate) }}</b></td>
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
</body>
</html>