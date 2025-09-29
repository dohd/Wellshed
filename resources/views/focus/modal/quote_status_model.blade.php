<div id="pop_model_1" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Approval</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['route' => ['biller.quotes.approve_quote', $quote], 'method' => 'POST', 'id' => 'form-approve']) }}
                    <div class="row">
                        <div class="col mb-1">
                            <label for="status">{{trans('general.mark_as')}}</label>
                            <select name="status" class="form-control mb-1 aprv-status" required>
                                <option value="">-- Select Status --</option>
                                @foreach (['pending', 'approved', 'cancelled'] as $val)
                                    <option value="{{ $val }}" {{ $val == $quote->status? 'selected' : '' }}>
                                        {{ ucfirst($val) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="approval-date">Approval Date</label>
                            <input type="text" class="form-control mb-1 aprv-date datepicker" name="approved_date" value="{{ dateFormat($quote->approved_date) }}" id="approveddate" required/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="approval-method">Approval Method</label>
                            <select class="form-control aprv-method mb-1" name="approved_method" id="approvedmethod" required>
                                <option value="">-- Select Method --</option>
                                @foreach (['email', 'sms', 'whatsapp', 'call', 'lpo','internal Approval', 'other'] as $val)
                                    <option value="{{ $val }}" {{ $val == $quote->approved_method? 'selected' : '' }}>
                                        {{ ucfirst($val) }}
                                    </option>
                                @endforeach                                
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="approved-by">Approved By</label>
                            <input type="text" maxlength="30" class="form-control aprv-by" name="approved_by" value="{{ $quote->approved_by }}" id="approvedby" placeholder="Approved By" required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="note">{{trans('general.note')}}</label>
                            <textarea class="form-control aprv-note" name="approval_note" placeholder="{{trans('general.note')}}" rows="5" required>{!! $quote->approval_note !!}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal">{{trans('general.close')}}</button>                       
                        <button type="submit" class="btn btn-primary" id="btn_approve">Approve</button> 
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>