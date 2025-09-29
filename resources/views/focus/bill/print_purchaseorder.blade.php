<html>
<head>
	<title>
		Purchase Order
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
					<b>Purchase Order</b>
				</span>				
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">SUPPLIER DETAILS:</span><br><br>
				<b>Dated :</b> {{ dateFormat($resource->date, 'd-M-Y') }}<br><br>
				<b>Name :</b> {{ $resource->supplier->name }}<br>
				<b>Address :</b> P.O Box {{ $resource->supplier->postbox }}<br>
				<b>Email :</b> {{ $resource->supplier->email }}<br>
				<b>Cell :</b> {{ $resource->supplier->phone }}
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">SHIPPING DETAILS:</span><br><br>
				<b>PO Number :</b> {{ gen4tid('PO-', $resource->tid) }}<br><br>				
				<b>Branch :</b> Head Office <br>
				<b>Client :</b> {{ auth()->user()->business->cname }} <br>
				<b>Address :</b> {{ auth()->user()->business->address }} <br>
				<b>Email :</b> {{ auth()->user()->business->email }}
			</td>
		</tr>
	</table><br>
	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Subject : <b>{{ $resource->note }}</b></td></tr>
	</table>
	<br>
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
            $groupedItems = [];
			$count = 1;

				// Group and sum quantities
				foreach ($resource->products as $item) {
                if (isset($groupedItems[$item->product_id])) {
                    $groupedItems[$item->product_id]['total_quantity'] += $item->qty;
                    $groupedItems[$item->product_id]['total_cost'] += $item->qty * $item->rate;
                } else {
                    $groupedItems[$item->product_id] = [
						'description' => $item->description,
                        'total_quantity' => $item->qty,
                        'uom' => $item->uom,
                        'rate' => $item->rate,
                        'total_cost' => $item->qty * $item->rate
                    ];
                }
            }
			@endphp
			@foreach ($groupedItems as $i => $details)
				<tr>
					<td>{{ $count }}</td>
					<td>{{ $details['description'] }}</td>
					<td class="align-c">{{ +$details['total_quantity'] }}</td>
					<td class="align-c">{{ $details['uom'] }}</td>
					<td class="align-r">{{	numberFormat($details['rate']) }}</td>
					<td class="align-r">{{ numberFormat($details['total_cost']) }}</td>
				</tr>
				@php $count++; @endphp
			@endforeach

			@php
				$emptyRows = 7 - $resource->products->count();
				$emptyRows = $emptyRows > 0 ? $emptyRows : 0;
			@endphp
			<!-- 20 dynamic empty rows -->
			@for ($i = 0; $i < $emptyRows; $i++)
				<tr>
					@for($j = 0; $j < 6; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
			<!--  -->
			<tr>
				<td colspan="4" class="bd-t" rowspan="2">
					<b>Terms: </b> {{ $resource->term ? $resource->term->title : '' }}<br>
					<b>Prepared By: </b> {{ $resource->user ? $resource->user->fullname : '' }}<br>
				</td>
				<td class="bd align-r">Sub Total:</td>
				<td class="bd align-r">{{ numberFormat($resource->paidttl) }}</td>
			</tr>
			
			<tr>
				<td class="align-r">Tax {{ $resource->tax ? $resource->tax . '%' : 'Off' }}</td>
				<td class="align-r">{{ number_format($resource->grandtax, 2) }}</td>
			</tr>
			
			<tr>
				<td colspan="4"></td>
				<td class="bd align-r"><b>Grand Total:</b></td>
				<td class="bd align-r">{{ number_format($resource->grandttl, 2) }}</td>
			</tr>
			
		</tbody>
	</table><br>
	<table class="ref" cellpadding="8">
        <tr>
            <td width="90%" style="border-color: transparent; border-right-style: hidden; border-left-style: hidden;border-bottom-style: hidden;">
                @if (count($resource->statuses) > 0)
					@foreach ($resource->statuses as $item)
						@if ($item->approval_status == 'approved')
							<b>Approved By: </b> {{ $item->approved_user ? $item->approved_user->fullname : '' }}
							@if ($item->approved_user && $item->approved_user->signature && !empty($item->approved_user->signature))
							<b>Sign: </b>
								<img src="{{ Storage::disk('public')->url('app/public/img/signs/' . $item->approved_user->signature) }}" alt="Signature" height="10" width="30">
							@endif
							<b>Approval Date: </b> {{ $item->approved_date }}<br>
						@endif
					@endforeach
				@endif
				@if (!empty($company->stamp))
					<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->stamp) }}" 
					alt="Stamp" 
					style="height: 150px; width: 300px; border-left-style: transparent; margin-left: 40px;">
				@endif
            </td>
        </tr>
    </table>
</body>
</html>
