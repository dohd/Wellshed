<html>
<head>
	<title>
		@php
			$prefixes = prefixesArray(['quote', 'proforma_invoice'], $company->id);
			$tid = $resource->tid;
			$doc_type = $resource->bank_id ? 'pi' : 'quote';
			$tid = gen4tid($doc_type == 'pi'? "{$prefixes[1]}-" : "{$prefixes[0]}-", $tid);
		@endphp
		{{ $resource->bank_id? 'Proforma Invoice' : 'Quotation' }}
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
					<b>{{ $resource->bank_id? 'PROFORMA INVOICE' : 'QUOTATION' }}</b>
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
					$branch = $resource->lead->branch? $resource->lead->branch->name : '';
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
				@if ($branch)
					<b>Branch :</b> {{ $branch }}<br>
				@endif
				<b>Address :</b> {{ $address }}<br>
				<b>Email :</b> {{ $email }}<br>
				<b>Cell :</b> {{ $cell }}<br>
				<b>Attention :</b> {{ $resource->attention }}<br>
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br><br>	
				<b>{{ $doc_type == 'pi'? 'Proforma No' : 'Quotation No' }} :</b> {{ $tid . $resource->revision }}<br><br>		
				<b>Date :</b> {{ dateFormat($resource->date, 'd-M-Y') }}<br>	
				<b>Valid Till :</b> {{ dateFormat("{$resource->date} + {$resource->validity} days", 'd-M-Y') }} <br>
				<b>Currency :</b> {{ $resource->currency? $resource->currency->code : '' }} <br>
				@if ($resource->client_ref)
					<b>Client Ref :</b> {{ $resource->client_ref }}
				@endif
			</td>
		</tr>
	</table><br>
	
	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Ref : <b>{{ $resource->notes }}</b></td></tr>
	</table><br>
	<div>{!! $resource->extra_header !!}</div><br>
	<table class="items" cellpadding="8">
		<thead>
			<tr>
				<td width="8%">No.</td>
				<td width="42%">ITEM DESCRIPTION</td>
				<td width="10%">QTY</td>
				<td width="10%">UoM</td>
				<td width="15%">RATE</td>
				<td width="15%">AMOUNT</td>
			</tr>
		</thead>
		<tbody>
			@php 
				$auto_num = 0;
			@endphp
			@foreach($resource->products as $product)
				@php 
					if ($product->misc) continue;
				@endphp
				@if ($product->a_type == 1)	
					@php 
						$auto_num++;
					@endphp
					<tr>
						<td>{{ $product->numbering ?: $auto_num }}</td>
						<td>{{ $product->product_name }}</td>
						<td class="align-c">{{ +$product->product_qty }}</td>
						<td class="align-c">{{ $product->unit }}</td>
						@if ($resource->print_type == 'exclusive')
							<td class="align-r">{{ numberFormat($product->product_subtotal) }}</td>
							<td class="align-r">{{ numberFormat($product->product_qty * $product->product_subtotal) }}</td>
						@else
							<td class="align-r">{{ numberFormat($product->product_price) }}</td>
							<td class="align-r">{{ numberFormat($product->product_qty * $product->product_price) }}</td>
						@endif
					</tr>
				@else
					<tr>
						<td><b>{{ $product->numbering }}<b></td>
						<td><b>{{ $product->product_name }}</b></td>
						@for($i = 0; $i < 4; $i++) 
							<td></td>
						@endfor
					</tr>
					@php 
						$auto_num = 0;
					@endphp
				@endif					
			@endforeach
			<!-- 20 dynamic empty rows -->
			@for ($i = count($resource->products); $i < 15; $i++)
				<tr>
					@for($j = 0; $j < 6; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
			<!--  -->
			<tr>
				<td colspan="4" class="bd-t" rowspan="2">
					@isset($resource->bank)
						<span class="customer-dt-title">BANK DETAILS:</span><br>
						<b>Account Name :</b> {{ $resource->bank->name }}<br>
						<b>Account Number :</b> {{ $resource->bank->number }}<br>
						<b>Bank :</b> {{ $resource->bank->bank }} &nbsp;&nbsp;<b>Branch :</b> {{ $resource->bank->branch }} <br>
						<b>Currency :</b> {{ $resource->currency? $resource->currency->code : 'Kenya Shillings' }} &nbsp;&nbsp;<b>Swift Code :</b> {{ $resource->bank->code }} <br>
						{{ $resource->bank->paybill? "({$resource->bank->paybill})" : '' }} <br><br>
					@endisset
					<b>Terms: </b> {{ $resource->term? $resource->term->title : '' }}<br>
					@if ($resource->prepared_by)
						<em>Prepared By : </em><b>{{ $resource->prepared_by }}</b>
					@endif
				</td>
				<td class="bd align-r">Sub Total:</td>
				@if ($resource->print_type == 'inclusive')
					<td class="bd align-r">{{ numberFormat($resource->total) }}</td>			
				@else
					<td class="bd align-r">{{ numberFormat($resource->subtotal) }}</td>
				@endif
			</tr>
			<tr>
				@if ($resource->print_type == 'inclusive')
					<td class="align-r">VAT {{ $resource->tax_id }}%</td>
					<td class="align-r">{{ $resource->tax_id ? 'INCLUSIVE' : 'NONE' }}</td>
				@else
					<td class="align-r">Tax {{ $resource->tax_id ? $resource->tax_id . '%' : 'OFF' }}</td>
					<td class="align-r">{{ numberFormat($resource->tax) }}</td>
				@endif
			</tr>
			<tr>
				<td colspan="4"></td>
				<td class="bd align-r"><b>Grand Total:</b></td>
				<td class="bd align-r">{{ numberFormat($resource->total) }}</td>
			</tr>
		</tbody>
	</table><br>
	<div>{!! $resource->extra_footer !!}</div>
</body>
</html>
