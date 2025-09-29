<div class="modal fade" id="notifyModal" tabindex="-1" role="dialog" aria-labelledby="notifyModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Notification to Referrers</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($customer_enrollment, ['route' => array('biller.customer_enrollments.notify_referrers', $customer_enrollment), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Payment Status</label>
                        <select class="custom-select" name="payment_status" id="payment_status">
                            @foreach (['pending', 'partial','paid'] as $val)
                                <option value="{{ $val }}" {{ @$customer_enrollment && $customer_enrollment->payment_status == $val? 'selected' : '' }}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <input type="hidden" name="notification_status" value="yes" id="">
                    <div class="form-group">
                        <label for="payment_date">Payment Date</label>
                        {{ Form::text('payment_date', null, ['class' => 'form-control datepicker', 'id'=>'date']) }}
                    </div>
                   <div class="form-group">
                        <label for="payment_note">Payment Note</label>
                        {{ Form::textarea('payment_note', null, ['class' => 'form-control']) }}
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