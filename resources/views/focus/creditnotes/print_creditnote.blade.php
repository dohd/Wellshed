<html>
<head>
	<title>{{ gen4tid('CN-', $resource->tid) }}</title>
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
		.items-table {
			font-size: 10pt; 
			border-collapse: collapse;
			height: 700px;
			width: 100%;
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
				<img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->url($image) : '' }}" style="object-fit:contain" width="100%">
			</td>
		</tr>
	</table>
	<table width="100%" style="font-size: 10pt;margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData">
				<span style="font-size:15pt; color:#0f4d9b;"><b>CREDIT NOTE</b></span>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="50%" style="border: 0.1mm solid #888888;"><span style="font-size: 7pt; color: #555555; font-family: sans;">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name : </b>{{ $resource->customer->company }}<br>
				<b>Client Tax Pin : </b>{{ $resource->customer->taxid }}<br><br>
				<b>Address : </b>{{ $resource->customer->address }}<br>
				<b>Email : </b>{{ $resource->customer->email }}<br>
				<b>Cell : </b> {{ $resource->customer->phone }}<br>
			<td width="5%">&nbsp;</td>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">REFERENCE DETAILS:</span><br><br>
				<b>Credit Note No : </b> {{ gen4tid('CN-', $resource->tid) }}<br>
				<b>CU Invoice No : </b> {{ $resource->cu_invoice_no }}<br><br>
				
				<b>Date : </b>{{ dateFormat($resource->date, 'd-M-Y') }}<br>
				<b>KRA Pin :</b> {{ auth()->user()->business->taxid }}<br>
				<b>Invoice No : </b>{{ gen4tid('', $resource->invoice->tid) }}<br>
				@if ($resource->currency)
					<b>Currency :</b> {{ $resource->currency->code }}<br>
				@endif
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
						<td class="mytotalss" style="text-align: center;">{{ +$item->qty }}</td>
						<td class="mytotalss" style="text-align: center;">{{ $item->unit }}</td>
						<td class="mytotalss" style="text-align: right;">{{ numberFormat($item->rate) }}</td>
						<td class="mytotalss" style="text-align: right;">{{ numberFormat($item->subtotal) }}</td>                
					</tr>
				@endforeach
			@else
				<tr class="dotted">
					<td class="mytotalss">1</td>
					<td class="mytotalss">{{ $resource->note }}</td>
					<td class="mytotalss" style="text-align: center;">1</td>
					<td class="mytotalss" style="text-align: center;">Lot</td>
					<td class="mytotalss" style="text-align: right;">{{ numberFormat($resource->subtotal) }}</td>
					<td class="mytotalss" style="text-align: right;">{{ numberFormat($resource->subtotal) }}</td>                
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


			@if ($item && $item->cstm_project_type && auth()->user()->ins == 85)
				<tr class="dotted">
					<td colspan="5" style="border-top: solid 1px black;"></td>
					<td class="totalss" style="border: solid 1px black;">Subtotal: </td>
					<td class="totalss" style="border: solid 1px black;">{{ numberFormat($resource->subtotal) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="5"></td>
					<td class="totalss" style="border-bottom: solid 1px black;">Tax 16%: </td>
					<td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat($resource->tax) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="5" style="border-bottom: solid 1px black;"></td>
					<td class="totalss" style="border-bottom: solid 1px black;"><b>Grand Total: </b></td>
					<td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat($resource->total) }}</td>
				</tr>
			@else
				<tr class="dotted">
					<td colspan="4" style="border-top: solid 1px black;"></td>
					<td class="totalss" style="border: solid 1px black;">Subtotal: </td>
					<td class="totalss" style="border: solid 1px black;">{{ numberFormat($resource->subtotal) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="4"></td>
					<td class="totalss" style="border-bottom: solid 1px black;">Tax 16%: </td>
					<td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat($resource->tax) }}</td>
				</tr>
				<tr class="dotted">
					<td colspan="4" style="border-bottom: solid 1px black;"></td>
					<td class="totalss" style="border-bottom: solid 1px black;"><b>Grand Total: </b></td>
					<td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat($resource->total) }}</td>
				</tr>
			@endif
		</tbody>
	</table>
</body>
</html>
