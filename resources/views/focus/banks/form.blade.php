<div class="row">
    <div class="col-md-6">
        <div class='form-group'>
            {{ Form::label( 'name', trans('banks.name'),['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-12'>
                {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => trans('banks.name'),'required'=>'']) }}
            </div>
        </div>
        <div class='form-group'>
            {{ Form::label( 'bank', trans('banks.bank'),['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-12'>
                {{ Form::text('bank', null, ['class' => 'form-control round', 'placeholder' => trans('banks.bank'),'required'=>'']) }}
            </div>
        </div>
        <div class='form-group'>
            {{ Form::label( 'number', trans('banks.number'),['class' => 'col-lg-4 control-label']) }}
            <div class='col-lg-12'>
                {{ Form::text('number', null, ['class' => 'form-control round', 'placeholder' => trans('banks.number'),'required'=>'']) }}
            </div>
        </div>
        <div class='form-group'>
            {{ Form::label( 'code', 'Swift Code',['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-12'>
                {{ Form::text('code', null, ['class' => 'form-control round', 'placeholder' => trans('banks.code')]) }}
            </div>
        </div>
        <div class='form-group'>
            {{ Form::label( 'note', trans('banks.note'),['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-12'>
                {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('banks.note')]) }}
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class='form-group'>
            {{ Form::label( 'address', trans('banks.address'),['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-12'>
                {{ Form::text('address', null, ['class' => 'form-control round', 'placeholder' => trans('banks.address')]) }}
            </div>
        </div>
        <div class='form-group'>
            {{ Form::label( 'branch', trans('banks.branch'),['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-12'>
                {{ Form::text('branch', null, ['class' => 'form-control round', 'placeholder' => trans('banks.branch')]) }}
            </div>
        </div>
        <div class='form-group'>
            {{ Form::label('paybill', 'Paybill Details',['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-12'>
                {{ Form::text('paybill', null, ['class' => 'form-control round', 'placeholder' => 'Bank Paybill']) }}
            </div>
        </div>
        <div class='form-group'>
            {{ Form::label( 'enable', trans('banks.enable'),['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-12'>
                <select class="form-control round" name="enable">
                    @php
                        switch (@$banks['enable']) {
                            case 'Yes' : echo  '<option value="yes">--'.trans('general.yes').'--</option>';
                            case 'No' : echo  '<option value="no">--'.trans('general.no').'--</option>';
                        }
                    @endphp
                    <option value="Yes">{{trans('general.yes')}}</option>
                    <option value="No">{{trans('general.no')}}</option>        
                </select>
            </div>
        </div>

        <!-- Bank Feeds params -->
        <div class="row no-gutters">
            <div class="col-md-4">
                {{ Form::label('ledger_account', 'Ledger Account',['class' => 'col-lg-12 control-label']) }}
                <div class='col-lg-12'>
                    <select class="form-control round" name="account_id">
                        <option value="">-- Ledger Account --</option>    
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" {{ $account->id == @$bank->account_id? 'selected' : '' }}>
                                {{ $account->holder }}
                            </option>
                        @endforeach   
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                {{ Form::label('begin_balance',  'Bank Ending Balance (Feeds)', ['class' => 'col-lg-12 control-label']) }}
                <div class='col-lg-12'>
                    {{ Form::text('feed_begin_balance', null, ['class' => 'form-control round', 'id' => 'feed_begin_balance', 'placeholder' => '0.00']) }}
                </div>
            </div>
            <div class="col-md-4">
                {{ Form::label('begin_date', 'As of Date', ['class' => 'col-lg-12 control-label']) }}
                <div class='col-lg-12'>
                    {{ Form::text('feed_begin_date', null, ['class' => 'form-control round datepicker', 'id' => 'feed_begin_date']) }}
                </div>
                
            </div>
        </div>
        
    </div>
</div>

@section("after-scripts")
<script type="text/javascript">
    const config = {
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };
    $('.datepicker').datepicker(config.date);

    const bank = @json(@$bank);
    console.log(bank)
    if (bank && bank.id) {
        if (bank.feed_begin_date) {
            $('#feed_begin_date').datepicker('setDate', new Date(bank.feed_begin_date));
        }
        $('#feed_begin_balance').val(accounting.formatNumber(+bank.feed_begin_balance));
        console.log(accounting.formatNumber(+bank.feed_begin_balance), $('#feed_begin_balance')[0]);
    }
</script>
@endsection
