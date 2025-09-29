<div class='form-group'>
    {{ Form::label( 'title', 'Title',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('title', null, ['class' => 'form-control round', 'placeholder' => 'Title']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'note', 'Date',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::date('date', null, ['class' => 'form-control round', 'placeholder' => 'Date']) }}
    </div>
</div>

@include('focus.commissions.partials.commision_items')

