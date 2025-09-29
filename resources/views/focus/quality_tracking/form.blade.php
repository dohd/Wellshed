<div class="row">
    <div class="col-4">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" required class="datepicker form-control box-size mb-2">
        {{-- {{ Form::text('date', null, ['class' => 'form-control round datepicker', 'id' => 'date_of_request']) }} --}}
    </div>
</div>


<div class="row">
    <div class="col-4">
        <label for="ticket">Customer</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select customer_id" name="customer_id" id="customer_id">
                {{-- @foreach ($clients as $client)
                    <option value="{{ +$client->id }}">{{ $client->name }}</option>
                @endforeach --}}
            </select>
        </div>
    </div>
    <div class="col-4">
        <label for="ticket">Branch</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select branch_id" name="branch_id" id="branch_id">
            </select>
        </div>
    </div>
    <div class="col-4">

        <div class="form-group">
            <label for="project" class="caption">Project</label>
            <select class="form-control" name="project" id="project" data-placeholder="Search Project by Name, Customer, Branch">
            </select>
        </div>


    </div>
</div>

<br>
<div class="row">
    <div class="col-8 mb-2">
        <label for="ticket">Parties involved</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select employee" name="employee[]" id="employee" multiple required>
                @foreach ($employees as $employee)
                    <option value="{{ +$employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-12 mb-2">
        <label for="ticket">Incident Description</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <textarea name="incident_desc" id="incident_desc-p0" rows="2" class="form-control tinyinput-small"
                      placeholder="Incident description"></textarea>
        </div>
    </div>
    <div class="col-12 mb-2">
        <label for="ticket">Root Cause</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <textarea name="route_course" id="route_course-p0" cols="35" rows="2" class="form-control tinyinput-small"
                      placeholder="Root Cause"></textarea>
        </div>
    </div>
</div>
<br>


<div class="row">
    <div class="col-4">
        <label for="ticket">Status</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select required" id="status" name="status">
                <option value="first-aid-case"> First Aid case </option>
                <option value="lost-work-day"> Lost Work day </option>
            </select>
        </div>
    </div>
    <div class="col-4">
        <label for="ticket">Responsibility</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select responsibility" name="responsibility" id="responsibility-p0">
                <option value="">Responsibility</option>
                @foreach ($employees as $employee)
                    <option value="{{ +$employee->id }}">{{ $employee->first_name }}
                        {{ $employee->last_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-4">
        <label for="ticket">Time to resolve(days)</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <input type="number" name="timing" id="timing-p0" class="form-control" placeholder="1" min="1">
        </div>
    </div>
</div>
<br>

<div class="row">

    <div class="mt-3 col-12" id="pro_tabs">

        <h2 class="mb-1">PDCA Cycle</h2>
        <h5 class="mb-2">
            The PDCA cycle or Plan-Do-Check-Act, is a four-step iterative management method used for the control and
            continuous improvement of processes. It promotes systematic problem-solving and continual enhancement.
        </h5>

        <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
            <li class="nav-item">
                <a class="nav-link active px-2" id="tab1" data-toggle="tab" href="#tab_data1" aria-controls="tab_data1" role="tab" aria-selected="true" style="font-size: 20px">
                    <i class="fa fa-lightbulb-o"></i> Plan
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link px-2" id="tab2" data-toggle="tab" href="#tab_data2" aria-controls="tab_data2" role="tab" aria-selected="true" style="font-size: 20px">
                    <i class="fa fa-gears"></i> Do
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link px-2" id="tab3" data-toggle="tab" href="#tab_data3" aria-controls="tab_data3" role="tab" aria-selected="true" style="font-size: 20px">
                    <i class="fa fa-life-bouy"></i> Check
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link px-2" id="tab4" data-toggle="tab" href="#tab_data4" aria-controls="tab_data4" role="tab" aria-selected="true" style="font-size: 20px">
                    <i class="fa fa-bullseye"></i> Act
                </a>
            </li>

        </ul>

        <div class="tab-content px-1 pt-1">

            <div class="tab-pane active in row" id="tab_data1" aria-labelledby="tab1" role="tabpanel">

                <div class="col-12 col-lg-10">
                    <h4>Plan</h4>
                    <h5>Develop a plan to address the problem or implement the improvement. This includes identifying resources, setting timelines, and assigning responsibilities.</h5>
                    <textarea name="plan" id="plan" class="tinyinput" cols="30" rows="10"></textarea>
                </div>

            </div>


            <div class="tab-pane row" id="tab_data2" aria-labelledby="tab2" role="tabpanel">

                <div class="col-12 col-lg-10">
                    <h4>Do</h4>
                    <h5>Implement the Plan, Document Observations & Record any issues, unexpected outcomes, or insights
                        gained during implementation.</h5>
                    <textarea name="do" id="do" class="tinyinput" cols="30" rows="10"></textarea>
                </div>

            </div>


            <div class="tab-pane row" id="tab_data3" aria-labelledby="tab3" role="tabpanel">

                <div class="col-12 col-lg-10">
                    <h4>Check</h4>
                    <h5>Analyze Results: Compare the collected data against the expected outcomes. <br>
                        Evaluate Effectiveness: Determine whether the plan worked as intended and met the goals.</h5>
                    <textarea name="check" id="check" class="tinyinput" cols="30" rows="10"></textarea>
                </div>

            </div>


            <div class="tab-pane row" id="tab_data4" aria-labelledby="tab4" role="tabpanel">

                <div class="col-12 col-lg-10">
                    <h4>Act</h4>
                    <h5>Standardize the Solution: If the plan was successful, implement the solution on a larger scale and standardize it across the organization.<br>
                        Make Adjustments: If the plan was not successful, identify what went wrong and make necessary adjustments.<br>
                        Continuous Improvement: Use the insights gained from the cycle to make ongoing improvements.</h5>
                    <textarea name="act" id="act" class="tinyinput" cols="30" rows="10"></textarea>
                </div>

            </div>


        </div>

    </div>



    <div class="mt-3 col-12">
        <hr class="px-4 mb-2">
        <div class="col-12 col-lg-10">
            <h5>Comments</h5>
            <div class="input-group">
                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                <textarea name="comments" id="comments-p0" cols="35" rows="2" class="form-control tinyinput" placeholder="Coments"></textarea>
            </div>
        </div>

    </div>




</div>
<br><br>

<!-- Countermeasure Closeout Section -->
<div class="row">
    <div class="my-3 col-12 col-lg-10" id="countermeasure_section">
        <h2 class="mb-1">Countermeasure Closeout</h2>
        <h5 class="mb-2">
            Document the countermeasures implemented and the closeout details. Ensure all steps are thoroughly completed and verified.
        </h5>

        <div class="row">
            <div class="col-12 mb-2">
                <label for="countermeasure">Countermeasure</label>
                <textarea
                        id="countermeasure" name="countermeasure" class="form-control tinyinput-small"
                        placeholder="Describe the specific actions taken to address the root cause and prevent recurrence"
                >
                </textarea>
            </div>
            <div class="col-6">
                <label for="responsible_person">Responsible Person</label>
                <select class="custom-select select2" name="cm_responsible_person" id="cm_responsible_person">
                    <option value="">Select Person</option>
                    @foreach ($employees as $employee)
                        <option value="{{ +$employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label for="completion_date">Completion Date</label>
                <input type="date" id="completion_date" name="completion_date" class="form-control mb-2">
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <label for="verification">Verification</label>
                <textarea
                        name="verification"
                        id="verification"
                        class="form-control tinyinput"
                        rows="3"
                        placeholder="Describe how the countermeasure was verified to be effective, including any tests conducted, observations made, and the outcomes. Mention who verified the countermeasure and any follow-up actions taken."
                >

                </textarea>
            </div>
        </div>
    </div>
</div>

