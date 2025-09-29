<div class="form-group row">
    <div class="col-2">
        <label for="row_number">Row No.</label>
        {{ Form::text('row_num', null, ['class' => 'form-control']) }}
    </div>
    <div class="col-4">
        <label for="supplier">Supplier</label>
        <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Choose-Supplier" {{ @$supplier_product? 'disabled' : 'required' }}>
            @foreach($suppliers as $row)
                <option value="{{ $row->id }}" {{ @$supplier_product && $supplier_product->supplier_id == $row->id? 'selected' : '' }}>
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
   @php
        $supplier_prod = \App\Models\product\ProductVariation::where('code',$supplier_product->product_code)->first();
    @endphp
   <div class="col-4">
       
        <label for="system_descr">System Description</label>
        <input type="text" class="form-control"  name="" value="{{@$supplier_product->product_variation ? $supplier_product->product_variation->name : ''}}"  id="" readonly>
    </div>
    <div class="col-2">
        <label for="code">Product Code</label>
        <input type="text" name="product_code" class="form-control" value="{{@$supplier_product->product_code}}" id="" readonly>
    </div>
    <div class="col-2">
        <label for="rate">Rate (VAT Exclusive)</label>
        {{ Form::text('rate', null, ['class' => 'form-control', 'id' => 'rate', 'required']) }}
    </div>
</div>

<div class="edit-form-btn">
    {{ link_to_route('biller.pricelistsSupplier.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
    {{ Form::submit(@$supplier_product? 'Update' : 'Create', ['class' => 'btn btn-primary btn-md']) }}                                            
</div>     

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
    };
    
    const Form = {
        supplierProduct: @json(@$supplier_product),

        init() {

            if ($('#contract').val()) {
                $('#supplier').select2({allowClear: true}).attr('disabled', true);
                $('#contract').attr('readonly', true);
            }
            $('#supplier').select2({allowClear: true});
            $('#product_id').select2({allowClear: true});
            $('#rate').focusout(this.rateChange);

            if (this.supplierProduct) {
                $('#rate').trigger('focusout');
            } else {
                $('#supplier').val('').trigger('change');
            }
            $('#product_id').change(function () {
                // Get the selected option
                var selectedOption = $(this).find(':selected');
                
                // Extract data attributes from the selected option
                var productCode = selectedOption.attr('product_code');
                var uom = selectedOption.attr('uom');
                var qty = selectedOption.attr('qty');
                
                // Populate input fields
                $('#product_code').val(productCode);
                $('#uom').val(uom);
                $('#qty').val(qty);
            });
        },

        rateChange() {
            const value = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(value));
        },
    };

    $(() => Form.init());
</script>
@endsection