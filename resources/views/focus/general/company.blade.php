@extends ('core.layouts.app')

@section('title', trans('business.company_settings'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('business.company_settings') }}</h4>
        </div>
    </div>

    <div class="content-body">
        {{ Form::open(['route' => 'biller.business.update_settings', 'method' => 'POST', 'files' => true, 'id' => 'manage-company']) }}
        <div class="card rounded">
            <div class="card-content">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-top-border nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active h4 pt-1" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
                                General Setting
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link h4 pt-1" id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
                                Logo & Icons & Links
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link h4 pt-1" id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">
                                WhatsApp Meta Config
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content px-1 pt-1 mb-1">
                        <!-- General Settings -->
                        <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class='form-group'>
                                        {{ Form::label('cname', trans('hrms.company'). ' Name *', ['class' => 'col control-label text-danger']) }}
                                        <div class='col'>
                                            {{ Form::text('cname', @$company['cname'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.company'), 'required' => 'required']) }}
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('sms_email_name', "Company SMS Sender Name*", ['class' => 'col control-label text-danger']) }}
                                        <div class='col'>
                                            {{ Form::text('sms_email_name', @$company['sms_email_name'], ['class' => 'form-control box-size', 'placeholder' => 'Company SMS Sender Name','required' => 'required']) }}
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('address', trans('hrms.address_1'), ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('address', @$company['address'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.address_1')]) }}
                                        </div>
                                    </div>
                                    
                                    <div class="row no-gutters mb-1">
                                        <div class="col-md-4">
                                            {{ Form::label('country', trans('hrms.country'), ['class' => 'col control-label']) }}
                                            <div class='col'>
                                                {{ Form::text('country', @$company['country'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.country')]) }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            {{ Form::label('region', trans('hrms.state'), ['class' => 'col control-label']) }}
                                            <div class='col'>
                                                {{ Form::text('region', @$company['region'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.state')]) }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            {{ Form::label('city', trans('hrms.city'), ['class' => 'col control-label']) }}
                                            <div class='col'>
                                                {{ Form::text('city', @$company['city'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.city')]) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row no-gutters">
                                        <div class="col-md-4">
                                            {{ Form::label('postbox', trans('hrms.postal'), ['class' => 'col control-label']) }}
                                            <div class='col'>
                                                {{ Form::text('postbox', @$company['postbox'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.postal')]) }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class='form-group'>
                                                {{ Form::label('email', trans('general.email'), ['class' => 'col control-label']) }}
                                                <div class='col'>
                                                    {{ Form::text('email', @$company['email'], ['class' => 'form-control box-size', 'placeholder' => trans('general.email')]) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class='form-group'>
                                                {{ Form::label('phone', trans('general.phone'), ['class' => 'col control-label']) }}
                                                <div class='col'>
                                                    {{ Form::text('phone', @$company['phone'], ['class' => 'form-control box-size', 'placeholder' => trans('general.phone')]) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>

                                    <div class='form-group'>
                                        <div class="row no-gutters">
                                            <div class='col-md-6'>
                                                {{ Form::label('taxid', 'TAX PIN', ['class' => 'col control-label']) }}
                                                <div class='col'>
                                                    {{ Form::text('taxid', @$company['taxid'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.tax_id')]) }}
                                                </div>
                                            </div>
                                            <div class='col-md-6'>
                                                {{ Form::label('etr_code', 'CU Serial Number', ['class' => 'col control-label']) }}
                                                <div class='col'>
                                                    {{ Form::text('etr_code', @$company['etr_code'], ['class' => 'form-control box-size', 'placeholder' => 'ETR Code']) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row no-gutters mb-1">
                                        <div class="col-md-4">
                                            {{ Form::label('fiscal_month', 'Start of Fiscal Month*', ['class' => 'col control-label text-danger']) }}
                                            <div class='col'>
                                                {{ Form::text('fiscal_month', @$company['fiscal_month'], ['class' => 'form-control', 'placeholder' => date('d-m-Y'), 'id' => 'fiscal_month', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="clock_in" class="col control-label">Clock-in Time</label>
                                            <div class="col">
                                                {{ Form::input('time', 'clock_in', @$company->clock_in, ['class' => 'form-control', 'placeholder' => 'HH:MM']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="clock_out" class="col control-label">Clock-out Time</label>
                                            <div class="col">
                                                {{ Form::input('time', 'clock_out', @$company->clock_out, ['class' => 'form-control', 'placeholder' => 'HH:MM']) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row no-gutters">                                    
                                        <div class="col-md-4">
                                            <label for="performance_percent" class="col control-label">Performance Percentage</label>
                                            <div class="col">
                                                {{ Form::text('performance_percent', @$company->performance_percent, ['class' => 'form-control box-size', 'placeholder' => 'Performance Percentage']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="notification_number" class="col control-label">Notify Contact</label>
                                            <div class="col">
                                                {{ Form::text('notification_number', @$company->notification_number, ['class' => 'form-control box-size', 'placeholder' => 'Notify Contact']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="rate" class="col control-label">Labour Rate</label>
                                            <div class="col">
                                                {{ Form::text('rate', @$company->rate, ['class' => 'form-control box-size', 'placeholder' => 'Labour Rate']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row no-gutters mb-1">
                                        <div class="col-md-4">
                                            <label for="company_commission" class="col control-label">Company Commission</label>
                                            <div class="col">
                                                {{ Form::text('company_commission', @$company->company_commission, ['class' => 'form-control box-size', 'placeholder' => '0.00','readonly']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="commission_1" class="col control-label">Commission if Tier 1 sells</label>
                                            <div class="col">
                                                {{ Form::text('commission_1', @$company->commission_1, ['class' => 'form-control box-size', 'placeholder' => '0.00','readonly']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="commission_2" class="col control-label">Commission if Tier 2 sells</label>
                                            <div class="col">
                                                {{ Form::text('commission_2', @$company->commission_2, ['class' => 'form-control box-size', 'placeholder' => '0.00','readonly']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row no-gutters">
                                        <div class="col-md-5">
                                            <label for="commission_3" class="col control-label">Commission to Tier 1, if Tier 2 sells</label>
                                            <div class="col">
                                                {{ Form::text('commission_3', @$company->commission_3, ['class' => 'form-control box-size', 'placeholder' => '0.00','readonly']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row no-gutters">                                    
                                        <div class="col-md-4">
                                            <label for="rate" class="col control-label">Default Quote Type</label>
                                            <div class="col">
                                                <div class="form-check form-check-inline">
                                                  <input class="form-check-input" type="radio" name="default_quote_type" id="inlineRadio1" value="project" {{ (!$company->default_quote_type || $company->default_quote_type == 'project')? 'checked' : '' }}>
                                                  <label class="form-check-label" for="inlineRadio1">Project</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                  <input class="form-check-input" type="radio" name="default_quote_type" id="inlineRadio2" value="standard" {{ $company->default_quote_type == 'standard'? 'checked' : '' }}>
                                                  <label class="form-check-label" for="inlineRadio2">Standard</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>                                     
                                </div>                                 
                            </div>
                        </div>

                        <!-- Logo & Icons -->
                        <div class="tab-pane in" id="active2" aria-labelledby="active-tab2" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row no-gutters mb-1">
                                        <div class="col-md-6">
                                            <div class='col'>
                                                <label for="review_url">Business Google Review URL</label>
                                                <input type="text" id="review_url" name="review_url" class="form-control"
                                                    value="{{ @$company->review_url }}"
                                                    placeholder="Enter the full URL like 'https://g.page/r/XYZ123456789/review'"
                                                >
                                            </div>                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div class='col'>
                                                <label for="whatsapp_business_url">WhatsApp Business URL</label>
                                                <input type="text" id="whatsapp_business_url" name="whatsapp_business_url" class="form-control"
                                                    value="{{ @$company->whatsapp_business_url }}"
                                                    placeholder="Enter the full URL like 'https://wa.me/254123456789'"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                   
                                    <div class="row no-gutters mb-1">
                                        <div class="col-md-6">
                                            <div class='col'>
                                                <label for="website_url">Business Website URL</label>
                                                <input type="text" id="website_url" name="website_url" class="form-control"
                                                    value="{{ @$company->website_url }}"
                                                    placeholder="Enter the full URL like 'https://www.abc.com'"
                                                >
                                            </div>                                            
                                        </div>
                                        <div class="col-md-6">
                                            {{ Form::label('location', 'Sales Coverage Area', ['class' => 'col control-label']) }}
                                            <div class='col'>
                                                {{ Form::text('location', @$company['location'], ['class' => 'form-control box-size', 'placeholder' => 'Sales Coverage Area']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row no-gutters mb-1">
                                        <div class="col-md-6">
                                            <div class='form-group'>
                                                {{ Form::label('chatgpt_email', 'ChatGPT Email', ['class' => 'col control-label']) }}
                                                <div class='col'>
                                                    {{ Form::text('chatgpt_email', @$company['chatgpt_email'], ['class' => 'form-control box-size', 'placeholder' => 'ChatGPT Email']) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class='col'>
                                                <label for="facebook_url">Facebook URL</label>
                                                <input type="url" id="facebook_url" name="facebook_url" class="form-control"
                                                    value="{{ @$company->facebook_url }}"
                                                    placeholder="Enter the full URL like 'https://www.facebook.com'"
                                                >
                                            </div>   
                                        </div>
                                    </div>
                                    <div class="row no-gutters mb-1">
                                        <div class="col-md-6">
                                            <div class='col'>
                                                <label for="twitter_url">X (Twitter) URL</label>
                                                <input type="url" id="twitter_url" name="twitter_url" class="form-control"
                                                    value="{{ @$company->twitter_url }}"
                                                    placeholder="Enter the full URL like 'https://www.x.com/@username'"
                                                >
                                            </div>                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div class='col'>
                                                <label for="instagram_url">Instagram URL</label>
                                                <input type="url" id="instagram_url" name="instagram_url" class="form-control"
                                                    value="{{ @$company->instagram_url }}"
                                                    placeholder="Enter the full URL like 'https://www.instagram.com'"
                                                >
                                            </div>   
                                        </div>
                                    </div>
                                    <div class="row no-gutters mb-1">
                                        <div class="col-md-6">
                                            <div class='col'>
                                                <label for="tiktok_url">TikTok URL</label>
                                                <input type="url" id="tiktok_url" name="tiktok_url" class="form-control"
                                                    value="{{ @$company->tiktok_url }}"
                                                    placeholder="Enter the full URL like 'https://www.tiktok.com/@username'"
                                                >
                                            </div>                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div class='col'>
                                                <label for="linkedIn_url">LinkedIn URL</label>
                                                <input type="text" id="linkedIn_url" name="linkedIn_url" class="form-control"
                                                    value="{{ @$company->linkedIn_url }}"
                                                    placeholder="Enter the full URL like 'https://www.linkedIn.com'"
                                                >
                                            </div>                                            
                                        </div>
                                    </div>
                                     <hr>

                                    <div class="col">
                                        <div class="row">
                                            <div class="col-md-6">
                                                {{ Form::label('icon', trans('business.favicon'), ['class' => 'control-label']) }}
                                                <p class="mb-2"><br><img class="img-fluid"
                                                        src="{{ Storage::disk('public')->url('app/public/img/company/ico/' . @$company['icon']) }}"
                                                        alt="Business favicon"></p>
                                                {!! Form::file('icon', ['class' => 'input mb-1']) !!}
                                                <small>{{ trans('hrms.blank_field') }}<br>only .ico format accepted</small>
                                            </div>
                                            <div class="col-md-6">
                                                {{ Form::label('theme_logo', trans('business.theme_logo'), ['class' => 'control-label']) }}
                                                <p class="mb-2"><br><img class="img-fluid avatar-100"
                                                        src="{{ Storage::disk('public')->url('app/public/img/company/theme/' . @$company['theme_logo']) }}"
                                                        alt="Business header logo"></p>
                                                {!! Form::file('theme_logo', ['class' => 'input mb-1']) !!}
                                                <small>{{ trans('hrms.blank_field') }}<br>only jpg|png format accepted.<br>Recommended
                                                    dimensions are
                                                    80x80. Use small size file - it will load quickly.
                                                </small>                                                
                                            </div>
                                            <div class="col-md-6">
                                                {{ Form::label('logo', 'Company Invoice & Quote Letterhead', ['class' => 'control-label']) }}
                                                <p class="mb-2"><br><img class="img-fluid avatar-lg"
                                                        src="{{ Storage::disk('public')->url('app/public/img/company/' . @$company['logo']) }}"
                                                        alt="Business Logo"></p>
                                                {!! Form::file('logo', ['class' => 'input mb-2']) !!}
                                                <small>{{ trans('hrms.blank_field') }}<br>only jpg|png format accepted. <br>Recommended
                                                    dimensions are
                                                    500x280. Use small size file - it will load quickly.
                                                </small>                                                
                                            </div>
                                            <div class="col-md-6">
                                                {{ Form::label('footer', 'Company Invoice & Quote Footer', ['class' => 'control-label']) }}
                                                <p class="mb-2"><br><img class="img-fluid avatar-lg"
                                                        src="{{ Storage::disk('public')->url('app/public/img/company/' . @$company['footer']) }}"
                                                        alt="Business Footer Logo"></p>
                                                {!! Form::file('footer', ['class' => 'input mb-2']) !!}
                                                <small>{{ trans('hrms.blank_field') }}<br>only jpg|png format accepted. <br>Recommended
                                                    dimensions are
                                                    500x280. Use small size file - it will load quickly.
                                                </small>                                                
                                            </div>
                                            <div class="col-md-6">
                                                {{ Form::label('stamp', 'Stamp', ['class' => 'control-label']) }}
                                                <p class="mb-2"><br><img class="img-fluid avatar-lg"
                                                        src="{{ Storage::disk('public')->url('app/public/img/company/' . @$company['stamp']) }}"
                                                        alt="Business Stamp"></p>
                                                {!! Form::file('stamp', ['class' => 'input mb-2']) !!}
                                                <small>{{ trans('hrms.blank_field') }}<br>only jpg|png format accepted. <br>Recommended
                                                    dimensions are
                                                    500x280. Use small size file - it will load quickly.
                                                </small>                                        
                                            </div>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Whatsapp Meta config -->
                        <div class="tab-pane in" id="active3" aria-labelledby="active-tab3" role="tabpanel">
                            <div class="row no-gutters mb-1">
                                <div class="col-md-4">
                                    <div class='col'>
                                        <label for="review_url">Whatsapp Business Account ID</label>
                                        <input type="text" name="whatsapp_business_account_id" class="form-control"
                                            value="{{ @$company->whatsapp_business_account_id }}"
                                            placeholder=""
                                        >
                                    </div>                                            
                                </div>
                                <div class="col-md-4">
                                    <div class='col'>
                                        <label for="review_url">Whatsapp Business Phone Number ID</label>
                                        <input type="text" name="whatsapp_phone_no_id" class="form-control"
                                            value="{{ @$company->whatsapp_phone_no_id }}"
                                            placeholder=""
                                        >
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class='col'>
                                        <label for="review_url">Meta Developer App ID</label>
                                        <input type="text" name="meta_developer_app_id" class="form-control"
                                            value="{{ @$company->meta_developer_app_id }}"
                                            placeholder=""
                                        >
                                    </div>                                            
                                </div>
                            </div>
                            <div class="row no-gutters mb-1">
                                <div class="col-md-4">
                                    <div class='col'>
                                        <label for="review_url">Whatsapp Access Token</label>
                                        <textarea rows="1" name="whatsapp_access_token" class="form-control">{{ @$company->whatsapp_access_token }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class='col'>
                                        <label for="review_url">Whatsapp Verify Token</label>
                                        <textarea rows="1" name="whatsapp_verify_token" class="form-control">{{ @$company->whatsapp_verify_token }}</textarea>
                                    </div>
                                </div>
                            </div>        
                        </div>
                    </div>

                    <div class="edit-form-btn mt-3">
                        {{ link_to_route('biller.dashboard', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                        {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                    </div>                     
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>
@endsection

@section('after-scripts')
    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            monthDate: {
                autoHide: true,
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                format: 'MM-yyyy',
                onClose: function(dateText, inst) { 
                    $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                }
            },
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        };
        $('#fiscal_month').datepicker(config.date);
        function adjustValues(changedField) {
            let companyCommission = parseFloat($('input[name="company_commission"]').val()) || 0;
            let commission1 = parseFloat($('input[name="commission_1"]').val()) || 0;
            let commission2 = parseFloat($('input[name="commission_2"]').val()) || 0;
            let commission3 = parseFloat($('input[name="commission_3"]').val()) || 0;

            if (changedField === 'company_commission') {
                // Adjust commission_1 to make Rule 1 = 100
                commission1 = 100 - companyCommission;
                $('input[name="commission_1"]').val(commission1.toFixed(2));

                // Adjust commission_2 and commission_3 proportionally to make Rule 2 = 100
                let remaining = 100 - companyCommission;
                let totalOther = commission2 + commission3 || 1; // avoid divide by zero
                commission2 = (commission2 / totalOther) * remaining;
                commission3 = (commission3 / totalOther) * remaining;
                $('input[name="commission_2"]').val(commission2.toFixed(2));
                $('input[name="commission_3"]').val(commission3.toFixed(2));

            } else if (changedField === 'commission_1') {
                companyCommission = 100 - commission1;
                $('input[name="company_commission"]').val(companyCommission.toFixed(2));

            } else if (changedField === 'commission_2' || changedField === 'commission_3') {
                let remaining = 100 - companyCommission;
                if (changedField === 'commission_2') {
                    commission3 = remaining - commission2;
                    $('input[name="commission_3"]').val(commission3.toFixed(2));
                } else {
                    commission2 = remaining - commission3;
                    $('input[name="commission_2"]').val(commission2.toFixed(2));
                }
            }
        }

        $('input[name="company_commission"]').on('input', function() {
            adjustValues('company_commission');
        });
        $('input[name="commission_1"]').on('input', function() {
            adjustValues('commission_1');
        });
        $('input[name="commission_2"]').on('input', function() {
            adjustValues('commission_2');
        });
        $('input[name="commission_3"]').on('input', function() {
            adjustValues('commission_3');
        });
    </script>
@endsection