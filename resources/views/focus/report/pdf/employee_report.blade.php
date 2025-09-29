<!DOCTYPE html>
<html>
<head>
    <title>Employee Summary Report</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }
        h1, h2, h5 {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0.5em 0;
            text-align: center;
        }
        h1 {
            font-size: 1.5em;
        }
        h2 {
            font-size: 1.2em;
            color: #555;
        }
        h5 {
            font-size: 1em;
            color: #333;
            font-weight: bold;
            margin-bottom: 1.5em;
        }
        .footer {
            font-size: 7pt;
            text-align: center;
            margin-top: 2em;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1em;
            font-family: Arial, sans-serif;
            font-size: 7pt;
        }
        .table-items th, .table-items td {
            padding: 8px;
            border: 0.1mm solid #000;
            text-align: center;
        }
        .table-items th {
            background-color: #BAD2FA;
            font-weight: bold;
        }
        .table-items tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .align-c {
            text-align: center;
        }
        .mt-3 {
            margin-top: 3em;
        }
        .dotted td {
            border-bottom: 1px dotted #000;
        }
        .page-footer {
            font-size: 9pt;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

    <div style="text-align: center;">
        <h1>{{ $data['company']->cname }}</h1>
        <h2>Employee Summary Report Generated on {{ date('d-m-Y') }}</h2>
        <h2>Employee Summary Report of {{ $data['month_year'] }}</h2>
        <h5>{{ $data['title'] }}</h5>
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Project</th>
                <th>Customer - Branch</th>
                <th>Project Title</th>
                <th>#QT/PI No.</th>
                <th>Employee</th>
                <th>Hrs</th>
                <th>Type</th>
                <th>Job Card</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_hrs = 0;
            @endphp
            @foreach ($data['report']->sortBy('date') as $k => $item)
            @php
                $total_hrs += $item->hrs;
            @endphp
                <tr class="dotted">
                    <td class="align-c">{{ $k + 1 }}</td>
                    <td>{{ $item->date }}</td>
                    <td>{{ $item->project }}</td>
                    <td>{{ $item->customer }}</td>
                    <td>{{ $item->project_name }}</td>
                    <td>{{ $item->tids }}</td>
                    <td>{{ $item->employee }}</td>
                    <td>{{ $item->hrs }}</td>
                    <td>{{ strtoupper($item->type) }}</td>
                    <td>{{ $item->jobcard }}</td>
                    <td>{{ $item->note }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"></td>
                <td>Total Hrs</td>
                <td>{{numberFormat($total_hrs)}}</td>
            </tr>
        </tfoot>
    </table><br>
    
    <h4>Summarized Per Week</h4>
    <table class="table-items">
        <thead>
            <tr>
                <th>Week</th>
                <th>Actual Hours</th>
                <th>Target Hours</th>
                <th>Total Actual Hrs/Total Target Hrs</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_actual_hrs = 0;
                $total_target_hrs = 0;
            @endphp
            @foreach ($data['week_report'] as $weekData)
                <tr>
                    @php
                        $total_actual_hrs += $weekData['actual_hours'];
                        $total_target_hrs += $weekData['target_hours'];
                    @endphp
                    <td>{{$weekData['week']}}</td>
                    <td>{{$weekData['actual_hours']}}</td>
                    <td>{{$weekData['target_hours']}}</td>
                    <td>{{$weekData['cumulative_fraction']}}</td>
                </tr>
            @endforeach
            <tr>
                <td>Totals</td>
                <td>{{$total_actual_hrs}}</td>
                <td>{{$total_target_hrs}}</td>
                <td>{{$total_actual_hrs.'/'.$total_target_hrs}}</td>
            </tr>
        </tbody>
    </table>

    <div class="page-footer">
        Page {PAGENO} of {nb}
    </div>

</body>
</html>
