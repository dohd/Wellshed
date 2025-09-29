@include('tinymce.scripts')
@extends ('core.layouts.app')
@section ('title', 'Comprehensive Operations Summary Report Options')
@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h2 class=" mb-0">Comprehensive Operations Summary Report Options</h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-radius: 8px;">
                        <div class="card-content">
                            <div class="card-body">
                                <div>
                                    @php
                                        $optionsList = [
                                            'invoices' => [
                                                'label' => 'Invoices',
                                                'description' => 'Include a detailed report of all invoices processed for the selected period.',
                                            ],
                                            'invoicePayments' => [
                                                'label' => 'Invoice Payments',
                                                'description' => 'Include a detailed report of all invoice payments processed for the selected period.',
                                            ],
                                            'purchases' => [
                                                'label' => 'Purchases',
                                                'description' => 'Include all purchase records processed within the selected time frame.',
                                            ],
                                            'purchaseOrders' => [
                                                'label' => 'Purchase Orders',
                                                'description' => 'Add a breakdown of purchase orders issued during the period.',
                                            ],
                                            'stockAlerts' => [
                                                'label' => 'Stock Alerts',
                                                'description' => 'Include alerts for low or out-of-stock items over the past week.',
                                            ],
                                            'dailyLabourHours' => [
                                                'label' => 'Daily Labour Hours',
                                                'description' => 'Add a summary of daily labor hours for the selected date range.',
                                            ],
                                            'dailySalesAndExpenses' => [
                                                'label' => 'Daily Sales and Expenses',
                                                'description' => 'Include a detailed report of daily sales and expenses for the selected dates.',
                                            ],
                                            'cashbookBalance' => [
                                                'label' => 'Cashbook Balance Summary',
                                                'description' => 'Include cashbook balance summary'
                                            ],
                                            'yesterdaysWorkLogs' => [
                                                'label' => "Work Logs",
                                                'description' => 'Add metrics from the day\'s work logs to the report.',
                                            ],
                                            'quotes' => [
                                                'label' => "Quotes & Proforma Invoices",
                                                'description' => 'Add Quotes & Proforma Invoices from the day to the report.',
                                            ],
                                            'tickets' => [
                                                'label' => "Tickets",
                                                'description' => 'Add tickets from the day to the report.',
                                            ],
                                            'projects' => [
                                                'label' => "Projects",
                                                'description' => 'Add projects from the day to the report.',
                                            ],
                                            'goodsReceiveNotes' => [
                                                'label' => "Goods Receive Notes",
                                                'description' => 'Add Goods Receive Notes from the day to the report.',
                                            ],
                                            'bankTransfers' => [
                                                'label' => "Bank Transfers",
                                                'description' => 'Add Bank Transfers from the day to the report.',
                                            ],
                                            'leaveApplications' => [
                                                'label' => "Leave Applications",
                                                'description' => 'Add Leave Applications from the day to the report.',
                                            ],
                                            'healthAndSafety' => [
                                                'label' => "Health & Safety",
                                                'description' => 'Add Health & Safety incidents from the day to the report.',
                                            ],
                                            'qualityTracking' => [
                                                'label' => "Quality Tracking",
                                                'description' => 'Add Quality Tracking incidents from the day to the report.',
                                            ],
                                            'environmentalTracking' => [
                                                'label' => "Environmental Tracking",
                                                'description' => 'Add Environmental Tracking incidents from the day to the report.',
                                            ],
                                            'documentManager' => [
                                                'label' => "Document Manager",
                                                'description' => 'Add Document Manager entries related to the day to the report.',
                                            ],
                                            'customerComplaints' => [
                                                'label' => "Customer Complaints",
                                                'description' => 'Add Customer Complaints entries from the day to the report.',
                                            ],
                                            'sentSms' => [
                                                'label' => "Sent Sms",
                                                'description' => 'Add Sent Bulk Sms from the day to the report.',
                                            ],
                                            'billPayments' => [
                                                'label' => "Bill Payments",
                                                'description' => 'Add Bill Payments from the day to the report.',
                                            ],
                                            'quoteBudgets' => [
                                                'label' => "Approved Quote Budgets",
                                                'description' => 'Add Approved Quote Budgets from the day to the report.',
                                            ],
                                            'birthdays' => [
                                                'label' => "Employee Birthdays",
                                                'description' => 'Add Employee Birthdays from the day to the report.',
                                            ],
                                            'grossProfit' => [
                                                'label' => "Projects Gross Profit",
                                                'description' => 'Add Gross Profit for Projects ended on the day to the report.',
                                            ],
                                            'agentLeads' => [
                                                'label' => "AI Agent Leads",
                                                'description' => 'Add AI Agent Leads Generated on the day to the report.',
                                            ],
                                            'agentChats' => [
                                                'label' => "AI Agent Chat Summary",
                                                'description' => 'Add AI Agent Chat Summary Generated on the day to the report.',
                                            ],
                                        ];
                                        ksort($optionsList);
                                    @endphp
                                    @permission('comprehensive-operational-summary-report-manager')
                                        <p class="mb-2" style="font-size: 1rem;">
                                            Please select the sections you'd like to include in your custom report. Check
                                            the boxes below for each type of data you want to be added. <br>
                                            Once selected, your daily <i>Comprehensive Operations Summary Report</i> will be generated with detailed information based
                                            on your choices.
                                            <br><br>
                                            <b>NOTE: </b> The report will be sent daily at 8:00am via email to all persons assigned the '<b><i>Comprehensive-Operational-Summary-Report Recipient</i></b>' user permission.
                                        </p>
                                        <form action="{{ route('biller.dbm-update-options') }}" method="POST" class="mb-4">
                                            @csrf
                                            <div class="row">
                                                @foreach($optionsList as $key => $val)
                                                    <div class="col-12 col-lg-6 mb-2">
                                                        <div class="form-check">
                                                            <!-- Larger checkbox -->
                                                            <input
                                                                id="{{ $key }}"
                                                                type="checkbox"
                                                                name="options[]"
                                                                value="{{ $key }}"
                                                                @if(in_array($key, $options ?? [])) checked @endif
                                                                class="form-check-input mr-1"
                                                                style="transform: scale(1.5);"
                                                            >
                                                            <!-- Larger label for the key -->
                                                            <label class="form-check-label" for="{{ $key }}" style="font-size: 1.25rem; padding-left: 6px;">
                                                                <strong>{{ $val['label'] }}</strong>
                                                            </label>
                                                            <!-- Description as paragraph below the checkbox -->
                                                            <p style="font-size: 0.875rem; margin-top: 0.5rem;">
                                                                {{ $val['description'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-3">Update Report Options</button>
                                        </form>
                                    @endauth
                                    @permission('comprehensive-operational-summary-report-recipient')
                                        <h2> View Past Report </h2>
                                        <div class="row mt-2">
                                            <div class="col-12 col-lg-8">
                                                <label for="dbms">Select A Date Below to View a Report</label>
                                                <select class="form-control box-size filter" id="dbms"
                                                        name="dbms"
                                                        data-placeholder="Select A Date to View a Report">
                                                    <option value=""></option>
                                                    @foreach ($dbm as $d)
                                                        <option value="{{ $d['uuid'] }}">
                                                            {{ $d['date'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <a href="" class="btn btn-secondary mt-3" id="viewReportBtn" target="_blank" disabled>View Report</a>
                                            </div>
                                        </div>
                                    @endauth
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
        const dbmSelect = $('#dbms');
        const viewReportBtn = $('#viewReportBtn');
        // Initialize select2
        dbmSelect.select2({ allowClear: true });
        // Function to handle select change and enable/disable button
        dbmSelect.on('change', function () {
            const selectedValue = $(this).val();
            if (selectedValue) {
                // Enable the button and update the href
                viewReportBtn.attr('href', `{{ url('api/daily-business-metrics') }}/${selectedValue}`);
                viewReportBtn.prop('disabled', false); // Enable the button
            } else {
                // Disable the button and clear the href
                viewReportBtn.attr('href', '');
                viewReportBtn.prop('disabled', true); // Disable the button
            }
        });
        // Initially disable the button if no value is selected
        if (!dbmSelect.val()) {
            viewReportBtn.prop('disabled', true);
        }
    </script>

@endsection
