<!DOCTYPE html>
<html>
<head>
    <title>Daily Orders Summary</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            font-size: 13px;
        }
        h1, h2 {
            text-align: center;
            margin: 0;
            padding: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: center;
        }
        th {
            background-color: #f7f7f7;
            text-align: left;
        }
        .summary-table td, .summary-table th {
            text-align: center;
        }
        .text-center { text-align: center; }
        .no-border { border: none; }
        .section-title {
            margin-top: 25px;
            font-size: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>

    <h1>{{ $company->name ?? 'Company Name' }}</h1>
    <h2>DAILY ORDERS SUMMARY</h2>

    <table class="no-border">
        <tr>
            <td><strong>Generated On:</strong> {{ now()->format('d-M-Y') }}</td>
            <td class="text-center">
                <strong>Between</strong> {{ $start_date->format('d-M-Y') }}
                <strong>And</strong> {{ $end_date->format('d-M-Y') }}
            </td>
        </tr>
    </table>

    {{-- 1️⃣ Orders Overview --}}
    <h3 class="section-title">Orders Overview</h3>
    <table class="summary-table">
        <tr>
            <th>Metric</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Gross Orders</td>
            <td>{{ number_format($summary['gross_orders'], 2) }}</td>
        </tr>
        <tr>
            <td>Taxes Collected</td>
            <td>{{ number_format($summary['taxes'], 2) }}</td>
        </tr>
        <tr>
            <td>Total Receipts</td>
            <td>{{ number_format($summary['total_receipts'], 2) }}</td>
        </tr>
        <tr>
            <td>Number of Orders</td>
            <td>{{ $summary['orders_count'] }}</td>
        </tr>
    </table>

    {{-- 2️⃣ Breakdown by Category --}}
    <h3 class="section-title">Breakdown by Category</h3>
    <table>
        <thead>
            <tr>
                <th>Item Category</th>
                <th>Units Ordered</th>
                <th>Gross Orders</th>
                <th>Taxes</th>
                <th>Net Orders</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat['name'] }}</td>
                    <td>{{ $cat['units'] }}</td>
                    <td>{{ number_format($cat['gross'], 2) }}</td>
                    <td>{{ number_format($cat['tax'], 2) }}</td>
                    <td>{{ number_format($cat['net'], 2) }}</td>
                    <td>{{ number_format($cat['percentage'], 1) }}%</td>
                </tr>
            @endforeach
            <tr>
                <th>TOTAL</th>
                <th>{{ $totals['units'] }}</th>
                <th>{{ number_format($totals['gross'], 2) }}</th>
                <th>{{ number_format($totals['tax'], 2) }}</th>
                <th>{{ number_format($totals['net'], 2) }}</th>
                <th>100%</th>
            </tr>
        </tbody>
    </table>

    {{-- 3️⃣ Payment Receipts Summary --}}
    <h3 class="section-title">Payment Receipts Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Payment Method</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment['mode'] }}</td>
                    <td>{{ number_format($payment['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">No Payments Recorded</td>
                </tr>
            @endforelse
            <tr>
                <th>TOTAL</th>
                <th>{{ number_format(collect($payments)->sum('amount'), 2) }}</th>
            </tr>
        </tbody>
    </table>

    {{-- 4️⃣ Delivery Schedule Summary (Planned) --}}
    <h3 class="section-title">Delivery Schedule Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Delivery Date</th>
                <th>Scheduled Orders</th>
                <th>Items Count</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deliverySchedules as $schedule)
                <tr>
                    <td>{{ $schedule->delivery_date->format('d-M-Y') }}</td>
                    <td>{{ $schedule->orders_count }}</td>
                    <td>{{ $schedule->items_count }}</td>
                    <td>{{ number_format($schedule->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No Delivery Schedules Found</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- 5️⃣ Actual Deliveries Summary (Executed) --}}
    <h3 class="section-title">Actual Deliveries Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Delivery Date</th>
                <th>Delivered Orders</th>
                <th>Items Delivered</th>
                <th>Total Delivered Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deliveries as $delivery)
                <tr>
                    <td>{{ $delivery->delivered_on->format('d-M-Y') }}</td>
                    <td>{{ $delivery->orders_count }}</td>
                    <td>{{ $delivery->items_count }}</td>
                    <td>{{ number_format($delivery->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No Deliveries Recorded</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- 6️⃣ Closing Balances --}}
    <h3 class="section-title">Closing Balances</h3>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Opening Cash Float</td>
                <td>{{ number_format($closingBalances['opening'], 2) }}</td>
            </tr>
            <tr>
                <td>Cash Received</td>
                <td>{{ number_format($closingBalances['received'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Closing Balance</strong></td>
                <td><strong>{{ number_format($closingBalances['closing'], 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated by {{ config('app.name') }} — {{ now()->format('d M Y, h:i A') }}
    </div>

</body>
</html>
