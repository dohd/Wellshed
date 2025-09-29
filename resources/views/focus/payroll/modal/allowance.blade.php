<div class="modal fade" id="allowanceModal" tabindex="-1" role="dialog" aria-labelledby="allowanceModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Edit Basic Salary</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biller.payroll.update_allowance') }}" method="post">
                @csrf
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label for="house_allowance">House Allowance</label>
                        <input type="text" class="form-control ha">
                        <input type="text" value="" class="form-control house" name="house_allowance" id="house" readonly>
                    </div>
                    <div class="form-group">
                        <label for="transport_allowance">Transport Allowance</label>
                        <input type="text" class="form-control ta">
                        <input type="text" value="" class="form-control transport" name="transport_allowance" id="transport">
                    </div>
                    <div class="form-group">
                        <label for="other_allowance">Other Allowance</label>
                        <input type="text" class="form-control oa">
                        <input type="text" value="" class="form-control other" name="other_allowance" id="other">
                       <input type="hidden" class="form-control pay_id" value="" name="id">
                       <input type="hidden" class="form-control absent_day" value="">
                       <input type="hidden" class="form-control month_day" value="" name="month" id="month">
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