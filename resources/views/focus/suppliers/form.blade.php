<div class="card-content">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                   aria-selected="true">{{trans('customers.billing_address')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab"
                   aria-selected="false">Payment Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4" role="tab"
                   aria-selected="false">{{trans('general.other')}}</a>
            </li>
        </ul>

        <div class="tab-content px-1 pt-1">
            <!-- billing address -->
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row no-gutters">
                            <div class="col-4">
                                <div class='form-group'>
                                    {{ Form::label('accounts_payable', 'A/P Account',['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        <select name="ap_account_id" class="form-control custom-select" id="ap_account" data-placeholder="Choose A/P Account" required>
                                            <option value="">-- Select Account --</option>
                                            @foreach ($accounts as $row)
                                                <option value="{{ $row->id }}" currencyId="{{ $row->currency_id }}" {{ $row->id == @$supplier->ap_account_id? 'selected' : '' }}>
                                                    {{ $row->holder }}
                                                </option>
                                            @endforeach
                                        </select>
                                        {{ Form::hidden('currency_id', null, ['id' => 'currency']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class='form-group'>
                                    {{ Form::label( 'company', trans('customers.company'),['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('company', null, ['class' => 'form-control box-size', 'id' => 'company', 'placeholder' => trans('customers.company')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label( 'name', trans('customers.name'),['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('name', null, ['class' => 'form-control box-size', 'id' => 'name', 'placeholder' => trans('customers.name').'*','required'=>'required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label( 'phone', trans('customers.phone'),['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('phone', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.phone')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label( 'email', trans('customers.email'),['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::email('email', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.email')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('taxid', 'TAX PIN',['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('taxid', null, ['class' => 'form-control box-size', 'placeholder' => 'TAX PIN', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row no-gutters">
                            <div class="col-4">
                                <div class='form-group'>
                                    {{ Form::label('balance', 'Opening Balance',['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('open_balance', numberFormat(@$supplier->open_balance), ['class' => 'form-control', 'id'=>'open_balance']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class='form-group'>
                                    {{ Form::label('date', 'As At',['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('open_balance_date', null, ['class' => 'form-control datepicker','id'=>'open_balance_date']) }}
                                    </div>
                                </div> 
                            </div>
                            <div class="col-4">
                                <div class='form-group'>
                                    {{ Form::label('note', 'Balance Note',['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::textarea('open_balance_note', null, ['class' => 'form-control', 'id' => 'open_balance_note', 'rows' => '1', 'style' => 'height:3em;']) }}
                                    </div>
                                </div>  
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('country', trans('customers.country'),['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('country', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.country')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('region', trans('customers.region'),['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('region', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.region')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('city', trans('customers.city'),['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('city', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.city')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('address', trans('customers.address'),['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('address', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.address')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('postbox', 'P.O Box',['class' => 'col-lg-12 control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('postbox', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.postbox')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- payment setting -->
            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">       
                <div class='form-group'>
                    {{ Form::label( 'account_no', 'ACCOUNT NUMBER',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('account_no', null, ['class' => 'form-control box-size', 'placeholder' => 'ACCOUNT NUMBER']) }}
                    </div>
                </div>              
                <div class='form-group'>
                    {{ Form::label( 'account_name', 'PRINT NAME ON CHEQUE AS',['class' => 'col-lg-3 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('account_name', null, ['class' => 'form-control box-size', 'placeholder' => 'CHEQUE NAME (ACCOUNT NAME)']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'bank', 'BANK',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('bank', null, ['class' => 'form-control box-size', 'placeholder' => 'BANK']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'bank_code', 'BANK CODE',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('bank_code', null, ['class' => 'form-control box-size', 'placeholder' => 'BANK CODE']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'payment_terms', 'PAYMENT TERMS',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>                     
                        {{ Form::select('payment_terms',['0'=>'ON RECEIPT','30'=>'AFTER 30 DAYS','45'=>'AFTER 45 DAY','60'=>'AFTER 60 DAY','90'=>'AFTER 90 DAY'], null, ['class' => 'form-control box-size', 'placeholder' => 'PAYMENT TERMS']) }}
                    </div>
                </div>             
                <div class='form-group'>
                    {{ Form::label( 'credit_limit', 'CREDIT LIMIT',['class' => 'col-lg-3 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('credit_limit', null, ['class' => 'form-control box-size', 'id'=>'credit_limit', 'placeholder' => 'CREDIT LIMIT']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'mpesa_payment', 'MPESA PAYMENT OPTIONS',['class' => 'col-lg-3 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('mpesa_payment', null, ['class' => 'form-control box-size', 'placeholder' => 'PAYBILL OR BUYGOODS NUMBER & ACCOUNT NUMBER']) }}
                    </div>
                </div>              
            </div>



            <!-- other details -->
            <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                <div class='form-group'>
                    <label for="contact_person_info" class="col-2">Contact Person Info</label>
                    <div class='col-10'>
                        {{ Form::textarea('contact_person_info', null, ['class' => 'form-control', 'placeholder' => 'Contact Person Info', 'rows' => '5']) }}
                    </div>
                </div>    
                <div class='form-group'>
                    {{ Form::label( 'docid', trans('customers.docid'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('docid', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.docid')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'custom1', trans('customers.custom1'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('custom1', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.custom1')]) }}
                    </div>
                </div>
                <div class='form-group hide_picture'>
                    {{ Form::label( 'picture', trans('customers.picture'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-6'>
                        {!! Form::file('picture', array('class'=>'input' )) !!}
                    </div>
                </div>
                <hr>

                {{-- User Info --}}
                <h6 class="mb-2">User Info</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('first_name', 'First Name',['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('first_name', @$supplier->user->first_name, ['class' => 'form-control box-size', 'placeholder' => 'First Name']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('last_name', 'Last Name',['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('last_name', @$supplier->user->last_name, ['class' => 'form-control box-size', 'placeholder' => 'Last Name']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('user_email', 'Email', ['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('user_email', @$supplier->user->email, ['class' => 'form-control box-size', 'placeholder' => 'Email']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label( 'password', trans('customers.password'),['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::password('password', ['class' => 'form-control box-size', 'id' => 'password', 'autocomplete' => 'new-password']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('password_confirmation', 'Confirm Password',['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::password('password_confirmation', ['class' => 'form-control box-size', 'id' => 'confirm_password', 'autocomplete' => 'new-password']) }}
                                        <label for="password_match" class="text-danger d-none">Password does not match !</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="ml-2 password-condition">
                                    <h4>Password must have:</h4>
                                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least 7 Characters</h5>
                                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> Contain Upper and Lowercase letters</h5>
                                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least one number</h5>
                                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least one symbol</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
    }
    // datepicker
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

    // edit supplier
    const supplier = @json(@$supplier);
    if (supplier) {
        const date = supplier.open_balance_date;
        const balance = parseFloat(supplier.open_balance);
        if (date) {
            $('#open_balance_date').datepicker('setDate', new Date(date));
        }
        $('#open_balance').val(accounting.formatNumber(balance));
    }

    $('#company').keyup(function() {
        $('#name').val(this.value)
    });
    $('#ap_account').change(function() {
        const currencyId = $(this).find(':selected').attr('currencyId');
        $('#currency').val(currencyId);
    });
    
    // on selecting groups
    $("#groups").select2({multiple: true});
    $("#groups").on("select2:select", function (evt) {
        var element = evt.params.data.element;
        var $element = $(element);
        $element.detach();
        $(this).append($element);
        $(this).trigger("change");
    });

    $("#open_balance").change(function() {
        const value = accounting.unformat($(this).val());
        $(this).val(accounting.formatNumber(value));
    });
    $("#credit_limit").change(function() {
        const input_val = $(this).val();
        $("#credit_limit").val(accounting.formatNumber(input_val));
    });

    // password validation
    $('#password').on('keyup', function() {
        const div = $('.password-condition');
        const value = $(this).val();
        if (value.length >= 7) div.find('h5:first').removeClass('text-danger').addClass('text-success');
            else div.find('h5:first').removeClass('text-success').addClass('text-danger');
            if (new RegExp("[a-z][A-Z]|[A-Z][a-z]").test(value)) div.find('h5:eq(1)').removeClass('text-danger').addClass('text-success');
            else div.find('h5:eq(1)').removeClass('text-success').addClass('text-danger');
            if (new RegExp("[0-9]").test(value)) div.find('h5:eq(-2)').removeClass('text-danger').addClass('text-success');
            else div.find('h5:eq(-2)').removeClass('text-success').addClass('text-danger');
            if (new RegExp("[^A-Za-z 0-9]").test(value)) div.find('h5:last').removeClass('text-danger').addClass('text-success');
            else div.find('h5:last').removeClass('text-success').addClass('text-danger');

    });
    $('#confirm_password').on('keyup', function() {
        if ($(this).val() != $('#password').val()) $(this).next().removeClass('d-none');
        else $(this).next().addClass('d-none');
    });
</script>
@endsection
