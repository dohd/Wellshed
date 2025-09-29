@extends ('core.layouts.app')

@section ('title', 'Promotional Code Reservations')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Promotional Code Reservations </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.promotional_code_reservations.header-buttons')
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
                                    <label for="typeFilter">Type</label>
                                    <select id="typeFilter" class="form-control filter" data-placeholder="Filter by Type">

                                        <option value="">Filter by Type</option>
                                        <option value="customers"> Customers </option>
                                        <option value="third_parties"> Third Parties </option>
                                        <option value="referrals"> Referrals </option>
                                    </select>
                                </div>

                                <div class="col-9 col-lg-2">
                                    <label for="statusFilter">Reservation Status</label>
                                    <select id="statusFilter" class="form-control filter" data-placeholder="Filter by Reservation Status">

                                        <option value="">Filter by Status</option>
                                        @foreach(['reserved', 'used', 'expired', 'cancelled'] as $status)
                                            <option value="{{ $status }}"> {{ ucfirst($status) }} </option>
                                        @endforeach

                                    </select>
                                </div>


                                <div class="col-9 col-lg-3">
                                    <label for="reservedDateFilter" > Date Reserved </label>
                                    <input type="date" id="reservedDateFilter" class="form-control filter"/>
                                </div>

                                <div class="col-9 col-lg-3">
                                    <label for="expiryDateFilter" > Expiry Date </label>
                                    <input type="date" id="expiryDateFilter" class="form-control filter"/>
                                </div>

                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>

                            </div>

                            <table id="reservationsTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Code</th>
                                    <th>Products</th>
                                    <th>Categories</th>
                                    <th>Date Reserved</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Referred By</th>
                                    <th>Reserved By</th>
                                    <th></th>
                                    <th>Created At</th>
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

    const statusFilter = $('#statusFilter');
    const typeFilter = $('#typeFilter');
    const reservedDateFilter = $('#reservedDateFilter');
    const expiryDateFilter = $('#expiryDateFilter');


    $('.filter').change(() => {

        $('#reservationsTable').DataTable().destroy();
        draw_data();
    })

    const clearFilters = $('#clearFilters');


    clearFilters.click(() => {

        statusFilter.val('');
        typeFilter.val('');
        reservedDateFilter.val('');
        expiryDateFilter.val('');

        $('#reservationsTable').DataTable().destroy();
        draw_data();
    })


    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#reservationsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.reserve-promo-codes.index") }}',
                type: 'GET',
                data: {

                    statusFilter: statusFilter.val(),
                    typeFilter: typeFilter.val(),
                    reservedDateFilter: reservedDateFilter.val(),
                    expiryDateFilter: expiryDateFilter.val(),
                }
            },
            columns: [
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'products',
                    name: 'products'
                },
                {
                    data: 'categories',
                    name: 'categories'
                },
                {
                    data: 'reserved_at',
                    name: 'reserved_at'
                },
                {
                    data: 'expires_at',
                    name: 'expires_at'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'referred_by',
                    name: 'referred_by'
                },
                {
                    data: 'reserved_by',
                    name: 'reserved_by'
                },
                {
                    data: 'created_at_sort',
                    name: 'created_at_sort',
                    visible: false, // hide the sort key column
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    orderData: [9],
                },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
            ],
            columnDefs: [
                { type: "custom-date-sort", targets: [9] },
            ],
            order: [
                [9, "desc"]
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
        if (confirm('Are you sure you want to delete this promotional code? \nAll related promo code reservations will be deleted!')) {
            // If confirmed, redirect to the delete URL
            window.location.href = deleteUrl;
        }
    });
</script>

@endsection

