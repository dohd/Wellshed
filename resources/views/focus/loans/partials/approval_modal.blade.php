<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Approve Loan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($loan, ['route' => ['biller.loans.update', $loan], 'method' => 'PATCH']) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Loan Status</label>
                        <select class="form-control" name="approval_status" id="status">
                            @foreach (['pending', 'review', 'approved', 'rejected'] as $val)
                                <option value="{{ $val }}">
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        {{ Form::text('approval_date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
                    </div>
                    <div class="form-group">
                        <label for="amount">Principal</label>
                        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'readonly']) }}
                    </div>
                    <div class="form-group">
                        <label for="fee">Fee</label>
                        {{ Form::text('fee', null, ['class' => 'form-control', 'id' => 'fee', 'readonly']) }}
                    </div>
                    <div class="form-group">
                        <label for="month_installment">Month Installment</label>
                        {{ Form::text('month_installment', null, ['class' => 'form-control', 'id' => 'month_installment', 'readonly']) }}
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        {{ Form::textarea('approval_note', null, ['class' => 'form-control', 'rows' => '4', 'id' => 'note']) }}
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Save', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>