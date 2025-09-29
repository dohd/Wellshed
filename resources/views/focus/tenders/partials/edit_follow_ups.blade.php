<div class="modal fade" id="followUpEditModal" tabindex="-1" role="dialog" aria-labelledby="followUpEditModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($tender, ['route' => array('biller.tender.update_follow_ups', $tender), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient">Recipient Name</label>
                       <input type="text" name="recipient" class="form-control" id="recipient">
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        {{ Form::text('date', null, ['class' => 'form-control datepicker','id' => 'follow_up_date' ]) }}
                    </div>
                    <div class="form-group">
                        <label for="reminder_date">Next Call Date</label>
                        {{ Form::text('reminder_date', null, ['class' => 'form-control datepicker', 'id' => 'follow_up_reminder_date']) }}
                    </div>
                    <div class="form-group">
                        <label for="remark">Remark</label>
                        {{ Form::textarea('remark', null, ['class' => 'form-control', 'id' => 'remark']) }}
                    </div>
                    <input type="hidden" name="id" id="follow_up_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>