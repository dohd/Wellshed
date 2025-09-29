<div class="card-content">
    <form id="basicSalary" action="{{ route('biller.payroll.store_otherdeduction') }}" method="post">
        @csrf
        <input type="hidden" name="payroll_id" value="{{ $payroll->id }}" id="">

        <div class="card-body">
            <button type="button" class="btn btn-sm btn-primary" id="refresh_other_deductions">Refresh Deduction</button>
            <table id="otherBenefitsTbl" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>

                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Pay After SHIF & Housing Levy</th>
                        <th>Other Allowances Totals</th>
                        <th>Benefits Totals</th>
                        <th>Loan</th>
                        <th>Advance</th>
                        <th>Deductions</th>
                        <th>Pay</th>

                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($payrollItems as $item)
{{--                        @if ($item)--}}
                            <tr>
                                <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->netpay }}</td>
                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                                <input type="hidden" id="netpay" value="{{ $item->netpay }}">
                                <td>
                                    <input type="text" id="total_other_allowances-{{ $i }}" name="other_allowances[]" class="form-control other-allow">
                                </td>
                                <td>
                                    <input type="text" id="total_benefits-{{ $i }}" name="benefits[]" class="form-control benefits">
                                </td>
                                <td width="15%">
                                    <input type="text" style="min-width: 150px" id="loan-{{ $i }}" name="loan[]" class="form-control loan">
                                </td>
                                <td width="15%">
                                    <input type="text" style="min-width: 150px" id="advance-{{ $i }}" value="{{number_format($item->advance, 2)}}" name="advance[]" class="form-control advance">
                                </td>
                                <td>
                                    <input type="text" style="min-width: 100px" id="total_other_deduction-{{ $i }}" name="other_deductions[]" class="form-control other-deductions">
                                </td>
                                <td>
                                    <input type="text" id="net_after_bnd-{{ $i }}" class="form-control net_after_bnd" readonly style="min-width: 400px">
                                </td>


                            </tr>
{{--                        @endif--}}
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group row">
            <div class="col-3">
                <label for="grand_total">Total Other Benefits</label>
                <input type="text" name="other_benefits_total" class="form-control" id="other_benefits_total"
                    readonly>
            </div>
            <div class="col-3">
                <label for="grand_total">Total Other Deductions</label>
                <input type="text" name="other_deductions_total" class="form-control" id="other_deductions_total"
                    readonly>
            </div>
            <div class="col-3">
                <label for="grand_total">Total Other Allowances</label>
                <input type="text" name="other_allowances_total" class="form-control" id="other_allowances_total"
                    readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-otherbenefits">Save Other Benefits and
                Deductions</button>
        </div>
    </form>


</div>
