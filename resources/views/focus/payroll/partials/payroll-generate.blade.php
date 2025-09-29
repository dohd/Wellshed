<div class="modal fade" id="generateModal" tabindex="-1" role="dialog" aria-labelledby="generateModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Generate Payroll</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biller.payroll.approve_payroll') }}" method="post">
                @csrf
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label for="amount">Total NetPay</label>
                        {{ Form::text('amount', amountFormat(@$payroll->total_netpay), ['class' => 'form-control', 'id' => 'amount', 'readonly']) }}
                    </div>
                    <div class="form-group">
                        <label for="account">Pay From Account</label>
                        <select name="account_id" id="account" class="custom-select" required>  
                            <option value="">-- select account --</option>                                 
                            @foreach ($accounts as $row)

                                @if($row->holder !== 'Stock Gain' && $row->holder !== 'Others' && $row->holder !== 'Point of Sale' && $row->holder !== 'Loan Penalty Receivable' && $row->holder !== 'Loan Interest Receivable')
                                    <option value="{{ $row->id }}" {{ $row->id == @$billpayment->account_id? 'selected' : '' }}>
                                        {{ $row->holder }}
                                    </option>
                                @endif

                            @endforeach
                        </select>
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