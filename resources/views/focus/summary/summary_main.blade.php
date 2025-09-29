<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Print Summary</title>
    <style>
        body {
            color: #2B2000;
        }

        table {
            width: 100%;
            line-height: 16pt;
            border-collapse: collapse;
        }

        .mfill {
            background-color: #eee;
        }

        .descr {
            font-size: 10pt;
            color: #515151;
        }

        .container {
            width: 210mm;
            height: 297mm;
            margin: auto;
            margin-bottom: 0;
            padding: 0mm;
            border: 0;
            font-size: 16pt;
            color: #000;
        }

        .container table {
            width: 100%;
            text-align: left;
        }

        .plist tr td {
            line-height: 12pt;
        }

        .subtotal-container {
            width: 35%;
            margin-left: auto;
        }

        .subtotal tr td {
            line-height: 10pt;
        }

        .sign {
            text-align: right;
            font-size: 10pt;
            margin-right: 110pt;
        }

        .sign1 {
            text-align: right;
            font-size: 10pt;
            margin-right: 90pt;
        }

        .sign2 {
            text-align: right;
            font-size: 10pt;
            margin-right: 115pt;
        }

        .sign3 {
            text-align: right;
            font-size: 10pt;
            margin-right: 115pt;
        }

        .terms {
            font-size: 9pt;
            line-height: 16pt;
        }

        .container table td {
            padding: 8pt 4pt 5pt 4pt;
            vertical-align: top;

        }

        .container table tr.top table td {
            padding-bottom: 20pt;
        }

        .container table tr.top table td.title {
            font-size: 45pt;
            line-height: 45pt;
            color: #555;
        }

        .container table tr.information table td {
            padding-bottom: 20pt;
        }

        .container table tr.heading td {
            background: #515151;
            color: #FFF;
            padding: 6pt;
        }

        .container table tr.details td {
            padding-bottom: 20pt;
        }

        .container table tr.item td {
            border-bottom: 1px solid #fff;
        }

        .container table tr.item.last td {
            border-bottom: none;
        }

        .container table tr.total td:nth-child(4) {
            border-top: 2px solid #fff;
            font-weight: bold;
        }

        .myco {
            width: 500pt;
        }

        .myco2 {
            width: 290pt;
        }

        .myw {
            width: 230pt;
            line-height: 20pt;
            text-align: center;
        }

        .summary {
            background: #515151;
            color: #FFF;
            padding: 6pt;

        }
    </style>
</head>
<body>
    <div class="container">
        <table>
            <tr>
                <td colspan="3" class="myco">
                    <img src="{{ Storage::disk('public')->url('app/public/img/company/' . config('core.logo')) }}" style="object-fit:contain" width="100%">
                </td>
            </tr>
            <tr>
                <td colspan="3" class="myw">
                    <h4>{{$lang['title']}}</h4>
                    <small>{{ trans('meta.generated_on') }}: {{ dateFormat(date('Y-m-d'), 'd-M-Y') }}</small>
                </td>
            </tr>
        </table>
        @yield('summary_body')
        <br>
        <hr>
    </div>
</body>
</html>