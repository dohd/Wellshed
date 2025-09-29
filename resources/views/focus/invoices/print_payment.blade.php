<html>
<head>
	<title>{{ gen4tid('PMT-', $resource->tid) }}</title>
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
				{{-- @if (file_exists(Storage::disk('public')->url('app/public/img/company/' . $company->logo))) --}}
					<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>					
				{{-- @endif --}}
			</td>
		</tr>
	</table>
	<table width="100%" style="font-size: 10pt;margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData">
				<span style="font-size:15pt; color:#0f4d9b; text-transform:uppercase;"><b>Receipt</b></span>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="50%" style="border: 0.1mm solid #888888; "><span style="font-size: 7pt; color: #555555; font-family: sans;">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name : </b>{{ @$resource->customer->company }}<br>				
				<b>Address : </b>{{ @$resource->customer->address }}<br>
				<b>Email : </b>{{ @$resource->customer->email }}<br>
				<b>Cell : </b> {{ @$resource->customer->phone }}<br>
			<td width="5%">&nbsp;</td>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">REFERENCE DETAILS:</span><br><br>
				<b>Date : </b>{{ dateFormat($resource->date, 'd-M-Y') }}<br>
				<b>Receipt No : </b>{{ gen4tid('PMT-', $resource->tid) }}<br>				
			</td>
		</tr>
	</table><br>

	<table class="items items-table" cellpadding=8 width="100%" style="text-align: center">
		<thead>
			<tr>
				<th>#</th>				
				<th>PMT Reference</th>
				<th>PMT Mode</th>
				<th>Inv No</th>
				<th>Inv Description</th>
				<th>Amount</th>			
			</tr>
		</thead>
		<tbody>
			@if ($resource->items->count())
				@foreach($resource->items as $k => $item)
					<tr class="dotted">
						<td width="50">{{ $k+1 }}</td>						
						<td>{{ $resource->reference }}</td>
						<td>{{ $resource->payment_mode }}</td>
						<td>{{ $item->invoice? gen4tid('Inv-',$item->invoice->tid) : '' }}</td>
						<td>{{ $item->invoice? $item->invoice->notes : '' }}</td>
						<td>{{ numberFormat($item->paid) }}</td>                           
					</tr>
				@endforeach
			@else
				<tr class="dotted">
					<td width="50">1.</td>					
					<td>{{ $resource->reference }}</td>
					<td>{{ $resource->payment_mode }}</td>
					<td></td>
					<td></td>
					<td>{{ numberFormat($resource->amount) }}</td>                           
				</tr>
			@endif
            <!-- dynamic empty rows -->
			@foreach (range(1,30) as $i)
				<tr>
					@foreach (range(1,6) as $j) 
						<td></td>
					@endforeach
				</tr>
			@endforeach
            <tr class="dotted">
                <td colspan="4" style="border-top: solid 1px black;"></td>
                <td style="border-top: solid 1px black; text-align: right"><b>Grand Total: </b></td>
                <td style="border-top: solid 1px black; text-align: center">{{ numberFormat($resource->amount) }}</td>
            </tr>
		</tbody>
	</table>
</body>
</html>
