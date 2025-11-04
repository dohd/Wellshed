<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Movement Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 20px;
            color: #000;
        }

        .header-table {
            width: 100%;
            border-bottom: 0.8mm solid #0f4d9b;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-table td:first-child {
            width: 25%;
        }

        .header-table td:last-child {
            text-align: right;
            font-size: 10pt;
            color: #0f4d9b;
        }

        h3 {
            text-align: center;
            color: #0f4d9b;
            text-transform: uppercase;
            margin: 5px 0 10px;
        }

        p {
            font-size: 10pt;
            margin: 3px 0;
        }

        .section-title {
            margin-top: 15px;
            font-weight: bold;
            color: #0f4d9b;
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #777;
            padding: 6px;
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2;
            color: #0f4d9b;
            font-weight: bold;
        }

        .text-right { text-align: right; }
        .center { text-align: center; }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        thead { display: table-header-group; } /* repeat headers on new pages */
    </style>
</head>
<body>

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td>
                @php $image = "img/company/{$company->logo}" @endphp
                <img src="{{ Storage::disk('public')->url($image) }}" alt="Company Logo" style="max-height: 100px;">
            </td>
        </tr>
    </table>

    {{-- TITLE --}}
    <h3>Product Movement Report</h3>
    <p><strong>From:</strong> {{ $start_date }} &nbsp;&nbsp; <strong>To:</strong> {{ $end_date }}</p>

    {{-- MOVEMENT SECTION --}}
    <h4 class="section-title">Movement</h4>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Product Code</th>
                <th>Item Name</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Rate</th>
                <th>Source / Destination</th>
                <th>Reference No.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $move)
                <tr>
                    <td>{{ $move['date'] }}</td>
                    <td>{{ $move['sku'] }}</td>
                    <td>{{ $move['item_name'] }}</td>
                    <td class="text-right">{{ number_format($move['qty'], 2) }}</td>
                    <td class="text-right">{{ number_format($move['rate'], 2) }}</td>
                    <td>{{ $move['source'] }}</td>
                    <td>{{ $move['reference_no'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">No movement records found for the selected date range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SUMMARY SECTION --}}
    <h4 class="section-title">Summary</h4>
    <table>
        <thead>
            <tr>
                <th>Product Code</th>
                <th>Item Name</th>
                <th class="text-right">Opening Qty</th>
                <th class="text-right">Inbound</th>
                <th class="text-right">Outbound</th>
                <th class="text-right">Adjustments</th>
                <th class="text-right">Closing Qty</th>
                <th class="text-right">Closing Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summary as $sum)
                <tr>
                    <td>{{ $sum['sku'] }}</td>
                    <td>{{ $sum['item_name'] }}</td>
                    <td class="text-right">{{ number_format($sum['opening_qty'], 2) }}</td>
                    <td class="text-right">{{ number_format($sum['inbound'], 2) }}</td>
                    <td class="text-right">{{ number_format($sum['outbound'], 2) }}</td>
                    <td class="text-right">{{ number_format($sum['adjustments'], 2) }}</td>
                    <td class="text-right">{{ number_format($sum['closing_qty'], 2) }}</td>
                    <td class="text-right">{{ number_format($sum['closing_value'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">No summary data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- FOOTER --}}
    <footer>
        Page {PAGENO} of {nbpg}
    </footer>

</body>
</html>
