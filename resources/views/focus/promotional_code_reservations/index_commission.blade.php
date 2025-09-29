@extends ('core.layouts.app')

@section ('title', 'Manage Commisions')

@section('page-header')
    <h1>{{ 'Manage  Commisions' }}</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Manage  Commisions' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            {{-- @include('focus.send_sms.partials.sms_setting-header-buttons') --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row form-group">
                                    <div class="col-4">

                                    <select id="source_filter" class="form-control">
                                        <option value="">All</option>
                                        <option value="quote">Quoted</option>
                                        <option value="invoice">Invoiced</option>
                                    </select>
                                </div>

                                <div class="col-4">
                                    <select id="paid_filter" class="form-control">
                                        <option value="">Default</option>
                                        <option value="due">Due</option>
                                        <option value="paid">Paid</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <button id="open-selected" class="btn btn-primary">Open Selected</button>

                                </div>

                            </div>

                            <div class="card-content">

                                <div class="card-body">
                                    <table id="send_sms-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th></th>
                                            <th>Redeemable Code</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Tier</th>
                                            <th>Status</th>
                                            <th>Commission</th>
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
    </div>
@endsection

@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        const config = {};
        const Index = {
            table: null,

            init() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // Set up change listeners for filters
                $('#source_filter').on('change', function () {
                    Index.table.ajax.reload();
                });

                $('#paid_filter').on('change', function () {
                    Index.table.ajax.reload();
                });
                $('#open-selected').on('click', function () {
                    // Get all checked checkboxes that are NOT disabled
                    const selectedUUIDs = $('.select-row:checked:not(:disabled)').map(function () {
                        return this.value;
                    }).get();

                    if (selectedUUIDs.length === 0) {
                        alert('Please select at least one item.');
                        return;
                    }

                    // Redirect with UUIDs in query string or via POST (as needed)
                    const url = '/promotions/create_commision_pay?uuids=' + encodeURIComponent(selectedUUIDs.join(','));
                    window.location.href = url;
                });


                Index.drawDataTable(); // Only initialize once
            },

            drawDataTable() {
                Index.table = $('#send_sms-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: @json(__('datatable.strings')),
                    ajax: {
                        url: '{{ route("biller.promotions.get_reservations") }}',
                        type: 'POST',
                        data: function (d) {
                            d.source = $('#source_filter').val();      // 'quote', 'invoice', or ''
                            d.paid = $('#paid_filter').val(); // true or false
                        }
                    },
                    columns: [
                        {data: 'DT_Row_Index', name: 'id'},
                        {data: 'checkbox', name: 'checkbox'},
                        {data: 'redeemable_code', name: 'redeemable_code'},
                        {data: 'name', name: 'name'},
                        {data: 'email', name: 'email'},
                        {data: 'phone', name: 'phone'},
                        {data: 'tier', name: 'tier'},
                        {data: 'status', name: 'status'},
                        {data: 'commision', name: 'commision'}
                        // {data: 'actions', name: 'actions', searchable: false, sortable: false}
                    ],
                    order: [[0, "desc"]],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print']
                });
            },
        };

        $(Index.init);
    </script>
@endsection

