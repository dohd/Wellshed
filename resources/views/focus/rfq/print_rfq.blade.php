@php use App\Models\account\Account;use App\Models\product\ProductVariation; @endphp
<html>
<head>
	<title>
		Request for Quotation
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

		/* Existing styles */
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

		/* Alternate row shading */
		.items tbody tr:nth-child(even) {
			background-color: #f2f2f2; /* Light grey color for even rows */
		}

		.align-r {
			text-align: right;
		}

		.align-c {
			text-align: center;
		}

		.align-l {
			text-align: left;
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
		}
	</style>
</head>
<body>
<htmlpagefooter name="myfooter">
	<div class="footer">
		Page {PAGENO} of {nb}
	</div>
</htmlpagefooter>
<sethtmlpagefooter name="myfooter" value="on"/>
<table class="header-table">
	<tr>
		<td>
			<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}"
				 style="object-fit:contain" width="100%"/>
		</td>
	</tr>
</table>
<table class="doc-table">
	<tr>
		<td class="doc-title-td">
				<span class='doc-title'>
					<b>Request for Quotation</b>
				</span>
		</td>
	</tr>
</table>
<br>
{{-- <table class="customer-dt" cellpadding="10">
	<tr>
		<td width="50%" style="font-size: 15px">
			{{ $company->cname }}<br>
			{{ $company->postbox }}<br>
			{{ $company->email }}<br>
			{{ $company->phone }}

			<br><br>
			Release Date : <b>{{ (new DateTime($rfq->date))->format('jS F, Y') }}</b><br>
			Submission Deadline : <b>{{ (new DateTime($rfq->due_date))->format('jS F, Y') }}</b><br><br>

		</td>
	</tr>
</table>
<br> --}}
<table class="customer-dt" cellpadding="10">
	<tr>
		<td width="50%">
			

			<span class="customer-dt-title">SUPPLIER DETAILS:</span><br>
			<b>Name :</b> {{ $supplier->name }}<br>
			<b>Address :</b> P.O Box {{ $supplier->postbox }}<br>
			<b>Email :</b> {{ $supplier->email }}<br>
			<b>Cell :</b> {{ $supplier->phone }}

		</td>
		<td width="5%">&nbsp;</td>
		<td width="45%">
			<span class="customer-dt-title">CLIENT DETAILS:</span><br><br>
			<b>RFQ Number :</b> {{ gen4tid('RFQ-', $rfq->tid) }}<br><br>				
			{{ $company->cname }}<br>
			{{ $company->postbox }}<br>
			{{ $company->email }}<br>
			{{ $company->phone }}

			<br><br>
			Release Date : <b>{{ (new DateTime($rfq->date))->format('jS F, Y') }}</b><br>
			Submission Deadline : <b>{{ (new DateTime($rfq->due_date))->format('jS F, Y') }}</b><br><br>
		</td>
	</tr>
</table><br>
<table class="ref" cellpadding="10">
	<tr>
		<td colspan="2"><b>{{ strtoupper($rfq->subject) }}</b></td>
	</tr>
</table>

<br>

<p style="text-align: justify; font-size: 14px; margin: 16px 0; line-height: 1.4;">
	We invite you to submit a quotation for our current procurement needs, as we believe you qualify to provide the required services to our company. This RFQ outlines the specific requirements for the goods or services we seek. We are confident in your capacity to deliver high-quality solutions within the required timelines. Please review the details carefully and submit a comprehensive proposal. Below is a table detailing the requested products and services, including specifications and quantities.
</p>


<br>
<table class="items" cellpadding="8">
	<thead>
	<tr>
		<td width="8%">#</td>
		<td width="42%">Item</td>
		<td width="12%">Type</td>
		<td width="5%">UoM</td>
		<td width="10%">Quantity</td>
		<td width="10%">Additional Specifications</td>
	</tr>
	</thead>
	<tbody>

	@foreach ($rfq->items as $key => $value)

		@php

			$product = null;
            $expense = null;

            if ($value->type === 'STOCK') $product = ProductVariation::find($value->product_id);
            if ($value->type === 'EXPENSE') $expense = Account::find($value->expense_account_id);
		@endphp

		<tr>
			<td>{{ $key+1 }}</td>
			@if($product)
				<td>{{ $product->name }}</td>
			@elseif($expense)
				<td>{{ $expense->holder }}</td>
			@else
				<td> <span style="color: #F32A00"><b><i> Item Not Found! </i></b></span> </td>
			@endif
			<td class="align-l"> {{ $value->type === 'STOCK' ? 'Product' : 'Service' }} </td>
			<td class="align-c">{{ $value->uom }}</td>
			<td class="align-c">{{ $value->quantity }}</td>
			<td class="align-c">{{ $value->description }}</td>
		</tr>
	@endforeach

	<!-- 20 dynamic empty rows -->
	@for ($i = count($rfq->products); $i < 15; $i++)
		<tr>
			@for($j = 0; $j < 6; $j++)
				<td></td>
			@endfor
		</tr>
	@endfor
	</tbody>
</table>

<br>
<br>

{{-- <p style="text-align: justify; font-size: 14px; margin: 16px 0; line-height: 1.4;">
	
</p> --}}

<table style="margin-top: 30px;">
	<tr>
		<td width="50%" style="font-weight: bolder">
			<p style="font-size: 14px; margin: 16px 0; line-height: 1.4;">
				Sincerely, <br>
				Procurement Department,<br>
				{{ $company->cname }}<br>
				{{ $company->email }}<br>
			</p>
		</td>
	</tr>
</table>


</body>
</html>
