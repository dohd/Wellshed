<form action="{{ route('biller.payroll.store_allowance') }}" method="post">
    @csrf
    <div class="card-content">
        <div class="card-body">
            <button type="button" class="btn btn-sm btn-primary" id="refresh_allowance">Refresh Allowances</button>
            <table id="allowanceTbl" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Basic Pay</th>
                        <th>Absent Days</th>
                        <th>Housing Allowance</th>
                        <th>Transport</th>
                        <th>Other Allowances</th>
                        <th>Total Allowance</th>
                        <th class="w-100">Gross Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($payrollItems as $item)
                        <tr>
                            <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ amountFormat($item->basic_salary) }}</td>
                            <td>{{ $item->absent_days }}</td>
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                            <input type="hidden" class="basic" value="{{ $item->basic_salary }}">
                            <input type="hidden" name="basic_plus_allowance[]" class="total_basic_allowance">
                            <input type="hidden" class="form-control absent_day" value="{{ $item->absent_days }}" id="absent_day-{{ $i }}">

                            @php

                                $allowances = \App\Models\salary\Salary::where('employee_id', $item->employee_id)
                                    ->select(
                                        'house_allowance',
                                        'transport_allowance',
                                    )
                                    ->first();

                            @endphp

                            <td>
                                <input type="number" class="form-control house"
                                    id="house-{{ $i }}">
                                <input type="number" name="house_allowance[]"
                                    class="form-control house_allowance"
                                    id="house_allowance-{{ $i }}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control transport"
                                    id="transport-{{ $i }}">
                                <input type="number" name="transport_allowance[]"
                                    class="form-control transport_allowance"
                                    id="transport_allowance-{{ $i }}" readonly>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="other_allowance[]"
                                    class="form-control other_allowance"
                                    id="other_allowance-{{ $i }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="total_allowance[]"
                                    class="form-control total_allowance"
                                    id="total_allowance-{{ $i }}" readonly>
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control basic_plus_allowance" readonly>
                            </td>


                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group">
            <div class="col-3">
                <label for="total">Total Allowances</label>
                <input type="text" name="allowance_total" class="form-control"
                    id="allowance_total" readonly>
            </div>
            <div class="col-3 mt-2">
                <label for="total">Total Gross Pay</label>
                <input type="text" class="form-control" id="totalGrossPay" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-allowances">Save Allowances</button>
        </div>
    </div>
</form>