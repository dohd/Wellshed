<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{-- {{ Form::model($purchase_requisition, ['route' => array('biller.purchase_requisitions.update', $purchase_requisition), 'method' => 'PATCH' ]) }} --}}
            {{ Form::open(['route' => 'biller.purchase_requisitions.approve', 'method' => 'POST', 'files' => false]) }}
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="custom-select" name="status" id="status">
                            @foreach (['pending', 'amend', 'approved', 'rejected'] as $val)
                                <option value="{{ $val }}" {{ @$purchase_requisition->status == $val? 'selected' : '' }}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        {{ Form::text('approved_date', null, ['class' => 'form-control datepicker', 'id' => 'approved_date']) }}
                    </div>
                    <div class="form-group">
                        <label for="status_note">Status Note</label>
                        {{ Form::text('status_note', @$purchase_requisition->status_note, ['class' => 'form-control']) }}
                    </div>
                    <input type="hidden" name="id" id="" value="{{$purchase_requisition->id}}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Submit', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>