<html>
<head>
	<title>{{ gen4tid('DjR-', $resource->tid) }}</title>
	<style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}
		p {
			margin: 0pt;
		}
		table.items {
			border: 0.1mm solid #000000;
		}
		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
		}
		td {
			vertical-align: top;
		}
		.items td {
			border-left: 0.1mm solid #000000;
			border-right: 0.1mm solid #000000;
		}
		table thead th {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid #000000;
			font-weight: normal;
		}
		.items td.totals {
			text-align: right;
			border: 0.1mm solid #000000;
		}
		.items td.totalsss {
			text-align: right;
		}
		.items td.mytotals {
			text-align: left;
			border: 0.1mm solid #000000;
		}
		.items td.mytotalss {
			text-align: left;
		}
		.items td.totalss {
			text-align: right;
			border: 0.1mm solid #000000;
			text-transform: uppercase;
		}
		.items td.cost {
			text-align: center;
		}
		.dotted td {
			border-bottom: dotted 1px black;
		}
		.dotted th {
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
		{{-- @if(file_exists(Storage::disk('public')->url('app/public/img/company/' . $company->footer))) --}}
			<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->footer) }}" style="object-fit:contain" width="100%"/>
		{{-- @endif --}}
		<div class="footer">Page {PAGENO} of {nb}</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />
	
	<table class="header-table">
		<tr>
			<td>
				{{-- @if (file_exists(Storage::disk('public')->url('app/public/img/company/' . $company->logo)))
					<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>
				@endif --}}
				<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>
			</td>
		</tr>
	</table>
	<table width="100%" style="font-size: 10pt;margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData">
				<span style="font-size:15pt;color:#0f4d9b;"><b>DIAGNOSIS / SITE SURVEY REPORT</b></span>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="50%" style="border: 0.1mm solid #888888; "><span style="font-size: 7pt; color: #555555; font-family: sans;">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name : </b>{{ $resource->client? $resource->client->company : ($resource->lead? $resource->lead->client_name : '') }}<br>
				<b>Site / Branch : </b>{{ $resource->branch? $resource->branch->name : '' }}<br>
				<b>Region : </b>{{ $resource->region }}<br>
				<b>Attention : </b> {{ $resource->attention }}<br>
			<td width="5%">&nbsp;</td>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">REFERENCE DETAILS:</span><br><br>
				<b>Report No : </b> {{ gen4tid('DjR-', $resource->tid) }}<br>
				<b>Date : </b>{{ dateFormat($resource->report_date, 'd-M-Y') }}<br><br>
				<b>Prepared By : </b>{{ $resource->prepared_by }}<br>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td style="border: 0.1mm solid #888888;">
				Ref : <b>{{ $resource->subject }}</b>
			</td>
		</tr>
	</table>
	<h5><span>a.</span> Equipment Details</h5>
	<table class="items items-table" cellpadding=8>
		<thead>
			<tr>
				<th width="20%">Tag No / Unique Id</th>
				<th>Serial No</th>
				<th>Make / Type</th>
				<th>Capacity</th>
				<th>Location</th>
				<th>Last Service</th>
				<th>Next Service</th>
			</tr>
		</thead>
		<tbody>
			<!-- ITEMS HERE -->
			@foreach($resource->items as $item)
				<tr class="dotted">
					<td class="mytotalss">{{ $item->unique_id }}</td>
					<td class="mytotalss">{{ $item->equip_serial }}</td>
					<td class="mytotalss">{{ $item->make_type }}</td>
					<td class="mytotalss">{{ $item->capacity }}</td>
					<td class="mytotalss">{{ $item->location }}</td>
					<td class="mytotalss">{{ dateFormat($item->last_service_date, $company->main_date_format) }}</td>
					<td class="mytotalss">{{ dateFormat($item->next_service_date, $company->main_date_format) }}</td>
				</tr>
			@endforeach
			<!-- END ITEMS HERE -->
		</tbody>
	</table>
	<div>
		@if ($resource->lead)
			<h5><span>b.</span> Call Out Details</h5>
			<p>
				{{ $resource->lead->title }} <b>on</b> <i>{{ dateFormat($resource->lead->date_of_request, 'd-M-Y') }}</i> <b>as
				per call reference</b> <i>{{ $resource->lead->client_ref }}</i>
			</p>
		@endif
		<br>
		<table class="items items-table" cellpadding=8>
			<thead>
				<tr>
					<th width="30%" >Djc Number</th>
					<th width="30%">Djc Date</th>
					<th width="40%">Diagnosis Technician(s)</th>
				</tr>
			</thead>
			<tbody>
				<tr class="dotted">
					<td>{{ $resource->job_card }}</td>
					<td>{{ dateFormat($resource->report_date, 'd-M-Y') }}</td>
					<td>{{ $resource->technician }}</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div>
		<h5><span>c.</span> Findings & Root Cause</h5>
		<p>{!! $resource->root_cause !!}</p>
	</div>
	<div>
		<h5><span>d.</span> Action Taken</h5>
		<p>{!! $resource->action_taken !!}</p>
	</div>
	<div>
		<h5><span>e.</span> Recommendation</h5>
		<p>{!! $resource->recommendations !!}</p>
	</div>

	@php
		$images = array_filter([
			$resource->image_one, $resource->image_two, 
			$resource->image_three, $resource->image_four
		], fn($v) => $v);
	@endphp
	@if($images)
		<div style="height: 3em;"></div>
		<h5><span>f.</span> Pictorials</h5>
		<table class="items items-table" cellpadding="8">		
			<tr class="dotted">				
				@for ($i = 0; $i < 2; $i++)
					<th width="25%"></th>
				@endfor		
			</tr>
			<tr class="dotted">
				<td>
					{{-- @if (file_exists(Storage::disk('public')->url('app/public/img/djcreport/' . $resource->image_one))) --}}
						<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $resource->image_one) }}" alt="image_one" border=3 height=300 width=300></img>
					{{-- @endif --}}
				</td>
				<td>
					{{-- @if(file_exists(Storage::disk('public')->url('app/public/img/djcreport/' . $resource->image_two))) --}}
						<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $resource->image_two) }}" alt="image_two" border=3 height=300 width=300></img>
					{{-- @endif --}}
				</td>
			</tr>
			<tr>
				<td class="cost">{{ $resource->caption_one }}</td>
				<td class="cost">{{ $resource->caption_two }}</td>
			</tr>
			<tr class="dotted">
				<td>
					{{-- @if(file_exists(Storage::disk('public')->url('app/public/img/djcreport/' . $resource->image_three))) --}}
						<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $resource->image_three) }}" alt="image_three" border=3 height=300 width=300></img>
					{{-- @endif --}}
				</td>
				<td>
					{{-- @if(file_exists(Storage::disk('public')->url('app/public/img/djcreport/' . $resource->image_four))) --}}
						<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $resource->image_four) }}" alt="image_four" border=3 height=300 width=300></img>
					{{-- @endif --}}
				</td>
			</tr>
			<tr>
				<td class="cost">{{ $resource->caption_three }}</td>
				<td class="cost">{{ $resource->caption_four }}</td>
			</tr>
		</table>
	@endif
</body>
</html>
