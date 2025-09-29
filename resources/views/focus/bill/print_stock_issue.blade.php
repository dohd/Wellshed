<html>
<head>
	<title>
		DNOTE
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
		.generator {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
		}
		.generate {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
		}
		.mt-3 {
			margin-top: 30px;
		}
		.generate tr td:nth-child(4) {
			
			width: 150px; /* Set the desired width */
			height: 150px; /* Set the height to be the same as width */
			border: 2px dotted #000;
			text-align: center; /* Optional: center the text inside the cell */
			vertical-align: middle; /* Optional: center the text vertically */
			box-sizing: border-box; 
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
					<b>DNOTE</b>
				</span>				
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">CUSTOMER DETAILS:</span><br><br>
				@php
					$clientname = @$resource->customer->company ?: '';
					$branch = @$resource->customer->branch? $resource->customer->branch->name : '';
					$address = @$resource->customer->address ?: '';
					$email = @$resource->customer->email ?: '';
					$cell = @$resource->customer->phone ?: '';
										
				@endphp
				<b>Client Name :</b> {{ $clientname }}<br>
				@if ($branch)
					<b>Branch :</b> {{ $branch }}<br>
				@endif
				<b>Address :</b> {{ $address }}<br>
				<b>Email :</b> {{ $email }}<br>
				<b>Cell :</b> {{ $cell }}<br>
				
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br><br>	
				@php
					$project_no = @$resource->quote->project ? gen4tid('PRJ-',$resource->quote->project->tid) .'-'.@$resource->quote->project->name : '';
					$lpo_no = '';
					$pr_tid = '';
					$mr_tid = '';
					if($resource->quote){

						$lpo_no = @$resource->quote->project ? $resource->quote->project->lpo : '';
					}
					if($resource->purchase_requisition)
					{
						$pr_tid = $resource->purchase_requisition ? gen4tid('PR-',$resource->purchase_requisition->tid) : '';
						$pr = $resource->purchase_requisition;
						if ($pr->project) {
							$project_no = $pr->project ? gen4tid('PRJ-',$pr->project->tid) .' '.$pr->project->name : '';
						}
						if($pr->purchase_request){
							$mr_tid = $pr->purchase_request ? gen4tid('REQ-',$pr->purchase_request->tid) : '';
						}
					}
					$user_name = '';
					$assign_to_ids = array_filter(explode(',', $resource->assign_to_ids)); // Remove empty values

					if (!empty($assign_to_ids)) {
						foreach ($assign_to_ids as $user_id) {
							$user = App\Models\hrm\Hrm::find($user_id);
							
							if ($user) { // Ensure user exists
								$user_name .= $user->fullname . ', ';
							}
						}
						
						$user_name = trim($user_name); // Trim extra space at the end
					}

				@endphp
				<b>DNOTE no :</b> {{ gen4tid('ISS-',$resource->tid) }}<br><br>		
				<b>Date :</b> {{ dateFormat($resource->date, 'd-M-Y') }}<br>	
				<b>LPO no :</b> {{ $lpo_no }}<br>
				<b>Project no :</b> {{ $project_no }}<br>
				@if ($resource->purchase_requisition)
					
				<b>PR no | MR no :</b> {{ $pr_tid }} | {{$mr_tid}}<br>
				@endif
				<b>Reference no :</b> {{ $resource->ref_no }}<br>
				
			</td>
		</tr>
	</table><br>
	
	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Ref : <b>{{ $resource->note }}</b></td></tr>
	</table><br>
	<div>{!! $resource->extra_header !!}</div><br>
	<table class="items" cellpadding="8">
		<thead>
			<tr>
				<td width="8%">No.</td>
				<td width="42%">ITEM DESCRIPTION</td>
				<td width="15%">UoM</td>
				<td width="15%">QTY</td>
				<td width="20%">Tick</td>
			</tr>
		</thead>
		<tbody>
			@php 
				$auto_num = 0;
			@endphp
			@foreach($resource->items as $i => $product)
				
					<tr>
						@php
							$unit = '';
							if($product->productvar){
								$var = $product->productvar;
								if($var->product){
									$unit = $var->product->unit ? $var->product->unit->code : '';
								}
							}
							
						@endphp
						<td>{{ $i+1 }}</td>
						<td>{{ @$product->productvar->name }}</td>
						<td class="align-c">{{ $unit }}</td>
						<td class="align-c">{{ +$product->issue_qty }}</td>
						<td class="align-c"></td>
						
					</tr>
						
			@endforeach
			<!-- 20 dynamic empty rows -->
			@for ($i = count($resource->items); $i < 15; $i++)
				<tr>
					@for($j = 0; $j < 5; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
			<tr>
				<td colspan="3" class="bd-t">
					<em>Issued by: <b>{{@$resource->user->fullname}}</b><br>
					<em>PR no: <b>{{@$pr_tid}}</b><br>
					<em>Requested By: <b>{{@$user_name}}</b><br>

				</td>
				<td class="bd align-r"><b></b></td>
				<td class="bd align-r"></td>
			</tr>
			<!--  -->
			
		</tbody>
	</table><br>
	<h3>Goods Delived By</h3>
	<table class="generator" cellpadding="10">
		<tr>
			<td width="40%">
				<h4>Name: <span class="ml-3">...............................................</span></h4>
				
				
			</td>
			<td width="20%">
				<h4>Sign: ............</h4>
			</td>
			<td width="30%">
				<h4>Car Reg No: .............</h4>
			</td>
			<td width="10%">
				
				
			</td>
		</tr>
	</table><br>
	<h3 class="mt-3">Goods Received in good order and condition by:</h3>
	<table class="generator mt-3" cellpadding="10">
		<tr>
			<td width="45%">
				<h4>Client Rep Name: <span class="ml-3">.............................</span></h4>
				
				
			</td>
			<td width="20%">
				<h4>Sign: ............</h4>
			</td>
			<td width="25%">
				<h4>Date: .............</h4>
			</td>
			<td width="10%">
				
				
			</td>
		</tr>
	</table><br>
	<table class="generate mt-3" cellpadding="10">
		<tr>
			<td width="20%">
				
				&nbsp;
				
			</td>
			<td width="20%">
				&nbsp;
			</td>
			<td width="20%">
				&nbsp;
			</td>
			<td width="40%" height="40%" rowspan="30" colspan="10">
				<span><b>STAMP HERE</b></span>
				
			</td>
		</tr>
	</table><br>
	<div>{!! $resource->extra_footer !!}</div>
</body>
</html>
