<html>
    <head>
        <title>Cash Flow Statement</title>
    </head>
    <style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}
        h5 {
			font-size: 1em;
			font-family: Arial, Helvetica, sans-serif;
			font-weight: bold;
            margin-bottom: .7em;
		}
		p {
			margin: 0pt;
		}
		table.items {
			border: 0.1mm solid #000000;
		}
		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
		}
		td {
			vertical-align: top;
		}
		.items td {
			border-left: 0.1mm solid #000000;
			border-right: 0.1mm solid #000000;
		}
		table thead th {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid #000000;
			font-weight: normal;
		}
		        
        .dotted td {
			border-bottom: dotted 1px black;
		}
		.dottedt th {
			border-bottom: dotted 1px black;
		}

		.footer {
			font-size: 9pt; 
			text-align: center; 
		}
		.table-items {
			font-size: 10pt; 
			border-collapse: collapse;
			height: 700px;
			width: 100%;
		}
	</style>
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div class="footer">Page {PAGENO} of {nb}</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />

    <div style="text-align: center;">
        <h1>{{ auth()->user()->business->cname }}</h1>
        @if ($date_range)
            <h2>Cash Flow Statement: Period from {{ dateFormat($date_range[0]) }} to {{ dateFormat($date_range[1]) }}</h2>
        @else
            <h2>Cash Flow Statement: As at {{ date('d-m-Y') }}</h2>
        @endif
    </div>

    @php
        $operating_sum = $net_income;
        $investing_sum = 0;
        $financing_sum = 0;
    @endphp
    <!-- Operating Activities -->
    <h5>Operating Activities</h5>
    <table class="table table-items" cellpadding="8">
        <thead>
            <tr>
                <th>#</th>
                <th>Account No</th>
                <th>Account</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr class="dotted">
                @for ($k = 0; $k < 2; $k++) <td></td> @endfor
                <td>Net Income</td>
                <td style="text-align: center;"><h3>{{ amountFormat($net_income) }}</h3></td>
            </tr>  
            <tr class="dotted">
                @for ($k = 0; $k < 3; $k++) <td></td> @endfor
                <td style="text-align: center;"><i>Adjustments to reconcile Net Income</i></td>
            </tr>  
            @foreach ($operating_accounts as $i => $account)
                @php
                    $q = $account->transactions()->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates));
                    $system = @$account->account_type_detail->system;
                    $account_type = $account->account_type;
                    $neg_prefix = false;
                    $balance = 0;
                    // non-cash adjustment
                    $ajustment_types = ['depreciation_expense', 'amortization_expense', 'asset_sale_gain', 'asset_sale_loss'];
                    if (in_array($system, $ajustment_types)) {
                        $q1 = clone $q;
                        if (in_array($account_type, ['Asset', 'Expense'])) $balance = $q1->sum(DB::raw('debit - credit'));
                        else $balance = $q1->sum(DB::raw('credit - debit'));
                        $balance = round($balance, 2);
                        // add expenses while subtract revenue
                        if (in_array($system, ['depreciation_expense', 'amortization_expense', 'asset_sale_loss'])) $operating_sum += $balance;
                        elseif ($system == 'asset_sale_gain') $operating_sum -= $balance;
                        if ($system == 'asset_sale_gain') $neg_prefix = true;
                    }
                    // changes in working capital
                    $ajustment_types = ['receivable', 'payable', 'inventory_asset', 'prepaid_expenses', 'accrued_liability', 'payroll_liability', 'credit_card'];
                    if (in_array($system, $ajustment_types)) {
                        $q2 = clone $q;
                        if (in_array($account_type, ['Asset', 'Expense'])) $balance = $q2->sum(DB::raw('debit - credit'));
                        else $balance = $q2->sum(DB::raw('credit - debit'));
                        $balance = round($balance, 2);
                        // subtract increase in asset else add decrease in asset
                        if ($account_type == 'Asset') {
                            if ($balance > 0) $operating_sum -= $balance;
                            else $operating_sum += $balance;
                            if ($balance > 0) $neg_prefix = true;
                        } 
                        // add increase in liability else subtract decrease in asset
                        if ($account_type == 'Liability') {
                            if ($balance > 0) $operating_sum += $balance;
                            else $operating_sum -= $balance;
                            if ($balance < 0) $neg_prefix = true;
                        }                                         
                    }
                    // other operating cash flows (interests and taxes)
                    $ajustment_types = ['interest_expense', 'sale_tax_payable', 'vat_payable'];
                    if (in_array($system, $ajustment_types)) {
                        $q3 = clone $q;
                        if ($account_type == 'Expense') $balance = $q3->sum(DB::raw('debit - credit'));
                        $balance = round($balance, 2);
                        // subtract expenses
                        $operating_sum -= $balance;
                        $neg_prefix = true;
                    }
                    if (!$balance) continue;
                @endphp
                <tr class="dotted">
                    <td>{{ $i+1 }}</td>
                    <td>{{ $account->number }}</td>
                    <td>{{ $account->holder }}</td>
                    <td style="text-align: center;">{{ numberFormat($balance) }}</td>
                </tr>
            @endforeach
            <tr class="dotted">
                @for ($k = 0; $k < 3; $k++) <td></td> @endfor
                <td style="text-align: center;"><h3>{{ amountFormat($operating_sum) }}</h3></td>
            </tr>        
        </tbody>
    </table>  

    <!-- Investing Activities -->
    <h5>Investing Activities</h5>
    <table class="table table-items" cellpadding="8">
        <thead>
            <tr>
                <th>#</th>
                <th>Account No</th>
                <th>Account</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($investing_accounts as $i => $account)
                @php
                    $q = $account->transactions()->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates));
                    $balance = $q->sum(DB::raw('debit - credit'));
                    $balance = round($balance, 2);
                    $investing_sum += $balance;
                @endphp
                <tr class="dotted">
                    <td>{{ $i+1 }}</td>
                    <td>{{ $account->number }}</td>
                    <td>{{ $account->holder }}</td>
                    <td style="text-align: center;">{{ numberFormat($balance) }}</td>
                </tr>
            @endforeach
            <tr class="dotted">
                @for ($k = 0; $k < 3; $k++) <td></td> @endfor
                <td style="text-align: center;"><h3>{{ amountFormat($investing_sum) }}</h3></td>
            </tr>        
        </tbody>
    </table>  

    <!-- Financing Activities -->
    <h5>Financing Activities</h5>
    <table class="table table-items" cellpadding="8">
        <thead>
            <tr>
                <th>#</th>
                <th>Account No</th>
                <th>Account</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($financing_accounts as $i => $account)
                @php
                    $q = $account->transactions()->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates));
                    $system = @$account->account_type_detail->system;
                    $account_type = $account->account_type;
                    $neg_prefix = false;
                    $balance = 0;
                    // cash in adjustment
                    $ajustment_types = ['common_stock', 'preferred_stock', 'bonds_payable', 'notes_payable', 'loan_payable'];
                    if (in_array($system, $ajustment_types)) {
                        $q1 = clone $q;
                        if (in_array($account_type, ['Asset', 'Expense'])) $balance = $q1->sum(DB::raw('debit - credit'));
                        else $balance = $q1->sum(DB::raw('credit - debit'));
                        $balance = round($balance, 2);
                        // add cash in
                        $financing_sum += $balance;
                        if ($system == 'asset_sale_gain') $neg_prefix = true;
                    }
                    // cash out adjustment
                    $ajustment_types = ['treasury_stock', 'dividends_payable',];
                    if (in_array($system, $ajustment_types)) {
                        $q2 = clone $q;
                        if (in_array($account_type, ['Asset', 'Expense'])) $balance = $q1->sum(DB::raw('debit - credit'));
                        else $balance = $q2->sum(DB::raw('credit - debit'));
                        $balance = round($balance, 2);
                        // subtract cash out
                        $financing_sum -= $balance;
                        if ($system == 'asset_sale_gain') $neg_prefix = true;
                    }                                        
                    if (!$balance) continue;
                @endphp
                <tr class="dotted">
                    <td>{{ $i+1 }}</td>
                    <td>{{ $account->number }}</td>
                    <td>{{ $account->holder }}</td>
                    <td style="text-align: center;">{{ numberFormat($balance) }}</td>
                </tr>
            @endforeach
            <tr class="dotted">
                @for ($k = 0; $k < 3; $k++) <td></td> @endfor
                <td style="text-align: center;"><h3>{{ amountFormat($financing_sum) }}</h3></td>
            </tr>        
        </tbody>
    </table>                                

    <h5>Summary</h5>
    <table class="table table-items" cellpadding="8">
        <tbody>
            @php
                $net_increase = $operating_sum + $investing_sum + $financing_sum;
                $ending_cash = $net_increase + $beginning_cash;
            @endphp
            <tr class="dotted">
                <td><b>Net Cash Increase</b></td>
                <td style="text-align: center;"><h5><b>{{ amountFormat($net_increase) }}</b></h5></td>
            </tr>
            <tr class="dotted">
                <td>Beginning Cash</td>
                <td style="text-align: center;">{{ amountFormat($beginning_cash) }}</td>
            </tr>
            <tr class="dotted">
                <td><b>Ending Cash</b></td>
                <td style="text-align: center;"><h5><b>{{ amountFormat($ending_cash) }}</b></h5></td>
            </tr>
        </tbody>
    </table>   
</body>
</html>