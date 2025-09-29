<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-1">
                <div class="col-md-6 col-12 select-col">
                    <label for="customer">Customer</label>
                    <select name="customer_id" id="customer" class="form-control" data-placeholder="Search Customer" autocomplete="off">
                        <option value=""></option>
                        @foreach ($customers as $row)
                            <option value="{{ $row->id }}" {{ @$sale_return->customer_id == $row->id? 'selected' : ''}}>
                                {{ $row->company ?: $row->name }}
                            </option>    
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-12">
                    <label for="tid">#Serial No.</label>
                    {{ Form::text('tid', $tid, ['class' => 'form-control', 'id' => 'tid', 'readonly' => 'readonly']) }}
                </div>
                <div class="col-md-2 col-12">
                    <label for="date">Date</label>
                    {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
                </div>
                <div class="col-md-2 col-12">
                    <label for="tid">Reference</label>
                    <select name="reference" id="ref" class="custom-select">
                        @foreach (['quote', 'proforma', 'invoice'] as $item)
                            <option value="{{$item}}" {{ @$sale_return->reference == 'item'? 'selected' : '' }}>{{ ucfirst($item) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6 col-12 quote-col">
                    <label for="invoice">Quote/Proforma Invoice</label>
                    <select name="quote_id" id="quote" class="form-control" data-placeholder="Search Quote" autocomplete="off">
                        <option value=""></option>
                        @if (isset($sale_return->quote))
                            <option value="{{ $sale_return->quote_id }}" selected>
                                {{ gen4tid($sale_return->quote->bank_id? 'PI-' : 'QT-', $sale_return->quote->tid) . ' ' . $sale_return->quote->notes }}
                            </option>
                        @endif
                    </select>
                </div>
                <div class="col-md-6 col-12 invoice-col d-none">
                    <label for="invoice">Invoice</label>
                    <select name="invoice_id" id="invoice" class="form-control" data-placeholder="Search Invoice" autocomplete="off">
                        <option value=""></option>
                        @if (isset($sale_return->invoice))
                            <option value="{{ $sale_return->invoice_id }}" selected>
                                {{ gen4tid('INV-', $sale_return->invoice->tid) . ' ' . $sale_return->invoice->notes }}
                            </option>
                        @endif
                    </select>
                </div>

                <div class="col-md-6 col-12">
                    <label for="note">Note</label>
                    {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required' => 'required']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <div class="card-body">
            <!-- products table -->
            <div class="table-responsive" style="max-height: 80vh">
                <table id="productsTbl" class="table table-sm tfr my_stripe_single text-center">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th>#</th>
                            <th class="text-left">Reference Name</th>
                            <th width="25%" class="text-left">Stock Item Name</th>
                            <th>Item Code</th>
                            <th>UoM</th>
                            <th>Qty On-Hand</th>
                            <th>New Qty</th>
                            <th>Return Qty</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (@$sale_return)
                            @foreach ($sale_return->items as $i => $item)
                                <tr>
                                    <td><span class="serial">{{ $i+1 }}</span></td>
                                    <td class="text-left"><span class="ref-name"></span></td>
                                    <td class="text-left"><span class="name">{{ @$item->productvar->name }}</span></td>
                                    <td><span class="product-code">{{ @$item->productvar->code }}</span></td>
                                    <td><span class="unit">{{ @$item->productvar->product->unit->code }}</span></td>
                                    <td><span class="qty-onhand">{{ $item->productvar? +$item->productvar->qty : '' }}</span></td>
                                    <td><span class="new-qty">{{ $item->productvar? +$item->productvar->qty : ''  }}</span></td>
                                    <td><input type="text" name="return_qty[]" value="{{ +$item->return_qty }}" origin-value="{{ +$item->return_qty }}" class="form-control return-qty" autocomplete="off"></td>
                                    <td>
                                        <select name="warehouse_id[]" class="form-control custom-select">
                                            <option value="">-- location --</option>
                                            @foreach ($warehouses as $wh)
                                                <option value="{{$wh->id}}" {{ $item->warehouse_id == $wh->id? 'selected' : '' }}>{{ $wh->title }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="row no-gutters">
                                            <div class="col-md-9">
                                                <select name="status[]" class="form-control custom-select">
                                                    <option value="">-- status --</option>
                                                    @foreach (['new', 'used', 'damaged', 'defective'] as $status)
                                                        <option value="{{$status}}" {{ $item->status == $status? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <input type="hidden" name="qty_onhand[]" value="{{ $item->productvar? +$item->productvar->qty : '' }}" class="qty-onhand-inp">
                                    <input type="hidden" name="new_qty[]" value="{{ $item->productvar? +$item->productvar->qty : '' }}" class="new-qty-inp">
                                    <input type="hidden" name="cost[]" value="{{ +$item->cost }}" class="cost">
                                    <input type="hidden" name="amount[]" value="{{ +$item->amount }}" class="amount">
                                    <input type="hidden" name="productvar_id[]" value="{{ $item->productvar_id }}" class="prodvar-id">
                                    <input type="hidden" name="verified_item_id[]" value="{{ $item->verified_item_id }}" class="verified-item-id">
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td><span class="serial"></span></td>
                                <td class="text-left"><span class="ref-name"></span></td>
                                <td class="text-left"><span class="name"></span></td>
                                <td><span class="product-code"></span></td>
                                <td><span class="unit"></span></td>
                                <td><span class="qty-onhand"></span></td>
                                <td><span class="new-qty"></span></td>
                                <td><input type="text" name="return_qty[]" class="form-control return-qty" autocomplete="off"></td>
                                <td>
                                    <select name="warehouse_id[]" class="form-control custom-select">
                                        <option value="">-- location --</option>
                                        @foreach ($warehouses as $item)
                                            <option value="{{$item->id}}">{{ $item->title }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="status[]" class="form-control custom-select">
                                        <option value="">-- status --</option>
                                        @foreach (['new', 'used', 'damaged', 'defective'] as $item)
                                            <option value="{{$item}}">{{ ucfirst($item) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <input type="hidden" name="qty_onhand[]" class="qty-onhand-inp">
                                <input type="hidden" name="new_qty[]" class="new-qty-inp">
                                <input type="hidden" name="cost[]" class="cost">
                                <input type="hidden" name="amount[]" class="amount">
                                <input type="hidden" name="productvar_id[]" class="prodvar-id">
                                <input type="hidden" name="verified_item_id[]" class="verified-item-id">
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>   
            <div class="form-group mt-2">
                <div class="col-2 ml-auto">
                    <label for="total">Return Stock Total</label>
                    {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
                </div>
            </div>  
            <div class="edit-form-btn row">
                {{ link_to_route('biller.sale_returns.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
            </div>              
        </div>
    </div>
</div>

@section('extra-scripts')
@include('focus.sale_returns.form_js')
@endsection
