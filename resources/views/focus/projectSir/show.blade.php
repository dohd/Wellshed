@php use App\Models\financialYear\FinancialYear; @endphp
@extends('core.layouts.app')

@section('title', 'Project Materials Report')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-2">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">Project Materials Report</h4>

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

                <div class="card-content">

                    <div class="card-body">
                        <div class="mb-1">

                            <div class="row mb-2">


                                <div class="col-9 col-lg-3">
                                    <label for="categoryFilter" >Filter by Product Category</label>
                                    <select id="categoryFilter" class="custom-select round select2 filter" data-placeholder="Select a Product Category" >
                                        <option value="">Select a Product Category</option>
                                        @foreach ($productCategories as $cat)
                                                <option
                                                        value="{{ $cat['id'] }}"
                                                        @if(@$filterValues['categoryFilter'] == $cat['id']) selected @endif
                                                >
                                                    {{ $cat->title }}
                                                </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-9 col-lg-3">
                                    <label for="fromDateFilter" >Filter from Date</label>
                                    <input type="date" id="fromDateFilter" class="form-control box-size filter"
                                           @if(@$filterValues['fromDateFilter']) value="{{ (new DateTime(@$filterValues['fromDateFilter']))->format('Y-m-d') }}" @endif
                                    >
                                </div>

                                <div class="col-9 col-lg-3">
                                    <label for="toDateFilter" >Filter to Date</label>
                                    <input type="date" id="toDateFilter" class="form-control box-size filter"
                                           @if(@$filterValues['toDateFilter']) value="{{ (new DateTime(@$filterValues['toDateFilter']))->format('Y-m-d') }}" @endif
                                    >
                                </div>

                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>


                            </div>

                            <div class="row mt-1">
                                <p class="col-12" style="font-size: 16px;"><span id="projectDetails" style="font-size: 25px;"></span></p>
                                <p class="col-6 col-lg-3" style="font-size: 16px;">Quantity: <span id="filteredQuantity" style="font-size: 25px; font-weight: bold"></span></p>
                                <p class="col-6 col-lg-3" style="font-size: 16px;">Stock Value: <span id="filteredValue" style="font-size: 25px; font-weight: bold"></span></p>
                            </div>

                        </div>
                        <table id="sirTable"
                               class="table table-striped table-bordered zero-configuration" cellspacing="0"
                               width="100%">
                            <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Issuances</th>
                                <th>Quantity</th>
                                <th>Value</th>
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

                drawSirTable();
            }, {{config('master.delay')}});
        });

        const categoryFilter = $('#categoryFilter');
        const fromDateFilter = $('#fromDateFilter');
        const toDateFilter = $('#toDateFilter');

        const clearFilters = $('#clearFilters');

        $('.select2').select2();

        $('.filter').change(() => {
            $('#sirTable').DataTable().destroy();
            drawSirTable();
        })

        clearFilters.click(() => {

            categoryFilter.val('').trigger('change');
            fromDateFilter.val('');
            toDateFilter.val('');

            $('#sirTable').DataTable().destroy();
            drawSirTable();
        })


        function drawSirTable() {

            $('#sirTable').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.project-sir-specifics-table") }}',
                    type: 'get',
                    data: {

                        projectFilter: @json($projectId),
                        fromDateFilter: fromDateFilter.val(),
                        toDateFilter: toDateFilter.val(),
                        categoryFilter: categoryFilter.val(),
                    }
                },
                columns: [
                    {data: 'product', name: 'product'},
                    {data: 'category', name: 'category'},
                    {data: 'issuances', name: 'issuances'},
                    {data: 'filteredQuantity', name: 'filteredQuantity'},
                    {data: 'filteredValue', name: 'filteredValue'},
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
                        url: '{{ route("biller.get-sir-specifics-summary") }}', // Update with your server endpoint
                        type: 'GET',
                        data: {

                            projectFilter: @json($projectId),
                            fromDateFilter: fromDateFilter.val(),
                            toDateFilter: toDateFilter.val(),
                            categoryFilter: categoryFilter.val(),
                        },
                        success: function (response) {

                            $('#projectDetails').html(response.projectDetails);

                            $('#filteredQuantity').text(accounting.formatNumber(response.totals.filteredQuantityGrandTotal));

                            $('#filteredValue').text(accounting.formatNumber(response.totals.filteredGrandTotal));
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
