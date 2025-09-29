<!DOCTYPE html>
<html>

<head>
    <title>KPI Summary Report</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }

        h1,
        h2,
        h5 {
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

        .table-items th,
        .table-items td {
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
        .text-danger{
            color: red;
        }
    </style>
</head>

<body>

    <div style="text-align: center;">
        <h1>{{ $data['company']->cname }}</h1>
        <h2>KPI Summary Report Generated on {{ date('d-m-Y') }}</h2>
        {{-- <h2>KPI Summary Report of {{ $data['month_year'] }}</h2> --}}
        <h5>{{ 'KPI Summary Report' }}</h5>
        <h5>{{ $data['employee'] }}</h5>
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th>#</th>
                <th>KPI</th>
                <th>Key Activities</th>
                {{-- <th>Target/UoM</th> --}}
                <th>Score / Target (Frequency)</th>
            </tr>
        </thead>
        <tbody>

            @php $count = 1; @endphp
            @foreach ($data['tasks'] as $tasks)
                @foreach ($tasks as $k => $item)
                    <tr class="dotted">
                        <td class="align-c">{{ $count }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ @$item->key_activity->name ?? $item->key_activities }}</td>
                        {{-- <td>{{ $item->target . '/' . $item->uom }}</td> --}}
                        <td>{{ $item->task_no }}</td>
                    </tr>
                    @php $count++; @endphp
                @endforeach
            @endforeach
        </tbody>
    </table><br>
    <h2 class="text-danger"><b>Please Note: Your missed target might affect your Salary/Wages at the end of the month</b></h2>

    <div class="page-footer">
        Page {PAGENO} of {nb}
    </div>

</body>

</html>
