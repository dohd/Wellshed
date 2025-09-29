{{ Form::open(['route' => ['biller.import.general', 'supplier'], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'files' => true, 'id' => 'import-data']) }}
<input type="hidden" name="update" value="1">
{!! Form::file('import_file', ['class' => 'form-control input col-md-6 mb-1']) !!}
<div class='form-group'>
    {{ Form::label('accounts_payable', 'A/P Account', ['class' => 'col-lg-12 control-label']) }}
    <div class='col'>
        <select name="ap_account_id" class="form-control custom-select col-md-6" id="ap_account"
            data-placeholder="Choose A/P Account" required>
            <option value="">-- Select Account --</option>
            @foreach ($data['accounts'] as $row)
                <option value="{{ $row->id }}" currencyId="{{ $row->currency_id }}"
                    {{ $row->id == @$supplier->ap_account_id ? 'selected' : '' }}>
                    {{ $row->holder }}
                </option>
            @endforeach
        </select>
        {{ Form::hidden('currency_id', null, ['id' => 'currency']) }}
    </div>
</div>
{{ Form::submit(trans('import.upload_import'), ['class' => 'btn btn-primary btn-md']) }}
{{ Form::close() }}
@section('after-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        const config = {
            ajaxSetup: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },

        };

        const Form = {
            init() {
                const {
                    ajaxSetup,
                    customerUrl,
                    customerCb
                } = config;
                $.ajaxSetup(ajaxSetup);

                $('#ap_account').change(function() {
                    const currencyId = $(this).find(':selected').attr('currencyId');
                    $('#currency').val(currencyId);
                });
            },
        }

        $(() => Form.init());
    </script>
@endsection
