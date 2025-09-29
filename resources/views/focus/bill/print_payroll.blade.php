<html>

<head>
    <title>
        @php
            
            $tid = $resource->employee_id;
            
            $tid = gen4tid('EMP', $tid);
        @endphp
        PaySlip
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
        }

        .table-taxable {
            width: 98%;
            margin: .5rem;
        }

        .border {
            border: 1px solid black;
            border-collapse: collapse;
        }

        #payment {
            display: flex;
            flex-direction: row;
        }

        #signature {
            display: flex;
            flex-flow: row;
            width: 100%;
        }

        

        #payment div {
            margin-right: 15px;
            padding-right: 15px;
        }

        .horizontal_dotted_line {
            margin-bottom: 5px;
            margin-left: 5px;
            border-bottom: 2px dotted;
            width: 80%;
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
                <img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}"
                    style="object-fit:contain" width="100%" />
            </td>
        </tr>
    </table>
    @php
        $date =  $resource->payroll->payroll_month;
         $monthName = date("j F Y", strtotime($date));
    @endphp
    <table class="doc-table">
        <tr>
            <td class="doc-title-td">
                <span class='doc-title'>
                    <b>PAYSLIP</b> 
                </span>
            </td>
        </tr>
        <tr>
            <td class="doc-title-td">
                <span class='doc-title'>
                    {{ gen4tid('PYRL-',$resource->payroll->id) }} ( {{ $monthName }} )  
                </span>
                
            </td>
        </tr>
    </table><br>
    <table class="customer-dt" cellpadding="5">
        {{-- if($resource->employee){ --}}
        @php
            $employee = $resource->employee;
            $hrmmeta = $resource->hrmmetas;
            $totalnontaxableallowances = $resource->total_benefits + $resource->total_other_allowances;
            $gross_taxable_allowance = $resource->total_allowance - $resource->tx_deductions;
            $totalnontaxdeductions = $resource->total_other_deduction + $resource->nhif + $resource->advance + $resource->loan;
            $gross_non_taxable_allowance = $totalnontaxableallowances - $totalnontaxdeductions;
        @endphp
        <tr>
            <td width="50%">
                <span class="customer-dt-title">EMPLOYEE DETAILS:</span><br><br>

                <b>Employee No :</b> {{ gen4tid('EMP-', $employee->id) }}<br>
                <b>Employee Name :</b> {{ $employee->first_name }} {{ $employee->last_name }}<br>
                <b>KRA PIN :</b>{{ $hrmmeta->kra_pin }}<br>
                <b>Contract Expiry Date :</b> {{ $resource->salary->end_date }}<br>

                <b>Job Title :</b> {{ $hrmmeta->position }} <br>
                <b>Department :</b> {{ $hrmmeta->department->name }} <br>
            </td>
            <td width="5%">&nbsp;</td>
            <td width="45%">
                <span class="customer-dt-title">PAYSLIP DETAILS:</span><br><br>
                <b>Basic Pay :</b> {{ amountFormat($resource->salary->basic_salary) }}<br><br>
                <b>Taxable Gross Allowances :</b>{{ amountFormat($gross_taxable_allowance) }}<br>
                <b>NSSF :</b> {{ amountFormat($resource->nssf) }} <br>
                <b>SHIF :</b> {{ amountFormat($resource->nhif) }} <br>
                <b>PAYE :</b> {{ amountFormat($resource->paye) }} <br>
                <b>Non-Taxable Gross Allowances :</b> {{ amountFormat($gross_non_taxable_allowance) }} <br>
                <b>Net Pay :</b> {{ amountFormat($resource->net_after_bnd) }} <br>
            </td>
        </tr>
        {{-- } --}}

    </table>
    <p><b>Payment Details</b></p>
    <table style="width:100%">
        <tbody>
            <tr>
                <td>
                    <h4>Date:</h4> {{date('d/m/Y', strtotime($resource->payroll->payroll_month))}}
                </td>
                <td>
                    <h4>Amount:</h4> {{ amountFormat($resource->net_after_bnd) }}
                </td>
                <td>
                    <h4> Acc.No :</h4> {{ $hrmmeta->account_number }}
                </td>
                <td>
                    <h4>Payment Method:</h4> MPESA
                </td>
            </tr>
        </tbody>
    </table>
    <p><b>Taxable Allowances and Deductions</b></p>
    <table class="border" style="width:100%">
        <thead>
            <tr>
                <td class="border" width="50%">
                    Allowances
                </td>
                <td class="border" width="50%">
                    Deductions
                </td>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td class="border" width="50%">
                    <table class="table-taxable border" cellpadding="8">
                        <thead>
                            <tr>
                                <th class="border">Item</th>
                                <th class="border">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border">
                                    Transport
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->transport_allowance) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    Housing
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->house_allowance) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    Other
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->other_allowance) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    <b>Total</b>
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->total_allowance) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="border" width="50%">
                    <table class="table-taxable border" cellpadding="8">
                        <thead>
                            <tr>
                                <th class="border">Item</th>
                                <th class="border">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border">
                                    NSSF
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->nhif) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    Absenteeism ({{ $resource->absent_days }})
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->absent_deduction) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="border">
                                    Other
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->total_other_deduction) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    <b>Total</b>
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->tx_deductions) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="border"><b>Gross Taxable Allowance</b> (Allowances-Deductions) </td>
                <td class="border"> <b> {{ amountFormat($gross_taxable_allowance) }}</td>
            </tr>
        </tbody>
    </table><br>

    <p><b>Non-Taxable Allowances and Deductions</b></p>
    <table class="border" style="width:100%">
        <thead>
            <tr>
                <td class="border" width="50%">
                    Allowances
                </td>
                <td class="border" width="50%">
                    Deductions
                </td>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td class="border" width="50%">
                    <table class="table-taxable border" cellpadding="8">
                        <thead>
                            <tr>
                                <th class="border">Item</th>
                                <th class="border">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border">
                                    Benefits
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->total_benefits) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="border">
                                    Other Allowances
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->total_other_allowances) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    <b>Total</b>
                                </td>

                                <td class="border">
                                    {{ amountFormat($totalnontaxableallowances) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="border" width="50%">
                    <table class="table-taxable border" cellpadding="8">
                        <thead>
                            <tr>
                                <th class="border">Item</th>
                                <th class="border">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border">
                                    Loan
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->loan) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    Advance
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->advance) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    SHIF
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->nhif) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="border">
                                    Other
                                </td>
                                <td class="border">
                                    {{ amountFormat($resource->total_other_deduction) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    <b>Total</b>
                                </td>

                                <td class="border">
                                    {{ amountFormat($totalnontaxdeductions) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="border"><b>Gross Non-Taxable Allowance</b> (Allowances-Deductions) </td>
                <td class="border"> <b> {{ amountFormat($gross_non_taxable_allowance) }}</td>
            </tr>
        </tbody>
    </table><br><br>

    <table style="width:100%">

        <tbody>
           
            <tr>

                <td style="width: 50%">
                    <div class="row" style="display: inline-block" >
                        <p>Prepared By</p>
                        <hr >
                    </div>
                   
                </td>
                <td  style="width: 50%">
                    <div class="row" style="display: inline-block" >
                        <p>Employee Signature</p>
                        <hr >
                    </div>
                   
                </td>
            </tr>
        </tbody>
    </table>
    <div>{!! $resource->extra_footer !!}</div>
</body>

</html>
