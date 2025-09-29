<div class="row">
    <fieldset class="form-group position-relative has-icon-left  col-md-4">
        <select id="wip_account" name="wip_account_id" class="form-control required select-box"  data-placeholder="Choose WIP Account" required>
            <option value=""></option>
            @foreach ($accounts as $row)
                <option value="{{ $row->id }}" {{ $row->id == @$project->wip_account_id? 'selected' : '' }}>
                    {{ $row->holder}}
                </option>
            @endforeach
        </select>
    </fieldset>

    <fieldset class="form-group position-relative has-icon-left  col-md-4">
        <select id="person" name="customer_id" class="form-control required select-box"  data-placeholder="{{trans('customers.customer')}}">
            <option value=""></option>
            @if(@$project->customer)
                <option value="{{ $project->customer->id }}" selected>
                    {{ $project->customer->company}}
                </option>
            @else
                @php $customers = @$customers ?: [] @endphp
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $customer->id == @$project->customer_id? 'selected' : '' }}>
                        {{ $customer->company}}
                    </option>
                @endforeach
            @endif
        </select>
    </fieldset>

    <fieldset class="form-group position-relative has-icon-left  col-md-4">
        <select id="branch_id" name="branch_id" class="form-control required select-box"  data-placeholder="Choose Branch">
            <option value=""></option>
            @isset($project->branch)
                <option value="{{ $project->branch->id }}" selected>
                    {{ $project->branch->name}}
                </option>
            @endisset
        </select>
    </fieldset>
</div>

<div class="row {{ @$project && $project->quotes->count()? 'd-none' : '' }}">
    <fieldset class="form-group position-relative has-icon-left  col-md-12">
        <select id="quotes" name="quotes[]" class="form-control select-box"  data-placeholder="Choose Quote / PI" multiple>
            <option value=""></option>
        </select>
    </fieldset>
</div>

<div class="row">
    <fieldset class="form-group col-12"> 
        {{ Form::text('name', null, ['class' => 'new-todo-item form-control required proj_title', 'placeholder' => trans('projects.name')]) }}
    </fieldset>
</div>
<div class="row">
    <fieldset class="form-group col-md-4">
        @php $statuses = @$statuses ?: []; @endphp
        <select class="custom-select required" id="todo-select" name="status" required>
            <option value="">-- Select Project Status --</option>
            @foreach($statuses as $row)
                <option value="{{ $row->id }}" {{ $project->status == $row->id? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </fieldset>

    <fieldset class="form-group col-md-4">
        <select class="custom-select required" id="todo-select" name="priority" required>
            <option value="">-- Select Project Priority --</option>
            @foreach (['low', 'medium', 'high', 'urgent'] as $val)
                <option value="{{ $val }}" {{ in_array($project->priority, [$val, ucfirst($val)]) ? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </fieldset>

    <fieldset class="form-group col-md-4">
        @php
            $tags = @$tags ?: [];
        @endphp
        <select class="form-control select-box" name="tags[]" id="tags" data-placeholder="Choose tags" multiple>
            <option value=""></option>
            @foreach($tags as $row)
                <option value="{{ $row->id }}" {{ in_array($row->id, (@$project->tags->pluck('id')->toArray() ?: [])) ? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </fieldset>
</div>
<fieldset class="form-group position-relative has-icon-left col-12">
    <div class="form-control-position"><i class="icon-emoticon-smile"></i></div>
    {{ Form::text('short_desc', null, ['class' => 'new-todo-desc form-control required proj_short_descr', 'placeholder' => trans('tasks.short_desc'), 'id' => 'new-todo-desc']) }}
</fieldset>
<fieldset class="form-group col-12">
    {{ Form::textarea('note', null, ['class' => 'new-todo-item form-control required', 'placeholder' => trans('tasks.description'), 'rows' => '5']) }}
</fieldset>
<div class="form-group row">
    <div class="col-md-4 col-xs-12 mt-1">
        <label class="col-sm-4 col-xs-6 control-label" for="sdate">{{ trans('meta.from_date') }} & Start Time</label>
        <div class="row no-gutters">
            <div class="col">
                {{ Form::text('start_date', null, ['id' => 'start_date', 'class' => 'form-control from_date required', 'data-toggle' => 'datepicker', 'required' => 'required']) }}
            </div>
            <div class="col">
                {{ Form::time('time_from', timeFormat(@$project->start_date), ['id' => 'time_from', 'class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xs-12 mt-1">
        <label class="col-sm-4 col-xs-6  control-label" for="sdate">{{ trans('meta.to_date') }} & End Time</label>
        <div class="row no-gutters">
            <div class="col">
                {{ Form::text('end_date', null, ['id' => 'end_date', 'class' => 'form-control to_date required', 'data-toggle' => 'datepicker', 'required' => 'required']) }}
            </div>
            <div class="col">
                {{ Form::time('time_to', timeFormat(@$project->end_date), ['id' => 'time_to', 'class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>

    <div class="col-md-3 col-xs-12 mt-1">
        <label for="reminder_days">Reminder Days</label>
        <input type="text" name="reminder_days" value="{{@$project->reminder_days}}" id="reminder_days" class="form-control">
    </div>
</div>

<div class="row">
    <input type="hidden" name="worth" id="" value="0">
    {{-- <fieldset class="form-group col-md-4">
        {{ Form::text('worth', @$project->worth? numberFormat(@$project->worth) : '', ['class' => 'new-todo-item form-control', 'placeholder' => 'Budget Estimate: 0.00']) }}
    </fieldset> --}}
    <fieldset class="form-group col-md-4">
        <select class="form-control select-box" name="project_share">
            @php
                $shares_types = [
                    trans('projects.private'),
                    trans('projects.internal'),
                    trans('projects.external'),
                    trans('projects.internal_participate'),
                    trans('projects.external_participate'),
                    trans('projects.global_participate'),
                    trans('projects.global_view')
                ];
            @endphp
            <option value="" selected>-- {{trans('projects.project_share')}} --</option>
            @foreach ($shares_types as $i => $val)
                <option value="{{ $i }}" {{ @$project->project_share != '' && $i == $project->project_share? 'selected' : '' }}>
                    {{ $val }}
                </option>
            @endforeach
        </select>
    </fieldset>
    <fieldset class="form-group position-relative has-icon-left col-md-4">
        @php
            $employees = @$employees ?: [];
        @endphp
        <select class="form-control select-box" name="employees[]" id="employee" data-placeholder="{{trans('tasks.assign')}}" multiple required>
            @foreach($employees as $employee)
                <option value="{{ $employee['id'] }}" {{ in_array($employee->id, (@$project->users->pluck('id')->toArray() ?: []))? 'selected' : '' }}>
                    {{ $employee->fullname }}
                </option>
            @endforeach
        </select>
    </fieldset>
</div>
