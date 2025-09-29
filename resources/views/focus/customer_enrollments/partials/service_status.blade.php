<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($customer_enrollment, ['route' => array('biller.customer_enrollments.change_status', $customer_enrollment), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="custom-select" name="status" id="status">
                            @foreach (['pending', 'review', 'approved', 'rejected'] as $val)
                                <option value="{{ $val }}" {{ @$customer_enrollment && $customer_enrollment->status == $val? 'selected' : '' }}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id'=>'date']) }}
                    </div>
                    <div class="form-group">
                        <label for="quote_amount">Quoted Amount</label>
                        {{ Form::text('quote_amount', null, ['class' => 'form-control', 'id'=>'quote_amount']) }}
                    </div>
                    <div class="form-group">
                        <label for="remark">Remark</label>
                        {{ Form::text('status_note', null, ['class' => 'form-control']) }}
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