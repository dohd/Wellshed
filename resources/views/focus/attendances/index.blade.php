@extends ('core.layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Attendance Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.attendances.partials.attendances-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row form-group">
                            <div class="col-4">
                                <label for="employee">Employee</label>
                                <select name="employee_id" id="employee" class="form-control" data-placeholder="Choose Employee">
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="date">Attendance Date (month - year)</label>
                                {{ Form::text('date', null, ['class' => 'form-control datepicker']) }}
                            </div>
                            <div class="col-2">
                                <label for="status">Attendance Status</label>
                                <select name="status" id="status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="late">Late/Away</option>
                                </select>
                            </div>                            
                            <div class="col-2">
                                <label for="status">Employee Status</label>
                                <select name="employee_status" id="employee_status" class="custom-select">
                                    <option value="">-- select employee status --</option>
                                    @foreach (['all','active', 'in_active'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </div>                            
                            <div class="col-2">
                                <label for="status">Employement Type</label>
                                <select name="employee_type" id="employee_type" class="custom-select">
                                    <option value="">-- select employment type --</option>
                                    @foreach (['Permanent','Casual', 'Fixed term','Part time'] as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="attendanceTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Date</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Hrs</th>
                                        <th>Status</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1">
                                            <i class="fa fa-spinner spinner"></i>
                                        </td>
                                    </tr>
                                </tbody>                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        employeeId: '',
        status: '',
        date: '',

        init() {
            // month picker
            $('.datepicker').datepicker({
                autoHide: true,
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                format: 'MM - yyyy',
                onClose: function(dateText, inst) { 
                    $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                }
            }).change(this.dateChange);


            $('#employee').select2({allowClear: true}).val('').trigger('change')
            .change(this.employeeChange);

            $('#status').change(this.statusChange);
            $('#employee_status').change(this.employeeStatusChange);
            $('#employee_type').change(this.employeeTypeChange);
            $('#date').change(this.dateChange);
            this.drawDataTable();
        },

        employeeChange() {
            Index.employeeId = $(this).val();
            $('#attendanceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        statusChange() {
            Index.status = $(this).val();
            $('#attendanceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },
        employeeStatusChange() {
            Index.employee_status = $(this).val();
            $('#attendanceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },
        employeeTypeChange() {
            Index.employee_type = $(this).val();
            $('#attendanceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        dateChange() {
            Index.date = $(this).val();
            $('#attendanceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },


        drawDataTable() {
            $('#attendanceTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.attendances.get') }}",
                    type: 'POST',
                    data: {
                        employee_id: this.employeeId, 
                        status: this.status, 
                        employee_status: this.employee_status, 
                        employee_type: this.employee_type, 
                        date: this.date
                    }
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'employee', name: 'employee'},
                    {data: 'date', name: 'date'},                    
                    {data: 'clock_in', name: 'clock_in'},
                    {data: 'clock_out', name: 'clock_out'},
                    {data: 'hrs', name: 'hrs'},
                    {data: 'status', name: 'status'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
