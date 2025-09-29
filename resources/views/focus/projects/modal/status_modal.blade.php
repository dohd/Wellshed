<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Project Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biller.projects.status_tag_update') }}" method="POST" id="statusModalForm">
                @csrf
                <input type="hidden" name="project_id" id="status_project_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control custom-select" name="status" id="status">
                            @foreach ($statuses as $row)
                                <option value="{{ $row->id }}"> {{ $row->name }}</option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason">Remark</label>
                        {{ Form::textarea('end_note', null, ['class' => 'form-control', 'rows' => '2', 'id' => 'end_note', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Submit', ['class' => "btn btn-primary"]) }}
                </div>
            </form>
        </div>
    </div>
</div>