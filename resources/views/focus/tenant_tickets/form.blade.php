<div class="form-group row">
    <div class="col-md-4">
        <label for="category">Ticket Category</label>
        <select name="category_id" id="category" class="custom-select">
            @foreach ($categories as $i => $item)
                <option value="{{ $item->id }}" {{ @$tenant_ticket->category_id == $item->id? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="service">Related Service</label>
        <select name="tenant_service_id" id="service" class="custom-select">
            <option value="">None</option>
            @foreach ($services as $i => $service)
                <option value="{{ $service->id }}" {{ @$tenant_ticket->tenant_service_id == $service->id? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="priority">Priority</label>
        <select name="priority" id="priority" class="custom-select">
            @foreach (['Low', 'Medium', 'High'] as $i => $value)
                <option value="{{ $value }}" {{ @$tenant_ticket->priority == $value? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row">
    <div class="col-12">
        <label for="subject" class="caption">Subject</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::text('subject', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div> 
<div class="form-group row">
    <div class="col-12">
        <label for="message" class="caption">Message</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 6, 'required' => 'required']) }}
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
        companySelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.tenants.select') }}",
                dataType: 'json',
                delay: 250,
                method: 'POST',
                data: ({term}) => ({q: term}),
                processResults: data => {
                    return {results: data.map(v => ({text: v.cname, id: v.id}))}
                }
            },
        }
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
        },
    };

    $(Index.init);
</script>
@endsection