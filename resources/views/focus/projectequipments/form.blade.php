<div class='form-group'>
    {{ Form::label( 'name', trans('miscs.name'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => trans('miscs.name')]) }}
    </div>
</div>



