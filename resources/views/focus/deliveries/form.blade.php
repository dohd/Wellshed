<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label('customer_id', 'Search Customer', ['class' => 'control-label']) }}
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Search Customer">
            <option value="">Search Customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" {{ isset($delivery) && $delivery->customer_id == $customer->id ? 'selected' : '' }}>
                    {{ $customer->company ?: $customer->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class='col-md-4'>
        {{ Form::label('order_id', 'Select Order', ['class' => 'control-label']) }}
        <select name="order_id" id="order" class="form-control" data-placeholder="Search Order">
            @if(isset($delivery) && $delivery->order)
                <option value="{{ $delivery->order->id }}" selected>{{ $delivery->order->description }}</option>
            @else
                <option value="">Search Order</option>
            @endif
        </select>
    </div>

    <div class='col-md-4'>
        {{ Form::label('delivery_schedule_id', 'Search Delivery Schedule', ['class' => 'control-label']) }}
        <select name="delivery_schedule_id" id="delivery_schedule" class="form-control" data-placeholder="Search Delivery Schedule">
            @if(isset($delivery) && $delivery->delivery_schedule)
                <option value="{{ $delivery->delivery_schedule->id }}" selected>{{ $delivery->delivery_schedule->delivery_date }}</option>
            @else
                <option value="">Search Delivery Schedule</option>
            @endif
        </select>
    </div>
</div>

<div class='form-group row'>
    <div class='col-md-3'>
        {{ Form::label('date', 'Delivery Date', ['class' => 'control-label']) }}
        {{ Form::date('date', old('date', isset($delivery) ? $delivery->date : now()), ['class' => 'form-control', 'placeholder' => 'Delivery Date']) }}
    </div>

    <div class='col-md-4'>
        {{ Form::label('driver', 'Change Driver', ['class' => 'control-label']) }}
        <select name="driver_id" id="driver" class="form-control" data-placeholder="Search Driver">
            <option value="">Search Driver</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ isset($delivery) && $delivery->driver_id == $user->id ? 'selected' : '' }}>
                    {{ $user->fullname }}
                </option>
            @endforeach
        </select>
    </div>

    <div class='col-md-5'>
        {{ Form::label('description', 'Description', ['class' => 'control-label']) }}
        {{ Form::text('description', old('description', isset($delivery) ? $delivery->description : null), ['class' => 'form-control', 'placeholder' => 'Description']) }}
    </div>
</div>

<div class='form-group row'>
    @include('focus.deliveries.partials.schedule_items')
</div>
