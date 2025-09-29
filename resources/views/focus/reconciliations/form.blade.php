<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-1">
                    <label for="serial" class="caption">#Serial</label>
                    {{ Form::text('tid', $tid, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>   
                <div class="col-2">
                    <label for="reconciled-on">Reconciled On</label>
                    {{ Form::text('reconciled_on', null, ['class' => 'form-control datepicker', 'id' => 'reconciled_on']) }}
                </div>
                
            </div>
            
            <div class="row mb-2">
                <div class="col-md-6 col-12">
                    <label for="payer" class="caption">Bank Account</label>                                       
                    <select class="custom-select" id="account" name="account_id" autocomplete="off" required>
                        <option value="">-- Select Bank --</option>
                        @foreach ($accounts as $bank)
                            <option value="{{ $bank->id }}" {{ $bank->id == @$reconciliation->account_id? 'selected' : ''}}>
                                {{ $bank->holder }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-2">
                    <label for="end_date" class="caption">Ending Month</label>
                    {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'id' => 'end_date', 'required' => 'required']) }}
                </div>  
                <div class="col-2">
                    <label for="ending-period">Ending Period</label>
                    {{ Form::text('ending_period', null, ['class' => 'form-control datepicker', 'id' => 'ending_period']) }}
                </div> 
                <div class="col-2">
                    <label for="end_balance" class="caption">Ending Balance</label>
                    {{ Form::text('end_balance', null, ['class' => 'form-control', 'id' => 'end_balance', 'autocomplete' => "off", 'required' => 'required']) }}
                    {{ Form::hidden('begin_balance', null, ['id' => 'begin_balance']) }}
                    {{ Form::hidden('cash_in', null, ['id' => 'cash_in']) }}
                    {{ Form::hidden('cash_out', null, ['id' => 'cash_out']) }}
                    {{ Form::hidden('cleared_balance', null, ['id' => 'cleared_balance']) }}
                    {{ Form::hidden('balance_diff', null, ['id' => 'balance_diff']) }}
                    <!-- -->
                    {{ Form::hidden('is_done', null, ['id' => 'is_done']) }}
                    {{ Form::hidden('ep_uncleared_balance', null, ['id' => 'ep_uncleared_balance']) }}
                    {{ Form::hidden('ep_account_balance', null, ['id' => 'ep_account_balance']) }}
                    {{ Form::hidden('uncleared_balance_after_ep', null, ['id' => 'uncleared_balance_after_ep']) }}
                    {{ Form::hidden('ro_account_balance', null, ['id' => 'ro_account_balance']) }}
                </div>  
            </div> 
            
            <div class="row">
                <div class="col-md-7 col-12">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Beginning Balance</th>
                                    <th>- Cash Out</th>
                                    <th>+ Cash In</th>
                                    <th>Cleared Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="begin-bal">0.00</td>
                                    <td class="cash-out">0.00</td>
                                    <td class="cash-in">0.00</td>
                                    <td class="cleared-bal">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-5 col-12">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Ending Balance</th>
                                    <th>- Cleared Balance</th>
                                    <th>Difference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="endin-bal">0.00</td>
                                    <td class="cleared-bal">0.00</td>
                                    <td class="bal-diff">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Uncleared Balance</th>
                                    <th>Account Balance <small class="text-success">(Cash In - Cash Out)</small>| On Ending Period </th>
                                    <th>Uncleared Balance After Ending Period</th>
                                    <th>Account Balance | On Reconciling</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="uncleared-bal">0.00</td>
                                    <td class="ep-account-bal">0.00</td>
                                    <td class="uncleared-bal-after-ep">0.00</td>
                                    <td class="recon-on-bal">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transactions table -->
@include('focus.reconciliations.partials.reconciliations_table')
<!--end Transactions table -->

@section('after-scripts')
@include('focus.reconciliations.form_js')
@endsection