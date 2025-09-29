<div class="modal fade" id="changeStatusModal" role="dialog" aria-labelledby="changeStatusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($supplier_product, ['route' => array('biller.pricelistsSupplier.change_status', $supplier_product), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Select Status</label>
                        <select class="custom-select" name="status" id="status" data-placeholder="Select Status" required>
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">In Active</option>
                                                       
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>