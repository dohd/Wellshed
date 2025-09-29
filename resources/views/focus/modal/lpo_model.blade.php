<div id="pop_model_4" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add LPO</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form_model_4">
                    <div class="row">
                        <div class="col mb-1 form-group">
                            <label for="customer">Select LPO</label>
                            <select id="lpo_id" name="lpo_id" class="form-control" required>
                                <option value="">-- Select LPO --</option>
                                @foreach ($lpos as $lpo)
                                    <option value="{{ $lpo->id }}">
                                        {{ $lpo->lpo_no }} || {{ $lpo->customer->company }} - {{ $lpo->branch->name }} ||
                                        {{ number_format($lpo->amount, 2) }} || {{ $lpo->remark }} || {{ dateFormat($lpo->date) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-1"><label for="pmethod">LPO Date</label>
                            <input type="date" class="form-control mb-1" placeholder="LPO  Date" name="lpo_date" data-toggle="datepicker" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="note">LPO Amount</label>
                            <input type="text" class="form-control" name="lpo_amount" placeholder="LPO Amount" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="note">LPO Number</label>
                            <input type="text" class="form-control" name="lpo_number" placeholder="LPO Number" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="bill_id" value="{{$invoice['id']}}">
                        <input type="hidden" name="bill_type" value="{{$invoice['bill_type']}}">
                        <button type="button" class="btn btn-warning" data-dismiss="modal">{{trans('general.close')}}</button>
                        <input type="hidden" id="action-url_4" value="{{route('biller.quotes.lpo')}}">
                        <button type="button" class="btn btn-primary submit_model" id="submit_model_4" data-itemid="4">Update Lpo Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>