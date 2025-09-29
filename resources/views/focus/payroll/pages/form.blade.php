<div class="card-content">

    <div class="card-body">
        <div class="form-group">

            <h4 class="float-right">Expired Contract: <span class="text-danger">{{ $expired_contracts }}</span></h4>
        </div>
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                    role="tab" aria-selected="true"><span class="">Basic Salary </span>
                    <i class="text-danger fa fa-times float-right cancel" aria-hidden="true"></i>
                    <i class="text-success fa fa-check float-right tick" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab"
                    aria-selected="false"><span>Taxable Allowances</span>
                    <i class="text-danger fa fa-times float-right cancel_allowance" aria-hidden="true"></i>
                    <i class="text-success fa fa-check float-right d-none tick_allowance" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3" role="tab"
                    aria-selected="false">
                    <span>Taxable Deductions</span>
                    <i class="text-danger fa fa-times float-right cancel_deduction" aria-hidden="true"></i>
                    <i class="text-success fa fa-check float-right d-none tick_deduction" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4" role="tab"
                    aria-selected="false">
                    <span>PAYE</span>
                    <i class="text-danger fa fa-times float-right cancel_paye" aria-hidden="true"></i>
                    <i class="text-success fa fa-check float-right d-none tick_paye" aria-hidden="true"></i>
                </a>
            </li>
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link" id="base-tab7" data-toggle="tab" aria-controls="tab7" href="#tab7" role="tab"--}}
{{--                    aria-selected="false">--}}
{{--                    <span>SHIF & Housing Levy</span>--}}
{{--                    <i class="text-danger fa fa-times float-right cancel_nhif" aria-hidden="true"></i>--}}
{{--                    <i class="text-success fa fa-check float-right d-none tick_nhif" aria-hidden="true"></i>--}}
{{--                </a>--}}
{{--            </li>--}}
            <li class="nav-item">
                <a class="nav-link" id="base-tab5" data-toggle="tab" aria-controls="tab5" href="#tab5" role="tab"
                    aria-selected="false">
                    <span>Other Deductions and Benefits</span>
                    <i class="text-danger fa fa-times float-right cancel_other_deductions" aria-hidden="true"></i>
                    <i class="text-success fa fa-check float-right d-none tick_other_deductions" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab6" data-toggle="tab" aria-controls="tab6" href="#tab6" role="tab"
                    aria-selected="false">
                    <span>Summary</span>
                    <i class="text-danger fa fa-times float-right cancel_total_netpay" aria-hidden="true"></i>
                    <i class="text-success fa fa-check float-right d-none tick_total_netpay" aria-hidden="true"></i>
                </a>
            </li>
        </ul>
        <div class="tab-content px-1 pt-1">
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                <div class="card-content">
                    @include('focus.payroll.pages.tabs.basic-salary')
                </div>
            </div>
            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                @include('focus.payroll.pages.tabs.tx-month-allowances')
            </div>
            <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                <div class="card-content">

                    @include('focus.payroll.pages.tabs.tx-month-deductions')
                </div>
            </div>
            <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                <div class="card-content">
                    @include('focus.payroll.pages.tabs.paye')
                </div>
            </div>
{{--            <div class="tab-pane" id="tab7" role="tabpanel" aria-labelledby="base-tab7">--}}
{{--                <div class="card-content">--}}
{{--                    @include('focus.payroll.pages.tabs.nhif')--}}
{{--                </div>--}}
{{--            </div>--}}
            <div class="tab-pane" id="tab5" role="tabpanel" aria-labelledby="base-tab5">
                <div class="card-content">
                    @include('focus.payroll.pages.tabs.otherbenefitsanddeductions')
                </div>
            </div>
            <div class="tab-pane" id="tab6" role="tabpanel" aria-labelledby="base-tab6">
                <div class="card-content">
                    @include('focus.payroll.pages.tabs.summary')
                </div>
            </div>
        </div>
    </div>
</div>
@include('focus.payroll.modal.basic-pay')
@include('focus.payroll.modal.allowance')
@include('focus.payroll.modal.deduction')
@include('focus.payroll.modal.other')
@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}
    <style>
        .hide {
            display: none;
            }

            #tooltip {
            /* position: absolute; */
            /* top: 0;
            left: 0; */
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            z-index: 999;
            }




    </style>
    <!-- jQuery -->
    @include('focus.payroll.partials.hover-modal')
    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };

        const Index = {
            payroll_items: @json($payrollItems),
            salary_total: @json($payroll->salary_total),
            allowance_total: @json($payroll->allowance_total),
            totalGrossPay: @json($payroll->gross_post_allowances),
            other_taxable_deductions: @json($payroll->other_taxable_deductions),
            paye_total: @json($payroll->paye_total),
            total_nhif: @json($payroll->total_nhif),
            other_deductions_total: @json($payroll->other_deductions_total),
            other_benefits_total: @json($payroll->other_benefits_total),
            total_salary_after_bnd: @json($payroll->total_salary_after_bnd),
            init() {
                // console.table(this.payroll_items);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#employeeTbl').on('keyup', '.absent, .present, .rate, .rate-month, .total, .additional_hours, .erp-sales-value', this.employeeChange);
            $('#employeeTbl').on('change', '.absent_deduction', this.absentRateChange);
            $('.ab-days').on('keyup', this.absentChange);
            $('#allowanceModal').on('keyup', '.ha, .oa, .ta', this.houseTransportChange);
            $('#deductionTbl').on('keyup', '.deduction', this.deductionChange);
            function employeeChange(row) {
                const absent = accounting.unformat(row.find('.absent').val());
                const rate = accounting.unformat(row.find('.rate').val());
                const absent_rate = accounting.unformat(row.find('.absent_deduction').val());
                const fixed_salary = accounting.unformat(row.find('.fixed_salary').val());
                const hourly_salary = accounting.unformat(row.find('.basic_hourly_salary').val());
                const additionalHours = accounting.unformat(row.find('.additional_hours').val());
                const hourlyRate = accounting.unformat(row.find('.pay_per_hr').val());
                const maxHourlySalary = accounting.unformat(row.find('.max_hourly_salary').val());
                let additionalHourlySalary = 0;

                const erpSalesRate = accounting.unformat(row.find('.erp-sales-rate').val());

                if(maxHourlySalary > 0 && erpSalesRate <= 0){
                    additionalHourlySalary = accounting.unformat((hourlyRate * additionalHours).toFixed(2));
                    console.log("Additional Hrly Salary: " + additionalHourlySalary);
                }

                row.find('.additional_hourly_salary').val(additionalHourlySalary.toFixed(2));



                const userIns = @json(\Illuminate\Support\Facades\Auth::user()->ins);

                console.table({userIns: userIns});

                let erpSalesCommission = 0;

                if (userIns === 2) {

                    const erpSalesValue = accounting.unformat(row.find('.erp-sales-value').val());
                    const erpSalesRate = accounting.unformat(row.find('.erp-sales-rate').val());

                    row.find('.erp-sales-commission').val((erpSalesValue * erpSalesRate).toFixed(2));
                    console.log("ERP COMMS: " + (erpSalesValue * erpSalesRate).toFixed(2))

                    console.table(userIns);

                    erpSalesCommission = accounting.unformat(row.find('.erp-sales-commission').val());
                }




                const working_days = $('.working_days').val();
                const month_days = $('.month_days').val();

                const absent_amount_deduct = fixed_salary / month_days * absent;
                const total_basic_salary = fixed_salary + hourly_salary + additionalHourlySalary + erpSalesCommission - absent_amount_deduct;

                const days_to_be_paid = month_days - absent;
                const rate_per_day = fixed_salary / month_days;
                const month_rate = days_to_be_paid * rate_per_day;

                row.find('.rate').val(rate_per_day.toFixed(2));
                row.find('.absent_deduction').val(absent_amount_deduct.toFixed(2));
                row.find('.rate-month').val(month_rate);
                row.find('.total').val(total_basic_salary.toFixed(2));

                // Optional: add a function to recalculate the total if needed
                Index.calTotal();
            }

                // Event listener for the button click to refresh the whole table
                $('#refresh').on('click', function() {
                    // Loop through each row of the table
                    $('#employeeTbl tr').each(function() {
                        const row = $(this);
                        employeeChange(row);  // Apply the function to each row
                    });
                });
                $('#refresh_allowance').on('click', function() {
                    // Loop through each row of the table
                    $('#allowanceTbl tr').each(function() {
                        const row = $(this);
                        allowanceTbChange(row);  // Apply the function to each row
                    });
                });
                $('#refresh_other_deductions').on('click', function() {
                    // Loop through each row of the table
                    $('#otherBenefitsTbl tr').each(function() {
                        const row = $(this);
                        otherDeductionsTbChange(row);  // Apply the function to each row
                    });
                });
                function allowanceTbChange(row){

                    const absent_day = accounting.unformat(row.find('.absent_day').val());
                    const house = 0;
                    const basic = accounting.unformat(row.find('.basic').val());
                    const house_allowance = 0;
                    const transport = 0;
                    const transport_allowance = 0;
                    const other_allowance = 0;
                    const month_days = $('.month_days').val();
                    const working_days = $('.working_days').val();

                    const absent_allowance = house/month_days * absent_day;
                    const cal_house_allowance = house - absent_allowance;
                    const ab_allowance = transport/month_days * absent_day;
                    const cal_transport_allowance = transport - ab_allowance;


                        const cal_total_allowance = cal_house_allowance + cal_transport_allowance + other_allowance;
                        const total_basic_allowance = cal_total_allowance + basic;
                        row.find('.house').val(0)
                        row.find('.transport').val(0)
                        row.find('.other_allowance').val(0)
                        row.find('.house_allowance').val(accounting.unformat(cal_house_allowance).toFixed(2));
                        row.find('.transport_allowance').val(accounting.unformat(cal_transport_allowance).toFixed(2));
                        row.find('.total_allowance').val(accounting.unformat(cal_total_allowance).toFixed(2));
                        row.find('.total_basic_allowance').val(accounting.unformat(total_basic_allowance).toFixed(2));
                        row.find('.basic_plus_allowance').val(accounting.unformat(total_basic_allowance).toFixed(2));

                        Index.calallowanceTotal();
                }

                function otherDeductionsTbChange(row){
                    const otherallowances = 0;
                    const benefits = 0;
                    const loan = 0;
                    const advance = accounting.unformat(row.find('.advance').val());
                    const others = 0;
                    const netpay = accounting.unformat(row.find('#netpay').val());

                    const pay_after_bnd = netpay + benefits + otherallowances - loan - advance - others;
                    row.find('.net_after_bnd').val(pay_after_bnd);

                    row.find('.other-allow').val(accounting.unformat(otherallowances));
                    row.find('.benefits').val(accounting.unformat(benefits));
                    row.find('.loan').val(accounting.unformat(loan));
                    row.find('.advance').val(accounting.unformat(advance));
                    row.find('.other-deductions').val(accounting.unformat(others));

                    Index.calTotalBenefitsAndDeductions();
                }

                $('#allowanceTbl').on('keyup',
                    '.house, .house_allowance, .transport, .transport_allowance, .other, .other_allowance', this
                    .allowanceChange);
                $('#otherBenefitsTbl').on('keyup',
                    '.loan, .advance, .benefits, .other-deductions, .other-allow', this
                    .otherBNDChange);
                $('#employeeTbl').on('click', '.edit', this.showModal);
                $('#allowanceTbl').on('click', '.edit-allowance', this.showAllowanceModal);
                $('#deductionTbl').on('click', '.edit-deduction', this.showDeductionModal);
                $('#otherBenefitsTbl').on('click', '.edit-other', this.showOtherModal);
                if (this.payroll_items && this.payroll_items.length) {
                    $('.cancel').addClass('d-none');
                    $('.submit-salary').addClass('d-none');
                    $('.tick').removeClass('d-none');
                    $('.cancel_allowance').removeClass('d-none');
                    $('.tick_allowance').addClass('d-none');
                    $('.cancel_other_deductions').removeClass('d-none');
                    $('.tick_other_deductions').addClass('d-none');
                    $('.cancel_salary_total').removeClass('d-none');
                    $('.tick_salary_total').addClass('d-none');
                    $('#employeeTbl tbody').html('');
                    this.payroll_items.forEach((v, i) => $('#employeeTbl tbody').append(Index.employeeRow(v, i)));
                    $('#salary_total').val(accounting.formatNumber(this.salary_total));
                    if (this.allowance_total && this.allowance_total.length) {
                        $('#allowanceTbl tbody').html('');
                        this.payroll_items.forEach((v, i) => $('#allowanceTbl tbody').append(Index.allowanceRow(v, i)));
                        $('#allowance_total').val(accounting.formatNumber(this.allowance_total));
                        $('#totalGrossPay').val(accounting.formatNumber(this.totalGrossPay));
                        console.log("TOTAL ALLOWANCE: " + this.allowance_total);
                        $('.tick_allowance').removeClass('d-none');
                        $('.cancel_allowance').addClass('d-none');
                        $('.submit-allowances').addClass('d-none');
                        if (this.other_taxable_deductions && this.other_taxable_deductions.length) {
                            $('.cancel_deduction').addClass('d-none');
                            $('.tick_deduction').removeClass('d-none');
                            $('.submit-deduction').addClass('d-none');
                            if (this.paye_total && this.paye_total.length) {
                                $('.cancel_paye').addClass('d-none');
                                $('.tick_paye').removeClass('d-none');
                                $('.submit-paye').addClass('d-none');

                                if (this.total_nhif && this.total_nhif.length) {
                                    $('.cancel_nhif').addClass('d-none');
                                    $('.tick_nhif').removeClass('d-none');
                                    $('.submit-nhif').addClass('d-none');
                                    if (this.other_benefits_total && this.other_benefits_total.length) {
                                    $('#otherBenefitsTbl tbody').html('');
                                    this.payroll_items.forEach((v, i) => $('#otherBenefitsTbl tbody:first').append(Index.deductionRow(v, i)));
                                    $('#other_benefits_total').val(accounting.formatNumber(this.other_benefits_total));
                                    $('#other_deductions_total').val(accounting.formatNumber(this
                                        .other_deductions_total));
                                    $('.cancel_other_deductions').addClass('d-none');
                                    $('.tick_other_deductions').removeClass('d-none');
                                    $('.submit-otherbenefits').addClass('d-none');
                                    if(this.total_salary_after_bnd && this.total_salary_after_bnd.length){
                                        $('.cancel_total_netpay').addClass('d-none');
                                        $('.tick_total_netpay').removeClass('d-none');
                                        $('.submit-netpay').addClass('d-none');
                                    }
                                }
                                }

                            }

                        }
                    }
                } else {
                    $('.tick').addClass('d-none');
                    $('.cancel').removeClass('d-none');
                }


                //Index.calTotal();
                Index.calTotalNetPay();
                Index.calTotalBenefitsAndDeductions();

            },

        allowanceChange() {
            const el = $(this);
            const row = el.parents('tr:first');

            const absent_day = accounting.unformat(row.find('.absent_day').val());
            const house = accounting.unformat(row.find('.house').val());
            const basic = accounting.unformat(row.find('.basic').val());
            const house_allowance = accounting.unformat(row.find('.house_allowance').val());
            const transport = accounting.unformat(row.find('.transport').val());
            const transport_allowance = accounting.unformat(row.find('.transport_allowance').val());
            const other_allowance = accounting.unformat(row.find('.other_allowance').val());
            const month_days = $('.month_days').val();
            const working_days = $('.working_days').val();

            const absent_allowance = house/month_days * absent_day;
            const cal_house_allowance = house - absent_allowance;
            const ab_allowance = transport/month_days * absent_day;
            const cal_transport_allowance = transport - ab_allowance;


                const cal_total_allowance = cal_house_allowance + cal_transport_allowance + other_allowance;
                const total_basic_allowance = cal_total_allowance + basic;
                row.find('.house_allowance').val(accounting.unformat(cal_house_allowance).toFixed(2));
                row.find('.transport_allowance').val(accounting.unformat(cal_transport_allowance).toFixed(2));
                row.find('.total_allowance').val(accounting.unformat(cal_total_allowance).toFixed(2));
                row.find('.total_basic_allowance').val(accounting.unformat(total_basic_allowance).toFixed(2));
                row.find('.basic_plus_allowance').val(accounting.unformat(total_basic_allowance).toFixed(2));

                Index.calallowanceTotal();
            },

            employeeChange() {
                const el = $(this);
                const row = el.parents('tr:first');


                const absent = accounting.unformat(row.find('.absent').val());

                const rate = accounting.unformat(row.find('.rate').val());

                const absent_rate = accounting.unformat(row.find('.absent_deduction').val());

                const fixed_salary = accounting.unformat(row.find('.fixed_salary').val());

                const hourly_salary = accounting.unformat(row.find('.basic_hourly_salary').val());

                const additionalHours = accounting.unformat(row.find('.additional_hours').val());

                const hourlyRate = accounting.unformat(row.find('.pay_per_hr').val());
                const maxHourlySalary = accounting.unformat(row.find('.max_hourly_salary').val());
                let additionalHourlySalary = 0;

                const erpSalesRate = accounting.unformat(row.find('.erp-sales-rate').val());
                console.table(erpSalesRate);

                if(maxHourlySalary > 0 && erpSalesRate <= 0){

                    additionalHourlySalary = accounting.unformat((hourlyRate * additionalHours).toFixed(2));
                    console.log("Additional Hrly Salary: " + additionalHourlySalary);
                }

                // hourly_salary += additionalHourlySalary;
                row.find('.additional_hourly_salary').val(accounting.unformat(additionalHourlySalary).toFixed(2));



                const userIns = @json(\Illuminate\Support\Facades\Auth::user()->ins);

                console.table({userIns: userIns});

                let erpSalesCommission = 0;

                if (userIns === 2) {

                    const erpSalesValue = accounting.unformat(row.find('.erp-sales-value').val());
                    const erpSalesRate = accounting.unformat(row.find('.erp-sales-rate').val());

                    row.find('.erp-sales-commission').val((erpSalesValue * erpSalesRate).toFixed(2));
                    console.log("ERP COMMS: " + (erpSalesValue * erpSalesRate).toFixed(2))

                    console.table(userIns);

                    erpSalesCommission = accounting.unformat(row.find('.erp-sales-commission').val());
                }



                const working_days = $('.working_days').val();

                const month_days = $('.month_days').val();

                const absent_amount_deduct = fixed_salary / month_days * absent;

                const total_basic_salary = fixed_salary + hourly_salary + additionalHourlySalary + erpSalesCommission - absent_amount_deduct;
                // console.table({
                //     'Fixed Salary' :  fixed_salary,
                //     "Hourly Salary" : hourly_salary,
                //     "Total Salary" : total_basic_salary
                // });

                const days_to_be_paid = month_days - absent;

                const rate_per_day = fixed_salary / month_days;

                const month_rate = days_to_be_paid * rate_per_day;


                row.find('.rate').val(accounting.unformat(rate_per_day).toFixed(2));
                row.find('.absent_deduction').val(accounting.unformat(absent_amount_deduct).toFixed(2));
                row.find('.rate-month').val(accounting.unformat(month_rate));
                row.find('.total').val(accounting.unformat(total_basic_salary).toFixed(2));
                Index.calTotal();

            },
            absentRateChange(){
                const el = $(this);
                const row = el.parents('tr:first');
               // row.find('.absent_deduction').val('');
                const absent = accounting.unformat(row.find('.absent').val());
                const rate = accounting.unformat(row.find('.rate').val());
                const absent_rate = accounting.unformat(row.find('.absent_deduction').val());
                const basic_pay = accounting.unformat(row.find('.basic_salary').val());
                const working_days = $('.working_days').val();
                const month_days = $('.month_days').val();
                const days_to_be_paid = month_days - absent;
                const absent_amount = absent_rate;
                const month_rate = basic_pay - absent_rate;

                row.find('.total').val(accounting.unformat(month_rate));
                //row.find('.absent_deduction').val(row.find('.absent_deduction').val());
                Index.calTotal();
            },
            deductionChange() {
                const el = $(this);
                const row = el.parents('tr:first');

                const basic_plus_allowance = accounting.unformat(row.find('#basic_plus_allowance').val());

                const deduction = accounting.unformat(row.find('.deduction').val());
                const nssf =  accounting.unformat(row.find('.nssf').val());
                const nhif = accounting.unformat(row.find('.nhif').val());
                const housing_levy = accounting.unformat(row.find('.housing_levy').val());



                const netPay = basic_plus_allowance - nssf - nhif - housing_levy - deduction;

                console.table({
                    ded: deduction,
                    bpa: basic_plus_allowance,
                    np: netPay,
                });

                row.find('.net_post_tx_deduction').val(accounting.formatNumber(netPay));

                Index.calTxDeductions();

            },
            calTotal() {
                let grandTotal = 0;

                let erpSalesCommissionTotal = 0

                $('#employeeTbl tbody tr').each(function() {
                    if (!$(this).find('.absent').val()) return;

                    const basic_pay = accounting.unformat($(this).find('.fixed_salary').val());
                    const hourly_pay = accounting.unformat($(this).find('.basic_hourly_salary').val());
                    const absent = accounting.unformat($(this).find('.absent_deduction').val());
                    const erp_sales_commission = accounting.unformat($(this).find('.erp-sales-commission').val());

                    erpSalesCommissionTotal += erp_sales_commission;

                    console.table({erpSalesCommissionTotal : erpSalesCommissionTotal});

                    const total_basic_salary = basic_pay + hourly_pay +  erp_sales_commission - absent;

                    // console.table({
                    //     'Basic pay' : basic_pay,
                    //     'Hourly pay' : hourly_pay,
                    //     'Absent' : absent,
                    //     'Salary' : total_basic_salary,
                    // });

                    grandTotal += total_basic_salary;
                });
                //
                $('#erp_commission_total').val(accounting.unformat(erpSalesCommissionTotal).toFixed(2));
                $('#salary_total').val(accounting.unformat(grandTotal).toFixed(2));
            },
            calTxDeductions() {

                let totalOtherDeductions = 0;
                let totalTaxableDeductions = 0;

                $('#deductionTbl tbody tr').each(function() {
                    if (!$(this).find('.deduction').val()) return;

                    const deduction = accounting.unformat($(this).find('.deduction').val());
                    totalOtherDeductions += deduction;

                    const nssf = accounting.unformat($(this).find('.nssf').val());
                    const nhif = accounting.unformat($(this).find('.nhif').val());
                    const housing_levy = accounting.unformat($(this).find('.housing_levy').val());

                    totalTaxableDeductions += nssf + nhif + housing_levy + deduction;
                    // console.log(totalOtherDeductions);
                });
                //
                $('#other_taxable_deductions').val(accounting.formatNumber(totalOtherDeductions));
                $('#other_taxable_deductions_hidden').val(accounting.unformat(totalOtherDeductions));

                $('#total_taxable_deductions').val(accounting.formatNumber(totalTaxableDeductions));
                $('#total_taxable_deductions_hidden').val(accounting.unformat(totalTaxableDeductions));


            },
            calTotalNetPay() {
                let grandTotal = 0;
                $('#summaryTable tbody tr').each(function() {

                    const net = accounting.unformat($(this).find('.netpay').text());
                    grandTotal += net;
                });

                $('#total_net').val(accounting.format(grandTotal));
                $('#total_netpay_summary').val(accounting.unformat(grandTotal));
            },
            otherBNDChange() {
                const el = $(this);
                const row = el.parents('tr:first');

                const otherallowances = accounting.unformat(row.find('.other-allow').val());
                const benefits = accounting.unformat(row.find('.benefits').val());
                const loan = accounting.unformat(row.find('.loan').val());
                const advance = accounting.unformat(row.find('.advance').val());
                const others = accounting.unformat(row.find('.other-deductions').val());
                const netpay = accounting.unformat(row.find('#netpay').val());

                const pay_after_bnd = netpay + benefits + otherallowances - loan - advance - others;
                row.find('.net_after_bnd').val(pay_after_bnd);

                row.find('.other-allow').val(accounting.unformat(otherallowances));
                row.find('.benefits').val(accounting.unformat(benefits));
                row.find('.loan').val(accounting.unformat(loan));
                row.find('.advance').val(accounting.unformat(advance));
                row.find('.other-deductions').val(accounting.unformat(others));

                Index.calTotalBenefitsAndDeductions();
            },

            calTotalBenefitsAndDeductions() {
                let benefitsTotal = 0;
                let deductionsTotal = 0;
                let loansTotal = 0;
                let otherAllowancesTotal = 0;
                $('#otherBenefitsTbl tbody tr').each(function() {

                    const benefits = accounting.unformat($(this).find('.benefits').val());
                    const otherallowances = accounting.unformat($(this).find('.other-allow').val());
                    const loans = accounting.unformat($(this).find('.loan').val());
                    const advance = accounting.unformat($(this).find('.advance').val());
                    const others = accounting.unformat($(this).find('.other-deductions').val());
                    const net = benefits;
                    const dedu = loans + advance + others;
                    benefitsTotal += net;
                    deductionsTotal += dedu;
                    loansTotal += loans;
                    console.log("LOANS TOTAL : " + loansTotal);
                    otherAllowancesTotal += otherallowances;
                });

                $('#other_benefits_total').val(accounting.unformat(benefitsTotal));
                $('#other_deductions_total').val(accounting.unformat(deductionsTotal));
                $('#other_allowances_total').val(accounting.unformat(otherAllowancesTotal));
            },
            calallowanceTotal() {
                let grandTotal = 0;
                let totalGross = 0;
                $('#allowanceTbl tbody tr').each(function() {
                    if (!$(this).find('.absent_day').val()) return;
                    const absent_day = accounting.unformat($(this).find('.absent_day').val());
                    const house = accounting.unformat($(this).find('.house').val());
                    const house_allowance = accounting.unformat($(this).find('.house_allowance').val());
                    const transport = accounting.unformat($(this).find('.transport').val());
                    const transport_allowance = accounting.unformat($(this).find('.transport_allowance').val());
                    const other_allowance = accounting.unformat($(this).find('.other_allowance').val());
                    const month_days = $('.month_days').val();
                    const working_days = $('.working_days').val();
                    const basicPlusAllowance = accounting.unformat($(this).find('.basic_plus_allowance').val());
                    totalGross += basicPlusAllowance;

                    const absent_allowance = house / month_days * absent_day;
                    const cal_house_allowance = house - absent_allowance;
                    const ab_allowance = transport / month_days * absent_day;
                    const cal_transport_allowance = transport - ab_allowance;

                    const cal_total_allowance = cal_house_allowance + cal_transport_allowance + other_allowance;
                    grandTotal += cal_total_allowance;
                });

                $('#allowance_total').val(accounting.unformat(grandTotal).toFixed(2));
                $('#totalGrossPay').val(accounting.unformat(totalGross).toFixed(2));
            },
            employeeRow(v, i) {

                const userIns = @json(\Illuminate\Support\Facades\Auth::user()->ins);

                if(userIns === 2) return `
                    <tr>
                        <td>${v.employee_id}</td>
                        <td>${v.name}</td>
                        <td class="editable-cell">${accounting.formatNumber(v.fixed_salary)}</td>
                        <td>${accounting.formatNumber((v.max_hourly_salary))}</td>
                        <td>${accounting.formatNumber((v.pay_per_hr))}</td>
                        <td>${accounting.formatNumber((v.man_hours))}</td>
                        <td>${accounting.formatNumber((v.additional_hours))}</td>
                        <td>${accounting.formatNumber((v.basic_hourly_salary))}</td>
                        <td>${accounting.formatNumber((v.additional_hourly_salary))}</td>


                        <td>${accounting.formatNumber((v.erp_sales_count))}</td>
                        <td>${accounting.formatNumber((v.erp_sales_value))}</td>
                        <td>${accounting.formatNumber((v.erp_sales_rate))}</td>
                        <td>${accounting.formatNumber((v.erp_sales_commission))}</td>


                        <td class="editable-cell">${v.absent_days}</td>
                        <td>${accounting.formatNumber(v.absent_daily_deduction)}</td>
                        <td>${accounting.formatNumber(v.absent_total_deduction)}</td>
                        <td>${accounting.formatNumber(v.basic_salary)}</td>
<!--                        <td>-->
<!--                            <a href="#" class="btn btn-danger btn-sm my-1 edit" data-toggle="modal" data-target="#basicModal">-->
<!--                                <i class="fa fa-pencil" aria-hidden="true"></i> Edit-->
<!--                            </a>-->
<!--                        </td>-->
                        <input type="hidden" name="id[]" value="${v.id}" class="form-control pid"  id="payroll_item-${i}">
                        <input type="hidden" name="absent_days[]" value="${v.absent_days}" class="form-control absent"  id="absent_days-${i}">
                        <input type="hidden" name="basic_salary[]" value="${v.basic_salary}" class="form-control basic_salary"  id="basic_salary-${i}">
                        <input type="hidden" name="absent_rate[]" value="${v.absent_deduction}" class="form-control absent_rate"  id="absent_rate-${i}">
                        <input type="hidden" name="rate_per_day[]" value="${v.rate_per_day} class="form-control rate"  id="rate-days-${i}">
                        <input type="hidden" name="rate_per_month[]" value="${v.basic_pay} class="form-control rate-month"  id="rate-month-${i}">
                    </tr>
                `;

                else return `
                    <tr>
                        <td>${v.employee_id}</td>
                        <td>${v.name}</td>
                        <td class="editable-cell">${accounting.formatNumber(v.fixed_salary)}</td>
                        <td>${accounting.formatNumber((v.max_hourly_salary))}</td>
                        <td>${accounting.formatNumber((v.pay_per_hr))}</td>
                        <td>${accounting.formatNumber((v.man_hours))}</td>
                        <td>${accounting.formatNumber((v.additional_hours))}</td>
                        <td>${accounting.formatNumber((v.basic_hourly_salary))}</td>
                        <td>${accounting.formatNumber((v.additional_hourly_salary))}</td>                        <td class="editable-cell">${v.absent_days}</td>
                        <td>${accounting.formatNumber(v.absent_daily_deduction)}</td>
                        <td>${accounting.formatNumber(v.absent_total_deduction)}</td>
                        <td>${accounting.formatNumber(v.basic_salary)}</td>
<!--                        <td>-->
<!--                            <a href="#" class="btn btn-danger btn-sm my-1 edit" data-toggle="modal" data-target="#basicModal">-->
<!--                                <i class="fa fa-pencil" aria-hidden="true"></i> Edit-->
<!--                            </a>-->
<!--                        </td>-->
                        <input type="hidden" name="id[]" value="${v.id}" class="form-control pid"  id="payroll_item-${i}">
                        <input type="hidden" name="absent_days[]" value="${v.absent_days}" class="form-control absent"  id="absent_days-${i}">
                        <input type="hidden" name="basic_salary[]" value="${v.basic_salary}" class="form-control basic_salary"  id="basic_salary-${i}">
                        <input type="hidden" name="absent_rate[]" value="${v.absent_deduction}" class="form-control absent_rate"  id="absent_rate-${i}">
                        <input type="hidden" name="rate_per_day[]" value="${v.rate_per_day} class="form-control rate"  id="rate-days-${i}">
                        <input type="hidden" name="rate_per_month[]" value="${v.basic_pay} class="form-control rate-month"  id="rate-month-${i}">
                    </tr>
                `;
            },
            showDeductionModal(){
                const el = $(this);
                const row = el.parents('tr:first');
                const id = accounting.unformat(row.find('.id').val());
                const deduction = accounting.unformat(row.find('.deduction').val());
                $('.deduction-id').val(id);
                $('.tx-deduction').val(deduction);
            },
            showOtherModal(){
                const el = $(this);
                const row = el.parents('tr:first');
                const id = accounting.unformat(row.find('.other-id').val());
                const other_allow = accounting.unformat(row.find('.other-allow').val());
                const benefits = accounting.unformat(row.find('.benefits').val());
                const loan = accounting.unformat(row.find('.loan').val());
                const advance = accounting.unformat(row.find('.advance').val());
                const other_deduction = accounting.unformat(row.find('.other-deduction').val());
                $('.o-id').val(id);
                $('.o-allow').val(other_allow);
                $('.benefit').val(benefits);
                $('.loans').val(loan);
                $('.advances').val(advance);
                $('.o-deductions').val(other_deduction);
            },

            showModal(){
                const el = $(this);
                const row = el.parents('tr:first');
               // row.find('.absent_deduction').val('');
                const absent = accounting.unformat(row.find('.absent').val());
                const absent_rate = accounting.unformat(row.find('.absent_deduction').val());
                const basic_salary = accounting.unformat(row.find('.basic_salary').val());
                const id = row.find('.pid').val();
                const month_days = $('.month_days').val();
                const working_days = $('.working_days').val();
                $('#ab-days').val(absent);
                $('#ab-rate').val(absent_rate);
                $('.salary').val(basic_salary);
                $('#id').val(id);
                $('#month').val(month_days);
                console.log(basic_salary);
            },
            absentChange(){
                const el = $(this);
                const row = el.parents('tr:first');
               // row.find('.absent_deduction').val('');
                const ab_days = $('.ab-days').val();
                const absent = accounting.unformat(row.find('.absent').val());
                const absent_rate = accounting.unformat(row.find('.absent_deduction').val());

                const basic_salary = $('.salary').val();
                const month_days = $('.month_days').val();
                const working_days = $('.working_days').val();
                const new_absent_rate = (basic_salary / month_days) * ab_days;
                // payable
                const payable = basic_salary - new_absent_rate;


                $('.ab-rate').val(new_absent_rate).change();
                $('#basic_pay').val(payable);
            },
            showAllowanceModal(){
                const el = $(this);
                const row = el.parents('tr:first');
               // row.find('.absent_deduction').val('');
                const house_allowance = accounting.unformat(row.find('.house_allowance').val());
                const absent = accounting.unformat(row.find('.absent').val());
                const transport_allowance = accounting.unformat(row.find('.transport_allowance').val());
                const other_allowance = accounting.unformat(row.find('.other_allowance').val());
                const id = row.find('.payid').val();
                const month_days = $('.month_days').val();
                const working_days = $('.working_days').val();
                $('.ha').val(house_allowance);
                $('.ta').val(transport_allowance);
                $('.oa').val(other_allowance);
                $('.pay_id').val(id);
                $('.month_day').val(month_days);
                $('.absent_day').val(absent)
                console.log(id);
            },
            houseTransportChange() {
                const el = $(this);
                const row = el.parents('tr:first');
               // row.find('.absent_deduction').val('');
                const house = $('.ha').val();
                const transport = $('.ta').val();
                const other = $('.oa').val();
                const absent_days = $('.absent_day').val();

                const month_days = $('.month_days').val();
                const working_days = $('.working_days').val();
                const house_allowance = (house / month_days) * absent_days;
                const ha = house - house_allowance;
                const transport_allowance = (transport / month_days) * absent_days;
                const ta = transport - transport_allowance;
                const other_allowance = (other / month_days) * absent_days;
                const oa = other - other_allowance;
                // payable
                //const payable = basic_salary - new_absent_rate;


                $('.house').val(ha).change();
                $('.transport').val(ta).change();
                $('.other').val(oa).change();
                $('.month').val(month_days);
                console.log(month_days);
                //$('#basic_pay').val(payable);
            },
            allowanceRow(v, i) {
                return `
                    <tr>
                        <td>${i+1}</td>
                        <td>${v.name}</td>
                        <td>${ accounting.formatNumber((parseFloat(v.fixed_salary) + parseFloat(v.basic_hourly_salary)).toFixed(2)) }</td>
                        <td>${v.absent_days}</td>
                        <td><input type="text" name="house_allowance[]" value="${accounting.formatNumber(v.house_allowance)}" class="form-control house_allowance"  id="house_allowance-${i}" readonly></td>
                        <td><input type="text" name="transport_allowance[]" value="${accounting.formatNumber(v.transport_allowance)}" class="form-control transport_allowance"  id="transport_allowance-${i}" readonly></td>
                        <td><input type="text" name="other_allowance[]" value="${accounting.formatNumber(v.other_allowance)}" class="form-control other_allowance"  id="other_allowance-${i}" readonly></td>
                        <td><input type="text" name="total_allowance[]" value="${accounting.formatNumber(v.total_allowance)}" class="form-control total_allowance"  id="total_allowance-${i}" readonly></td>
                        <td><input type="text" value="${ accounting.formatNumber(v.basic_plus_allowance) }" class="form-control total_allowance"  id="total_allowance-${i}" readonly></td>
<!--                        <td>-->
<!--                            <a href="#" class="btn btn-danger btn-sm my-1 edit-allowance" data-toggle="modal" data-target="#allowanceModal">-->
<!--                                <i class="fa fa-pencil" aria-hidden="true"></i> Edit-->
<!--                            </a>-->
<!--                        </td>-->
                        <input type="hidden" name="id[]" value="${v.id}" class="form-control payid"  id="payroll_item-${i}">
                        <input type="hidden" name="absent_days[]" value="${v.absent_days}" class="form-control absent"  id="absent_days-${i}">
                        <input type="hidden" name="rate_per_day[]" value="${v.rate_per_day} class="form-control rate"  id="rate-days-${i}">
                        <input type="hidden" name="rate_per_month[]" value="${v.basic_pay} class="form-control rate-month"  id="rate-month-${i}">
                    </tr>
                `;
            },
            deductionRow(v, i) {

                return `
                <tr>
                                <td> ${v.employee_id }</td>
                                <td>${ v.name }</td>
                                <td>${ accounting.formatNumber(v.netpay) }</td>
                                <td><input type="text" name="total_other_allowances[]" class="form-control other-allow"
                                        id="total_other_allowances-${i}" value="${v.other_allowances }" readonly>
                                </td>
                                <td>
                                    <input type="text" name="total_benefits[]" class="form-control benefits"
                                        id="total_benefits-${i}" value="${v.benefits }" readonly>
                                </td>

                                <td>
                                    <input type="text" name="loan[]" class="form-control loan"
                                        id="loan-${i}" value="${v.loan }" readonly>
                                </td>
                                <td>
                                    <input type="text" name="advance[]" class="form-control advance"
                                        id="advance-${i}" value="${v.advance }" readonly>
                                </td>
                                <td>
                                    <input type="text" id="other_taxable_deductions-${i }" name="other_taxable_deductions[]"   value="${v.other_taxable_deductions }" class="form-control other-deductions" readonly>
                                </td>
                                <td>
                                    <input type="text" id="net_after_bnd-${i }" name="net_after_bnd[]"   value="${(v.net_after_bnd) }" class="form-control other-deductions" readonly style="min-width: 150px">
                                </td>



<!--                                <td>-->
<!--                                    <a href="#" class="btn btn-danger btn-sm my-1 edit-other" data-toggle="modal" data-target="#otherModal">-->
<!--                                        <i class="fa fa-pencil" aria-hidden="true"></i> Edit-->
<!--                                    </a>-->
<!--                                </td>-->

                                <input type="hidden" name="id[]" class="other-id" value="${ v.id }">
                                <input type="hidden" name="payroll_id" value="${v.payroll_id }">

                </tr>
                `;
            },

        };
        $(() => Index.init());
    </script>
@endsection
