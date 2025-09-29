<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            border: 2px solid red;
            width: 300px;
            font-size: 9pt;
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 3px;
        }
        .total-deductions, .net-pay {
            font-weight: bold;
        }
        .net-pay {
            color: orange;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        .actions a {
            margin: 0 10px;
            text-decoration: none;
            color: blue;
        }
        .small_text{
            font-size: 7pt; 
        }
        .big_text{
            font-size: 8pt;
        }
        .float_right {
            float: right;
        }
    </style>
</head>
<body>

    <table class="header-table"  width="100%" height="50%">
        <tr>
            <td>
                {{-- <img src="C:\LaravelApps\lvl-erp-v2\public\storage\img\company\theme\1685513534Lean Logo..png"
                     width="100%" height="50%" /> --}}
                <img src="{{ Storage::disk('public')->url('app/public/img/company/theme/' . $company->theme_logo) }}"
                    style="object-fit:contain" width="100%" height="50%" />
            </td>
        </tr>
    </table>
    @php
        $date =  $resource->payroll->payroll_month;
        $monthName = date("F Y", strtotime($date));
        $generated_at = date("d-m-Y", strtotime($resource->payroll->approval_date));
        $employee = $resource->employee;
        $hrmmeta = $resource->hrmmetas;
        $total_deductions = $resource->nhif + $resource->paye + $resource->nssf + $resource->housing_levy;
        $total_non_taxable_allowances = $resource->other_allowances + $resource->benefits;
        $total_non_taxable_deductions = $resource->other_deductions + $resource->advance + $resource->loan;
    @endphp

    <h2 class="big_text">{{ gen4tid('PYRL-',@$resource->payroll->id) }} <br>( {{ $monthName }} ) </h2>

    <table>
        <tr>
            <td>
                <span class="customer-dt-title">EMPLOYEE DETAILS:</b></span><br><hr>

                <b>Employee No :</b> {{ gen4tid('EMP-', $employee->tid) }}<br>
                <b>Employee Name :</b> {{ $employee->first_name }} {{ $employee->last_name }}<br>
                <b>KRA PIN : </b>{{ $hrmmeta->kra_pin }}<br>
                <b>ID NO : </b>{{ $hrmmeta->id_number }}<br>
                <b>Contract Expiry Date :</b> {{ @$resource->salary->end_date }}<br>
                <b>Job Title :</b> {{ $hrmmeta->position }} <br>
                <b>Department :</b> {{ @$hrmmeta->department->name }} <br>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td><b>Gross Pay</b></td>
            <td colspan="6"></td>
            <td><b>{{number_format($resource->basic_plus_allowance, 2)}}</b></td>
        </tr>
        <tr>
            <td colspan="2" class="section-title">Taxable Deductions:</td>
        </tr>
        <tr>
            <td>PAYE*</td>
            <td colspan="2"></td>
            <td>{{number_format($resource->paye, 2)}}</td>
        </tr>
        <tr>
            <td>NSSF</td>
            <td colspan="2"></td>
            <td>{{number_format($resource->nssf, 2)}}</td>
        </tr>
        <tr>
            <td>SHIF</td>
            <td colspan="2"></td>
            <td>{{number_format($resource->nhif, 2)}}</td>
        </tr>
        <tr>
            <td>Housing Levy</td>
            <td colspan="2"></td>
            <td>{{number_format($resource->housing_levy, 2)}}</td>
        </tr>
        <tr class="total-deductions">
            <td><b>Total Taxable Deductions</b></td>
            <td colspan="6"></td>
            <td><b>-{{number_format($total_deductions, 2)}}</b></td>
        </tr>
        
    </table>

    <div class="section-title small_text">PAYE Information:*</div>
    <table class="small_text">
        <tr>
            <td>Gross Pay</td>
            <td>{{number_format($resource->basic_plus_allowance, 2)}}</td>
        </tr>
        <tr>
            <td>Allowable Deductions (NSSF)</td>
            
            <td>{{number_format($resource->nssf, 2)}}</td>
        </tr>
        <tr>
            <td>Taxable Pay</td>
            <td>{{number_format($resource->taxable_gross, 2)}}</td>
        </tr>
        <tr>
            <td>Personal Relief</td>
            <td>{{number_format($resource->personal_relief, 2)}}</td>
        </tr>
        <tr>
            <td>Insurance Relief</td>
            <td>{{number_format($resource->nhif_relief, 2)}}</td>
        </tr>
        <tr>
            <td>Affordable Housing Relief</td>
            <td>{{number_format($resource->ahl_relief, 2)}}</td>
        </tr>
    </table>
    <div class="section-title">Non Taxable Allowances:</div>
    <table>
        <tr>
            <td>Benefits</td>
            <td>{{number_format($resource->benefits,2)}}</td>
        </tr>
        <tr>
            <td>Other Allowances</td>
            <td>{{number_format($resource->other_allowances, 2)}}</td>
        </tr>
        <tr>
            <td colspan="2"><b>Total Non Taxable Allowances</b></td>
            <td><b>{{number_format($total_non_taxable_allowances, 2)}}</b></td>
        </tr>
    </table>
    <div class="section-title">Non Taxable Deductions:</div>
    <table>
        <tr>
            <td>Loans</td>
            <td>{{number_format($resource->loan, 2)}}</td>
        </tr>
        <tr>
            <td>Advance</td>
            <td>{{number_format($resource->advance, 2)}}</td>
        </tr>
        <tr>
            <td>Other Deductions</td>
            <td>{{number_format($resource->other_deductions,2)}}</td>
        </tr>
        <tr>
            <td colspan="2"><b>Total Non Taxable Deductions</b></td>
            <td><b>-{{number_format($total_non_taxable_deductions ,2)}}</b></td>
        </tr>
    </table>
    <table width="100%" cellpadding="2">
        <tr class="net-pay">
            <td><b>Net Pay</b></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td colspan="1"></td>
            <td class="float_right"><b>{{number_format($resource->net_after_bnd, 2)}}</b></td>
        </tr>
    </table>
    <div class="section-title">Approved on: {{$generated_at}}</div>
    <div class="section-title">MPESA: {{$hrmmeta->primary_contact}}</div>
    <div class="section-title">Bank: {{$hrmmeta->bank_name}}</div>
    <div class="section-title">Account Number: {{$hrmmeta->account_number}}</div>

</body>
</html>
