<div class="card-content" >
    <form id="basicSalary" action="{{ route('biller.payroll.store_summary')}}" method="post">
        @csrf
        <input type="hidden" name="payroll_id" value="{{ $payroll->id }}" id="">
        <div class="card-body">
            <table id="summaryTable" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Basic Salary</th>
                        <th>Max Hourly Salary</th>
                        <th>Hourly Wage</th>
                        <th>Hours Worked</th>
                        <th>Basic Hourly Salary</th>
                        <th>Absent Days</th>
                        <th>Absenteeism Deduction Rate</th>
                        <th>Absenteeism Deduction</th>
                        <th>Total Basic Salary </th>
                        <th>House Allowance</th>
                        <th>Transport Allowance</th>
                        <th>Other Allowance</th>
                        <th>Total Allowances</th>
                        <th>Basic + Allowance</th>
                        <th>NSSF</th>
                        <th>SHIF</th>
                        <th>Taxable Deductions</th>
                        <th>Narration</th>
                        <th>Taxable Gross</th>
                        <th>Housing Levy</th>
                        <th>Income Tax</th>
                        <th>Personal Relief</th>
                        <th>PAYE</th>
                        <th>Pay after PAYE</th>
                        <td>Loan Deduction</td>
                        <td>Advance Deduction</td>
                        <th>Other Benefits</th>
                        <th>Other Deductions</th>
                        <th>Other Allowances</th>
                        <th>Net Pay</th>

                        
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                        
                    @endphp
                    @foreach ($payrollItems as $item)
                        @if ($item)

                        <tr>
                            <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                            <td>{{ $item->name }}</td>
                            <input type="hidden" name="id[]" value="{{ $item->fixed_salary }}">
                            <input type="hidden" name="payroll_id" value="{{ $item->max_hourly_salary }}">
                            <td>{{ amountFormat($item->fixed_salary) }}</td>
                            <td>{{ amountFormat($item->max_hourly_salary) }}</td>
                            <td>{{ amountFormat($item->pay_per_hr) }}</td>
                            <td>{{ $item->man_hours }}</td>
                            <td>{{amountFormat($item->basic_hourly_salary) }}</td>
                            <td>{{ $item->absent_days }}</td>
                            <td>{{ amountFormat($item->absent_daily_deduction) }}</td>
                            <td >{{ amountFormat($item->absent_total_deduction) }}</td>
                            <td >{{ amountFormat($item->basic_salary) }}</td>
                            <td >{{ amountFormat($item->house_allowance) }}</td>
                            <td>{{ amountFormat($item->transport_allowance) }}</td>
                            <td>{{ amountFormat($item->other_allowance) }}</td>
                            <td>{{ amountFormat($item->total_allowance) }}</td>
                            <td>{{ amountFormat($item->basic_plus_allowance) }}</td>
                            <td>{{ amountFormat($item->nssf) }}</td>
                            <td>{{ amountFormat($item->nhif) }}</td>
                            <td>{{ amountFormat($item->taxable_deductions) }}</td>
                            <td>{{ $item->deduction_narration }}</td>
                            <td>{{ amountFormat($item->taxable_gross) }}</td>
                            <td>{{ amountFormat($item->housing_levy) }}</td>
                            <td>{{ amountFormat($item->income_tax) }}</td>
                            <td>{{ amountFormat($item->personal_relief) }}</td>
                            <td>{{ amountFormat($item->paye) }}</td>
                            <input type="hidden" name="netpay[]" value="{{ $item->netpay }}" id="">
                            <td class="netpay">{{ amountFormat($item->netpay) }} </td>
                            <td>{{ amountFormat($item->loan) }}</td>
                            <td>{{ amountFormat($item->advance) }}</td>
                            <td>{{ amountFormat($item->benefits) }}</td>
                            <td>{{ amountFormat($item->other_deductions) }}</td>
                            <td>{{ amountFormat($item->other_allowances) }}</td>
                            <td>{{ amountFormat($item->net_after_bnd) }}</td>

                           
                        </tr>
                        
                        @endif
                    @endforeach
                   
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <div class="col-3">
                <label for="grand_total">Total Net Pay</label>
                <input type="text" class="form-control" id="total_net"  readonly>
                <input type="hidden" name="total_netpay" class="form-control" id="total_netpay_summary" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-netpay">Save Net Pay</button>
        </div>
    </form>
    
    
</div>