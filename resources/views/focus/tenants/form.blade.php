@include('tinymce.scripts')
<div class="row">
    <div class="col-6">
        <div class="card rounded">
            <div class="card-content">
                <div class="card-body">
                    <!-- Busniess Info -->
                    <h6 class="mb-2">Business Info</h6>
                    <div class="row no-gutters">
                        <div class="col-6">
                            <div class='form-group'>
                                {{ Form::label('customer', 'Search Business', ['class' => 'col control-label']) }}
                                <div class='col'>
                                    <select name="customer_id" id="customer"  data-placeholder="Search Business" @isset($tenant) disabled @endisset>
                                        @if (isset($tenant->package->customer))
                                            <option selected value="{{ @$tenant->package->customer_id }}">{{ $tenant->package->customer->company }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class='form-group'>
                                {{ Form::label('cname', 'Business Name', ['class' => 'col control-label']) }}
                                <div class='col'>
                                    {{ Form::text('cname', @$tenant['cname'], ['class' => 'form-control box-size', 'placeholder' => 'Business Name', 'cname' => 'cname', 'required' => 'required', 'readonly' => 'readonly']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('address', 'Street Address', ['class' => 'col control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('address', @$tenant['address'], ['class' => 'form-control box-size', 'placeholder' => 'Street Address', 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('country', trans('hrms.country'), ['class' => 'col control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('country', @$tenant['country'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.country'), 'country' => 'country', 'required' => 'required', 'readonly' => 'readonly']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-4">
                                <div class='form-group'>
                                    {{ Form::label('postbox', trans('hrms.postal'), ['class' => 'col control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('postbox', @$tenant['postbox'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.postal'), 'required' => 'required']) }}
                                    </div>
                                </div>    
                            </div>
                            <div class="col-4">
                                <div class='form-group'>
                                    {{ Form::label('email', 'Email Address', ['class' => 'col control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('email', @$tenant['email'], ['class' => 'form-control box-size', 'placeholder' => 'Email Address', 'email' => 'email', 'required' => 'required', 'readonly' => 'readonly']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class='form-group'>
                                    {{ Form::label('phone', trans('general.phone'), ['class' => 'col control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('phone', @$tenant['phone'], ['class' => 'form-control box-size', 'placeholder' => trans('general.phone'), 'phone' => 'phone', 'required' => 'required', 'readonly' => 'readonly']) }}
                                    </div>
                                </div>
                            </div>
                        </div>                    
                    </div>
                    <!-- End Busniess Info -->
                    <hr>

                    <!-- Billing Details -->
                    <div class="form-group">
                        <div class="row mb-2">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('parentAccount', 'Parent Business Account', ['class' => 'control-label']) }}
                                    <select name="parent_account_id" id="parentAccount"  data-placeholder="Search Parent Account">
                                        @if (@$tenant->parent_account)
                                            <option selected value="{{ $tenant->parent_account_id }}">
                                                {{ $tenant->parent_account->company }}
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label for="billing_date" class="form-label">Billing Date & Time</label>
                                <input type="datetime-local" class="form-control" id="billing_date" name="billing_date" value="{{@$tenant->billing_date}}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cutoff Date & Time</label>
                                @php
                                    $cutoffDate = '';
                                    if (@$tenant){
                                        $billingDate = $tenant->billing_date;
                                        $cutoffDate = $billingDate;
                                        $graceDays = +$tenant->grace_days;
                                        if ($graceDays) {
                                            $cutoffDate = date('Y-m-d H:i:s', strtotime($cutoffDate . " +{$graceDays} days"));
                                        }
                                        if (strtotime($billingDate) == strtotime($cutoffDate)) {
                                            $cutoffDate = date('Y-m-d H:i:s', strtotime($cutoffDate . " +7 days"));
                                        }
                                    }
                                @endphp
                                <input type="datetime-local" class="form-control" id="cutoff_date" name="cutoff_date" value="{{ $cutoffDate }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label for="billing_status" class="form-label">Billing Status</label>
                                <select name="billing_status" id="billing_status" class="form-control">
                                    @foreach(['active', 'onboarding'] as $u)
                                        <option value="{{$u}}" @if(@$tenant->billing_status === $u) selected @endif> {{ ucfirst($u) }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label for="grace_days" class="form-label">Grace Days</label>
                                <input type="number" name="grace_days" id="grace_days" class="form-control" value="{{@$tenant->grace_days ?? 0}}" required>
                            </div>
                            <div class="col-md-4">
                                @php
                                    $startDate = (new DateTime('2024-12-01'))->format('Y-m-d');
                                    $start = new DateTime($startDate);
                                    $end = new DateTime();
                                    // Calculate the difference between the two dates
                                    $difference = $start->diff($end);
                                    $points = 0;
                                    if (!$difference->invert){
                                        $months = $difference->y * 12 + $difference->m;
                                        $points = $months * 2;
                                    }
                                @endphp
                                <label for="loyalty_points" class="form-label">Loyalty Points</label>
                                <input type="number" id="loyalty_points" class="form-control" value="{{@$tenant->loyalty_points}}" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="billing_notes" >Notes</label>
                            <textarea name="billing_notes" id="billing_notes" class="col-8 col-lg-8 tinyinput" cols="30" rows="10" placeholder="Additional details on billing..."
                                      aria-label="Billing Notes">
                                {{@$tenant->billing_notes}}
                            </textarea>
                        </div>
                        @if(access()->allow('disable-client-accounts') && @$tenant)
                            @if($allStatusZero)
                                <div class="col-12 col-6 mt-2">
                                    <a href="{{ route('biller.tenants.disable-client-accounts', ['allStatusZero' => intval($allStatusZero) , 'clientBusinessId' => @$tenant->id]) }}"
                                       class="btn btn-success"
                                       style="width: 300px"
                                       onclick="event.preventDefault(); confirmEnableClientAccounts('{{ route('biller.tenants.disable-client-accounts', ['allStatusZero' => intval($allStatusZero) , 'clientBusinessId' => @$tenant->id]) }}')">
                                        <i class="fa fa-refresh"></i> Enable Client Accounts
                                    </a>
                                </div>
                            @else
                                <div class="col-12 col-6 mt-2">
                                    <a href="{{ route('biller.tenants.disable-client-accounts', ['allStatusZero' => intval($allStatusZero) , 'clientBusinessId' => @$tenant->id]) }}"
                                       class="btn btn-danger"
                                       style="width: 300px"
                                       onclick="event.preventDefault(); confirmDisableClientAccounts('{{ route('biller.tenants.disable-client-accounts', ['allStatusZero' => intval($allStatusZero) , 'clientBusinessId' => @$tenant->id]) }}')">
                                        <i class="fa fa-trash"></i> Disable Client Accounts
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                    <!-- End Billing Details -->
                </div>
            </div>
        </div>
    </div>

    <!-- Package Info -->
    <div class="col-6 pl-0 pb-0">
        <div class="card rounded">
            <div class="card-content" style="padding-bottom: 450px;">
                <div class="card-body">
                    <div class="row mb-1">
                        <div class="col-12">
                            <h6 class="mb-2">Package Info</h6>
                            <div class='form-group'>
                                {{ Form::label('date', 'Date', ['class' => 'col control-label']) }}
                                <div class='col'>
                                    {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row no-gutters">
                                    <div class="col-6">
                                        <label for="name" class="col">Sales Agent</label>
                                        <div class="col">
                                            <select name="sales_agent_id" id="sales_agent_id" class="form-control" required data-placeholder="Select Agent">
                                                <option value="">Select Agent</option>
                                                @foreach ($salesAgents as $agent)
                                                    <option value="{{ $agent['id'] }}" @if(@$tenant->sales_agent_id === $agent['id']) selected @endif>{{ $agent['first_name'] . " " . $agent['last_name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label for="name" class="col">Relationship Manager</label>
                                        <div class="col">
                                            <select name="relationship_manager_id" id="relationship_manager_id" class="form-control" required data-placeholder="Select Agent">
                                                <option value=""></option>
                                                @foreach ($salesAgents as $agent)
                                                    <option value="{{ $agent['id'] }}" @if(@$tenant->relationship_manager_id === $agent['id']) selected @endif>{{ $agent['first_name'] . " " . $agent['last_name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class="row no-gutters">
                                    <div class="col-8">
                                        {{ Form::label('account_plan', 'Account Plan', ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            <select name="package_id" id="package" class="custom-select" data-placeholder="Select a Package">
                                                <option value="">Select a Package</option>
                                                @foreach ($tenant_services as $service)
                                                    @if($service->package && $service->package->first())
                                                        @php $servicePkg = $service->package->first() @endphp
                                                        <option 
                                                            value="{{ $service->id }}" 
                                                            price="{{ $servicePkg->price }}" 
                                                            {{ @$tenant->package->service->id == $service->id? 'selected' : '' }}
                                                        >
                                                            {{ $servicePkg->name }} Price - {{ amountFormat($servicePkg->price) }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>                                    
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <label for="no_of_users" class="col">Number of Users</label>
                                        <div class="col">
                                            {{ Form::number('no_of_users', @$tenant->package->no_of_users, ['class' => 'form-control', 'id' => 'no_of_users', 'placeholder' => '0']) }}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="subscriptionRate" class="col">Subscription Rate</label>
                                        <div class="col">
                                            {{ Form::text('subscription_rate', numberFormat(@$tenant->package->subscription_rate), ['class' => 'form-control', 'id' => 'subscriptionRate', 'placeholder' => '0.00']) }}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        {{ Form::label('vat_rate', 'Select VAT', ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            <select name="vat_rate" id="vatRate" class="custom-select">
                                                <option value="">-- VAT --</option>
                                                @foreach ($additionals as $row)
                                                    <option value="{{ $row->value }}" {{ @$tenant && +$tenant->package->vat_rate == +$row->value? 'selected' : '' }}>
                                                        {{ $row->name }}
                                                    </option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='form-group'>
                                {{ Form::label('subscr_term', 'Subscription Term', ['class' => 'col control-label']) }}
                                <div class='col'>
                                    @php
                                        $terms = ['1' => 'MONTHLY', '3' => '3 MONTHS', '6' => '6 MONTHS', '12' => 'ANNUALLY'];
                                    @endphp
                                    <select name="subscr_term" id="subscr_term" class="custom-select">
                                        @foreach ($terms as $key => $value)
                                            <option value="{{ $key }}" {{ @$tenant->package->subscr_term == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>                            
                        </div>
                    </div> 
                    <div class="form-group">
                        <div class="col">
                            <h4><b>Net Cost:</b> <span class="net-cost">0.00</span></h4>
                            <h4><b>VAT:</b> <span class="vat">0.00</span></h4>
                            <h4><b>Total Cost:</b> <span class="total-cost">0.00</span></h4>
                            {{ Form::hidden('net_cost', null, ['id' => 'net_cost']) }}
                            {{ Form::hidden('vat', null, ['id' => 'vat']) }}
                            {{ Form::hidden('total_cost', null, ['id' => 'total_cost']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section("after-scripts")
@include('focus.tenants.form_js')
@endsection    
