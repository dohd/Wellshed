<div class="modal fade" id="editTrModal" tabindex="-1" role="dialog" aria-labelledby="editTrModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Transaction</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ Form::open(['route' => ['biller.transactions.update', $tr], 'method' => 'PATCH']) }}
                    <div class="row form-group">
                        <div class="col-12">
                            <label for="note">Note</label>
                            {{ Form::text('note', $tr->note, ['class' => 'form-control', 'disabled']) }}
                        </div>                     
                    </div>
                    <div class="row form-group">
                        <div class="col-6">
                            <label for="account">Account</label>
                            <select name="account_id" class="form-control" id="account" data-id="{{ $tr->account_id }}" data-placeholder="Search Account">
                                <option value="{{ $tr->account->id }}" selected>{{ $tr->account->holder }}</option>
                            </select>                        
                        </div>
                        <div class="col-6">
                            <label for="tid">Transaction No</label>
                            {{ Form::text('tid', gen4tid('Tr-', $tr->tid), ['class' => 'form-control', 'disabled']) }}
                        </div>
                    </div>
                    
                    <div class="row form-group">
                        <div class="col-6">
                            <label for="debit">Debit</label>
                            {{ Form::text('debit', numberFormat($tr->debit), ['class' => 'form-control', 'id' => 'debit']) }}
                        </div>
                        <div class="col-6">
                            <label for="credit">Credit</label>
                            {{ Form::text('credit', numberFormat($tr->credit), ['class' => 'form-control', 'id' => 'credit']) }}
                        </div>                        
                    </div>        
                    
                    <div class="row form-group">
                        <div class="col-6">
                            <label for="tr_date">Date</label>
                            {{ Form::text('tr_date', dateFormat($tr->tr_date), ['class' => 'form-control datepicker', 'id' => 'tr_date']) }}
                        </div>
                    </div>              
                                   
                    <div class="form-group float-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        {{ Form::submit('Update', ['class' => 'btn btn-primary']) }}
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>