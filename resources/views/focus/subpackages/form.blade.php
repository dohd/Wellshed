<div class='form-group'>
    <div class="col-lg-10">
        <input type="checkbox" name="is_disabled" id="is_disabled" value="1" {{ @$subpackage->is_disabled === 1? 'checked' : '' }}>
        {{ Form::label('is_disabled', 'Is Disabled', ['class' => 'control-label']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label('name', 'Package Name', ['class' => 'col-lg-2 control-label']) }}
    <div class="col-lg-10">
        {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Package Name', 'required' => 'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label('price', 'Package Price',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('price', null, ['class' => 'form-control box-size', 'placeholder' => '0.00', 'required' => 'required']) }}
    </div>
</div>

<div class='form-group'>
    {{ Form::label( 'max_bottle', 'Max Deliverable Bottles',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::number('max_bottle', null, ['class' => 'form-control box-size', 'placeholder' => '0', 'required' => 'required']) }}
    </div>
</div>

<div class='form-group'>
    {{ Form::label('product', 'Default Product',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        <select name="productvar_id" id="product" class="form-control" data-placeholder="Choose Product">
            <option value=""></option>
            @foreach ($productvars as $product)
            <option value="{{ $product->id }}" {{ @$subpackage->productvar_id === $product->id? 'selected' : '' }}>
              {{ $product->code }}  - {{ $product->name }}
            </option>
            @endforeach
        </select>
    </div>
</div>

<div class='form-group'>
    {{ Form::label('features', 'Features', ['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::textarea('features', null, ['class' => 'form-control box-size tinyinput', 'placeholder' => 'Features...', 'rows' => '2']) }}
    </div>
</div>

@section("after-scripts")
@include('tinymce.scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        tinymce: {
            selector: '.tinyinput',
            menubar: 'file edit view format table tools',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 300,
        },
    }

    const Form = {
        init() {
            tinymce.init(config.tinymce);   
            $('#product').select2({allowClear: true});         
        },
    }

    $(Form.init);
</script>
@endsection