<form action="{{ route('biller.payroll.store_nhif') }}" method="post">
    @csrf
    <div class="card-content">
        <div class="card-body">
            <table id="nhifTbl" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Pay After PAYE</th>
                        <th>SHIF</th>
{{--                        <th>Pay After SHIF</th>--}}
                        <th>Housing Levy</th>
                        <th>Pay After SHIF & Housing Levy</th>
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
                            <td>{{ amountFormat(bcsub($item->taxable_gross, $item->paye, 2)) }}</td>
                            <td>{{ amountFormat($item->nhif) }}</td>
{{--                            <td>{{ amountFormat(bcsub($item->taxable_gross, bcadd($item->paye, $item->nhif, 2), 2)) }}</td>--}}
                            <td>{{ amountFormat($item->housing_levy) }}</td>
                            <td>{{ amountFormat($item->netpay) }}</td>
                            <input type="hidden" name="payroll_id"
                                value="{{ $item->payroll_id }}">     
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group row">
            <div class="col-3">
                <label for="total">Total SHIF</label>
                <input type="text" value="{{ amountFormat($total_nhif) }}"
                    class="form-control" id="" readonly>
                <input type="hidden" name="total_nhif" value="{{ $total_nhif }}"
                    class="form-control" id="total_nhif" readonly>
            </div>
            <div class="col-3">
                <label for="total">Total Housing Levy </label>
                <input type="text" value="{{ amountFormat(bcmul($total_housing_levy, 2, 2)) }}"
                    class="form-control" id="" readonly>
                <input type="hidden" name="total_nhif" value="{{ bcmul($total_housing_levy, 2, 2) }}"
                    class="form-control" id="total_nhif" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-nhif">Save SHIF</button>
        </div>
    </div>
</form>