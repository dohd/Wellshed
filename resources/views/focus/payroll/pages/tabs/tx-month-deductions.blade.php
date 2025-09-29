<form action="{{ route('biller.payroll.store_deduction') }}" method="post">
    @csrf
    <div class="card-content">
        <div class="card-body">
            <table id="deductionTbl" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Gross Pay</th>
                        <th>NSSF</th>
                        <th>SHIF</th>
                        <th>Housing Levy</th>
                        <th>Other Deductions</th>
                        <th>Narration</th>
                        <th>Pay</th>
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
                            <td class="editable-cell">{{ amountFormat($item->basic_plus_allowance) }}</td>
                            <td>{{ amountFormat($item->nssf) }}</td>
                            <td>{{ amountFormat($item->nhif) }}</td>
                            <td>{{ amountFormat($item->housing_levy) }}</td>
{{--                            <td>{{ amountFormat($item->taxable_gross) }}</td>--}}
                            <td>
                                <input type="number" step="0.01" class="form-control deduction" value="{{ $item->additional_taxable_deductions }}" name="additional_taxable_deductions[]"
                                       @if($item->netpay != 0.00) readonly @endif
                                >
                            </td>
                            <td>
                                <textarea class="form-control deduction" name="deduction_narration[]" @if($item->netpay != 0.00) readonly @endif style="min-width: 400px">{{ $item->deduction_narration }}</textarea>
                            </td>
                            <td>
                                <input type="text" id="net_post_tx_deduction" class="form-control net_post_tx_deduction" value="{{ $item->taxable_gross == 0.00 ? $item->basic_plus_allowance : $item->taxable_gross }}" readonly>
                            </td>

                            @if ($total_tx_deduction > 0)
{{--                                <td>--}}
{{--                                    <a href="#" class="btn btn-danger btn-sm my-1 edit-deduction" data-toggle="modal" data-target="#deductionModal">--}}
{{--                                        <i class="fa fa-pencil" aria-hidden="true"></i> Edit--}}
{{--                                    </a>--}}
{{--                                </td>--}}
                            @endif


                            <input type="hidden" name="id[]" class="id" value="{{ $item->id }}">
                            <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                            <input type="hidden" id="basic_plus_allowance" class="basic_plus_allowance" value="{{ $item->basic_plus_allowance }}">
                            <input type="hidden" name="nssf[]" class="nssf" value="{{ $item->nssf }}" id="">
                            <input type="hidden" name="nhif[]" class="nhif" value="{{ $item->nhif }}" id="">
                            <input type="hidden" name="housing_levy[]" class="housing_levy" value="{{ $item->housing_levy }}" id="">
{{--                            name="total_sat_deduction[]"--}}
                            <input type="hidden" value="{{ $item->nhif + $item->nhif }}" id="">
{{--                            name="gross_pay[]"--}}
                            <input type="hidden" value="{{ $item->gross_pay }}" id="">


                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group row">
            <div class="col-2">
                <label for="total">Total NSSF</label>
                <input type="text" value="{{ amountFormat(bcmul($total_nssf, 2, 2)) }}" class="form-control" readonly>
                <input type="hidden" name="total_nssf" value="{{ bcmul($total_nssf, 2, 2) }}" class="form-control" id="total_nssf" readonly>
            </div>
            <div class="col-2">
                <label for="total">Total SHIF</label>
                <input type="text" value="{{ amountFormat($total_nhif) }}" class="form-control" readonly>
                <input type="hidden" name="total_nhif" value="{{ $total_nhif }}" class="form-control" id="total_nssf" readonly>
            </div>
            <div class="col-2">
                <label for="total">Total Housing Levy</label>
                <input type="text" value="{{ amountFormat(bcmul($total_housing_levy, 2, 2)) }}" class="form-control" readonly>
                <input type="hidden" name="total_housing_levy" value="{{ bcmul($total_housing_levy, 2, 2) }}" class="form-control" id="total_nssf" readonly>
            </div>
            <div class="col-2">
                <label for="total">Other Deductions</label>
                <input type="text" value="{{ amountFormat($total_tx_deduction) }}"
                    class="form-control" id="other_taxable_deductions" readonly>
                <input type="hidden" name="other_taxable_deductions" value="{{ $total_tx_deduction }}"
                    class="form-control" id="other_taxable_deductions_hidden" readonly>
            </div>
            <div class="col-3">
                <label for="total">Total Employee Deductions</label>
                <input type="text" value="{{ $payroll->other_taxable_deductions ? amountFormat($payroll->total_nssf + $payroll->total_nhif + $payroll->total_housing_levy + $payroll->other_taxable_deductions) : 0.00 }}" class="form-control" id="total_taxable_deductions" readonly>
                <input type="hidden" value="" class="form-control" id="total_taxable_deductions_hidden" name="total_taxable_deductions" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-deduction">Save Deductions</button>
        </div>
    </div>
</form>