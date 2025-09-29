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
                                    <label for="clientFilter" >Filter by Client</label>
                                    <select id="clientFilter" class="custom-select round select2 filter" data-placeholder="Select a Client" >
                                        <option value="">Select a Client</option>
                                        @foreach ($clients as $cl)
                                            <option value="{{ $cl['id'] }}"> {{ $cl->company }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-9 col-lg-3">
                                    <label for="projectFilter" >Filter by Project</label>
                                    <select id="projectFilter" class="custom-select round select2 filter" data-placeholder="Select a Project" >
                                        <option value="">Select a Project</option>
                                        @foreach ($projects as $prj)
                                            <option value="{{ $prj['id'] }}"> {{ $prj['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-9 col-lg-3">
                                    <label for="categoryFilter" >Filter by Product Category</label>
                                    <select id="categoryFilter" class="custom-select round select2 filter" data-placeholder="Select a Product Category" >
                                        <option value="">Select a Product Category</option>
                                        @foreach ($productCategories as $cat)
                                            <option value="{{ $cat['id'] }}"> {{ $cat->title }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-9 col-lg-3">
                                    <label for="fromDateFilter" >Filter from Date</label>
                                    <input type="date" id="fromDateFilter" class="form-control box-size filter" >
                                </div>

                                <div class="col-9 col-lg-3">
                                    <label for="toDateFilter" >Filter to Date</label>
                                    <input type="date" id="toDateFilter" class="form-control box-size filter" >
                                </div>

                                <div class="col-3">
                                    <button id="clearNonDateFilters" class="btn btn-facebook round mt-2" > Clear Non-Date Filters </button>
                                </div>

                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear All Filters </button>
                                </div>


                            </div>

                            <div class="row mt-1">

                                <p class="col-6 col-lg-3" style="font-size: 16px;">Filtered Quantity: <span id="filteredQuantity" style="font-size: 25px; font-weight: bold"></span></p>
                                <p class="col-6 col-lg-3" style="font-size: 16px;">Filtered Stock Value: <span id="filteredValue" style="font-size: 25px; font-weight: bold"></span></p>
                                <p class="col-6 col-lg-3" style="font-size: 16px;">All Time Quantity: <span id="allTimeQuantity" style="font-size: 25px; font-weight: bold"></span></p>
                                <p class="col-6 col-lg-3" style="font-size: 16px;">All Time Stock Value: <span id="allTimeTotal" style="font-size: 25px; font-weight: bold"></span></p>
                                <div class="col-6 col-lg-3">
                                    <a id="printSir" href="{{ route('biller.print-project-sir') }}" target="_blank" class="btn btn-primary mt-2" > Print Report </a>
                                </div>

                            </div>

                        </div>
                        <table id="sirTable"
                               class="table table-striped table-bordered zero-configuration" cellspacing="0"
                               width="100%">
                            <thead>
                            <tr>
                                <th>Client</th>
                                <th>Project</th>
                                <th>Filtered Quantity</th>
                                <th>Filtered Value</th>
                                <th>All Time Quantity</th>
                                <th>All Time Value</th>
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
        const clientFilter = $('#clientFilter');
        const projectFilter = $('#projectFilter');

        const clearFilters = $('#clearFilters');
        const clearNonDateFilters = $('#clearNonDateFilters');

        $('.select2').select2();

        $('.filter').change(() => {
            $('#sirTable').DataTable().destroy();
            drawSirTable();
        })

        $('#clientFilter').change(() => {

            projectFilter.val('').trigger('change');

            $('#sirTable').DataTable().destroy();
            drawSirTable();
        })

        clearFilters.click(() => {

            categoryFilter.val('').trigger('change');
            fromDateFilter.val('');
            toDateFilter.val('');
            clientFilter.val('').trigger('change');
            projectFilter.val('').trigger('change');

            $('#sirTable').DataTable().destroy();
            drawSirTable();
        })

        clearNonDateFilters.click(() => {

            categoryFilter.val('').trigger('change');
            clientFilter.val('').trigger('change');
            projectFilter.val('').trigger('change');

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
                    url: '{{ route("biller.project-sir-table") }}',
                    type: 'get',
                    data: {

                        fromDateFilter: fromDateFilter.val(),
                        toDateFilter: toDateFilter.val(),
                        categoryFilter: categoryFilter.val(),
                        clientFilter: clientFilter.val(),
                        projectFilter: projectFilter.val(),
                    }
                },
                columns: [
                    {data: 'client', name: 'client'},
                    {data: 'project', name: 'project'},

                    {data: 'filteredQuantity', name: 'filteredQuantity'},
                    {data: 'filteredValue', name: 'filteredValue'},

                    {data: 'allTimeQuantity', name: 'allTimeQuantity'},
                    {data: 'allTimeValue', name: 'allTimeValue'},
                    // Add other columns as needed
                ],
                order: [],
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
                        url: '{{ route("biller.get-sir-projects-summary") }}', // Update with your server endpoint
                        type: 'GET',
                        data: {

                            fromDateFilter: fromDateFilter.val(),
                            toDateFilter: toDateFilter.val(),
                            categoryFilter: categoryFilter.val(),
                            clientFilter: clientFilter.val(),
                            projectFilter: projectFilter.val(),
                        },
                        success: function (response) {

                            $('#projectDetails').html(response.projectDetails);

                            $('#filteredQuantity').text(accounting.formatNumber(response.totals.filteredQuantityGrandTotal));
                            $('#allTimeQuantity').text(accounting.formatNumber(response.totals.allTimeQuantityGrandTotal));

                            $('#filteredValue').text(accounting.formatNumber(response.totals.filteredGrandTotal));
                            $('#allTimeTotal').text(accounting.formatNumber(response.totals.allTimeGrandTotal));

                            $('#printSir').attr('href', response.printUrl);
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
