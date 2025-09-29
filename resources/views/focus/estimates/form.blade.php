<div class="row mb-1">
    <div class="col-md-2 col-12">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div>
    <div class="col-md-4 col-12 select-col">
        <label for="customer">Customer</label>
        <select name="customer_id" id="customer" class="form-control d-none" data-placeholder="Search Customer" autocomplete="off" required>
            <option value=""></option>
            @foreach ($customers as $row)
                <option value="{{ $row->id }}" {{ @$estimate->customer_id == $row->id? 'selected' : ''}}>
                    {{ $row->company ?: $row->name }}
                </option>    
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-12">
        <label for="quote">Quote / PI</label>
        <select name="quote_id" id="quote" class="form-control" data-placeholder="Search Quote / PI Number" autocomplete="off" required>
            <option value=""></option>
            @if (@$estimate->quote)
                <option value="{{ $estimate->quote_id }}" selected>
                    {{ gen4tid($estimate->quote->bank_id? 'PI-' : 'QT-', $estimate->quote->tid) }} {{ $estimate->quote->notes }}
                </option>    
            @endif
        </select>
    </div>
</div>

<div class="row mb-1">
    <div class="col-md-12 col-12">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div>
</div>

<div class="table-responsive">
    <table id="productsTbl" class="table table-sm tfr my_stripe_single text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th width="25%">Product</th>
                <th>UoM</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amount</th>
                <th>Est. Qty</th>
                <th>Est. Rate</th>
                <th>Est. Amount</th>
            </tr>
        </thead>
        <tbody>
            @if (@$estimate)
                @foreach ($estimate->items as $i => $item)
                    <tr>
                        <td><span class="num">{{ @$item->vrf_item->numbering }}</span></td>
                        <td><span class="name">{{ @$item->vrf_item->product_name }}</span></td>
                        <td><span class="unit">{{ @$item->vrf_item->unit }}</span></td>                
                        <td><span class="qty">{{ +$item->qty }}</span></td>
                        <td><span class="rate">{{ numberFormat($item->rate) }}</span></td>
                        <td><span class="amount">{{ numberFormat($item->amount) }}</span></td>
                        <td><input type="text" name="est_qty[]" value="{{ +$item->est_qty }}" class="form-control est-qty"></td>
                        <td><input type="text" name="est_rate[]" value="{{ numberFormat($item->est_rate) }}" class="form-control est-rate" readonly></td>
                        <td><input type="text" name="est_amount[]" value="{{ numberFormat($item->est_amount) }}" class="form-control est-amount"></td>
                        <input type="hidden" name="indx[]" value="{{ $item->indx }}" class="indx">
                        <input type="hidden" name="vrf_item_id[]" value="{{ $item->vrf_item_id }}" class="vrfitem-id">
                        <input type="hidden" name="productvar_id[]" value="{{ $item->productvar_id }}" class="prodvar-id">
                        <input type="hidden" name="tax[]" value="{{ numberFormat($item->tax) }}" class="tax">
                        <input type="hidden" name="qty[]" value="{{ +$item->qty }}" class="qty-inp">
                        <input type="hidden" name="rate[]" value="{{ numberFormat($item->rate) }}" class="rate-inp">
                        <input type="hidden" name="amount[]" value="{{ numberFormat($item->amount) }}" class="amount-inp">
                        <input type="hidden" name="rem_qty[]" value="{{ +$item->est_qty }}" class="rem-qty">
                        <input type="hidden" name="rem_amount[]" value="{{ numberFormat($item->rem_amount) }}" class="rem-amount">
                    </tr>
                @endforeach
            @else
                <tr class="d-none">
                    <td><span class="num"></span></td>
                    <td><span class="name"></span></td>
                    <td><span class="unit"></span></td>                
                    <td><span class="qty"></span></td>
                    <td><span class="rate"></span></td>
                    <td><span class="amount"></span></td>
                    <td><input type="text" name="est_qty[]" class="form-control est-qty"></td>
                    <td><input type="text" name="est_rate[]" class="form-control est-rate" readonly></td>
                    <td><input type="text" name="est_amount[]" class="form-control est-amount"></td>
                    <input type="hidden" name="indx[]" class="indx">
                    <input type="hidden" name="vrf_item_id[]" class="vrfitem-id">
                    <input type="hidden" name="productvar_id[]" class="prodvar-id">
                    <input type="hidden" name="tax[]" class="tax">
                    <input type="hidden" name="qty[]" class="qty-inp">
                    <input type="hidden" name="rate[]" class="rate-inp">
                    <input type="hidden" name="amount[]" class="amount-inp">
                    <input type="hidden" name="rem_qty[]" class="rem-qty">
                    <input type="hidden" name="rem_amount[]" class="rem-amount">
                </tr>
            @endif
        </tbody>
    </table>
</div>   
<div class="form-group row mt-2">
    <div class="col-4 ml-auto">
        <div class="row no-gutters mb-1">
            <div class="col-6 text-right"><label for="total" class="mr-1 mt-1">Total Amount</label></div>
            <div class="col-6">
                {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total','readonly' => 'readonly', 'autocomplete' => "off"]) }}
            </div>
        </div>
        <div class="row no-gutters mb-1">
            <div class="col-6 text-right"><label for="total" class="mr-1 mt-1">Total Est. Amount</label></div>
            <div class="col-6">
                {{ Form::text('est_total', null, ['class' => 'form-control', 'id' => 'est-total','readonly' => 'readonly', 'autocomplete' => "off"]) }}
            </div>
        </div>
        <div class="row no-gutters">
            <div class="col-6 text-right"><label for="total" class="mr-1 mt-1">Balance</label></div>
            <div class="col-6">
                {{ Form::text('balance', null, ['class' => 'form-control', 'id' => 'balance','readonly' => 'readonly', 'autocomplete' => "off"]) }}
            </div>
        </div>
    </div>
</div>

@section('extra-scripts')
@include('focus.estimates.form_js')
@endsection
