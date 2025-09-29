<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($tender, ['route' => array('biller.tender.change_status', $tender), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Tender Stages</label>
                        <select class="custom-select" name="tender_stages" id="status">
                            @foreach (['open', 'negotiation', 'won', 'lost','cancelled'] as $val)
                                <option value="{{ $val }}" {{ @$tender && $tender->status == $val? 'selected' : '' }}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="d-none div_won">
                        <div class="form-group">
                            <label for="won_date">Date</label>
                            {{ Form::text('won_date', null, ['class' => 'form-control datepicker', 'id' => 'won_date']) }}
                        </div>
                        <div class="form-group">
                            <label for="users">Search Users to Notify</label>
                            <select name="employee_ids[]" id="users" class="form-control" data-placeholder="Search Users" multiple>
                                @foreach ($users as $user)
                                    <option value="{{$user->id}}">{{$user->fullname}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            {{ Form::textarea('message', null, ['class' => 'form-control']) }}
                        </div>
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