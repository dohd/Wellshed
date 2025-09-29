<div class='form-group row'>
    <div class="col-6">
        {{ Form::label( 'title', 'Title',['class' => 'col-lg-2 control-label']) }}
        {{ Form::text('title', null, ['class' => 'form-control round', 'placeholder' => 'Title']) }}
    </div>
    <div class="col-6">
        {{ Form::label( 'description', 'Description',['class' => 'col-lg-2 control-label']) }}
        {{ Form::text('description', null, ['class' => 'form-control round', 'placeholder' => 'Description']) }}
    
    </div>
</div>
<div class='form-group row mt-3'>
    
    @include('focus.prospect_questions.partials.prospect_question_items')
</div>
