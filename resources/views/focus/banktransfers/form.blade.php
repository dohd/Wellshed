<div class='row'>
    <div class='col-md-1'>
        <div class='form-group'>
            {{ Form::label('tid', '#Transfer No',['class' => 'col-12 control-label']) }}
            <div class='col'>
                {{ Form::text('tid', $tid, ['class' => 'form-control round required', 'placeholder' => trans('general.note'), 'readonly' => 'readonly']) }}
            </div>
        </div>
    </div>
    <div class='col-md-2'>
        <div class='form-group'>
            {{ Form::label('transaction_date', 'Transfer Date', ['class' => 'control-label']) }}
            <div class='col'>
                <fieldset class="form-group position-relative has-icon-left">
                    <input type="text" class="form-control round datepicker" placeholder="{{trans('general.payment_date')}}*" name="transaction_date" id="date" required>
                    <div class="form-control-position">
                        <span class="fa fa-calendar" aria-hidden="true"></span>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>

<div class="row no-gutters">
    <div class="col-md-3 pr-0">
        <div class='form-group'>
            {{ Form::label('account_id', 'Transfer Money From', ['class' => 'col-12 control-label']) }}
            <div class="col">
                <select name="account_id" id="source-account" class='custom-select round' required>
                    <option value="">-- select account --</option>
                    @foreach($accounts as $row)
                        <option 
                            value="{{ $row->id }}" 
                            currencyId="{{ $row->currency_id }}"
                            currencyRate="{{ +$row->currency->rate }}"
                            currencyCode="{{ $row->currency->code }}"
                            systemCode="{{ $row->system }}"
                            {{ @$banktransfer->account_id == $row->id? 'selected' : '' }}
                        >
                            {{ $row->holder }} ({{ $row->currency->code }}): {{ numberFormat($row->balance) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class='col-md-2'>
        <div class='form-group'>
            {{ Form::label( 'amount', 'Transfer Amount', ['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('amount', null, ['class' => 'form-control round required', 'placeholder' => 'Amount', 'id' => 'amount', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <!-- Default exchange rate -->
    {{ Form::hidden('default_rate', null, ['class' => 'form-control round exchange-rate', 'id' => 'default-rate']) }}
</div>

<div class='row no-gutters'>
    <div class="col-md-3 pr-0">
        <div class='form-group'>
            {{ Form::label('debit_account_id', 'Transfer Money To',['class' => 'col-12 control-label']) }}
            <div class="col">                
                <select name="debit_account_id" id="dest-account" class='custom-select round' required>
                    <option value="">-- select account --</option>
                    @foreach($accounts as $row)
                        <option 
                            value="{{ $row->id }}" 
                            currencyId="{{ $row->currency_id }}"
                            currencyRate="{{ +$row->currency->rate }}"
                            currencyCode="{{ $row->currency->code }}"
                            systemCode="{{ $row->system }}"
                            {{ @$banktransfer->debit_account_id == $row->id? 'selected' : '' }}
                        >
                            {{ $row->holder }} ({{ $row->currency->code }}): {{ numberFormat($row->balance) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>  
    </div>
    <div class="col-md-2">
        <div class='form-group'>
            {{ Form::label('bank_rate', 'Bank Exchange Rate',['class' => 'col-12 control-label']) }}
            <div class='col'>
                {{ Form::text('bank_rate', null, ['class' => 'form-control round exchange-rate', 'id' => 'bank-rate']) }}
            </div>
        </div>
    </div>
    <div class='col-md-2'>
        <div class='form-group'>
            {{ Form::label('receipt-amount', 'Receipt Amount', ['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('receipt_amount', null, ['class' => 'form-control round', 'readonly' => 'readonly', 'id' => 'rcpt-amount']) }}
            </div>
        </div>
    </div>
    <div class='col-md-2'>
        <div class='form-group'>
            {{ Form::label( 'method', 'Transfer Method', ['class' => 'col-12 control-label']) }}
            <div class="col">
                <select name="method" class='custom-select round'>
                    @foreach(['Cash', 'Mobile Money', 'EFT', 'RTGS', 'Cheque'] as $val)
                        <option value="{{ $val }}" {{ @$pmt_mode == $val? 'selected' : ''}}>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class='col-md-2'>
        <div class='form-group'>
            {{ Form::label( 'refer_no', 'Reference No',['class' => 'col-12 control-label']) }}
            <div class='col'>
                {{ Form::text('refer_no', null, ['class' => 'form-control round', 'placeholder' => 'Reference No', 'id' => 'refer_no']) }}
            </div>
        </div>
    </div>
</div>

<div class='row'>   
    <div class='col-md-6 pr-0'>
        <div class='form-group'>
            {{ Form::label( 'note', trans('general.note'),['class' => 'col-12 control-label']) }}
            <div class='col'>
                {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('general.note'), 'id' => 'note']) }}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 pr-0">
        <div class="form-group">
            <div class="col">
                <input type="checkbox" id="select_user">
                <label for="">Attach Employee / Casual / Third Party</label>
            </div>
        </div>
    </div>
</div>
<div class='row d-none div_users'>   
    <div class='col-md-3 pr-0'>
        <div class='form-group'>
            {{ Form::label( 'user_type', 'Select User Type',['class' => 'col-12 control-label']) }}
            <div class='col'>
                 <select name="user_type" id="user_type" class="round form-control">
                    <option value="">--select user type--</option>
                    <option value="employee" {{ @$banktransfer->user_type == 'employee' ? 'selected' :'' }}>Employee</option>
                    <option value="casual" {{ @$banktransfer->user_type == 'casual' ? 'selected' :'' }}>Casual Labourer</option>
                    <option value="third_party_user" {{ @$banktransfer->user_type == 'third_party_user' ? 'selected' :'' }}>Third Party User</option>
                </select>
            </div>
        </div>
    </div>

        <div class="col-4 div_employee">
            <label for="employee">Search Employee</label>
            <select name="employee_id" id="employee" class="form-control" data-placeholder="Search Employee">
                <option value="">Search Employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $employee->id == @$banktransfer->employee_id ? 'selected' : '' }}>{{ $employee->fullname }}</option>
                @endforeach
            </select>
            <input type="hidden" value="{{ @$banktransfer->employee_id }}" name="employee_id" id="employee-inp" disabled>
        </div>
        <div class="col-4 div_casual d-none">
            <label for="casual">Search Casual Labourer</label>
            <select name="casual_id" id="casual" class="form-control" data-placeholder="Search Casual Labourer">
                <option value="">Search Casual Labourer</option>
                @foreach ($casuals as $casual)
                    <option value="{{ $casual->id }}" {{ $casual->id == @$banktransfer->casual_id ? 'selected' : '' }}>{{ $casual->name }}</option>
                @endforeach
            </select>
            <input type="hidden" value="{{ @$banktransfer->casual_id }}" name="casual_id" id="casual-inp" disabled>
        </div>
        <div class="col-4 div_third_party_user d-none">
            <label for="third_party_user">Search Third Party User</label>
            <select name="third_party_user_id" id="third_party_user" class="form-control" data-placeholder="Search Third Party User">
                <option value="">Search Third Party User</option>
                @foreach ($third_party_users as $third_party_user)
                    <option value="{{ $third_party_user->id }}" {{ $third_party_user->id == @$banktransfer->third_party_user_id ? 'selected' : '' }}>{{ $third_party_user->name }}</option>
                @endforeach
            </select>
            <input type="hidden" value="{{ @$banktransfer->third_party_user_id }}" name="third_party_user_id" id="third_party_user-inp" disabled>
        </div>
</div>
<h5 class="text-black mb-2 ml-1">Receipt Amount (Local Currency): <b><span id="home-amount">0.00</span></b></h5>
{{ Form::hidden('source_amount_fx', null, ['id' => 'source_amount_fx']) }}
{{ Form::hidden('dest_amount_fx', null, ['id' => 'dest_amount_fx']) }}
{{ Form::hidden('fx_gain_total', null, ['id' => 'fx_gain_total']) }}
{{ Form::hidden('fx_loss_total', null, ['id' => 'fx_loss_total']) }}

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
@include('focus.banktransfers.form_js')
@endsection