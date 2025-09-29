<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Copy Equipments To Schedule</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($taskschedule, ['route' => ['biller.taskschedules.update', $taskschedule], 'method' => 'PATCH']) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Target Schedule</label>
                        <select class="form-control" name="schedule_id" id="schedule">
                            @foreach ($taskschedules_rel as $row)
                                <option value="{{ $row->id }}">
                                    {{ $row->title }}
                                </option>
                            @endforeach                            
                        </select>
                        <input type="hidden" name="is_copy" value="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Save', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>