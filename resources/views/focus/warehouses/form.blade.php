<div class='form-group'>
    {{ Form::label( 'title', 'Location Name', ['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('title', null, ['class' => 'form-control box-size']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'extra', 'Location Description', ['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('extra', null, ['class' => 'form-control box-size']) }}
    </div>
</div>

