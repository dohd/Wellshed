@extends ('core.layouts.app')

@section ('title', 'Employee Appraisals')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Employee Appraisals </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.employeeAppraisal.partials.header-buttons')
                </div>
            </div>
        </div>

    </div>


    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">

                            <div class="row mb-2">
                                <div class="col-9 col-lg-4">
                                    <label for="employee" >Filter by Employee</label>
                                    <select class="form-control box-size filter" id="employee" name="employee" data-placeholder="Filter by Employee">

                                        <option value=""> Select Employee </option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp['id'] }}">{{ $emp['first_name'] . " " . $emp['last_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-9 col-lg-4">
                                    <label for="employment_type" >Filter by Employment Type</label>
                                    <select class="form-control box-size filter" id="employment_type" name="employment_type" data-placeholder="Filter by Employment Type">

                                        <option value=""> Select Employment Type </option>
                                        @foreach (['Permanent', 'Casual', 'Fixed term', 'Part time'] as $type)
                                            <option value="{{ $type }}" >{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-9 col-lg-2">
                                    <label for="Appraisal_type" >Filter by Appraisal Type</label>
                                    <select class="form-control box-size filter" id="appraisal_type" name="Appraisal_type" data-placeholder="Filter by Appraisal Type">

                                        <option value=""> Select Appraisal Type </option>
                                        @foreach ($appraisal_types as $type)
                                            <option value="{{ $type->id }}" >{{ $type->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-2">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>
                            </div>

                            <table id="appraisalsTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Employment Date</th>
                                        <th>Employment Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        {{-- <th>Absent Days</th> --}}
                                        <th>Score (%)</th>
                                        <th>Tasks (%)</th>
                                        <th>% Performance</th>
                                        <th>Hours(Avg) %</th>
                                        <th>Attendance (%)</th>
                                        <th>Total Avg (%)</th>
                                        <th>Supervisor</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
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
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}

<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    const employeeFilter = $('#employee');
    const employmentTypeFilter = $('#employment_type');
    const appraisalTypeFilter = $('#appraisal_type');

    employeeFilter.select2({ allowClear: true });
    employmentTypeFilter.select2({ allowClear: true });
    appraisalTypeFilter.select2({ allowClear: true });

    $('.filter').change(() => {
        $('#appraisalsTable').DataTable().destroy();
        draw_data();
    })

    const clearFilters = $('#clearFilters');


    clearFilters.click(() => {

        employeeFilter.val('').trigger('change');
        employmentTypeFilter.val('').trigger('change');
        appraisalTypeFilter.val('').trigger('change');

        $('#appraisalsTable').DataTable().destroy();
        draw_data();
    })


    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#appraisalsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.employee_appraisals.index") }}',
                type: 'GET',
                data: {
                    employeeFilter: employeeFilter.val(),
                    employmentTypeFilter: employmentTypeFilter.val(),
                    appraisalTypeFilter: appraisalTypeFilter.val(),
                }
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {
                    data: 'employee',
                    name: 'employee'
                },
                {
                    data: 'employment_date',
                    name: 'employment_date'
                },
                {
                    data: 'employment_type',
                    name: 'employment_type'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'end_date',
                    name: 'end_date'
                },
                // {
                //     data: 'absent',
                //     name: 'absent'
                // },
                {
                    data: 'score',
                    name: 'score'
                },
                {
                    data: 'tasks_count',
                    name: 'tasks_count'
                },
                {
                    data: 'tasks_avg_work_done',
                    name: 'tasks_avg_work_done'
                },
                {
                    data: 'tasks_avg_hours',
                    name: 'tasks_avg_hours'
                },
                {
                    data: 'attendance_percent',
                    name: 'attendance_percent'
                },
                {
                    data: 'total_percentage_avg',
                    name: 'total_percentage_avg'
                },
                {
                    data: 'supervisor',
                    name: 'supervisor'
                },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [0, "desc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection