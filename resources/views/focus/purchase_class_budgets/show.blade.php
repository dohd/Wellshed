@php use App\Models\financialYear\FinancialYear; @endphp
@extends('core.layouts.app')

@section('title', 'Non-Project Class Budget Report')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-2">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">Non-Project Class Budget Report</h4>

            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">

                    <div class="media-body media-right text-right">
                        @include('focus.purchase_class_budgets.partials.header-buttons')
                    </div>
                </div>
            </div>
        </div>
        <div class="card">

            <div class="card-body">

                <h1> {{ $purchaseClassBudget->purchaseClass->name }} </h1>

                <table class="mb-2" style="border-collapse: collapse;">

                    <tr>
                        <td style="padding-right: 8px; font-size: large; font-weight: bolder;">Expense Category:</td>
                        <td style="font-size: x-large;">
                            {{ optional(optional($purchaseClassBudget->purchaseClass)->expenseCategory)->name }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-right: 8px; font-size: large; font-weight: bolder;">Fin. Year:</td>
                        <td style="font-size: x-large;">
                            @if($purchaseClassBudget->financial_year_id)
                                {{ FinancialYear::find($purchaseClassBudget->financial_year_id)->name }}
                            @else
                                <b style="color: #FF8200; font-size: medium"><i> Financial Year Not Set </i></b>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-right: 8px; font-size: large; font-weight: bolder;">Budget:</td>
                        <td style="font-size: x-large;">
                            @if($purchaseClassBudget->budget)
                                {{ numberFormat($purchaseClassBudget->budget) }}
                            @else
                                <b style="color: #FF8200; font-size: medium"><i> Budget Not Set </i></b>
                            @endif
                        </td>
                    </tr>
                </table>


                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                           role="tab" aria-selected="true"><span class="">Purchases </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2"
                           role="tab" aria-selected="false"><span>Purchase-orders</span>
                        </a>
                    </li>

                </ul>
                <div class="tab-content px-1 pt-1">
                    <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                        @include('focus.purchase_class_budgets.partials.purchases-report')
                    </div>
                    <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                        @include('focus.purchase_class_budgets.partials.purchase-orders-report')
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
    {{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
    {{ Html::script('focus/js/select2.min.js') }}


    <script>

        $(function () {
            setTimeout(function () {

                drawPurchaseDataTables();
                drawPurchaseOrdersDataTable();
            }, {{config('master.delay')}});
        });

        const purchaseMonthFilter = $('#purchaseMonth');
        const clearPurchaseFilters = $('#clearPurchaseFilters');

        purchaseMonthFilter.select2();

        $('.p-filter').change(() => {
            $('#purchasesTbl').DataTable().destroy();
            drawPurchaseDataTables();
        })

        clearPurchaseFilters.click(() => {

            purchaseMonthFilter.val('').trigger('change');

            $('#purchasesTbl').DataTable().destroy();
            drawPurchaseDataTables();
        })


        function drawPurchaseDataTables() {

            $('#purchasesTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.purchase_class_budgets.get-purchases-data", $purchaseClassBudget->id) }}',
                    type: 'post',
                    data: {
                        purchaseMonthFilter: purchaseMonthFilter.val(),
                        financialYear: {{ $purchaseClassBudget->financialYear ? (new DateTime($purchaseClassBudget->financialYear->start_date))->format('Y') : 0 }}
                    }
                },
                columns: [
                    {data: 'p_number', name: 'po_number'},
                    {data: 'supplier', name: 'supplier'},
                    {data: 'item_names', name: 'item_names'},
                    {data: 'month', name: 'month'},
                    {data: 'date', name: 'date'},
                    {data: 'total', name: 'total'},
                    {data: 'created_by', name: 'created_by'},
                    // Add other columns as needed
                ],
                order: [[1, 'asc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
                drawCallback: function (settings) {

                    $.ajax({
                        url: '{{ route("biller.purchase-class-budgets.purchases-metrics") }}', // Update with your server endpoint
                        type: 'GET',
                        data: {
                            id: {{ $purchaseClassBudget->id }},
                            purchaseMonthFilter: purchaseMonthFilter.val(),
                            financialYear: {{ $purchaseClassBudget->financialYear ? (new DateTime($purchaseClassBudget->financialYear->start_date))->format('Y') : 0 }}
                        },
                        success: function (response) {

                            $('#purchasesValue').text(accounting.formatNumber(response.purchaseItemsValue));
                            $('#purchasesCount').text(accounting.formatNumber(response.purchaseItemsCount));
                            $('#purchasesMonthBudget').text(accounting.formatNumber(response.monthBudget));
                        },
                        error: function (xhr, status, error) {
                            // Parse the error response
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
                }
            });


        }


        const purchaseOrderMonthFilter = $('#poMonth');
        const clearPurchaseOrderFilters = $('#clearPurchaseOrderFilters');

        purchaseOrderMonthFilter.select2();

        $('.po-filter').change(() => {

            $('#purchaseOrdersTbl').DataTable().destroy();
            drawPurchaseOrdersDataTable();
        })

        clearPurchaseOrderFilters.click(() => {

            purchaseOrderMonthFilter.val('').trigger('change');

            $('#purchaseOrdersTbl').DataTable().destroy();
            drawPurchaseOrdersDataTable();
        })


        function drawPurchaseOrdersDataTable() {

            console.table({
                purchaseOrderMonthFilter: purchaseOrderMonthFilter.val(),
                financialYear: {{ $purchaseClassBudget->financialYear ? (new DateTime($purchaseClassBudget->financialYear->start_date))->format('Y') : 0 }}
            });


            $('#purchaseOrdersTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.purchase_class_budgets.get-purchase-orders-data", $purchaseClassBudget->id) }}',
                    type: 'post',
                    data: {
                        purchaseOrderMonthFilter: purchaseOrderMonthFilter.val(),
                        financialYear: {{ $purchaseClassBudget->financialYear ? (new DateTime($purchaseClassBudget->financialYear->start_date))->format('Y') : 0 }}
                    },
                },
                columns: [
                    {data: 'po_number', name: 'po_number'},
                    {data: 'supplier', name: 'supplier'},
                    {data: 'item_names', name: 'item_names'},
                    {data: 'month', name: 'month'},
                    {data: 'date', name: 'date'},
                    {data: 'total', name: 'total'},
                    {data: 'created_by', name: 'created_by'},
                ],
                order: [[1, 'asc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
                drawCallback: function (settings) {

                    $.ajax({
                        url: '{{ route("biller.purchase-class-budgets.purchase-orders-metrics") }}', // Update with your server endpoint
                        type: 'GET',
                        data: {
                            id: {{ $purchaseClassBudget->id }},
                            purchaseOrderMonthFilter: purchaseOrderMonthFilter.val(),
                            financialYear: {{ $purchaseClassBudget->financialYear ? (new DateTime($purchaseClassBudget->financialYear->start_date))->format('Y') : 0 }}
                        },
                        success: function (response) {

                            $('#purchaseOrdersValue').text(accounting.formatNumber(response.purchaseOrdersValue));
                            $('#purchaseOrdersCount').text(accounting.formatNumber(response.purchaseOrdersCount));
                            $('#purchaseOrdersMonthBudget').text(accounting.formatNumber(response.monthBudget));
                        },
                        error: function (xhr, status, error) {
                            // Parse the error response
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
                }

            });

        }


    </script>
@endsection
