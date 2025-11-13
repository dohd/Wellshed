<div class="tab-content px-1 pt-1">
    <!-- Account Details -->
    <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
        <div class='mb-1'>
            <div><label for="onetimeFee">One-time Fee</label></div>                        
            <div class="d-inline-block custom-control custom-checkbox mr-1">
                <input type="radio" class="custom-control-input bg-primary client-type" name="onetime_fee" id="colorCheck1" value="include" checked>
                <label class="custom-control-label" for="colorCheck1">Include</label>
            </div>
            <div class="d-inline-block custom-control custom-checkbox mr-1">
                <input type="radio" class="custom-control-input bg-purple client-type" name="onetime_fee" value="exclude" id="colorCheck3">
                <label class="custom-control-label" for="colorCheck3">Exclude</label>
            </div>
        </div>

        <fieldset class="form-group position-relative has-icon-left mb-1">
            <label>Subscription Package</label>
            <select name="sub_package_id" id="subpackage" class="form-control" required>
                @foreach ($subpackages as $package)
                    <option value="{{ $package->id }}" {{ $package->id === @$customer->package->id? 'selected' : '' }}>
                        {{ $package->name }} (Ksh. {{ numberFormat($package->price) }} / Month)
                         - Up to {{ $package->max_bottle }} bottles
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
                        <option value="{{ $item }}" {{ $item === @$customer->segment? 'selected' : '' }}>
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
            <label for="company">Company Name</label>
            {{ Form::text('company', null, ['class' => 'form-control', 'id' => 'company']) }}
            @if ($errors->has('company'))
                <div class="alert bg-warning alert-dismissible m-1" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    {{ $errors->first('company') }}
                </div>
            @endif
        </fieldset>   

        <div class="row">
            <div class="col-md-6">
                <fieldset class="form-group position-relative has-icon-left mb-1">
                    <label>First Name</label>
                    {{ Form::text('first_name', @$customer->hrm->first_name, ['class' => 'form-control', 'id' => 'firstname', 'placeholder' => "First Name"]) }}
                    @if ($errors->has('first_name'))
                        <div class="alert bg-warning alert-dismissible m-1" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            {{ $errors->first('first_name') }}
                        </div>
                    @endif
                </fieldset>
            </div>
            <div class="col-md-6">
                <fieldset class="form-group position-relative has-icon-left mb-1">
                    <label>Last Name</label>
                    {{ Form::text('last_name', @$customer->hrm->last_name, ['class' => 'form-control', 'id' => 'lastname', 'placeholder' => "Last Name"]) }}
                    @if ($errors->has('last_name'))
                        <div class="alert bg-warning alert-dismissible m-1" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            {{ $errors->first('last_name') }}
                        </div>
                    @endif
                </fieldset>
            </div>
        </div>                                                        
        
        <div class="row">
            <div class="col-md-6">
                <fieldset class="form-group position-relative has-icon-left mb-1">
                    <label>Phone Number</label>
                    {{ Form::text('phone_no', @$customer->phone, ['class' => 'form-control', 'id' => 'phone_no', 'placeholder' => "2547XXXXXXXX", 'required' => 'required']) }}   
                    @if ($errors->has('phone_no'))
                        <div class="alert bg-warning alert-dismissible m-1" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            {{ $errors->first('phone_no') }}
                        </div>
                    @endif
                </fieldset>
            </div>
            <div class="col-md-6">
                <fieldset class="form-group position-relative has-icon-left mb-1">
                    <label>Email</label>
                    {{ Form::text('email', null, ['class' => 'form-control', 'id' => 'email', 'placeholder' => "e.g john@doe.com", 'required' => 'required']) }}
                    @if ($errors->has('email'))
                        <div class="alert bg-warning alert-dismissible m-1" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </fieldset>
            </div>
        </div>                                                    

        @if (!isset($customer))
        <label>Password</label>
        <fieldset class="form-group position-relative has-icon-left">
            <input type="password" class="form-control form-control-lg" id="password" name="password">
            <div class="form-control-position"><i class="fa fa-key"></i></div>                                                        
        </fieldset>
        @endif
    </div>

    <!-- Delivery Information -->
    <div class="tab-pane" id="tab9" role="tabpanel" aria-labelledby="base-tab9">
        <div class="form-group">
            <label>Delivery Zone</label>
            <fieldset class="form-group position-relative has-icon-left mb-1">
                <input type="hidden" name="target_zone_id" id="targetzone">
                <select name="target_zone_item_id[]" id="targetzoneItem" class="form-control" data-placeholder="Choose Zone Location" required>
                    <option></option>
                    @foreach ($targetzones as $zone)
                        <option value="{{ $zone->id }}" disabled>{{ $zone->name }}</option>
                        @foreach ($zone->items as $subzone)
                            <option value="{{ $subzone->id }}" data-target_zone_id="{{ $zone->id }}" {{ $subzone->id === @$customerZone->target_zone_item_id? 'selected' : '' }}>
                                {{ $subzone->sub_zone_name }}
                            </option>
                        @endforeach                                                                    
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
        {{-- <div class="form-group d-none">
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
        </div>  --}}  

        <fieldset class="form-group position-relative has-icon-left mb-1">
            <label>Building Name</label>
            <input type="text" class="form-control" id="building" name="building_name" value="{{ @$customer->mainAddress->building_name }}" placeholder="Building Name" required>                                                
            @if ($errors->has('building_name'))
                <div class="alert bg-warning alert-dismissible m-1" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    {{ $errors->first('building_name') }}
                </div>
            @endif
        </fieldset>  

        <div class="row">
            <div class="col-md-6">
                <fieldset class="form-group position-relative has-icon-left mb-1">
                    <label>Floor Number</label>
                    <input type="text" class="form-control" id="floor" name="floor_no" value="{{ @$customer->mainAddress->floor_no }}" placeholder="Floor Number">                                                
                    @if ($errors->has('floor_no'))
                        <div class="alert bg-warning alert-dismissible m-1" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            {{ $errors->first('floor_no') }}
                        </div>
                    @endif
                </fieldset>  
            </div>
            <div class="col-md-6">
                <label>Door Number</label>
                    <input type="text" class="form-control" id="door" name="door_no" value="{{ @$customer->mainAddress->door_no }}" placeholder="Door Number">                                                
                    @if ($errors->has('door_no'))
                        <div class="alert bg-warning alert-dismissible m-1" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            {{ $errors->first('door_no') }}
                        </div>
                    @endif
                </fieldset>  
            </div>
        </div>  

        <fieldset class="form-group position-relative has-icon-left mb-1">
            <label>Additional Info</label>
            <input type="text" class="form-control" id="additionalInfo" name="additional_info" value="{{ @$customer->mainAddress->additional_info }}" placeholder="Additional Info">                                                
            @if ($errors->has('additional_info'))
                <div class="alert bg-warning alert-dismissible m-1" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    {{ $errors->first('additional_info') }}
                </div>
            @endif
        </fieldset>                                                 
    </div>
</div>