<div class="form-group row">
    <div class="col-2">
        <label for="organization_type">Type of Organization</label>
        <select name="organization_type" id="organization_type" class="form-control">
            @foreach (['private','government'] as $item)
                <option value="{{$item}}">{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label for="ticket">Ticket</label>
        <select class="form-control" name="lead_id" id="lead_id" data-placeholder="Search Ticket" required> 
            <option value=""></option>                                                
            @foreach ($leads as $lead)
                @php
                    if (!@$lead->id) continue;
                    $customer_name = '';
                    if ($lead->customer) {
                        $customer_name .= $lead->customer->company;
                        if ($lead->branch) $customer_name .= " - {$lead->branch->name}";
                    } else $customer_name = $lead->client_name;
                    
                    // create mode
                    $prefix = $prefixes[1];
                    if (isset($quote)) $prefix = $prefixes[2]; //edit mode
                @endphp
                <option 
                    value="{{ $lead->id }}" 
                    title="{{ $lead->title }}" 
                    client_ref="{{ $lead->client_ref }}"
                    customer_id="{{ $lead->client_id }}"
                    branch_id="{{ $lead->branch_id }}"
                    assign_to="{{ $lead->assign_to }}"
                    currencyId="{{ @$lead->customer->currency_id }}"
                    incomeCategory="{{ @$lead->category }}"
                    {{ $lead->id == @$tender->lead_id ? 'selected' : '' }}
                >
                    {{ gen4tid("{$prefix}-", $lead->reference) }} - {{ $customer_name }} - {{ $lead->title }}
                </option>
            @endforeach                                                                                             
        </select>
    </div>
    <div class="col-4">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="{{@$tender->title}}" class="form-control" required>
    </div>
    <div class="col-2">
        <label for="notify">Notify Team Members</label>
        <select name="notify" id="notify" class="form-control">
            <option value="">--select--</option>
            @foreach (['no','yes'] as $item)
                <option value="{{$item}}" {{@$tender->notify == $item ? 'selected' : ''}}>{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
   
</div>
<div class="form-group row col">
    <label for="description">Description</label>
    <textarea name="description" id="description" cols="30" class="form-control" rows="3">{{@$tender->description}}</textarea>
</div>
<div class="form-group row">
    <div class="col-3 d-none">
        <label for="date">Current Date</label>
        <input type="text" name="date" id="date" class="form-control datepicker">
    </div>
    <div class="col-3">
        <label for="site_visit_date">Site Visit Date</label>
        <input type="text" name="site_visit_date" id="site_visit_date" class="form-control datepicker">
    </div>
    <div class="col-3">
        <label for="submission_date">Tender Submission Date</label>
        <input type="text" name="submission_date" id="submission_date" class="form-control datepicker">
    </div>
    <div class="col-3">
        <label for="consultant">Consultant</label>
        <input type="text" name="consultant" id="consultant" value="{{@$tender->consultant}}" class="form-control">
    </div>
    <div class="col-3">
        <label for="type">Tender Type</label>
        <select name="type" id="type" class="custom-select">
            @foreach (['tender', 'prequal', 'other'] as $item)
                <option value="{{ $item }}" {{ $item == @$tender->type? 'selected' : '' }}>
                    {{ ucfirst($item) }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row">
    <div class="col-4">
        <label for="team_member_ids">Team Members</label>
        <select name="team_member_ids[]" id="team_member_ids" class="form-control" data-placeholder="Search Team Members" multiple>
            <option value="">Search Team Members</option>
            @foreach ($users as $user)
            @php
                $ids = explode(',', @$tender->team_member_ids);
            @endphp
                <option value="{{$user->id}}" {{ in_array($user->id, (@$ids ?: []))? 'selected' : '' }}>{{$user->fullname}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="amount">Tender Amount</label>
        <input type="text" name="amount" id="amount" value="{{number_format(@$tender->amount, 2)}}" class="form-control">
    </div>
    <div class="col-3">
        <label for="bid_bond_processed">Bid Bond Duration (Days)</label>
        <input type="text" name="bid_bond_processed" id="bid_bond_processed" value="{{@$tender->bid_bond_processed}}" class="form-control">
    </div>
    <div class="col-3">
        <label for="bid_bond_amount">Bid Bond Amount</label>
        <input type="text" name="bid_bond_amount" id="bid_bond_amount" value="{{number_format(@$tender->bid_bond_amount, 2)}}" class="form-control">
    </div>
</div>
