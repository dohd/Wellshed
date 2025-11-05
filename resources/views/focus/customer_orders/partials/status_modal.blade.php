<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($orders, ['route' => ['biller.customer_orders.update_status', $orders], 'method' => 'PATCH' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Order Status</label>
                        <select class="form-control" name="status" id="status">
                            @foreach (['draft','confirmed','started','completed','cancelled','suspended'] as $i => $val)
                                <option value="{{ $val }}" {{ $val == $orders->status ? 'selected' : '' }}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        {{ Form::textarea('reason', null, ['class' => 'form-control', 'rows' => 4]) }}
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