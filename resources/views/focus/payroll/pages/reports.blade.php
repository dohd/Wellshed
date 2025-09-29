@extends('core.layouts.app')

@section('title', 'Payroll Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">Payroll Reports</h4>

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
            <div class="card-header">
                <div class="row">
                    <div class="col-1 h2">{{ (new DateTime($payrollTallies[0]['payroll_month']))->format('M Y') }}</div>
                    <div class="col-4 h3">| {{ $payrollTallies[0]['working_days'] }} Working Days</div>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                           role="tab" aria-selected="true"><span class="">NSSF REPORTS </span>

                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2"
                           role="tab" aria-selected="false"><span>PAYE REPORTS</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3"
                           role="tab" aria-selected="false">
                            <span>SHIF REPORTS</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9"
                           role="tab" aria-selected="false">
                            <span>HOUSING LEVY REPORTS</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab8" data-toggle="tab" aria-controls="tab8" href="#tab8" role="tab"
                           aria-selected="false">
                            <span>PAYROLL REPORTS</span>
                        </a>
                    </li>
                    @if(\Illuminate\Support\Facades\Auth::user()->ins === 2)

                        <li class="nav-item">
                            <a class="nav-link" id="base-tab10" data-toggle="tab" aria-controls="tab9" href="#tab10" role="tab"
                               aria-selected="false">
                                <span>ERP SALES COMMISSIONS REPORT</span>
                            </a>
                        </li>
                    @endif

                </ul>
                <div class="tab-content px-1 pt-1">
                    <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                        @include('focus.payroll.reports.nssf-report')
                    </div>
                    <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                        @include('focus.payroll.reports.paye-report')
                    </div>
                    <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                        @include('focus.payroll.reports.nhif-report')
                    </div>
                    <div class="tab-pane" id="tab9" role="tabpanel" aria-labelledby="base-tab9">
                        @include('focus.payroll.reports.housing-levy-report')
                    </div>
                    <div class="tab-pane" id="tab8" role="tabpanel" aria-labelledby="base-tab8">
                        @include('focus.payroll.reports.payroll-report')
                    </div>
                    @if(\Illuminate\Support\Facades\Auth::user()->ins === 2)
                        <div class="tab-pane" id="tab10" role="tabpanel" aria-labelledby="base-tab10">
                            @include('focus.payroll.reports.erp-commissions-report')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        $(function () {
            setTimeout(function () {
                draw_data();
            }, {{ config('master.delay') }});
        });

        $('#status').change(function () {
            reloadAllTables();
        });

        function reloadAllTables() {
            ['#payrollTable', '#nssfTbl', '#erpCommsTbl', '#payeTbl', '#nhifTbl', '#housingLevyTbl'].forEach(id => {
                if ($.fn.DataTable.isDataTable(id)) {
                    $(id).DataTable().destroy();
                }
            });
            draw_data();
        }

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const payroll_id = @json(@$payroll);
            const status = $('#status').val();

            setupDataTable('#payrollTable', '{{ route("biller.payroll.get_reports", $payroll) }}', status, [
                {data: 'payroll_id'}, {data: 'employee_id'}, {data: 'name'}, {data: 'id_number'},
                {data: 'fixed_salary'}, {data: 'max_hourly_salary'}, {data: 'pay_per_hr'}, {data: 'man_hours'},
                {data: 'basic_hourly_salary'}, {data: 'absent_days'}, {data: 'absent_daily_deduction'},
                {data: 'absent_total_deduction'}, {data: 'basic_salary'}, {data: 'house_allowance'},
                {data: 'transport_allowance'}, {data: 'other_allowance'}, {data: 'total_allowance'},
                {data: 'basic_plus_allowance'}, {data: 'taxable_deductions'}, {data: 'deduction_narration'},
                {data: 'nssf'}, {data: 'taxable_gross'}, {data: 'nhif'}, {data: 'housing_levy'},
                {data: 'income_tax'}, {data: 'nhif_relief'}, {data: 'personal_relief'}, {data: 'paye'},
                {data: 'netpay'}, {data: 'loan'}, {data: 'advance'}, {data: 'benefits'},
                {data: 'other_deductions'}, {data: 'other_allowances'}, {data: 'net_after_bnd'},
                {data: 'primary_contact'},
            ]);

            setupDataTable('#nssfTbl', '{{ route("biller.payroll.getNssfReport", $payroll) }}', status, [
                {data: 'payroll_id'}, {data: 'surname'}, {data: 'other_names'}, {data: 'id_number'},
                {data: 'kra_pin'}, {data: 'nssf_number'}, {data: 'basic_plus_allowance'}
            ]);

            setupDataTable('#erpCommsTbl', '{{ route("biller.payroll.getErpCommissionsReport", $payroll) }}', status, [
                {data: 'payroll_id'}, {data: 'surname'}, {data: 'other_names'}, {data: 'id_number'},
                {data: 'erp_sales_count'}, {data: 'erp_sales_value'}, {data: 'erp_sales_rate'},
                {data: 'erp_sales_commission'}, {data: 'basic_plus_allowance'}
            ]);

            setupDataTable('#payeTbl', '{{ route("biller.payroll.getPayeReport", $payroll) }}', status, [
                {data: 'kra_pin'}, {data: 'name'}, {data: 'tax_obligation'}, {data: 'employee_type'},
                {data: 'basic_plus_allowance'}, {data: 'house_allowance'}, {data: 'transport_allowance'},
                {data: 'zero_col'}, {data: 'zero_col'}, {data: 'zero_col'}, {data: 'zero_col'},
                {data: 'other_allowance'}, {data: 'blank_col'}, {data: 'zero_col'}, {data: 'zero_col'},
                {data: 'blank_col'}, {data: 'zero_col'}, {data: 'benefit_not_given'}, {data: 'blank_col'},
                {data: 'blank_col'}, {data: 'blank_col'}, {data: 'blank_col'}, {data: 'blank_col'},
                {data: '30pc_of_cash_pay'}, {data: 'nssf'}, {data: 'permissible_limits'}, {data: 'zero_col'},
                {data: 'ahl_relief'}, {data: 'blank_col'}, {data: 'blank_col'}, {data: 'blank_col'},
                {data: 'personal_relief'}, {data: 'nhif_relief'}, {data: 'blank_col'}, {data: 'paye'},
            ]);

            setupDataTable('#nhifTbl', '{{ route("biller.payroll.getNhifReport", $payroll) }}', status, [
                {data: 'payroll_id'}, {data: 'surname'}, {data: 'other_names'}, {data: 'id_number'},
                {data: 'nhif_number'}, {data: 'nhif'}
            ]);

            setupDataTable('#housingLevyTbl', '{{ route("biller.payroll.getHousingLevyReport", $payroll) }}', status, [
                {data: 'id_number'}, {data: 'name'}, {data: 'kra_pin'}, {data: 'basic_plus_allowance'}
            ]);
        }

        function setupDataTable(selector, url, status, columns) {
            const payrollMdl = @json($payrollMdl);
            $(selector).dataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                language: @json(__('datatable.strings')),
                ajax: {
                    url: url,
                    type: 'post',
                    data: {
                        payroll_id: @json(@$payroll),
                        status: status
                    }
                },
                columns: columns,
                order: [[0, 'asc']],
                searchDelay: 500,
                dom: payrollMdl.status == 'approved'? 'Blfrtip' : 'lfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [10, 25, 50, 100, 200, -1],
                    [10, 25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });
        }
    </script>
@endsection
