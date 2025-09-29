<div class="modal fade " id="remarksModal" tabindex="-1" role="dialog" aria-labelledby="remarksModal" aria-hidden="true">
    <div class="modal-dialog " style="max-width: 1080px" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-remarks-label">Remarks History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="mx-2 my-2">
                <p>Prospect Details</p>
                <div id="prospectTableDetailsRemarks" class="mt-2">
                    
                </div>
            </div>

            <div class="mx-2">
                <p>Records</p>
                <div id="recordsTableModal" class="mt-2">
               
                </div>
            </div>
            <div class="mx-2">
                <p>Call History</p>
                <div id="tableModal" class="mt-2">
               
                </div>
            </div>
           
           
            <div class="mx-3">
               
                    <h5 class="my-2">New Remark</h5>
                    {{ Form::open(['id'=>'remarkform']) }}
                    <div class="form-group column">
                        {{ Form::hidden('prospect_id',null,['class'=>'form-control','id'=>'prospect_id']) }}
                        <div class="row">
                           
                            <div class="col-sm-6"><label for="recepient" class="caption">Recepient Name</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                    {{ Form::text('recepient', null, ['class' => 'form-control ', 'placeholder' => 'Name', 'id'=>'remarksrecepient' ,'required']) }}
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="temperate">Choose Type of Temperate</label>
                                <select name="temperate" id="temperate" class="form-control" data-placeholder="Choose temperate">
                                    <option value="">Choose Temperate</option>
                                    @foreach (['hot','cold','warm'] as $item)
                                        <option value="{{$item}}">{{ucfirst($item)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3"><label for="reminder_date" class="caption">Next Reminder Date</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                    <input type="datetime-local" name="reminder_date" id="remarksreminder_date" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12"><label for="remarks" class="caption">Remarks</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                    {{ Form::textarea('any_remarks', null, ['class' => 'form-control','rows' => 3, 'placeholder' => 'Remark','id'=>'remarksanyremarks','required']) }}
                                </div>
                            </div>
                        </div>
                      
                        
                            {{ Form::button("Save Remark",['class'=>' my-2 btn btn-md btn-primary','id'=>'save_remark']) }}
                        {{ Form::close() }}   
                    </div>
        
                         
            
            </div>
        
        </div>
    </div>
</div>

