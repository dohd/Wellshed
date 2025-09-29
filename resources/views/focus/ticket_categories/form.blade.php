<div class="form-group row">
    <div class="col-md-12">
        <label for="module">Module</label>
        <select name="module" id="module" class="custom-select">
            @foreach (['Client Area', 'CRM'] as $i => $value)
                <option value="{{ $value }}" {{ @$ticket_category->module == $value? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>  
</div>
<div class="form-group row">
    <div class="col-md-12">
        <label for="name" class="caption">Name</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
        },
    };

    $(Index.init);
</script>
@endsection