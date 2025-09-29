<div class="row">
    <div class="col-sm-6">
        <div>
            <div class="form-group row">
                <div class="col-12">
                    <h3>Site Survey Details</h3>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12"><label for="ref_type" class="caption">Ticket </label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select class="form-control round" name="lead_id" id="lead_id" required>
                            @foreach ($leads as $lead)
                                @php
                                    $name =  $lead->customer? $lead->customer->company : $lead->client_name;
                                    $branch = $lead->branch? $lead->branch->name : '';
                                    if ($name && $branch) $name .= " - {$branch}";                                                                
                                @endphp
                                <option 
                                    value="{{ $lead->id }}" 
                                    title="{{ $lead->title }}"
                                    client_ref="{{ $lead->client_ref }}"
                                    branch_id="{{ $lead->branch? $lead->branch->id : '' }}"
                                    client_id="{{ $lead->customer? $lead->customer->id : '' }}"
                                    {{ @$djc && $djc->lead_id == $lead->id? 'selected' : '' }}
                                >
                                    {{ gen4tid("{$prefixes[1]}-", @$lead->reference) }} - {{ $name }} - {{ $lead->title }}
                                </option>
                            @endforeach
                        </select>                                     
                        <input type="hidden" name="client_id" value="" id="client_id">
                        <input type="hidden" name="branch_id" value="" id="branch_id">                                                
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-5">
                    <label for="attention" class="attention">Attention <span class="text-danger">*</span></label>
                    {{ Form::text('attention', null, ['class' => 'form-control round required', 'placeholder' => 'Attention','autocomplete'=>'false','id'=>'attention']) }}
                </div>
                <div class="col-sm-4">
                    <label for="jobcard" class="jobcard">Job Card</label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                        {{ Form::text('job_card', null, ['class' => 'form-control round required', 'placeholder' => 'Job Card', 'id'=>'jobcard']) }}
                    </div>
                </div>
                <div class="col-sm-3">
                    <label for="jobcard" class="jobcard">Job Card Date</label>
                    <div class="input-group">
                        {{ Form::text('jobcard_date', null, ['class' => 'form-control datepicker', 'id'=>'jobcard_date']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-4"><label for="tid" class="caption">Report No</label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                        {{ Form::text('tid', gen4tid("{$prefixes[0]}-", @$djc? $djc->tid : $tid+1), ['class' => 'form-control round', 'disabled']) }}
                        <input type="hidden" name="tid" value="{{ @$djc? $djc->tid : $tid+1 }}">
                    </div>
                </div>                                        
                <div class="col-sm-4"><label for="report_date" class="caption">Report {{trans('general.date')}}</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('report_date', null, ['class' => 'form-control datepicker round']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="reference" class="caption">Client Ref / Callout ID</label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                        {{ Form::text('client_ref', null, ['class' => 'form-control round ', 'id' => 'client_ref', 'required']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-4">
                    <label for="region" class="caption"> Region</label>
                    {{ Form::text('region', null, ['class' => 'form-control round ', 'placeholder' => 'Region','autocomplete'=>'false','id'=>'region']) }}
                </div>
                <div class="col-sm-4"><label for="prepared_by" class="caption">Prepared By <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                        {{ Form::text('prepared_by', null, ['class' => 'form-control round', 'placeholder' => 'Prepared By']) }}
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="technician" class="caption"> Technician <span class="text-danger">*</span></label>
                    {{ Form::text('technician', null, ['class' => 'form-control round required', 'placeholder' => 'Technician','autocomplete'=>'false','id'=>'prepaired_by','required' => 'required']) }}
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
                    <label for="subject" class="caption">Subject / Title <span class="text-danger">*</span></label>
                    {{ Form::text('subject', null, ['class' => 'form-control round required', 'placeholder' => 'Subject / Title','autocomplete'=>'false','id'=>'subject']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="toAddInfo" class="root_cause">Findings / Root Cause</label>
                    {{ Form::textarea('root_cause', null, ['id' => 'root_cause', 'class' => 'form-control round tinyinput', 'placeholder' => 'Findings / Root cause','rows'=>'4']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="toAddInfo" class="action_taken">Action Taken</label>
                    {{ Form::textarea('action_taken', null, ['id' => 'action_taken', 'class' => 'form-control round tinyinput', 'placeholder' => 'Action Taken','rows'=>'4']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="toAddInfo" class="recommendations">Recommendations</label>
                    {{ Form::textarea('recommendations', null, ['id' => 'recommendations', 'class' => 'form-control round tinyinput', 'placeholder' => 'Recommendations','rows'=>'4']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div>
    <table id="equipmentsTbl" class="tfr my_stripe_single text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="20%">Tag Id / Unique Number</th>
                <th>Jobcard</th>
                <th>Serial No.</th>
                <th>Make / Type</th>
                <th>Capacity / Size</th>
                <th>Location</th>
                <th>Last Service Date</th>
                <th>Next Service Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <div class="row mt-1">
        <div class="col-6">
            <button type="button" class="btn btn-success" aria-label="Left Align" id="addqproduct">
                <i class="fa fa-plus-square"></i> Add Equipment
            </button>
        </div>
    </div>
    <div class="row">
        <div class="edit-form-btn col-2 ml-auto">
            {{ link_to_route('biller.djcs.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
            {{ Form::submit('Generate', ['class' => 'btn btn-primary btn-md']) }}                                            
        </div>   
    </div>
</div>