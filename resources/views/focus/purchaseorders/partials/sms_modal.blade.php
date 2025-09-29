<div class="modal fade" id="smsModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Send SMS</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::open(['route' => 'biller.purchaseorders.send_single_sms', 'method' => 'POST']) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">SMS To</label>
                        <input type="hidden" name="id" value="{{$purchaseorder->id}}" id="">
                        <input type="text" name="sms_to" id="sms_to" class="form-control" value="{{@$purchaseorder->supplier->phone}}" placeholder="2547..... or 07...">
                        
                    </div>
                    <div class="form-group">
                        <label for="reason">Subject</label>
                        {{ Form::textarea('subject', null, ['class' => 'form-control', 'rows' => 4]) }}
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