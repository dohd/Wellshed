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
                                        <div class="card-title text-center">
                                            <div class="p-1">
                                                <img class="avatar-100" src="{{ Storage::disk('public')->url('app/public/img/company/' . business()['logo']) }}" alt="Logo">
                                            </div>
                                        </div>
                                        <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2">
                                            <span>Registration Details</span>
                                        </h6>

                                        <form class="form-horizontal" method="POST" action="{{ route('crm.register') }}" id="registerForm">
                                            {{ csrf_field() }}

                                            <!-- Tab Menu -->
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                                                       aria-selected="true">Account</a>
                                                </li>            
                                                <li class="nav-item">
                                                    <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9" role="tab"
                                                       aria-selected="false">Delivery</a>
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

                                                    <fieldset class="form-group position-relative has-icon-left mb-1">
                                                        <label>First Name</label>
                                                        <input type="text" class="form-control" id="firstname" name="first_name" placeholder="First Name" required>                                                
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
                                                        <input type="text" class="form-control" id="lastname" name="last_name" placeholder="Last Name" required>                                                
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
                                                        <input type="text" class="form-control" id="primarycontact" name="primary_contact" placeholder="Phone Number" required>                                                
                                                        @if ($errors->has('primary_contact'))
                                                            <div class="alert bg-warning alert-dismissible m-1" role="alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <span aria-hidden="true">×</span>
                                                                </button>
                                                                {{ $errors->first('primary_contact') }}
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
                                                            <select name="target_zone_id" id="target_zone" class="form-control" required>
                                                                @foreach ($targetzones as $zone)
                                                                    <option value="{{ $zone->id }}">
                                                                        {{ $zone->name }}: {{ $zone->items->implode('sub_zone_name', ', ') }}
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
                                                    <div class="form-group">
                                                        <fieldset class="form-group position-relative has-icon-left mb-1">
                                                            <label>Segment</label>
                                                            <select name="segment" id="segment" class="form-control" required>
                                                                @foreach (['offices', 'households'] as $item)
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
<script>
    $("#registerForm").submit(function(e) {
        let isValid = true;

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
        }
    });
</script>
@endsection