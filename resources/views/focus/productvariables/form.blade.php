<div class="row form-group">
    <div class='col-6'>
        <label for="title">UoM Title</label>
        {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'e.g Kilograms', 'required' => 'required']) }}
    </div>
</div>

<div class="row form-group">
    <div class='col-6'>         
        @if (config('services.efris.base_url'))
            <div class="row">
                <div class="col-md-6">
                    <label for="code">UoM Code</label>
                    {{ Form::text('code', null, ['class' => 'form-control', 'id' => 'code', 'placeholder' => 'e.g Kg', 'required' => 'required']) }} 
                </div>
                @if (config('services.efris.base_url'))
                    <div class="col-md-6">
                        <label for="code">
                            EFRIS UoM Code <span class="text-danger">*</span>                        
                            <div style="width: 1rem; height: 1rem;" class="spinner-border unit-loading" role="status"><span class="sr-only">Loading...</span></div>
                        </label>
                        <select name="efris_unit" id="efrisUnit" class="custom-select" data-placeholder="Search Unit">
                            <option value=""></option>
                        </select>     
                        {{ Form::hidden('efris_unit_name', null, ['id' => "efrisUnitName"]) }}               
                    </div>
                @endif
            </div>
        @else
            <label for="code">UoM Code</label>
            {{ Form::text('code', null, ['class' => 'form-control', 'id' => 'code', 'placeholder' => 'e.g Kg', 'required' => 'required']) }}  
        @endif
    </div>
</div>

<div class="row form-group">
    <div class="col-6">
        <div class="row">
            <div class='col-md-6'>
                <label for="type">Type of Unit</label>
                <select name="unit_type" id="unit_type" class="custom-select">
                    @foreach (['base', 'compound'] as $val)
                        <option value="{{ $val }}" {{ $val == @$productvariable->unit_type? 'selected' : '' }}>
                            {{ ucfirst($val) }}
                        </option>    
                    @endforeach          
                </select>
            </div>
            <div class='col-md-6'>
                <label for="type">Base Unit to Map</label>
                <select name="base_unit_id" id="base_unit_id" class="custom-select" disabled>
                    <option value=""></option>
                    @foreach ($base_units as $unit)
                        <option value="{{ $unit->id }}" {{ $val == @$productvariable->base_unit_id? 'selected' : '' }}>
                            {{ ucfirst($unit->title) }} ({{ $unit->code }})
                        </option>    
                    @endforeach          
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row form-group">
    <div class="col-6">
        <div class="row">
            <div class='col-md-6'>
                <label for="count_type">UoM Counted As</label>
                <select name="count_type" id="count_type" class="custom-select">
                    @foreach (['whole', 'rational'] as $val) 
                        <option value="{{ $val }}" {{ $val == @$productvariable->count_type? 'sselected' : '' }}>{{ ucfirst($val) }}</option>
                    @endforeach
                </select>
            </div>
            <div class='col-md-6'>
                <label for="rate">Compound Ratio Per Base Unit</label>
                {{ Form::text('base_ratio', null, ['class' => 'form-control', 'id' => 'base_ratio', 'readonly']) }}
            </div>
        </div>
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
            $('#base_unit_id').select2({allowClear: true});
            $('#efrisUnit').select2({allowClear: true});
            Form.fetchEfrisUnits();

            $('#efrisUnit').change(Form.efrisUnitChange);
            $('#unit_type').change(Form.unitTypeChange);
            $('#base_ratio').focusout(Form.baseRatioChange).focusout();

            /** Edit Mode */
            const unit = @json(@$productvariable);
            if (unit && unit.id) {
                $('#base_unit_id').val(unit.base_unit_id).attr('selected', true).change();
                $('#unit_type').change();
                const ratio = $('#base_ratio').val();
                $('#base_ratio').val(accounting.formatNumber(ratio));  
            }
        },

        fetchEfrisUnits() {
            const isEfris = @json(config('services.efris.base_url'));
            if (isEfris) {
                $.post("{{ route('biller.efris.system_dictionary_update') }}", {key: 'rateUnit'})
                .then(rateUnit => {
                    if (rateUnit && rateUnit.length) {
                        rateUnit.forEach(v => {
                            $('#efrisUnit').append(`<option value="${v.value}" name="${v.name}">${v.name}<option>`);
                        });
                        $('#efrisUnit').change();
    
                        $('.unit-loading').addClass('d-none');
                        // on editing
                        const unit = @json(@$productvariable);
                        if (unit && unit.id) {
                            $('#efrisUnit').val(unit.efris_unit);
                            $('#efrisUnitName').val(unit.efris_unit_name);
                        }
                    }
                })
                .fail((xhr, status, error) => {
                    $('.unit-loading').addClass('d-none');
                });
            }
        },

        efrisUnitChange() {
            if ($(this).val()) {
                const name = $(this).find(':selected').attr('name');
                $('#efrisUnitName').val(name);
            } else {
                $('#efrisUnitName').val('');
            }
        },

        baseRatioChange() {
            const el = $(this);
            const ratio = accounting.unformat(el.val());
            if (!ratio) el.val(1);
            if ($('#unit_type').val() == 'compound') {
                if (ratio <= 1) el.val(2);
            }
            el.val(accounting.formatNumber(el.val()));
        },

        unitTypeChange() {
            if ($(this).val() == 'compound') {
                $('#base_ratio').attr({readonly: false,required: true});
                $('#base_unit_id').attr({disabled: false,required: true});
            } else {
                $('#base_ratio').val('1.00').attr({readonly: true, required: false});
                $('#base_unit_id').attr({disabled: true, required: false}).val('').change();
            }
            $('#base_ratio').focusout();
        }
    }

    $(Form.init);
</script>
@endsection
