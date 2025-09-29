
<div class='row'>
    <div class='form-group col-2'>
        <div><label for="tid">Charge ID</label></div>
        {{ Form::text('tid', @$last_charge->tid+1, ['class' => 'form-control round', 'readonly']) }}
    </div>
    <div class='form-group col-3'>
        <div><label for="method">Payment Mode</label></div>
        <select name="payment_mode" class='form-control round' required>
            <option value="">-- Select Mode --</option>
            @foreach($payment_modes as $mode)
                <option value="{{ $mode }}">
                    {{ $mode }}
                </option>
            @endforeach
        </select>            
    </div>
    <div class='form-group col-3'>
        <div><label for="date">Payment Date</label></div>
        <input type="text" name="date" class="form-control datepicker round">
    </div>
</div>

<div class='row'>
    <div class='form-group col-5'>
        <div><label for="bank">Bank Account</label></div>
        <select name="bank_id" class='form-control round' required>
            <option value="">-- Select Bank --</option>
            @foreach($bank_accounts as $account)
                <option value="{{ $account['id'] }}">
                    {{ $account['holder'] }}
                </option>
            @endforeach
        </select>
    </div> 
    <div class='form-group col-5'>
        <div><label for="expense">Charge Expense Account</label></div>
        <select name="expense_id" class='form-control round' required>
            @foreach($exp_accounts as $account)
                <option value="{{ $account['id'] }}">
                    {{ $account['holder'] }}
                </option>
            @endforeach
        </select>
    </div> 
</div>
<div class='row'>
    <div class='form-group col-2'>
        <div><label for="reference">Reference</label></div>
        {{ Form::text('reference', null, ['class' => 'form-control round', 'required']) }}
    </div>
    <div class='form-group col-3'>
        <div><label for="amount">Amount</label></div>
        {{ Form::text('amount', null, ['class' => 'form-control round', 'required']) }}
    </div>
    <div class='form-group col-5'>
        <div><label for="note">Note</label></div>
        {{ Form::text('note', null, ['class' => 'form-control round', 'required']) }}
    </div>
</div>

@section("after-scripts")
<script type="text/javascript">
    $('.datepicker')
    .datepicker({
        autoHide: true,
        format: "{{ config('core.user_date_format') }}"
    })
    .datepicker('setDate', new Date());
</script>
@endsection