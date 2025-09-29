<div class="form-group row">
    <div class="col-2">
        <label for="row_number">Row No.</label>
        {{ Form::text('row_num', null, ['class' => 'form-control']) }}
    </div>
    <div class="col-4">
        <label for="customer">Customer</label>
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose-customer" required>
            @foreach($customers as $row)
                <option value="{{ $row->id }}" {{ @$client_product && $client_product->customer_id == $row->id? 'selected' : '' }}>
                    {{ $row->company }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="contract">Contract Title</label>
        {{ Form::text('contract', null, ['class' => 'form-control', 'required']) }}
    </div>
    <div class="col-4">
        <label for="description">Product Description</label>
        {{ Form::text('descr', null, ['class' => 'form-control', 'required']) }}
    </div>
   
</div>
<div class="form-group row">
    <div class="col-2">
        <label for="uom">Unit of Measure (UoM)</label>
        {{ Form::text('uom', null, ['class' => 'form-control', 'required']) }}
    </div>
    <div class="col-2">
        <label for="rate">Rate (Ksh.)</label>
        {{ Form::text('rate', null, ['class' => 'form-control', 'id' => 'rate', 'required']) }}
    </div>
</div>

<div class="edit-form-btn">
    {{ link_to_route('biller.client_products.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
    {{ Form::submit(@$client_product? 'Update' : 'Create', ['class' => 'btn btn-primary btn-md']) }}                                            
</div>     

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
    };
    
    const Form = {
        clientProduct: @json(@$client_product),

        init() {
            $('#customer').select2({allowClear: true});
            $('#rate').focusout(this.rateChange);

            if (this.clientProduct) {
                $('#rate').trigger('focusout');
            } else {
                $('#customer').val('').trigger('change');
            }
        },

        rateChange() {
            const value = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(value));
        },
    };

    $(() => Form.init());
</script>
@endsection