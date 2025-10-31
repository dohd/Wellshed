@extends('core.layouts.public_app',['page'=>' class="horizontal-layout horizontal-menu 2-columns bg-full-screen-image" data-open="click" data-menu="horizontal-menu" data-col="2-columns"'])
@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <section class="flexbox-container">
                    <div class="col-12 d-flex align-items-center justify-content-center">
                        <div class="col-lg-4 col-md-8 col-10 box-shadow-2 p-0">
                            <div class="card border-grey border-lighten-3 m-0">
                                <div class="card-content">
                                    <div class="card-body">
                                        <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2">
                                            <span>Registration Details</span>
                                        </h6>

                                        <form class="form-horizontal" method="POST" action="{{ route('crm.register') }}" id="registerForm">
                                            {{ csrf_field() }}

                                            <!-- Tab Menu -->
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                                                       aria-selected="true">Account Details</a>
                                                </li>            
                                                <li class="nav-item">
                                                    <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9" role="tab"
                                                       aria-selected="false">Delivery Zone</a>
                                                </li>
                                            </ul>

                                            <!-- Tab Content -->
                                            <div class="tab-content px-1 pt-1">
                                                <!-- Account Details -->
                                                <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                                                    <fieldset class="form-group position-relative has-icon-left mb-1">
                                                        <label>Subscription Package</label>
                                                        <select name="sub_package_id" id="sbbpackage" class="form-control" required>
                                                            @foreach ($subpackages as $package)
                                                                <option value="{{ $package->id }}">
                                                                    {{ $package->name }} (Ksh. {{ numberFormat($package->price) }} / month)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('sub_package_id'))
                                                            <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <span aria-hidden="true">×</span>
                                                                </button>
                                                                {{ $errors->first('sub_package_id') }}
                                                            </div>
                                                        @endif
                                                    </fieldset>  
                                                    <div class="form-group">
                                                        <fieldset class="form-group position-relative has-icon-left mb-1">
                                                            <label>Segment (Office / Household)</label>
                                                            <select name="segment" id="segment" class="form-control" required>
                                                                @foreach (['office', 'household'] as $item)
                                                                    <option value="{{ $item }}">
                                                                        {{ ucfirst($item) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->has('segment'))
                                                                <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                        <span aria-hidden="true">×</span>
                                                                    </button>
                                                                    {{ $errors->first('segment') }}
                                                                </div>
                                                            @endif
                                                        </fieldset>
                                                    </div>     

                                                    <fieldset class="form-group position-relative has-icon-left mb-1">
                                                        <label>Company Name</label>
                                                        <input type="text" class="form-control" id="company" name="company" placeholder="Company Name" required>                                                
                                                        @if ($errors->has('company'))
                                                            <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <span aria-hidden="true">×</span>
                                                                </button>
                                                                {{ $errors->first('company') }}
                                                            </div>
                                                        @endif
                                                    </fieldset>                                                   

                                                    <fieldset class="form-group position-relative has-icon-left mb-1">
                                                        <label>First Name</label>
                                                        <input type="text" name="first_name" class="form-control" id="firstname"  placeholder="First Name" required>                                                
                                                        @if ($errors->has('first_name'))
                                                            <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <span aria-hidden="true">×</span>
                                                                </button>
                                                                {{ $errors->first('first_name') }}
                                                            </div>
                                                        @endif
                                                    </fieldset>
                                                    <fieldset class="form-group position-relative has-icon-left mb-1">
                                                        <label>Last Name</label>
                                                        <input type="text" name="last_name" class="form-control" id="lastname" placeholder="Last Name" required>                                                
                                                        @if ($errors->has('last_name'))
                                                            <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <span aria-hidden="true">×</span>
                                                                </button>
                                                                {{ $errors->first('last_name') }}
                                                            </div>
                                                        @endif
                                                    </fieldset>
                                                    <fieldset class="form-group position-relative has-icon-left mb-1">
                                                        <label>Phone Number</label>
                                                        <input type="text" name="phone_no" class="form-control" id="primarycontact" placeholder="Phone Number" required>                                                
                                                        @if ($errors->has('phone_no'))
                                                            <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <span aria-hidden="true">×</span>
                                                                </button>
                                                                {{ $errors->first('phone_no') }}
                                                            </div>
                                                        @endif
                                                    </fieldset>
                                                    <fieldset class="form-group position-relative has-icon-left mb-1">
                                                        <label>Email</label>
                                                        <input type="text" class="form-control" id="email" name="email" placeholder="Email" required>                                                
                                                        @if ($errors->has('email'))
                                                            <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <span aria-hidden="true">×</span>
                                                                </button>
                                                                {{ $errors->first('email') }}
                                                            </div>
                                                        @endif
                                                    </fieldset>

                                                    <label>Password</label>
                                                    <fieldset class="form-group position-relative has-icon-left">
                                                        <input type="password" class="form-control form-control-lg" id="user-password" name="password" placeholder="Enter Password" required>
                                                        <div class="form-control-position"><i class="fa fa-key"></i></div>                                                        
                                                    </fieldset>
                                                </div>

                                                <!-- Delivery Information -->
                                                <div class="tab-pane" id="tab9" role="tabpanel" aria-labelledby="base-tab9">
                                                    <div class="form-group">
                                                        <label>Delivery Zone</label>
                                                        <fieldset class="form-group position-relative has-icon-left mb-1">
                                                            <select name="target_zone_id" id="targetzone" class="form-control" data-placeholder="Choose Zone" required>
                                                                <option></option>
                                                                @foreach ($targetzones as $zone)
                                                                    <option value="{{ $zone->id }}">
                                                                        {{ $zone->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->has('target_zone_id'))
                                                                <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                        <span aria-hidden="true">×</span>
                                                                    </button>
                                                                    {{ $errors->first('target_zone_id') }}
                                                                </div>
                                                            @endif
                                                        </fieldset>                                                        
                                                    </div> 
                                                    <div class="form-group d-none">
                                                        <label>Locations</label>
                                                        <fieldset class="position-relative has-icon-left mb-1">
                                                            <select name="target_zone_item_id[]" id="targetzoneItem" class="form-control" multiple required>
                                                            </select>
                                                            @if ($errors->has('target_zone_item_id'))
                                                                <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                        <span aria-hidden="true">×</span>
                                                                    </button>
                                                                    {{ $errors->first('target_zone_item_id') }}
                                                                </div>
                                                            @endif
                                                        </fieldset>                                                        
                                                    </div>                                                    
                                                </div>
                                            </div>

                                            @if(session('flash_user_error'))
                                                <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                    <button type="button" class="close" data-dismiss="alert"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                    {{session('flash_user_error')}}
                                                </div>
                                            @endif

                                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                                <i class="fa fa-user-circle-o" aria-hidden="true"></i> Register
                                            </button>                                                        
                                        </form>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div>
                                        <a href="{{ route('login') }}" class="card-link">Back to Login</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
@endsection

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {};

    const targetZones = @json($targetzones);

    const Form = {
        init() {
            $('#segment, #targetzone, #targetzoneItem').select2({allowClear: true});

            $('#segment').change(Form.segmentChange).change();
            $('#targetzone').change(Form.targetZoneChange).change();
            $("#registerForm").submit(Form.formSubmit); 
        },

        segmentChange() {
            $('#firstname, #lastname, #company').val('');
            if ($(this).val() === 'office') {
                $('#company').closest('.form-group').removeClass('d-none');
                $('#company').attr('required', true);
                $('#firstname, #lastname').each(function() {
                    $(this).closest('.form-group').addClass('d-none');
                    $(this).attr('required', false);
                });
            } else {
                $('#company').closest('.form-group').addClass('d-none');
                $('#company').attr('required', false);
                $('#firstname, #lastname').each(function() {
                    $(this).closest('.form-group').removeClass('d-none');
                    $(this).attr('required', true);
                });
            }
        },

        targetZoneChange() {
            $('#targetzoneItem').html('').closest('.form-group').addClass('d-none');
            if ($(this).val()) {
                const zone = targetZones.filter(zone => zone.id == $(this).val())[0] || null;
                if (zone?.id) {
                    $('#targetzoneItem').closest('.form-group').removeClass('d-none');
                    zone.items.forEach(v => {
                        $('#targetzoneItem').append(`<option value="${v.id}">${v.sub_zone_name}</option>`);
                    });
                }               
            }
        },

        formSubmit() {
            {{-- let isValid = true;
            $(this).find("input, select, textarea").each(function() {
                if ($.trim($(this).val()) === "") {
                    isValid = false;
                    $(this).css("border", "1px solid red"); // highlight empty field
                } else {
                    $(this).css("border", ""); // reset border if filled
                }
            });

            if (!isValid) {
                e.preventDefault(); // stop form submission
                alert("Please fill in all fields.");
            } --}}
        },
    };

    $(Form.init);
</script>
@endsection