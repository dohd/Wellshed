
<div class='form-group row'>
    <div class="col-md-2">
        <label for="tid" class="caption">Order No.</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            {{ Form::text('tid', gen4tid("ORD-", @$orders? $orders->tid : $last_tid+1), ['class' => 'form-control round', 'disabled']) }}
            {{ Form::hidden('tid', @$orders? $orders->tid : $last_tid+1) }}
        </div>
    </div>
    <div class='col-lg-4'>
        {{ Form::label( 'customer', 'Search Customer',['class' => 'control-label']) }}
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Search Customer">
            <option value="">Search Customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->company }}</option>
            @endforeach
        </select>
    </div>
    <div class='col-lg-4'>
        {{ Form::label( 'branch', 'Search Branch',['class' => 'control-label']) }}
        <select name="branch_id" id="branch" class="form-control" data-placeholder="Search Branch">
            <option value="">Search Branch</option>
        </select>
    </div>
    <div class='col-lg-2'>
        {{ Form::label( 'order_type', 'Select Order Type',['class' => 'control-label']) }}
        <select name="order_type" id="order_type" class="form-control" required>
            <option value="">--select order type--</option>
            <option value="one_time">One Time</option>
            <option value="recurring">Recurring</option>
        </select>
    </div>
</div>
<div class='form-group row'>
    <div class='col-lg-10'>
        {{ Form::label( 'description', 'Description',['class' => 'control-label']) }}
        {{ Form::text('description', null, ['class' => 'form-control', 'placeholder' => 'Description']) }}
    </div>
    <div class='col-lg-2'>
        {{ Form::label( 'frequency', 'Select Frequency',['class' => 'control-label']) }}
        <select name="frequency" id="frequency" class="form-control" required>
            <option value="">--select frequency--</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="bi_weekly">Bi Weekly</option>
            <option value="monthly">Monthly</option>
        </select>
    </div>
</div>
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
        <label class="mb-0">{{trans('general.grand_total')}}
        </label>
        <input type="text" name="total" class="form-control" id="total" readonly>
        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }}
    </div>
</div>