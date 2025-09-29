@extends ('core.layouts.app')

@section ('title', 'Employee Daily Logs')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Employee Daily Logs </h2>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.employeeDailyLog.partials.edl-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">

            @if($isReviewer)
                <div class="col-12 col-lg">
                    <div class="card mb-1 mb-lg-3">
                        <div class="card-content">
                            <div class="media align-items-stretch">
                                <div class="p-2 text-center bg-primary bg-darken-2 radius-8-left">
                                    <i class="icon-note font-large-1 white"></i>
                                </div>
                                <div class="p-2 bg-gradient-x-primary white media-body radius-8-right">
                                    <h5>Yesterday's Filled Logs</h5>
                                    <h5 class="text-bold-500 mb-0" style="font-size: 21px;">
                                        {{$edlMetrics['filledYesterday']}} @if($edlMetrics['filledYesterday'] > 1 ) logs @else log @endif
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg">
                    <div class="card mb-1 mb-lg-3 radius-8">
                        <div class="card-content">
                            <div class="media align-items-stretch">
                                <div class="p-2 text-center bg-success bg-darken-2 radius-8-left">
                                    <i class="icon-clock font-large-1 white"></i>
                                </div>
                                <div class="p-2 bg-gradient-x-success white media-body radius-8-right">
                                    <h5>Yesterday's Logged Key Activities</h5>
                                    <h5 class="text-bold-500 mb-0" style="font-size: 21px;" s>
                                        <!--<i class="ft-arrow-up"></i> <span id="dash_4"><i class="fa fa-spinner spinner"></i></span>-->
                                        {{ $edlMetrics['tasksLoggedYesterday'] }} @if($edlMetrics['tasksLoggedYesterday'] > 1 ) key activities @else key activity @endif
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg">
                    <div class="card mb-1 mb-lg-3 radius-8">
                        <div class="card-content">
                            <div class="media align-items-stretch">
                                <div class="p-2 text-center bg-success bg-darken-2 radius-8-left">
                                    <i class="icon-clock font-large-1 white"></i>
                                </div>
                                <div class="p-2 bg-gradient-x-success white media-body radius-8-right">
                                    <h5>Yesterday's Logged Hours</h5>
                                    <h5 class="text-bold-500 mb-0" style="font-size: 21px;">
                                        <!--<i class="ft-arrow-up"></i> <span id="dash_4"><i class="fa fa-spinner spinner"></i></span>-->
                                        {{ $edlMetrics['hoursLoggedYesterday'] }} @if($edlMetrics['hoursLoggedYesterday'] > 1 ) hours @else hour @endif
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg">
                    <div class="card mb-1 mb-lg-3 radius-8">
                        <div class="card-content">
                            <div class="media align-items-stretch">
                                <div class="p-2 text-center bg-warning bg-darken-2 radius-8-left">
                                    <i class="icon-note font-large-1 white"></i>
                                </div>
                                <div class="p-2 bg-gradient-x-warning white media-body radius-8-right">
                                    <h5>Yesterday's Unfilled Logs</h5>
                                    <h5 class="text-bold-500 mb-0" style="font-size: 21px;">
                                        {{ $edlMetrics['notFilledYesterday'] }} @if($edlMetrics['notFilledYesterday'] > 1 ) logs @else log @endif
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg">
                    <div class="card mb-1 mb-lg-3 radius-8">
                        <div class="card-content">
                            <div class="media align-items-stretch">
                                <div class="p-2 text-center bg-warning bg-darken-2 radius-8-left">
                                    <i class="icon-note font-large-1 white"></i>
                                </div>
                                <div class="p-2 bg-gradient-x-warning white media-body radius-8-right">
                                    <h5>Yesterday's Unreviewed Logs</h5>
                                    <h5 class="text-bold-500 mb-0" style="font-size: 21px;">
                                        {{ $edlMetrics['yesterdayUnreviewedLogs'] }} @if($edlMetrics['yesterdayUnreviewedLogs'] > 1 ) logs @else log @endif
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            @endif


            <div class="col-12">

                <div class="card" style="border-radius: 8px;">

{{--                    <div class="justify-content-end mt-1 mr-1 mb-2">--}}

{{--                        <div class="media float-right mr-2">--}}
{{--                            <a href="{{ route('biller.employee-daily-log.create') }}" class="btn btn-bitbucket" style="border-radius:8px;"> Create Log </a>--}}
{{--                        </div>--}}

{{--                    </div>--}}


                    <div class="row p-1 p-lg-0 ml-lg-2 mt-2">

                        @if($isReviewer)
                            <div class="col-6 mt-1 col-lg-2 mt-lg-0">
                                <label for="employee">Employee</label>
                                <select name="employee" id="employee" class="form-control select2" style="border-radius:8px;">
                                    <option value="">-- Filter by Employee --</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp['id'] }}">{{ $emp['full_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-6 mt-1 col-lg-2 mt-lg-0">
                            <label for="date">Date</label>
                            <select name="date" id="date" class="form-control select2" style="border-radius:8px;">
                                <option value="">-- Filter by Date --</option>
                                @foreach ($edlDates as $val)
                                    <option value="{{ $val }}">{{ (new DateTime($val))->format('D, d M Y') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 mt-1 col-lg-2 mt-lg-0">
                            <label for="month">Month</label>
                            <select name="month" id="month" class="form-control" style="border-radius:8px;">
                                <option value="">-- Filter by Month --</option>
                                @foreach ($months as $mon)
                                    <option value="{{ $mon['value'] }}">{{ $mon['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 mt-1 col-lg-2 mt-lg-0">
                            <label for="year">Year</label>
                            <select name="year" id="year" class="form-control" style="border-radius:8px;">
                                <option value="">-- Filter by Year --</option>
                                @foreach ($edlYears as $yr)
                                    <option value="{{ $yr }}">{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mt-1 col-lg-2 mt-lg-0">
                            <label for="total">Total Hrs</label>                             
                            <input type="text" name="total_hrs" class="form-control" id="total_hrs" readonly>
                        </div>
                        <div class="col-6 mt-1 col-lg-2 mt-lg-0">
                            <label for="total">% Average Performance</label>                             
                            <input type="text" name="avg_performance" class="form-control" id="avg_performance" readonly>
                        </div>

                        <div class="col-4">

                            <button id="clear_filters" class="btn btn-secondary round mt-2" > Clear Filters </button>

                        </div>

{{--                            @if($isReviewer)--}}
{{--                                <div class="col-auto mt-2">--}}
{{--                                    <a href="{{ route('biller.employee-task-subcategories.index') }}" class="btn btn-secondary" style="border-radius:8px;"> Manage Key Activity Subcategories </a>--}}
{{--                                </div>--}}

{{--                                <div class="col-auto mt-2">--}}
{{--                                    <a href="{{ route('biller.edl-subcategory-allocations.index') }}" class="btn btn-foursquare" style="border-radius:8px;"> Allocate Key Activity Subcategories </a>--}}
{{--                                </div>--}}
{{--                            @endif--}}


                    </div>

                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">

                            <table id="edl-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>EDL</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Branch</th>
                                        <th>Employee</th>
                                        <th>Key Activities</th>
                                        <th>Hours</th>
                                        <th>% Average Performance</th>
                                        <th>Rating</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- The Modal -->
                            <div id="deleteModal" class="modal">
                                <div class="modal-content">
                                    <p>Are you sure you want to delete this item?</p>
                                    <button id="confirmDelete">Yes, Delete</button>
                                    <button id="cancelDelete">Cancel</button>
                                </div>
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
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}

<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    $('#date').select2();
    $('#employee').select2();

    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#edl-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.employee-daily-log.index") }}',
                type: 'GET',
                data: {
                    employee: $('#employee').val(),
                    date: $('#date').val(),
                    month: $('#month').val(),
                    year: $('#year').val(),
                },
                dataSrc: ({data}) => {
                    $('#total_hrs').val('');
                    $('#avg_performance').val('');
                    if (data.length) 
                    {
                        $('#total_hrs').val(data[0].total_hours);
                    $('#avg_performance').val(data[0].total_performance);  
                    }                          
                    return data;
                },
            },
            columns: [
                {
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'employee',
                    name: 'employee'
                },
                {
                    data: 'tasks',
                    name: 'tasks'
                },
                {
                    data: 'hours',
                    name: 'hours'
                },
                {
                    data: 'average_percent_performance',
                    name: 'average_percent_performance'
                },
                {
                    data: 'rating',
                    name: 'rating'
                },
                {
                    data: 'remarks',
                    name: 'remarks'
                },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [1, "desc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    $('#employee').change( () => {
        $('#edl-table').DataTable().destroy();
        draw_data();
    })

    $('#date').change( () => {
        $('#edl-table').DataTable().destroy();
        draw_data();
    })

    $('#month').change( () => {
        $('#edl-table').DataTable().destroy();
        draw_data();
    })

    $('#year').change( () => {
        $('#edl-table').DataTable().destroy();
        draw_data();
    })

    $('#clear_filters').click( () => {
        $('#employee').select2().val('').trigger('change');
        $('#date').val('');
        $('#month').val('');
        $('#year').val('');
        $('#edl-table').DataTable().destroy();
        draw_data();
    });



</script>
@endsection

<style>
    /* Styling for the modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.75);
    }

    .radius-8-right {
        border-radius: 0 8px 8px 0;
    }
    .radius-8-left {
        border-radius: 8px 0 0 8px;
    }
    .radius-8 {
        border-radius: 8px;
    }


</style>

