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
        tfoot th {
            background: #d9edf7;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                @php $image = "img/company/{$company->logo}" @endphp
                <img src="{{ Storage::disk('public')->url($image) }}" style="object-fit:contain" width="100%">
            </td>
        </tr>
    </table>

    <h3 class="center">Product Movement Report</h3>
    <p>From Date: {{ $start_date }}</p>
    <p>Date To: {{ $end_date }}</p>

    @php
        $total_planned = $schedule_items->sum('qty');
        $total_delivered = $schedule_items->sum('delivered_qty');
        $total_returned = $schedule_items->sum('returned_qty');
        $total_remaining = $schedule_items->sum('remaining_qty');
    @endphp

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Product Code</th>
            <th>Delivery Date</th>
            <th>Product Name</th>
            <th>Planned Qty</th>
            <th>Delivered Qty</th>
            <th>Returned Qty</th>
            <th>Remaining Qty</th>
        </tr>
    </thead>
    <tbody>
        @forelse($schedule_items as $i => $item)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $item->product->code ?? '-' }}</td>
                <td>{{ $item->schedule->delivery_date }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td class="center">{{ $item->qty ?? 0 }}</td>
                <td class="center">{{ $item->delivered_qty }}</td>
                <td class="center">{{ $item->returned_qty }}</td>
                <td class="center">{{ $item->remaining_qty ?? 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="center">No deliveries scheduled</td>
            </tr>
        @endforelse
    </tbody>

    @if(count($schedule_items))
    <tfoot>
        <tr>
            <th colspan="4" class="center">Totals</th>
            <th>{{ $total_planned }}</th>
            <th>{{ $total_delivered }}</th>
            <th>{{ $total_returned }}</th>
            <th>{{ $total_remaining }}</th>
        </tr>
    </tfoot>
    @endif
</table>

</body>
</html>
