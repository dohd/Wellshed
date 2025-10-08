@extends ('core.layouts.app')
@section('title', trans('general.dashboard_title') . ' | ' . config('core.cname'))
@section('content')
<head>
    <!-- Latest CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
</head>

<!-- BEGIN: Content-->
<div class="">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="col-12 ">
                <div class="">
                    @php
                        $welcomeMsg = \App\Models\documentBoard\WelcomeMessage::first();
                    @endphp
                    <p>
                        <span style="font-size: 24px"> Welcome to your business dashboard </span> <br>
                        @if($welcomeMsg)
                            Here's <span style="font-size: 18px; text-underline: #0a721b"><a href="{{ route('biller.company-notice-board.central') }}" class="link-hover">the latest information</a></span> from your management.
                        @endif
                    </p>
                </div>

                @if(!$hasDashboardPerms)
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <div style="padding: 20px; background-color: white; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); text-align: center; border-radius: 8px;">
                            <p style="font-size: 30px; color: darkblue;">
                                You Have Not Been Assigned any Dashboard Visualizations Viewing Privileges
                                <br>
                                <span style="font-size: 15px;">If this is a mistake, contact your account Administrator </span>
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="content-body">
            <div class="row match-height height-70-per">
                <!-- New Customers Chart -->
                @permission('dashboard-visualizations-new-customers')
                <div class="col-12 col-lg-6">
                    <div class="card radius-8">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="bar-chart-container">
                                    <p class="ml-6 text-lg-left"> {{ $newCustomersMetrics['title'] }} </p>
                                    <canvas id="new-customers-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth

                <!-- Product Categories Bar Chart -->
                @permission('dashboard-visualizations-product-category-value')
                <div class="col-12">
                    <div class="card radius-8">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card radius-8">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="bar-chart-container">
                                                <p class="ml-6" id="productCategoriesBarTitle" style="font-size: 20px"> Inventory Value by Product Category </p>
                                                <canvas id="product-categories-bar" style="max-height: 400px;"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth    
            </div>

            <!-- Recent & Monthly Sales -->
            <div class="row match-height">
                <!-- Stock Alerts -->
                @permission('dashboard-visualizations-stock-alerts')
                    <div class="col-xl-12 col-lg-12">
                        <div class="card">
                            <div class="card-header ">
                                <h4 class="card-title">{{ trans('dashboard.stock_alert') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive height-500">
                                    <table class="table table-hover mb-1">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th width="30%">Product Name</th>
                                            <th width="15%">Product Code</th>
                                            <th width="10%">Qty</th>
                                            <th width="10%">UoM</th>
                                            <th width="10%">Qty Alert</th>
                                            <th width="25%">Warehouse</th>
                                            <th width="10%">MoQ</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data['stock_alert'] as $i => $productvariation)
                                            <tr>
                                                <td class="text-truncate">{{ $i+1 }}</td>
                                                <td class="text-truncate">
                                                    <a href="{{ route('biller.products.show', $productvariation->parent_id) }}">
                                                        {{ $productvariation->name }}
                                                    </a>
                                                </td>
                                                <td>{{$productvariation->code}}</td>
                                                <td class="text-truncate"><span class="badge badge-danger float-xs-right">
                                                    Qty: {{ +$productvariation->qty }}
                                                </span></td>
                                                <td>{{ @$productvariation->product->unit->code }}  </td>
                                                <td class="text-truncate">{{ +$productvariation->alert }} </td>
                                                <td class="text-truncate"><small class="purple"> <iclass="ft-map-pin"></i>{{ @$productvariation->warehouse->title }}</small></td>
                                                <td class="text-truncate">{{ numberFormat($productvariation->moq) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>            
        </div>
    </div>
</div>
<input type="hidden" id="loader_url" value="{{ route('biller.tasks.load') }}">
<input type="hidden" id="mini_dash" value="{{ route('biller.mini_dash') }}">
<!-- END: Content-->
{{-- @include('focus.projects.modal.task_view') --}}
@endsection

@section('after-styles')
{!! Html::style('core/app-assets/vendors/css/charts/morris.css') !!}
<style>
    div.scroll {
        background-color: #fed9ff;
        width: 600px;
        height: 150px;
        overflow-x: hidden;
        overflow-y: auto;
        text-align: center;
        padding: 20px;
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
    .grid-container-2 {
        display: grid;
        gap: 20px;
        grid-template-columns: auto auto;
    }

    .link-hover {

        color: #68D835; /* Uses default text color */
        transition: color 0.8s ease; /* Smooth transition */
    }

    .link-hover:hover {
        color: #001180; /* Changes to green on hover */
    }
</style>
@endsection

@section('extra-scripts')
{{ Html::script('core/app-assets/vendors/js/charts/raphael-min.js') }}
{{ Html::script('core/app-assets/vendors/js/charts/morris.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}

<script type="text/javascript">
    Chart.register(ChartDataLabels);

    //New Customers Chart
    $(function(){
        let newCustomerMetrics = @json($newCustomersMetrics);
        //get the pie chart canvas
        var ctx = $("#new-customers-chart");
        //create Pie Chart class object
        var chart1 = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: (newCustomerMetrics.months),
                datasets: [
                    {
                        label: "New Customers",
                        data: newCustomerMetrics.newCustomers,
                        backgroundColor: 'rgb(102,223,61, 0.3)',
                        borderColor: 'rgba(75,192,77)',
                        borderWidth: 1,
                    },
                ]
            },
            options: {
                datasets : {
                    bar : {
                        borderRadius : 6,
                        borderSkipped : 'bottom',
                    }
                },
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'New Customers'
                        },
                        ticks: {
                            beginAtZero:true,
                            userCallback: function(value, index, values) {
                                value = value.toString();
                                value = value.split(/(?=(?:...)*$)/);
                                value = value.join(',');
                                return value;
                            }
                        }
                    }]
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
                plugins:{
                    datalabels: {
                        color: 'black',
                        anchor: 'top',
                        labels: {
                            title: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        padding:{
                            bottom: 20,
                        }
                    }
                }
            }
        });
    });

    let productCategoriesBarChart = null;
    function drawProductCategoriesBar() {
        var ctx = $("#product-categories-bar");
        let chartData = @json($productCategoriesMetrics);
        let barColors = [
            'rgba(255, 99, 132)',  // Pink
            'rgba(54, 162, 235)',  // Blue
            'rgba(255, 206, 86)',  // Yellow
            'rgba(75, 192, 192)',  // Teal
            'rgba(153, 102, 255)', // Purple
            'rgba(255, 159, 64)',  // Orange
            'rgba(255, 99, 71)',   // Red
            'rgba(128, 0, 128)',   // Dark Purple
            'rgba(0, 128, 128)',   // Dark Teal
            'rgba(128, 128, 0)',   // Olive
            'rgba(0, 0, 128)',     // Navy
            'rgba(255, 105, 180)', // Hot Pink
            'rgba(0, 255, 255)',   // Cyan
            'rgba(255, 165, 0)',   // Orange
            'rgba(0, 255, 0)',     // Lime
            'rgba(255, 0, 255)',   // Magenta
            'rgba(192, 192, 192)', // Silver
            'rgba(255, 20, 147)',  // Deep Pink
            'rgba(0, 0, 255)',     // Blue
            'rgba(139, 69, 19)',   // Saddle Brown
            'rgba(255, 0, 0)',     // Red
            'rgba(255, 69, 0)',    // Red-Orange
            'rgba(0, 128, 0)',     // Green
            'rgba(0, 0, 139)',     // Dark Blue
            'rgba(106, 90, 205)',  // Slate Blue
            'rgba(255, 215, 0)',   // Gold
            'rgba(0, 250, 154)',   // Medium Spring Green
            'rgba(0, 191, 255)',   // Deep Sky Blue
            'rgba(147, 112, 219)', // Medium Purple
            'rgba(255, 140, 0)',   // Dark Orange
            'rgba(70, 130, 180)',  // Steel Blue
            'rgba(32, 178, 170)',  // Light Sea Green
            'rgba(255, 192, 203)', // Pink
            'rgba(128, 128, 128)', // Gray
            'rgba(245, 222, 179)', // Wheat
            'rgba(139, 0, 139)',   // Dark Magenta
            'rgba(255, 228, 196)', // Bisque
            'rgba(75, 0, 130)',    // Indigo
            'rgba(255, 240, 245)', // Lavender Blush
            'rgba(255, 69, 150)',  // Light Coral
            'rgba(60, 179, 113)',  // Medium Sea Green
            'rgba(186, 85, 211)',  // Medium Orchid
            'rgba(186, 85, 211)',  // Medium Orchid
            'rgba(255, 105, 180)', // Hot Pink
            'rgba(124, 252, 0)',   // Lawn Green
            'rgba(255, 20, 147)',  // Deep Pink
            'rgba(0, 255, 0)',     // Lime
            'rgba(0, 255, 255)'    // Cyan
        ];
        let chartData2 = {
            "categories": [
                "Sauna Steam Accessories",
                "Freon Gas",
                "AirCon Accessories",
                "HVAC Equipments",
                "Duct Work Accessories",
                "Labour Rates",
                "Raised Flooring Works",
                "Refrigeration Units",
                "Cooling Towers",
                "Heating Units",
                "Ventilation Systems",
                "Thermostats",
                "Insulation Materials",
                "Pipe Insulation",
                "Air Filters",
                "Exhaust Fans",
                "Humidifiers",
                "Dehumidifiers",
                "Air Quality Sensors",
                "Heat Exchangers",
                "Energy Recovery Ventilators",
                "UV Lights",
                "Air Purifiers",
                "Chillers",
                "Compressors",
                "Condensers",
                "Evaporators",
                "Fan Coils",
                "Heat Pumps",
                "Air Curtains",
                "Water Treatment Equipment",
                "Cooling Coils",
                "Boilers",
                "Solar Panels",
                "Wind Turbines",
                "Energy Meters",
                "Zone Control Systems",
                "Dampers",
                "Plenum Chambers",
                "Air Handling Units",
                "Expansion Valves",
                "Blower Motors",
                "Piping Systems",
                "Control Panels",
                "Temperature Sensors"
            ],
            "values": [
                20655.17,
                156977.6,
                1587413.97,
                2714562.88,
                288655,
                9300,
                0,
                55000.5,
                75000.75,
                125000.89,
                35000.23,
                45000.67,
                24000.45,
                89000.12,
                120000.78,
                67000.54,
                83000.9,
                94000.3,
                41000.7,
                31000.8,
                195000.25,
                215000.6,
                245000.35,
                185000.9,
                225000.78,
                245000.56,
                285000.34,
                300000.65,
                340000.12,
                370000.98,
                390000.11,
                430000.99,
                470000.42,
                520000.8,
                560000.15,
                590000.2,
                610000.35,
                650000.4,
                700000.5,
                740000.75,
                780000.88,
                830000.91,
                870000.4,
                920000.3,
                960000.25
            ]
        };
        // Create a new chart instance
        productCategoriesBarChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ["Product Category"], // Single generic label on x-axis
                datasets: chartData.categories.map((category, index) => ({
                    label: category,
                    data: [chartData.values[index]],
                    backgroundColor: barColors[index],
                    borderRadius: {
                        topLeft: 6,
                        topRight: 6,
                        bottomLeft: 0,
                        bottomRight: 0
                    },
                    borderSkipped: 'bottom'
                }))
            },
            options: {
                scales: {
                    x: {
                        display: true,
                        // title: {
                        //     display: false,
                        //     text: 'Product Categories',
                        // }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Value'
                        },
                        ticks: {
                            beginAtZero: true,
                            userCallback: function(value, index, values) {
                                value = value.toString();
                                value = value.split(/(?=(?:...)*$)/);
                                value = value.join(',');
                                return value;
                            }
                        }
                    }
                },
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'nearest',  // Ensures tooltips appear only for the hovered bar
                        callbacks: {
                            label: function(tooltipItem) {
                                const label = tooltipItem.dataset.label || '';
                                const value = tooltipItem.raw.toLocaleString();
                                return `${label}: ${value}`;
                            }
                        }
                    },
                    datalabels: {
                        display: false,
                        color: 'black',
                        anchor: 'top',
                        labels: {
                            title: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        padding: {
                            bottom: 20,
                        }
                    }
                }
            }
        });
    }

    $('a[data-toggle=tab').on('shown.bs.tab', function(e) {
        window.dispatchEvent(new Event('resize'));
    });
</script>
@endsection
