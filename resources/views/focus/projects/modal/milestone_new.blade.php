<div class="modal" id="AddMileStoneModal" tabindex="-1" role="dialog" aria-labelledby="AddMileStoneLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            @if (isset($milestone))
                <!-- Edit mode -->
                <form id="data_form_mile_stone" class="todo-input">
                    <div class="modal-header">
                        <h5 class="modal-title" id="AddMileStoneLabel">Edit Milestone</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <fieldset class="form-group col-12">
                                <input type="text" class="name form-control" placeholder="{{trans('additionals.name')}}" name="name" value="{{ $milestone->name }}">
                            </fieldset>
                        </div>

                        <fieldset class="form-group">
                            <textarea class="descr form-control" placeholder="{{trans('tasks.description')}}" rows="6" name="description">{{ $milestone->note }}</textarea>
                        </fieldset>

                        <div class="form-group row mt-3">
                            <div class="col-4">
                                <label for="sdate">{{trans('general.due_date')}}</label>
                                <input type="text" class="form-control required to_date" placeholder="End Date" name="duedate" value="{{ dateFormat($milestone->due_date) }}" data-toggle="datepicker" autocomplete="false">
                                <input type="time" name="time_to" class="form-control to_time" value="23:59">
                            </div>
                            <div class="col-4">
                                {{ Form::label('color', trans('miscs.color'),['class' => 'col-2 control-label']) }}
                                {{ Form::text('color', $milestone->color, ['class' => 'form-control round color', 'id'=>'color','placeholder' => trans('miscs.color'),'autocomplete'=>'off']) }}
                            </div>
                            <div class="col-4">
                                <label for="amount">
                                    <span class="text-primary">
                                        (Budget Limit: <span class="milestone-limit font-weight-bold text-dark">0.00</span>)
                                    </span>
                                </label>
                                <input type="text" class="form-control amount" name="amount" value="{{ numberFormat($milestone->amount) }}" id="milestone-amount" placeholder="Milestone Amount" required>
                            </div>
                        </div>
                        <input type="hidden" value="{{ route('biller.projects.update_meta') }}" id="action-url">
                        <input type="hidden" value="{{ $project->id }}" name="project_id">
                        <input type="hidden" value="{{ $milestone->id }}" name="object_id">
                        <input type="hidden" value="2" name="obj_type">
                    </div>
                    <div class="modal-footer">
                        <fieldset class="form-group position-relative has-icon-left mb-0">
                            <button type="button" id="submit-data_mile_stone" class="btn btn-info add-todo-item" data-dismiss="modal">
                                <i class="fa fa-paper-plane-o d-block d-lg-none"></i>
                                <span class="d-none d-lg-block">Update</span>
                            </button>
                        </fieldset>
                    </div>
                </form>
            @else
                <form id="data_form_mile_stone" class="todo-input">
                    <div class="modal-header">
                        <h5 class="modal-title" id="AddMileStoneLabel">{{trans('projects.milestone_add')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <fieldset class="form-group col-12">
                                <input type="text" class="name form-control" placeholder="{{trans('additionals.name')}}" name="name">
                            </fieldset>
                        </div>

                        <fieldset class="form-group">
                            <textarea class="descr form-control" placeholder="{{trans('tasks.description')}}" rows="6" name="description"></textarea>
                        </fieldset>

                        <div class="form-group row mt-3">
                            <div class="col-4">
                                <label for="sdate">{{trans('general.due_date')}}</label>
                                <input type="text" class="form-control required to_date" placeholder="End Date" name="duedate" data-toggle="datepicker" autocomplete="false">
                                <input type="time" name="time_to" class="form-control to_time" value="23:59">
                            </div>
                            <div class="col-4">
                                {{ Form::label('color', trans('miscs.color'),['class' => 'col-2 control-label']) }}
                                {{ Form::text('color', '#0b97f4', ['class' => 'form-control round color', 'id'=>'color','placeholder' => trans('miscs.color'),'autocomplete'=>'off']) }}
                            </div>
                            <div class="col-4">
                                <label for="amount">
                                    <span class="text-primary">
                                        (Budget Limit: <span class="milestone-limit font-weight-bold text-dark">0.00</span>)
                                    </span>
                                </label>
                                <input type="text" class="form-control amount" name="amount" id="milestone-amount" placeholder="Milestone Amount" required>
                            </div>
                        </div>
                        <input type="hidden" value="{{route('biller.projects.store_meta')}}" id="action-url">
                        <input type="hidden" value="{{$project->id}}" name="project_id">
                        <input type="hidden" value="2" name="obj_type">
                    </div>
                    <div class="modal-footer">
                        <fieldset class="form-group position-relative has-icon-left mb-0">
                            <button type="button" id="submit-data_mile_stone" class="btn btn-info add-todo-item" data-dismiss="modal">
                                <i class="fa fa-paper-plane-o d-block d-lg-none"></i>
                                <span class="d-none d-lg-block">{{trans('general.add')}}</span></button>
                        </fieldset>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>