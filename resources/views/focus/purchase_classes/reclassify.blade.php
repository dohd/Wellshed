@extends ('core.layouts.app')

@section ('title', 'Reclassify Non-Project Purchase Classes Records')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0"> Reclassify Non-Project Purchase Classes Records</h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">

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
                            <div class="mb-1">

                                <div class="row mb-2">


                                    <div class="col-10 col-lg-3">
                                        <label for="purchaseClass" >Filter by Purchase Class</label>
                                        <select class="form-control box-size filter" id="purchaseClass" name="purchaseClass" data-placeholder="Filter by Purchase Class">

                                            <option value="">Filter by Purchase Class</option>
                                            @foreach ($purchaseClasses as $p)
                                                <option value="{{ $p->id }}">
                                                    {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-10 col-lg-3">
                                        <label for="expenseCategory" >Filter by Expense Category</label>
                                        <select class="form-control box-size filter" id="expenseCategory" name="expenseCategory" data-placeholder="Filter by Expense Category">

                                            <option value="">Filter by Expense Category</option>
                                            @foreach ($expenseCategories as $eC)
                                                <option value="{{ $eC->id }}">
                                                    {{ $eC->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-10 col-lg-3">
                                        <label for="purchaseMonth" >Filter by Month</label>
                                        <select class="form-control box-size filter" id="purchaseMonth" name="purchaseMonth" data-placeholder="Filter by Month">

                                            <option value="">Filter by Month</option>

                                            @php
                                                $months = ['January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12];

                                            @endphp

                                            @foreach ($months as $m => $val)
                                                <option value="{{ $val }}">
                                                    {{ $m }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="col-10 col-lg-3">
                                        <label for="purchaseYear" >Filter by Year</label>
                                        <select class="form-control box-size filter" id="purchaseYear" name="purchaseYear" data-placeholder="Filter by Year">

                                            <option value="">Filter by Year</option>

                                            @php
                                                $years = [];
                                                $currentYear = (new DateTime())->format('Y');

                                                for ($i = 0; $i <= 100; $i++) {
                                                    $years[] = $currentYear - $i;
                                                }
                                            @endphp

                                            @foreach ($years as $yr)
                                                <option value="{{ $yr }}">
                                                    {{ $yr }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-10 col-lg-3 mt-lg-1">
                                        <label for="fromDate" >Filter from Date</label>
                                        <input type="date" id="fromDate" class="form-control box-size filter2" >
                                    </div>

                                    <div class="col-10 col-lg-3 mt-lg-1">
                                        <label for="toDate" >Filter to Date</label>
                                        <input type="date" id="toDate" class="form-control box-size filter2" >
                                    </div>

                                    <div class="col-3 mt-lg-1">
                                        <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                    </div>
                                </div>

                                <div class="row mt-1">
                                    <p class="col-6 col-lg-4" style="font-size: 16px;">Budget: <span id="monthBudget" style="font-size: 25px; font-weight: bold"></span></p>
                                    <p class="col-6 col-lg-4" style="font-size: 16px;">Purchases: <span id="purchasesCount" style="font-size: 25px; font-weight: bold"></span></p>
                                    <p class="col-6 col-lg-4" style="font-size: 16px;">Total Expense: <span id="purchasesValue" style="font-size: 25px; font-weight: bold"></span></p>
                                </div>

                            </div>

                            <form id="checkbox-form" action="{{ route('biller.reclassify-pc-purchases') }}" method="POST">
                                @csrf

                                <div class="mt-3">

                                    <h3>Reclassify to Purchase Class</h3>

                                    <div class="row mb-1 mt-1">

                                        <div class="col-12 col-lg-6">
                                            <label for="purchaseClass" >Purchase Class</label>
                                            <select class="form-control box-size select2" id="purchaseClass" name="purchase_class" data-placeholder="Select a Purchase Class" required>

                                                <option value="">Select a Purchase Class</option>
                                                @foreach ($purchaseClasses as $p)
                                                    <option value="{{ $p->id }}">
                                                        {{ $p->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-10 col-lg-3">
                                            <button type="submit" id="submit-btn" class="btn btn-success"> <i class="fa fa-refresh" aria-hidden="true"></i> &nbsp; Reclassify Records</button>
                                        </div>
                                    </div>

                                </div>


                                <table id="purchasesTbl"
                                       class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                       width="100%">
                                    <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th>DP Number</th>
                                        <th>Supplier</th>
                                        <th>Note</th>
                                        <th>Items</th>
                                        <th>Month</th>
                                        <th>Date</th>
                                        <th>Created By</th>
                                        <th>Value</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>

                            </form>
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
    setTimeout(() => drawPurchaseDataTables(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    const monthFilter = $('#purchaseMonth');
    const yearFilter = $('#purchaseYear');
    const purchaseClassFilter = $('#purchaseClass');
    const expenseCategoryFilter = $('#expenseCategory');
    const fromDateFilter = $('#fromDate');
    const toDateFilter = $('#toDate');

    $('.filter, .select2').select2({ allowClear: true });

    $('.filter, .filter2').change(() => {

        console.table({
            monthFilter: monthFilter.val(),
            yearFilter: yearFilter.val(),
            purchaseClassFilter: purchaseClassFilter.val(),
            expenseCategoryFilter: expenseCategoryFilter.val(),
            fromDateFilter: fromDateFilter.val(),
            toDateFilter: toDateFilter.val(),
        });

        $('#purchasesTbl').DataTable().destroy();
        drawPurchaseDataTables();
    })

    const clearFilters = $('#clearFilters');


    clearFilters.click(() => {

        monthFilter.val('').trigger('change');
        yearFilter.val('').trigger('change');
        purchaseClassFilter.val('').trigger('change');
        expenseCategoryFilter.val('').trigger('change');
        fromDateFilter.val('');
        toDateFilter.val('');

        $('#purchasesTbl').DataTable().destroy();
        drawPurchaseDataTables();
    })

    // Handle 'Select All' functionality
    $('#select-all').on('click', function () {
        let isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
    });

    // Gather all checkbox values on form submission
    $('#checkbox-form').on('submit', function (e) {
        // Prevent default submission to handle confirmation
        e.preventDefault();

        // Show confirmation dialog
        if (!confirm('Are You Sure You Want To Reclassify These Purchases?')) {
            // If user cancels, stop the form submission
            return false;
        }

        // Gather all selected checkboxes
        let selected = [];
        $('.row-checkbox:checked').each(function () {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            alert('Please select at least one purchase.');
            return false; // Prevent submission if no checkboxes are selected
        }

        // Add selected checkboxes as a hidden input to the form
        let form = $(this);
        let input = $('<input>')
            .attr('type', 'hidden')
            .attr('name', 'selected_items')
            .val(JSON.stringify(selected));
        form.append(input);

        // Submit the form
        form.off('submit').submit(); // Remove the event listener and allow submission
    });

    function drawPurchaseDataTables() {

        $('#purchasesTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: '{{ route("biller.purchase-class-reclassify") }}',
                type: 'get',
                data: {
                    monthFilter: monthFilter.val(),
                    yearFilter: yearFilter.val(),
                    purchaseClassFilter: purchaseClassFilter.val(),
                    expenseCategoryFilter: expenseCategoryFilter.val(),
                    fromDateFilter: fromDateFilter.val(),
                    toDateFilter: toDateFilter.val(),
                }
            },
            columns: [
                {
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false
                },
                {data: 'p_number', name: 'p_number'},
                {data: 'supplier', name: 'supplier'},
                {data: 'note', name: 'note'},
                {data: 'items', name: 'items'},
                {data: 'month', name: 'month'},
                {data: 'date', name: 'date'},
                {data: 'created_by', name: 'created_by'},
                {data: 'total', name: 'total'},
                // Add other columns as needed
            ],
            order: [[4, 'desc']],
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
                    url: '{{ route("biller.purchase-class-breviary-callback") }}',
                    type: 'get',
                    data: {
                        monthFilter: monthFilter.val(),
                        yearFilter:yearFilter.val(),
                        purchaseClassFilter: purchaseClassFilter.val(),
                        expenseCategoryFilter: expenseCategoryFilter.val(),
                        fromDateFilter: fromDateFilter.val(),
                        toDateFilter: toDateFilter.val(),
                    },
                    success: function (response) {

                        $('#purchasesValue').text(accounting.formatNumber(response.total));
                        $('#purchasesCount').text(response.count);
                        $('#monthBudget').text(response.monthBudget);
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