<html>
<head>
	<title>Daily Metrics</title>	
	<style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}
		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
			width: 100%;
			border-collapse: collapse;
		}
		table thead td {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid black;
			font-variant: small-caps;
			padding: 8px; /* Add padding for better spacing */
		}
		td {
			vertical-align: top;
			padding: 8px; /* Add padding for better spacing */
		}
		.bullets {
			width: 8px;
		}
		.items {
			border-bottom: 0.1mm solid black;
			font-size: 10pt;
		}
		.items td {
			border-left: 0.1mm solid black;
			border-right: 0.1mm solid black;
		}
		.items tr:hover {
			background-color: #f2f2f2; /* Add hover effect */
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
			border-top: 1px solid;
		}
		.ref {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
			border-collapse: collapse;
		}
		.ref tr td {
			border: 0.1mm solid #888888;
			padding: 8px; /* Add padding for better spacing */
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
			margin-top: 20px; /* Add margin for spacing */
		}
		p {
			text-align: justify;
		}
		h2 {
			margin-top: 30px;
		}
		table tbody tr:nth-child(odd) {
			background-color: #f2f2f2; /* Light gray for odd rows */
		}
		table tbody tr:nth-child(even) {
			background-color: #ffffff; /* White for even rows */
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
<sethtmlpagefooter name="myfooter" value="on"/>
<table class="header-table">
	<tr>
		<td>
			<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>
		</td>
	</tr>
</table>
@php
	$options = json_decode($dbm->options);
@endphp
<div class="container">
	<h1> The 8pm Daily Operations Summary Report for {{$company['cname']}}</h1>
	<h3>{{$dateToday->format('l, jS F, Y')}}</h3>
	<h2>Introduction</h2>
	<p>
		This report presents a detailed overview of key operational activities processed over the specified period.
		It includes critical data on invoices, purchases, and purchase orders handled on specific dates, alongside
		important metrics such as stock alerts from the past week, labour hours logged, and sales and expense summaries.
		<br><br>
		The information provided aims to give a comprehensive snapshot of the organization's financial and operational
		performance, facilitating better decision-making and operational planning. By closely tracking financial
		transactions, procurement activities, workforce productivity, and stock levels, this report serves as a vital
		tool for optimizing daily business processes and improving overall efficiency.
		<br><br>
		The report is dynamically generated, ensuring that the most up-to-date data is presented for accurate
		and timely insights.
	</p>
	@if($dbm->ins === 2)
		<div style="margin-bottom: 10px">
			<h2>Active Tenants as at {{$dateToday->format('l, jS F, Y')}}</h2>
			@if(isset($payload['tenantsActive']) && count($payload['tenantsActive']) > 0)
				<p>
					Active Tenants as at the specified date.
				</p>
				<table class="items">
					<thead>
					<tr>
						<td>#</td>
						<td>Business Name</td>
						<td>Billing Date</td>
					</tr>
					</thead>
					<tbody>
					@php
						$no = 1;
					@endphp
					@foreach($payload['tenantsActive'] as $ticket)
						<tr>
							<td>{{ $no++ }}</td>
							<td>{{ $ticket['name'] }}</td>
							<td>{{ $ticket['billing_date'] }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p>No active tenants found.</p>
			@endif
		</div>
		<div style="margin-bottom: 10px">
			<h2>Suspended Tenants as at {{$dateToday->format('l, jS F, Y')}}</h2>
			@if(isset($payload['tenantsSuspended']) && count($payload['tenantsSuspended']) > 0)
				<p>
					Suspended Tenants as at the specified date.
				</p>
				<table class="items">
					<thead>
					<tr>
						<td>#</td>
						<td>Business Name</td>
						<td>Billing Date</td>
					</tr>
					</thead>
					<tbody>
					@php
						$no = 1;
					@endphp
					@foreach($payload['tenantsSuspended'] as $ticket)
						<tr>
							<td>{{ $no++ }}</td>
							<td>{{ $ticket['name'] }}</td>
							<td>{{ $ticket['billing_date'] }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p>No active tenants found.</p>
			@endif
		</div>
		<div style="margin-bottom: 10px">
			<h2>Tenants With 7 or Less days Left to Cutoff on {{$dateToday->format('l, jS F, Y')}}</h2>
			@if(isset($payload['7DayTenants']) && count($payload['7DayTenants']) > 0)
				<table class="items">
					<thead>
					<tr>
						<td>#</td>
						<td>Business Name</td>
						<td>Cutoff Date</td>
					</tr>
					</thead>
					<tbody>
					@php
						$no = 1;
					@endphp
					@foreach($payload['7DayTenants'] as $ticket)
						<tr>
							<td>{{ $no++ }}</td>
							<td>{{ $ticket['name'] }}</td>
							<td>{{ $ticket['cutoff_date'] }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p>No Tenants With 7 or Less days Left to Cutoff found.</p>
			@endif
		</div>
		<div style="margin-bottom: 10px">
			<h2>Tenants Onboarding as at {{$dateToday->format('l, jS F, Y')}}</h2>
			@if(isset($payload['tenantsOnboarding']) && count($payload['tenantsOnboarding']) > 0)
				<p>
					Tenants Onboarding as at the specified date.
				</p>
				<table class="items">
					<thead>
					<tr>
						<td>#</td>
						<td>Business Name</td>
						<td>Billing Date</td>
					</tr>
					</thead>
					<tbody>
					@php
						$no1 = 1;
					@endphp
					@foreach($payload['tenantsOnboarding'] as $ticket)
						<tr>
							<td>{{ $no1++ }}</td>
							<td>{{ $ticket['name'] }}</td>
							<td>{{ $ticket['billing_date'] }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p>No onboarding tenants found.</p>
			@endif
		</div>
		<div style="margin-bottom: 10px">
			<h2>Tenants Activated on {{$dateToday->format('l, jS F, Y')}}</h2>
			@if(isset($payload['tenantsActivated']) && count($payload['tenantsActivated']) > 0)
				<p>
					Tenants Activated on the specified date.
				</p>
				<table class="items">
					<thead>
					<tr>
						<td>#</td>
						<td>Business Name</td>
						<td>Time</td>
					</tr>
					</thead>
					<tbody>
					@php
						$no1 = 1;
					@endphp
					@foreach($payload['tenantsActivated'] as $ticket)
						<tr>
							<td>{{ $no1++ }}</td>
							<td>{{ $ticket['name'] }}</td>
							<td>{{ $ticket['time'] }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p>No activated tenants found.</p>
			@endif
		</div>
		<div style="margin-bottom: 10px">
			<h2>Tenants Deactivated on {{$dateToday->format('l, jS F, Y')}}</h2>
			@if(isset($payload['tenantsDeactivated']) && count($payload['tenantsDeactivated']) > 0)
				<p>
					Tenants Deactivated on the specified date.
				</p>
				<table class="items">
					<thead>
					<tr>
						<td>#</td>
						<td>Business Name</td>
						<td>Time</td>
					</tr>
					</thead>
					<tbody>
					@php
						$no1 = 1;
					@endphp
					@foreach($payload['tenantsDeactivated'] as $ticket)
						<tr>
							<td>{{ $no1++ }}</td>
							<td>{{ $ticket['name'] }}</td>
							<td>{{ $ticket['time'] }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p>No activated tenants found.</p>
			@endif
		</div>
		<div style="margin-bottom: 10px">
			<h2>Tenants Loyalty Points Redeemed on {{$dateToday->format('l, jS F, Y')}}</h2>
			@if(isset($payload['tenantsLoyaltyRedemptions']) && count($payload['tenantsLoyaltyRedemptions']) > 0)
				<p>
					Tenants Deactivated on the specified date.
				</p>
				<table class="items">
					<thead>
					<tr>
						<td>Business Name</td>
						<td>Points</td>
						<td>Days</td>
					</tr>
					</thead>
					<tbody>
					@foreach($payload['tenantsLoyaltyRedemptions'] as $ticket)
						<tr>
							<td>{{ $ticket['name'] }}</td>
							<td>{{ $ticket['points'] }}</td>
							<td>{{ $ticket['days'] }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p>No points redemptions found.</p>
			@endif
		</div>
		<div style="margin-bottom: 10px">
			<h2>Tenants Grace Days Requested on {{$dateToday->format('l, jS F, Y')}}</h2>
			@if(isset($payload['tenantsGraceRequests']) && count($payload['tenantsGraceRequests']) > 0)
				<p>
					Tenants Deactivated on the specified date.
				</p>
				<table class="items">
					<thead>
					<tr>
						<td>Business Name</td>
						<td>Days</td>
					</tr>
					</thead>
					<tbody>
					@foreach($payload['tenantsGraceRequests'] as $ticket)
						<tr>
							<td>{{ $ticket['name'] }}</td>
							<td>{{ $ticket['days'] }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p>No points redemptions found.</p>
			@endif
		</div>
		<div style="margin-bottom: 10px">
			<h2>Tenants Summary on {{$dateToday->format('l, jS F, Y')}}</h2>
			<p>
				Tenants Deactivated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Metric</td>
					<td>Value</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><b>Active Tenants</b></td>
					<td>{{ $payload['tenantsActiveCount'] }}</td>
				</tr>
				<tr>
					<td><b>Suspended Tenants</b></td>
					<td>{{ $payload['tenantsSuspendedCount'] }}</td>
				</tr>
				<tr>
					<td><b>Onboarding Tenants</b></td>
					<td>{{ $payload['tenantsOnboardingCount'] }}</td>
				</tr>
				<tr>
					<td><b>Tenants Activated on this date</b></td>
					<td>{{ $payload['tenantsActivatedCount'] }}</td>
				</tr>
				<tr>
					<td><b>Tenants Deactivated on this date</b></td>
					<td>{{ $payload['tenantsDeactivatedCount'] }}</td>
				</tr>
				<tr>
					<td><b>Total Redeemed Loyalty Points</b></td>
					<td>{{ $payload['totalRedeemedLoyaltyPoints'] }}</td>
				</tr>
				<tr>
					<td><b>Total Redeemed Loyalty Points Days</b></td>
					<td>{{ $payload['totalRedeemedLoyaltyDays'] }}</td>
				</tr>
				<tr>
					<td><b>Total Granted Grace Days</b></td>
					<td>{{ $payload['totalRedeemedGraceDays'] }}</td>
				</tr>
				</tbody>
			</table>
		</div>
	@endif
	<!-- AI Agent Chats Count -->
	@if(empty($options) || (!empty($options) && in_array('agentChats', $options)))
		<h2>AI Agent Chats On {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(@$payload['agentChatCount']['totalCount'])
			<p>This section provides a summary of chat count on the specified date.</p>
			<table class="items">
				<thead>
					<tr>
						<td>Chat Source</td>
						<td>Count</td>
					</tr>
				</thead>
				<tbody>
					@php $agentChatCount = $payload['agentChatCount'] @endphp
					<tr>
						<td>Total Leads</td>
						<td>{{ @$agentChatCount['totalCount'] }}</td>
					</tr>
					<tr>
						<td>Facebook Leads</td>
						<td>{{ @$agentChatCount['facebookCount'] }}</td>
					</tr>
					<tr>
						<td>Whatsapp Leads</td>
						<td>{{ @$agentChatCount['whatsappCount'] }}</td>
					</tr>
					<tr>
						<td>Instagram leads</td>
						<td>{{ @$agentChatCount['instagramCount'] }}</td>
					</tr>
					<tr>
						<td>Website leads</td>
						<td>{{ @$agentChatCount['websiteCount'] }}</td>
					</tr>
				</tbody>
			</table>
		@else
			<p>No chats found.</p>
		@endif
	@endif
	<!-- End AI Agent Chats -->
	<!-- AI Agent Leads -->
	@if(empty($options) || (!empty($options) && in_array('agentLeads', $options)))
		<h2>AI Agent Leads Generated On {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(@$payload['agentLeadsCount']['totalCount'])
			<p>This section provides a summary of all leads generated on the specified date.</p>
			<table class="items">
				<thead>
					<tr>
						<td>Lead Source</td>
						<td>Count</td>
					</tr>
				</thead>
				<tbody>
					@php $agentLeadsCount = $payload['agentLeadsCount'] @endphp
					<tr>
						<td>Total Leads</td>
						<td>{{ @$agentLeadsCount['totalCount'] }}</td>
					</tr>
					<tr>
						<td>Facebook Leads</td>
						<td>{{ @$agentLeadsCount['facebookCount'] }}</td>
					</tr>
					<tr>
						<td>Whatsapp Leads</td>
						<td>{{ @$agentLeadsCount['whatsappCount'] }}</td>
					</tr>
					<tr>
						<td>Instagram leads</td>
						<td>{{ @$agentLeadsCount['instagramCount'] }}</td>
					</tr>
					<tr>
						<td>Website leads</td>
						<td>{{ @$agentLeadsCount['websiteCount'] }}</td>
					</tr>
				</tbody>
			</table>
		@else
			<p>No leads found.</p>
		@endif
	@endif
	<!-- End AI Agent Leads -->
	<!-- Tickets -->
	@if(empty($options) || (!empty($options) && in_array('tickets', $options)))
		<h2>Tickets Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['tickets']) && count($payload['tickets']) > 0)
			<p>
				This section provides a summary of all tickets issued/updated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Ticket ID</td>
					<td>Title</td>
					<td>Status</td>
					<td>Client Type</td>
					<td>Customer</td>
					<td>Branch</td>
					<td>Source</td>
					<td>Phone</td>
					<td>Email</td>
					<td>Created By</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['tickets'] as $ticket)
					<tr>
						<td>{{ $ticket['tid'] }}</td>
						<td>{{ $ticket['title'] }}</td>
						<td>{{ $ticket['status'] }}</td>
						<td>{{ $ticket['client_type'] }}</td>
						<td>{{ $ticket['customer'] }}</td>
						<td>{{ $ticket['branch'] }}</td>
						<td>{{ $ticket['source'] }}</td>
						<td>{{ $ticket['client_contact'] }}</td>
						<td>{{ $ticket['client_email'] }}</td>
						<td>{{ $ticket['creator'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No tickets found.</p>
		@endif
	@endif

	@if(empty($options) || (!empty($options) && in_array('quotes', $options)))
		<h2>Tender Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['tenders']) && count($payload['tenders']) > 0)
			<p>
				This section provides a summary of all Tender created/updated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Customer</td>
					<td>Ticket</td>
					<td>Title</td>
					<td>Type of Organization</td>
					<td>Submission Date</td>
					<td>Site Visit Date</td>
					<td>Amount</td>
					<td>Bid Bond Amount</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['tenders'] as $tender)
					<tr>
						<td>{{ $tender['customer'] }}</td>
						<td>{{ $tender['ticket_tid'] }}</td>
						<td>{{ $tender['title'] }}</td>
						<td>{{ $tender['stages'] }}</td>
						<td>{{ $tender['submission_date'] }}</td>
						<td>{{ $tender['site_visit_date'] }}</td>
						<td>{{ $tender['amount'] }}</td>
						<td>{{ $tender['bid_bond_amount'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No Tender found.</p>
		@endif
	@endif
	<!-- End Tickets -->
	@if(empty($options) || (!empty($options) && in_array('quotes', $options)))
		<h2>Quotes & Proforma Invoices Processed/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['quotes']) && count($payload['quotes']) > 0)
			<p>
				This section provides a summary of all Quotes & Proforma Invoices issued on the specified date. This
				includes
				detailed information about customer requests, quote amounts, and their current status, giving you
				a comprehensive overview of your quote activity for the day.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Quote ID</td>
					<td>Customer</td>
					<td>Branch</td>
					<td>Title</td>
					<td>Status</td>
					<td>Approved by</td>
					<td>Date</td>
					<td>Created By</td>
					<td>Currency</td>
					<td>Total</td>
					<td>Tax</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['quotes'] as $quote)
					<tr>
						<td>{{ $quote['tid'] }}</td>
						<td>{{ $quote['customer'] }}</td>
						<td>{{ $quote['branch'] }}</td>
						<td>{{ $quote['notes'] }}</td>
						<td>{{ $quote['status'] }}</td>
						<td>{{ $quote['approved_by'] }}</td>
						<td>{{ $quote['date'] }}</td>
						<td>{{ $quote['creator'] }}</td>
						<td>{{ $quote['currency'] }}</td>
						<td>{{ number_format($quote['total'], 2) }}</td>
						<td>{{ number_format($quote['tax'], 2) }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="9" style="border-top: 2px solid black;"><b>TOTALS</b></td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['quotesTotal'], 2) }}</b></td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['quotesTotalTax'], 2) }}</b>
					</td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No quotes found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('projects', $options)))
		<h2>Projects Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['projects']) && count($payload['projects']) > 0)
			<p>
				This section provides a summary of all projects created/updated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Project ID</td>
					<td>Title</td>
					<td>Priority</td>
					<td>Status</td>
					<td>Quote</td>
					<td>Customer</td>
					<td>Branch</td>
					<td>Created By</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['projects'] as $project)
					<tr>
						<td>{{ $project['tid'] }}</td>
						<td>{{ $project['title'] }}</td>
						<td>{{ $project['priority'] }}</td>
						<td>{{ $project['status'] }}</td>
						<td>{{ $project['quote'] }}</td>
						<td>{{ $project['customer'] }}</td>
						<td>{{ $project['branch'] }}</td>
						<td>{{ $project['created_by'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No projects found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('grossProfit', $options)))
		<h2>Gross Profit for Projects Ended on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['grossProfit']) && count($payload['grossProfit']) > 0)
			<p>
				This section provides a summary of gross profit for projects ended on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Project ID</td>
					<td>Title</td>
					<td>Customer</td>
					<td>Quote</td>
					<td>Quote Values</td>
					<td>Income</td>
					<td>Expense</td>
					<td>Gross Profit</td>
					<td>Percentage Profit</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['grossProfit'] as $project)
					<tr>
						<td>{{ $project['tid'] }}</td>
						<td>{{ $project['title'] }}</td>
						<td>{{ $project['customer'] }}</td>
						<td>{{ $project['quote'] }}</td>
						<td> {!! $project['quote_amount'] !!} </td>
						<td>{{ number_format($project['income'], 2) }}</td>
						<td>{{ number_format($project['expense'], 2) }}</td>
						<td>{{ number_format($project['gross_profit'], 2) }}</td>
						<td>{{ $project['perc_profit'] }}</td>
					</tr>
				@endforeach
                <tr>
                    <td colspan="5" style="border-top: 2px solid black;"><b>TOTALS</b></td>
                    <td style="border-top: 2px solid black;">
                        <b>{{ number_format($payload['totalGrossProfitIncome'], 2) }}</b>
                    </td>
                    <td style="border-top: 2px solid black;">
                        <b>{{ number_format($payload['totalGrossProfitExpense'], 2) }}</b>
                    </td>
                    <td style="border-top: 2px solid black;">
                        <b>{{ number_format($payload['totalGrossProfitProfit'], 2) }}</b>
                    </td>
                    <td style="border-top: 2px solid black;"> &nbsp;</td>
                </tr>
                </tbody>
			</table>
		@else
			<p>No projects found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('quoteBudgets', $options)))
		<h2>Approved Quote Budgets Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['quoteBudgets']) && count($payload['quoteBudgets']) > 0)
			<p>
				This section provides a summary of all approved quote budgets created/updated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Quote</td>
					<td>Customer</td>
					<td>Branch</td>
					<td>Notes</td>
					<td>Created By</td>
					<td>Currency</td>
					<td>Quoted Amount</td>
					<td>Budget</td>
					<td>Margin</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['quoteBudgets'] as $quote)
					<tr>
						<td>{{ $quote['quote'] }}</td>
						<td>{{ $quote['customer'] }}</td>
						<td>{{ $quote['branch'] }}</td>
						<td>{{ $quote['notes'] }}</td>
						<td>{{ $quote['creator'] }}</td>
						<td>{{ $quote['currency'] }}</td>
						<td>{{ number_format($quote['quoted_value'], 2) }}</td>
						<td>{{ number_format($quote['budget'], 2) }}</td>
						<td>{{ number_format($quote['margin'], 2) }}%</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="6" style="border-top: 2px solid black;"><b>TOTALS</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['quoteBudgetsQuotesTotal'], 2) }}</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['quoteBudgetsBudgetsTotal'], 2) }}</b></td>
					<td style="border-top: 2px solid black;"> &nbsp;</td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No approved quote budgets found.</p>
		@endif
	@endif
	
	@if(empty($options) || (!empty($options) && in_array('quotes', $options)))
		<h2>JoB Valuations Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['job_valuations']) && count($payload['job_valuations']) > 0)
			<p>
				This section provides a summary of all JoB valuations created/updated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>#Serial</td>
					<td>#Quote No</td>
					<td>Customer</td>
					<td>Valuation Title</td>
					<td>Quote Total</td>
					<td>Valuation</td>  
					<td>Balance</td>                                                  
					<td>Date</td>
					<td>#Invoice No.</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['job_valuations'] as $valuate)
					<tr>
						<td>{{ $valuate['tid'] }}</td>
						<td>{{ $valuate['quote_tid'] }}</td>
						<td>{{ $valuate['customer'] }}</td>
						<td>{{ $valuate['note'] }}</td>
						<td>{{ number_format($valuate['total'], 2) }}</td>
						<td>{{ number_format($valuate['subtotal'], 2) }}</td>
						<td>{{ number_format($valuate['balance'], 2) }}</td>
						<td>{{ $valuate['date'] }}</td>
						<td>{{ $valuate['invoice_tid'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No JoB Valuation found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('quotes', $options)))
		<h2>BoQ Valuations Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['boq_valuations']) && count($payload['boq_valuations']) > 0)
			<p>
				This section provides a summary of all BoQ valuations created/updated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>#Serial</td>
					<td>#BoQ No</td>
					<td>Customer</td>
					<td>Valuation Title</td>
					<td>BoQ Total</td>
					<td>Valuation</td>  
					<td>Balance</td>                                                  
					<td>Date</td>
					<td>#Invoice No.</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['boq_valuations'] as $bq_val)
					<tr>
						<td>{{ $bq_val['tid'] }}</td>
						<td>{{ $bq_val['boq_tid'] }}</td>
						<td>{{ $bq_val['customer'] }}</td>
						<td>{{ $bq_val['note'] }}</td>
						<td>{{ number_format($bq_val['total'], 2) }}</td>
						<td>{{ number_format($bq_val['subtotal'], 2) }}</td>
						<td>{{ number_format($bq_val['balance'], 2) }}</td>
						<td>{{ $bq_val['date'] }}</td>
						<td>{{ $bq_val['invoice_tid'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No BoQ Valuation found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('invoices', $options)))
		<h2>Invoices Processed/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['invoices']) && count($payload['invoices']) > 0)
			<p>
				This section provides a summary of all invoices that were processed on the given date. It includes key
				details
				such as invoice numbers, amounts, and the status of payments. The data offers insight into daily
				financial
				transactions and assists in tracking outstanding balances.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Invoice ID</td>
					<td>Customer</td>
					<td>Status</td>
					<td>Invoice No</td>
					<td>Date</td>
					<td>Due Date</td>
					<td>Total</td>
					<td>Tax</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['invoices'] as $invoice)
					<tr>
						<td>{{ $invoice['tid'] }}</td>
						<td>{{ $invoice['customer'] }}</td>
						<td>{{ $invoice['status'] }}</td>
						<td>{{ $invoice['cu_invoice_no'] }}</td>
						<td>{{ $invoice['date'] }}</td>
						<td>{{ $invoice['due_date'] }}</td>
						<td>{{ number_format($invoice['total'], 2) }}</td>
						<td>{{ number_format($invoice['tax'], 2) }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="6" style="border-top: 2px solid black;"><b>TOTALS</b></td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['invoicesTotal'], 2) }}</b>
					</td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['invoicesTotalTax'], 2) }}</b>
					</td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No invoices found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('invoicePayments', $options)))
		<h2>Invoice Payments Processed/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['invoicePayments']) && count($payload['invoicePayments']) > 0)
			<p>
				This section provides a summary of all invoice payments that were processed on the given date. It includes key
				details
				such as invoice numbers, amounts, and the status of payments. The data offers insight into daily
				financial
				transactions and assists in tracking outstanding balances.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Payment No</td>
					<td>Date Paid</td>
					<td>Customer</td>
					<td>Note</td>
					<td>Created By</td>
					<td>Account</td>
					<td>Reference</td>
					<td>Payment Mode</td>
					<td>Payment Type</td>
					<td>Currency</td>
					<td>Amount</td>
					<td>Withholding VAT</td>
					<td>Withholding Tax</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['invoicePayments'] as $invoice)
					<tr>
						<td>{{ $invoice['tid'] }}</td>
						<td>{{ $invoice['date'] }}</td>
						<td>{{ $invoice['customer'] }}</td>
						<td>{{ $invoice['note'] }}</td>
						<td>{{ $invoice['creator'] }}</td>
						<td>{{ $invoice['account'] }}</td>
						<td>{{ $invoice['reference'] }}</td>
						<td>{{ $invoice['payment_mode'] }}</td>
						<td>{{ $invoice['payment_type'] }}</td>
						<td>{{ $invoice['currency'] }}</td>
						<td>{{ number_format($invoice['amount'], 2) }}</td>
						<td>{{ number_format($invoice['wh_vat_amount'], 2) }}</td>
						<td>{{ number_format($invoice['wh_tax_amount'], 2) }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="10" style="border-top: 2px solid black;"><b>TOTALS</b></td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['invoicePaymentsTotal'], 2) }}</b></td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['invoicePaymentsTotalWhVat'], 2) }}</b></td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['invoicePaymentsTotalWhTax'], 2) }}</b></td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No invoices found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('purchases', $options)))
		<h2>Purchases Processed/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['purchases']) && count($payload['purchases']) > 0)
			<p>
				This report outlines all purchases made on the specified date. It captures essential information such as
				purchase amounts, vendors, and items acquired, providing a clear view of daily procurement activities
				and
				ensuring accurate record-keeping for future reference.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Purchase ID</td>
					<td>Title</td>
					<td>Supplier</td>
					<td>Status</td>
					<td>Invoice No</td>
					<td>Date</td>
					<td>Due Date</td>
					<td>Total</td>
					<td>Tax</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['purchases'] as $purchase)
					<tr>
						<td>{{ $purchase['tid'] }}</td>
						<td>{{ $purchase['note'] }}</td>
						<td>{{ $purchase['customer'] ?? 'N/A' }}</td>
						<td>{{ $purchase['status'] }}</td>
						<td>{{ $purchase['cu_invoice_no'] }}</td>
						<td>{{ $purchase['date'] }}</td>
						<td>{{ $purchase['due_date'] }}</td>
						<td>{{ number_format($purchase['total'], 2) }}</td>
						<td>{{ number_format($purchase['tax'], 2) }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="7" style="border-top: 2px solid black;"><b>TOTALS</b></td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['purchasesTotal'], 2) }}</b>
					</td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['purchasesTotalTax'], 2) }}</b></td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No purchases found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('bankTransfers', $options)))
		<h2>Bank Transfers Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['bankTransfers']) && count($payload['bankTransfers']) > 0)
			<p>
				This section provides a summary of all Bank Transfers created/updated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Transfer ID</td>
					<td>Date</td>
					<td>Method</td>
					<td>Reference No.</td>
					<td>Account</td>
					<td>Debit Account</td>
					<td>Note</td>
					<td>Created By</td>
					<td>Amount</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['bankTransfers'] as $bt)
					<tr>
						<td>{{ $bt['tid'] }}</td>
						<td>{{ $bt['date'] }}</td>
						<td>{{ $bt['method'] }}</td>
						<td>{{ $bt['refer_no'] }}</td>
						<td>{{ $bt['account'] }}</td>
						<td>{{ $bt['debitAccount'] }}</td>
						<td>{{ $bt['note'] }}</td>
						<td>{{ $bt['createdBy'] }}</td>
						<td>{{ number_format($bt['amount'], 2) }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="8" style="border-top: 2px solid black;"><b>TOTAL</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['bankTransfersTotal'], 2) }}</b></td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No Bank Transfers found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('billPayments', $options)))
		<h2>Bill Payments Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['billPayments']) && count($payload['billPayments']) > 0)
			<p>
				This section provides a summary of all Bill Payments created/updated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>No</td>
					<td>Note</td>
					<td>Supplier</td>
					<td>Paid From</td>
					<td>Date</td>
					<td>Mode</td>
					<td>Reference</td>
					<td>Bill No</td>
					<td>DP No</td>
					<td>Amount</td>
					<td>Unallocated</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['billPayments'] as $bt)
					<tr>
						<td>{{ $bt['tid'] }}</td>
						<td>{{ $bt['note'] }}</td>
						<td>{{ $bt['supplier'] }}</td>
						<td>{{ $bt['paid_from'] }}</td>
						<td>{{ $bt['date'] }}</td>
						<td>{{ $bt['mode'] }}</td>
						<td>{{ $bt['reference'] }}</td>
						<td>{{ $bt['bill_no'] }}</td>
						<td>{{ $bt['dp_no'] }}</td>
						<td>{{ number_format($bt['amount'], 2) }}</td>
						<td>{{ number_format($bt['unallocated'], 2) }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="9" style="border-top: 2px solid black;"><b>TOTAL</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['billPaymentsTotalAmount'], 2) }}</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['billPaymentsTotalUnallocated'], 2) }}</b></td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No Bill Payments found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('yesterdaysWorkLogs', $options)))
		<h2 class="mt-4 text-xl font-semibold">Yesterdays Work Logs Metrics</h2>
		@if(isset($payload['edlMetrics']) && count($payload['edlMetrics']) > 0)
			<p>
				This section contains key metrics from the work logs recorded on {{$dateToday->format('l, jS F, Y')}}.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Metric</td>
					<td>Value</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>Filled Today</td>
					<td>{{ $payload['edlMetrics']['filledToday'] }}</td>
				</tr>
				<tr>
					<td>Not Filled Today</td>
					<td>{{ $payload['edlMetrics']['notFilledToday'] }}</td>
				</tr>
				<tr>
					<td>Tasks Logged Today</td>
					<td>{{ $payload['edlMetrics']['tasksLoggedToday'] }}</td>
				</tr>
				<tr>
					<td>Hours Logged Today</td>
					<td>{{ $payload['edlMetrics']['hoursLoggedToday'] }}</td>
				</tr>
				<tr>
					<td>Today's Unreviewed Logs</td>
					<td>{{ $payload['edlMetrics']['todayUnreviewedLogs'] }}</td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No work logs metrics found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('dailyLabourHours', $options)))
		<h2 class="mt-4 text-xl font-semibold">{{ $payload['sevenDayLabourHours']['chartTitle'] }}</h2>
		@if(isset($payload['sevenDayLabourHours']) && count($payload['sevenDayLabourHours']) > 0)
			This section presents an overview of daily labour hours logged by employees during the specified period. The
			data includes total hours worked per day, helping to track workforce productivity and manage labour costs
			effectively.
			<table class="items">
				<thead>
				<tr>
					<td>Date</td>
					<td>Labour Hours</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['sevenDayLabourHours']['labourDates'] as $index => $date)
					<tr>
						<td>{{ $date }}</td>
						<td>{{ $payload['sevenDayLabourHours']['hoursTotals'][$index] }}</td>
					</tr>
				@endforeach
				<tr>
					<td style="border-top: 2px solid black;"><b>TOTAL</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['sevenDayLabourHoursTotal'], 2) }}</b></td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No labour hours found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('purchaseOrders', $options)))
		<h2>Purchase Orders Processed/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['purchase_orders']) && count($payload['purchase_orders']) > 0)
			<p>
				This section details all purchase orders generated and processed on the given date. It highlights
				important
				aspects like order numbers, vendor details, and items requested, enabling better management of supply
				chain
				operations and inventory planning.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>TID</td>
					<td>Ttile</td>
					<td>Supplier</td>
					<td>Status</td>
					<td>Date</td>
					<td>Due Date</td>
					<td>Currency</td>
					<td>Total</td>
					<td>Tax</td>
					<td>Paid</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['purchase_orders'] as $order)
					<tr>
						<td>{{ $order['tid'] }}</td>
						<td>{{ $order['note'] }}</td>
						<td>{{ $order['supplier'] }}</td>
						<td>{{ $order['status'] }}</td>
						<td>{{ $order['date'] }}</td>
						<td>{{ $order['due_date'] }}</td>
						<td>{{ $order['currency'] }}</td>
						<td>{{ is_numeric($order['total']) ? number_format($order['total'], 2) : $order['total'] }}</td>
						<td>{{ is_numeric($order['tax']) ? number_format($order['tax'], 2) : $order['tax'] }}</td>
						<td>{{ is_numeric($order['paid']) ? number_format($order['paid'], 2) : $order['paid'] }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="7" style="border-top: 2px solid black;"><b>TOTALS</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['purchaseOrdersTotal'], 2) }}</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['purchaseOrdersTotalTax'], 2) }}</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['purchaseOrdersTotalPaid'], 2) }}</b></td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No purchase orders found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('goodsReceiveNotes', $options)))
		<h2>Goods Receive Notes Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['goodsReceiveNotes']) && count($payload['goodsReceiveNotes']) > 0)
			<p>
				This section provides a summary of all Goods Receive Notes created/updated on the specified date.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>GRN ID</td>
					<td>Date</td>
					<td>Title</td>
					<td>Supplier</td>
					<td>Purchase Order</td>
					<td>Delivery Note</td>
					<td>Invoice Number</td>
					<td>Invoice Date</td>
					<td>Created By</td>
					<td>Currency</td>
					<td>Tax</td>
					<td>Total</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['goodsReceiveNotes'] as $grn)
					<tr>
						<td>{{ $grn['tid'] }}</td>
						<td>{{ $grn['date'] }}</td>
						<td>{{ $grn['title'] }}</td>
						<td>{{ $grn['supplier'] }}</td>
						<td>{{ $grn['purchaseOrder'] }}</td>
						<td>{{ $grn['deliveryNote'] }}</td>
						<td>{{ $grn['invoiceNo'] }}</td>
						<td>{{ $grn['invoiceDate'] }}</td>
						<td>{{ $grn['createdBy'] }}</td>
						<td>{{ $grn['currency'] }}</td>
						<td>{{ number_format($grn['total'], 2) }}</td>
						<td>{{ number_format($grn['tax'], 2) }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="10" style="border-top: 2px solid black;"><b>TOTALS</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['goodsReceiveNotesTotalTax'], 2) }}</b></td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['goodsReceiveNotesTotal'], 2) }}</b></td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No Goods Receive Notes found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('stockAlerts', $options)))
		<h2>Stock Alerts on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['stock_alert']) && count($payload['stock_alert']) > 0)
			<p>
				This part of the report highlights stock alerts triggered in the last seven days, indicating items that
				have reached critical inventory levels. Monitoring these alerts ensures that stock shortages are
				promptly
				addressed to avoid disruptions in business operations.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Code</td>
					<td>Name</td>
					<td>Price</td>
					<td>Selling Price</td>
					<td>Quantity</td>
					<td>Alert</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['stock_alert'] as $stock)
					<tr>
						<td>{{ $stock['code'] }}</td>
						<td>{{ $stock['name'] }}</td>
						<td>{{ $stock['price'] }}</td>
						<td>{{ $stock['selling_price'] }}</td>
						<td>{{ $stock['qty'] }}</td>
						<td>{{ $stock['alert'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No stock alerts found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('dailySalesAndExpenses', $options)))
		<h2 class="mt-4 text-xl font-semibold">{{ $payload['sevenDaySalesExpenses']['chartTitle'] }}</h2>
		@if(isset($payload['sevenDaySalesExpenses']) && count($payload['sevenDaySalesExpenses']) > 0)
			<p>
				This report provides a detailed summary of sales and expenses recorded each day within the given
				timeframe.
				By comparing revenues against expenditures, it helps assess daily financial performance and supports
				budget management efforts.
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Date</td>
					<td>Sales Total (KES)</td>
					<td>Expenses Total (KES)</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['sevenDaySalesExpenses']['salesDates'] as $index => $date)
					<tr>
						<td>{{ $date }}</td>
						<td>{{ number_format($payload['sevenDaySalesExpenses']['salesTotals'][$index], 2) }}</td>
						<td>{{ number_format($payload['sevenDaySalesExpenses']['expensesTotals'][$index], 2) }}</td>
					</tr>
				@endforeach
				<tr>
					<td style="border-top: 2px solid black;"><b>TOTALS</b></td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['sdseSalesTotal'], 2) }}</b>
					</td>
					<td style="border-top: 2px solid black;">
						<b>{{ number_format($payload['sdseExpensesTotal'], 2) }}</b></td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No sales and expenses found.</p>
		@endif
	@endif
	<!-- Cashbook summary balance -->
	@if(empty($options) || (!empty($options) && in_array('cashbookBalance', $options)))
		<h2 class="mt-4 text-xl font-semibold">Cashbook Balance Summary</h2>
		@if(isset($payload['cashbookBalance']))
			<table class="items">
				<thead>
					<tr>
						<td>Period</td>
						<td>Debit (Cash-In)</td>
						<td>Credit (Cash-Out)</td>
						<td>Balance</td>
					</tr>
				</thead>
				<tbody>
					@php 
						$periodLabels = ['Today', 'Yesterday', '7 Days Rolling', '30 Days Rolling'];
					@endphp
					@foreach($payload['cashbookBalance'] as $key => $period)
						<tr>
							<td>{{ $periodLabels[$key] }}</td>
							<td>{{ numberFormat(@$period[2]) }}</td>
							<td>{{ numberFormat(@$period[3]) }}</td>
							<td>{{ numberFormat(@$period[4]) }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@else
			<p>Cashbook balance summary not available.</p>
		@endif
	@endif
	<!-- End cashbook summary balance -->
	@if(empty($options) || (!empty($options) && in_array('leaveApplications', $options)))
		<h2>Leave Applications Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['leaveApplications']) && count($payload['leaveApplications']) > 0)
			<p>
				This part of the report highlights Leave Applications on the day
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Employee</td>
					<td>Category</td>
					<td>Submission Date</td>
					<td>Reason</td>
					<td>Duration</td>
					<td>Start Date</td>
					<td>End Date</td>
					<td>Status</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['leaveApplications'] as $la)
					<tr>
						<td>{{ $la['employee'] }}</td>
						<td>{{ $la['category'] }}</td>
						<td>{{ $la['submission_date'] }}</td>
						<td>{{ $la['reason'] }}</td>
						<td>{{ $la['duration'] }}</td>
						<td>{{ $la['start_date'] }}</td>
						<td>{{ $la['end_date'] }}</td>
						<td>{{ $la['status'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No leave Applications alerts found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('healthAndSafety', $options)))
		<h2>Health & Safety Incidents Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['healthAndSafety']) && count($payload['healthAndSafety']) > 0)
			<p>
				This part of the report highlights Health & Safety Incidents on the day
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Date</td>
					<td>Project</td>
					<td>Customer</td>
					<td>Branch</td>
					<td>Involved</td>
					<td>Incident Description</td>
					<td>Cause</td>
					<td>Status</td>
					<td>Timing</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['healthAndSafety'] as $hs)
					<tr>
						<td>{{ $hs['date'] }}</td>
						<td>{{ $hs['project'] }}</td>
						<td>{{ $hs['customer'] }}</td>
						<td>{{ $hs['branch'] }}</td>
						<td>{!! $hs['involved'] !!}</td>
						<td>{!! $hs['incident_desc'] !!}</td>
						<td>{!! $hs['cause'] !!}</td>
						<td>{{ $hs['status'] }}</td>
						<td>{{ $hs['timing'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No Health & Safety incidents found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('qualityTracking', $options)))
		<h2>Quality Tracking Incidents Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['qualityTracking']) && count($payload['qualityTracking']) > 0)
			<p>
				This part of the report highlights Quality Tracking Incidents on the day
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Date</td>
					<td>Project</td>
					<td>Customer</td>
					<td>Branch</td>
					<td>Involved</td>
					<td>Incident Description</td>
					<td>Cause</td>
					<td>Status</td>
					<td>Timing</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['qualityTracking'] as $hs)
					<tr>
						<td>{{ $hs['date'] }}</td>
						<td>{{ $hs['project'] }}</td>
						<td>{{ $hs['customer'] }}</td>
						<td>{{ $hs['branch'] }}</td>
						<td>{!! $hs['involved'] !!}</td>
						<td>{!! $hs['incident_desc'] !!}</td>
						<td>{!! $hs['cause'] !!}</td>
						<td>{{ $hs['status'] }}</td>
						<td>{{ $hs['timing'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No Quality Tracking incidents found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('environmentalTracking', $options)))
		<h2>Environmental Tracking Incidents Created/Updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['environmentalTracking']) && count($payload['environmentalTracking']) > 0)
			<p>
				This part of the report highlights Environmental Tracking Incidents on the day
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Date</td>
					<td>Project</td>
					<td>Customer</td>
					<td>Branch</td>
					<td>Involved</td>
					<td>Incident Description</td>
					<td>Cause</td>
					<td>Status</td>
					<td>Timing</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['qualityTracking'] as $hs)
					<tr>
						<td>{{ $hs['date'] }}</td>
						<td>{{ $hs['project'] }}</td>
						<td>{{ $hs['customer'] }}</td>
						<td>{{ $hs['branch'] }}</td>
						<td>{!! $hs['involved'] !!}</td>
						<td>{!! $hs['incident_desc'] !!}</td>
						<td>{!! $hs['cause'] !!}</td>
						<td>{{ $hs['status'] }}</td>
						<td>{{ $hs['timing'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No Environmental Tracking incidents found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('documentManager', $options)))
		<h2>Document Expiry Manager entries Created/Updated/Related to {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['documentManager']) && count($payload['documentManager']) > 0)
			<p>
				This part of the report highlights Document Expiry Manager entries related to the day
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Name</td>
					<td>Document Type</td>
					<td>Status</td>
					<td>Responsible</td>
					<td>Co Responsible</td>
					<td>Issue Date</td>
					<td>Renewal Date</td>
					<td>Expiry Date</td>
					<td>Renewal Fee</td>
					<td>Alert Days</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['documentManager'] as $dm)
					<tr>
						<td>{{ $dm['name'] }}</td>
						<td>{{ $dm['document_type'] }}</td>
						<td>{{ $dm['status'] }}</td>
						<td>{{ $dm['responsible'] }}</td>
						<td>{{ $dm['co_responsible'] }}</td>
						<td>{{ $dm['issue_date'] }}</td>
						<td>{{ $dm['renewal_date'] }}</td>
						<td>{{ $dm['expiry_date'] }}</td>
						<td>{{ $dm['cost_of_renewal'] }}</td>
						<td>{{ $dm['alert_days'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No Environmental Tracking incidents found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('customerComplaints', $options)))
		<h2>Customer Complaints entered/updated on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['customerComplaints']) && count($payload['customerComplaints']) > 0)
			<p>
				This part of the report highlights Customer Complaints entered on the day
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Date</td>
					<td>Project</td>
					<td>Customer</td>
					<td>Complaint Type</td>
					<td>Status</td>
					<td>Complained To</td>
					<td>Issue Description</td>
					<td>Initial Scale</td>
					<td>Solver</td>
					<td>Current Scale</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['customerComplaints'] as $dm)
					<tr>
						<td>{{ $dm['date'] }}</td>
						<td>{{ $dm['project'] }}</td>
						<td>{{ $dm['customer'] }}</td>
						<td>{{ $dm['complaint_type'] }}</td>
						<td>{{ $dm['status'] }}</td>
						<td>{!! $dm['complain_to'] !!}</td>
						<td>{{ $dm['issue_description'] }}</td>
						<td>{{ $dm['initial_scale'] }}</td>
						<td>{{ $dm['solver'] }}</td>
						<td>{{ $dm['current_scale'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No Customer Complaints found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('sentSms', $options)))
		<h2>Bulk Sms Sent on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['sentSms']) && count($payload['sentSms']) > 0)
			<p>
				This part of the report highlights Bulk Sms Sent on the day
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Content</td>
					<td>Type</td>
					<td>Delivery</td>
					<td>Status</td>
					<td>Time Sent</td>
					<td>Created At</td>
					<td>Cost</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['sentSms'] as $bs)
					<tr>
						<td>{{ maskPasswordInMessage($bs['content']) }}</td>
						<td>{{ $bs['type'] }}</td>
						<td>{{ $bs['delivery'] }}</td>
						<td>{{ $bs['status'] }}</td>
						<td>{{ $bs['time_sent'] }}</td>
						<td>{{ $bs['created'] }}</td>
						<td>{{ number_format($bs['cost'], 2) }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="6" style="border-top: 2px solid black;"><b>TOTALS</b></td>
					<td style="border-top: 2px solid black;"><b>{{ number_format($payload['sentSmsTotal'], 2) }}</b>
					</td>
				</tr>
				</tbody>
			</table>
		@else
			<p>No Bulk Sms found.</p>
		@endif
	@endif
	@if(empty($options) || (!empty($options) && in_array('birthdays', $options)))
		<h2>Employee Birthdays on {{$dateToday->format('l, jS F, Y')}}</h2>
		@if(isset($payload['birthdays']) && count($payload['birthdays']) > 0)
			<p>
				This part of the report highlights Employee Birthdays on the day
			</p>
			<table class="items">
				<thead>
				<tr>
					<td>Name</td>
					<td>Employee No.</td>
					<td>Position</td>
					<td>Department</td>
					<td>Age</td>
				</tr>
				</thead>
				<tbody>
				@foreach($payload['birthdays'] as $dm)
					<tr>
						<td>{{ $dm['name'] }}</td>
						<td>{{ $dm['tid'] }}</td>
						<td>{{ $dm['position'] }}</td>
						<td>{{ $dm['department'] }}</td>
						<td>{{ $dm['age'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p>No Birthdays found.</p>
		@endif
	@endif
</div>
</body>
{{--</body>--}}
</html>
