<div class='form-group row'>
    <div class='col-lg-4'>
        {{ Form::label('customer_id', 'Search Customer', ['class' => 'control-label']) }}
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Search Customer">
            <option value="">Search Customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}"
                    {{ @$delivery_schedule->customer_id == $customer->id ? 'selected' : '' }}>
                    {{ $customer->name ?: $customer->company }}</option>
            @endforeach
        </select>
    </div>
    <div class='col-lg-5'>
        {{ Form::label('order_id', 'Order', ['class' => 'control-label']) }}
        <select name="order_id" id="order" class="form-control">
            @isset($delivery_schedule)
                <option value="{{ $delivery_schedule->order_id }}">
                    {{ gen4tid('ORD-', $delivery_schedule->order->tid) . ' ' . $delivery_schedule->order->description }}</option>
            @endisset
        </select>
    </div>
    <div class='col-lg-3'>
        {{ Form::label('delivery_date', 'Delivery Date', ['class' => 'control-label']) }}
        {{ Form::date('delivery_date', now(), ['class' => 'form-control', 'placeholder' => 'Delivery Date']) }}
    </div>
</div>
<div class='form-group row'>
    <div class='col-lg-10'>
        {{ Form::label('remarks', 'Remarks', ['class' => 'control-label']) }}
        {{ Form::text('remarks', null, ['class' => 'form-control round', 'placeholder' => 'Remarks']) }}
    </div>
</div>
<div class="card-body">
    <table id="itemsTbl" class="table table-bordered" width="100%">
        <thead>
            <tr>
                <th>#</th>
                <th style="width: 10%;">Product Code</th>
                <th style="width: 45%;">Product</th>
                <th style="width: 15%;">Planned Qty</th>
                <th style="width: 15%;">Rate</th>
                <th style="width: 15%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @isset($delivery_schedule)
                @foreach ($delivery_schedule->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            <input type="text" name="items[{{ $index }}][product_code]" class="form-control"
                                value="{{ $item->product->code ?? '' }}" readonly>
                        </td>

                        <td>
                            <input type="text" name="items[{{ $index }}][product_name]" class="form-control"
                                value="{{ $item->product->name ?? '' }}" readonly>
                        </td>

                        <td>
                            <input type="number" name="items[{{ $index }}][qty]" class="form-control text-end qty"
                                value="{{ $item->qty }}">
                        </td>


                        <td>
                            <input type="text" name="items[{{ $index }}][rate]"
                                class="form-control text-end rate" value="{{ $item->rate }}" readonly>
                        </td>

                        <td>
                            <input type="text" name="items[{{ $index }}][amount]"
                                class="form-control text-end amt" value="{{ numberFormat($item->amount) }}" readonly>
                            <input type="hidden" value="{{ $item->order_item->itemtax }}" class="rowtax">
                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                        </td>
                    </tr>
                @endforeach
            @endisset
        </tbody>
    </table>
</div>
