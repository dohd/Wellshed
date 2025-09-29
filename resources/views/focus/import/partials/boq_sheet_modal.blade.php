<div class="modal fade" id="boqSheetModal" tabindex="-1" role="dialog" aria-labelledby="boqSheetModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Add BoQ Sheet</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::open(['route' => 'biller.boqs.store_boq_sheet', 'method' => 'POST']) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sheet_name">BoQ Sheet Name</label>
                        <input type="text" name="sheet_name" id="sheet_name" class="form-control" placeholder="BoQ Sheet Name">
                        
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 4]) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Submit', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>