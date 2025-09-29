
{!! Form::hidden('prospect_id', null, [
    'class' => 'form-control ',
    
    'id' => 'notavailable_prospect',
]) !!}

<div class="form-group row">
  
    <div class="col-md-6">
        <p>Remarks/Notes</p>
        {!! Form::textarea('any_remarks', null, [
            'class' => 'form-control ',
            'rows'=>3,
            'placeholder' => 'Remarks',
            'id' => 'remarks',
        ]) !!}
    </div>
</div>