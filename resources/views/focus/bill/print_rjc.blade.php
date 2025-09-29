<html>
<head>
	<title>{{ 'RjR-'.sprintf('%04d', $resource->tid) }}</title>
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
        @php $image = "img/company/{$company->footer}" @endphp
	    <img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->url($image) : '' }}" style="object-fit:contain" width="100%">
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
				<span style="font-size:15pt;color:#0f4d9b;"><b>REPAIR / CORRECTIVE ACTION REPORT</b></span>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="50%" style="border: 0.1mm solid #888888; "><span style="font-size: 7pt; color: #555555; font-family: sans;">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name : </b>{{ $resource->quote->client->company }}<br>
				<b>Site / Branch : </b>{{ $resource->quote->branch->name }}<br>
				<b>Region : </b>{{ $resource->region }}<br>
				<b>Attention : </b> {{ $resource->attention }}<br>
			<td width="5%">&nbsp;</td>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">REFERENCE DETAILS:</span><br><br>
				<b>Report No : </b> {{ 'RjR-'.sprintf('%04d', $resource->tid) }}<br>
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
				<th width="20%">Tag Id / Unique No</th>
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
			@foreach($resource->rjc_items as $item)
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
		<h5><span>b.</span> Call Out Details</h5>
		<p>
			{{ $resource->quote->lead->title }} <b>on</b> <i>{{ dateFormat($resource->quote->lead->date_of_request, 'd-M-Y') }}</i> <b>as
			per call reference</b> <i>{{ $resource->quote->lead->client_ref }}</i>
		</p><br>
		<table class="items items-table" cellpadding=8>
			<thead>
				<tr>
					<th width="24%">Diagnosis Job Card Report <br>(Number, Date)</th>
					<th width="16%">Quote (Number, Date)</th>
					<th width="16%">Quote (Djc Ref, Djc Date)</th>
					<th width="44">Repair Job Card <br>(Number, Date, Technician)</th>			
				</tr>
			</thead>
			<tbody>
				<tr class="dotted">
					<td>
						@foreach ($resource->djcs as $djc)
							{{ 'DjR-'.sprintf('%04d', $djc->tid) }} ; 
							{{ dateFormat($djc->report_date, 'd-m-Y') }} 
							<br>
						@endforeach
					</td>
					@php
						$tid = sprintf('%04d', $resource->quote->tid);
						if ($resource->quote->bank_id) $tid = 'PI-'.$tid;
						else $tid = 'QT-'.$tid;
					@endphp
					<td>{{ $tid }} ; {{ dateFormat($resource->quote->resourcedate, 'd-m-Y') }}</td>
					<td>
						{{ $resource->quote->reference }} ; 
						{{ dateFormat($resource->quote->reference_date, 'd-m-Y') }}
					</td>
					<td>
						@foreach ($resource->quote->verified_jcs as $rjc)
							@if ($rjc->type == 2 && $rjc->date)
								DN-{{ $rjc->reference }} ; {{ dateFormat($rjc->date, 'd-m-Y') }} ; {{ $rjc->technician }}
							@elseif ($rjc->date)
								JC-{{ $rjc->reference }} ; {{ dateFormat($rjc->date, 'd-m-Y') }} ; {{ $rjc->technician }}
							@endif							
							<br>
						@endforeach
					</td>					
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
		<h5><span>f.</span> Pictorials</h5>
		<table class="items items-table" cellpadding="8">		
			<tr class="dotted">				
				@for ($i = 0; $i < 2; $i++)
					<th width="25%"></th>
				@endfor		
			</tr>
			<tr class="dotted">
				<td>
			        @php $image = "img/rjcreport/{$resource->image_one}" @endphp
				    <img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->url($image) : '' }}" style="object-fit:contain" width="100%">
				</td>
				<td>
			        @php $image = "img/rjcreport/{$resource->image_two}" @endphp
				    <img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->url($image) : '' }}" style="object-fit:contain" width="100%">
				</td>
			</tr>
			<tr>
				<td class="cost">{{ $resource->caption_one }}</td>
				<td class="cost">{{ $resource->caption_two }}</td>
			</tr>
			<tr class="dotted">
				<td>
			        @php $image = "img/rjcreport/{$resource->image_three}" @endphp
				    <img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->url($image) : '' }}" style="object-fit:contain" width="100%">
				</td>
				<td>
			        @php $image = "img/rjcreport/{$resource->image_four}" @endphp
				    <img src="{{ is_file(Storage::disk('public')->path($image))? Storage::disk('public')->url($image) : '' }}" style="object-fit:contain" width="100%">
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
