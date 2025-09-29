<div class="form-group row">
    <input type="hidden" value="0" id="credit">
    <input type="hidden" value="0" id="total_aging">
    <input type="hidden" value="0" id="outstanding_balance">
    <div class="col-4">
        <label for="supplier">Supplier</label>
        <select id="supplier" name="supplier_id" class="form-control" data-placeholder="Choose Supplier" required>
            <option value=""></option>
            @foreach ($suppliers as $row)
                <option value="{{ $row->id }}" 
                    currencyId="{{ $row->currency_id }}"
                    currencyCode="{{ @$row->currency->code }}"
                    currencyRate="{{ @$row->currency->rate }}"
                    {{ @$goodsreceivenote && $goodsreceivenote->supplier_id == $row->id? 'selected' : '' }}
                >
                    {{ $row->name . ' : ' . $row->email }} 
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="col-2">
        <label for="tid" class="caption">#Serial</label>
        {{ Form::text('tid', @$goodsreceivenote ? $goodsreceivenote->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>
    <div class="col-2">
        <label for="date" class="caption">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div>
    <div class="col-md-2">            
        <label for="currency_code">Currency Rate</label>
        <div class="row no-gutters">
            <div class="col-md-6"> 
                <select name="currency_id" id="currency" class="custom-select" required>
                    @if (@$goodsreceivenote->currency)
                        <option value="{{ $goodsreceivenote->currency_id }}" rate="{{ +$goodsreceivenote->fx_curr_rate }}" selected>
                            {{ $goodsreceivenote->currency->code }}
                        </option>
                    @endif
                </select>
            </div>
            <div class="col-md-6">
                {{ Form::text('fx_curr_rate', null, ['class' => 'form-control', 'id' => 'fx_curr_rate', 'required' => 'required', 'readonly' => 'readonly']) }}
            </div>
        </div>
    </div> 
    <div class="col-2">
        <label for="tax" class="caption">TAX %</label>
        <select name="tax_rate" id="tax_rate" class="custom-select">
            @foreach ($additionals as $row)
            <option value="{{ +$row->value }}" {{ @$goodsreceivenote && $goodsreceivenote->tax_rate == +$row->value? 'selected' : '' }}>
                {{ $row->name }} 
            </option>
        @endforeach 
        </select>
    </div>
</div>

<div class="form-group row">
    <div class="col-md-4">
        <label for="purchaseorder" class="caption">Supplier Order</label>
        <select name="purchaseorder_id" id="purchaseorder" class="form-control" data-placeholder="Choose Order">
            <option value=""></option>
            @isset($goodsreceivenote)
                <option value="{{ $goodsreceivenote->purchaseorder_id }}" selected>
                    {{ @$goodsreceivenote->purchaseorder->note }}
                </option>
            @endisset
        </select>
    </div>
    <div class="col-2">
        <label for="dnote" class="caption">DNote No.</label>
        {{ Form::text('dnote', null, ['class' => 'form-control', 'id' => 'dnote', 'required']) }}
    </div>
    <div class="col-2">
        <label for="jobcard" class="caption">JobCard No. <span>(optional)</span></label>
        {{ Form::text('jobcard_no', null, ['class' => 'form-control', 'id' => 'jobcard']) }}
    </div>
    
    <div class="col-2">
        <label for="receive_status" class="caption">Receipt Status</label>
        <select name="invoice_status" id="invoice_status" class="custom-select">
            @foreach (['without_invoice', 'with_invoice'] as $val)
            <option value="{{ $val }}">{{ ucfirst(str_replace('_', ' ', $val)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="invoice" class="caption">CU Invoice No.</label>
        {{ Form::text('invoice_no', null, ['class' => 'form-control', 'id' => 'invoice_no', 'disabled']) }}
    </div>
    
</div>

<div class="form-group row">
    <div class="col-2">
        <label for="date" class="caption">Invoice Date</label>
        {{ Form::text('invoice_date', null, ['class' => 'form-control datepicker', 'id' => 'invoice_date', 'disabled']) }}
    </div>
    <div class="col-10">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div>
</div>

<div class="table-responsive" style="max-height: 80vh">
    <table class="table text-center" id="productTbl" width="100%">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="5%">#</th>
                <th width="20%">Product Description</th>
                <th>Project</th>
                <th>Warehouse</th>
                <th>Ledger Account</th>
                <th>UoM</th>
                <th>Qty Ordered</th>
                <th>Qty Received</th>
                <th>Qty Due</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @isset($goodsreceivenote)
                @php $grn = $goodsreceivenote @endphp
                @foreach ($grn->items as $i => $item)
                    @php
                        $po_item = $item->purchaseorder_item;
                        if (!$po_item) continue;
                        $qty_due = $po_item->qty - $po_item->qty_received;
                        $project_name = $item->project? gen4tid('Prj-', $item->project->tid) . ' - ' . $item->project->name : '';
                        $stock_type = $item->productvariation->product->stock_type ?? '';
                    @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td class="text-left">
                            {{ $po_item->description }}
                            <input type="hidden" class="product_code" name="product_code[]" value="{{$po_item->product_code}}">
                        </td>
                        <td> 
                            <input type="text" class="form-control projectstock" value="{{ $project_name }}" id="projectstocktext-{{$i}}" placeholder="Search Project By Name">
                            <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="{{$item->itemproject_id}}" id="projectstockval-{{$i}}">
                        </td>
                        <td>
                            <select name="warehouse_id[]" class="form-control warehouse {{$stock_type == 'service' ? 'd-none' : ''}}" id="warehouseid-{{$i}}">
                                <option value="0">--- Select Warehouse --</option>
                                @foreach ($warehouses as $row)
                                    <option value="{{ $row->id }}" {{ $row->id == $item->warehouse_id? 'selected' : 0 }}>
                                        {{ $row->title }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" class="form-control accountname" name="name[]" value="{{ @$item->account->holder }}" id="accountname-{{$i}}" placeholder="Enter Ledger"></td>
                        <td>{{ $po_item->uom }}</td>
                        <td class="qty_ordered">{{ +$po_item->qty }}</td>
                        <td class="qty_received">{{ floatval($po_item->grn_items->sum('qty')) }}</td>                        
                        <td class="qty_due">{{ $qty_due > 0? +$qty_due : 0 }}</td>
                        <td><input name="qty[]" value="{{ +$item->qty }}" origin="{{ +$item->qty }}" id="qty" class="form-control qty"></td>
                        <input type="hidden" id="expitemid-{{$i}}" value="{{$item->account_id}}" class="expitemid" name="account_id[]">
                        <input type="hidden" name="rate[]" value="{{ +$po_item->rate }}" class="rate">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                    </tr>
                @endforeach
            @endisset
        </tbody>
    </table>
</div>

@if(access()->allow('grn-totals'))
    <div class="row">
        <div class="col-2 ml-auto">
            <label for="subtotal">Subtotal</label>
            {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
        </div>
    </div>
    <div class="row">
        <div class="col-2 ml-auto">
            <label for="tax">Tax</label>
            {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
        </div>
    </div>
    <div class="row">
        <div class="col-2 ml-auto">
            <label for="total">Total</label>
            {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
        </div>
    </div>
@else
    <div class="row d-none">
        <div class="col-2 ml-auto">
            <label for="subtotal">Subtotal</label>
            {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly', 'style'=>"display: none;"]) }}
        </div>
    </div>
    <div class="row d-none">
        <div class="col-2 ml-auto">
            <label for="tax">Tax</label>
            {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
        </div>
    </div>
    <div class="row d-none">
        <div class="col-2 ml-auto">
            <label for="total">Total</label>
            {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
        </div>
    </div>
@endif

@section('after-scripts')
@include('focus.goodsreceivenotes.form_js')
@endsection