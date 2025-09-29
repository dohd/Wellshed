<div class='form-group'>
    {{ Form::label( 'title', 'Title',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('title', null, ['class' => 'form-control round', 'placeholder' => 'Title']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'month_year', 'Month/Year',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::month('month_year', @$appraisal_type->month_year, ['class' => 'form-control']) }}
    </div>
</div>