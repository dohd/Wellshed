<div class="card-content">
    <div class="card-body">
        <div class="row mb-1">
            <div class="col-2 h4">Total NETPAY</div>                           
            <div class="col-4 h4 netpay_worth">{{ $tallies['final_pay_total'] }}</div>
            <div class="col-4">
                <select name="" id="status" class="form-control status" style="border: none;">
                    <option value="permanent">Permanent</option>
                    <option value="casual">Casual Labourer</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>
        <table id="payrollTable" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Payroll ID</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>National ID</th>
                    <th>Fixed Salary</th>
                    <th>Max Hourly Salary</th>
                    <th>Pay Per Hour</th>
                    <th>Man Hours</th>
                    <th>Basic Hourly Salary</th>
                    <th>Absent Days</th>
                    <th>Absent Daily Deduction</th>
                    <th>Absent Total Deduction</th>
                    <th>Basic Salary</th>
                    <th>House Allowance</th>
                    <th>Transport Allowance</th>
                    <th>Other Allowance</th>
                    <th>Total Allowance</th>
                    <th>Basic Plus Allowance</th>
                    <th>Taxable Deductions</th>
                    <th>Deduction Narration</th>
                    <th>NSSF</th>
                    <th>Taxable Gross</th>
                    <th>SHIF</th>
                    <th>Housing Levy</th>
                    <th>Income Tax</th>
                    <th>SHIF Relief</th>
                    <th>Personal Relief</th>
                    <th>PAYE</th>
                    <th>Netpay</th>
                    <th>Loan</th>
                    <th>Advance</th>
                    <th>Benefits</th>
                    <th>Other Deductions</th>
                    <th>Other Allowances</th>
                    <th>Net Pay</th>
                    <th>MPESA Number</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>