@extends ('core.layouts.app')

@section('title', 'Health And Safety Tracking')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title mb-0">Health And Safety Tracking for {{ date('F Y') }}</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.health_and_safety.partials.health-and-safety-header-buttons')
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar-left" style="width: 250px;">
            <div class="sidebar">
                <div class="sidebar-content">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-content">
                                        <div class="content-header-left col-6">
                                            <h4 class="content-header-title">KEY</h4>
                                        </div>

                                        <table style="display:inline-table"  class="table table-hover table-striped table-condensed">
                                            <thead>
                                                <th>Color</th>
                                                <th>Status</th>
                                            </thead>
                                            <tr>
                                                <td>Red</td>
                                                <td>Lost Work Day</td>
                                            </tr>
                                            <tr>
                                                <td>Yellow</td>
                                                <td>First Aid Case</td>
                                            </tr>
                                            <tr>
                                                <td>Green</td>
                                                <td>No Incident</td>
                                            </tr>
                                            {{-- <tr>
                                                <td>White</td>
                                                <td>Pending</td>
                                            </tr> --}}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="content-left" style="width: calc(100% - 270px)">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="content-header row mb-1">
                                    <div class="content-header-left col-6">
                                        <h4 class="content-header-title">{{ date('F Y') }} Health and Safety Green Cross
                                            Calendar</h4>
                                    </div>
                                    <div class="col-6">
                                        <div class="media-body media-right text-right">
                                        </div>
                                    </div>
                                </div>
                                {{-- <table>
                                    @php
                                        $values = [1, 2, 3, 4];
                                        $chunks = array_chunk($values, 2); // Split the array into chunks of 2 elements

                                        // dd($chunk3);
                                        foreach ($chunks as $chunk) {
                                            echo '<tr>';
                                            foreach ($chunk as $value) {
                                                echo '<td>' . $value . '</td>';
                                            }
                                            echo '</tr>';
                                        }
                                    @endphp
                                </table> --}}

                                <table class="healthAndSafetyTable" id="healthAndSafetyTable">
                                    {{-- <tbody> --}}
                                    @foreach ($chunk1 as $a)
                                        <tr>
                                            <td colspan="3"
                                                style='border-left:none; border-top:none; border-bottom:none'>
                                            </td>
                                            @foreach ($a as $value)
                                                @if ($value['color'] == 'red')
                                                    <td class="danger">{{ $value['day'] }}</td>
                                                @elseif($value['color'] == 'yellow')
                                                    <td class="warning">{{ $value['day'] }}</td>
                                                @elseif($value['color'] == 'green')
                                                    <td class="good">{{ $value['day'] }}</td>
                                                @endif
                                            @endforeach
                                            <td colspan="3"
                                                style='border-top:none;border-bottom:none;border-right:none;'>
                                            </td>
                                            {{-- <td colspan="3"
                                                    style='border-left:none; border-top:none; border-bottom:none'>
                                                </td>
                                                <td class="good" id="day1">{{$day['day']}}</td>
                                                <td class="good">{{$day['day']}}</td>
                                                <td colspan="3"
                                                    style='border-top:none;border-bottom:none;border-right:none;'>
                                                </td> --}}
                                        </tr>
                                    @endforeach
                                    {{-- </tbody> --}}
                                    {{-- <tr>
                                        <td colspan="3" style='border-left:none;border-top:none;'></td>
                                        <td class="good">3</td>
                                        <td class="good">4</td>
                                        <td colspan="3" style='border-left:none;border-top:none;border-right:none;'></td>
                                    </tr> --}}
                                    @foreach ($chunk2 as $b)
                                        <tr>
                                            @foreach ($b as $value)
                                                @if ($value['color'] == 'red')
                                                    <td class="danger">{{ $value['day'] }}</td>
                                                @elseif($value['color'] == 'yellow')
                                                    <td class="warning">{{ $value['day'] }}</td>
                                                @elseif($value['color'] == 'green')
                                                    <td class="good">{{ $value['day'] }}</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    @foreach ($chunk3 as $c)
                                        <tr>
                                            <td colspan="3"
                                                style='border-left:none; border-top:none; border-bottom:none'>
                                            </td>
                                            @foreach ($c as $value)
                                                @if ($value['color'] == 'red')
                                                    <td class="danger">{{ $value['day'] }}</td>
                                                @elseif($value['color'] == 'yellow')
                                                    <td class="warning">{{ $value['day'] }}</td>
                                                @elseif($value['color'] == 'green')
                                                    <td class="good">{{ $value['day'] }}</td>
                                                @endif
                                            @endforeach
                                            <td colspan="3"
                                                style='border-right:none; border-top:none; border-bottom:none'>
                                            </td>
                                        </tr>
                                    @endforeach
                                    {{-- 
                                    <tr>
                                        <td colspan="3" style='border-left:none; border-top:none; border-bottom:none'>
                                        </td>
                                        <td class="good">21</td>
                                        <td class="good">22</td>
                                        <td colspan="3" style='border-right:none; border-top:none; border-bottom:none'>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style='border-left:none; border-top:none; border-bottom:none'>
                                        </td>
                                        <td class="good">23</td>
                                        <td class="good">24</td>
                                        <td colspan="3" style='border-right:none; border-top:none; border-bottom:none'>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style='border-left:none; border-top:none; border-bottom:none'>
                                        </td>
                                        <td class="good">25</td>
                                        <td class="good">26</td>
                                        <td colspan="3" style='border-right:none; border-top:none; border-bottom:none'>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style='border-left:none; border-top:none; border-bottom:none'>
                                        </td>
                                        <td class="good">27</td>
                                        <td class="good">28</td>
                                        <td colspan="3" style='border-right:none; border-top:none; border-bottom:none'>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style='border-left:none; border-top:none; border-bottom:none'>
                                        </td>
                                        <td class="good">29</td>
                                        <td class="good">30</td>
                                        <td colspan="3" style='border-right:none; border-top:none; border-bottom:none'>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style='border-left:none; border-top:none; border-bottom:none'>
                                        </td>
                                        <td class="good">31</td>
                                        <td class=""></td>
                                        <td colspan="3" style='border-right:none; border-top:none; border-bottom:none'>
                                        </td>
                                    </tr> --}}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Content -->
    </div>

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    {{-- <input type="hidden" id="loader_url" value="{{route('biller.projects.load')}}"> --}}
    @include('focus.health_and_safety.modal.new')
@endsection

@section('after-styles')
    <style>
        .incidentsTable {
            /* position: relative; */
            border-collapse: collapse;
            margin: 20px auto;

        }

        .incidentsTable th,
        .incidentsTable td {
            border: 1px solid black;
            width: 50px;
            height: 50px;
            text-align: center;
            color: black;
        }


        .healthAndSafetyTable {
            border-collapse: collapse;
            margin: 20px auto;

        }

        .healthAndSafetyTable th,
        .healthAndSafetyTable td {
            border: 1px solid black;
            width: 50px;
            height: 50px;
            text-align: center;
            color: black;
            cursor: pointer;
        }

        .good {
            background-color: green;
        }

        .warning {
            background-color: yellow;
        }

        .danger {
            background-color: red;
        }
    </style>
@endsection

{{-- @section('after-styles')
    {{ Html::style('core/app-assets/css-'.visual().'/pages/app-todo.css') }}
    {{ Html::style('core/app-assets/css-'.visual().'/plugins/forms/checkboxes-radios.css') }}
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection --}}
@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
    {{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
            branchSelect: {
                allowClear: true,
                ajax: {
                    url: "{{ route('biller.branches.select') }}",
                    dataType: 'json',
                    type: 'POST',
                    data: ({
                        term
                    }) => ({
                        search: term,
                        customer_id: $("#customerFilter").val()
                    }),
                    processResults: data => {
                        return {
                            results: data.map(v => ({
                                text: v.name,
                                id: v.id
                            }))
                        }
                    },
                }
            },
            quoteSelect: {
                allowClear: true,
                ajax: {
                    url: "{{ route('biller.projects.quotes_select') }}",
                    dataType: 'json',
                    type: 'POST',
                    data: ({
                        term
                    }) => ({
                        search: term,
                        customer_id: $("#person").val(),
                        branch_id: $("#branch_id").val()
                    }),
                    processResults: data => {
                        return {
                            results: data.map(v => ({
                                text: v.name,
                                id: v.id
                            }))
                        }
                    },
                }
            }
        };

        const Index = {
            startDate: '',
            endDate: '',

            init() {
                $.ajaxSetup(config.ajax);
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $("#submit-data_project").on("click", Index.onSubmitProject);
                $("#projectStatus").change(Index.onChangeStatus);
                $("#customerFilter").select2({
                    allowClear: true
                }).change(Index.onChangeCustomer);
                $("#branchFilter").select2(config.branchSelect).change(Index.onChangeBranch);
                $('#AddProjectModal').on('shown.bs.modal', Index.onShownModal);
                $(document).on('click', '.status', Index.onStatusClick);
                $('#search').click(Index.onClickSearch);
                $('#healthAndSafetyTable td').click(Index.getDay);
                Index.drawDataTable();
            },

            getDay() {
                $.ajax({
                    url: "{{ route('biller.day.incidents') }}",
                    type: 'POST',
                    data: {
                        day: $(this).text(),
                    },
                    success: (data) => {
                        console.log(data);
                        $('#incidentsTable tbody').html('');
                        $('#AddProjectModal').modal('show');
                        $('#data_project').text(`Incidents for ${data[1]}`);
                        if (data[0].length == 0) {
                            $('#incidentsTable tbody').append(
                                    `<tr>
                                        <td colspan="6">This day has no incidents.</td>
                                    </tr>
                                    `
                                );
                        } else {
                            data[0].forEach(function(v, i) {
                                $('#incidentsTable tbody').append(
                                    `<tr>
                                        <td>${i}</td>
                                        <td>${v.customer.company}</td>
                                        <td>${v.project.name}</td>
                                        <td>${v.incident_desc}</td>
                                        <td>${v.status}</td>
                                        <td>${v.timing}</td>
                                    </tr>
                                    `
                                );
                            });
                        }
                    }
                });

            },

            onClickSearch() {
                Index.startDate = $('#start_date').val();
                Index.endDate = $('#end_date').val();
                $('#projectsTbl').DataTable().destroy();
                Index.drawDataTable();
            },

            onStatusClick() {
                $('#status_project_id').val($(this).attr('project-id'));
                $('#status').val($(this).attr('data-id'));
                $('#end_note').val($(this).attr('end-note'));
            },

            onSubmitProject() {
                e.preventDefault();
                let form_data = {};
                form_data['form'] = $("#data_form_project").serialize();
                form_data['url'] = $('#action-url').val();
                $('#AddProjectModal').modal('toggle');
                addObject(form_data, true);
            },

            onChangeCustomer() {
                $("#branchFilter option:not(:eq(0))").remove();
                $('#projectsTbl').DataTable().destroy();
                Index.drawDataTable();
            },

            onChangeBranch() {
                $('#projectsTbl').DataTable().destroy();
                Index.drawDataTable();
            },

            onChangeStatus() {
                $('#projectsTbl').DataTable().destroy();
                Index.drawDataTable();
            },

            onShownModal() {
                $('[data-toggle="datepicker"]').datepicker({
                    autoHide: true,
                    format: "{{ config('core.user_date_format') }}"
                });
                $('.from_date').datepicker(config.date).datepicker('setDate', new Date());
                $('.to_date').datepicker(config.date).datepicker('setDate',
                    '{{ dateFormat(date('Y-m-d', strtotime('+30 days', strtotime(date('Y-m-d'))))) }}');
                $('#color').colorpicker();
                $("#tags").select2();
                $("#employee").select2();

                const branchConfig = {
                    ...config.branchSelect
                };
                branchConfig.ajax.data = ({
                    term
                }) => ({
                    search: term,
                    customer_id: $('#person').val()
                });
                $("#branch_id").select2(branchConfig);

                $("#person").select2({
                        allowClear: true,
                        dropdownParent: $('#AddProjectModal .modal-body')
                    })
                    .change(function() {
                        $("#branch_id").val('')
                    });

                // attach primary quote
                $("#quotes").select2(config.quoteSelect).change(function() {
                    $('.proj_title').val('');
                    $('.proj_short_descr').val('');
                    let text = $("#quotes option:eq(1)").text();
                    if (text) {
                        text = text.split('-')[2];
                        $('.proj_title').val(text);
                        $('.proj_short_descr').val(text);
                    }
                });
            },

            drawDataTable() {
                $('#projectsTbl').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: "{{ route('biller.projects.get') }}",
                        type: 'POST',
                        data: {
                            customer_id: $("#customerFilter").val(),
                            branch_id: $("#branchFilter").val(),
                            status: $("#projectStatus").val(),
                            start_date: Index.startDate,
                            end_date: Index.endDate,
                        }
                    },
                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        ...['tid', 'main_quote_id', 'name', 'exp_profit_margin', 'priority', 'status',
                            'job_hrs', 'start_date', 'end_date'
                        ].map(v => ({
                            data: v,
                            name: v
                        })),
                        {
                            data: 'actions',
                            name: 'actions',
                            searchable: false,
                            sortable: false
                        }
                    ],
                    columnDefs: [{
                            type: "custom-number-sort",
                            targets: [4]
                        },
                        // { type: "custom-date-sort", targets: [1,6] }
                    ],
                    order: [
                        [0, "desc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                });
            },
        };

        $(Index.init);
    </script>
@endsection
