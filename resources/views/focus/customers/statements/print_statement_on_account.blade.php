<html>
<head>
	<style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
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
		table thead th {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid #000000;
			font-weight: normal;
		}
		td {
			vertical-align: top;
		}
		.items-table {
			font-size: 10pt; 
			border-collapse: collapse;
			height: 700px;
			width: 100%;
		}
		.items td {
			border-left: 0.1mm solid #000000;
			border-right: 0.1mm solid #000000;
		}
		.items td.totals {
			text-align: right;
			border: 0.1mm solid #000000;
		}
		.items td.totalsss {
			text-align: right;
		}
		.items td.mytotals {
			text-align: left;
			border: 0.1mm solid #000000;
		}
		.items td.mytotalss {
			text-align: left;
		}
		.items td.totalss {
			text-align: right;
			border: 0.1mm solid #000000;
			text-transform: uppercase;
		}
		.items td.cost {
			text-align: center;
		}
		.dotted td {
			border-bottom: dotted 1px black;
		}
		.dotted th {
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
	</style>
    <title>Statement On Account</title>
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div class="footer">Page {PAGENO} of {nb}</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />
	
	<table class="header-table">
		<tr>
			<td>
                @php $image = "img/company/{$company->logo}" @endphp
                <img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->path($image) : '' }}" style="object-fit:contain" width="100%">
            </td>
		</tr>
	</table>

	<table width="100%" style="font-size:10pt; margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData">
				<span style="font-size:15pt; color:#0f4d9b; text-transform:uppercase;"><b>statement on account</b></span>
			</td>
		</tr>
	</table>

	<div>
		<table class="customer-dt" cellpadding="10">
			<tr>
				<td width="50%">
					<b>Customer Name :</b> {{ $customer->company }}<br>
					<b>Start Date :</b> {{ request('start_date')? dateFormat($start_date, 'd M Y') : '' }}<br>
					<b>End Date :</b> {{ date('d M Y') }}<br>
				</td>
			</tr>
		</table>
	</div>

	<table class="items items-table" cellpadding="8">
		<thead>
			<tr>
				<th>#</th>
				<th>Date</th>
				<th>Type</th>
				<th>Description</th>
				<th>Invoice Amount</th>
				<th>Amount Paid</th>
				<th>Account Balance</th>
			</tr>
		</thead>
		<tbody>
			@php
				$balance = 0;
				$i = 0;
			@endphp
			@foreach($transactions as $tr)
				<tr class="dotted">
					<td class="mytotalss">
						@php
							$i++;
							echo $i;
						@endphp
					</td>
					<td class="mytotalss">{{ dateFormat($tr->tr_date) }}</td>
					<td class="mytotalss">{{ $tr->tr_type }}</td>
					<td class="mytotalss">
						@php
							if ($tr->tr_type == 'inv' && $tr->invoice) {
								$tid = gen4tid('Inv-', $tr->invoice->tid);
								echo "({$tid}) {$tr->invoice->notes}";
							} elseif ($tr->tr_type == 'dep' && $tr->deposit) {
								$project = @$tr->deposit->relPayment->project ?: @$tr->deposit->project;
								if ($project) {
									$tid = gen4tid('PRJ-', $project->tid);
									$name = $project->name;
									echo "({$tid}) - {$name} <br> {$tr->note}";
								}
								else echo $tr->note;
							}
							else echo $tr->note;
						@endphp
					</td>
					<td class="mytotalss">{{ numberFormat($tr->debit) }}</td>
					<td class="mytotalss">{{ numberFormat($tr->credit) }}</td>
					<td class="mytotalss">
						@php
							if ($tr->debit > 0) $balance += $tr->debit;
							elseif ($tr->credit > 0) $balance -= $tr->credit;
							echo numberFormat($balance);
						@endphp
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	<!-- Aging -->
	<div style="margin-top: 1em">
		<div>
			<span style="font-size:10pt; color:#0f4d9b; text-transform:capitalize;"><b>Aging (days)</b></span>
		</div>
		
		<table class="items items-table" cellpadding="8">
			<thead>
				<tr>                                                    
					@foreach (['0 - 30', '31 - 60', '61 - 90', '91 - 120', '120+'] as $val)
						<th>{{ $val }}</th>
					@endforeach
					<th>Aging Total</th>  
					<th>Outstanding</th>                     
				</tr>
			</thead>
			<tbody>
				<tr>              
					@php $total_aging = 0; @endphp  					        
					@for ($i = 0; $i < count($aging_cluster); $i++) 
						<td>{{ numberFormat($aging_cluster[$i]) }}</td>
						@php $total_aging += $aging_cluster[$i] @endphp						
					@endfor
					<td>{{ numberFormat($total_aging) }}</td>
					<td>{{ numberFormat($total_aging) }}</td>
				</tr>                    
			</tbody>                     
		</table>  
	</div>
</body>
</html>
