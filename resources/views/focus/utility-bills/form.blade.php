<div class="form-group row">
    <div class="col-2">
        <label for="tid" class="caption">Bill No.</label>
        {{ Form::text('tid', @$utility_bill ? $utility_bill->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
    <div class="col-2">
        <label for="type">Parent Document</label>
        <select name="document_type" id="document_type" class="custom-select">
            @foreach (['goods_receive_note', 'kra_bill', 'contract', 'other'] as $val)
                <option value="{{ $val }}" {{ @$utility_bill && $utility_bill->document_type == $val? 'selected' : '' }}>
                    {{ strtoupper(str_replace('_', ' ', $val)) }}
                </option>
            @endforeach
        </select>
    </div> 
    <div class="col-2">
        <label for="reference_type">Reference Type</label>
        <select name="reference_type" id="reference_type" class="custom-select">
            @foreach (['invoice', 'receipt', 'voucher'] as $val)
                <option value="{{ $val }}" {{ @$utility_bill && $utility_bill->reference_type == $val? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div> 
    <div class="col-2">
        <label for="reference">Reference No</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference']) }}
    </div>
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div>
    <div class="col-2">
        <label for="due_date">Due Date</label>
        {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'due_date', 'required' => 'required']) }}
    </div>
</div> 

<div class="form-group row">  
    <div class="col-2">
        <label for="tax_rate">TAX %</label>
        <select name="tax_rate" id="tax_rate" class="custom-select">
            @foreach ($additionals as $row)
            <option value="{{ +$row->value }}" {{ @$utility_bill->tax_rate == +$row->value? 'selected' : '' }}>
                {{ $row->name }} 
            </option>
        @endforeach 
        </select>
    </div>  
    <div class="col-md-2">            
        <label for="currency_code">Currency Rate</label>
        <div class="row no-gutters">
            <div class="col-md-6"> 
                <select name="currency_id" id="currency" class="custom-select" required>
                    @foreach ($currencies as $row)
                        @if (@$utility_bill && $row->id == $utility_bill->currency_id)
                            <option value="{{ $row->id }}" rate="{{ +$utility_bill->fx_curr_rate ?: +$row->rate }}" selected>
                                {{ $row->code }}
                            </option>
                        @else
                            <option value="{{ $row->id }}" rate="{{ $row->rate }}" {{$row->rate == 1? 'selected' : ''}}>{{ $row->code }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                {{ Form::text('fx_curr_rate', @$utility_bill? +$utility_bill->fx_curr_rate : null, ['class' => 'form-control', 'id' => 'fx_curr_rate', 'required' => 'required', 'readonly' => 'readonly']) }}
            </div>
        </div>
    </div> 
    <div class="col-6">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>                          
</div>
<div class="form-group row">  
    <div class="col-4">
        <label for="supplier">Supplier</label>
        <select name="supplier_id" id="supplier" class="custom-select" data-placeholder="Choose Supplier">
            @foreach ($suppliers as $row)
                <option value="{{ $row->id }}" {{ @$utility_bill && $utility_bill->supplier_id == $row->id? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>                      
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="documentsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="5%">#</th>
                <th>Date</th>
                <th width="25%">Item Description</th>
                <th width="10%">Qty</th>
                <th>Rate</th>
                <th>Tax</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @isset($utility_bill)
                @foreach ($utility_bill->items as $k => $item)
                    <tr>
                        <td>{{ $k+1 }}</td>
                        <td>{{ dateFormat($item->date) }}</td>
                        <td><input type="text" name="item_note[]" value="{{ $item->note }}"  class="form-control note" readonly></td>
                        <td><input type="text" name="item_qty[]" value="{{ +$item->qty }}" class="form-control qty"></td>
                        <td><input type="text" name="item_subtotal[]" value="{{ numberFormat($item->subtotal) }}" class="form-control rate" readonly></td>
                        <td><input type="text" name="item_tax[]" value="{{ numberFormat($item->tax) }}" class="form-control tax" readonly></td>
                        <td><input type="text" name="item_total[]" value="{{ numberFormat($item->total) }}" class="form-control total" readonly></td>
                        <td><a href="#" class="btn btn-link pt-0 delete"><i class="fa fa-trash fa-2x text-danger"></i></a></td>
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                        <input type="hidden" name="item_ref_id[]" value="{{ $item->ref_id }}">
                        <input type="hidden" name="rfx_rate[]" class="rfx-rate">
                        <input type="hidden" name="rfx_subtotal[]" class="rfx-subtotal">
                        <input type="hidden" name="rfx_taxable[]" class="rfx-taxable">
                        <input type="hidden" name="rfx_tax[]" class="rfx-tax">
                        <input type="hidden" name="rfx_total[]" class="rfx-total">
                    </tr>
                @endforeach
            @endisset
        </tbody>  
    </table>
</div>

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
<div class="row mb-1">  
    <div class="col-2 ml-auto">
        <label for="total">Total</label>   
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div>                          
</div>
<input type="hidden" name="fx_subtotal" id="fx_subtotal">
<input type="hidden" name="fx_taxable" id="fx_taxable">
<input type="hidden" name="fx_tax" id="fx_tax">
<input type="hidden" name="fx_total" id="fx_total">

@section('after-scripts')
@include('focus.utility-bills.form_js')
@endsection
