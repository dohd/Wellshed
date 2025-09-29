<div class="modal" id="AddEmployeeModal" role="dialog" aria-labelledby="data_project" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title content-header-title" id="data_project">Employee Hours</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body"> 
                {{ Form::open(['route' => 'biller.labour_allocations.store']) }}   
                    <div id="project_name" class="mb-1"></div>
                    <input type="hidden"  name="project_id" id="project_id">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="date">Date</label>
                            <input type="text" class="form-control datepicker" name="date" id="date" >
                        </div>
                        <div class="col-md-3">
                            <label for="type">Job Type</label>
                            <select name="type" id="type" class="custom-select" required>
                                <option value="">-- Select Job Type --</option>
                                @php
                                    $job_types = ['diagnosis', 'repair', 'maintenance', 'installation', 'supply', 'special_movement_allowance', 'standby_time', 'others'];
                                @endphp
                                @foreach($job_types as $value)
                                    <option value="{{ $value }}">
                                        @php
                                            if ($value == 'diagnosis') echo 'Diagnosis / Site Survey';
                                            elseif ($value == 'special_movement_allowance') echo 'Special Movement Allowance (> 2 Hours)';
                                            elseif ($value == 'standby_time') echo 'Standby Time';
                                            else echo ucfirst($value);
                                        @endphp
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="reference">Reference</label>
                            <div class="row no-gutters">
                                <div class="col-md-6">
                                    <select name="ref_type" class="custom-select">
                                        @foreach (['jobcard' => 'Job Card', 'dnote' => 'Delivery Note'] as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="job_card" id="job_card" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="project_milestone" class="caption" style="display: inline-block;">Project Budget Line</label>
                            <select id="project_milestone" name="project_milestone" class="form-control">
                                <option value="">Select a Budget Line</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="task" class="caption" style="display: inline-block;">Tasks</label>
                            <select id="task" name="task_id" class="form-control">
                                <option value="0">Select a Task</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="task_percent">% Progress</label>
                            <input type="text" id="task_percent" value="0" class="form-control" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="percent_type">Progress Action</label>
                            <select name="percent_type" id="percent_type" class="form-control">
                                <option value="">-----Select Type -------</option>
                                <option value="increment">Increment</option>
                                <option value="decrement">Decrement</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="percent_qty">% Progress Value</label>
                            <input type="text" id="percent_qty" name="percent_qty"  class="form-control">
                        </div>
                    </div>
                    <div class="form-group row mb-1">
                        <div class="col-md-6">
                            <label for="note">Note</label>
                            <textarea name="note" id="note" cols="30" rows="1" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="sharedHrs" name="hour_type"> 
                                <label class="form-check-label" for="sharedHrs">Shared Daily Hours</label>
                            </div> 
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="weeklyHrs" name="hour_type"> 
                                <label class="form-check-label" for="weeklyHrs">Weekly Casual Hours</label>
                            </div>                     
                        </div>
                    </div>

                    <!-- Shared Casual Hours -->
                    <fieldset class="border p-1 mb-3 shared-hrs-ctn d-none">
                        <legend class="w-auto float-none h5">Shared Casuals Hours</legend>
                        <div class="row mb-1">
                            <div class="col-md-2">
                                <label for="hrs">Labour Hours <span id="expectedHrs" class="text-primary"></span> </label>
                                <input type="number" step="0.01" name="hrs" id="hrs" class="form-control">
                            </div>
                            <div class="col-md-1">
                                <label for="hrs">Is Payable</label>
                                <select name="is_payable" id="is_payable" class="custom-select">
                                    @foreach(['1' => 'Yes','0' => 'No'] as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="assigns">Employees</label>
                                <select class="form-control select-box" name="employee_id[]" id="employee" data-placeholder="Search for an Employee" multiple>
                                    <option value=""></option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee['id'] }}">
                                            {{ $employee->fullname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="casual_labourers">Casual Labourers</label>
                                <select class="form-control select-box" name="casual_labourer_id[]" id="casual_labourers" data-placeholder="Search for a Casual Labourer" multiple>
                                    <option value=""></option>
                                    @foreach($casualLabourers as $cL)
                                        <option value="{{ $cL['id'] }}">
                                            {{ $cL->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Weekly Casual Hours -->
                    @include('focus.labour_allocations.partials.weekly_hours')

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>