<div class="modal fade" id="sendInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="sendInvoiceModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Send Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($invoice, ['route' => array('biller.invoices.send_sms_and_email', $invoice), 'method' => 'POST' ]) }}
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
                        <label for="phone_number">Phone Number</label>
                        {{ Form::text('phone_number', @$invoice->customer->phone, ['class' => 'form-control']) }}
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        {{ Form::text('email', @$invoice->customer->email, ['class' => 'form-control']) }}
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