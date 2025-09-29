<div class="form-group row">
    <div class="col-2">
        <label for="title">Material Requisition No.</label>
        {{ Form::text('tid', gen4tid('REQ-', @$purchase_request? $purchase_request->tid : @$tid+1), ['class' => 'form-control', 'disabled']) }}
        {{ Form::hidden('tid', @$purchase_request? $purchase_request->tid : @$tid+1) }}
    </div>

    <div class="col-4">
        <label for="employee">Requestor</label>
        <select name="employee_id" id="user" class="form-control" data-placeholder="Search Employee" required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ @$purchase_request->employee_id == $user->id? 'selected' : '' }}>
                    {{ $user->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div>

    <div class="col-2">
        <label for="priority">Priority Level</label>
        <select name="priority" id="priority" class="custom-select">
            @foreach (['low', 'medium', 'high'] as $val)
                <option value="{{ $val }}" {{ @$purchase_request->priority == $val? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div>   

    <div class="col-2">
        <label for="expect_date">Expected Delivery Date</label>
        {{ Form::text('expect_date', null, ['class' => 'form-control datepicker', 'id' => 'expect_date']) }}
    </div>    
</div>

<div class="form-group row">
    <div class="col-4">
        <label for="title">Remark</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div>
    <div class="col-2">
        <label for="type">Item Type</label>
        <select name="item_type" id="item_type" class="form-control" required>
            <option value="">Select Item Type</option>
            <option value="stock" {{'stock' == @$purchase_request->item_type ? 'selected' : ''}}>Non Project</option>
            <option value="project" {{'project' == @$purchase_request->item_type ? 'selected' : ''}}>Project</option>
            <option value="finished_goods" {{'finished_goods' == @$purchase_request->item_type ? 'selected' : ''}}>Finished Goods</option>
        </select>
    </div>
    <div class="col-3 div_project d-none">
        <label for="">Search Project</label>
        <select name="project_id" id="project" class="form-control" data-placeholder="Search Project">
        </select>
    </div>
    <div class="col-3 div_milestone d-none">
        <label for="project_milestone" class="caption">Project Budget Line</label>        
        <select id="project_milestone" name="project_milestone_id" class="form-control">
            <option value="">Select a Budget Line</option>
    
        </select>
    </div>
    <div class="col-4 div_fg_goods d-none">
        <label for="fg_goods">Search Finished Goods</label>
        <select name="part_id" id="fg_goods" class="form-control" data-placeholder="Search Finished Goods">
            <option value="">Search Finished Goods</option>
            @foreach ($fg_goods as $good)
                <option value="{{ $good->id }}" {{ $good->id == @$purchase_request->part_id ? 'selected' : '' }}>{{ $good->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-6 mt-2">
                    
    <label for="">Select Users to Notify on MR Creation</label>
    <input type="checkbox" id="select_user">

</div>
<div class="form-group row div_reviewer d-none">
    <div class="col-12">
        <label for="reviewer">Reviewer</label>
        <select name="reviewer_ids[]" id="reviewer_ids" class="form-control" data-placeholder="Search Reviewer" multiple>
            <option value="">Search Reviewer</option>
            @foreach ($users as $user)
            @php
                $ids = explode(',', @$purchase_request->reviewer_ids);
            @endphp
                <option value="{{ $user->id }}" {{ in_array($user->id, (@$ids ?: []))? 'selected' : '' }}>
                    {{ $user->full_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row mt-2">
    @include('focus.purchase_requests.partials.requisition_items')
</div>
<div class="form-group row">
    <a href="javascript:" class="btn btn-success addProduct" id="addProduct"><i class="fa fa-plus-square"></i> Add Product</a>
</div>


<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.purchase_requests.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        @php
            $disabled = '';
            if (isset($purchase_request) && $purchase_request->status == 'approved')
                $disabled = 'disabled';
        @endphp
        {{ Form::submit(@$purchase_request? 'Update' : 'Create', ['class' => 'form-control btn btn-primary text-white', $disabled]) }}
    </div>
</div>

