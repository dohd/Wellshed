@if (config('services.efris.base_url'))
    <div class="row">
        <div class="col-6">
            <div class="row">
                <div class="col-6">
                    <div class='form-group'>
                        {{ Form::label( 'code', trans('currencies.code'),['class' => 'col-12 control-label']) }}
                        <div class='col-12'>
                            {{ Form::text('code', null, ['class' => 'form-control round', 'placeholder' => trans('currencies.code'),'maxlength '=>3]) }}
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <label for="code">
                        EFRIS Currency <span class="text-danger">*</span>                        
                        <div style="width: 1rem; height: 1rem;" class="spinner-border unit-loading" role="status"><span class="sr-only">Loading...</span></div>
                    </label>
                    <select name="efris_currency" id="efrisCurrency" class="custom-select" data-placeholder="Search Currency">
                        <option value=""></option>
                    </select>     
                    {{ Form::hidden('efris_currency_name', null, ['id' => "efrisCurrencyName"]) }}  
                </div>
            </div>
        </div>
    </div>
@else
    <div class='form-group'>
        {{ Form::label( 'code', trans('currencies.code'),['class' => 'col-lg-2 control-label']) }}
        <div class='col-lg-3'>
            {{ Form::text('code', null, ['class' => 'form-control round', 'placeholder' => trans('currencies.code'),'maxlength '=>3]) }}
        </div>
    </div>
@endif


<div class='form-group'>
    {{ Form::label( 'symbol', trans('currencies.symbol'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-3'>
        {{ Form::text('symbol', null, ['class' => 'form-control round', 'placeholder' => trans('currencies.symbol'),'maxlength '=>3]) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'rate', trans('currencies.rate'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-3'>
        @if(isset($currencies))
            {{ Form::text('rate', null, ['class' => 'form-control round', 'placeholder' => trans('currencies.rate'),'onkeypress'=>"return isNumber(event)",'maxlength '=>10]) }}
        @else
            {{ Form::text('rate', 1, ['class' => 'form-control round', 'placeholder' => trans('currencies.rate'),'onkeypress'=>"return isNumber(event)",'maxlength '=>10]) }}
        @endif
        <small>{{trans('currencies.rate_info')}}</small>
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'thousand_sep', trans('currencies.thousand_sep'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-3'>

        <select name="thousand_sep" class="form-control round">
            @if(isset($currencies))

                <option value="{{$currencies->thousand_sep}}"> {{ $currencies->thousand_sep }} </option>
            @endif
            <option value=",">, (Comma)</option>
            <option value=".">. (Dot)</option>
        </select>
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'decimal_sep', trans('currencies.decimal_sep'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-3'>
        <select name="decimal_sep" class="form-control round">
            @if(isset($currencies))
                <option value="{{$currencies->decimal_sep}}"> {{ $currencies->decimal_sep }} </option>
            @endif
            <option value=".">. (Dot)</option>
            <option value=",">, (Comma)</option>

        </select>
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'precision_point', trans('currencies.precision_point'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-3'>

        <select name="precision_point" class="form-control round">
            @if(isset($currencies))
                <option value="{{$currencies->precision_point}}">--{{ $currencies->precision_point }}--</option>
            @endif
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
        </select>
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'symbol_position', trans('currencies.symbol_position'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-3'>
        <select name="symbol_position" class="form-control round">
            @if(isset($currencies))
                <option value="{{$currencies->symbol_position}}">
                    --{{ ($currencies->symbol_position==1) ? trans('currencies.left') : trans('currencies.right') }}--
                </option>
            @endif
            <option value="1">{{trans('currencies.left')}}</option>
            <option value="0">{{trans('currencies.right')}}</option>
        </select>

    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Form = {
        init() {
            $.ajaxSetup(config.ajax);
            $('#efrisCurrency').select2({allowClear: true});
            
            const isEfris = @json(config('services.efris.base_url'));
            if (isEfris) Form.fetchEfrisCurrencies();
            
            $('#efrisCurrency').change(Form.efrisCurrencyChange);
        },

        fetchEfrisCurrencies() {
            $.post("{{ route('biller.efris.system_dictionary_update') }}", {key: 'currencyType'})
            .then(currencyType => {
                if (currencyType && currencyType.length) {
                    currencyType.forEach(v => {
                        $('#efrisCurrency').append(`<option value="${v.value}" name="${v.name}">${v.name}<option>`);
                    });
                    $('#efrisCurrency').change();
                    $('.unit-loading').addClass('d-none');
                    // on editing
                    const currency = @json(@$currency);
                    if (currency && currency.id) {
                        $('#efrisCurrency').val(currency.efris_currency);
                        $('#efrisCurrencyName').val(currency.efris_currency_name);
                    }
                }
            })
            .fail((xhr, status, error) => {
                $('.unit-loading').addClass('d-none');
            });
        },

        efrisCurrencyChange() {
            if ($(this).val()) {
                const name = $(this).find(':selected').attr('name');
                $('#efrisCurrencyName').val(name);
            } else {
                $('#efrisCurrencyName').val('');
            }
        },
    };
    $(Form.init);
</script>
@endsection
