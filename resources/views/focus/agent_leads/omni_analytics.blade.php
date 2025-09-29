@extends ('core.layouts.app')
@section ('title', 'AI Chat Analytics')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">AI Chat Analytics</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            {{-- <a href="{{ route('biller.agent_leads.index') }}" class="btn btn-info  btn-lighten-2">
                                <i class="fa fa-list-alt"></i> AI Leads
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <!-- summaries -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body p-1">
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="font-weight-bold h5">Chat Bot Users</label>
                                    <p class="h1 font-weight-bold">{{ $botUsersCt }}</p>
                                </div>
                                <div class="col-md-4"><i class="fa fa-users pt-1" style="font-size: 3em" aria-hidden="true"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body p-1">
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="font-weight-bold h5">Human Help Requested</label>
                                    <p class="h1 font-weight-bold">{{ $humanHelpCt }}</p>
                                </div>
                                <div class="col-md-4"><i class="fa fa-user-circle pt-1" style="font-size: 3em" aria-hidden="true"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body p-1">
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="font-weight-bold h5">Incoming Messages</label>
                                    <p class="h1 font-weight-bold">{{ $inMsgCt }}</p>
                                </div>
                                <div class="col-md-4"><i class="fa fa-comments-o" style="font-size: 3em;" aria-hidden="true"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body p-1">
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="font-weight-bold h5">Outgoing Messages</label>
                                    <p class="h1 font-weight-bold">{{ $outMsgCt }}</p>
                                </div>
                                <div class="col-md-4"><i class="fa fa-comments" style="font-size: 3em;" aria-hidden="true"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end summaries -->

            <!-- charts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <p class="font-weight-bold h5">Users Report ({{ $botUsersCt }} of {{ $botUsersCt }})</p>
                                <canvas id="usersReportChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <p class="font-weight-bold h5">Platform Specific Users</p>
                                <canvas id="usersBySourceChat" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <p class="font-weight-bold h5">Users Daily Report</p>
                                <canvas id="usersDailyReportChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <p class="font-weight-bold h5">Top 5 Messages</p>
                            <div class="d-flex justify-content-between bg-info px-1 pt-1">
                                <p class="font-weight-bold">MESSAGE</p>
                                <p class="font-weight-bold">COUNT</p>
                            </div>
                            <hr class="mb-0 pb-0" style="border-bottom: 1px solid #ccc">
                            @foreach ($topMsgCt as $row)
                                <div class="d-flex justify-content-between px-1 pt-1" style="border-bottom: 1px solid #ccc">
                                    <p class="h5">{{ $row->message }}</p>
                                    <p class="font-weight-bold h5">{{ $row->count }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>   
            <!-- end charts -->

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <p class="font-weight-bold h5">Top 5 Stories</p>
                            <div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <p class="font-weight-bold h5">Top 5 CTAs</p>
                            <div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>   
        </div>
    </div>
@endsection

@section('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
{{ Html::script('core/app-assets/vendors/js/charts/raphael-min.js') }}
{{ Html::script('core/app-assets/vendors/js/charts/morris.min.js') }}
<script>
    $(() => {
        Chart.register(ChartDataLabels);

        // Users Report Chart  
        const usersByDate = @json($usersByDate);
        var chart1 = new Chart($("#usersReportChart"), {
            type: 'line',
            data: {
                labels: usersByDate.map(v => v.date),
                datasets: [
                    {
                        label: "Users",
                        data: usersByDate.map(v => v.count),
                        tension: 0.1,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192)',
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
                responsive: true,
                plugins:{
                    datalabels: {
                        display: true,
                        color: 'black',
                        anchor: 'top',
                        align: 'right',
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

        // Users By Source Chat
        const usersByType = @json($usersByType);
        var chart2 = new Chart($("#usersBySourceChat"), {
            type: 'doughnut',
            data: {
                labels: usersByType.map(v => v.user_type),
                datasets: [
                    {
                        label: "Users",
                        data: usersByType.map(v => v.count),
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)'
                        ],
                        hoverOffset: 4
                    },
                ]
            },
            options: {
                responsive: true,
                plugins:{
                    datalabels: {
                        display: true,
                        color: 'black',
                        anchor: 'top',
                        align: 'right',
                        labels: {
                            title: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        formatter: (value, context) => {
                            const total = 5+10+51;
                            return accounting.formatNumber(value/total*100, 1, ",") + '%';
                        },
                    }
                }
            }
        });

        // Users Daily Report Chart
        const usersByDay = @json($usersByDay);;
        var chart3 = new Chart($("#usersDailyReportChart"), {
            type: 'bar',
            data: {
                labels: usersByDay.map(v => v.dayname),
                datasets: [
                    {
                        label: "Users",
                        data: usersByDay.map(v => v.count),
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)'
                        ],
                        hoverOffset: 4
                    },
                ]
            },
            options: {
                scales: {
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'No. of Conversations'
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
                plugins:{
                    datalabels: {
                        display: true,
                        color: 'black',
                        anchor: 'top',
                        align: 'top',
                        labels: {
                            title: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        formatter: (value, context) => {
                            return value;
                            const total = 5+10+51;
                            return accounting.formatNumber(value/total*100, 1, ",") + '%';
                        },
                    }
                }
            }
        });
    });
</script>
@endsection
