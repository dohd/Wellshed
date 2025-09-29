@extends ('core.layouts.app')

@section ('title', 'Client Feedback')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Client Feedback </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.client_feedback.header-buttons')
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

                            <div class="row mb-2">

                                <div class="col-9 col-lg-3">
                                    <label for="categoryFilter">Type</label>
                                    <select id="categoryFilter" class="form-control filter" data-placeholder="Filter by Category">

                                        <option value="">Filter by Category</option>
                                        <option value="Customer Direct Message"> Redeem code /Others </option>
                                        <option value="Complaint"> Complaint </option>
                                        <option value="Quality Concern"> Quality Concern </option>
                                    </select>
                                </div>


                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>

                            </div>

                            <table id="feedbackTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Category</th>
                                    <th>Message</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
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

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    const categoryFilter = $('#categoryFilter');


    $('.filter').change(() => {

        $('#feedbackTable').DataTable().destroy();
        draw_data();
    })

    const clearFilters = $('#clearFilters');


    clearFilters.click(() => {

        categoryFilter.val('');

        $('#feedbackTable').DataTable().destroy();
        draw_data();
    })


    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#feedbackTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.client-feedback.index") }}',
                type: 'GET',
                data: {

                    categoryFilter: categoryFilter.val(),
                }
            },
            columns: [
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'details',
                    name: 'details'
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
            buttons: ['csv', 'excel', 'print'],
        });
    }

    $(document).on('click', '.delete', function(e) {

        e.preventDefault(); // Prevent the default link behavior

        var deleteUrl = $(this).attr('href'); // Get the delete URL from the href attribute

        // Show a confirmation dialog
        if (confirm('Are you sure you want to delete this client feedback? \nAll related promo code reservations will be deleted!')) {
            // If confirmed, redirect to the delete URL
            window.location.href = deleteUrl;
        }
    });
</script>

@endsection

