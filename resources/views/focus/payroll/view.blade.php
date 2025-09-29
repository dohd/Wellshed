@extends ('core.layouts.app')

@section('title', 'Payroll Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-2">
            <div class="content-header-left col-md-6 col-12">
                <h4 class="content-header-title mb-0">View Payroll</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.payroll.partials.payroll-header-buttons')
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div id="responseMessage"></div>
                <div class="row mb-2">
                    <div class="col-md-12 col-12">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            <a href="#" class="btn btn-info mr-1" data-toggle="modal" data-target="#statusModal">
                                <i class="fa fa-pencil" aria-hidden="true"></i> Approve
                            </a>
                            <form action="{{route('biller.payroll.send_mail')}}" method="post">
                                @csrf
                                <input type="hidden" name="id" id="" value="{{$payroll->id}}">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-paper-plane-o"></i> {{trans('general.send')}}
                                </button>
                            </form>
                        </div>
                    </div>

                    @if ($payroll->status === 'approved')
                        <div class="col-12 mt-1">
                            <div class="d-flex flex-row align-items-center">
                                <h2 class="mb-0"><i>THIS PAYROLL IS APPROVED</i></h2>
                                <a href="#" data-toggle="modal" data-target="#statusModal">
                                    <small class="ml-2" style="color: red;"> change </small>
                                </a>
                            </div>
                            <div class="row mt-1">
                                <h4 class="col-12 col-lg-8">{{ $payroll->approval_note }}</h4>
                            </div>
                        </div>
                    @endif
                </div>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="base-tab0" data-toggle="tab" aria-controls="tab0" href="#tab0"
                            role="tab" aria-selected="true"><span class="">Payroll </span>

                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                            role="tab" aria-selected="true"><span class="">Basic </span>

                        </a>
                    </li>
                </ul>
                <div class="tab-content px-1 pt-1">
                    <div class="tab-pane active" id="tab0" role="tabpanel" aria-labelledby="base-tab0">
                        <div class="card-content">
                            <div class="card-body">

                                <div class="row">

                                    <div class="col-12 col-lg-6">
                                        <h3 class="mb-1">Employees' Pay, Deductions & Taxes</h3>
                                        <table class="table table-bordered table-sm">
                                            @php


                                                if(\Illuminate\Support\Facades\Auth::user()->ins === 2) $details = [
                                                    'Payroll No' => gen4tid('PYRLL-', $payroll->tid),
                                                    'Processing Date' => (new DateTime($payroll->processing_date))->format('M jS Y'),
                                                    'Payroll Month' => (new DateTime($payroll->payroll_month))->format('M Y'),
                                                    'Days of Month' => $payroll->total_month_days,
                                                    'Working Days' => $payroll->working_days,
                                                    'Salary' => numberFormat($payroll->salary_total),
                                                    'Erp Sales Commissions' => numberFormat($payroll->erp_commission_total),
                                                    'Allowances' => numberFormat($payroll->allowance_total),
                                                    'NSSF' => numberFormat($payrollItems->sum('nssf_tally')),
                                                    'Housing Levy' => numberFormat($payrollItems->sum('housing_levy_tally')),
                                                    'SHIF' => numberFormat($payrollItems->sum('nhif_tally')),
                                                    'Taxable Pay Deductions' => numberFormat($payrollItems->sum('taxable_deductions_tally')),

    //                                                'Total Allowances' => numberFormat($payrollItems->sum('allowances_tally')),
    //                                                'Total Deductions' => numberFormat($payrollItems->sum('deductions_tally')),
                                                    'Taxable Pay' => numberFormat($payrollItems->sum('taxable_gross_tally')),
                                                    'PAYE' => numberFormat($payroll->paye_total),
                                                    'Pay after Tax' => numberFormat( bcsub($payrollItems->sum('taxable_gross_tally'), $payroll->paye_total, 2)),
                                                    'Employee Advances' => numberFormat($items->sum('advance')),
                                                    'Total Netpay' => numberFormat($payroll->total_salary_after_bnd),

    //                                                "Employer's NSSF" => numberFormat($payrollItems->sum('nssf_tally')),
    //                                                "Employer's Housing Levy" => numberFormat($payrollItems->sum('housing_levy_tally')),
                                                ];

                                                else $details = [
                                                    'Payroll No' => gen4tid('PYRLL-', $payroll->tid),
                                                    'Processing Date' => (new DateTime($payroll->processing_date))->format('M jS Y'),
                                                    'Payroll Month' => (new DateTime($payroll->payroll_month))->format('M Y'),
                                                    'Days of Month' => $payroll->total_month_days,
                                                    'Working Days' => $payroll->working_days,
                                                    'Salary' => numberFormat($payroll->salary_total),
                                                    'Allowances' => numberFormat($payroll->allowance_total),
                                                    'NSSF' => numberFormat($payrollItems->sum('nssf_tally')),
                                                    'Housing Levy' => numberFormat($payrollItems->sum('housing_levy_tally')),
                                                    'SHIF' => numberFormat($payrollItems->sum('nhif_tally')),
                                                    'Taxable Pay Deductions' => numberFormat($payrollItems->sum('taxable_deductions_tally')),

    //                                                'Total Allowances' => numberFormat($payrollItems->sum('allowances_tally')),
    //                                                'Total Deductions' => numberFormat($payrollItems->sum('deductions_tally')),
                                                    'Taxable Pay' => numberFormat($payrollItems->sum('taxable_gross_tally')),
                                                    'PAYE' => numberFormat($payroll->paye_total),
                                                    'Pay after Tax' => numberFormat( bcsub($payrollItems->sum('taxable_gross_tally'), $payroll->paye_total, 2)),
                                                    'Employee Advances' => numberFormat($items->sum('advance')),
                                                    'Total Netpay' => numberFormat($payroll->total_salary_after_bnd),
    //                                                "Employer's NSSF" => numberFormat($payrollItems->sum('nssf_tally')),
    //                                                "Employer's Housing Levy" => numberFormat($payrollItems->sum('housing_levy_tally')),
                                                ];
                                            @endphp
                                            @foreach ($details as $key => $val)
                                                <tr>
                                                    <th width="50%">{{ $key }}</th>
                                                    <td>{{ $val }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>

                                    <div class="col-12 col-lg-6">
                                        <h3 class="mb-1">Employer's Contributions</h3>
                                        <table class="table table-bordered table-sm">
                                            @php
                                                $details = [
                                                    "Employer's NSSF" => numberFormat($payrollItems->sum('nssf_tally')),
                                                    "Employer's Housing Levy" => numberFormat($payrollItems->sum('housing_levy_tally')),
                                                ];
                                            @endphp
                                            @foreach ($details as $key => $val)
                                                <tr>
                                                    <th width="50%">{{ $key }}</th>
                                                    <td>{{ $val }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-body">
                                    <table id="employeeTbl"
                                           class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%"                                            width="100%">
                                        <thead>
                                            <tr>
                                                <th>Employee Id</th>
                                                <th>Employee Name</th>
                                                <th>Fixed Salary</th>
                                                <th>Max Hourly Salary</th>
                                                <th>Hourly Wage</th>
                                                <th>Man Hours</th>
                                                <th>Basic Hourly Salary</th>
                                                <th>Absent Days</th>
                                                {{-- <th>Present Days</th> --}}
                                                <th>Absent Daily Deduction</th>
                                                <th>Absent Total Deduction</th>
                                                <th>Total Basic Salary</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $i = 1;
                                                // dd(count($payrollItems));
                                            @endphp
                                            @foreach ($items as $item)
                                            @php
                                                $valid_token = token_validator('', 'q'.$item->id, true);
                                                // dd($item->employee);
                                            @endphp
                                                <tr>
                                                    <td>{{ gen4tid('EMP-', @$item->employee->tid) }}</td>
                                                    <td>{{ @$item->employee->fullname }}</td>
                                                    <td>{{ numberFormat($item->fixed_salary) }}</td>
                                                    <td>{{ $item->max_hourly_salary }}</td>
                                                    <td>{{ $item->pay_per_hr }}</td>
                                                    <td>{{ $item->man_hours }}</td>
                                                    <td>{{ $item->basic_hourly_salary }}</td>
                                                    <td>{{ $item->absent_days }}</td>
                                                    <td>{{ $item->absent_daily_deduction }}</td>
                                                    <td>{{ $item->absent_total_deduction }}</td>
                                                    <td>{{ $item->basic_salary }}</td>

                                                    <td>
                                                       <a href={{ route('biller.print_payroll', [$item->id, 13, $valid_token,1]) }} class="btn btn-purple round"
                                                           target="_blank" data-toggle="tooltip"
                                                           data-placement="top" title="Print"><i
                                                               class="fa fa-print" aria-hidden="true"></i></a>
                                                    </td>
                                                                {{-- <td>{{ route('biller.print_payroll', [$item->id, 12, $valid_token,1]) }}</td> --}}
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="allowanceTbl" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>Absent Days</th>
                                            <th>Housing Allowance</th>
                                            <th>Transport</th>
                                            <th>Other Allowances</th>
                                            <th>Total Allowance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($payroll->payroll_items as $item)
                                            <tr>
                                                <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                <td>{{ $item->employee_name }}</td>
                                                <td>{{ $item->absent_days }}</td>
                                                <td>
                                                    {{ numberFormat($item->house_allowance) }}
                                                <td>
                                                    {{ numberFormat($item->transport_allowance) }}
                                                </td>
                                                <td>
                                                    {{ numberFormat($item->other_allowance) }}
                                                </td>
                                                <td>
                                                    {{ numberFormat($item->total_allowance) }}
                                                </td>

                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="deductionTbl" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>Basic + Allowances</th>
                                            <th>NSSF</th>
                                            <th>SHIF</th>
                                            <th>Gross Pay</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($payroll->payroll_items as $item)
                                            <tr>
                                                <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                <td>{{ $item->employee_name }}</td>
                                                <td>{{ numberFormat($item->total_basic_allowance) }}</td>
                                                <td>{{ numberFormat($item->nssf) }}</td>
                                                <td>{{ numberFormat($item->nhif) }}</td>
                                                <td>{{ numberFormat($item->gross_pay) }}</td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="payeTbl" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>Gross Pay</th>
                                            <th>NSSF</th>
                                            <th>SHIF</th>
                                            <th>PAYE</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($payroll->payroll_items as $item)
                                            <tr>
                                                <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                <td>{{ $item->employee_name }}</td>
                                                <td>{{ numberFormat($item->gross_pay) }}</td>
                                                <td>{{ numberFormat($item->nssf) }}</td>
                                                <td>{{ numberFormat($item->nhif) }}</td>
                                                <td>{{ numberFormat($item->paye) }}</td>

                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab5" role="tabpanel" aria-labelledby="base-tab5">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="nhifTbl" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>Taxable Pay</th>
                                            <th>SHIF</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($payroll->payroll_items as $item)
                                            <tr>
                                                <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                <td>{{ $item->employee_name }}</td>
                                                <td>{{ numberFormat($item->taxable_gross) }}</td>
                                                <td>{{ numberFormat($item->nhif) }}</td>
                                                <input type="hidden" name="payroll_id"
                                                    value="{{ $item->payroll_id }}">
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab6" role="tabpanel" aria-labelledby="base-tab6">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="otherBenefitsTbl"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>Other Allowances Totals</th>
                                            <th>Benefits Totals</th>
                                            <th>Deductions</th>
                                            <th>Other Deductions Totals</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($payroll->payroll_items as $item)
                                            @if ($item)
                                                <tr>
                                                    <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                    <td>{{ $item->employee_name }}</td>
                                                    <td>{{ numberFormat($item->total_other_allowances) }}
                                                    </td>
                                                    <td>{{ numberFormat($item->total_benefits) }}
                                                    </td>


                                                    <td>
                                                        <table class="table" style="width: 100%;">
                                                            <thead>
                                                                <tr>
                                                                    <th>Loan</th>
                                                                    <th>Advance</th>
                                                                </tr>

                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>{{ numberFormat($item->loan) }}

                                                                    </td>
                                                                    <td>{{ numberFormat($item->advance) }}

                                                                    </td>
                                                                </tr>
                                                            </tbody>


                                                        </table>
                                                    </td>

                                                    <td>{{ numberFormat($item->total_other_deduction) }}
                                                    </td>


                                                </tr>
                                            @endif
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab7" role="tabpanel" aria-labelledby="base-tab7">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="summaryTable"
                                    class="table table-striped table-responsive table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>Total Basic Salary</th>
                                            <th>Total Tx Allowances</th>
                                            <th>NSSF</th>
                                            <th>Total Taxable Deductions</th>
                                            <th>Taxable Gross</th>
                                            <th>Total PAYE</th>
                                            <th>SHIF</th>
                                            <th>Other Allowances</th>
                                            <th>Other Benefits</th>
                                            <th>Other Deductions</th>
                                            <th>Net Pay</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($payroll->payroll_items as $item)
                                            @if ($item)
                                                @php
                                                    $salary = $item->basic_pay;    
                                                    $allowances = $item->total_allowance;
                                                    $deductions = $item->tx_deductions;
                                                    $paye = $item->paye;
                                                    $nssf = $item->nssf;
                                                    $nhif = $item->nhif;
                                                    $total_other_allowances = $item->total_other_allowances;
                                                    $taxable_gross = $item->taxable_gross;
                                                    $benefits = $item->total_benefits;
                                                    $loan_advance = $item->loan + $item->advance;
                                                    $otherdeductions = $item->total_other_deduction + $loan_advance;
                                                    $net_pay = $item->gross_pay - ($item->paye + $item->nhif);
                                                    $net = $net_pay + $total_other_allowances + $benefits - $otherdeductions;
                                                @endphp
                                                <tr>
                                                    <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                    <td>{{ $item->employee_name }}</td>
                                                    <td>{{ numberFormat($salary) }}</td>
                                                    <td>{{ numberFormat($allowances) }}</td>
                                                    <td>{{ numberFormat($nssf) }}</td>
                                                    <td>{{ numberFormat($deductions) }}</td>
                                                    <td>{{ numberFormat($taxable_gross) }}</td>
                                                    <td>{{ numberFormat($paye) }}</td>
                                                    <td>{{ numberFormat($nhif) }}</td>
                                                    <td>{{ numberFormat($total_other_allowances) }}</td>
                                                    <td>{{ numberFormat($benefits) }}</td>
                                                    <td>{{ numberFormat($otherdeductions) }}</td>
                                                    <td class="netpay">{{ numberFormat($item->netpay) }} </td>


                                                </tr>
                                            @endif
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab8" role="tabpanel" aria-labelledby="base-tab8">
                        <div class="card-content">
                            <div class="card-body">
                             @include('focus.payroll.partials.generate-payroll')
                            </div>                    
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('focus.payroll.partials.approval')
@include('focus.payroll.partials.payroll-generate')
@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    config = {
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    }
    approval();
    $('#statusModal').on('shown.bs.modal', function() {
            $('.datepicker').datepicker({
                container: '#statusModal',
                ...config.date
            });
        });
    $('.send_mail').click(function () { 
        var id = @json($payroll->id);
        $.ajax({
            url: "{{ route('biller.payroll.send_mail')}}",
            method: "POST",
            data: {
                id: id,
            },
            success: function (response) {
                // Display the message in the response
                $('#responseMessage').html('<p>' + response.message + '</p>');
                // location.reload();
            },
            error: function (xhr) {
                // Handle error case (optional)
                $('#responseMessage').html('<p>Error occurred</p>');
            }
        });

        
    });
    function approval() {
        $('.send_mail').addClass('d-none');
        var status = @json($payroll->status);
        if(status == 'approved'){
            $('.send_mail').removeClass('d-none');
            $('.approve').addClass('d-none');
        }
    }
        
</script>
<script>
    $(function () {
        setTimeout(function () {
            draw_data()
        }, {{config('master.delay')}});
    });

    function draw_data() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var dataTable = $('#payrollTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                @lang('datatable.strings')
            },
            ajax: {
                url: '{{ route("biller.payroll.get_employee") }}',
                data: {payroll_id: @json(@$payroll->id)},
                type: 'post'
            },
            columns: [
                {data: 'employee_id', name: 'employee_id'},
                {data: 'employee_name', name: 'employee_name'},
                {data: 'basic_pay', name: 'basic_pay'},
                {data: 'absent_days', name: 'absent_days'},
                {data: 'house_allowance', name: 'house_allowance'},
                {data: 'transport_allowance', name: 'transport_allowance'},
                {data: 'other_allowance', name: 'other_allowance'},
                {data: 'gross_pay', name: 'gross_pay'},
                {data: 'nssf', name: 'nssf'},
                {data: 'tx_deductions', name: 'tx_deductions'},
                {data: 'paye', name: 'paye'},
                {data: 'taxable_gross', name: 'taxable_gross'},
                {data: 'total_other_allowances', name: 'total_other_allowances'},
                {data: 'total_benefits', name: 'total_benefits'},
                {data: 'loan', name: 'loan'},
                {data: 'advance', name: 'advance'},
                {data: 'total_other_deductions', name: 'total_other_deductions'},
                {data: 'netpay', name: 'netpay', searchable: false, sortable: false}
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print']
        });
        //$('#payrollTbl_wrapper').removeClass('form-inline');

    }
</script>
@endsection
