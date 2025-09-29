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
<div class="form-group row">
    @include('focus.standard_templates.partials.standard_template_items')
</div>
<div class="form-group row">
    <a href="javascript:" class="btn btn-success addProduct" id="addProduct"><i class="fa fa-plus-square"></i> Add Product</a>
</div>
