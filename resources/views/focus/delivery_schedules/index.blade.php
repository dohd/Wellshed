@extends ('core.layouts.app')

@section ('title', 'Delivery Schedule Management')

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
                                            <th>Delivery Days</th>
                                            <th>Delivery Date</th>
                                            <th>Status</th>
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
{{ Html::script(mix('js/dataTable.js')) }}

<script>
    $(document).ready(function () {
        const table = initDeliverySchedulesTable();

        // Handle status button click
        $(document).on('click', '.btn-change-status', function () {
            const id = $(this).data('id');
            const currentStatus = $(this).data('status');
            console.log(id)
            $('#status_id').val(id);
            $('#status_current').val(currentStatus);
            $('#statusModal').modal('show');
        });

        // Handle status form submission
        $('#statusForm').on('submit', function (e) {
            e.preventDefault();
            const id = $('#status_id').val();
            const status = $('#status_new').val();

            $.ajax({
                url: '{{ route("biller.delivery_schedules.update_status") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id,
                    status
                },
                beforeSend: () => $('#saveStatusBtn').prop('disabled', true),
                success: function (response) {
                    $('#statusModal').modal('hide');
                    $('#saveStatusBtn').prop('disabled', false);
                    table.ajax.reload(null, false); // reload without resetting pagination
                    // toastr.success('Status updated successfully!');
                },
                error: function (xhr) {
                    $('#saveStatusBtn').prop('disabled', false);
                    // toastr.error('Failed to update status.');
                }
            });
        });
    });

    /**
     * Initialize DataTable
     */
    function initDeliverySchedulesTable() {
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });

        return $('#delivery_schedules-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searchDelay: 500,
            order: [[0, 'desc']],
            language: @json(__('datatable.strings')),
            ajax: {
                url: '{{ route("biller.delivery_schedules.get") }}',
                type: 'POST'
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'tid', name: 'tid'},
                {data: 'order', name: 'order'},
                {data: 'customer', name: 'customer'},
                {data: 'delivery_days', name: 'delivery_days'},
                {data: 'delivery_date', name: 'delivery_date'},
                {
                    data: 'status',
                    name: 'status',
                    render: function (data, type, row) {
                        const badgeClass = data === 'Delivered'
                            ? 'badge-success'
                            : (data === 'Scheduled' ? 'badge-warning' : 'badge-secondary');
                        return `
                            <span class="badge ${badgeClass}">${data}</span>
                            <button class="btn btn-sm btn-outline-primary btn-change-status"
                                    data-id="${row.id}" data-status="${data}">
                                Change
                            </button>
                        `;
                    }
                },
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            dom: 'Blfrtip',
            buttons: [
                {extend: 'csv', footer: true, exportOptions: {columns: ':visible'}},
                {extend: 'excel', footer: true, exportOptions: {columns: ':visible'}},
                {extend: 'print', footer: true, exportOptions: {columns: ':visible'}}
            ]
        });
    }
</script>

{{-- Status Update Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
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
                <div class="form-group">
                    <label>Current Status</label>
                    <input type="text" class="form-control" id="status_current" readonly>
                </div>
                <div class="form-group">
                    <label>New Status</label>
                    <select class="form-control" id="status_new" name="status" required>
                        <option value="">Select Status</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="en_route">En Route</option>
                        {{-- <option value="delivered">Delivered</option> --}}
                        <option value="cancelled">Cancelled</option>
                        <option value="failed">Failed</option>
                    </select>
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

