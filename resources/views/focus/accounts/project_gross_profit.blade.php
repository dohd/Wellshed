@extends ('core.layouts.app')
@section ('title', trans('labels.backend.accounts.management'))

@section('content')
    <head>
        <!-- Latest CSS -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    </head>
    <div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Gross Profit</h4>
        </div>
    </div>
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                           role="tab" aria-selected="true"><span>Tabular Report</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2"
                           role="tab" aria-selected="false"><span>Charts</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content px-1 pt-1">
                    <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                        <!-- Table Filters -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="d-flex">
                                                    <label for="invDateFrom" style="min-width: 120px;">Invoice Period</label>
                                                    <input type="text" name="invoice_start_date" id="invStartDate" placeholder="{{ date('01-m-Y') }}" class="form-control form-control-sm datepicker mr-1">
                                                    <input type="text" name="invoice_end_date" id="invEndDate" placeholder="{{ date('d-m-Y') }}" class="form-control form-control-sm datepicker">
                                                </div>
                                            </div>
                                        </div>   
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="d-flex">
                                                    <label for="prjDateFrom" style="min-width: 120px;">Project Period</label>
                                                    <input type="text" name="prj_start_date" id="prjStartDate" placeholder="{{ date('01-m-Y') }}" class="form-control form-control-sm datepicker mr-1">
                                                    <input type="text" name="prj_end_date" id="prjEndDate" placeholder="{{ date('d-m-Y') }}" class="form-control form-control-sm datepicker">
                                                </div>
                                            </div>                                            
                                        </div>
                                        <hr> 
                                        <div class="row">
                                            <div class="col-md-2">
                                                <select name="status" id="status" class="custom-select">
                                                    <option value="">-- Project Status --</option>
                                                    @foreach (['active', 'complete','expense','verified','invoiced'] as $val)
                                                        <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-control select2" id="customerFilter" data-placeholder="Search Customer">
                                                    <option value=""></option>
                                                    @foreach ($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-control select2" id="branchFilter" data-placeholder="Search Branch">
                                                    <option value=""></option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" id="filterBtn" class="btn btn-info"><i class="fa fa-filter" aria-hidden="true"></i> Filter</button>
                                            </div>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="d-flex">
                                                        <label style="min-width: 100px;">Gross Profit</label>
                                                        <input type="text" name="amount_total" style="max-width: 150px;" class="form-control form-control-sm" id="amount_total" readonly>
                                                    </div>                                                    
                                                </div>
                                            </div>
                                            <hr>
                                            <table id="projectsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Project No</th>
                                                    <th>Client-Branch</th>
                                                    <th>Title</th>
                                                    <th>QT/PI Amt.</th>
                                                    <th>Verification</th>
                                                    <th>Invoice Date</th>
                                                    <th>Invoice Items</th>
                                                    <th>Invoice Amt.</th>
                                                    <th width="20%">Exp. Item</th>
                                                    <th width="20%">Ledger Account</th>
                                                    <th>Expense Amt.</th>
                                                    <th>G.P</th>
                                                    <th>%P</th>
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

                    <!-- Charts Tab Pane -->
                    <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                        <div class="card radius-8">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-3">
                                            <label for="barCustomerFilter">Customer</label>
                                            <select class="form-control box-size mb-2 filter bar-filter select2" id="barCustomerFilter" name="barCustomerFilter" required data-placeholder="Filter by Customer"
                                                    aria-label="Select Customer">
                                                <option value="">Filter By Customer</option>
                                                @php
                                                    $customers = \App\Models\customer\Customer::orderBy('company')->get();
                                                @endphp
                                                @foreach ($customers as $s)
                                                    <option value="{{ $s['id'] }}">
                                                        {{ $s['company'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-3">
                                            <label for="barStatusFilter">Status</label>
                                            <select class="form-control box-size mb-2 filter bar-filter" id="barStatusFilter" name="barStatusFilter" required data-placeholder="Filter by Status"
                                                    aria-label="Select Department">
                                                <option value="">Filter By Status</option>
                                                @php
                                                    $statuses = \App\Models\misc\Misc::where('section', 2)->orderBy('name')->get();
                                                @endphp
                                                @foreach ($statuses as $s)
                                                    <option value="{{ $s['id'] }}">
                                                        {{ $s['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-10 col-lg-3">
                                            <label for="barFromDateFilter" >Filter from Date</label>
                                            <input type="date" id="barFromDateFilter" class="form-control box-size filter bar-filter" >
                                        </div>
                                        <div class="col-10 col-lg-3">
                                            <label for="barToDateFilter" >Filter to Date</label>
                                            <input type="date" id="barToDateFilter" class="form-control box-size filter bar-filter" >
                                        </div>
                                        <div class="col-2">
                                            <button id="clearBarFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                        </div>
                                    </div>
                                    <div class="card radius-8">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="bar-chart-container">
                                                    <p class="ml-6"> Project Gross Profit Bar Chart Summary  </p>
                                                    <canvas id="gpBarChart" style="max-height: 650px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card radius-8">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-3">
                                            <label for="pieCustomerFilter">Customer</label>
                                            <select class="form-control box-size mb-2 select2 filter pie-filter" id="pieCustomerFilter" name="pieCustomerFilter" required data-placeholder="Filter by Customer"
                                                    aria-label="Select Customer">
                                                <option value="">Filter By Customer</option>
                                                @php
                                                    $customers = \App\Models\customer\Customer::orderBy('company')->get();
                                                @endphp
                                                @foreach ($customers as $s)
                                                    <option value="{{ $s['id'] }}">
                                                        {{ $s['company'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-3">
                                            <label for="pieStatusFilter">Status</label>
                                            <select class="form-control box-size mb-2 select2 filter pie-filter" id="pieStatusFilter" name="pieStatusFilter" required data-placeholder="Filter by Status"
                                                    aria-label="Select Department">
                                                <option value="">Filter By Status</option>
                                                @php
                                                    $statuses = \App\Models\misc\Misc::where('section', 2)->orderBy('name')->get();
                                                @endphp
                                                @foreach ($statuses as $s)
                                                    <option value="{{ $s['id'] }}">
                                                        {{ $s['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-10 col-lg-3">
                                            <label for="pieFromDateFilter" >Filter from Date</label>
                                            <input type="date" id="pieFromDateFilter" class="form-control box-size filter pie-filter" >
                                        </div>
                                        <div class="col-10 col-lg-3">
                                            <label for="pieToDateFilter" >Filter to Date</label>
                                            <input type="date" id="pieToDateFilter" class="form-control box-size filter pie-filter" >
                                        </div>
                                        <div class="col-2">
                                            <button id="clearPieFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                        </div>
                                    </div>
                                    <div class="card radius-8">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="pie-chart-container">
                                                    <p class="ml-6"> Project Gross Profit Pie Chart Summary  </p>
                                                    <canvas id="gpPieChart" style="max-height: 650px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customerFilter").val()}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        },
    };

    let ajaxRequest = null;
    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);
            $('.select2').select2({ allowClear: true });
            $("#branchFilter").select2(config.branchSelect);
            $("#customerFilter").select2({allowClear: true})
            .change(Index.onChangeCustomer);
            
            setTimeout(() => Index.drawDataTable(), 500);
            $('#filterBtn').click(Index.filterBtnClick);
        },
        
        onChangeCustomer() {
            $("#branchFilter option:not(:eq(0))").remove();
        },

        filterBtnClick() {
            $('#projectsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            let table = $('#projectsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                ajax: function (data, callback, settings) {
                    if (ajaxRequest) ajaxRequest.abort();
                    ajaxRequest = $.ajax({
                        url: "{{ route('biller.accounts.get_project_gross_profit') }}",
                        method: 'POST',
                        data: {
                            customer_id: $("#customerFilter").val(),
                            branch_id: $("#branchFilter").val(),
                            status: $("#status").val(),
                            prj_start_date: $("#prjStartDate").val(),
                            prj_end_date: $("#prjEndDate").val(),
                            inv_start_date: $("#invStartDate").val(),
                            inv_end_date: $("#invEndDate").val(),
                        },
                        success: function (response) {
                            let {data} = response;
                            if (data.length) {
                                $('#amount_total').val('');
                                $('#amount_total').val(data[data.length-1].total_profit); 
                            }
                            // Populate DataTable
                            callback(response); 
                        },
                        error: function (xhr, status, error) {
                            if (status === 'abort') {
                                console.log('Previous DataTable AJAX request aborted.');
                            } else {
                                console.error('DataTable load error:', error);
                            }
                        }
                    });
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'tid',
                        name: 'tid'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'quote_amount',
                        name: 'quote_amount'
                    },
                    {
                        data: 'verify_date',
                        name: 'verify_date'
                    },
                    {
                        data: 'invoice_date',
                        name: 'invoice_date'
                    },
                    {
                        data: 'sale_items',
                        name: 'sale_items'
                    },
                    {
                        data: 'income',
                        name: 'income'
                    },
                    {
                        data: 'expense_item',
                        name: 'expense_item'
                    },
                    {
                        data: 'ledgers',
                        name: 'ledgers'
                    },
                    {
                        data: 'expense',
                        name: 'expense'
                    },
                    {
                        data: 'gross_profit',
                        name: 'gross_profit'
                    },
                    {
                        data: 'percent_profit',
                        name: 'percent_profit'
                    },
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [7, 8, 9, 10] },
                    { type: "custom-date-sort", targets: [5, 6] }
                ],
                order: [
                    [0, "desc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(Index.init);
    
    Chart.register(ChartDataLabels);

    const fetchBarChartData = () => {


        const barStatusFilter = $('#barStatusFilter');
        const barFromDateFilter = $('#barFromDateFilter');
        const barToDateFilter = $('#barToDateFilter');
        const barCustomerFilter = $('#barCustomerFilter');

        console.table({
            status: barStatusFilter.val(),
            fromDate: barFromDateFilter.val(),
            toDate: barToDateFilter.val(),
            customer: barCustomerFilter.val(),
        });

        return $.ajax({
            url: '{{ route("biller.get-gp-chart-data") }}',
            type: 'GET',
            data: {
                status: barStatusFilter.val(),
                fromDate: barFromDateFilter.val(),
                toDate: barToDateFilter.val(),
                customer: barCustomerFilter.val(),
            },

            success: function (response) {

                console.table(response);
                return response;
            },

            error: function (xhr, status, error) {

                const errorData = xhr.responseJSON;
                if (errorData) {
                    console.table({
                        message: errorData.message,
                        code: errorData.code,
                        file: errorData.file,
                        line: errorData.line
                    });
                } else {
                    console.error('Error fetching data:', error);
                }
            }
        });
    };


    let gpBarChartInstance = null;
    async function drawGpBarChart () {

        var ctx = $("#gpBarChart");

        let chartData = await fetchBarChartData();

        if (!chartData) {
            console.error("No data available to draw the chart.");
            return;
        }

        // Destroy the existing chart instance if it exists
        if (gpBarChartInstance) {
            gpBarChartInstance.destroy();
        }


        // Create a new chart instance
        gpBarChartInstance = new Chart(ctx, {
            type: 'bar',

            data: {
                labels: ['Projects Summary'],
                datasets: [
                    {
                        label: "Income",
                        data: chartData.income,
                        // tension: 0.1,
                        backgroundColor: 'rgba(54, 162, 235)',
                        borderColor: 'rgba(54, 162, 235)',
                        // type: 'line',
                        order: 1
                    },
                    {
                        label: "Expense",
                        data: chartData.expense,
                        tension: 0.1,
                        backgroundColor: 'rgba(255, 99, 132)',
                        borderColor: 'rgba(255, 99, 132)',
                        order: 2
                    },
                    {
                        label: chartData.profit > 0 ? "Profit" : "Loss",
                        data: chartData.profit,
                        tension: 0.1,
                        backgroundColor: chartData.profit > 0 ? 'rgb(82,178,56)' : 'red',
                        borderColor: chartData.profit > 0 ? 'rgb(82,178,56)' : 'red',
                        order: 3
                    },
                ]
            },
            options: {
                datasets: {
                    bar: {
                        borderRadius: 6,
                        borderSkipped: 'bottom',
                    }
                },
                scales: {
                    x: {  // Use 'x' instead of 'xAxes'
                        title: {  // Use 'title' instead of 'scaleLabel'
                            display: false,
                            text: 'Quote Number'
                        }
                    },
                    y: {  // Use 'y' instead of 'yAxes'
                        title: {  // Use 'title' instead of 'scaleLabel'
                            display: true,
                            text: 'Value'
                        },
                        ticks: {
                            beginAtZero: true,
                            callback: function (value, index, values) {  // Use 'callback' instead of 'userCallback'
                                value = value.toString();
                                value = value.split(/(?=(?:...)*$)/);
                                value = value.join(',');
                                return value;
                            }
                        }
                    }
                },
                tooltips: {
                    enabled: true,
                    mode: 'single',
                    callbacks: {
                        title: function (tooltipItems, data) {
                            //Return value for title
                            return tooltipItems[0].xLabel;
                        },
                        label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                            //Return value for label
                            return tooltipItems.yLabel;
                        }
                    }
                },
                responsive: true,
                transitions: {
                    show: {
                        animations: {
                            x: {
                                from: 0
                            },
                            y: {
                                from: 0
                            }
                        }
                    },
                    hide: {
                        animations: {
                            x: {
                                to: 0
                            },
                            y: {
                                to: 0
                            }
                        }
                    }
                },
                plugins: {
                    datalabels: {
                        display: true,
                        color: 'black',
                        anchor: 'top',
                        align: 'center',
                        labels: {
                            title: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        formatter: (value, context) => {
                            // Use Intl.NumberFormat to format the value with commas
                            return new Intl.NumberFormat('en-US').format(value);
                        },
                    }
                }
            }
        });


    }



    const fetchPieChartData = () => {


        const pieStatusFilter = $('#pieStatusFilter');
        const pieFromDateFilter = $('#pieFromDateFilter');
        const pieToDateFilter = $('#pieToDateFilter');
        const pieCustomerFilter = $('#pieCustomerFilter');


        console.table({
            status: pieStatusFilter.val(),
            fromDate: pieFromDateFilter.val(),
            toDate: pieToDateFilter.val(),
            customer: pieCustomerFilter.val(),
        });

        return $.ajax({
            url: '{{ route("biller.get-gp-chart-data") }}',
            type: 'GET',
            data: {
                status: pieStatusFilter.val(),
                fromDate: pieFromDateFilter.val(),
                toDate: pieToDateFilter.val(),
                customer: pieCustomerFilter.val(),
            },

            success: function (response) {

                console.table(response);
                return response;
            },

            error: function (xhr, status, error) {

                const errorData = xhr.responseJSON;
                if (errorData) {
                    console.table({
                        message: errorData.message,
                        code: errorData.code,
                        file: errorData.file,
                        line: errorData.line
                    });
                } else {
                    console.error('Error fetching data:', error);
                }
            }
        });
    };


    let gpPieChartInstance = null;
    async function drawGpPieChart () {

        var ctx = $("#gpPieChart");

        let chartData = await fetchPieChartData();

        if (!chartData) {
            console.error("No data available to draw the chart.");
            return;
        }

        // Destroy the existing chart instance if it exists
        if (gpPieChartInstance) {
            gpPieChartInstance.destroy();
        }

        let profit = 0;
        let loss = 0;

        if (chartData.profit > 0) profit = Math.abs(chartData.profit);
        else loss = Math.abs(chartData.profit);


        // Create a new chart instance
        gpPieChartInstance = new Chart(ctx, {
            type: 'pie',

            data: {
                labels: ["Income", 'Expense', 'Profit', 'Loss'],
                datasets: [
                    {
                        data: [chartData.income, chartData.expense, profit, loss],
                        backgroundColor: ['rgba(54, 162, 235)', 'rgba(255, 99, 132)', 'rgb(82,178,56)', 'red'],
                    },
                ]
            },
            options: {
                tooltips: {
                    enabled: true,
                    mode: 'single',
                    callbacks: {
                        title: function (tooltipItems, data) {
                            //Return value for title
                            return tooltipItems[0].xLabel;
                        },
                        label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                            //Return value for label
                            return tooltipItems.yLabel;
                        }
                    }
                },
                responsive: true,
                transitions: {
                    show: {
                        animations: {
                            x: {
                                from: 0
                            },
                            y: {
                                from: 0
                            }
                        }
                    },
                    hide: {
                        animations: {
                            x: {
                                to: 0
                            },
                            y: {
                                to: 0
                            }
                        }
                    }
                },
                plugins: {
                    datalabels: {
                        display: true,
                        color: 'black',
                        anchor: 'top',
                        align: 'center',
                        labels: {
                            title: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        formatter: (value, context) => {

                            console.table({valuee: value});
                            
                            // Get total sum of the dataset
                            const total = parseInt(chartData.income) + parseInt(chartData.expense) + Math.abs(parseInt(chartData.profit));

                            console.table({total: total});

                            // Calculate the percentage
                            const percentage = ((value / total) * 100).toFixed(1);

                            // Format the value with commas
                            const formattedValue = new Intl.NumberFormat('en-US').format(value);

                            // Return formatted value with percentage
                            return `${formattedValue} (${percentage}%)`;
                        }
                    }
                }
            }
        });


    }


    drawGpBarChart();
    drawGpPieChart();


    $('.bar-filter').change(async () => {

        console.log("REDRAWING BAR!!!!!!")

        await drawGpBarChart(); // Redraw the chart with new data
    });

    $('.pie-filter').change(async () => {

        console.log("REDRAWING PIE!!!!!!")

        await drawGpPieChart(); // Redraw the chart with new data
    });


    $('#clearBarFilters').click(() => {


        $('#bar-filter').val('');
        $('#barCustomerFilter').val('').trigger('change');
        $('#barStatusFilter').val('').trigger('change');
    });


    $('#clearPieFilters').click(() => {


        $('#pie-filter').val('');
        $('#pieCustomerFilter').val('').trigger('change');
        $('#pieStatusFilter').val('').trigger('change');
    });



</script>
@endsection