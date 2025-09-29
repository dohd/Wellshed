<div class='form-group row'>
    <div class='col-7'>
        {{ Form::label( 'title', trans('terms.title'),['class' => 'control-label']) }}
        {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => trans('terms.title')]) }}
    </div>
    <div class='col-3'>
        {{ Form::label( 'type', trans('terms.type'),['class' => 'control-label']) }}
        <select class="form-control" name="type" required>
            @foreach (['All', 'Invoices', 'Quotes', 'General Bills', 'LPO','RFQ'] as $k => $val)
                <option value="{{ $k }}" {{ $k ==  @$term['type'] ? 'selected' : '' }}>{{ $val }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class='form-group row'>
    <div class='col-lg-10'>
        {{ Form::label( 'terms', trans('terms.terms'),['class' => 'control-label']) }}
        {{ Form::textarea('terms', null, ['class' => 'form-control html_editor round', 'placeholder' => trans('terms.terms')]) }}
    </div>
</div>

@section('after-scripts')
<script>
    // initialize html editor
    editor();
</script>
@endsection