<div class="modal" id="attachLabourModal" role="dialog" aria-labelledby="data_project" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title content-header-title" id="data_project">Attach Employee Hours</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body">
                {{ Form::open(['route' => 'biller.quotes.storeverified', 'id' => 'attachLabourForm']) }}   
                    <div id="project_name" class="mb-1"></div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="date">Date</label>
                            <input type="text" name="mdl_date" class="form-control datepicker" id="job_date" >
                        </div>
                        <div class="col-md-6">
                            <label for="type">Job Type</label>
                            <select name="mdl_job_type" id="job_type" class="custom-select">
                                <option value="">-- Select Job Type --</option>
                                @foreach(['diagnosis', 'repair', 'maintenance', 'installation', 'supply', 'special_movement_allowance', 'paid_idle_time', 'others'] as $value)
                                    <option value="{{ $value }}">
                                        @php
                                            if ($value == 'diagnosis') echo 'Diagnosis / Site Survey';
                                            elseif ($value == 'special_movement_allowance') echo 'Special Movement Allowance (> 3Hrs)';
                                            elseif ($value == 'paid_idle_time') echo 'Paid Idle Time';
                                            else echo ucfirst($value);
                                        @endphp
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="assigns">Employee</label>
                            <select name="mdl_employee" class="form-control select2" id="employee" data-placeholder="Search Employee" multiple>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee['id'] }}">
                                        {{ $employee->fullname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="job_card">Job Card / DNote</label>
                            <div class="row no-gutters">
                                <div class="col-md-6">
                                    <select name="mdl_ref_type" class="custom-select" id="mdl_ref_type">
                                        @foreach (['jobcard' => 'Job Card', 'dnote' => 'Delivery Note'] as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="mdl_jobcard" id="job_card" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="hrs">Labour Hours <span id="expectedHrs" class="text-primary"></span></label>
                            <input type="text" name="mdl_hrs" id="hrs" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="hrs">Is Payable</label>
                            <select name="mdl_is_payable" id="is_payable" class="custom-select">
                                @foreach(['1' => 'Yes','0' => 'No'] as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="note">Note</label>
                            <input type="text" name="mdl_note" id="note" class="form-control">
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="attachLabourBtn">Attach</button>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>