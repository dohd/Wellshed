<div class='form-group row'>
    <div class="col-md-2">
        <label for="tid" class="caption">Order No.</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o"></span></div>
            {{ Form::text('tid', gen4tid('ORD-', @$orders ? $orders->tid : $last_tid + 1), ['class' => 'form-control round', 'disabled']) }}
            {{ Form::hidden('tid', @$orders ? $orders->tid : $last_tid + 1) }}
        </div>
    </div>

    <div class='col-lg-4'>
        {{ Form::label('customer', 'Search Customer') }}
        <select name="customer_id" id="customer" class="form-control">
            <option value="">Search Customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}"
                    {{ @$customer_order->customer_id == $customer->id ? 'selected' : '' }}>
                    {{ $customer->company }}
                </option>
            @endforeach
        </select>
    </div>

    <div class='col-lg-4'>
        {{ Form::label('branch', 'Search Branch') }}
        <select name="branch_id" id="branch" class="form-control">
            <option value="">Search Branch</option>
        </select>
    </div>

    <div class='col-lg-2'>
        {{ Form::label('order_type', 'Select Order Type') }}
        <select name="order_type" id="order_type" class="form-control" required>
            <option value="">--select--</option>
            <option value="one_time" {{ @$customer_order->order_type == 'one_time' ? 'selected' : '' }}>One Time</option>
            <option value="recurring" {{ @$customer_order->order_type == 'recurring' ? 'selected' : '' }}>Recurring</option>
        </select>
    </div>
</div>

<div class='form-group row'>
    <div class='col-lg-10'>
        {{ Form::label('description', 'Description') }}
        {{ Form::text('description', @$customer_order->description, ['class' => 'form-control', 'placeholder' => 'Description']) }}
    </div>
</div>

<hr>

<h2>Delivery Frequency</h2>

<div class='form-group row'>
    <div class='col-lg-4'>
        {{ Form::label('frequency_type', 'Delivery Frequency') }}
        <select name="frequency" id="frequency_type" class="form-control">
            <option value="">--select--</option>
            <option value="daily" {{ @$customer_order->frequency == 'daily' ? 'selected' : '' }}>Daily</option>
            <option value="weekly" {{ @$customer_order->frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
            <option value="custom" {{ @$customer_order->frequency == 'custom' ? 'selected' : '' }}>Custom</option>
        </select>
    </div>

    <div class="col-lg-4">
        <label for="driver">Search Driver</label>
        <select name="driver_id" id="driver" class="form-control">
            <option value="">Search Driver</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ @$customer_order->driver_id == $user->id ? 'selected' : '' }}>
                    {{ $user->fullname }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- <div class="col-lg-4">
        <label for="route">Route</label>
        <select name="route" id="route" class="form-control">
            <option value="">Search Route</option>
            @foreach ($locations as $loc)
                <option value="{{ $loc->id }}" {{ @$customer_order->route == $loc->id ? 'selected' : '' }}>
                    {{ $loc->target_zone->name . ' - ' . $loc->sub_zone_name }}
                </option>
            @endforeach
        </select>
    </div> --}}
    <div class='col-lg-4'>
        {{ Form::label('start_month', 'Start Month') }}
        {{ Form::date('start_month', @$customer_order->start_month, ['class' => 'form-control']) }}
    </div>
</div>

<div class='form-group row'>

    {{-- <div class='col-lg-4'>
        {{ Form::label('end_month', 'End Month') }}
        {{ Form::date('end_month', @$customer_order->end_month, ['class' => 'form-control']) }}
    </div> --}}
    <div class="col-lg-6">
        <label>Delivery Days</label>
        {{-- {{ dd($customer_order->deliver_days()->first()->delivery_days) }} --}}
        <select name="delivery_days[]" multiple class="form-control">
            @php
                $dayList = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

                $selected = isset($customer_order)
                    ? optional($customer_order->deliver_days()->first())->delivery_days ?? []
                    : [];

                // If stored as JSON string → decode
                if (!is_array($selected)) {
                    $selected = json_decode($selected, true) ?? [];
                }

                $selectedDays = array_values($selected); // Ensure it's a simple array
            @endphp

            @foreach ($dayList as $day)
                <option value="{{ $day }}" {{ in_array($day, $selectedDays) ? 'selected' : '' }}>
                    {{ ucfirst($day) }}
                </option>
            @endforeach
        </select>

        <small>Select days (e.g. Mon, Tue)</small>
    </div>
    <div class="col-lg-6">
        <label>Delivery Locations</label>

        @php
            // Load JSON from DB
            // adjust column name if different

            $savedDays = isset($customer_order)
                    ? optional($customer_order->deliver_days()->first())->locations_for_days ?? []
                    : [];
            
            // Decode JSON to array
            $savedDays = is_array($savedDays) ? $savedDays : json_decode($savedDays, true);

            // Collect all unique location IDs from JSON
            $selectedLocations = [];

            if(!empty($savedDays)) {
                foreach($savedDays as $day => $locIds) {
                    foreach($locIds as $locId) {
                        $selectedLocations[] = $locId;
                    }
                }
            }

            // Ensure unique IDs
            $selectedLocations = array_unique($selectedLocations);
        @endphp

        <select name="locations[]" multiple class="form-control">
            @foreach ($locations as $loc)
                <option value="{{ $loc->id }}"
                    {{ in_array($loc->id, $selectedLocations) ? 'selected' : '' }}>
                    {{ $loc->target_zone->name }} - {{ $loc->sub_zone_name }}
                </option>
            @endforeach
        </select>

        <small>Select multiple zones e.g Westlands, Kilimani</small>
    </div>
</div>

{{-- ✅ Delivery Days --}}
{{-- <div class="form-group row">
    
</div> --}}

{{-- ✅ Locations multi select --}}
<div class="form-group row">
    <div class="col-lg-6">
        <label>Week Numbers</label>

        @php
            $selected = isset($customer_order)
                    ? optional($customer_order->deliver_days()->first())->week_numbers ?? []
                    : [];

            // Ensure value is an array
            if (!is_array($selected)) {
                $decoded = json_decode($selected, true);
                $selected = is_array($decoded) ? $decoded : [];
            }
        @endphp

        <select name="week_numbers[]" multiple class="form-control">
            @for ($i = 1; $i <= 4; $i++)
                <option value="{{ $i }}" 
                    {{ in_array($i, $selected) ? 'selected' : '' }}>
                    Week {{ $i }}
                </option>
            @endfor
        </select>

        <small>(Optional) For monthly/bi-weekly schedules</small>
    </div>
</div>


{{-- ✅ Week Numbers --}}
{{-- <div class="form-group row">
    
</div> --}}


<hr>

<h2>Items</h2>

@include('focus.customer_orders.partials.order_items')

<div class="form-group row">
    <div class="col-9"></div>
    <div class="col-3">
        <label>Subtotal</label>
        <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>

        <input type="hidden" name="taxable" id="vatable" readonly>

        <label>Total Tax</label>
        <input type="text" name="tax" id="tax" class="form-control" readonly>

        <label>Grand Total</label>
        <input type="text" name="total" class="form-control" id="total" readonly>

        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }}
    </div>
</div>
