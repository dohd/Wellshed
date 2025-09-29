<div class="form-group row">
    <div class="col-2">
        <label for="title">Requisition No.</label>
        {{ Form::text('tid', gen4tid('PR-', @$purchase_requisition? $purchase_requisition->tid : @$tid+1), ['class' => 'form-control', 'disabled']) }}
        {{ Form::hidden('tid', @$purchase_requisition? $purchase_requisition->tid : @$tid+1) }}
    </div>

    <div class="col-4">
        <label for="employee">Requestor</label>
        <select name="employee_id" id="user" class="form-control" data-placeholder="Search Employee" required disabled>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ @$purchase_requisition->employee_id == $user->id? 'selected' : '' }}>
                    {{ $user->full_name }}
                </option>
            @endforeach
        </select>
        {{ Form::hidden('employee_id', @$purchase_requisition->employee_id) }}
    </div>

    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div>

    <div class="col-2">
        <label for="priority">Priority Level</label>
        <select name="priority" id="priority" class="custom-select" disabled>
            @foreach (['low', 'medium', 'high'] as $val)
                <option value="{{ $val }}" {{ @$purchase_requisition->priority == $val? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
        {{ Form::hidden('priority', @$purchase_requisition->priority) }}
    </div>   

    <div class="col-2">
        <label for="expect_date">Expected Delivery Date</label>
        {{ Form::text('expect_date', null, ['class' => 'form-control datepicker', 'id' => 'expect_date']) }}
    </div>    
</div>

<div class="form-group row">
    <div class="col-4">
        <label for="title">Remark</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'readonly']) }}
    </div>
    <div class="col-2">
        <label for="type">Item Type</label>
        <select name="item_type" id="item_type" class="form-control" required disabled>
            <option value="">Select Item Type</option>
            <option value="stock" {{'stock' == @$purchase_requisition->item_type ? 'selected' : ''}}>Non Project</option>
            <option value="project" {{'project' == @$purchase_requisition->item_type ? 'selected' : ''}}>Project</option>
        </select>
        {{ Form::hidden('item_type', @$purchase_requisition->item_type) }}
    </div>
    <div class="col-3 div_project d-none">
        <label for="">Search Project</label> 
        <select name="project_id" id="project" class="form-control" data-placeholder="Search Project" disabled>
        </select>
        {{ Form::hidden('project_id', @$purchase_requisition->project_id) }}
    </div>
    <div class="col-3 div_milestone d-none">
        <label for="project_milestone" class="caption">Project Budget Line</label>        
        <select id="project_milestone" name="project_milestone_id" class="form-control" disabled>
            <option value="">Select a Budget Line</option>
    
        </select>
        {{ Form::hidden('project_milestone_id', @$purchase_requisition->project_milestone_id) }}
        {{ Form::hidden('purchase_requisition_id', @$purchase_requisition->id) }}
    </div>
</div>

<div class="form-group row">
    @include('focus.purchase_requisitions.partials.edit_requisition_items')
</div>
<div class="form-group row">
    {{-- <a href="javascript:" class="btn btn-success addProduct" id="addProduct"><i class="fa fa-plus-square"></i> Add Product</a> --}}
</div>


<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.purchase_requisitions.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        @php
            $disabled = '';
            if (isset($purchase_requisition) && $purchase_requisition->status == 'approved')
                $disabled = 'disabled';
        @endphp
        {{ Form::submit(@$purchase_requisition? 'Update' : 'Create', ['class' => 'form-control btn btn-primary text-white', $disabled]) }}
    </div>
</div>

