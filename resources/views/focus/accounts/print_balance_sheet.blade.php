<html>
    <head>
        <title>Balance Sheet</title>
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

    <div style="text-align: center; line-height: 0">
        <h1>{{ auth()->user()->business->cname }}</h1>
        <h2>Balance Sheet as at {{ $dates[1]? dateFormat($dates[1]) : date('d-m-Y') }}</h2>
    </div>

    @php
        $balance_cluster = array();
    @endphp
    @foreach(['Asset', 'Equity', 'Liability', 'Summary'] as $i => $type)
        @if ($i < 3)
            <h5>{{ $type }} {{trans('accounts.accounts')}}</h5>
            <table class="table table-items" cellpadding=8>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Account No</th>
                        <th>Account</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $ledger_balance = 0;
                        $j = 0;
                        $k = 0;
                    @endphp
                    @foreach ($accounts as $account)
                        @php
                            $balance = 0;
                            $date = @$dates[1];

                            $debit = $account->transactions()
                            ->when(@$date, fn($q) => $q->whereDate('tr_date', '<=', $date))
                            ->sum('debit');
                            $credit = $account->transactions()
                            ->when(@$date, fn($q) => $q->whereDate('tr_date', '<=', $date))
                            ->sum('credit');

                            if ($account->account_type == 'Asset') {
                                $balance = round($debit - $credit, 2);
                                if ($balance < 0) {
                                    $account->holder .= ' (credit)';
                                    $account->account_type = 'Liability';
                                    $balance *= -1; 
                                }
                            } elseif (in_array($account->account_type, ['Liability', 'Equity'], 1)) {
                                $balance = round($credit - $debit, 2);
                                if ($balance < 0 && $account->account_type == 'Liability') {
                                    $account->holder .= ' (debit)';
                                    $account->account_type = 'Asset';
                                    $balance *= -1; 
                                }
                            }
                        @endphp
                        @if ($balance)
                            <!-- Equity -->
                            @if ($i == 1)
                                @if ($account->account_type == $type)  
                                    @php                                                
                                        $ledger_balance += $balance;
                                        $j++;
                                    @endphp                                  
                                    <tr class="dotted">
                                        <td>{{ $j }}</td>
                                        <td>{{ $account->number }}</td>
                                        <td>{{ $account->holder }}</td>
                                        <td style="text-align: center;">{{ numberFormat($balance) }}</td>
                                    </tr>
                                @else  
                                    <!-- P&L -->
                                    @php                                                    
                                        if ($k == 1) continue;
                                    @endphp
                                    <tr class="dotted">
                                        <td></td>
                                        <td></td>
                                        <td><i>Net Profit</i></td>
                                        <td style="text-align: center;">{{ numberFormat($net_profit) }}</td>
                                    </tr>
                                    @php
                                        
                                        $ledger_balance += $net_profit;
                                        $k++;
                                    @endphp
                                @endif
                            @elseif (in_array($i, [0, 2], 1) && $account->account_type == $type)
                                <!-- Asset or Liability -->
                                @php                                                
                                    $ledger_balance += $balance;
                                    $j++;
                                @endphp   
                                <tr class="dotted">
                                    <td>{{ $j }}</td>
                                    <td>{{ $account->number }}</td>
                                    <td>{{ $account->holder }}</td>
                                    <td style="text-align: center;">{{ numberFormat($balance) }}</td>
                                </tr>
                            @endif
                        @endif
                    @endforeach
                    @php
                        $balance_cluster[] = compact('type', 'ledger_balance');
                    @endphp
                    <tr class="dotted">
                        @for ($k = 0; $k < 3; $k++)
                            <td></td>
                        @endfor
                        <td style="text-align: center;"><h3 class="text-xl-left">{{ amountFormat($ledger_balance) }}</h3></td>
                    </tr>
                </tbody>
            </table>                                
        @else
            <!-- summary -->
            <h5>{{ $type }} <br>Asset = Equity  + (Revenue - Expense) + Liability</h5>
            <table class="table table-items" cellpadding=8>
                <tbody>
                    @php
                        $asset_bal = $balance_cluster[0]['ledger_balance'];
                        $equity_bal = $balance_cluster[1]['ledger_balance'];
                        $liability_bal = $balance_cluster[2]['ledger_balance'];
                    @endphp   
                    <tr class="dotted">
                        <td>
                            <h3>
                                {{ numberFormat($asset_bal) }} 
                                = {{ numberFormat($equity_bal - $net_profit) }} + ({{ numberFormat($net_profit) }}) + {{ numberFormat($liability_bal) }} <br>
                                <span style="visibility: hidden;">{{ numberFormat($equity_bal + $liability_bal) }}</span> 
                                = {{ numberFormat($equity_bal + $liability_bal) }}
                                @if (round($asset_bal) != round($equity_bal + $liability_bal))
                                    <span style="color: red; font-size:small">(Asset diff: {{ numberFormat($asset_bal - ($equity_bal + $liability_bal)) }})</span> 
                                @endif
                            </h3>
                        </td>
                    </tr>                    
                </tbody>
            </table>    
        @endif                           
    @endforeach
</body>
</html>