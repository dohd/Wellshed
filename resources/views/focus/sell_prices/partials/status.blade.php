<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($sell_price, ['route' => array('biller.sell_prices.change_status', $sell_price), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="custom-select" name="status" id="status">
                            @foreach (['pending', 'approved', 'amend','rejected'] as $val)
                                <option value="{{ $val }}" {{ @$sell_price && $sell_price->status == $val? 'selected' : '' }}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status_note">Remark</label>
                        {{ Form::textarea('status_note', null, ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="modal-footer">
                    @php
                        $disabled = '';
                        if (isset($sell_price) && $sell_price->status == 'approved')
                            $disabled = 'disabled';
                    @endphp
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary", $disabled]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>