<form action="{{ route('biller.payroll.store_paye') }}" method="post">
    @csrf
    <div class="card-content">
        <div class="card-body">
            <table id="payeTbl" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Taxable Pay</th>
                        <th>Income Tax</th>
                        <th>Personal Relief</th>
                        <th>PAYE</th>
                        <th>Pay After Tax</th>
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
                            <td>{{ amountFormat($item->taxable_gross) }}</td>
                            <td>{{ amountFormat($item->income_tax) }}</td>
                            <td>{{ amountFormat($item->personal_relief) }}</td>
                            <td>{{ amountFormat($item->paye) }}</td>
                            <td>{{ amountFormat(bcsub($item->taxable_gross, $item->paye, 2)) }}</td>
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="payroll_id"
                                value="{{ $item->payroll_id }}">
                            <input type="hidden" name="paye[]" value="{{ $item->paye }}"
                                id="">
{{--                            <input type="hidden" name="taxable_gross[]" value="{{ $item->gross_pay - $item->paye }}"--}}
{{--                                id="">--}}
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group">
            <div class="col-3">
                <label for="total">Total PAYE</label>
                <input type="text" value="{{ amountFormat($payrollItems->pluck('paye')->sum()) }}"
                    class="form-control" id="" readonly>
                <input type="hidden" name="paye_total" value="{{ $payrollItems->pluck('paye')->sum() }}"
                    class="form-control" id="paye_total" readonly>
{{--                <label for="total">Total PAYE</label>--}}
{{--                <input type="text" name="total_gross" value="{{ $total_gross }}"--}}
{{--                    class="form-control" id="paye_total" readonly>--}}
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-paye">Save PAYE</button>
        </div>
    </div>
</form>