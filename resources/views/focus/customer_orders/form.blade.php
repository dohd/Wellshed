<div class='form-group row'>
    <div class="col-md-2">
        <label for="tid" class="caption">Order No.</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            {{ Form::text('tid', gen4tid('ORD-', @$orders ? $orders->tid : $last_tid + 1), ['class' => 'form-control round', 'disabled']) }}
            {{ Form::hidden('tid', @$orders ? $orders->tid : $last_tid + 1) }}
        </div>
    </div>
    <div class='col-lg-4'>
        {{ Form::label('customer', 'Search Customer', ['class' => 'control-label']) }}
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Search Customer">
            <option value="">Search Customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}"
                    {{ $customer->id == @$customer_order->customer_id ? 'selected' : '' }}>{{ $customer->company }}
                </option>
            @endforeach
        </select>
    </div>
    <div class='col-lg-4'>
        {{ Form::label('branch', 'Search Branch', ['class' => 'control-label']) }}
        <select name="branch_id" id="branch" class="form-control" data-placeholder="Search Branch">
            <option value="">Search Branch</option>
        </select>
    </div>
    <div class='col-lg-2'>
        {{ Form::label('order_type', 'Select Order Type', ['class' => 'control-label']) }}
        <select name="order_type" id="order_type" class="form-control" required>
            <option value="">--select order type--</option>
            <option value="one_time" {{ @$customer_order->order_type == 'one_time' ? 'selected' : '' }}>One Time
            </option>
            <option value="recurring" {{ @$customer_order->order_type == 'recurring' ? 'selected' : '' }}>Recurring
            </option>
        </select>
    </div>
</div>
<div class='form-group row'>
    <div class='col-lg-10'>
        {{ Form::label('description', 'Description', ['class' => 'control-label']) }}
        {{ Form::text('description', null, ['class' => 'form-control', 'placeholder' => 'Description']) }}
    </div>
    
</div>
<hr>
<h2>Delivery Frequency</h2>
<div>
    <div class='form-group row'>
        <div class='col-lg-4'>
            {{ Form::label('frequency_type', 'Delivery Frequency', ['class' => 'control-label']) }}
            <select name="frequency" id="frequency_type" class="form-control">
                <option value="">--select frequency type--</option>
                <option value="daily" {{ @$customer_order->frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ @$customer_order->frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="bi_weekly" {{ @$customer_order->frequency == 'bi_weekly' ? 'selected' : '' }}>Bi Weekly</option>
                <option value="monthly" {{ @$customer_order->frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>
        <div class="col-lg-4">
            <label for="driver">Search Driver</label>
            <select name="driver_id" id="driver" class="form-control" data-placeholder="Search Driver">
                <option value="">Search Driver</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ @$customer_order->driver_id == $user->id ? 'selected' : '' }}>{{ $user->fullname }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4">
            <label for="route">Route</label>
            {{-- <input type="text" value="{{ @$customer_order->route }}" name="route" id="route" class="form-control"> --}}
            <select name="route" id="route" class="form-control" data-placeholder="Search Route">
                <option value="">Search Route</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" {{ $location->id == @$customer_order->route ? 'selected' : '' }}>{{ $location->target_zone->name.' - '.$location->sub_zone_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class='form-group row'>
        <div class='col-lg-4'>
            {{ Form::label('start_month', 'Start (Period From)', ['class' => 'control-label']) }}
            {{ Form::month('start_month',@$customer_order->start_month, ['class' => 'form-control', 'placeholder' => 'Start (Period From)']) }}
        </div>
        <div class='col-lg-4'>
            {{ Form::label('end_month', 'End (Period To)', ['class' => 'control-label']) }}
            {{ Form::month('end_month', @$customer_order->end_month, ['class' => 'form-control', 'placeholder' => 'End (Period To)']) }}
        </div>
        {{-- <div class='col-lg-4'>
            {{ Form::label('expected_time', 'Expected Delivery Time', ['class' => 'control-label']) }}
            {{ Form::time('expected_time', @$customer_order->expected_time, ['class' => 'form-control', 'placeholder' => 'Expected Delivery Time']) }}
        </div> --}}
    </div>
    <div class='form-group row'>
        <div class="col-6">
            <div class="table-responsive">
                <table id="daysTbl" class="table" widht="50%">
                    <thead>
                        <tr>
                            <th>Delivery Days</th>
                            <th>Expected Delivery Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- schedule row template -->
                        <tr>
                            @php
                                $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Sarturday','Sunday'];
                            @endphp
                            <td>
                                <select name="delivery_days[]" id="delivery_days" class="form-control">
                                    <option value="">Select a day</option>
                                    @foreach($days as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                {{ Form::time('expected_time[]', null, ['class' => 'form-control', 'placeholder' => 'Expected Delivery Time','id' => 'expected_time-0']) }}
                            </td>
                            <input type="hidden" name="d_id[]" value="0" id="">
                            <td>
                                <button type="button" class="btn btn-outline-light btn-sm mt-1 remove">
                                    <i class="fa fa-trash fa-lg text-danger"></i>
                                </button>
                            </td>
                        </tr>
                        @isset($customer_order)
                            @if (count($customer_order->deliver_days) > 0)
                                @foreach ($customer_order->deliver_days as $i => $item)
                                    <tr>
                                        @php
                                            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Sarturday','Sunday'];
                                        @endphp
                                        <td>
                                            <select name="delivery_days[]" id="delivery_days-{{ $i+1 }}" class="form-control">
                                                <option value="">Select a day</option>
                                                @foreach($days as $day)
                                                    <option value="{{ $day }}" {{ $item->delivery_days == $day ? 'selected' : '' }}>{{ $day }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            {{ Form::time('expected_time[]', $item->expected_time, [
                                                'class' => 'form-control',
                                                'placeholder' => 'Expected Delivery Time',
                                                'id' => 'expected_time-' . ($i + 1)
                                            ]) }}
                                        </td>
                                        <input type="hidden" name="d_id[]" value="{{ $item->id }}" id="">
                                        <td>
                                            <button type="button" class="btn btn-outline-light btn-sm mt-1 remove">
                                                <i class="fa fa-trash fa-lg text-danger"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endisset
                    </tbody>
                </table>
            </div>
            <button class="btn btn-success btn-sm ml-2" type="button" id="addDoc">
                <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
            </button>
        </div>
    </div>

</div>
<hr>
<h2>Items</h2>
@include('focus.customer_orders.partials.order_items')
<div class="form-group row">
    <div class="col-9"></div>
    <div class="col-3">
        <label class="mb-0">Subtotal</label>
        <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>
        {{-- <label class="mb-0">Taxable</label> --}}
        <input type="hidden" name="taxable" id="vatable" class="form-control" readonly>
        <label class="mb-0" id="tax-label">{{ trans('general.total_tax') }}</label>
        <input type="text" name="tax" id="tax" class="form-control" readonly>
        <label class="mb-0">{{ trans('general.grand_total') }}
        </label>
        <input type="text" name="total" class="form-control" id="total" readonly>
        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }}
    </div>
</div>
