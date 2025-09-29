
<div class='row mb-2'>
    <div class='col-12 col-md-8'>
        {{ Form::label( 'employee', 'Employee Name',['class' => 'control-label']) }}

        <label for="employee">Employee</label>
        <select name="employee_id" id="employee_id" class="form-control round" required @if(!empty($salary)) disabled @endif>
            <option value="">-- Select Employee --</option>
            @foreach ($employees as $emp)
                <option value="{{ $emp['id'] }}"
                        @if(!empty($salary))
                            @if($salary->employee_id === $emp['id']) selected @endif
                        @endif
                >{{ $emp['full_name'] }}</option>
            @endforeach
        </select>

    </div>



</div>

<div class='row mb-1'>


    <div class="col-12 col-md-3">
        {{ Form::label( 'hourly_salary', 'Pay Split (Hourly/None)',['class' => 'control-label']) }}
        <select class="form-control round" name="hourly_salary" id="hourly_salary">
            <option value="0.00">Net Retainer</option>
            <option value="1.00">No Basic Salary (Fully on Hourly Salary)</option>
            <option value="0.50">50 Percent</option>
            <option value="0.40">40 Percent</option>

            @if(\Illuminate\Support\Facades\Auth::user()->ins === 2)
                <option value="7.77">Sales Commission</option>
            @endif
        </select>
    </div>

    <div class="col-12 col-md-2">
        {{ Form::label( 'basic_salary', 'Basic Pay',['class' => 'control-label']) }}
        {{ Form::number('basic_salary', null, ['id' => 'basic_salary', 'step' => '0.01', 'class' => 'form-control round', 'placeholder' => '0.00', 'required']) }}
    </div>

    <div class="col-12 col-md-2">
        {{ Form::label( 'hourly_wage', 'Hourly Wage',['class' => 'control-label']) }}
        {{ Form::number('hourly_wage', null, ['id' => 'hourly_wage' , 'step' => '0.01', 'class' => 'form-control round', 'placeholder' => '0.00', 'disabled' => true, 'required']) }}
    </div>

    @if(\Illuminate\Support\Facades\Auth::user()->ins === 2)
        <div class="col-12 col-md-2">
            {{ Form::label( 'sale_rate', 'Sale Rate (%)',['class' => 'control-label']) }}
            {{ Form::number('sale_rate', null, ['id' => 'sale_rate' , 'step' => '0.01', 'class' => 'form-control round', 'placeholder' => '0.00', 'disabled' => true, 'required']) }}
        </div>
    @endif

</div>

<div class='row mb-1'>


    <div class="col-12 col-md-4">
        <label for="nhif">SHIF Status</label>
        <select name="nhif" id="nhif" class="form-control round" required >
            <option value="">-- Select SHIF Status --</option>
            @php
                $nhifOptions = [
                    'Make Deduction' => 1,
                    'Exempt' => 0
                ];

            @endphp

            @foreach ($nhifOptions as $option => $value)
                <option value="{{ $value }}"
                        @if(!empty($salary))
                            @if($salary->nhif === $value) selected @endif
                        @endif
                >{{ $option }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12 col-md-4">
        <label for="deduction_exempt"> Deduct PAYE, NSSF & Housing Levy </label>
        <select name="deduction_exempt" id="deduction_exempt" class="form-control round" required >
            <option value="">-- Select Deduction Status --</option>
            @php
                $deductionOptions = [
                    'Make All Deductions' => 0,
                    'No Deductions' => 1
                ];

            @endphp

            @foreach ($deductionOptions as $option => $value)
                <option value="{{ $value }}"
                        @if(!empty($salary))
                            @if($salary->deduction_exempt === $value) selected @endif
                        @endif
                >{{ $option }}</option>
            @endforeach
        </select>
    </div>

</div>


<div class="row">
    <div class="col-12 col-md-3">
        <label for="commencement_date" class="form-label">Commencement Date</label>
        <input type="date" class="form-control" id="commencement_date" name="commencement_date" value="{{@$salary->commencement_date}}" required>
    </div>
</div>

@if(!empty($salaryHistory))
    <div class="container mt-4">
        <h1>Salary History</h1>
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Set On</th>
                <th>Commencement Date</th>
                <th>Job Grade</th>
                <th>Basic Salary</th>
                <th>Hourly Salary</th>
                <th>SHIF</th>
                <th>Deductions Exempt</th>
            </tr>
            </thead>
            <tbody>
            @foreach (@$salaryHistory as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ (new DateTime($record['date']))->format("jS F, Y") }}</td>
                    <td>{{ $record['commencement_date'] ? (new DateTime($record['commencement_date']))->format("jS F, Y") : '' }}</td>
                    <td>{{ $record['job_grade'] }}</td>
                    <td>{{ $record['basic_salary'] }}</td>
                    <td>{{ $record['hourly_salary'] }}</td>
                    <td>{{ boolval($record['nhif']) ? 'Deducted' : "Exempt" }}</td>
                    <td>{{ boolval($record['deduction_exempt']) ? "Exempt" : 'Deducted'}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="edit-form-btn text-right mt-3 mb-2">
    {{ link_to_route('biller.salary.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md px-5']) }}
    {{ Form::submit( empty($salary) ? 'Create' : 'Update' , ['class' => 'btn btn-primary btn-md px-5']) }}
    <div class="clearfix"></div>
</div>


@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        $('#employee_id').select2()

        $(document).ready(function () {
            // Event listener for the select input
            $('#hourly_salary').on('change', function () {
                let selectedValue = $(this).val();

                // Toggle input based on selected value
                if (selectedValue === '1.00') {

                    $('#hourly_wage').prop('disabled', false)

                    $('#basic_salary').prop('disabled', true);
                    $('#sale_rate').prop('disabled', true);

                    $('#basic_salary').val(null);
                    $('#sale_rate').val(null);
                }
                else if (selectedValue === '7.77') {

                    $('#sale_rate').prop('disabled', false);
                    $('#basic_salary').prop('disabled', false);

                    $('#hourly_wage').prop('disabled', true);

                    $('#hourly_wage').val(null);
                }
                else {

                    $('#basic_salary').prop('disabled', false);

                    $('#hourly_wage').prop('disabled', true);
                    $('#sale_rate').prop('disabled', true);

                    $('#hourly_wage').val(null);
                    $('#sale_rate').val(null);
                }
            });

            $('#sale_rate').on('input', function () {

                if ($(this).val() > 100) $(this).val(100);
            });
        });

    </script>

@endsection
