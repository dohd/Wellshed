<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($lead, ['route' => ['biller.leads.update_status', $lead], 'method' => 'PATCH' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" name="status" id="status">
                            @foreach (['Open', 'Closed'] as $i => $val)
                                <option value="{{ $i }}" {{ $i == $lead->status? 'selected' : '' }}>
                                    {{ $val }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <select class="form-control" name="reason" id="reason">
                            @foreach (['new', 'won', 'lost'] as $val)
                                <option value="{{ $val }}" {{ $val == $lead->reason? 'selected' : '' }}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea name="note" id="note" class="form-control"></textarea>
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