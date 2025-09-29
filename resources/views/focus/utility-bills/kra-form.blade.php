<div class="form-group row">
    <div class="col-4">
        <label for="supplier">KRA Creditor</label>
        <select name="supplier_id" class="form-control"  data-placeholder="Choose KRA Creditor" id="supplier">
            @foreach ($suppliers as $row)
                <option value="{{ $row->id }}">{{ $row->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="tid">Transaction ID</label>
        {{ Form::text('tid', $tid+1, ['class' => 'form-control', 'readonly']) }}
    </div>
    <div class="col-2">
        <label for="date">Registration Date</label>
        {{ Form::text('reg_date', null, ['class' => 'form-control datepicker']) }}
    </div>
    <div class="col-3">
        <label for="number">Registration No.</label>
        {{ Form::text('reg_no', null, ['class' => 'form-control', 'required']) }}
    </div>                            
</div>
<div class="form-group row">
    <div class="col-6">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'required']) }}
    </div>
</div>

<div class="table-responsive">
    <table class="table text-center tfr my_stripe_single" id="billsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Payment Type</th>
                <th>Tax Obligation</th>
                <th>Tax Period</th>
                <th>Amount (Ksh.)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ Form::text('payment_type[]', null, ['class' => 'form-control payment_type', 'placeholder' => 'Payment Type', 'id' => 'paymenttype-0', 'required']) }}</td>
                <td>{{ Form::text('tax_type[]', null, ['class' => 'form-control tax_type', 'placeholder' => 'Tax Type', 'id' => 'taxtype-0', 'required']) }}</td>
                <td>{{ Form::text('tax_period[]', null, ['class' => 'form-control tax_period', 'placeholder' => 'Tax Period', 'id' => 'taxperiod-0', 'required']) }}</td>
                <td>{{ Form::text('amount[]', null, ['class' => 'form-control amount', 'placeholder' => '0.00', 'id' => 'amount-0', 'required']) }}</td>
                <td><a href="javascript:" class="btn btn-light del"><i class="danger fa fa-trash fa-lg"></i></a></td> 
            </tr>
        </tbody>
    </table>
</div>
<a href="javascript:" class="btn btn-success" aria-label="Left Align" id="addRow">
    <i class="fa fa-plus-square"></i> Add Row
</a>

<div class="form-group row">
    <div class="col-2 ml-auto">
        <label for="total">Total Amount (Ksh.)</label>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div>
</div>
<div class="form-group row">                            
    <div class="col-12"> 
        {{ Form::submit('Generate', ['class' => 'btn btn-primary btn-lg float-right mr-3']) }}                                
    </div>
</div>
