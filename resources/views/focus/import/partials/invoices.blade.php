{{ Form::open(['route' => ['biller.import.general', 'invoices'], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'files' => true, 'id' => 'import-data']) }}
    <input type="hidden" name="update" value="1">
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}

    <div class="row form-group">
        <div class="col-4">
            <label for="customer">Customer</label>
            <select class="form-control" name="customer_id" id="customer" data-placeholder="Choose Customer" required></select>
        </div>
        <div class="col-2">
            <label for="income_category" class="caption">Income Category*</label>
            <select class="custom-select" name="account_id" required>
                <option value="">-- Select Category --</option>                                        
                @foreach ($data['accounts'] as $row)
                    @php
                        $account_type = $row->accountType;
                        if ($account_type->name != 'Income') continue;
                    @endphp

                    @if($row->holder !== 'Stock Gain' && $row->holder !== 'Others' && $row->holder !== 'Point of Sale' && $row->holder !== 'Loan Penalty Receivable' && $row->holder !== 'Loan Interest Receivable')
                        <option value="{{ $row->id }}" {{ $row->id == @$invoice->account_id ? 'selected' : '' }}>
                            {{ $row->holder }}
                        </option>
                    @endif

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