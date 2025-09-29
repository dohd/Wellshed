@extends ('core.layouts.app')

@section ('title', 'Recent Customers')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Recent Customers </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.recentCustomers.header-buttons')
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

                                <div class="col-9 col-lg-4">
                                    <label for="employee" >Filter Recent Customers After Date</label>
                                    <input type="date" id="from_date_filter" class="form-control filter" value="{{$lowerDateLimit}}"/>
                                </div>

                                <div class="col-9 col-lg-4">
                                    <label for="employee" >Filter Recent Customers To Date</label>
                                    <input type="date" id="to_date_filter" class="form-control filter" value="{{$upperDateLimit}}"/>
                                </div>

                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>

                            </div>

                            <table id="pcTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>Last Invoice</th>
                                        <th>Inv. Title</th>
                                        <th>Inv. Date</th>
                                        <th>Inv. Value</th>
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

    const lowerDateFilter = $('#from_date_filter');
    const upperDateFilter = $('#to_date_filter');

    // $('.filter').select2({ allowClear: true });

    $('.filter').change(() => {

        $('#pcTable').DataTable().destroy();
        draw_data();
    })

    const clearFilters = $('#clearFilters');


    clearFilters.click(() => {

        lowerDateFilter.val('');
        upperDateFilter.val('');

        $('#pcTable').DataTable().destroy();
        draw_data();
    })


    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#pcTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.recent-customers.index") }}',
                type: 'GET',
                data: {

                    lowerDateFilter: lowerDateFilter.val(),
                    upperDateFilter: upperDateFilter.val(),
                }
            },
            columns: [
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'last_invoice',
                    name: 'last_invoice'
                },
                {
                    data: 'last_invoice_title',
                    name: 'last_invoice_title'
                },
                {
                    data: 'last_invoice_date',
                    name: 'last_invoice_date'
                },
                {
                    data: 'last_invoice_value',
                    name: 'last_invoice_value'
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
</script>
@endsection