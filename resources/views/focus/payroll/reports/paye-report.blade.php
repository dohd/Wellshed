<div class="card-content">
    <div class="card-body">
        <div class="card-body">
            <div class="row mb-1">
                <div class="col-2 h4">Total PAYE</div>                           
                <div class="col-4 h4 paye_worth">{{ $tallies['paye_total'] }}</div>
            </div>
            <table id="payeTbl"
                   class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%"
                width="100%">
                <thead>
                    <tr>
                        <th>PIN of Employee</th>
                        <th>Name of Employee</th>
                        <th>Residential Status</th>
                        <th>Type of Employee</th>
                        <th>Basic Salary</th>
                        <th>Housing Allowance</th>
                        <th>Transport Allowances</th>
                        <th>Leave Pay</th>
                        <th>Overtime Allowance</th>
                        <th>Director's Fee</th>
                        <th>Lump Sum Payment if any</th>
                        <th>Other Allowance</th>
                        <th>Total Cash Pay</th>
                        <td>Value of car benefit ("Value of car benefit" from D_Computation_of_car_benefit)</td>
                        <td>Other non-cash benefits</td>
                        <td>Total non-cash pay (D) = (B + C) (C if greater than 3000)</td>
                        <td>Global income (in case of non-full-time service director) (Kshs) (E)</td>
                        <td>Type of housing</td>
                        <td>Rent of house/Market value</td>
                        <td>Computed rent of house (F)</td>
                        <td>Rent recovered from employee (G)</td>
                        <td>Net value of housing (Kshs) (H) = (F - G)</td>
                        <td>Total gross pay (Kshs) (I) = (A + D + E + H)</td>
                        <td>30% of cash pay (J) = (A) * 30%</td>
                        <td>Actual Contribution (K)</td>
                        <td>Permissible limits (L)</td>
                        <td>Mortgage interest (Max 25,000 Kshs a month) (M)</td>
                        <td>Affordable housing relief (Max 9,000 Kshs a month) (N)</td>
                        <td>Amount of benefits (O) = (Lower of J, k, l + m)</td>
                        <td>Taxable pay (Kshs) (P) = (I - O)</td>
                        <td>Tax payable (Kshs) (Q) = (P) * Slab Rate</td>
                        <td>Monthly personal relief (Kshs) (R)</td>
                        <td>Amount of insurance/PRMF Relief (Total of "Amount of insurance/PRMF Relief" from E_computation_of_insu_relief) (Kshs) (S)</td>
                        <td>Payee tax (Kshs) (T) = (Q - R - S - N)</td>
                        <td>Self assessed PAYE tax (Kshs)</td>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>