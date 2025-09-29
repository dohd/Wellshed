@extends ('core.layouts.app')

@section ('title', 'Promotional Codes')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Promotional Codes </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.promotional_codes.header-buttons')
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
                                    <label for="promoTypeFilter">Status</label>
                                    <select id="promoTypeFilter" class="form-control filter" data-placeholder="Filter by Promo Type">

                                        <option value="">Filter by Promo Type</option>
                                        <option value="specific_products"> Specific Products </option>
                                        <option value="product_categories"> Product Categories </option>
                                        <option value="description_promo"> Descriptive Promotion </option>
                                    </select>
                                </div>


                                <div class="col-9 col-lg-3">
                                    <label for="fromDateFilter" >Filter From Date</label>
                                    <input type="date" id="fromDateFilter" class="form-control filter"/>
                                </div>

                                <div class="col-9 col-lg-3">
                                    <label for="untilDateFilter" >Filter Until Date</label>
                                    <input type="date" id="untilDateFilter" class="form-control filter"/>
                                </div>

                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>

                            </div>

                            <table id="promoTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Promo Type</th>
                                        {{-- <th>Promo Link</th> --}}
                                        <th>Description</th>
                                        <th>Products</th>
                                        <th>Categories</th>
                                        <th>Valid From</th>
                                        <th>Valid Until</th>
                                        <th>Discount Type</th>
                                        <th>Tier 1 Value</th>
                                        <th>Tier 2 Value</th>
                                        <th>Tier 3 Value</th>
                                        <th>Usage Limit</th>
                                        <th>Reserved</th>
                                        <th>Used</th>
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

    const promoTypeFilter = $('#promoTypeFilter');
    const fromDateFilter = $('#fromDateFilter');
    const untilDateFilter = $('#untilDateFilter');


    $('.filter').change(() => {

        $('#promoTable').DataTable().destroy();
        draw_data();
    })

    const clearFilters = $('#clearFilters');


    clearFilters.click(() => {

        promoTypeFilter.val('');
        fromDateFilter.val('');
        untilDateFilter.val('');

        $('#promoTable').DataTable().destroy();
        draw_data();
    })


    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#promoTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.promotional-codes.index") }}',
                type: 'GET',
                data: {

                    promoTypeFilter: promoTypeFilter.val(),
                    fromDateFilter: fromDateFilter.val(),
                    untilDateFilter: untilDateFilter.val(),
                }
            },
            columns: [
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'promo_type',
                    name: 'promo_type'
                },
                // {
                //     data: 'promo_link',
                //     name: 'promo_link'
                // },
                {
                    data: 'description',
                    name: 'description'
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
                    data: 'valid_from',
                    name: 'valid_from'
                },
                {
                    data: 'valid_until',
                    name: 'valid_until'
                },
                {
                    data: 'discount_type',
                    name: 'discount_type'
                },
                {
                    data: 'discount_value',
                    name: 'discount_value'
                },
                {
                    data: 'discount_value_2',
                    name: 'discount_value_2'
                },
                {
                    data: 'discount_value_3',
                    name: 'discount_value_3'
                },
                {
                    data: 'usage_limit',
                    name: 'usage_limit'
                },
                {
                    data: 'reservations_count',
                    name: 'reservations_count'
                },
                {
                    data: 'used_count',
                    name: 'used_count'
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
        if (confirm('Are you sure you want to delete this promotional code? \nAll related promo code reservations will be deleted!')) {
            // If confirmed, redirect to the delete URL
            window.location.href = deleteUrl;
        }
    });
</script>
@endsection

