{{ Form::open(['route' => ['biller.import.general', $data['type']], 'method' => 'POST', 'files' => true, 'id' => 'import-data']) }}
    {{ Form::hidden('update', 1) }}
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
    <div class="row form-group">
        <div class="col-4">
            <label for="supplier">Supplier</label>
            <select class="form-control" name="supplier_id" id="supplier" data-placeholder="Choose Supplier" required></select>
        </div>
        <div class="col-2">
            <label for="contract">Contract Title</label>
            {{ Form::text('contract', null, ['class' => 'form-control', 'required']) }}
        </div>
    </div>
    {{ Form::submit(trans('import.upload_import'), ['class' => 'btn btn-primary btn-md']) }}
{{ Form::close() }}

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajaxSetup: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        supplierUrl: "{{ route('biller.suppliers.select') }}",
        select2(url, callback, extraData={}) {
            return {
                allowClear: true,
                ajax: {
                    url,
                    dataType: 'json',
                    type: 'POST',
                    data: ({term}) => ({search: term, keyword: term, ...extraData}),
                    quietMillis: 50,
                    processResults: callback
                }
            }
        },
        supplierCb(data) {
            return { results: data.map(v => ({id: v.id, text: v.name + ' - ' + v.email})) }
        },
    };

    const Form = {
        init() {
            const {ajaxSetup, supplierUrl, supplierCb} = config;
            $.ajaxSetup(ajaxSetup);

            $('#supplier').select2(config.select2(supplierUrl, supplierCb))
        },
    }

    $(() => Form.init());
</script>
@endsection