<div class="modal fade" id="reminderModal" tabindex="-1" role="dialog" aria-labelledby="reminderModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Add Reminder</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($lead, ['route' => ['biller.leads.update_reminder', $lead], 'method' => 'PATCH' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Reminder Date</label>
                        <div class='col'>
                            {{-- {{ Form::text('reminder_date', null, ['class' => 'form-control box-size datepicker', 'id'=>'reminder_date']) }} --}}
                            <input type="datetime-local" name="reminder_date" id="datetime" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reason">Exact Date</label>
                        <div class='col'>
                            {{-- {{ Form::text('exact_date', null, ['class' => 'form-control box-size datepicker', 'id'=>'exact-date']) }} --}}
                            <input type="datetime-local" name="exact_date" id="exact_date" class="form-control" />
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
@section('extra-scripts')

<script>
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#exact-date').datepicker('setDate', new Date());
    $('#reminder_date').datepicker('setDate', new Date());
</script>
@endsection