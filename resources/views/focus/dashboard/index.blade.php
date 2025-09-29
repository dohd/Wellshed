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
                                <p
                                    style="font-size: 30px; color: darkblue;"
                                >
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

                    @permission('dashboard-visualizations-recent-projects')
                        <div class="col-12 col-lg-6">
                        <div class="card radius-8">
                            <div class="card-header">
                                <h4 class="card-title">{{ trans('dashboard.recent') }} Projects</h4>
                                <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                                <div class="heading-elements">
                                    <ul class="list-inline mb-0">
                                        <li><a href="{{ route('biller.projects.index') }}" class="btn btn-success btn-sm rounded">Manage Projects</a></li>
                                        <li><a data-action="reload"><i class="icon-reload"></i></a></li>
                                        <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive height-300">
                                    <table class="table table-hover mb-1">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>#Project No.</th>
                                            <th width="5em">Project Name</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Deadline</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($projects as $i => $project)
                                            <tr>
                                                <td class="text-truncate">{{ $i+1 }}</td>
                                                <td class="text-truncate">
                                                    <a href="{{ route('biller.projects.show', $project) }}">{{ gen4tid('PRJ-', $project->tid) }}</a>
                                                </td>
                                                <td class="text-truncate">{{ $project->name }}</td>
                                                <td class="text-truncate">{{ $project->priority }} </td>
                                                <td class="text-truncate">{{ @$project->misc->name }}</td>
                                                <td class="text-truncate">{{ dateFormat($project->end_date) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth



                @permission('dashboard-visualizations-recent-ai-leads')

                    <div class="col-12 col-lg-6">

                        <div class="card radius-8">
                            <div class="card-header">

                                <h4 class="card-title"> A.I Agent Leads From the Past 7 Days </h4>

                            </div>

                            <div class="card-body">
                                <div class="table-responsive height-300">
                                    <table class="table table-hover mb-1">


                                        <thead>
                                            <tr>
                                                <th>Lead Source</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>


                                        <tbody>

                                        <tr>
                                            <td>Total Leads</td>
                                            <td>{{ @$agentLeadsReport['totalCount'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Facebook Leads</td>
                                            <td>{{ @$agentLeadsReport['facebookCount'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Whatsapp Leads</td>
                                            <td>{{ @$agentLeadsReport['whatsappCount'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Instagram leads</td>
                                            <td>{{ @$agentLeadsReport['instagramCount'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Website leads</td>
                                            <td>{{ @$agentLeadsReport['websiteCount'] }}</td>
                                        </tr>

                                        </tbody>


                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>

                @endauth



                @permission('dashboard-visualizations-recent-ai-transcripts')

                    <div class="col-12 col-lg-6">

                        <div class="card radius-8">
                            <div class="card-header">

                                <h4 class="card-title"> A.I Chat Transcripts Summary From the Past 7 Days </h4>

                            </div>

                            <div class="card-body">
                                <div class="table-responsive height-300">
                                    <table class="table table-hover mb-1">


                                        <thead>
                                            <tr>
                                                <td>Chat Source</td>
                                                <td>Count</td>
                                            </tr>
                                        </thead>


                                        <tbody>

                                            <tr>
                                                <td>Total Chats</td>
                                                <td>{{ @$agentChatsReport['totalCount'] }}</td>
                                            </tr>
                                            <tr>
                                                <td>Facebook Chats</td>
                                                <td>{{ @$agentChatsReport['facebookCount'] }}</td>
                                            </tr>
                                            <tr>
                                                <td>Whatsapp Chats</td>
                                                <td>{{ @$agentChatsReport['whatsappCount'] }}</td>
                                            </tr>
                                            <tr>
                                                <td>Instagram Chats</td>
                                                <td>{{ @$agentChatsReport['instagramCount'] }}</td>
                                            </tr>
                                            <tr>
                                                <td>Website Chats</td>
                                                <td>{{ @$agentChatsReport['websiteCount'] }}</td>
                                            </tr>

                                        </tbody>


                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>

                @endauth




                @permission('dashboard-visualizations-recent-quotes')

                    <div class="col-12 col-lg-6">

                        <div class="card radius-8">
                            <div class="card-header">

                                <h4 class="card-title"> Quotes & Proforma Invoices From the Past 7 Days </h4>

                            </div>

                            <div class="card-body">
                                <div class="table-responsive height-300">
                                    <table class="table table-hover mb-1">


                                        <thead>
                                            <tr>
                                                <td>Quote ID</td>
                                                <td>Customer</td>
                                                <td>Branch</td>
                                                <td>Title</td>
                                                <td>Status</td>
                                                <td>Approved by</td>
                                                <td>Date</td>
                                                <td>Created By</td>
                                                <td>Currency</td>
                                                <td>Total</td>
                                                <td>Tax</td>
                                            </tr>
                                        </thead>


                                        <tbody>

                                            @foreach($quotesReport as $quote)
                                                <tr>
                                                    <td>{{ $quote['tid'] }}</td>
                                                    <td>{{ $quote['customer'] }}</td>
                                                    <td>{{ $quote['branch'] }}</td>
                                                    <td>{{ $quote['notes'] }}</td>
                                                    <td>{{ $quote['status'] }}</td>
                                                    <td>{{ $quote['approved_by'] }}</td>
                                                    <td>{{ $quote['date'] }}</td>
                                                    <td>{{ $quote['creator'] }}</td>
                                                    <td>{{ $quote['currency'] }}</td>
                                                    <td>{{ number_format($quote['total'], 2) }}</td>
                                                    <td>{{ number_format($quote['tax'], 2) }}</td>
                                                </tr>
                                            @endforeach

                                        </tbody>

                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>

                @endauth



                @permission('dashboard-visualizations-recent-leads')

                    <div class="col-12 col-lg-6">

                        <div class="card radius-8">
                            <div class="card-header">

                                <h4 class="card-title"> Leads From the Past 7 Days </h4>

                            </div>

                            <div class="card-body">
                                <div class="table-responsive height-300">
                                    <table class="table table-hover mb-1">


                                        <thead>
                                            <tr>
                                                <td>Ticket ID</td>
                                                <td>Title</td>
                                                <td>Status</td>
                                                <td>Client Type</td>
                                                <td>Customer</td>
                                                <td>Branch</td>
                                                <td>Source</td>
                                                <td>Phone</td>
                                                <td>Email</td>
                                                <td>Created By</td>
                                            </tr>
                                        </thead>


                                        <tbody>

                                            @foreach($leadsReport as $lead)
                                                <tr>
                                                    <td>{{ $lead['tid'] }}</td>
                                                    <td>{{ $lead['title'] }}</td>
                                                    <td>{{ $lead['status'] }}</td>
                                                    <td>{{ $lead['client_type'] }}</td>
                                                    <td>{{ $lead['customer'] }}</td>
                                                    <td>{{ $lead['branch'] }}</td>
                                                    <td>{{ $lead['source'] }}</td>
                                                    <td>{{ $lead['client_contact'] }}</td>
                                                    <td>{{ $lead['client_email'] }}</td>
                                                    <td>{{ $lead['creator'] }}</td>
                                                </tr>
                                            @endforeach

                                        </tbody>

                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>

                @endauth





                    <!-- Labour Hours Graph -->
                    @permission('dashboard-visualizations-daily-labour-hours')
                        <div class="col-12 col-lg-6">
                        <div class="card radius-8">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="bar-chart-container">
                                        <p class="ml-6 card-title"> {{ $sevenDayLabourHours['chartTitle'] }} </p>
                                        <canvas id="key-quantities-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth


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


                    <!-- New AI Agent Leads Chart -->
                    @permission('dashboard-visualizations-new-ai-leads')
                        <div class="col-12 col-lg-6">
                        <div class="card radius-8">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="bar-chart-container">
                                        <p class="ml-6"> {{ $newAgentLeadsMetrics['title'] }} </p>
                                        <canvas id="new-ai-agent-leads-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth


                    <!-- Project Statuses Chart -->
                    @permission('dashboard-visualizations-project-distribution-by-status')
                        <div class="col-12 col-lg-6">
                        <div class="card radius-8">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="bar-chart-container">
                                        <p class="ml-6"> {{ 'Project Distribution by Status' }} </p>
                                        <canvas id="project-statuses-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth


                    <!-- Non-Project Class Budget Donut Chart -->
                    @permission('dashboard-visualizations-purchase-class-budget-distribution')
                        <div class="col-12 col-lg-6">

                        <div class="card radius-8">
                            <div class="card-content">
                                <div class="card-body">

                                    <div class="col-12">
                                        <label for="pcb-donut-department">Department</label>
                                        <select class="form-control box-size mb-2" id="pcb-donut-department" name="pcb-donut-department" required data-placeholder="Select a Department"
                                                aria-label="Select Department">
                                            @foreach ($departments as $dep)
                                                <option value="{{ $dep['id'] }}"
                                                        {{--                                                        @if(@$purchaseClassBudget['department'] === $dep['id']) selected @endif--}}
                                                >
                                                    {{ $dep['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="card radius-8">
                                        <div class="card-content">
                                            <div class="card-body">

                                                <div class="bar-chart-container">
                                                    <p class="ml-6" id="pcbDonutTitle">  </p>
                                                    <canvas id="purchase-class-budget-donut" style="max-height: 400px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                    @endauth


                    <!-- Non-Project Class Budget Grouped Chart -->
                    @permission('dashboard-visualizations-purchase-class-budget-expenditure')
                        <div class="col-12">

                        <div class="card radius-8">
                            <div class="card-content">
                                <div class="card-body">

                                    <div class="col-12 col-lg-3">
                                        <label for="pcb-grouped-bar-department">Department</label>
                                        <select class="form-control box-size mb-2" id="pcb-grouped-bar-department" name="pcb-grouped-bar-department" required data-placeholder="Select a Department"
                                                aria-label="Select Department">
                                            @foreach ($departments as $dep)
                                                <option value="{{ $dep['id'] }}"
                                                        {{--                                                        @if(@$purchaseClassBudget['department'] === $dep['id']) selected @endif--}}
                                                >
                                                    {{ $dep['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="card radius-8">
                                        <div class="card-content">
                                            <div class="card-body">

                                                <div class="bar-chart-container">
                                                    <p class="ml-6" id="pcbGroupedBarTitle" style="font-size: 20px"> </p>
                                                    <canvas id="purchase-class-budget-grouped-bar" style="max-height: 400px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
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


                    <!-- EDL Metrics -->
                    @permission('dashboard-visualizations-edl-metrics')
                        <div class="col-12">

                            <div class="card radius-8">
                                <div class="card-content">
                                    <div class="card-body">

                                        <p class="ml-6" style="font-size: 20px"> Employee Daily Log Metrics </p>

                                        <div class="row">
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
                                                                <h5>Yesterday's Logged Tasks</h5>
                                                                <h5 class="text-bold-500 mb-0" style="font-size: 21px;" s>
                                                                    <!--<i class="ft-arrow-up"></i> <span id="dash_4"><i class="fa fa-spinner spinner"></i></span>-->
                                                                    {{ $edlMetrics['tasksLoggedYesterday'] }} @if($edlMetrics['tasksLoggedYesterday'] > 1 ) tasks @else task @endif
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
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    @endauth


                </div>





                <div class="row ">
                    <!-- Sale Invoices Graph -->
                    @permission('dashboard-visualizations-daily-sales-and-expenses')
                        <div class="col-12 col-xl-8 col-lg-12">
                        <div class="card radius-8">

                            <div class="card-content">
                                <div class="card-body">
                                        <div class="bar-chart-container">
                                            <p class="ml-6 card-title">{{ $sevenDaySalesExpenses['chartTitle'] }}</p>
                                            <canvas id="invoice-totals-chart" ></canvas>
                                        </div>
{{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth

                    <div class="col-xl-4 col-lg-12">

                        <div>

                            @permission('dashboard-visualizations-general-metrics')
                                <!-- Yesterday's Man Hours -->
                                <div class="card mb-1 radius-8">
                                    <div class="card-content">
                                        <div class="media align-items-stretch">
                                            <div class="p-2 text-center bg-warning bg-darken-2 radius-8-left">
                                                <i class="icon-clock font-large-1 white"></i>
                                            </div>
                                            <div class="p-2 bg-gradient-x-warning white media-body radius-8-right">
                                                <h6>Yesterday's Labour Hours PQRSTU</h6>
                                                <h5 class="text-bold-400 mb-0">
                                                    {{ $labourAllocationData['yesterday']['ylaTotalManHours'] }} hrs
                                                    <small class="float-right mr-4">Target: 72</small>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Yesterday Invoices -->
                                <div class="card mb-1 radius-8">
                                    <div class="card-content">
                                        <div class="media align-items-stretch">
                                            <div class="p-2 text-center bg-primary bg-darken-2 radius-8-left">
                                                <i class="fa fa-file-text-o font-large-1 white"></i>
                                            </div>
                                            <div class="p-2 bg-gradient-x-primary white media-body radius-8-right">
                                                <h6>Yesterday Invoices</h6>
                                                <h5 class="text-bold-400 mb-0">
                                                    <!--<i class="ft-plus"></i> -->
                                                    <!--<span id="dash_1"><i class="fa fa-spinner spinner"></i></span>-->
                                                    {{ $data['invoices']->count() }} invoice(s)
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-1 radius-8">
                                    <div class="card-content">
                                        <div class="media align-items-stretch">
                                            <div class="p-2 text-center bg-primary bg-darken-2 radius-8-left">
                                                <i class="fa icon-credit-card font-large-1 white"></i>
                                            </div>
                                            <div class="p-2 bg-gradient-x-primary white media-body radius-8-right">
                                                <h6>Yesterday Purchases</h6>
                                                <h5 class="text-bold-400 mb-0">
                                                    <!--<i class="ft-plus"></i> -->
                                                    <!--<span id="dash_1"><i class="fa fa-spinner spinner"></i></span>-->
                                                    {{ $data['purchases']->count() + $data['purchase_orders']->count() }} purchase(s)
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- This Month's Man Hours -->
                                <div class="card mb-1 radius-8">
                                    <div class="card-content">
                                        <div class="media align-items-stretch">
                                            <div class="p-2 text-center bg-success bg-darken-2 radius-8-left">
                                                <i class="icon-clock font-large-1 white"></i>
                                            </div>
                                            <div class="p-2 bg-gradient-x-success white media-body radius-8-right">
                                                <h6>This Month's Labour Hours</h6>
                                                <h5 class="text-bold-400 mb-0">
                                                    <!--<i class="ft-arrow-up"></i> <span id="dash_4"><i class="fa fa-spinner spinner"></i></span>-->
                                                    {{ $labourAllocationData['thisMonth']['tmlaTotalManHours'] }} hrs
                                                    <small class="float-right mr-4">Target: {{  $labourAllocationData['thisMonth']['monthHoursTarget'] }}</small>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- This Month's Sales Total-->
                                <div class="card mb-1 radius-8">
                                    <div class="card-content">
                                        <div class="media align-items-stretch">
                                            <div class="p-2 text-center bg-success bg-darken-2 radius-8-left">
                                                <i class="icon-note font-large-1 white"></i>
                                            </div>
                                            <div class="p-2 bg-gradient-x-success white media-body radius-8-right">
                                                <h6>This Month Sales Total</h6>
                                                <h5 class="text-bold-400 mb-0">
                                                    <!--<i class="ft-arrow-up"></i> <span id="dash_4"><i class="fa fa-spinner spinner"></i></span>-->
                                                    KES {{ number_format($keyMetrics['thisMonth']['totals']['sales'], 2, '.', ',') }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- This Month's Purchases Total-->
                                <div class="card mb-1 radius-8">
                                    <div class="card-content">
                                        <div class="media align-items-stretch">
                                            <div class="p-2 text-center bg-success bg-darken-2 radius-8-left">
                                                <i class="icon-clock font-large-1 white"></i>
                                            </div>
                                            <div class="p-2 bg-gradient-x-success white media-body radius-8-right">
                                                <h6>This Month Purchases Total</h6>
                                                <h5 class="text-bold-400 mb-0">
                                                    <!--<i class="ft-arrow-up"></i> <span id="dash_4"><i class="fa fa-spinner spinner"></i></span>-->
                                                   KES {{ number_format($keyMetrics['thisMonth']['totals']['expenses'], 2, '.', ',') }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endauth


                        </div>

                    </div>
                </div>
                
                    <!-- Recent & Monthly Sales -->
                    <div class="row match-height">

                        <!-- Recent Invoices -->
                        @permission('dashboard-visualizations-recent-invoices')
                            <div class="col-xl-8 col-lg-12">
                                <div class="card radius-8">
                                    <div class="card-header">
                                        <h4 class="card-title">{{ trans('dashboard.recent_invoices') }}</h4>
                                        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                        <div class="heading-elements">
                                            <ul class="list-inline mb-0">
                                                <li><a href="{{ route('biller.invoices.create') }}"
                                                        class="btn btn-primary btn-sm rounded">{{ trans('invoices.add_sale') }}</a>
                                                </li>
                                                <li><a href="{{ route('biller.invoices.index') }}"
                                                        class="btn btn-success btn-sm rounded">{{ trans('invoices.manage_invoices') }}</a>
                                                </li>
                                                <li></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="card-content">
                                    <div class="table-responsive">
                                        <table id="recent-orders"
                                            class="table table-hover mb-0 ps-container ps-theme-default">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('invoices.invoice') }}</th>
                                                    <th>{{ trans('customers.customer') }}</th>
                                                    <th>{{ trans('invoices.invoice_due_date') }}</th>
                                                    <th>{{ trans('general.amount') }}</th>
                                                    <th>{{ trans('general.status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $prefixes = prefixes();
                                                @endphp
                                                @foreach ($data['invoices'] as $invoice)
                                                    <tr>
                                                        <td class="text-truncate"><a
                                                                href="{{ route('biller.invoices.show', [$invoice['id']]) }}">
                                                                @switch($invoice['i_class'])
                                                                    @case(0)
                                                                        {{ $prefixes->where('class', '=', 1)->first()->value }}
                                                                    @break
                                                                    @case(1)
                                                                        {{ $prefixes->where('class', '=', 10)->first()->value }}
                                                                    @break
                                                                    @case($invoice['i_class'] > 1)
                                                                        {{ $prefixes->where('class', '=', 6)->first()->value }}
                                                                    @break
                                                                @endswitch #{{ $invoice['tid'] }}
                                                            </a></td>
                                                        <td class="text-truncate">{{ @$invoice->customer->company ?: @$invoice->customer->name  }}</td>
                                                        <td class="text-truncate">{{ dateFormat($invoice['invoiceduedate']) }}
                                                        </td>
                                                        <td class="text-truncate">{{ amountFormat($invoice['total']) }}</td>
                                                        <td class="text-truncate"><span
                                                                class="st-{{ $invoice['status'] }}">{{ trans('payments.' . $invoice['status']) }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                </div>
                            </div>
                        @endauth

                        <!-- Recent Buyers -->
                        @permission('dashboard-visualizations-recent-buyers')
                            <div class="col-12 col-lg-4 card radius-8" >
                                <div class="card-header">
                                    <h4 class="card-title">{{ trans('dashboard.recent_buyers') }}</h4>
                                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-content px-1">
                                    <div id="recent-buyers_p" class="media-list height-450 position-relative">
                                        @foreach ($data['customers'] as $customer)
                                            <a href="#" class="media border-0">
                                                <div class="media-left pr-1">
                                                        <span class="avatar avatar-md avatar-online"><img
                                                                    class="media-object rounded-circle"
                                                                    src="https://loremflickr.com/400/400/city">
                                                            <i></i>
                                                        </span>
                                                </div>
                                                <div class="media-body w-100">
                                                    <h6 class="list-group-item-heading">
                                                        {{ $customer->company ?: $customer->name }}
                                                    </h6>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endauth

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
    </div>
    <input type="hidden" id="loader_url" value="{{ route('biller.tasks.load') }}">
    <input type="hidden" id="mini_dash" value="{{ route('biller.mini_dash') }}">
    <!-- END: Content-->
    {{-- @include('focus.projects.modal.task_view') --}}
@endsection

@section('after-styles')
    {!! Html::style('core/app-assets/vendors/css/charts/morris.css') !!}
@endsection

@section('extra-scripts')
{{ Html::script('core/app-assets/vendors/js/charts/raphael-min.js') }}
{{ Html::script('core/app-assets/vendors/js/charts/morris.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}

<script type="text/javascript">

    Chart.register(ChartDataLabels);


    // Daily Sales and Expense Totals
    $(function(){

        let sevenDaySalesExpenses = @json($sevenDaySalesExpenses);

        var ctx = $("#invoice-totals-chart");

        var chart1 = new Chart(ctx, {
            type: 'line',

            data: {
                labels: sevenDaySalesExpenses.salesDates,
                datasets: [
                    {
                        label: "Sales",
                        data: sevenDaySalesExpenses.salesTotals,
                        tension: 0.1,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192)',
                    },
                    {
                        label: "Expenses",
                        data: sevenDaySalesExpenses.expensesTotals,
                        tension: 0.1,
                        backgroundColor: 'rgba(255, 205, 86, 0.2)',
                        borderColor: 'rgba(255, 205, 86)',
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
                            labelString: 'Day of the month'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Invoice Totals'
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
                tooltips: {
                    enabled: true,
                    mode: 'single',
                    callbacks: {
                        title: function (tooltipItems, data) {
                            //Return value for title
                            return dailySalesExpensesData.month + ' ' + tooltipItems[0].xLabel;
                        },
                        label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                            //Return value for label
                            return 'KES ' + tooltipItems.yLabel;
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


    });


    // Key Metrics Quantities
    $(function(){

        let sevenDayLabourHours = @json($sevenDayLabourHours);


        //get the pie chart canvas
        var ctx = $("#key-quantities-chart");

        //create Pie Chart class object
        var chart1 = new Chart(ctx, {
            type: 'bar',

            data: {
                labels: (sevenDayLabourHours.labourDates),
                datasets: [

                    {
                        label: "Labour Hours",
                        data: sevenDayLabourHours.hoursTotals,
                        backgroundColor: 'rgba(75,192,77,0.2)',
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
                            labelString: 'Day of the month'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Invoice Totals'
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


    //Key Metrics TOTALS
    $(function(){

        let keyMetrics = @json($keyMetrics);
        let yesterdayTotals = keyMetrics.yesterday.totals;
        let monthTotals = keyMetrics.thisMonth.totals;

        //get the pie chart canvas
        var ctx = $("#key-totals-chart");

        //create Pie Chart class object
        var chart1 = new Chart(ctx, {
            type: 'bar',

            data: {
                labels: ["Yesterday", "This Month"],
                datasets: [
                    {
                        label: "Sales",
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(255, 205, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(201, 203, 207, 0.2)'
                        ],
                        borderWidth: 1,
                        borderColor: 'rgba(255, 205, 86)',
                        data: [yesterdayTotals.sales, monthTotals.sales]
                    },
                    {
                        label: "Expense",
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderWidth: 1,
                        borderColor: 'rgba(153, 102, 255)',
                        data: [yesterdayTotals.expenses, monthTotals.expenses]
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
                            labelString: 'Day of the month'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Invoice Totals'
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


    //New AI Leads Chart
    $(function(){

        let newAgentLeadsMetrics = @json($newAgentLeadsMetrics);


        //get the pie chart canvas
        var ctx = $("#new-ai-agent-leads-chart");

        //create Pie Chart class object
        var chart1 = new Chart(ctx, {
            type: 'bar',

            data: {
                labels: (newAgentLeadsMetrics.months),
                datasets: [

                    {
                        label: "New AI Leads",
                        data: newAgentLeadsMetrics.newLeads,
                        backgroundColor: 'rgb(104,124,162,0.4)',
                        borderColor: 'rgb(104,124,162)',
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
                            labelString: 'New Leads'
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


    // Project Status Distribution
    $(function(){

        let projectStatusMetrics = @json($projectStatusMetrics);

        //get the pie chart canvas
        var ctx = $("#project-statuses-chart");

        //create Pie Chart class object
        var chart1 = new Chart(ctx, {
            type: 'polarArea',

            data: {
                labels: (projectStatusMetrics.statuses),
                datasets: [

                    {
                        label: "Projects",
                        data: projectStatusMetrics.projects,
                        backgroundColor: [
                            'rgba(130, 255, 81, 0.5)', // Pink
                            'rgba(54, 162, 235, 0.5)', // Blue
                            'rgba(255, 206, 86, 0.5)', // Yellow
                            'rgba(75, 192, 192, 0.5)', // Teal
                            'rgba(153, 102, 255, 0.5)', // Purple
                            'rgba(255, 159, 64, 0.5)', // Orange
                            'rgba(255, 64, 0, 0.5)'   // Red
                        ],
                    },

                ]
            },
            options: {
                scales: {
                    r: {
                        pointLabels: {
                            display: true,
                            centerPointLabels: true,
                            font: {
                                size: 18
                            }
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


    $(document).ready(function() {

        $("#pcb-donut-department").select2();
        $("#pcb-grouped-bar-department").select2();
        drawPcbDonut();
        drawPcbGroupedBar()
        drawProductCategoriesBar();
    });

    let pcbDonutDepartmentVal = $("#pcb-donut-department").val();

    $('#pcb-donut-department').change(async () => {
        pcbDonutDepartmentVal = $("#pcb-donut-department").val();
        console.table({ depNo: pcbDonutDepartmentVal });

        await drawPcbDonut(); // Redraw the chart with new data
    });

    let pcbGroupedBarDepartmentVal = $("#pcb-grouped-bar-department").val();

    $('#pcb-grouped-bar-department').change(async () => {
        pcbGroupedBarDepartmentVal = $("#pcb-grouped-bar-department").val();
        console.table({ depNo: pcbGroupedBarDepartmentVal });

        await drawPcbGroupedBar(); // Redraw the chart with new data
    });


    const fetchPcbChartData = (departmentNumber) => {

        console.log("Fetching PCB data for Department " + departmentNumber);
        console.table({ url: '{{url('/')}}' });

        return $.ajax({
            url: 'purchase-class-budget/department/' + departmentNumber + '/chart-metrics',
            type: 'GET',
            data: {},
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



    let pcbDonutChartInstance = null;

    async function drawPcbDonut() {

        var ctx = $("#purchase-class-budget-donut");

        // Await the fetchPcbChartData function
        let chartData = await fetchPcbChartData(pcbDonutDepartmentVal);

        if (!chartData) {
            console.error("No data available to draw the chart.");
            return;
        }

        // Destroy the existing chart instance if it exists
        if (pcbDonutChartInstance) {
            pcbDonutChartInstance.destroy();
        }

        $('#pcbDonutTitle').text(chartData.donutTitle);


        // Create a new chart instance
        pcbDonutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.names,
                datasets: [{
                    label: "Projects",
                    data: chartData.budgets,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',  // Pink
                        'rgba(54, 162, 235, 0.5)',  // Blue
                        'rgba(255, 206, 86, 0.5)',  // Yellow
                        'rgba(75, 192, 192, 0.5)',  // Teal
                        'rgba(153, 102, 255, 0.5)', // Purple
                        'rgba(255, 159, 64, 0.5)',  // Orange
                        'rgba(255, 99, 71, 0.5)',   // Red
                        'rgba(128, 0, 128, 0.5)',   // Dark Purple
                        'rgba(0, 128, 128, 0.5)',   // Dark Teal
                        'rgba(128, 128, 0, 0.5)',   // Olive
                        'rgba(0, 0, 128, 0.5)',     // Navy
                        'rgba(255, 105, 180, 0.5)', // Hot Pink
                        'rgba(0, 255, 255, 0.5)',   // Cyan
                        'rgba(255, 165, 0, 0.5)',   // Orange
                        'rgba(0, 255, 0, 0.5)',     // Lime
                        'rgba(255, 0, 255, 0.5)',   // Magenta
                        'rgba(192, 192, 192, 0.5)', // Silver
                        'rgba(255, 20, 147, 0.5)',  // Deep Pink
                        'rgba(0, 0, 255, 0.5)',     // Blue
                        'rgba(139, 69, 19, 0.5)',   // Saddle Brown
                        'rgba(255, 0, 0, 0.5)',     // Red
                        'rgba(255, 69, 0, 0.5)',    // Red-Orange
                        'rgba(0, 128, 0, 0.5)',     // Green
                        'rgba(0, 0, 139, 0.5)',     // Dark Blue
                        'rgba(106, 90, 205, 0.5)',  // Slate Blue
                        'rgba(255, 215, 0, 0.5)',   // Gold
                        'rgba(0, 250, 154, 0.5)',   // Medium Spring Green
                        'rgba(0, 191, 255, 0.5)',   // Deep Sky Blue
                        'rgba(147, 112, 219, 0.5)', // Medium Purple
                        'rgba(255, 140, 0, 0.5)',   // Dark Orange
                        'rgba(70, 130, 180, 0.5)',  // Steel Blue
                        'rgba(32, 178, 170, 0.5)',  // Light Sea Green
                        'rgba(255, 192, 203, 0.5)', // Pink
                        'rgba(128, 128, 128, 0.5)', // Gray
                        'rgba(245, 222, 179, 0.5)', // Wheat
                        'rgba(139, 0, 139, 0.5)',   // Dark Magenta
                        'rgba(255, 228, 196, 0.5)', // Bisque
                        'rgba(75, 0, 130, 0.5)',    // Indigo
                        'rgba(255, 240, 245, 0.5)', // Lavender Blush
                        'rgba(255, 69, 150, 0.5)',  // Light Coral
                        'rgba(60, 179, 113, 0.5)',  // Medium Sea Green
                        'rgba(186, 85, 211, 0.5)',  // Medium Orchid
                        'rgba(186, 85, 211, 0.5)',  // Medium Orchid
                        'rgba(255, 105, 180, 0.5)', // Hot Pink
                        'rgba(124, 252, 0, 0.5)',   // Lawn Green
                        'rgba(255, 20, 147, 0.5)',  // Deep Pink
                        'rgba(0, 255, 0, 0.5)',     // Lime
                        'rgba(0, 255, 255, 0.5)'    // Cyan
                    ],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    datalabels: {
                        display: false // Ensures data labels are hidden
                    }
                }
            }
        });
    }


    let pcbGroupedBarChartInstance = null;

    async function drawPcbGroupedBar() {

        var ctx = $("#purchase-class-budget-grouped-bar");

        // Await the fetchPcbChartData function
        let chartData = await fetchPcbChartData(pcbGroupedBarDepartmentVal);

        if (!chartData) {
            console.error("No data available to draw the chart.");
            return;
        }

        // Destroy the existing chart instance if it exists
        if (pcbGroupedBarChartInstance) {
            pcbGroupedBarChartInstance.destroy();
        }

        $('#pcbGroupedBarTitle').text(chartData.groupedBarTitle);

        console.log("barTitle: " + chartData.groupedBarTitle)

        // Create a new chart instance
        pcbGroupedBarChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.names,
                datasets: [
                    {
                        label: 'Budget',
                        data: chartData.budgets,
                        backgroundColor: 'rgba(255, 99, 132, 1)',
                        stack: 'Stack 0',
                        borderRadius: {
                            topLeft: 6,
                            topRight: 6,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        borderSkipped: 'bottom' // Apply border radius only to the top of the stack
                    },
                    {
                        label: 'Purchases',
                        data: chartData.purchasesValue,
                        backgroundColor: 'rgba(54, 162, 235, 1)',
                        stack: 'Stack 1',
                        borderRadius: 0, // No radius on middle bars
                        borderSkipped: false // Ensures no borders are skipped
                    },
                    {
                        label: 'Purchase Orders',
                        data: chartData.purchaseOrdersValue,
                        backgroundColor: 'rgba(75, 192, 192, 1)',
                        stack: 'Stack 1',
                        borderRadius: {
                            topLeft: 6,
                            topRight: 6,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        borderSkipped: 'bottom' // Apply border radius only to the top of the stack
                    },
                ]
            },
            options: {
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Non-Project Class',
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Fiscal value'
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



    function loadDash() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var action_url = $('#mini_dash').val();
        $.ajax({
            url: action_url,
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                var i = 1;
                $.each(data.dash, function(key, value) {
                    $('#dash_' + i).text(value);
                    i++;
                });
                drawCompareChart(data.inv_exp);
                sales(data.sales);
            }
        });
        window.dispatchEvent(new Event('resize'));
    }

    function drawCompareChart(inv_exp) {
        $('#dashboard-sales-breakdown-chart').empty();
        Morris.Donut({
            element: 'income-compare-chart',
            data: [{
                    label: "{{ trans('accounts.Income') }}",
                    value: inv_exp.income
                },
                {
                    label: "{{ trans('accounts.Expenses') }}",
                    value: inv_exp.expense
                }
            ],
            resize: true,
            colors: ['#34cea7', '#ff6e40'],
            gridTextSize: 6,
            gridTextWeight: 400
        });
    }

    function drawIncomeChart(dataIncome) {
        $('#dashboard-income-chart').empty();
        Morris.Area({
            element: 'dashboard-income-chart',
            data: dataIncome,
            xkey: 'x',
            ykeys: ['y'],
            ymin: 'auto 40',
            labels: ['{{ trans('general.amount') }}'],
            xLabels: "day",
            hideHover: 'auto',
            yLabelFormat: function(y) {
                // Only integers
                if (y === parseInt(y, 10)) return y;
                return '';
            },
            resize: true,
            lineColors: ['#00A5A8'],
            pointFillColors: ['#00A5A8'],
            fillOpacity: 0.4,
        });
    }

    function drawExpenseChart(dataExpenses) {
        $('#dashboard-expense-chart').empty();
        Morris.Area({
            element: 'dashboard-expense-chart',
            data: dataExpenses,
            xkey: 'x',
            ykeys: ['y'],
            ymin: 'auto 0',
            labels: ['{{ trans('general.amount') }}'],
            xLabels: "day",
            hideHover: 'auto',
            yLabelFormat: function(y) {
                // Only integers
                if (y === parseInt(y, 10)) return y;
                return '';
            },
            resize: true,
            lineColors: ['#ff6e40'],
            pointFillColors: ['#34cea7']
        });
    }

    function sales(sales_data) {
        $('#products-sales').empty();
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        Morris.Area({
            element: 'products-sales',
            data: sales_data,
            xkey: 'y',
            ykeys: ['sales', 'invoices'],
            labels: ['sales', 'invoices'],
            behaveLikeLine: true,
            xLabelFormat: function(x) {
                var day = x.getDate();
                var month = months[x.getMonth()];
                return day + ' ' + month;
            },
            resize: true,
            pointSize: 0,
            pointStrokeColors: ['#00B5B8', '#FA8E57', '#F25E75'],
            smooth: true,
            gridLineColor: '#E4E7ED',
            numLines: 6,
            gridtextSize: 14,
            lineWidth: 0,
            fillOpacity: 0.9,
            hideHover: 'auto',
            lineColors: ['#00B5B8', '#F25E75'],
        });
    }

    $('a[data-toggle=tab').on('shown.bs.tab', function(e) {
        window.dispatchEvent(new Event('resize'));
    });
</script>

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
