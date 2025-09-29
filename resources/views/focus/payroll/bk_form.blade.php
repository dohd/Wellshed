<div class="card-content">
    <div class="card-body">
        <div class="form-group">
            <div class="col-4">
                <label for="date">Processing Date</label>
                <input type="date" id="processing_date" class="form-control" name="processing_date">
            </div>
        </div>
            
        <div class="form-group row">
            <label for="date_range" class="col-12">Date Range:</label>
            <div class="col-3">
                <label for="date_from">Date From</label>
                <input type="date" id="pdate_from" class="form-control" name="date_from">
            </div>
            <div class="col-3">
                <label for="date_to">Date To</label>
                <input type="date" id="date_to" class="form-control" name="date_to">
            </div>
        </div>
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                   aria-selected="true">Employee Information</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab"
                   aria-selected="false">Deductions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3" role="tab"
                   aria-selected="false">Benefits</a>
            </li>
        </ul>
        <div class="tab-content px-1 pt-1">
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">

                <div class="card-content">
                    <div class="card-body">
                        <table id="employeebl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Basic Pay</th>
                                    <th>Total Allowances</th>
                                    <th>Gross Pay</th>
                                    <th>Absent Days</th>
                                    <th>Attendance Rate</th>
                                    <th>Gross After Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                @endphp
                                @foreach ($employees as $employee)
                                    @if ($employee->employees_salary)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $employee->employees_salary->employee_name }}</td>
                                        <td>{{ amountFormat($employee->employees_salary->basic_pay) }}</td>
                                        <td>{{ amountFormat($employee->employees_salary->house_allowance + $employee->employees_salary->transport_allowance) }}</td>
                                        <td>{{ amountFormat($employee->gross_pay) }}</td>
                                        <td>{{$employee->absent_days}}</td>
                                        <td>{{amountFormat($employee->attendance_rate)}}</td>
                                        <td>{{amountFormat($employee->gross_after_attendance)}}</td>
                                        <input type="hidden" name="basic_pay" value="{{$employee->employees_salary->basic_pay}}" id="">
                                    </tr>
                                    @endif
                                @endforeach
                               
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                <div class="card-content">
                    <div class="card-body">
                        <table id="employeebl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Gross After Attendance</th>
                                    <th>Paye</th>
                                    <th>Nssf</th>
                                    <th>SHIF</th>
                                    <th>Gross Less Deduction</th>
                                    <th>Advance Payment</th>
                                    <th>Gross Less Advance</th>
                                    <th>Loan</th>
                                    <th>Surcharge</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                @endphp
                                @foreach ($employees as $employee)
                                    @if ($employee->employees_salary)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{amountFormat($employee->gross_after_attendance)}}</td>
                                        <td>{{amountFormat($employee->paye)}}</td>
                                        <td>{{amountFormat($employee->nssf)}}</td>
                                        <td>{{amountFormat($employee->nhif)}}</td>
                                        <td>{{amountFormat($employee->gross_less_deductions)}}</td>
                                        <td>{{amountFormat($employee->advance)}}</td>
                                        <td>{{amountFormat($employee->gross_less_advance)}}</td>
                                        <td>{{amountFormat($employee->loans)}}</td>
                                        <td>{{amountFormat($employee->surcharge)}}</td>
                                    
                                        <input type="hidden" name="basic_pay" value="{{$employee->employees_salary->basic_pay}}" id="">
                                    </tr>
                                    @endif
                                @endforeach
                               
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                <div class="card-content">
                    <div class="card-body">
                        <table id="employeebl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Gross After Deductions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                @endphp
                                @foreach ($employees as $employee)
                                    @if ($employee->employees_salary)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{amountFormat($employee->gross_less_deductions)}}</td>
                                    
                                        <input type="hidden" name="basic_pay" value="{{$employee->employees_salary->basic_pay}}" id="">
                                    </tr>
                                    @endif
                                @endforeach
                               
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section("after-scripts")
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }}
    };

    const Index = {
        dateFrom: @json(request('date_from')),
        init() {
            this.drawDataTable();
            $('#date_from').val(this.dateFrom).change(this.dateFromChange)
            console.log($('#date_from').val(this.dateFrom).change());
        },

        dateFromChange() {
            Index.dateFrom = $(this).val();
            console.log($(this).val());
            return $(this).val();
            // $.post("{{ route("biller.payroll.get_employee") }}", 
            //     function (data) {
                    
            //     },
                
            // );
        },
        drawDataTable() {
            $('#employeeTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.payroll.get_employee") }}',
                    type: 'post',
                    data: {
                        
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'employee_name', name: 'employee_name'},
                    {data: 'basic_pay', name: 'basic_pay'},
                    {data: 'total_allowances', name: 'total_allowances'}, 
                    {data: 'gross_pay', name: 'gross_pay'},                   
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print']
            });
        },
    };
    $(() => Index.init());

</script>
@endsection
