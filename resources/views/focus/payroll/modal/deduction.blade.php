<div class="modal fade" id="deductionModal" tabindex="-1" role="dialog" aria-labelledby="deductionModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Edit Taxable Deductions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biller.payroll.update_deduction') }}" method="post">
                @csrf
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label for="tx_deductions">Taxable Deductions</label>
                        <input type="text" value="" class="form-control tx-deduction" name="tx_deductions">
                        <input type="hidden" class="form-control deduction-id" value="" name="id" id="deduction-id">
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