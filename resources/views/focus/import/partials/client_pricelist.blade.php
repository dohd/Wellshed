{{ Form::open(['route' => ['biller.import.general', $data['type']], 'method' => 'POST', 'files' => true, 'id' => 'import-data']) }}
    {{ Form::hidden('update', 1) }}
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
    <div class="row form-group">
        <div class="col-4">
            <label for="customer">Customer</label>
            <select class="form-control" name="customer_id" id="customer" data-placeholder="Choose Customer" required></select>
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
        customerUrl: "{{ route('biller.customers.select') }}",
        select2(url, callback, extraData={}) {
            return {
                allowClear: true,
                ajax: {
                    url,
                    dataType: 'json',
                    type: 'POST',
                    data: ({term}) => ({search: term, ...extraData}),
                    quietMillis: 50,
                    processResults: callback
                }
            }
        },
        customerCb(data) {
            return { results: data.map(v => ({id: v.id, text: v.name + ' - ' + v.company})) }
        },
    };

    const Form = {
        init() {
            const {ajaxSetup, customerUrl, customerCb} = config;
            $.ajaxSetup(ajaxSetup);

            $('#customer').select2(config.select2(customerUrl, customerCb))
        },
    }

    $(() => Form.init());
</script>
@endsection