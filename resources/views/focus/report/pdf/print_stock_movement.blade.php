<html>
<head>
	<title>
		Stock Movement Report
	</title>
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
					<b>Stock Movement Report</b>
				</span>				
			</td>
		</tr>
	</table><br>
	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Movement From : <b>{{ $start_date }}</b> To: <b>{{$end_date}}</b></td></tr>
	</table>
	<br>
	<table class="items" cellpadding="8">
		<thead>
			<tr>
				<td width="5%">No.</td>
                <td width="12%">Location</td>
				<td width="30%">PRODUCT NAME</td>
				<td width="15%">Product Code</td>
				<td width="10%">QTY OUT</td>
				<td width="10%">RETURN QTY</td>
				<td width="8%">UoM</td>
				{{-- <td width="15%">RATE</td> --}}
				<td width="10%">AMOUNT</td>
			</tr>
		</thead>
		<tbody>
			@php
                $count = 1;
            @endphp
			@foreach ($products as $i => $item)
				<tr>
					<td>{{ $count }}</td>
                    <td>{{ @$item->warehouse }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="align-c">{{ $item->code }}</td>
                    <td class="align-c">{{ +$item->issue_qty }}</td>
                    <td class="align-c">{{ +$item->return_qty }}</td>
                    <td class="align-c">{{ $item->unit }}</td>
                    <td class="align-r">{{ numberFormat($item->amount) }}</td>
				</tr>
				@php
                    $count ++;
                @endphp
			@endforeach

			<!-- 20 dynamic empty rows -->
			@for ($i = count($products); $i < 15; $i++)
				<tr>
					@for($j = 0; $j < 6; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
		</tbody>
	</table>
</body>
</html>
