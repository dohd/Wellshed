
<div class="modal fade" id="sendSmsEmailModal" tabindex="-1" role="dialog" aria-labelledby="sendSmsEmailModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Notify Suppliers</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($rfq_analysis, ['route' => array('biller.rfq_analysis.notify_suppliers', $rfq_analysis), 'method' => 'POST' ]) }}
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
                        <label for="">Select Supplier</label>
                        <select name="supplier_ids[]" id="supplier_id" class="form-control" data-placeholder="Search Supplier" multiple>
                            <option value="">Search Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{$supplier->id}}" {{$supplier->id == @$rfq_analysis->supplier_id ? 'selected' : ''}}>{{$supplier->company ?: $supplier->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'approved_date']) }}
                    </div>
                    <div class="form-group  d-none div_subject">
                        <label for="subject">Subject</label>
                        {{ Form::text('subjects', null, ['class' => 'form-control']) }}
                    </div>
                    <div class="form-group">
                        <label for="text_message">Message</label>
                        {{ Form::textarea('text_message', null, ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Submit', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>