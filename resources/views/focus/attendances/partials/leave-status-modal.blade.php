<div class="modal fade" id="leaveStatusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($attendance, ['route' => array('biller.attendances.update_status', $attendance), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="custom-select" name="status" id="status">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="on_leave">On Leave</option>
                            <option value="late">Late/Away</option>                         
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="type">Clock In/Out Status</label>
                        <select class="custom-select" name="type" id="type">
                            <option value="clock_in">Clock In</option>
                            <option value="clock_out">Clock Out</option>                      
                        </select>
                    </div>
                    <div class="form-group" id="in">
                        <label for="clock-in">Clock In</label>
                        <input type="time" name="clock_in" value="{{ $attendance->clock_in }}" placeholder="HH:MM" class="form-control clock-in">
                    </div>
                    <div class="form-group d-none" id="out">
                        <label for="clock-in">Clock Out</label>
                        <input type="time" name="clock_out" value="{{ $attendance->clock_out }}" placeholder="HH:MM" class="form-control clock-out">
                    </div>
                    <div class="form-group">
                        <label for="workshift">Select WorkShift</label>
                        <select name="workshift_id" id="workshift" class="form-control">
                            @foreach ($workshifts as $workshift)
                                <option value="{{$workshift->id}}" {{$attendance->workshift_id == $workshift->id ? 'selected' : ''}}>{{$workshift->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="remark">Remark</label>
                        {{ Form::text('status_note', null, ['class' => 'form-control status_note','readonly']) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>