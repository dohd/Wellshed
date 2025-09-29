<div class="form-group row">
    <div class="col-md-6">
        <label for="customer">Customer</label>
        <select name="refill_customer_id" id="customer" class="form-control" data-placeholder="Search Customer" required>
            <option value=""></option>
            @foreach ($refill_customers as $row)
                <option value="{{ $row->id }}" {{ $row->id == @$product_refill->refill_customer_id? 'selected' : '' }}>{{ $row->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label for="date">Service Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div>

    <div class="col-md-2">
        <label for="date">Next Service Date</label>
        {{ Form::text('next_date', null, ['class' => 'form-control datepicker', 'id' => 'next_date']) }}
    </div>  
</div>

<div class="form-group row">
    <div class="col-md-6">
        <label for="products">Products</label>
        <select name="product_id[]" id="product" class="form-control" data-placeholder="Search Product" multiple required>
            @php
                $product_ids = @$product_refill? $product_refill->refill_products->pluck('id')->toArray() : [];
            @endphp
            @foreach ($refill_products as $row)
                <option value="{{ $row->id }}" {{ in_array($row->id, $product_ids)? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="col-md-2">
        <label for="date">Reminder Start Date</label>
        {{ Form::text('rem_start_date', null, ['class' => 'form-control datepicker', 'id' => 'rem_date']) }}
    </div>

    <div class="col-md-2">
        <label for="interval">Reminder Interval</label>
        <select name="rem_interval" id="interval" class="custom-select">
            <option value="">-- Select Interval --</option>
            @foreach (['daily', 'weekly', 'monthly'] as $value)
                <option value="{{ $value }}" {{ $value == @$product_refill->rem_interval? 'selected' : '' }}>{{ ucfirst($value) }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="col-md-2">
        <label for="frequency">Reminder Frequency</label>
        {{ Form::text('rem_frequency', null, ['class' => 'form-control', 'id' => 'frequency']) }}
    </div>
</div>

<div class="form-group row">
    <div class="col-md-12">
        <label for="title">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    $('#customer').select2({allowClear: true});
    $('#product').select2({allowClear: true});
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

    productRefill = @json(@$product_refill);
    if (productRefill.id) {
        $('#date').datepicker('setDate', new Date(productRefill.date));
        if (productRefill.next_date) $('#next_date').datepicker('setDate', new Date(productRefill.next_date));
        else $('#next_date').val('');
        if (productRefill.rem_start_date) $('#rem_date').datepicker('setDate', new Date(productRefill.rem_start_date));
        else $('#rem_date').val('');
    }
</script>
@endsection