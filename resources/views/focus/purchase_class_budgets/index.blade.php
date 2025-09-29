@extends ('core.layouts.app')

@section ('title', 'Non-Project Class Budgets')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h2 class=" mb-0">Non-Project Class Budgets </h2>
            </div>

            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.purchase_class_budgets.partials.header-buttons')
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

                                <div class="row mt-2 mb-1">

                                    <div class="col-12 row">

                                        <div class="col-9 col-lg-3">
                                            <label for="purchase_class_filter">Filter by Non-Project Class</label>
                                            <select class="form-control box-size filter" id="purchase_class_filter"
                                                    name="purchase_class_filter"
                                                    data-placeholder="Filter by Non-Project Class">

                                                <option value=""></option>

                                                @foreach ($purchaseClasses as $pC)
                                                    <option value="{{ $pC['id'] }}">
                                                        {{ $pC['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-9 col-lg-3">
                                            <label for="financial_year_filter">Filter by Financial Year</label>
                                            <select class="form-control box-size filter" id="financial_year_filter"
                                                    name="financial_year_filter"
                                                    data-placeholder="Filter by Financial Year">

                                                <option value=""></option>

                                                @foreach ($financialYears as $fY)
                                                    <option value="{{ $fY['id'] }}">
                                                        {{ $fY['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-9 col-lg-3">
                                            <label for="department_id">Filter by Class List</label>
                                            <select class="form-control box-size mb-2 filter" id="classlist_filter" name="classlist_filter" required data-placeholder="Filter by Class List" aria-label="Filter by Class List">
                                                <option value="">Filter by Class List</option>
                                                @foreach ($classLists as $c)
                                                    <option value="{{ $c['id'] }}">
                                                        {{ $c['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-9 col-lg-3">
                                            <label for="purchaseOrderMonth" >Filter by Expense Category</label>
                                            <select class="form-control box-size filter" id="expense_category_filter" name="expense_category_filter" data-placeholder="Filter by Expense Category">

                                                <option value=""></option>
                                                @foreach ($expenseCategories as $eC)
                                                    <option value="{{ $eC->id }}">
                                                        {{ $eC->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-6 col-lg-2">
                                            <button id="clear_filters" class="btn btn-secondary round mt-2"> Clear
                                                Filters
                                            </button>
                                        </div>
                                    </div>

                                </div>

                                <div class="row mt-3">
                                    <p class="col-6 col-lg-4" style="font-size: 16px;">Total Budget: <span
                                                id="totalBudget" style="font-size: 25px; font-weight: bold"></span></p>
                                    <p class="col-6 col-lg-4" style="font-size: 16px;">Total Purchase Items Value: <span
                                                id="totalPurchases" style="font-size: 25px; font-weight: bold"></span>
                                    </p>
                                    <p class="col-6 col-lg-4" style="font-size: 16px;">Total Purchase Order Items Value: <span
                                                id="totalPurchaseOrders"
                                                style="font-size: 25px; font-weight: bold"></span></p>
                                </div>

                                <table id="purchase-class-budget-table"
                                       class="table table-striped table-bordered zero-configuration mt-2" cellspacing="0"
                                       width="100%">
                                    <thead>
                                    <tr>
                                        <th>Non-Project Class</th>
                                        <th>Expense Category</th>
                                        <th>Financial Year</th>
                                        <th>Department</th>
                                        <th>Budget</th>
                                        <th>Expense to Date</th>
                                        <th>Balance on Budget</th>
                                        <th>Action</th>
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
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>
        setTimeout(() => draw_data(), "{{ config('master.delay') }}");

        $.ajaxSetup({headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

        const financialYearFilter = $('#financial_year_filter');
        const purchaseClassFilter = $('#purchase_class_filter');
        const expenseCategoryFilter = $('#expense_category_filter');
        const classlistFilter = $('#classlist_filter');
        const clearFilters = $('#clear_filters');

        financialYearFilter.select2({allowClear: true});
        purchaseClassFilter.select2({allowClear: true});
        expenseCategoryFilter.select2({allowClear: true});
        classlistFilter.select2({allowClear: true});

        function draw_data() {
            const tableLan = {@lang('datatable.strings')};

            var dataTable = $('#purchase-class-budget-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: tableLan,
                ajax: {
                    url: '{{ route("biller.purchase-class-budgets.index") }}',
                    type: 'GET',
                    data: {
                        financial_year_filter: financialYearFilter.val(),
                        purchase_class_filter: purchaseClassFilter.val(),
                        expense_category_filter: expenseCategoryFilter.val(),
                        classlist_filter: classlistFilter.val(),
                    }
                },
                columns: [
                    {
                        data: 'purchaseClass',
                        name: 'purchaseClass'
                    },
                    {
                        data: 'expense_category',
                        name: 'expense_category'
                    },
                    {
                        data: 'financial_year_id',
                        name: 'financial_year_id'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'budget',
                        name: 'budget'
                    },
                    // {
                    //     data: 'budget_sum',
                    //     name: 'budget_sum',
                    //     visible: false
                    // },
                    {
                        data: 'expense_to_date',
                        name: 'expense_to_date'
                    },
                    {
                        data: 'balance_to_date',
                        name: 'balance_to_date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        sortable: false
                    }
                ],
                order: [
                    [0, "asc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'pdf', 'print'],
                drawCallback: function (settings) {

                    $.ajax({
                        url: '{{ route("biller.purchase-class-budgets.metrics") }}', // Update with your server endpoint
                        type: 'GET',
                        data: {
                            financial_year_filter: financialYearFilter.val(),
                            purchase_class_filter: purchaseClassFilter.val(),
                            expense_category_filter: expenseCategoryFilter.val(),
                            classlist_filter: classlistFilter.val(),
                            search_term: $('#purchase-class-budget-table_filter input').val()
                        },
                        success: function (response) {
                            // Assuming the server returns an object with a totalBudget property
                            console.table(response);

                            console.table({searchParam: $('#purchase-class-budget-table_filter input').val()})

                            // console.log("TOTAL BUDGET: " + response.totalBudget.toFixed(2))
                            $('#totalBudget').text(response.totalBudget.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }));
                            $('#totalPurchases').text(response.totalPurchases.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }));
                            $('#totalPurchaseOrders').text(response.totalPurchaseOrders.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }));
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

        $('.filter').change(() => {
            $('#purchase-class-budget-table').DataTable().destroy();
            draw_data();
        })

        clearFilters.click(() => {

            financialYearFilter.val('').trigger('change');
            purchaseClassFilter.val('').trigger('change');
            expenseCategoryFilter.val('').trigger('change');
            classlistFilter.val('').trigger('change');

            $('#purchase-class-budget-table').DataTable().destroy();
            draw_data();
        })

    </script>
@endsection