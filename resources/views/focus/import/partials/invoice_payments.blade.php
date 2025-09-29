{{ Form::open(['route' => ['biller.import.general', 'invoice_payments'], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'files' => true, 'id' => 'import-data']) }}
    <input type="hidden" name="update" value="1">
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
    <div class="row form-group">
        <div class="col-4">
            <label for="customer">Customer</label>
            <select class="form-control" name="customer_id" id="customer" data-placeholder="Choose Customer" required></select>
        </div>
        <div class="col-2">
            <label for="account">Receive On Bank (Ledger Account)</label>
                    <select name="account_id" id="account" class="custom-select" required>
                        <option value="">-- Select Account --</option>
                            @foreach ($data['accounts'] as $row)
                                <option value="{{ $row->id }}">{{ $row->holder }}</option>
                            @endforeach
                    </select>                                      
            </select>
        </div>
        <div class="col-2">
            <label for="type">Payment Type</label>
            <select name="payment_type" id="payment_type" class="custom-select">
                @foreach (['per_invoice', 'on_account', 'advance_payment'] as $val)
                    <option value="{{ $val }}">
                        {{ ucwords(str_replace('_', ' ', $val)) }}
                    </option>
                @endforeach
            </select>
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