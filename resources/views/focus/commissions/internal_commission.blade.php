@extends ('core.layouts.app')

@section ('title', 'Manage Internal Commisions')

@section('page-header')
    <h1>{{ 'Manage Internal Commisions' }}</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Manage Internal Commisions' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            {{-- @include('focus.commission.partials.sms_setting-header-buttons') --}}
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

                                    <select id="promotional_code" class="form-control" data-placeholder="Search PromoCode">
                                        <option value="">Search PromoCode</option>
                                        @foreach ($promotional_codes as $promo)
                                            <option value="{{ $promo->id }}">{{ $promo->code }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-4">
                                    <select id="payment_status" class="form-control">
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
                                    <table id="commission-table"
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
    {{ Html::script('focus/js/select2.min.js') }}
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
                $('#promotional_code').select2({allowClear: true});

                // Set up change listeners for filters
                $('#promotional_code').on('change', function () {
                    Index.table.ajax.reload();
                });

                $('#payment_status').on('change', function () {
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
                    const url = '/commissions/create_commision_pay?ids=' + encodeURIComponent(selectedUUIDs.join(','));
                    window.location.href = url;
                });

                $(document).on('click', '#select-all', function () {
                    const isChecked = $(this).is(':checked');
                    $('.select-row:not(:disabled)').prop('checked', isChecked);
                });

                Index.drawDataTable(); // Only initialize once
            },

            drawDataTable() {
                Index.table = $('#commission-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: @json(__('datatable.strings')),
                    ajax: {
                        url: '{{ route("biller.commissions.get_internal_commission") }}',
                        type: 'POST',
                        data: function (d) {
                            d.promotional_code = $('#promotional_code').val();      // 'quote', 'invoice', or ''
                            d.payment_status = $('#payment_status').val(); // true or false
                        }
                    },
                    columns: [
                        {data: 'DT_Row_Index', name: 'id'},
                        {
                            data: 'checkbox',
                            name: 'checkbox',
                            orderable: false,
                            searchable: false,
                            title: '<input type="checkbox" id="select-all">'
                        },
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

