<html>
<head>
	<title>{{ $resource->is_debit? "Debit Note" : "Credit Note" }}</title>
	<style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}

		p {
			margin: 0pt;
		}

		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
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
            width: 100%;
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
		.items td.mytotalss {
			text-align: left;
		}
		.items td.totalss {
			text-align: right;
		}
		.items td.cost {
			text-align: center;
		}
		.items-table {
			font-size: 10pt; 
			border-collapse: collapse;
			height: 700px;
			width: 100%;
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
			font-size: 10pt; 
			text-align: center; 
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
	</style>
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
	{{-- <table width="100%" style="font-size: 10pt;margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData">
				<span style="font-size:15pt; color:#0f4d9b;"><b>{{ $resource->is_debit? "DEBIT NOTE" : "CREDIT NOTE" }}</b></span>
			</td>
		</tr>
	</table><br> --}}
	<table class="doc-table">
        <tr>
            <td class="doc-title-td">
                <span class='doc-title'><b>{{ $resource->is_debit? "DEBIT NOTE" : "CREDIT NOTE" }}</b></span>
            </td>
        </tr>
    </table><br>

	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="50%" style="border: 0.1mm solid #888888;"><span style="font-size: 7pt; color: #555555; font-family: sans;">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name : </b>{{ $resource->customer->company }}<br>
				<b>Client TIN : </b>{{ $resource->customer->taxid }}<br>
				<b>Address : </b>{{ $resource->customer->address }}<br>
				<b>Email : </b>{{ $resource->customer->email }}<br>
				<b>Mobile No : </b> {{ $resource->customer->phone }}<br>
			<td width="5%">&nbsp;</td>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">REFERENCE DETAILS:</span><br><br>
				<b>Legal Name :</b> {{ $company->cname }}<br>
                <b>TIN :</b> {{ $company->taxid }}<br>
                <b>Reference No :</b> {{ $resource->efris_reference_no }}<br>
				<!--<b>System No :</b> {{ gen4tid('', $resource->tid) }}<br>-->
				<b>{{ $resource->is_debit? 'Debit Note' : 'Credit Note' }} No :</b> {{ gen4tid('', $resource->tid) }}<br>
                <b>Currency :</b> {{ @$resource->currency->code }}<br>
			</td>
		</tr>
	</table>
	<br>

	@if ($resource->note)
		<table class="ref" width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
			<tr><td width="100%" style="border: 0.1mm solid #888888;">Ref : <b>{{ $resource->note }}</b></td></tr>
		</table>
		<br>
	@endif

	<!-- Item List -->
	<table class="items items-table" cellpadding=8>
		<thead>
			<tr>
				<th width="6%">No</th>
				@php $item = $resource->items->first() @endphp
                <!-- Custom col for Epicenter Africa -->    
                @if ($item && $item->cstm_project_type && auth()->user()->ins == 85) 
					<th width="25%">Item Code</th>
					<th width="20%">Item Description</th>
				@else
					<th width="45%">Item Description</th>
				@endif
				<th width="8%">Qty</th>
				<th width="10%">UoM</th>
				<th width="15%">Rate</th>
				<th width="15%">Amount</th>				
			</tr>
		</thead>
		<tbody>
			@if ($resource->items->count())
				@foreach ($resource->items as $item)
					<tr class="dotted">
						<td class="mytotalss">{{ $item->numbering }}</td>
						@if ($item->cstm_project_type)
							<td class="mytotalss">{{ $item->cstm_project_type }}</td>
							<td class="mytotalss">{{ $item->name }}</td>
                        @else
							<td class="mytotalss">{{ $item->name }}</td>
						@endif
						<td class="mytotalss" style="text-align: center;">{{ -$item->qty }}</td>
						<td class="mytotalss" style="text-align: center;">{{ $item->unit }}</td>
						<td class="mytotalss" style="text-align: right;">{{ numberFormat($item->rate) }}</td>
						<td class="mytotalss" style="text-align: right;">{{ numberFormat(-$item->subtotal) }}</td>                
					</tr>
				@endforeach
			@else
				<tr class="dotted">
					<td class="mytotalss">1</td>
					<td class="mytotalss">{{ $resource->note }}</td>
					<td class="mytotalss" style="text-align: center;">-1</td>
					<td class="mytotalss" style="text-align: center;">Lot</td>
					<td class="mytotalss" style="text-align: right;">{{ numberFormat($resource->subtotal) }}</td>
					<td class="mytotalss" style="text-align: right;">{{ numberFormat(-$resource->subtotal) }}</td>                
				</tr>
			@endif
            <!-- dynamic empty rows -->
			@php
				$emptyRows = 15 - $resource->items->count();
				$emptyRows = $emptyRows > 0 ? $emptyRows : 0;
			@endphp

			@for ($i = 0; $i < $emptyRows; $i++)
				<tr>
					@if ($item && $item->cstm_project_type && auth()->user()->ins == 85)
						@for($j = 0; $j < 7; $j++) <td></td> @endfor    
					@else
						@for($j = 0; $j < 6; $j++) <td></td> @endfor    
					@endif
				</tr>
			@endfor

			<!-- Epicenter Item Summary -->
			@if ($item && $item->cstm_project_type && auth()->user()->ins == 85)
				<tr class="dotted">
					<td colspan="5" style="border-top: solid 1px black;"></td>
					<td class="totalss" style="border: solid 1px black;">Taxable Total: </td>
					<td class="totalss" style="border: solid 1px black;">{{ numberFormat(+$resource->taxable > 0? -$resource->taxable : $resource->taxable) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="5"></td>
					<td class="totalss" style="border: solid 1px black;">Subtotal: </td>
					<td class="totalss" style="border: solid 1px black;">{{ numberFormat(-$resource->subtotal) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="5"></td> 
					<td class="totalss" style="border-bottom: solid 1px black;">{{ intval($resource->tax_id)? "Tax ". intval($resource->tax_id) ."% " : "Tax OFF " }}</td>
					<td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat(+$resource->tax > 0? -$resource->tax : $resource->tax) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="5" style="border-bottom: solid 1px black;"></td>
					<td class="totalss" style="border-bottom: solid 1px black;"><b>Grand Total: </b></td>
					<td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat(-$resource->total) }}</td>
				</tr>
			@else
				<!-- Default Item Summary -->
				<tr class="dotted">
					<td colspan="4" style="border-top: solid 1px black;"></td>
					<td class="totalss" style="border: solid 1px black;">Taxable Total: </td>
					<td class="totalss" style="border: solid 1px black;">{{ numberFormat(+$resource->taxable > 0? -$resource->taxable : $resource->taxable) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="4"></td>
					<td class="totalss" style="border: solid 1px black;">Subtotal: </td>
					<td class="totalss" style="border: solid 1px black;">{{ numberFormat($resource->subtotal) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="4"></td>
					<td class="totalss" style="border-bottom: solid 1px black;">{{ intval($resource->tax_id)? "Tax ". intval($resource->tax_id) ."% " : "Tax OFF " }}</td>
					<td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat(+$resource->tax > 0? -$resource->tax : $resource->tax) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="4" style="border-bottom: solid 1px black;"></td>
					<td class="totalss" style="border-bottom: solid 1px black;"><b>Grand Total: </b></td>
					<td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat($resource->total) }}</td>
				</tr>
			@endif
		</tbody>
	</table>
	<br>

	<table class="ref" cellpadding="10">
        <tr>
            <td width="85%" style="border-right-style: transparent">
				<b>Device No :</b> {{ $company->etr_code }}<br>
                <b>Credit Note No :</b> {{ $resource->efris_creditnote_no }}<br>
				<b>Fiscal Document No :</b> {{ $resource->efris_ori_invoice_no }}<br>
                <b>Verification Code :</b> {{ $resource->efris_antifakecode }}<br>
                <b>Issued Date & Time:</b> {{ $resource->efris_issued_date ?: date('d-m-Y', strtotime($resource->date)) }}<br>
            </td>
            <td width="15%" style="border-left-style: transparent">
                @php $image = "qr/EfrisCreditNote-{$resource->efris_creditnote_no}.png" @endphp
                <img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->path($image) : '' }}" style="object-fit:contain" width="90" height="90">
            </td>
        </tr>
    </table>
</body>
</html>
