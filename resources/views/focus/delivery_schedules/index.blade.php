@extends ('core.layouts.app')

@section('title', 'Delivery Schedule Management')

@section('page-header')
    <h1>Delivery Schedule Management</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Delivery Schedule Management</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.delivery_schedules.partials.delivery_schedules-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <form id="filters" class="row g-2">

                                    {{-- Mode: single / range --}}
                                    <div class="col-12 d-flex align-items-center gap-3 mb-1">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="mode" id="mode_single"
                                                value="single" checked>
                                            <label class="form-check-label" for="mode_single">Single date</label>
                                        </div>
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="mode" id="mode_range"
                                                value="range">
                                            <label class="form-check-label" for="mode_range">Date range</label>
                                        </div>
                                    </div>

                                    {{-- Single date --}}
                                    <div class="col-md-3" id="single_date_wrap">
                                        <label for="delivery_date" class="form-label mb-1">Delivery date</label>
                                        <input type="date" class="form-control" id="delivery_date" name="delivery_date">
                                    </div>

                                    {{-- Range --}}
                                    <div class="col-md-3 d-none" id="range_start_wrap">
                                        <label for="start_date" class="form-label mb-1">Start date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date">
                                    </div>
                                    <div class="col-md-3 d-none" id="range_end_wrap">
                                        <label for="end_date" class="form-label mb-1">End date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date">
                                    </div>

                                    {{-- Extra filters --}}
                                    <div class="w-100"></div>

                                    <div class="col-md-3">
                                        <label for="order_no" class="form-label mb-1">Order No</label>
                                        <select name="order_no" id="order_no" class="form-control" data-placeholder="Search Order">
                                            <option value="">Search Order</option>
                                            @foreach ($orders as $order)
                                                <option value="{{ $order->id }}">{{ gen4tid('ORD-',$order->tid).' '.$order->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="customer" class="form-label mb-1">Customer</label>
                                            <select name="customer" id="customer" class="form-control" data-placeholder="Search Customer">
                                            <option value="">Search Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->company ?: $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- <div class="col-md-3">
                                        @php
                                            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Sarturday','Sunday'];
                                        @endphp
                                        <label for="delivery_days" class="form-label mb-1">Delivery Days</label>
                                            <select name="delivery_days" id="delivery_days" class="form-control">
                                                <option value="">Select a day</option>
                                                @foreach($days as $day)
                                                    <option value="{{ $day }}">{{ $day }}</option>
                                                @endforeach
                                            </select>
                                    </div> --}}

                                    <div class="col-md-3 d-flex align-items-end gap-2 mt-2">
                                        <button type="button" id="applyFilters" class="btn btn-primary me-2">Apply</button>
                                        <button type="button" id="resetFilters"
                                            class="btn btn-outline-secondary">Reset</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    <table id="delivery_schedules-table"
                                        class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                        width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Schedule No.</th>
                                                <th>Order</th>
                                                <th>Customer</th>
                                                <th>Delivery Date</th>
                                                <th>Status</th>
                                                <th>Location</th>
                                                <th>{{ trans('labels.general.actions') }}</th>
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
    {{-- Your global DataTables bundle --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>
        // ---------- UI helpers ----------
        function toggleFilterMode() {
            const isSingle = document.getElementById('mode_single')?.checked;
            const singleWrap = document.getElementById('single_date_wrap');
            const rangeStart = document.getElementById('range_start_wrap');
            const rangeEnd   = document.getElementById('range_end_wrap');

            if (!singleWrap || !rangeStart || !rangeEnd) return;

            singleWrap.classList.toggle('d-none', !isSingle);
            rangeStart.classList.toggle('d-none', isSingle);
            rangeEnd.classList.toggle('d-none', isSingle);

            if (isSingle) {
                $('#start_date, #end_date').val('');
            } else {
                $('#delivery_date').val('');
            }
            $('#order_no').val(null).trigger('change');
            $('#customer').val(null).trigger('change');

        }

        $(document).ready(function () {
            // Init table
            const table = initDeliverySchedulesTable();

            $('#order_no').select2({allowClear:true});
            $('#customer').select2({allowClear:true});
            // Filter events
            $('#mode_single, #mode_range').on('change', toggleFilterMode);

            $('#applyFilters').on('click', function () {
                const rangeMode = $('#mode_range').is(':checked');
                if (rangeMode) {
                    const s = $('#start_date').val();
                    const e = $('#end_date').val();
                    if (s && e && new Date(s) > new Date(e)) {
                        alert('Start date cannot be after end date.');
                        return;
                    }
                }
                table.ajax.reload(null, false);
            });

            $('#resetFilters').on('click', function () {
                $('#filters')[0]?.reset();
                toggleFilterMode();
                table.ajax.reload(null, false);
            });

            // Apply on Enter inside text inputs
            $('#order_no, #customer, #delivery_days').on('keypress', function(e){
                if (e.which === 13) $('#applyFilters').click();
            });

            // Initial UI state
            toggleFilterMode();

            // Status change click
            $(document).on('click', '.btn-change-status', function() {
                const id = $(this).data('id');
                const currentStatus = $(this).data('status');
                $('#status_id').val(id);
                $('#status_current').val(currentStatus);
                if(currentStatus == 'Delivered'){
                    $('#saveStatusBtn').prop('disabled', true);
                }
                $('#statusModal').modal('show');
            });

            // Status form submit
            $('#statusForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#status_id').val();
                const status = $('#status_new').val();
                const status_note = $('#status_note').val();

                $.ajax({
                    url: '{{ route('biller.delivery_schedules.update_status') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id,
                        status,
                        status_note
                    },
                    beforeSend: () => $('#saveStatusBtn').prop('disabled', true),
                    success: function() {
                        $('#statusModal').modal('hide');
                        $('#saveStatusBtn').prop('disabled', false);
                        table.ajax.reload(null, false);
                    },
                    error: function() {
                        $('#saveStatusBtn').prop('disabled', false);
                    }
                });
            });
        });

        // ---------- DataTable ----------
        function initDeliverySchedulesTable() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            return $('#delivery_schedules-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                searchDelay: 500,
                order: [[0, 'desc']],
                language: @json(__('datatable.strings')),
                ajax: {
                    url: '{{ route('biller.delivery_schedules.get') }}',
                    type: 'POST',
                    data: function (d) {
                        // Date filters
                        const isSingle = document.getElementById('mode_single')?.checked;
                        if (isSingle) {
                            d.delivery_date = $('#delivery_date').val() || null;
                            d.start_date = null;
                            d.end_date   = null;
                        } else {
                            d.delivery_date = null;
                            d.start_date = $('#start_date').val() || null;
                            d.end_date   = $('#end_date').val() || null;
                        }

                        // Extra filters
                        d.order_no      = $('#order_no').val() || null;
                        d.customer      = $('#customer').val() || null;
                        d.delivery_days = $('#delivery_days').val() || null;
                    }
                },
                columns: [
                    { data: 'DT_Row_Index', name: 'id' },
                    { data: 'tid',          name: 'tid' },
                    { data: 'order',        name: 'order' },
                    { data: 'customer',     name: 'customer' },
                    { data: 'delivery_date',name: 'delivery_date' },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            const norm = (data || '').toString().toLowerCase();
                            const map = {
                                'delivered': 'badge-success',
                                'scheduled': 'badge-warning',
                                'en route': 'badge-info',
                                'en_route': 'badge-info',
                                'cancelled': 'badge-danger',
                                'failed': 'badge-secondary'
                            };
                            const badgeClass = map[norm] || 'badge-secondary';
                            const label = data || '—';
                            return `
                                <span class="badge ${badgeClass}">${label}</span>
                                <button class="btn btn-sm btn-outline-primary btn-change-status"
                                        data-id="${row.id}" data-status="${label}">
                                    Change
                                </button>
                            `;
                        }
                    },
                    { data: 'location',name: 'location' },
                    { data: 'actions', name: 'actions', searchable: false, sortable: false }
                ],
                dom: 'Blfrtip',
                buttons: [
                    { extend: 'csv',   footer: true, exportOptions: { columns: ':visible' } },
                    { extend: 'excel', footer: true, exportOptions: { columns: ':visible' } },
                    { extend: 'print', footer: true, exportOptions: { columns: ':visible' } }
                ]
            });
        }
    </script>

    {{-- ===================== STATUS MODAL ===================== --}}
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="statusForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Delivery Status</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="status_id" name="id">
                        <div class="form-group mb-2">
                            <label>Current Status</label>
                            <input type="text" class="form-control" id="status_current" readonly>
                        </div>
                        <div class="form-group">
                            <label>New Status</label>
                            <select class="form-control" id="status_new" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Scheduled">Scheduled</option>
                                <option value="en_route">En Route</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="failed">Failed</option>
                                {{-- If you need Delivered selectable:
                                <option value="delivered">Delivered</option>
                                --}}
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="status_note" class="form-label">Remarks</label>
                            <textarea name="status_note" id="status_note" cols="30" rows="10" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" id="saveStatusBtn" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
