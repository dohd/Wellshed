
{!! Form::hidden('prospect_id', null, [
    'class' => 'form-control ',
    
    'id' => 'busyprospect_id',
]) !!}

<div class="form-group row">
    <div class="col-sm-6"><label for="recepient" class="caption">Recepient Name</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
            {{ Form::text('recepient', null, ['class' => 'form-control ', 'placeholder' => 'Name', 'id'=>'picked_busy_recepient' ]) }}
        </div>
    </div>
</div>
<div class="form-group row">
    <div class="col-md-6">
        <p>Reminder Date</p>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <input type="datetime-local" name="reminder_date" id="busy_reminder_date" class="form-control"/>
        </div>
    </div>
    <div class="col-md-6">
        <p>Remarks/Notes</p>
        {!! Form::text('any_remarks', null, [
            'class' => 'form-control ',
            'placeholder' => 'Remarks',
            'id' => 'busy_reminder_notes',
        ]) !!}
    </div>
</div>