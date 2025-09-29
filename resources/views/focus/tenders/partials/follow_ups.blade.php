<div class="modal fade" id="followUpModal" tabindex="-1" role="dialog" aria-labelledby="followUpModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($tender, ['route' => array('biller.tender.store_follow_ups', $tender), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient">Recipient Name</label>
                       <input type="text" name="recipient" class="form-control" id="">
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        {{ Form::text('date', null, ['class' => 'form-control datepicker' ]) }}
                    </div>
                    <div class="form-group">
                        <label for="reminder_date">Next Call Date</label>
                        {{ Form::text('reminder_date', null, ['class' => 'form-control datepicker']) }}
                    </div>
                    <div class="form-group">
                        <label for="remark">Remark</label>
                        {{ Form::textarea('remark', null, ['class' => 'form-control']) }}
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