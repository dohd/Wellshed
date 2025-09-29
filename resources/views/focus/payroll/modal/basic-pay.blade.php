<div class="modal fade" id="basicModal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Edit Basic Salary</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biller.payroll.update_basic') }}" method="post">
                @csrf
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label for="absent_days">Absent Days</label>
                        <input type="text" value="" class="form-control ab-days" name="absent_days" id="ab-days">
                    </div>
                    <div class="form-group">
                        <label for="absent_rate">Absent Rate</label>
                       <input type="text" class="form-control ab-rate" value="" name="absent_rate" id="ab-rate">
                       <input type="hidden" class="form-control salary" value="" id="salary">
                       <input type="hidden" class="form-control basic_pay" value="" name="basic_pay" id="basic_pay">
                       <input type="hidden" class="form-control id" value="" name="id" id="id">
                       <input type="hidden" class="form-control month" value="" name="month" id="month">
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