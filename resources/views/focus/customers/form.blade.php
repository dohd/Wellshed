<div class="card-content">
    <div class="card-body">
        <!-- Tab Menu -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab" aria-selected="true">{{trans('customers.billing_address')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab5" data-toggle="tab" aria-controls="tab5" href="#tab5" role="tab"
                   aria-selected="false">Payment Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab" aria-selected="false">{{trans('customers.shipping_address')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4" role="tab" aria-selected="false">{{trans('general.other')}}</a>
            </li>
        </ul>


        <div class="tab-content px-1 mt-2">
            <!-- billing address -->
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row no-gutters">
                            <div class="col-4">
                                <div class='form-group'>
                                    {{ Form::label('accounts_receivable', 'A/R Account',['class' => 'col-lg-12 control-label']) }}
                                    <div class='col-lg-12'>
                                        <select name="ar_account_id" class="form-control custom-select" id="ar_account" data-placeholder="Choose A/R Account" required>
                                            <option value="">-- Select Account --</option>
                                            @foreach ($accounts as $row)
                                                <option value="{{ $row->id }}" {{ $row->id == @$customer->ar_account_id? 'selected' : '' }}>
                                                    {{ $row->holder }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class='form-group'>
                                    {{ Form::label('company', 'Company Name / Client Name',['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-lg-12'>
                                        {{ Form::text('company', old('company', @$customer_data['name']), ['class' => 'form-control box-size', 'placeholder' => 'Company Name / Client Name' . '*', 'required']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label( 'email', trans('customers.email'),['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-lg-12'>
                                        {{ Form::email('email', old('email', @$customer_data['email']), ['class' => 'form-control box-size', 'placeholder' => trans('customers.email')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('name', 'Contact Name', ['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-md-12'>
                                        {{ Form::text('name', old('name', @$customer_data['name']), ['class' => 'form-control box-size', 'placeholder' => trans('customers.name')]) }}
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label( 'phone', trans('customers.phone'),['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-md-12'>
                                        {{ Form::text('phone', old('contact', @$customer_data['contact']), ['class' => 'form-control box-size', 'placeholder' => trans('customers.phone')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label( 'address', trans('customers.address') . ' (P.O Box, City)', ['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-sm-12'>
                                        {{ Form::text('address', old('address', @$customer_data['address']), ['class' => 'form-control box-size', 'placeholder' => trans('customers.address') . ' (P.O Box, City)']) }}
                                    </div>
                                </div>  
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-4">
                                <div class='form-group'>
                                    {{ Form::label('opening_balance', 'Opening Balance', ['class' => 'col-lg-12 control-label']) }}
                                    <div class='col-lg-12'>
                                        {{ Form::text('open_balance', '0.00', ['class' => 'form-control', 'id' => 'open_balance']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class='form-group'>
                                    {{ Form::label('as_at_date', 'As At',['class' => 'col-lg-12 control-label']) }}
                                    <div class='col-lg-12'>
                                        {{ Form::text('open_balance_date', null, ['class' => 'form-control datepicker', 'id' => 'open_balance_date']) }}
                                    </div>
                                </div>      
                            </div>
                            <div class="col-5">
                                <div class='form-group'>
                                    {{ Form::label('note', 'Balance Note',['class' => 'col-lg-12 control-label']) }}
                                    <div class='col-lg-12'>
                                        {{ Form::textarea('open_balance_note', null, ['class' => 'form-control', 'rows' => '1', 'style' => 'height:3em;', 'id' => 'open_balance_note']) }}
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row no-gutters">
                            <div class="col-sm-6">
                                <div class='form-group'>
                                    {{ Form::label( 'country', trans('customers.country'),['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-md-12'>
                                        {{ Form::text('country', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.country')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class='form-group'>
                                    {{ Form::label( 'region', trans('customers.region'),['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-md-12'>
                                        {{ Form::text('region', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.region')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-sm-6">
                                <div class='form-group'>
                                    {{ Form::label( 'city', trans('customers.city'),['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-md-12'>
                                        {{ Form::text('city', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.city')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class='form-group'>
                                    {{ Form::label( 'postbox', 'Company Location',['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-md-12'>
                                        {{ Form::text('postbox', null, ['class' => 'form-control box-size', 'placeholder' => 'Location']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class='form-group'>
                                    <div class="col-lg-6 d-flex d-flex-row">
                                        {{ Form::label('taxid', trans('customers.taxid'),['class' => 'control-label mr-1']) }}
                                        @if (config('services.efris.base_url'))
                                            <div style="width: 1rem; height: 1rem;" class="spinner-border taxid-spinner" role="status"></div>
                                        @endif
                                    </div>
                                    <div class='col-lg-12'>
                                        {{ Form::text('taxid', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.taxid'), 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('tax_exempt', 'Is Tax Exempted',['class' => 'col-lg-6 control-label']) }}
                                    <div class='col-lg-12'>
                                        <select name="is_tax_exempt" id="is_tax_exempt" class="custom-select">
                                            @foreach (['No', 'Yes'] as $key => $val)
                                                <option value="{{ $key }}" {{ @$customer->is_tax_exempt == $key? 'selected' : '' }}>{{ $val }}</option>
                                            @endforeach
                                        </select>
                                        
                                    </div>
                                </div>
                            </div>                            
                        </div>
                        @if (config('services.efris.base_url'))
                            <div class="row no-gutters">
                                <div class="col-6">
                                    <div class='form-group'>
                                        <div class='col-lg-12'>
                                            <label for="buyer_type">EFRIS Buyer Type <span class="text-danger">*</span></label>
                                            <select name="efris_buyer_type" id="efrisBuyerType" class="custom-select">
                                                @foreach (['0:B2B', '1:B2C', '2:FOREIGNER', '3:B2G'] as $key => $item)
                                                    <option value="{{ $key }}">{{ $item }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="efris_buyer_type_name" id="efrisBuyerTypeName">
                                        </div>
                                    </div>
                                </div>                            
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- payment setting -->
            <div class="tab-pane" id="tab5" role="tabpanel" aria-labelledby="base-tab5">       
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
                        {{ Form::text('credit_limit', numberFormat(@$customer->credit_limit), ['class' => 'form-control box-size', 'id'=>'credit_limit', 'placeholder' => 'CREDIT LIMIT']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'mpesa_payment', 'MPESA PAYMENT OPTIONS',['class' => 'col-lg-3 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('mpesa_payment', null, ['class' => 'form-control box-size', 'placeholder' => 'PAYBILL OR BUYGOODS NUMBER & ACCOUNT NUMBER']) }}
                    </div>
                </div>              
            </div>



            <!-- shipping address -->
            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'name_s', trans('customers.name_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('name_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.name_s')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'phone_s', trans('customers.phone_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('phone_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.phone_s')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'email_s', trans('customers.email_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('email_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.email_s')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'address_s', trans('customers.address_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('address_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.address_s')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'city_s', trans('customers.city_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('city_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.city_s')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'region_s', trans('customers.region_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('region_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.region_s')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'country_s', trans('customers.country_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('country_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.country_s')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'postbox_s', trans('customers.postbox_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('postbox_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.postbox_s')]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>




            <!-- other details -->
            <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                {!! @$fields !!}
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
                <div class='form-group row'>
                    <div class='col-4'>
                        {{ Form::label( 'client_name', 'Client Name',['class' => 'control-label']) }}
                        {{ Form::text('client_name', old('client_name', @$customer_data['name']), ['class' => 'form-control box-size client_name', 'placeholder' => 'Client Name']) }}
                    </div>
                    <div class='col-3'>
                        {{ Form::label( 'client_phone', 'Client Contact',['class' => 'control-label']) }}
                        {{ Form::text('client_phone', old('client_phone', @$customer_data['contact']), ['class' => 'form-control box-size client_phone', 'placeholder' => 'Client Contact']) }}
                    </div>
                    <div class="col-2">
                        <input type="checkbox" id="check_dob" class="mt-2">
                        <label for="">Add Birth Day</label>
                    </div>
                    <div class='col-3 d-none div_dob'>
                        {{ Form::label( 'dob', 'DOB',['class' => 'control-label']) }}
                        {{ Form::text('dob', null, ['class' => 'form-control box-size datepickers dob', 'placeholder' => 'DOB']) }}
                    </div>
                </div>
                <div class='form-group hide_picture'>
                    {{ Form::label('picture', trans('customers.picture'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-6'>
                        {!! Form::file('picture', array('class'=>'input' )) !!}
                    </div>
                </div>
                <hr>

                <!-- Customer User Info -->
                <h6 class="mb-2">Customer User Logins</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('first_name', 'First Name',['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('first_name', @$customer->user->first_name, ['class' => 'form-control box-size', 'placeholder' => 'First Name']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('last_name', 'Last Name',['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('last_name', @$customer->user->last_name, ['class' => 'form-control box-size', 'placeholder' => 'Last Name']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('user_email', 'Email', ['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('user_email', @$customer->user->email, ['class' => 'form-control box-size', 'placeholder' => 'Email']) }}
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
                                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least 7 characters</h5>
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
    const config = {
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
    };

    const Form = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('.datepickers').datepicker({format: "{{ config('core.user_date_format')}}", autoHide: true});

            $('#efrisBuyerType').change(Form.changeBuyerType).change();
            $('#open_balance').change(Form.openBalanceChange);
            $('#taxid').change(Form.changeTaxId).change();
            $('#company').keyup(function() { 
                $('#name').val($(this).val()) 
                $('.client_name').val($(this).val()) 
            });
            $('#name').keyup(function() { 
                $('.client_name').val($(this).val()) 
            });
            $('#phone').keyup(function() { 
                $('.client_phone').val($(this).val()) 
            });
            $('#check_dob').click(function(){
                if ($(this).is(':checked', true)) {
                    $('.div_dob').removeClass('d-none');
                } else {
                    $('.div_dob').addClass('d-none');
                }
            });

            $('#password').on('keyup', Form.passwordKeyUp);
            $('#confirm_password').on('keyup', Form.confirmPasswordKeyUp);

            // Edit Mode
            const customer = @json(@$customer);
            if (customer && customer.id) {
                const date = customer.open_balance_date;
                const dob = customer.dob;
                const balance = parseFloat(customer.open_balance);
                if (date) $('#open_balance_date').datepicker('setDate', new Date(date));
                if (dob) $('.dob').datepicker('setDate', new Date(dob));
                $('#open_balance').val(accounting.formatNumber(balance));
                $('#efrisBuyerType').val(customer.efris_buyer_type).change();
            }
        },

        changeTaxId() {
            if (!$(this).val()) return;

            // fetch EFRIS TIN info
            const isEfris = @json(config('services.efris.base_url'));
            if (isEfris) {
                $.post("{{ route('biller.customers.query_tin_info') }}", {
                    tin: $(this).val(),
                })
                .then(({taxpayer}) => {
                    if (taxpayer) {
                        $('#company').val(taxpayer.legalName).keyup();
                        $('#company').attr('readonly', true);
                        if (!$('#phone').val()) $('#phone').val(taxpayer.contactMobile);
                    }
                    $('.taxid-spinner').addClass('d-none');
                })
                .fail((xhr,status, error) => {
                    $('#company').attr('readonly', false);
                    $('.taxid-spinner').addClass('d-none');
                });
            }
        },

        changeBuyerType() {
            const text = $(this).find(':selected').text();
            $('#efrisBuyerTypeName').val(text.split(':')[1]);
        },

        passwordKeyUp() {
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
        },

        confirmPasswordKeyUp() {
            if ($(this).val() != $('#password').val()) $(this).next().removeClass('d-none');
            else $(this).next().addClass('d-none');
        },

        openBalanceChange() {
            const balance = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(balance));
        },
    };

    $(Form.init);
</script>
@endsection