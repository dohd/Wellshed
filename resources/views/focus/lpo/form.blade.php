<input type="hidden" name="lpo_id" id="lpo_id">
<div class="row">
    <div class="form-group col-6">
        <div><label for="customer">Search Customer</label></div>
        <select id="person" name="customer_id" class="form-control" data-placeholder="{{ trans('customers.customer') }}" required>
        </select>
    </div>
    <div class="form-group col-6">
        <div><label for="branch">Branch</label></div>
        <select id="branch_id" name="branch_id" class="form-control" data-placeholder="Branch" required>
        </select>
    </div>
</div>
<div class="row">
    <div class="form-group col-4">
        <label for="lpo_date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
    </div>
    <div class="form-group col-4">
        <label for="lpo_number">LPO Number</label>
        {{ Form::text('lpo_no', null, ['class' => 'form-control', 'id' => 'lpo_no', 'required']) }}
    </div>
    <div class="form-group col-4">
        <label for="lpo_amount">Amount</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div>
</div>
<div class="row">
    <div class="form-group col-10">
        <label for="lpo_amount">Remark</label>
        {{ Form::textarea('remark', null, ['class' => 'form-control', 'cols' => "100", 'rows' => "8", 'id' => 'remark']) }}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
</div>