<div class="modal fade" id="closeProject" tabindex="-1" role="dialog" aria-labelledby="closeProject" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">End Project</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ Form::open(['route' => ['biller.projects.close_project', $project], 'method' => 'POST']) }}
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="text" class="form-control datepicker" name="end_date">
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        <input type="text" class="form-control" name="end_note" required>
                    </div>
                    <div class="form-group float-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        {{ Form::submit('End', ['class' => 'btn btn-danger', 'disabled']) }}
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>