<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Approval Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($advance_payment, ['route' => array('biller.advance_payments.update', $advance_payment), 'method' => 'PATCH' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="custom-select" name="status" id="status">
                            @foreach (['pending', 'approved', 'rejected'] as $val)
                                <option value="{{ $val }}" {{ @$advance_payment && $advance_payment->status == $val? 'selected' : '' }}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="approve_date">Date</label>
                        {{ Form::text('approve_date', dateFormat($advance_payment->approve_date), ['class' => 'form-control datepicker', 'id' => 'approve_date']) }}
                    </div>
                    <div class="form-group">
                        <label for="approve_amount">Amount</label>
                        {{ Form::text('approve_amount', numberFormat($advance_payment->approve_amount), ['class' => 'form-control', 'id' => 'approve_amount']) }}
                    </div>
                    <div class="form-group">
                        <label for="approve_note">Note</label>
                        {{ Form::text('approve_note', null, ['class' => 'form-control', 'id' => 'approve_note']) }}
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