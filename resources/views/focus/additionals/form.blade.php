<div class='form-group'>
    <div class='col-lg-10'>
        {{ Form::label( 'name', trans('additionals.name'),['class' => 'col-lg-2 control-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'Name E.g VAT']) }}
    </div>
</div>
<div class='form-group' id="value1">
    <div class='col-lg-10'>
        {{ Form::label( 'value', trans('additionals.value'),['class' => 'col-lg-2 control-label']) }}
        {{ Form::text('value', null, ['class' => 'form-control round', 'placeholder' => 'Rate E.g 16']) }}
    </div>
</div>
<div class="form-group">
    <div class='col-lg-10'>
        {{ Form::label('is_default', 'Is Defalut',['class' => 'col-lg-2 control-label']) }}
        <select class="form-control round" name="is_default" id='default_a'>
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>
    </div>
</div>