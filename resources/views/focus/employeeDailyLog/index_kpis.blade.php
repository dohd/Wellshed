@extends ('core.layouts.app')

@section ('title', 'KPI Track Report')

@section('page-header')
    <h1>{{ 'KPI Track Report' }}</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'KPI Track Report' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.employeeDailyLog.partials.edl-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card" id="filters">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-3">
                                    <label for="financial_year">Financial Years</label>
                                    <select name="financial_year" id="financial_year" class="form-control" style="border-radius:8px;">
                                        <option value="">-- Filter by financial_year --</option>
                                        @foreach ($financial_years as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="users">Search Employee</label>
                                    <select name="user_id" id="user" data-placeholder="Search Employee" class="form-control">
                                        <option value="">Search Employee</option>
                                        @foreach ($users as $user)
                                            <option value="{{$user->id}}">{{$user->fullname}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="">Frequency</label>
                                    <select name="frequency" id="frequency" class="form-control">
                                        @foreach ($frequencies as $frequency)
                                            <option value="{{$frequency}}">{{$frequency}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="month">Month</label>
                                    <select name="month" id="month" class="form-control" style="border-radius:8px;">
                                        <option value="">-- Filter by Month --</option>
                                        @foreach ($months as $mon)
                                            <option value="{{ $mon['value'] }}">{{ $mon['label'] }}</option>
                                        @endforeach
                                    </select>
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
                                    <table id="edlTbl"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Task Name</th>
                                            <th>Key Activity</th>
                                            <th>Tasks Per Frequency</th>
                                            <th>Employee Name</th>
                                        </tr>
                                        </thead>


                                        <tbody>
                                        <tr>
                                            <td colspan="100%" class="text-center text-success font-large-1"><i
                                                        class="fa fa-spinner spinner"></i></td>
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
    </div>
@endsection


@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script('focus/js/select2.min.js') }}
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>

        const config = {
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
            datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
        };
        const Index = {
            financial_year: '',
            frequency: '',
            month: '',
            
            init(){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('#user').select2({allowClear: true});
                $('#financial_year').change(Index.financialChange);
                $('#frequency').change(Index.frequencyChange);
                $('#user').change(Index.userChange);
                $('#month').change(Index.monthChange);
               
                
                Index.drawDataTable();
            },
            financialChange() {
                $('#edlTbl').DataTable().destroy();
                return Index.drawDataTable();
            },
            userChange() {
                $('#edlTbl').DataTable().destroy();
                return Index.drawDataTable();
            },
            frequencyChange() {
                $('#edlTbl').DataTable().destroy();
                return Index.drawDataTable();
            },
            monthChange() {
                $('#edlTbl').DataTable().destroy();
                return Index.drawDataTable();
            },
           
            drawDataTable(params = {}) {
                $('#edlTbl').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: {@lang('datatable.strings')},
                    ajax: {
                        url: '{{ route("biller.employee-daily-log.get_kpis") }}',
                        type: 'post',
                        data: {
                            financial_year_id: $('#financial_year').val(),
                            user_id: $('#user').val(),
                            frequency: $('#frequency').val(),
                            month: $('#month').val(),
                            ...params,
                        },
                    },
                    columns: [
                        {data: 'DT_Row_Index', name: 'id'},
                        {data: 'task_name', name: 'task_name'},
                        {data: 'key_activities', name: 'key_activities'},
                        {data: 'task_per_frequency', name: 'task_per_frequency'},
                        {data: 'user_name', name: 'user_name'},
                        // {data: 'actions', name: 'actions', searchable: false, sortable: false}
                    ],
                    order: [[0, "desc"]],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print']
                });
            },
        };
        $(Index.init)
    </script>
@endsection
