<div class='form-group row'>
    <div class='col-lg-5'>
        {{ Form::label( 'frequency_type', 'Delivery Frequency',['class' => 'control-label']) }}
       <select name="frequency_type" id="frequency_type" class="form-control">
        <option value="">--select frequency type--</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="bi_weekly">Bi Weekly</option>
        <option value="monthly">Monthly</option>
       </select>
    </div>
</div>
<div class='form-group row'>
    <div class='col-lg-4'>
        {{ Form::label( 'start_month', 'Start (Period From)',['class' => 'control-label']) }}
        {{ Form::month('start_month', null, ['class' => 'form-control', 'placeholder' => 'Start (Period From)']) }}
    </div>
    <div class='col-lg-4'>
        {{ Form::label( 'end_month', 'End (Period To)',['class' => 'control-label']) }}
        {{ Form::month('end_month', null, ['class' => 'form-control', 'placeholder' => 'End (Period To)']) }}
    </div>
</div>
<div class='form-group row'>
    <div class='col-lg-10'>
        {{ Form::label( 'note', trans('departments.note'),['class' => 'control-label']) }}
        {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('departments.note')]) }}
    </div>
</div>
