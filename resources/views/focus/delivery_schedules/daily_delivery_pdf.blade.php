<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-size: 12px; font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #777; padding: 6px; }
        th { background: #eee; text-align: left; }
        h3 { margin-bottom: 0; }
        p { margin: 2px 0 10px; }
        .center { text-align: center; }
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
    </style>
</head>
<body>
    <table class="header-table">
		<tr>
			<td>
				@php $image = "img/company/{$company->logo}" @endphp
				<img src="{{  Storage::disk('public')->url($image) }}" style="object-fit:contain" width="100%">
			</td>
		</tr>
	</table>

<h3 class="center">Daily Delivery Report</h3>
<p>From Date: {{ $start_date }}</p>
<p>Date To: {{ $end_date }}</p>

<table>
    <thead>
        <tr>
            <th>Schedule No.</th>
            <th>Customer</th>
            <th>Order</th>
            <th>Date</th>
            <th>Status</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report as $schedule)
            <tr>
                <td>{{ gen4tid('DS-',$schedule->tid) }}</td>
                <td>{{ $schedule->order->customer->name ?? '-' }}</td>
                <td>{{ gen4tid('ORD-',$schedule->order->tid) ?? '-' }}</td>
                <td class="center">{{ $schedule->delivery_date }}</td>
                <td>{{ str_replace('_','',ucfirst($schedule->status)) }}</td>
                <td>{{ $schedule->order->location->sub_zone_name ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="center">No deliveries scheduled</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
