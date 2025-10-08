<div class='form-group row'>
    <div class='col-md-5'>
        {{ Form::label( 'order_id', 'Select Order',['class' => 'control-label']) }}
       <select name="order_id" id="order" class="form-control" data-placeholder="Search Order">
            <option value="">Search Order</option>
            @foreach ($orders as $order)
                @php
                    $tid = gen4tid('ORD-',$order->tid);
                    $customer = $order->customer ? $order->customer->company : '';
                    $full = $tid .'-'.$customer;
                @endphp
                <option value="{{ $order->id }}">{{ $full }}</option>
            @endforeach
       </select>
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'delivery_schedule_id', 'Search Delivery Schedule',['class' => 'control-label']) }}
       <select name="delivery_schedule_id" id="delivery_schedule" class="form-control" data-placeholder="Search Delivery Schedule">
            <option value="">Search Delivery Schedule</option>
       </select>
    </div>
    <div class='col-md-3'>
        {{ Form::label( 'date', 'Delivery Date',['class' => 'control-label']) }}
        {{ Form::date('date', null, ['class' => 'form-control', 'placeholder' => 'Delivery Date']) }}
    </div>
</div>
<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label( 'driver', 'Change Driver',['class' => 'control-label']) }}
        <select name="driver_id" id="driver" class="form-control" data-placeholder="Search Driver">
            <option value="">Search Driver</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->fullname }}</option>
            @endforeach
       </select>
    </div>
    <div class='col-md-8'>
        {{ Form::label( 'description', 'Description',['class' => 'control-label']) }}
        {{ Form::text('description', null, ['class' => 'form-control', 'placeholder' => 'Description']) }}
    </div>
</div>
<div class='form-group row'>
    @include('focus.deliveries.partials.schedule_items')
</div>
