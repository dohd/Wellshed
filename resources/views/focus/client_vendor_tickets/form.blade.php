<div class="form-group row">
    <div class="col-md-3">
        <label for="priority">Priority</label>
        <select name="priority" id="priority" class="custom-select">
            @foreach (['Low', 'Medium', 'High'] as $i => $value)
                <option value="{{ $value }}" {{ @$client_vendor_ticket->priority == $value? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="tag">Ticket Level</label>
        <select name="tag_id" id="tag" class="custom-select" required>
            <option value="">-- Select Level --</option>
            @foreach ($tags as $i => $item)
                <option value="{{ $item->id }}" {{ @$client_vendor_ticket->tag_id == $item->id? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="col-md-3">
        <label for="category">Equipment Category</label>
        <select name="equip_categ_id" id="equip_category" class="custom-select">
            <option value="">-- Select Category --</option>
            @foreach ($equip_categories as $i => $item)
                <option value="{{ $item->id }}" {{ @$client_vendor_ticket->equip_categ_id == $item->id? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="location" class="caption">Equipment Location</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::text('equip_location', null, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
</div>

<div class="form-group row">
    <div class="col-md-6">
        <label for="subject" class="caption">Subject</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::text('subject', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <label for="quoteOrPi">Quote / PI</label>
        <select name="quote_id" id="quote" class="form-control" data-placeholder="Search Quote / PI">
            <option value=""></option>
        </select>
    </div>

    <div class="col-md-3">
        <label for="project">Project</label>
        <select name="project_id" id="project" class="form-control" data-placeholder="Search Project">
            <option value=""></option>
        </select>
    </div>
</div> 
<div class="form-group row">
    <div class="col-md-6">
        <label for="message" class="caption">Message</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => "3", 'required' => 'required']) }}
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
        quoteSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.client_vendor_tickets.query_quotes') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        },
        projectSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.client_vendor_tickets.query_projects') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        },
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            $('#quote').select2(config.quoteSelect2);
            $('#project').select2(config.projectSelect2);
        },
    };

    $(Index.init);
</script>
@endsection