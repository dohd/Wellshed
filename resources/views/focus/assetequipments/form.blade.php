<div class="row">
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'purchase_date', 'Purchase Date',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('purchase_date', null, ['class' => 'form-control box-size datepicker', 'id'=>'purchase_date']) }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('account_type', 'Account Type', ['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control required" name="account_type" id="account_type" required>
                    <option value="">-- Select Account Type --</option>
                    @foreach (['Asset' => 'Asset', 'Expense' => 'Stockable Expense e.g PPE'] as $key => $val)
                        <option value="{{ $key }}" {{ $key == @$assetequipment->account_type? 'selected' : '' }}>
                            {{ $val }}
                        </option>
                    @endforeach                                       
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('ledger_account', 'Ledger Account', ['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="account_id" id="account_id" data-placeholder="Choose Ledger Account" required>
                    @isset ($assetequipment) 
                        <option value="{{ $assetequipment->account_id }}" selected>
                            {{ $assetequipment->account? $assetequipment->account->holder : '' }}
                        </option>
                    @endisset
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('condition', 'Condition Type',['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="condition">
                    <option value="">-- Select Condition Type --</option>
                    @foreach (['new', 'used'] as $val)
                        <option value="{{ $val }}" {{ $val == @$assetequipment->condition? 'selected' : '' }}>
                            {{ ucfirst($val) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'name', 'Item Name',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Item Name*', 'required']) }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'serial', 'Item Serial',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('serial', null, ['class' => 'form-control box-size', 'placeholder' => 'Item Serial']) }}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('manufacturer', 'Manufacturer/Supplier',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('manufacturer', null, ['class' => 'form-control box-size', 'placeholder' => 'Vendor']) }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('model', 'Model', ['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('model', null, ['class' => 'form-control box-size']) }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'location', 'Location', ['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('location', null, ['class' => 'form-control box-size', 'placeholder' => 'Location']) }}
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('warranty', 'Under Warranty', ['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="warranty">
                    <option value="">-- Select Warranty --</option>
                    @foreach (['no', 'yes'] as $val)
                        <option value="{{ $val }}" {{ $val == @$assetequipment->warranty? 'selected' : '' }}>
                            {{ ucfirst($val) }}
                        </option>
                    @endforeach                  
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'warranty_expiry_date', 'Warranty Expiry Date', ['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('warranty_expiry_date', null, ['class' => 'form-control box-size datepicker', 'id'=>'warranty_expiry_date']) }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('item_tag', 'Item Tag',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('item_tag', null, ['class' => 'form-control box-size', 'placeholder' => 'Item Tag']) }}
            </div>
        </div>
    </div>


    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('quantity', 'Quantity',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::number('quantity', null, ['class' => 'form-control box-size', 'placeholder' => 'Quantity', 'step' => '0.01']) }}
            </div>
        </div>
    </div>


    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('unit_price', 'Unit Price',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::number('unit_price', null, ['class' => 'form-control box-size', 'placeholder' => 'Unit Price', 'step' => '0.01']) }}
            </div>
        </div>
    </div>


    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('total_price', 'Total Price',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::number('total_price', null, ['class' => 'form-control box-size', 'placeholder' => 'Total Price', 'step' => '0.01', 'readonly']) }}
            </div>
        </div>
    </div>



</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $("#account_id").select2();
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // select2 config
    $("#account_id").select2({
        ajax: {
            url: "{{ route('biller.assetequipments.ledger_load') }}",
            dataType: 'json',
            type: 'POST',
            data: ({term}) => ({term, account_type: $("#account_type").val()}),
            quietMillis: 50,
            processResults: function(data) {
                return { results: data.map(v => ({text: v.holder, id: v.id})) };
            },
        }
    });

    $("#account_type").on('change', function() {
        $("#account_id").val('').change();
    });


    function calculateTotalPrice() {
        const quantity = parseFloat($('#quantity').val());
        const unitPrice = parseFloat($('#unit_price').val());

        if (!isNaN(quantity) && !isNaN(unitPrice)) {
            const totalPrice = quantity * unitPrice;
            $('#total_price').val(totalPrice.toFixed(2)); // Display with two decimal places
        } else {
            $('#total_price').val('');
        }
    }

    // Listen for changes in the quantity and unit price fields
    $('#quantity, #unit_price').on('input', calculateTotalPrice);

    // Initialize the total price when the page loads
    calculateTotalPrice();


</script>
@endsection
