<div class='form-group'>
    {{ Form::label( 'name', trans('departments.name'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => trans('departments.name')]) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'description', 'Description',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('description', null, ['class' => 'form-control round', 'placeholder' => 'Description']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'rate', 'Hourly Rate', ['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::number('rate', null, ['step' => '0.01', 'class' => 'form-control round','id' => 'rate', 'placeholder' => '0.00']) }}
    </div>
</div>
