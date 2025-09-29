<div class="row">
    <div class="col-sm-6">
        <div>
            <div class="form-group row">
                <div class="fcol-sm-12">
                    <h3 class="title pl-1">Repair Job Card Details</h3>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12"><label for="ref_type" class="caption">Search Project </label>
                    <div class="input-group">
                        <select class="form-control  round  select-box" name="project_id" id="project" required>
                            @foreach ($projects as $project)
                                <option 
                                    value="{{ $project->id }}" 
                                    customer_id="{{ $project->quote? $project->quote->customer_id : '' }}"
                                    branch_id="{{ $project->quote? $project->quote->branch_id : '' }}"
                                    {{ @$rjc && $rjc->project_id == $project->id? 'selected' : '' }}
                                >                                                       
                                    {{ gen4tid('Prj-', $project->tid) }} 
                                    [ {{ $project->quote_tids }} ] [ {{ $project->lead_tids }} ]                                                         
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>                                                
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-12">
                    <label for="attention" class="attention">Attention <span class="text-danger">*</span></label>
                    {{ Form::text('attention', null, ['class' => 'form-control round', 'placeholder' => 'Attention', 'id'=>'attention']) }}
                </div>                                        
            </div>
            <div class="form-group row">
                <div class="col-sm-4"><label for="tid" class="caption">Report No</label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                        {{ Form::text('tid', gen4tid('RjR-', @$rjc? $rjc->tid : $tid+1), ['class' => 'form-control round', 'disabled']) }}
                        <input type="hidden" name="tid" value="{{ @$rjc? $rjc->tid : $tid+1 }}">
                    </div>
                </div>
                <div class="col-sm-4"><label for="report_date" class="caption">Report {{trans('general.date')}}</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span>
                        </div>
                        {{ Form::text('report_date', null, ['class' => 'form-control round datepicker']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="reference" class="caption">Client Ref / Callout ID</label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                        </div>
                        {{ Form::text('client_ref', null, ['class' => 'form-control round ', 'placeholder' => 'Client Ref', 'id' => 'client_ref', 'required']) }}
                    </div>
                </div>                                        
            </div>
            <div class="form-group row">
                <div class="col-sm-4">
                    <label for="region" class="caption"> Region</label>
                    {{ Form::text('region', null, ['class' => 'form-control round ', 'placeholder' => 'Region','autocomplete'=>'false','id'=>'region']) }}
                </div>
                <div class="col-sm-4"><label for="prepared_by" class="caption">Prepared By <span class="text-danger">*<span></label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                        </div>
                        {{ Form::text('prepared_by', null, ['class' => 'form-control round', 'placeholder' => 'Prepared By']) }}
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="technician" class="caption"> Technician <span class="text-danger">*<span></label>
                    {{ Form::text('technician', null, ['class' => 'form-control round required', 'placeholder' => 'Technician', 'autocomplete'=>'false', 'id'=>'technician', 'required' => 'required']) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-3"><label for="client_name" class="caption"> Image 1 </label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {!! Form::file('image_one', array('class'=>'input', 'id'=>'image_one' )) !!}
                    </div>
                </div>
                <div class="col-sm-3"><label for="client_email" class="caption"> Image 2</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {!! Form::file('image_two', array('class'=>'input', 'id'=>'image_two' )) !!}
                    </div>
                </div>
                <div class="col-sm-3"><label for="client_email" class="caption"> Image 3</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {!! Form::file('image_three', array('class'=>'input', 'id'=>'image_three' )) !!}
                    </div>
                </div>
                <div class="col-sm-3"><label for="client_email" class="caption"> Image 4</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {!! Form::file('image_four', array('class'=>'input', 'id'=>'image_four' )) !!}
                    </div>
                </div>                                    
            </div>

            <div class="form-group row">
                <div class="col-sm-3"><label for="caption" class="caption"> Caption 1 </label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                        </div>
                        {{ Form::text('caption_one', null, ['class' => 'form-control round ', 'placeholder' => 'Caption 1','id'=>'caption_one']) }}
                    </div>
                </div>
                <div class="col-sm-3"><label for="client_email" class="caption"> Caption 2</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                        </div>
                        {{ Form::text('caption_two', null, ['class' => 'form-control round ', 'placeholder' => 'Caption 2','id'=>'caption_two']) }}
                    </div>
                </div>
                <div class="col-sm-3"><label for="caption_three" class="caption"> Caption 3</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                        </div>
                        {{ Form::text('caption_three', null, ['class' => 'form-control round ', 'placeholder' => 'Caption 4','id'=>'caption_three']) }}
                    </div>
                </div>
                <div class="col-sm-3"><label for="client_email" class="caption"> Caption 4</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                        </div>
                        {{ Form::text('caption_four', null, ['class' => 'form-control round ', 'placeholder' => 'Caption 4','id'=>'caption_four']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="col-sm-12">
                    <h3 class="subject">Report</h3>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="subject" class="caption">Subject / Title <span class="text-danger">*<span></label>
                    {{ Form::text('subject', null, ['class' => 'form-control round required', 'placeholder' => 'Subject / Title', 'autocomplete'=>'false', 'id'=>'subject']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="toAddInfo" class="root_cause">Findings and Root Cause</label>
                    {{ Form::textarea('root_cause', null, ['class' => 'form-control round html_editor', 'placeholder' => 'Root Cause','rows'=>'4']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="toAddInfo" class="action_taken">Action Taken</label>
                    {{ Form::textarea('action_taken', null, ['class' => 'form-control round html_editor', 'placeholder' => 'Action Taken','rows'=>'4']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="toAddInfo" class="recommendations">Recommendations</label>
                    {{ Form::textarea('recommendations', null, ['class' => 'form-control round html_editor', 'placeholder' => 'Recommendations','rows'=>'4']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div>
    <table id="equipmentsTbl" class="table-responsive tfr my_stripe_single text-center">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white">
                <th width="20%">Tag Id / Unique Number</th>
                <th>Jobcard</th>
                <th>Serial No</th>
                <th>Make / Type</th>
                <th>Capacity</th>
                <th>Location</th>
                <th>Last Service Date</th>
                <th>Next Service Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="row mt-1">
        <div class="col-12">
            <div class="btn-group">
                <button type="button" class="btn btn-success mr-1" aria-label="Left Align" id="addqproduct">
                    <i class="fa fa-plus-square"></i> Add Equipment
                </button>
                <a href="#" class="btn btn-purple" id="djc-link">
                    <i class="fa fa-print"></i> Djc Report
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="edit-form-btn col-2 ml-auto">
            {{ link_to_route('biller.rjcs.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
            {{ Form::submit('Generate', ['class' => 'btn btn-primary btn-md']) }}                                            
        </div>   
    </div>
</div>