<html>
<head>
	<title>{{ gen4tid('RCN-', $model->tid) }}</title>
	<style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
			width: 100%;
		}
		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
		}
		table.items {
			border: 0.1mm solid #000000;
		}
		td {
			vertical-align: top;
		}
		table thead th {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid #000000;
			font-weight: normal;
		}
		.items td {
			border-left: 0.1mm solid #000000;
			border-right: 0.1mm solid #000000;
		}
		.dotted td {
			border-bottom: none;
		}
		.dottedt th {
			border-bottom: dotted 1px black;
		}
		h5 {
			text-decoration: underline;
			font-size: 1em;
			font-family: Arial, Helvetica, sans-serif;
			font-weight: bold;
		}
		h5 span {
			text-decoration: none;
		}
		.footer {
			font-size: 9pt; 
			text-align: center; 
		}
		.items-table {
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

	<table width="100%" style="border-bottom: 0.8mm solid #0f4d9b;">
		<tr>
			<td>
				<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>
			</td>
		</tr>
	</table>
	<table width="100%" style="font-size: 10pt;margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData">
				<span style="font-size:15pt; color:#0f4d9b; text-transform:uppercase;">
					<b>{{ @$model->account->holder }}, Period Ending {{ dateFormat($model->ending_period) }}</b>
				</span><br>
				<span style="font-size:15pt; color:#0f4d9b; text-transform:uppercase;"><b>Reconciliation Summary</b></span>
			</td>
		</tr>
	</table>
	<p style="margin: 0;margin-top: 10px;">Reconciled On: <i>{{ dateFormat($model->reconciled_on) }}</i></p>
	<p style="margin: 0;margin-bottom: 5px;">Reconciled By: <i>{{ $model->user->full_name }}</i></p>

	<!-- summary table -->
	<table class="items items-table" cellpadding=8 width="100%">
		<tbody>
			<tr class="dotted">
				<td><b>Beginning Balance</b></td>                        
				<td>{{ numberFormat($model->begin_balance) }}</td>
			</tr>
			<tr class="dotted" style="background-color: #BAD2FA">
				<td style="padding-left: 20px;"><b>Cleared Transactions</b></td>                        
				<td></td>
			</tr>
			<tr class="dotted">
				@php
					$modelItems = $model->items->where('checked', 1)->where('source', '!=', 'Receive Payment')->where('type', 'cash-out');
				@endphp
				<td style="padding-left: 30px;">Checks and Payments ({{ $modelItems->count() }})</td>                        
				<td>{{ numberFormat($modelItems->sum('credit')) }}</td>
			</tr>
			<tr class="dotted">
				@php
					$modelItems = $model->items->where('checked', 1)->whereIn('source', ['Receive Payment', 'Journal', 'Transfer'])->where('type', 'cash-in');
				@endphp
				<td style="padding-left: 30px;">Deposits and Credits ({{ $modelItems->count() }})</td>                        
				<td>{{ numberFormat($modelItems->sum('debit')) }}</td>
			</tr>
			<tr class="dotted" style="background-color: #BAD2FA">
				@php
					$totalCleared = $modelItems->sum('debit')-$modelItems->sum('credit')
				@endphp
				<td style="padding-left: 20px;"><b>Total Cleared Transactions</b></td>                        
				<td style="border:solid 1px black;">{{ numberFormat($totalCleared) }}</td>
			</tr>
			<tr class="dotted">
				@php
					$clearedBalance = $model->begin_balance-$totalCleared
				@endphp
				<td><b>Cleared Balance</b></td>                        
				<td style="border: solid 1px black; border-bottom: double;">{{ numberFormat($clearedBalance) }}</td>
			</tr>
			<tr class="dotted" style="background-color: #BAD2FA">
				<td style="padding-left: 20px;"><b>Uncleared Transactions</b></td>                        
				<td></td>
			</tr>
			@php 
				use \Carbon\Carbon; 
				$periodStart = Carbon::parse($model->ending_period)->startOfMonth()->format('d-m-Y');
				$periodEnd = Carbon::parse($model->ending_period)->format('d-m-Y');
				$modelItems = $model->items->whereNull('checked')->whereBetween('date', [$periodStart, $periodEnd]);
				$modelItemsDebit = $modelItems->where('debit', '>', 0);
				$modelItemsCredit = $modelItems->where('credit', '>', 0);
				$totalUncleared = $modelItemsDebit->sum('debit')-$modelItemsCredit->sum('credit')
			@endphp
			<tr class="dotted">
				<td style="padding-left: 30px;">Checks and Payments ({{ $modelItemsCredit->count() }})</td>                        
				<td>{{ numberFormat($modelItemsCredit->sum('credit')) }}</td>
			</tr>
			<tr class="dotted">
				<td style="padding-left: 30px;">Deposits and Credits ({{ $modelItemsDebit->count() }})</td>                        
				<td>{{ numberFormat($modelItemsDebit->sum('debit')) }}</td>
			</tr>
			<tr class="dotted" style="background-color: #BAD2FA">
				<td style="padding-left: 20px;"><b>Total Uncleared Transactions</b></td>                        
				<td style="border:solid 1px black;">{{ numberFormat($totalUncleared) }}</td>
			</tr>
			<tr class="dotted">
				<td><b>Register Balance As Of {{ dateFormat($model->ending_period) }}</b></td>                        
				<td style="border:solid 1px black; border-bottom: double;">{{ numberFormat($model->ep_account_balance) }}</td>
			</tr>
			<tr class="dotted">
				<td><b>Register Balance As Of {{ dateFormat($model->reconciled_on) }}</b></td>                        
				<td>{{ numberFormat($model->ro_account_balance) }}</td>
			</tr>
			<tr class="dotted" style="background-color: #BAD2FA">
				<td style="padding-left: 20px;"><b>Cleared Transactions After {{ dateFormat($model->ending_period) }}</b></td>                        
				<td></td>
			</tr>
			<tr class="dotted">
				@php
					$periodEnd = Carbon::parse($model->ending_period)->format('d-m-Y');
					$modelItems = $model->items->where('checked', 1)->where('date', '>', $periodEnd);
					$clearedTranxs = $modelItems->sum('debit')-$modelItems->sum('credit');
				@endphp
				<td style="padding-left: 30px;">Transactions ({{ $modelItems->count() }})</td>                        
				<td>{{ numberFormat($clearedTranxs) }}</td>
			</tr>
			<tr class="dotted" style="background-color: #BAD2FA">
				<td style="padding-left: 20px;"><b>Uncleared Transactions After {{ dateFormat($model->ending_period) }}</b></td>                        
				<td></td>
			</tr>
			<tr class="dotted">
				@php
					$periodEnd = Carbon::parse($model->ending_period)->format('d-m-Y');
					$modelItems = $model->items->whereNull('checked')->where('date', '>', $periodEnd);
					$unclearedTranxs = $modelItems->sum('debit')-$modelItems->sum('credit');
				@endphp
				<td style="padding-left: 30px;">Transactions ({{ $modelItems->count() }})</td>                        
				<td>{{ numberFormat($unclearedTranxs) }}</td>
			</tr>
			<tr class="dotted">
				<td><b>Ending Balance</b></td>                        
				<td style="border:solid 1px black; border-bottom: double;">{{ numberFormat($model->end_balance) }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>
