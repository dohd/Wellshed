<div class="modal fade" id="editStatusModal" tabindex="-1" role="dialog" aria-labelledby="editStatusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::open(['route' => ['biller.purchaseorders.update_status'], 'method' => 'post' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" name="approval_status" id="status">
                            @foreach (['pending','approved','rejected','amend'] as $val)
                                <option value="{{ $val }}">
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <input type="hidden" name="id" id="state_id">
                    <div class="form-group">
                        <label for="date">Date</label>
                        {{ Form::text('approved_date', null, ['class' => 'form-control datepicker', 'id' => 'approved_date']) }}
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        {{ Form::textarea('status_note', null, ['class' => 'form-control', 'rows' => 4, 'id' => 'status_note']) }}
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