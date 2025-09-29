<div class="form-group row">
    <div class="col-12 col-lg-4">
        <label for="load_payroll">Select Month</label>
        {{ Form::month('payroll_month', @$payroll->processing_month, ['class' => 'form-control']) }}
    </div>
</div>