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
				<span style="font-size:15pt; color:#0f4d9b; text-transform:uppercase;"><b>Reconciliation Detail</b></span>
			</td>
		</tr>
	</table>
	<p style="margin:0;">Reconciled On: <i>{{ dateFormat($model->reconciled_on) }}</i></p>
	<p style="margin:0;">Reconciled By: <i>{{ $model->user->full_name }}</i></p>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="48%" style="border: 0.1mm solid #888888; ">
				<b>Statement Beginning Balance : </b>{{ numberFormat($model->begin_balance) }}<br>				
				<b>Checks And Payments : </b>{{ numberFormat($model->cash_out) }}<br>
				<b>Deposits And Other Credits : </b>{{ numberFormat($model->cash_in) }}<br>
				<b>Cleared Balance : </b> {{ numberFormat($model->cleared_balance) }}<br>
				<b>Statement Ending Balance : </b> {{ numberFormat($model->end_balance) }}<br>
				<b>Difference : </b> {{ numberFormat($model->balance_diff) }}<br>
			</td>	
			<td width="2%">&nbsp;</td>
			<td width="50%" style="border: 0.1mm solid #888888;">
				@php $endingPeriod = dateFormat($model->ending_period) @endphp
				<b>Uncleared Transactions As Of EP : </b>{{ numberFormat($model->ep_uncleared_balance) }}<br>				
				<b>Account Balance As Of EP : </b>{{ numberFormat($model->ep_account_balance) }}<br>
				<b>Cleared Transactions After EP : </b>{{ numberFormat($model->cleared_balance_after_ep) }}<br>
				<b>Uncleared Transactions After EP : </b> {{ numberFormat($model->uncleared_balance_after_ep) }}<br>
				<b>Account Balance As Of RO : </b> {{ numberFormat($model->ro_account_balance) }}<br>
			</td>
		</tr>
	</table>

	<p style="margin-bottom:0;">Details</p>
	<!-- Checks And Payments Cleared -->
	<h5 style="margin-top:0;margin-bottom:5px;">Checks And Payments Cleared</h5>
	<table class="items items-table" cellpadding=8 width="100%" style="text-align:center;">
		<thead>
			<tr>
				<th>#</th>				
				<th>Date</th>
				<th>Type</th>
				<th>Ref No</th>
				<th width="25%">Payee</th>
				<th>Amount</th>			
			</tr>
		</thead>
		<tbody>
			@php
				$modelItems = $model->items->where('checked', 1)->where('source', '!=', 'Receive Payment')->where('type', 'cash-out');
			@endphp
			@foreach($modelItems as $item)
				<tr class="dotted">
					<td width="50">{{ $loop->iteration }}</td>						
					<td>{{ $item->date }}</td>
					<td>{{ $item->source }}</td>
					<td>{{ $item->ref_no }}</td>
					<td>{{ $item->payee }}</td>
					<td>{{ numberFormat($item->credit) }}</td>                           
				</tr>
			@endforeach
            <tr class="dotted">
                <td colspan="4" style="border-top: solid 1px black;"></td>
                <td style="border-top:solid 1px black; border-left-style:hidden; text-align:center"><b>Total </b></td>
                <td style="border-top:solid 1px black; text-align:center">{{ numberFormat($modelItems->sum('credit')) }}</td>
            </tr>
		</tbody>
	</table>

	<!-- Deposits and other credits cleared -->
	<h5 style="margin-bottom:5px;">Deposits And Other Credits Cleared</h5>
	<table class="items items-table" cellpadding=8 width="100%" style="text-align:center;">
		<thead>
			<tr>
				<th>#</th>				
				<th>Date</th>
				<th>Type</th>
				<th>Ref No</th>
				<th width="25%">Payee</th>
				<th>Amount</th>			
			</tr>
		</thead>
		<tbody>
			@php
				$modelItems = $model->items->where('checked', 1)->whereIn('source', ['Receive Payment', 'Journal', 'Transfer'])->where('type', 'cash-in');
			@endphp
			@foreach($modelItems as $item)
				<tr class="dotted">
					<td width="50">{{ $loop->iteration }}</td>						
					<td>{{ $item->date }}</td>
					<td>{{ $item->source }}</td>
					<td>{{ $item->ref_no }}</td>
					<td>{{ $item->payee }}</td>
					<td>{{ numberFormat($item->debit) }}</td>                           
				</tr>
			@endforeach
            <tr class="dotted">
                <td colspan="4" style="border-top: solid 1px black;"></td>
                <td style="border-top:solid 1px black; border-left-style:hidden; text-align:center"><b>Total </b></td>
                <td style="border-top:solid 1px black; text-align:center">{{ numberFormat($modelItems->sum('debit')) }}</td>
            </tr>
		</tbody>
	</table>

	@php use \Carbon\Carbon; @endphp
	<p style="margin-bottom:0;">Other Details</p>
	<!-- Uncleared transactions as of ending period -->
	<h5 style="margin-top:0;margin-bottom:5px;">Uncleared Transactions As Of {{ dateFormat($model->ending_period) }}</h5>
	<table class="items items-table" cellpadding=8 width="100%" style="text-align:center;">
		<thead>
			<tr>
				<th>#</th>				
				<th>Date</th>
				<th>Type</th>
				<th>Ref No</th>
				<th width="25%">Payee</th>
				<th>Amount</th>			
			</tr>
		</thead>
		<tbody>
			@php
				$periodStart = Carbon::parse($model->ending_period)->startOfMonth()->format('d-m-Y');
				$periodEnd = Carbon::parse($model->ending_period)->format('d-m-Y');
				$modelItems = $model->items->whereNull('checked')->whereBetween('date', [$periodStart, $periodEnd]);
			@endphp
			@foreach($modelItems as $item)
				<tr class="dotted">
					<td width="50">{{ $loop->iteration }}</td>						
					<td>{{ $item->date }}</td>
					<td>{{ $item->source }}</td>
					<td>{{ $item->ref_no }}</td>
					<td>{{ $item->payee }}</td>
					<td>{{ numberFormat(+$item->debit ?: -$item->credit) }}</td>                           
				</tr>
			@endforeach
            <tr class="dotted">
                <td colspan="4" style="border-top: solid 1px black;"></td>
                <td style="border-top:solid 1px black; border-left-style:hidden; text-align:center"><b>Total </b></td>
                <td style="border-top:solid 1px black; text-align:center">{{ numberFormat($modelItems->sum('debit')-$modelItems->sum('credit')) }}</td>
            </tr>
		</tbody>
	</table>

	<!-- Cleared transactions after ending period -->
	<h5 style="margin-bottom:5px;">Cleared Transactions After {{ dateFormat($model->ending_period) }}</h5>
	<table class="items items-table" cellpadding=8 width="100%" style="text-align:center;">
		<thead>
			<tr>
				<th>#</th>				
				<th>Date</th>
				<th>Type</th>
				<th>Ref No</th>
				<th width="25%">Payee</th>
				<th>Amount</th>			
			</tr>
		</thead>
		<tbody>
			@php
				$periodEnd = Carbon::parse($model->ending_period)->format('d-m-Y');
				$modelItems = $model->items->where('checked', 1)->where('date', '>', $periodEnd);
			@endphp
			@foreach($modelItems as $item)
				<tr class="dotted">
					<td width="50">{{ $loop->iteration }}</td>						
					<td>{{ $item->date }}</td>
					<td>{{ $item->source }}</td>
					<td>{{ $item->ref_no }}</td>
					<td>{{ $item->payee }}</td>
					<td>{{ numberFormat(+$item->debit ?: -$item->credit) }}</td>                           
				</tr>
			@endforeach
            <tr class="dotted">
                <td colspan="4" style="border-top: solid 1px black;"></td>
                <td style="border-top:solid 1px black; border-left-style:hidden; text-align:center"><b>Total </b></td>
                <td style="border-top:solid 1px black; text-align:center">{{ numberFormat($modelItems->sum('debit')-$modelItems->sum('credit')) }}</td>
            </tr>
		</tbody>
	</table>

	<!-- Uncleared transactions after ending period -->
	<h5 style="margin-bottom:5px;">Uncleared Transactions After {{ dateFormat($model->ending_period) }}</h5>
	<table class="items items-table" cellpadding=8 width="100%" style="text-align:center;">
		<thead>
			<tr>
				<th>#</th>				
				<th>Date</th>
				<th>Type</th>
				<th>Ref No</th>
				<th width="25%">Payee</th>
				<th>Amount</th>			
			</tr>
		</thead>
		<tbody>
			@php
				$periodEnd = Carbon::parse($model->ending_period)->format('d-m-Y');
				$modelItems = $model->items->whereNull('checked')->where('date', '>', $periodEnd);
			@endphp
			@foreach($modelItems as $item)
				<tr class="dotted">
					<td width="50">{{ $loop->iteration }}</td>						
					<td>{{ $item->date }}</td>
					<td>{{ $item->source }}</td>
					<td>{{ $item->ref_no }}</td>
					<td>{{ $item->payee }}</td>
					<td>{{ numberFormat(+$item->debit ?: -$item->credit) }}</td>                           
				</tr>
			@endforeach
            <tr class="dotted">
                <td colspan="4" style="border-top: solid 1px black;"></td>
                <td style="border-top:solid 1px black; border-left-style:hidden; text-align:center"><b>Total </b></td>
                <td style="border-top:solid 1px black; text-align:center">{{ numberFormat($modelItems->sum('debit')-$modelItems->sum('credit')) }}</td>
            </tr>
		</tbody>
	</table>
</body>
</html>
