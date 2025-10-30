@extends ('core.layouts.app')

@section('title', 'Delivery Management')

@section('page-header')
    <h1>Delivery Management</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Delivery Management</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.deliveries.partials.deliveries-header-buttons')
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
                                    <table id="deliveries-table"
                                        class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                        width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Delivery No.</th>
                                                <th>Order</th>
                                                <th>Customer</th>
                                                <th>Delivery Schedule No.</th>
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

    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="changeStatusModalLabel">Change Delivery Status</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changeStatusForm">
                <div class="modal-body">
                    <input type="hidden" id="delivery_id" name="delivery_id">

                    <div class="form-group mb-3">
                        <label for="status" class="form-label">Select New Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="">-- Select Status --</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="status_note" class="form-label">Remarks</label>
                        <textarea name="status_note" id="status_note" cols="30" rows="10" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}

<script>
$(function () {
    // ✅ Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ✅ Initialize table after short delay
    setTimeout(draw_data, {{ config('master.delay') }});

    function draw_data() {
        $('#deliveries-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                @lang('datatable.strings')
            },
            ajax: {
                url: '{{ route("biller.deliveries.get") }}',
                type: 'post'
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'tid', name: 'tid'},
                {data: 'order', name: 'order'},
                {data: 'customer', name: 'customer'},
                {data: 'delivery_schedule', name: 'delivery_schedule'},
                {data: 'date', name: 'date'},
                {data: 'status', name: 'status'},
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "desc"]],
        });

        $('#deliveries-table_wrapper').removeClass('form-inline');
    }

    // ✅ Open modal
    $(document).on('click', '.change-status-btn', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#delivery_id').val(id);
        $('#changeStatusModal').modal('show');
    });

    // ✅ Submit form via AJAX
    $('#changeStatusForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("biller.deliveries.change_status") }}', // adjust route name
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.status == 'success') {
                    $('#changeStatusModal').modal('hide');
                    $('#deliveries-table').DataTable().ajax.reload(null, false);
                    // toastr.success('Delivery status updated successfully');
                } else {
                    // toastr.error('Failed to update status');
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                toastr.error('Something went wrong');
            }
        });
    });
});
</script>
@endsection

