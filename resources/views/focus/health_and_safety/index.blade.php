@extends ('core.layouts.app')

@section('title', 'Health and Safety Tracking')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title mb-0">Health and Safety Tracking</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.health_and_safety.partials.health-and-safety-header-buttons')
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
                                    <div class="col-3">
                                        <label for="client_filter">Client</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select class="custom-select filter" name="client_filter" id="client_filter" data-placeholder="Filter by Client">

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="project_filter" class="caption">Project</label>
                                            <select class="form-control filter" name="project_filter" id="project_filter" data-placeholder="Filter by Project">
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="status_filter" class="caption">Status</label>
                                            <select class="form-control filter" name="status_filter" id="status_filter" data-placeholder="Filter by Status">
                                                <option value=""> </option>
                                                <option value="first-aid-case"> First Aid Case </option>
                                                <option value="lost-work-day"> Lost Work Day </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="month_filter" class="caption">Month</label>
                                            <select class="form-control filter" name="month_filter" id="month_filter" data-placeholder="Filter by Month">
                                                <option value=""></option>
                                                @foreach($months as $m => $val)
                                                    <option value="{{ $val }}"> {{ $m }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-4">

                                        <button id="clear_filters" class="btn btn-secondary round" > Clear Filters </button>

                                    </div>

                                </div>


                                <table id="healthAndSafetyTable"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Client</th>
                                            <th>Project</th>
                                            <th>Incident</th>
                                            <th>Root Cause</th>
                                            <th>Status</th>
                                            <th>Resolution Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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

    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
    {{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>
        setTimeout(() => draw_data(),1500);

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        };

        const clientFilter = $('#client_filter');
        const projectFilter = $('#project_filter');
        const statusFilter = $('#status_filter');
        const monthFilter = $('#month_filter');
        const clearFilters = $('#clear_filters');


        statusFilter.select2();
        monthFilter.select2({});

        function select2Config(url, callback) {
            return {
                ajax: {
                    url,
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: ({term}) => ({q: term, keyword: term}),
                    processResults: callback
                }
            }
        }

        // load projects dropdown
        const projectUrl = "{{ route('biller.projects.project_search') }}";
        function projectData(data) {

            return {results: data.map(v => ({id: v.id, text: v.name}))};
        }
        $("#project_filter").select2(select2Config(projectUrl, projectData));

        $("#client_filter").select2({
            ajax: {
                url: "{{route('biller.customers.select')}}",
                dataType: 'json',
                type: 'POST',
                data: customer_id => ({customer_id}),
                processResults: (data) => {
                    return { results: data.map(v => ({text: v.company, id: v.id})) }
                },
            }
        });




        function draw_data() {
            const tableLan = {@lang('datatable.strings')};
            var dataTable = $('#healthAndSafetyTable').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: tableLan,
                ajax: {
                    url: '{{ route('biller.health-safety-table.get') }}',
                    data: {
                        client_filter: clientFilter.val(),
                        project_filter: projectFilter.val(),
                        status_filter: statusFilter.val(),
                        month_filter: monthFilter.val(),
                    },
                    type: 'post'
                },
                columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'client',
                        name: 'client'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'incident',
                        name: 'incident'
                    },
                    {
                        data: 'root_cause',
                        name: 'root_cause'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'resolution_time',
                        name: 'resolution_time'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                order: [
                    [0, "desc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print']
            });
        }

        $('.filter').change( () => {
            $('#healthAndSafetyTable').DataTable().destroy();
            draw_data();
        })

        clearFilters.click(() => {

            clientFilter.val('').trigger('change');
            projectFilter.val('').trigger('change');
            statusFilter.val('').trigger('change');
            monthFilter.val('').trigger('change');

            $('#healthAndSafetyTable').DataTable().destroy();
            draw_data();
        })


    </script>

@endsection
