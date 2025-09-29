<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Approve Payroll</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biller.payroll.approve_payroll') }}" method="post">
                @csrf
                <div class="modal-body">


                    <div class="form-group">
                        <label for="status"> Status</label>
                        <select class="form-control" name="status" id="status">
                            @foreach (['PENDING','APPROVED', 'REJECTED'] as $val)
                                <option value="{{ $val }}" @if($val === $payroll->status) selected @endif>
                                    {{ $val }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
{{--                        {{ Form::text('approval_date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}--}}
                        <input type="text" id="approval_date" name="approval_date" required placeholder="Approval Date"
                               class="datepicker form-control box-size mb-2"
                               value="{{$payroll['approval_date']}}"
                        >
                        <input type="hidden" name="id" value="{{ @$payroll->id}}">
                    </div>
                    <div class="form-group">
                        <label for="amount">Total NetPay</label>
                        {{ Form::text('amount', amountFormat(@$payroll->total_salary_after_bnd), ['class' => 'form-control', 'id' => 'amount', 'readonly']) }}
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea name="approval_note" id="approval_note" class="col-12" rows="4">{{ $payroll->approval_note }}</textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Save', ['class' => "btn btn-primary"]) }}
                </div>
            </form>
        </div>
    </div>
</div>