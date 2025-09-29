<div class='form-group row'>
    <div class='col-lg-6'>
        {{ Form::label( 'name', trans('departments.name'),['class' => 'control-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => trans('departments.name')]) }}
    </div>
    <div class="col-lg-2">
        <label for="">Select Template</label>
        <select name="type" id="type" class="form-control">
            <option value="">--select template--</option>
            @foreach (['no','yes'] as $item)
                <option value="{{$item}}" {{@$part->type == $item ? 'selected' : ''}}>{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 d-none div_template">
        <label for="">Search Template</label>
        <select name="template_id" id="template" class="form-control" data-placeholder="Search Template">
            <option value="">Search Template</option>
            @foreach ($templates as $template)
                <option value="{{$template->id}}" {{@$part->template_id == $template->id ? 'selected' : ''}}>{{$template->name}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class='form-group row'>
    <div class="col-2">
        <label for="">Total Needed</label>
        <input type="text" name="total_qty" id="total" class="form-control" placeholder="0.00" value="{{number_format(@$part->total_qty,2) ?? 1}}" required>
    </div>
    <div class='col-lg-10'>
        {{ Form::label( 'description', 'Description',['class' => 'control-label']) }}
        {{ Form::text('description', null, ['class' => 'form-control round', 'placeholder' => 'Description']) }}
    </div>
</div>
<div class="form-group row m-1">
    @include('focus.parts.partials.part_items')
</div>
<div class="form-group row m-1">
    <a href="javascript:" class="btn btn-success addProduct" id="addProduct"><i class="fa fa-plus-square"></i> Add Product</a>
</div>
