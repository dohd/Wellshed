<div class='form-group'>
    {{ Form::label('Category', 'Category', ['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'Category', 'required']) }}
    </div>
</div>