<div class="modal fade" id="otherModal" tabindex="-1" role="dialog" aria-labelledby="otherModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Edit Taxable Deductions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biller.payroll.update_other') }}" method="post">
                @csrf
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label for="total_other_allowances">Other Allowances</label>
                        <input type="text" value="" class="form-control o-allow" name="total_other_allowances">
                        <input type="hidden" name="id" class="o-id">
                    </div>
                    <div class="form-group">
                        <label for="total_benefits">Total Benefits</label>
                        <input type="text" value="" class="form-control benefit" name="total_benefits">
                    </div>
                    <div class="form-group">
                        <label for="loan">Loan</label>
                        <input type="text" value="" class="form-control loans" name="loan">
                    </div>
                    <div class="form-group">
                        <label for="advance">Advance</label>
                        <input type="text" value="" class="form-control advances" name="advance">
                    </div>
                    <div class="form-group">
                        <label for="total_other_deduction">Other Deductions</label>
                        <input type="text" value="" class="form-control o-deductions" name="total_other_deduction">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Save', ['class' => "btn btn-primary"]) }}
                </div>
            </form>
        </div>
    </div>
</div>