<div class="form-group row">
    <div class="col-6">
        {{ Form::label('tid', 'Serial') }}
        {{ Form::text('tid', $tid, ['class' => 'form-control', 'disabled' => 'disabled']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-6">
        {{ Form::label('name', 'Name') }} <span class="text-danger">*</span>
        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Account Name', 'required' => 'required']) }}
    </div>
</div>
<div class="form-group row"> 
    <div class="col-6">
        {{ Form::label('note', 'Description') }}
        {{ Form::text('note', null, ['class' => 'form-control', 'placeholder' => 'Description']) }}    
    </div>       
</div>
<div class="form-group row">
    <div class="col-6">
        <div class="row">
            <div class="col-md-4">
                {{ Form::label('is_class', 'Is sub-class') }}
                <select name="is_sub_class" class="form-control custom-select" id="is_sub_class" required>
                    <option value="">-- Select --</option>
                    @foreach (['No','Yes'] as $k => $val) 
                        <option value="{{ $k }}" {{ $k == @$account->is_sub_account ? 'selected' : '' }}>
                            {{ $val }}
                        </option>
                    @endforeach
                </select>   
            </div>
            <div class="col-md-8" style="margin-top: 2em">
                <select name="parent_id" class="form-control" id="parent_id" data-placeholder="Enter Parent Class" disabled>
                    <option value=""></option>
                    @foreach ($classlists as $item)
                        <option value="{{ $item->id }}" {{ @$classlist->parent_id == $item->id? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $('#parent_id').select2({allowClear: true});

    // on change parent-id
    $('#is_sub_class').change(function() {
        if (this.value == 0) $('#parent_id').attr('disabled', true);
        else $('#parent_id').attr('disabled', false);
        $('#parent_id').val('').change();
    });

    const classlist = @json(@$classlist);
    if (classlist && classlist.id) {
        if (classlist.parent_id) {
            $('#is_sub_class').val(1);
            $('#parent_id').attr('disabled', false);
        }
    }
</script>
@endsection