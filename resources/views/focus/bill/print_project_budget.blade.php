<html>
<head>
	<title>Picking List / Budget</title>
	<style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}
		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
		}
		table thead td {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid black;
			font-variant: small-caps;
		}
		td {
			vertical-align: top;
		}
		.bullets {
			width: 8px;
		}
		.items {
			border-bottom: 0.1mm solid black;
			font-size: 10pt; 
			border-collapse: collapse;
			width: 100%;
			font-family: sans-serif;
		}
		.items td {
			border-left: 0.1mm solid black;
			border-right: 0.1mm solid black;
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
			border-top: 1px solid
		}
		.ref {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
			border-collapse: collapse;
		}
		.ref tr td {
			border: 0.1mm solid #888888; 
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
			margin-top:5px;
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
			color:#0f4d9b; 
			font-size:9pt; 
			margin: 0;
		}
		.header-table-child {
			color:#0f4d9b; 
			font-size:8pt;
		}
		.header-table-child tr:nth-child(2) td {
			font-size:9pt; 
			padding-left:50px;
		}
		.footer {
			font-size: 9pt;
			text-align: center;
		}
	</style>
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div class="footer">

			@if(!empty($company->footer))
				<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->footer) }}" style="object-fit:contain" width="100%"/>
			@endif

			Page {PAGENO} of {nb}
		</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />
	<table class="header-table">
		<tr>
			<td>
				<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>
			</td>
		</tr>
	</table>
	<table class="doc-table">
		<tr>
			<td class="doc-title-td">
				<span class='doc-title'>
					<b>Picking List / Budget</b>
				</span>				
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">CUSTOMER DETAILS:</span><br><br>
				@php
					$clientname = $resource->lead->client_name;
					$branch = '';
					$address = $resource->lead->client_address;
					$email = $resource->lead->client_email;
					$cell = $resource->lead->client_contact;
					if ($resource->client) {
						$clientname = $resource->client->company;						
						$branch = $resource->branch? $resource->branch->name : '';
						$address = $resource->client->address;
						$email = $resource->client->email;
						$cell = $resource->client->phone;
					}					
				@endphp
				<b>Client Name :</b> {{ $clientname }}<br>
				<b>Branch :</b> {{ $branch }}<br>
				<b>Address :</b> {{ $address }}<br>
				<b>Email :</b> {{ $email }}<br>
				<b>Cell :</b> {{ $cell }}<br>
				<b>Attention :</b> {{ $resource->attention }}<br>
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br><br>
				<b>Date :</b> {{ dateFormat($resource->invoicedate, 'd-M-Y') }}<br>
				@php
					$tid = sprintf('%04d', $resource->tid);
					$field_name = 'Quotation No';
					$field_value = 'QT-' . $tid;
					if ($resource->bank_id) {
						$field_name = 'Proforma No';
						$field_value = 'PI-' . $tid;
					}
				@endphp
				<b>{{ $field_name }} :</b> {{ $field_value }}<br>				
				<b>Valid Till :</b> {{ dateFormat($resource->invoiceduedate, 'd-M-Y') }} <br>
				<b>Currency :</b> Kenya Shillings <br><br>
				<b>Client Ref: </b> {{ $resource->client_ref }}
			</td>
		</tr>
	</table><br>
	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Ref : <b>{{ $resource->notes }}</b></td></tr>
	</table>
	<br>
	{{-- budget items --}}
	@php
		$budget = $resource->budgets()->first();                
	@endphp
	<table class="items" cellpadding="8">
		<thead>
			<tr>
				<td width="8%">No.</td>
				<td width="42%">ITEM DESCRIPTION</td>
				<td width="10%">QTY</td>
				<td width="10%">UoM</td>				
			</tr>
		</thead>
		<tbody>            
			@foreach($budget->items as $item)
				@if ($item->a_type == 1)					
					<tr>
						<td>{{ $item->numbering }}</td>
						<td>{{ $item->product_name }}</td>
						<td class="align-c">{{ +$item->new_qty }}</td>
						<td class="align-c">{{ $item->unit }}</td>						
					</tr>
				@else
					<tr>
						<td><b>{{ $item->numbering }}<b></td>
						<td><b>{{ $item->product_name }}</b></td>
						@for($i = 0; $i < 2; $i++) 
							<td></td>
						@endfor
					</tr>
				@endif				
			@endforeach
			<!-- 20 dynamic empty rows -->
			@for ($i = count($resource->products); $i < 15; $i++)
				<tr>
					@for($j = 0; $j < 4; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
			<!--  -->
		</tbody>
	</table>
	<br>
	@isset($budget)
		<div style="width: 100%;">
			<div style="float: left; width: 50%">
				<table class="items" cellpadding="8">
					<thead>
						<tr>
							<td width="8%">No.</td>
							<td width="42%">Skill Type</td>
							<td width="10%">Working Hours</td>
							<td width="10%">No. Technicians</td>
						</tr>
					</thead>
					<tbody>
						@foreach ($budget->skillsets as $k => $val)
							<tr>
								<td>{{ $k+1 }}</td>
								<td>{{ $val->skill }}</td>
								<td>{{ $val->hours }}</td>
								<td>{{ $val->no_technician }}</td>
							</tr>
						@endforeach						
					</tbody>
				</table>
			</div>
			<div style="float: left; margin-left: 5%">
				<b>Tools Required & Notes :</b><br>
				{!! $budget->note !!}
			</div>		
		</div>	
	@endisset
</body>
</html>
