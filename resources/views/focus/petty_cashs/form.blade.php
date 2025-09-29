<div class='form-group row'>
    <div class='col-4'>
        {{ Form::label( 'title', 'Title',['class' => 'control-label']) }}
        {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'Title']) }}
    </div>
    <div class="col-4">
        <label for="type">Item Type</label>
        <select name="item_type" id="item_type" class="form-control">
            <option value="purchase_requisition">Purchase Requisition</option>
            <option value="others">Others</option>
        </select>
    </div>
    <div class="col-4">
        {{ Form::label( 'purchase_requisition', 'Search Purchase Requisition',['class' => 'control-label']) }}
        <select name="purchase_requisition" id="purchase_requisition" class="form-control" data-placeholder="Search Purchase Requisition" >
            <option value=""></option>
            @foreach ($purchase_requisitions as $purchase_requisition)
                @php
                    $pr_tid = gen4tid('PR-', $purchase_requisition->tid);
                    $pr_name = $purchase_requisition->note;
                    $project_tid = $purchase_requisition->project ? gen4tid('PRJ-',$purchase_requisition->project->tid) : '';
                    $project_name = $purchase_requisition->project ? $purchase_requisition->project->name : '';
                    $mr_tid = $purchase_requisition->purchase_request ? gen4tid('REQ-',$purchase_requisition->purchase_request->tid) : '';

                    $full = $pr_tid . ' | ' .$pr_name. ' | '. $mr_tid . ' | '.$project_tid.' | '.$project_name;
                @endphp
                <option value="{{$purchase_requisition->id}}" {{@$petty_cash->purchase_requisition == $purchase_requisition->id ? 'selected' : ''}}>{{$full}}</option>
            @endforeach
        </select>
    </div>
    
</div>
<div class='form-group row'>
    <div class="col-2">
        {{ Form::label( 'date', 'Date',['class' => 'control-label']) }}
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Date']) }}
    </div>
    <div class="col-2">
        {{ Form::label( 'expected_date', 'Expected Date',['class' => 'control-label']) }}
        {{ Form::text('expected_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Expected Date']) }}
    </div>
    <div class="col-2">
        <label for="taxFormat" class="caption">Tax</label>
        <select class="custom-select" name="tax" id="tax">
            @foreach ($additionals as $row)
                <option value="{{ +$row->value }}" {{ +$row->value == @$petty_cash->tax ? 'selected' : ''}}>
                    {{ $row->name }} 
                </option>
            @endforeach                                                    
        </select>
    </div>
    <div class="col-2">
        <label for="user_type">User Type</label>
        <select name="user_type" id="user_type" class="form-control">
            <option value="">--select user type--</option>
            <option value="employee" {{ @$petty_cash->user_type == 'employee' ? 'selected' :'' }}>Employee</option>
            <option value="casual" {{ @$petty_cash->user_type == 'casual' ? 'selected' :'' }}>Casual Labourer</option>
            <option value="third_party_user" {{ @$petty_cash->user_type == 'third_party_user' ? 'selected' :'' }}>Third Party User</option>
        </select>
    </div>
    <div class="col-4 div_employee">
        <label for="employee">Search Employee</label>
        <select name="employee_id" id="employee" class="form-control" data-placeholder="Search Employee">
            <option value="">Search Employee</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" {{ $employee->id == @$petty_cash->employee_id ? 'selected' : '' }}>{{ $employee->fullname }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4 div_casual d-none">
        <label for="casual">Search Casual Labourer</label>
        <select name="casual_id" id="casual" class="form-control" data-placeholder="Search Casual Labourer">
            <option value="">Search Casual Labourer</option>
            @foreach ($casuals as $casual)
                <option value="{{ $casual->id }}" {{ $casual->id == @$petty_cash->casual_id ? 'selected' : '' }}>{{ $casual->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4 div_third_party_user d-none">
        <label for="third_party_user">Search Third Party User</label>
        <select name="third_party_user_id" id="third_party_user" class="form-control" data-placeholder="Search Third Party User">
            <option value="">Search Third Party User</option>
            @foreach ($third_party_users as $third_party_user)
                <option value="{{ $third_party_user->id }}" {{ $third_party_user->id == @$petty_cash->third_party_user_id ? 'selected' : '' }}>{{ $third_party_user->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row form-group">
    <div class="col-2">
        <label for="amount_given">Amount Given</label>
         {{ Form::text('amount_given', null, ['class' => 'form-control', 'placeholder' => '0.00']) }}
    </div>
    <div class="col-4">
        <label for="user">Search Approvers</label>
        <select name="approver_ids[]" id="user" class="form-control" data-placeholder="Search Approvers" multiple>
            <option value="">Search Approvers</option>
            @foreach ($employees as $user)
                <option value="{{ $user->id }}" {{ $user->id == @$petty_cash->user_id ? 'selected' : '' }}>{{ $user->fullname }}</option>
            @endforeach
        </select>
    </div>
     <div class='col-lg-6'>
        {{ Form::label( 'description', 'Description',['class' => 'control-label']) }}
        {{ Form::textarea('description', null, ['class' => 'form-control round', 'placeholder' => 'Description']) }}
    </div>
</div>
@include('focus.petty_cashs.partials.items')

<div class="form-group row">
    <div class="col-9">    
    </div>
    <div class="col-3">
        <label class="mb-0">Subtotal</label>
        <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>
        <label class="mb-0" id="tax-label">{{ trans('general.total_tax') }}</label>
        <input type="text" name="tax_amount" id="tax_amount" class="form-control" readonly>
        <label class="mb-0">{{trans('general.grand_total')}}
        </label>
        <input type="text" name="total" class="form-control" id="total" readonly>
        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }}
    </div>
</div>