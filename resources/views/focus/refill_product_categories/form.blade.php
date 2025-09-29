<div class='form-group'>
    {{ Form::label( 'title', trans('productcategories.title'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('title', null, ['class' => 'form-control box-size', 'placeholder' => trans('productcategories.title').'*','required'=>'required']) }}
    </div>
</div>

<div class='form-group'>
    {{ Form::label( 'extra', trans('productcategories.extra'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('extra', null, ['class' => 'form-control box-size', 'placeholder' => trans('productcategories.extra')]) }}
    </div>
</div>

