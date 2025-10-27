<div class='form-group row'>
    <div class='col-lg-4'>
        {{ Form::label( 'customer_id', 'Search Customer',['class' => 'control-label']) }}
       <select name="customer_id" id="customer" class="form-control" data-placeholder="Search Customer">
           <option value="">Search Customer</option>
           @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" {{ @$delivery_schedule->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name ?: $customer->company }}</option>
            @endforeach
       </select>
    </div>
    <div class='col-lg-5'>
        {{ Form::label( 'order_id', 'Order',['class' => 'control-label']) }}
       <select name="order_id" id="order" class="form-control">
        @isset($delivery_schedule)
            <option value="{{ $delivery_schedule->order_id }}">{{ gen4tid('ORD-',$delivery_schedule->order->tid).' '.$delivery_schedule->order->description }}</option>
        @endisset
       </select>
    </div>
    <div class='col-lg-3'>
        {{ Form::label( 'delivery_date', 'Delivery Date',['class' => 'control-label']) }}
        {{ Form::date('delivery_date', now(), ['class' => 'form-control', 'placeholder' => 'Delivery Date']) }}
    </div>
</div>
<div class='form-group row'>
    <div class='col-lg-10'>
        {{ Form::label( 'remarks', 'Remarks',['class' => 'control-label']) }}
        {{ Form::text('remarks', null, ['class' => 'form-control round', 'placeholder' => 'Remarks']) }}
    </div>
</div>
