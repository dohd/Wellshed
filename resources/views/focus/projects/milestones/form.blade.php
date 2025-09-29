
<div class="row">
    <fieldset class="form-group col-12">
        <input type="text" class="name form-control" placeholder="{{ trans('additionals.name') }}" value="{{@$milestone->name}}" name="name" required>
    </fieldset>
</div>

<fieldset class="form-group">
    <textarea class="descr form-control" placeholder="{{ trans('tasks.description') }}" rows="6" name="description">{{@$milestone->note}}</textarea>
</fieldset>

<div class="form-group row mt-3">
    {{-- <div class="col-4">
        <label for="sdate">{{ trans('general.due_date') }}</label>
        <input type="text" class="form-control required to_date" placeholder="End Date" name="duedate"
            data-toggle="datepicker" autocomplete="false">
        <input type="time" name="time_to" class="form-control to_time" value="23:59">
    </div> --}}
    <div class="col-md-3 col-xs-12">
        <label class="col-sm-3 col-xs-6 control-label" for="sdate">{{ trans('meta.from_date') }}</label>
        <div class="row no-gutters">
            <div class="col">
                {{ Form::text('start_date', dateFormat(@$milestone->start_date), ['id' => 'start_date', 'class' => 'form-control from_date required', 'data-toggle' => 'datepicker', 'required' => 'required']) }}
            </div>
            <div class="col">
                {{ Form::time('time_from', timeFormat(@$milestone->start_date), ['id' => 'time_from', 'class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>

    <div class="col-md-3 col-xs-12">
        <label class="col-sm-3 col-xs-6  control-label" for="sdate">{{ trans('meta.to_date') }}</label>
        <div class="row no-gutters">
            <div class="col">
                {{ Form::text('end_date', dateFormat(@$milestone->end_date), ['id' => 'end_date', 'class' => 'form-control to_date required', 'data-toggle' => 'datepicker', 'required' => 'required']) }}
            </div>
            <div class="col">
                {{ Form::time('time_to', timeFormat(@$milestone->end_date), ['id' => 'time_to', 'class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="col-3">
        {{ Form::label('color', trans('miscs.color'), ['class' => 'col-2 control-label']) }}
        {{ Form::text('color', '#0b97f4', ['class' => 'form-control round color', 'id' => 'color', 'placeholder' => trans('miscs.color'), 'autocomplete' => 'off']) }}
    </div>
    <div class="col-3">
        <label for="amount">
            <span class="text-primary">
                (Budget Limit: <span class="milestone-limit font-weight-bold text-dark">0.00</span>)
            </span>
        </label>
        <input type="text" class="form-control amount" name="amount" id="milestone-amount"
            placeholder="Milestone Amount" value="{{+@$milestone->amount}}" required readonly>
    </div>
</div>
<div class="form-group row">

    <div class="col-6">
        <label for="users">Assign Users</label>
        <fieldset class="form-group position-relative has-icon-left">
            <select class="form-control  select-box" name="employees[]" id="employee" data-placeholder="{{trans('tasks.assign')}}" multiple>
                @foreach($employees as $employee)
                    <option value="{{$employee['id']}}" {{ in_array($employee->id, (@$milestone->users ? @$milestone->users->pluck('id')->toArray(): []))? 'selected' : '' }}>{{$employee['first_name']}} {{$employee['last_name']}}</option>
                @endforeach
            </select>
        </fieldset>
    </div>
    <div class="col-4">
        <label for="budgets">Budgets</label>
        <select name="budget_id" id="budget" class="form-control" data-placeholder="Choose Budget">
            <option value="">Choose Budget</option>
            @foreach ($budgets as $budget)
                @if ($budget)
                    @php

                        $budget_name = '';
                        if ($budget->quote) {
                            $budget_name = gen4tid('QT-', $budget->quote->tid) . '-' . '-' . $budget->quote->notes;
                        }
                    @endphp
                    <option value="{{ $budget->id }}" {{$budget->id == @$milestone->budget_id ? 'selected' : ''}}>{{ $budget_name }}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>
{{-- <input type="hidden" value="{{route('biller.projects.store_meta')}}" id="action-url"> --}}
<input type="hidden" value="{{ $project->id }}" name="project_id">
{{-- <input type="hidden" value="2" name="obj_type"> --}}
@include('focus.projects.milestones.partials.budget_items')
