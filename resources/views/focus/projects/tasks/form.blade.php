<form id="data_form_task" class="todo-input">
    <div class="card-body">
        <div class="row">
            <fieldset class="form-group col-md-10">
                <input type="text" class="new-todo-item form-control" placeholder="{{trans('tasks.name')}}" name="name" value="{{$tasks->name}}">
            </fieldset>
            <fieldset class="form-group col-md-2">
                <select class="custom-select" name="item_type">
                    @foreach(['task', 'title', 'subtitle'] as $row)
                        <option value="{{ $row }}" {{ @$tasks->item_type == $row? 'selected' : '' }}>
                            {{ ucfirst($row) }}
                        </option>
                    @endforeach
                </select>
            </fieldset>
        </div>
        <div class="row">
            <fieldset class="form-group col-md-4">
                <select class="custom-select" id="todo-select" name="status">
                    <option>-- Select Task Status --</option>
                    @foreach($mics->where('section',2)->unique('name') as $row)
                        <option value="{{ $row['id'] }}" {{ $tasks->status == $row['id']? 'selected' : '' }}> {{ $row['name'] }}</option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="form-group col-md-4">
                @php
                    $details = [
                        'Low' => trans('tasks.Low'),
                        'Medium' => trans('tasks.Medium'),
                        'High' => trans('tasks.High'),
                        'Urgent' => trans('tasks.Urgent'),
                    ];
                @endphp
                <select class="custom-select" id="todo-select" name="priority">
                    <option>-- Select Task Priority --</option>
                    @foreach ($details as $key => $value)
                        <option value="{{ $key }}" {{ $tasks->priority == $key? 'selected' : '' }} >{{ $value }}</option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="form-group col-md-4">
                <select class="form-control  select-box" name="tags[]" id="tags" data-placeholder="{{trans('tags.select')}}" multiple>
                    @php($tags = $tasks->tags->pluck('id')->toArray())
                    @foreach($mics->where('section','=',1) as $tag)
                            <option value="{{$tag['id']}}" {{ in_array($tag->id, $tags)? 'selected' : '' }}>
                                {{$tag['name']}}
                            </option>
                        @endforeach
                </select>
            </fieldset>
        </div>

        <fieldset class="form-group col-12">
            <textarea class="new-todo-item form-control" placeholder="{{trans('tasks.description')}}" rows="6" name="description">{{ @$tasks->description }}</textarea>                                    
        </fieldset>

        <div class="form-group row">
            <div class="col-md-4 col-xs-12 mt-1">
                <label class="col-sm-4 col-xs-6 control-label" for="sdate">{{trans('meta.from_date')}}</label>
                <div class="row no-gutters">
                    <div class="col-6">
                        <input type="text" class="form-control from_date required" placeholder="Start Date" name="start" autocomplete="false" data-toggle="datepicker">
                    </div>
                    <div class="col-6">
                        <input type="time" name="time_from" class="form-control" value="{{timeFormat($tasks->start)}}">
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-xs-12 mt-1"">
                <label class="col-sm-4 col-xs-6 control-label" for="sdate">{{trans('meta.to_date')}}</label>
                <div class="row no-gutters">
                    <div class="col-6">
                        <input type="text" class="form-control required to_date" placeholder="End Date" name="duedate" data-toggle="datepicker" autocomplete="false">
                    </div>
                    <div class="col-6">
                        <input type="time" name="time_to" class="form-control" value="{{timeFormat($tasks->duedate)}}">
                    </div>                    
                </div>
            </div>

            <div class="col-md-4 col-xs-12 mt-1">
                <label class="col-sm-4 col-xs-6 control-label" for="sdate">{{ trans('tasks.link_to_calender') }}</label>
                <div class="row">
                    @if (isset($tasks->events))
                        <div class="col-4"><input type="checkbox" class="form-control" name="link_to_calender" checked></div>
                        <div class="col-8">{{ Form::text('color', $tasks->events['color'],['class' => 'form-control round', 'id'=>'color_t','placeholder' => trans('miscs.color'),'autocomplete'=>'off','value'=>$tasks->events['color']]) }}</div>
                    @else
                        <div class="col-4"><input type="checkbox" class="form-control" name="link_to_calender"></div>
                        <div class="col-8">{{ Form::text('color', '#0b97f4',['class' => 'form-control round', 'id'=>'color_t','placeholder' => trans('miscs.color'),'autocomplete'=>'off']) }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row fom-group">
            <div class="col-6">
                <fieldset class="form-group position-relative has-icon-left">
                    <select class="form-control select-box" name="employees[]" id="employee" data-placeholder="{{trans('tasks.assign')}}" multiple>
                        @php($users = $tasks->users->pluck('id')->toArray())
                        @foreach($employees as $employee)
                            <option value="{{$employee['id']}}" {{ in_array($employee->id, $users)? 'selected' : '' }}>{{ $employee->fullname }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>

            <div class="col-6">
                <select class="form-control select-box" name="milestone_id" id="milestone" data-placeholder="{{trans('tasks.assign')}}">
                    <option value="">-- Choose Milestone --</option>
                    @foreach($tasks->project->milestones as $milestone)
                        <option value="{{ $milestone->id }}" {{ @$tasks->milestone->id == $milestone->id? 'selected' : '' }}>
                            {{ $milestone->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <input type="hidden" value="{{ route('biller.tasks.store') }}" id="action-url_task">
</form>