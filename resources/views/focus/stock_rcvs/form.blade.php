<div class="row mb-1"> 
    <div class="col-md-2 col-12">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div>
    <div class="col-md-3 col-12">
        <label for="ref_no">Reference No.</label>
        {{ Form::text('ref_no', null, ['class' => 'form-control', 'id' => 'ref_no']) }}
    </div>
    <div class="col-md-7 col-12">
        <label for="employee">Received By</label>
        <select name="receiver_id" id="receiver" class="form-control" data-placeholder="Search Employee" autocomplete="off" required>
            <option value=""></option>
            @foreach ($employees as $row)
                <option value="{{ $row->id }}" {{ @$stock_rcv->receiver_id == $row->id? 'selected' : '' }}>
                    {{ $row->first_name }} {{ $row->last_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="row mb-1">
    <div class="col-md-12">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div>
</div>
<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="productsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th width="30%">Stock Item</th>
                <th>Unit</th>
                <th>Transf. Qty</th>
                <th>Qty Rem</th>
                <th width="10%">Received. Qty</th>
            </tr>
        </thead>
        <tbody>
            @if (@$stock_rcv)
                @foreach ($stock_rcv->items as $i => $item)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><span class="name">{{ @$item->productvar->name }}</span></td> 
                        <td><span class="unit">{{ @$item->productvar->product->unit->code }}</span></td>                
                        <td><span class="qty-transf">{{ +$item->qty_transf }}</span></td>
                        <td><span class="qty-rem">{{ +$item->qty_rem }}</span></td>
                        <td><input type="text" name="qty_rcv[]" value="{{ +$item->qty_rcv }}" class="form-control qty-rcv" autocomplete="off"></td>
                        <input type="hidden" value="{{ round($item->transfer_item->rcv_items()->sum('qty_rcv')) }}" class="qty-accum">
                        <input type="hidden" name="qty_rem[]" value="{{ +$item->qty_rem }}" class="qty-rem-inp">
                        <input type="hidden" name="qty_transf[]" value="{{ +$item->qty_transf }}" class="qty-transf-inp">
                        <input type="hidden" name="cost[]" value="{{ $item->cost }}" class="cost">
                        <input type="hidden" name="amount[]" value="{{ $item->amount }}" class="amount">
                        <input type="hidden" name="productvar_id[]" value="{{ $item->productvar_id }}" class="prodvar-id">
                        <input type="hidden" name="transf_item_id[]" value="{{ $item->transf_item_id }}" class="transf-item-id">
                    </tr>
                @endforeach
            @endif
            @if (@$stock_transfer)
                @foreach ($stock_transfer->items as $i => $item)
                    @php
                        $qty_accum = round($item->rcv_items->sum('qty_rcv'));
                        if ($qty_accum > 0) {
                            $qty_rem = round($item->qty_transf) - $qty_accum;
                        } else {
                            $qty_accum = round($item->qty_transf);
                            $qty_rem = round($item->qty_transf);
                        }        
                    @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><span class="name">{{ @$item->productvar->name }}</span></td> 
                        <td><span class="unit">{{ @$item->productvar->product->unit->code }}</span></td>                
                        <td><span class="qty-transf">{{ +$item->qty_transf }}</span></td>
                        <td><span class="qty-rem">{{ $qty_rem }}</span></td>
                        <td><input type="text"  name="qty_rcv[]" class="form-control qty-rcv" autocomplete="off"></td>
                        <input type="hidden" value="{{ $qty_accum }}" class="qty-accum">
                        <input type="hidden" name="qty_rem[]" value="{{ $qty_rem }}" class="qty-rem-inp">
                        <input type="hidden" name="qty_transf[]" value="{{ +$item->qty_transf }}" class="qty-transf-inp">
                        <input type="hidden" name="cost[]" value="{{ $item->cost }}" class="cost">
                        <input type="hidden" name="amount[]" value="{{ $item->amount }}" class="amount">
                        <input type="hidden" name="productvar_id[]" value="{{ $item->productvar_id }}" class="prodvar-id">
                        <input type="hidden" name="transf_item_id[]" value="{{ $item->id }}" class="transf-item-id">
                    </tr>
                @endforeach
            @endif
        </tbody>  
    </table>
</div>
{{ Form::hidden('stock_transfer_id', request('stock_transfer_id')) }}
{{ Form::hidden('total', null, ['id' => 'total']) }}

@section('extra-scripts')
@include('focus.stock_rcvs.form_js')
@endsection