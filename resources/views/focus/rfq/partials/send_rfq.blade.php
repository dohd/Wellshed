<div class="modal fade" id="sendRfqModal" tabindex="-1" role="dialog" aria-labelledby="sendRfqModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Send RFQ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($rfq, ['route' => array('biller.rfq.send_sms_and_email', $rfq), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Send Email or SMS or Both</label>
                        <select class="custom-select" name="send_email_sms" id="send_email_sms">
                            <option value="sms">SMS</option>                       
                            <option value="email">Email</option>                       
                            <option value="both">Both</option>                       
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="supplier">Search Supplier</label>
                        <select name="supplier_ids[]" id="suppliers" data-placeholder="Search Suppliers" class="form-control" multiple>
                            <option value="">Search Suppliers</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{$supplier->id}}">{{$supplier->company}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Send', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>