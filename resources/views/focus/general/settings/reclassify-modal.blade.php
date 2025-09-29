<div class="modal fade" id="reclassifyModal" tabindex="-1" role="dialog" aria-labelledby="reclassifyModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            {{ Form::open(['route' => 'biller.settings.reclassify_transactions', 'method' => 'post']) }}
                {{ Form::hidden('prev_account_id', null, ['id' => 'prev-account']) }}
                {{ Form::hidden('tr_id', null, ['id' => 'tr-id']) }}
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-status-label">Reclassify Transactions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="account">Change Account To</label>
                        <select class="custom-select" name="account_id" id="dest-account" data-placeholder="Choose Account" required>
                            <option value=""></option>
                            @foreach ($accounts as $item)
                                <option value="{{ $item->id }}">{{ $item->number }}-{{ $item->holder }} ({{ $item->account_type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="class">Change Class To</label>
                        <select class="custom-select" name="classlist_id" id="dest-class" data-placeholder="Choose Class">
                            <option value=""></option>
                            @foreach ($classlists as $row)
                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => '3', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    {{ Form::submit('Apply', ['class' => "btn btn-success"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>