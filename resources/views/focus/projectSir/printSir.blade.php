<html>
<head>
	<title>
		Project Materials Report
	</title>

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

		/* Header Row Styling */
		table thead th {
			background-color: #BAD2FA;  /* Light Blue Background */
			text-align: center;
			border: 1px solid black;  /* Black border around the header cells */
			font-variant: small-caps;
			padding: 8px; /* Add padding for better spacing */
		}

		/* Data Cells Styling */
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

		/* Zebra Stripes for Table Rows */
		table tbody tr:nth-child(odd) {
			background-color: #f2f2f2; /* Light gray for odd rows */
		}

		table tbody tr:nth-child(even) {
			background-color: #ffffff; /* White for even rows */
		}
	</style>
</head>

<body>
@php

	$company = \Illuminate\Support\Facades\Auth::user()->business;

@endphp

<htmlpagefooter name="myfooter">
	<div class="footer">
		@if(!empty($company->footer))
			<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->footer) }}" style="object-fit:contain" width="100%"/>
		@endif
		Page {PAGENO} of {nb}
	</div>
</htmlpagefooter>
<sethtmlpagefooter name="myfooter" value="on"/>
<table class="header-table">
	<tr>
		<td>
			<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>
		</td>
	</tr>
</table>

<div class="container">
	<h1> Project Materials Report</h1>
	<p style="font-size:16px;"> Date: <b>{{(new DateTime())->format('l, jS F, Y')}}</b> </p>

	@if($filters['categoryFilter'] || $filters['fromDateFilter'] || $filters['toDateFilter'])

		<hr>

		<h3>Filters</h3>
		@if($filters['categoryFilter'])
			<p style="font-size:16px;"> Product Category: <b>{{ $filters['categoryFilter'] }}</b> </p>
		@endif

		@if($filters['fromDateFilter'])
			<p style="font-size:16px;"> Filter From Date: <b>{{ (new DateTime($filters['fromDateFilter']))->format('d/m/Y') }}</b> </p>
		@endif

		@if($filters['toDateFilter'])
			<p style="font-size:16px;"> Filter To Date: <b>{{ (new DateTime($filters['toDateFilter']))->format('d/m/Y') }}</b> </p>
		@endif

		<hr>

	@endif


	<table class="items">
		<thead>
		<tr>
			<th>Client</th>
			<th>Project</th>
			<th>Filtered Quantity</th>
			<th>Filtered Value</th>
			<th>All Time Quantity</th>
			<th>All Time Value</th>
		</tr>
		</thead>
		<tbody>
		@foreach($payload as $prj)
			<tr>
				<td>{{ $prj->client }}</td>
				<td>{{ $prj->project }}</td>
				<td>{{ number_format($prj->filteredQuantity, 2) }}</td>
				<td>{{ number_format($prj->filteredValue, 2) }}</td>
				<td>{{ number_format($prj->allTimeQuantity, 2) }}</td>
				<td>{{ number_format($prj->allTimeValue, 2) }}</td>
			</tr>
		@endforeach

		<tr>
			<td colspan="2" style="border-top: 2px solid black;"><b>TOTALS</b></td>
			<td style="border-top: 2px solid black;"><b>{{ number_format($payload->pluck('filteredQuantity')->sum(), 2) }}</b></td>
			<td style="border-top: 2px solid black;"><b>{{ number_format($payload->pluck('filteredValue')->sum(), 2) }}</b></td>
			<td style="border-top: 2px solid black;"><b>{{ number_format($payload->pluck('allTimeQuantity')->sum(), 2) }}</b></td>
			<td style="border-top: 2px solid black;"><b>{{ number_format($payload->pluck('allTimeValue')->sum(), 2) }}</b></td>
		</tr>
		</tbody>
	</table>
</div>
</body>
</html>
