<div class="modal fade " id="callModal" tabindex="-1" role="dialog" aria-labelledby="callModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 1080px" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-remarks-label">Call History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="mx-2 my-2">
                <p>Prospect Details</p>
                <div id="prospectTableDetails" class="mt-2">

                </div>
            </div>
            <div class="mx-2">
                <p>Call History</p>
                <div id="remarksTableModal" class="mt-2">

                </div>
            </div>

            <div class="mx-2 mt-3">

                <div class="form-group row">
                    <div class=' mb-2'>

                        <div><label for="prospect-type">Select Call Status</label></div>
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-primary call-status" name="call_status"
                                id="colorCheck1" value="picked" checked required>
                            <label class="custom-control-label" for="colorCheck1">Answered</label>
                        </div>
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-blue call-status" name="call_status"
                                id="colorCheck2" value="pickedbusy" required>
                            <label class="custom-control-label" for="colorCheck2">Answered But Busy</label>
                        </div>
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-purple call-status" name="call_status"
                                value="notpicked" id="colorCheck3" required>
                            <label class="custom-control-label" for="colorCheck3">Not Answered</label>
                        </div>
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-danger call-status" name="call_status"
                                value="notavailable" id="colorCheck4" required>
                            <label class="custom-control-label" for="colorCheck4">Not Available</label>
                        </div>

                    </div>
                </div>
            </div>



            <div id="div_picked" class="mx-2">
                <h3>Follow up questions</h3>
                {{ Form::open(['route' => 'biller.prospectcallresolves.store', 'method' => 'POST', 'id' => 'picked']) }}
                @include('focus.prospects.calllist.picked_form')
                {{ Form::submit('Save Call Chat', ['class' => ' my-2 btn btn-md btn-primary', 'id' => 'save_call_chat']) }}
                {!! Form::close() !!}
            </div>
            <div id="div_picked_busy" style="display:none" class="mx-2">
                <h3>Picked But Busy</h3>
                {{ Form::open(['route' => 'biller.prospectcallresolves.pickedbusy', 'method' => 'POST', 'id' => 'pickedbusy']) }}
                @include('focus.prospects.calllist.picked_busy_form')
                {{ Form::submit('Save Reschedule', ['class' => ' my-2 btn btn-md btn-primary', 'id' => 'save_reshedule']) }}
                {!! Form::close() !!}
            </div>


            <div id="div_notpicked" style="display:none" class="mx-2">
                <h3>Busy Not Picking</h3>
                {{ Form::open(['route' => 'biller.prospectcallresolves.notpicked', 'method' => 'POST', 'id' => 'notpicked']) }}
                @include('focus.prospects.calllist.notpicked_form')
                {{ Form::submit('Record As Not Picked', ['class' => ' my-2 btn btn-md btn-primary', 'id' => 'save_reminder']) }}
                {{ Form::close() }}


            </div>
            <div id="div_notpicked_available" style="display:none" class="mx-2">
                <h3>Not Available</h3>
                {{ Form::open(['route' => 'biller.prospectcallresolves.notavailable', 'method' => 'POST', 'id' => 'notavailable']) }}
                @include('focus.prospects.calllist.picked_notavailable_form')
                {{ Form::submit('Record As Not Available', ['class' => ' my-2 btn btn-md btn-primary', 'id' => 'save_remark']) }}
                {{ Form::close() }}


            </div>


        </div>
    </div>
</div>
