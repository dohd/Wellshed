<html>
<head>
	@php
		$label = $resource->bank_id? 'pi' : 'quote';
		$prefixes = prefixesArray(['quote', 'proforma_invoice'], $company->id);
		$tid = gen4tid($label == 'pi'? "{$prefixes[1]}-" : "{$prefixes[0]}-", $resource->tid);
		$v_no = '';
		if (isset($resource->verified_jcs[0])) $v_no .= ' (v' . $resource->verified_jcs[0]->verify_no . ')';
	@endphp
	<title>VERIFICATION {{ $v_no }}</title>
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
			@if(file_exists(Storage::disk('public')->url('app/public/img/company/' . $company->footer)))
				<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->footer) }}" style="object-fit:contain" width="100%"/>
			@endif
			Page {PAGENO} of {nb}
		</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />
	<table class="header-table">
		<tr>
			<td>
				{{-- @if (file_exists(Storage::disk('public')->url('app/public/img/company/' . $company->logo))) --}}
					<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>
				{{-- @endif --}}
			</td>
		</tr>
	</table>

	<table class="doc-table">
		<tr>
			<td class="doc-title-td">				
                <span class='doc-title'><b>WORKDONE VERIFICATION</b></span>			
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name :</b> {{ $resource->client->company }}<br>
				@if ($resource->branch)
					<b>Branch :</b> {{ $resource->branch->name }}<br>
				@endif
				<b>Address :</b> {{ $resource->client->address }}<br>
				<b>Email :</b> {{ $resource->client->email }}<br>
				<b>Cell :</b> {{ $resource->client->phone }}<br>
				<b>Attention :</b> {{ $resource->attention }}<br>
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br><br>
				<b>Reference No :</b> {{ $tid . $v_no }}<br>
                <b>Reference Date :</b> {{ dateFormat($resource->verification_date, 'd-M-Y') }} <br>
				<b>Currency :</b> {{ $resource->currency? $resource->currency->code : '' }} <br><br>
				@if ($resource->client_ref)
					<b>Client Ref: </b> {{ $resource->client_ref }}
				@endif
			</td>
		</tr>
	</table><br>
	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Ref : <b>{{ $resource->notes }}</b></td></tr>
        <tr>
            @php
				$jcard_refs = array();
                $dnote_refs = array();
                foreach ($resource->verified_jcs as $jc) {
					// type 2 is d-note and type 1 is jobcard
                    if ($jc->type == 2) $dnote_refs[] = $jc->reference;
                    else $jcard_refs[] = $jc->reference;
                }                
            @endphp
			@if ($jcard_refs || $dnote_refs)
				<td>RJC : <b>{{ implode(', ', $jcard_refs) }}</b></td>
				<td>DNOTE : <b>{{ implode(', ', $dnote_refs) }}</b></td>
			@endif
        </tr>		
	</table>
	<br>
	<table class="items" style="border-bottom: none;" cellpadding="8">
		<thead>
			<tr>
				<td width="6%">No.</td>
				<td width="38%">ITEM DESCRIPTION</td>
				<td width="6%">QTY</td>
				<td width="10%">UoM</td>
				<td width="15%">RATE</td>
				<td width="15%">AMOUNT</td>
                <td width="10%">REMARK</td>
			</tr>
		</thead>
		<tbody>
			@foreach($resource->verified_items as $product)
				@if ($product->a_type == 1)	
					<tr>
						<td>{{ $product->numbering }}</td>
						<td>{{ $product->product_name }}</td>
						<td class="align-c">{{ +$product->product_qty }}</td>
						<td class="align-c">{{ $product->unit }}</td>
                        <td class="align-r">
                            @if ($resource->print_type == 'inclusive')
                                {{ numberFormat($product->product_subtotal) }}
                            @else
                                {{ numberFormat($product->product_price) }}
                            @endif
                        </td>
                        <td class="align-r">
                            @if ($resource->print_type == 'inclusive')
                                {{ numberFormat($product->product_qty * $product->product_subtotal) }}
                            @else
                                {{ numberFormat($product->product_qty * $product->product_price) }}
                            @endif
                        </td>						
                        <td>{{ $product->remark }}</td>
					</tr>
				@else
					<tr>
						<td><b>{{ $product->numbering }}<b></td>
						<td><b>{{ $product->product_name }}</b></td>
						@for($i = 0; $i < 5; $i++) 
                            <td></td>
                        @endfor
					</tr>
				@endif				
			@endforeach
			<!-- 20 dynamic empty rows -->
			@for ($i = count($resource->verified_items); $i < 15; $i++)
				<tr>
					@for($j = 0; $j < 7; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
			<!--  -->
			<tr>
				<td colspan="4" class="bd-t">
					@isset($resource->bank)
						<span class="customer-dt-title">BANK DETAILS:</span><br>
						<b>Account Name :</b> {{ $resource->bank->name }}<br>
						<b>Account Number :</b> {{ $resource->bank->number }}<br>
						<b>Bank :</b> {{ $resource->bank->bank }} &nbsp;&nbsp;<b>Branch :</b> {{ $resource->bank->branch }} <br>
						<b>Currency :</b> Kenya Shillings &nbsp;&nbsp;<b>Swift Code :</b> {{ $resource->bank->code }} <br>
						{{ $resource->bank->paybill? "({$resource->bank->paybill})" : '' }}
					@endisset
				</td>
				<td class="bd align-r">Sub Total:</td>
                <td class="bd align-r">
                    @if ($resource->print_type == 'inclusive')
                        {{ numberFormat($resource->total) }}
                    @else
                        {{ numberFormat($resource->subtotal) }}
                    @endif
                </td>				
                <td class="bd-t"></td>
			</tr>
			<tr>
				<td colspan="4">
					@isset($resource->gen_remark)
						<b>General Remark</b> : <i>{{ $resource->gen_remark }}<i>
					@endisset
				</td>
				@if ($resource->print_type == 'inclusive')
					<td class="align-r">VAT {{ $resource->tax_id }}%</td>
					<td class="align-r">
						@php
							$text = $resource->tax_id ? 'INCLUSIVE' : 'NONE';
						@endphp
						{{ $text }}
					</td>
				@else
					<td class="align-r">Tax {{ $resource->tax_id }}%</td>
					<td class="align-r">{{ numberFormat($resource->tax) }}</td>
				@endif
				<td class=""></td>
			</tr>
			<tr>
				<td colspan="4" style="border-bottom: 1px solid;">
					@if ($resource->prepared_by)
						<em>Prepared By : </em><b>{{ $resource->prepared_by }}</b>
					@endif
				</td>
				<td class="bd align-r"><b>Grand Total:</b></td>
				<td class="bd align-r">{{ numberFormat($resource->total) }}</td>
				<td style="border-bottom: 1px solid;"></td>
			</tr>
		</tbody>
	</table>
</body>
</html>
