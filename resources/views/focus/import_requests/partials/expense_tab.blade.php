<table id="expensesTbl" class="table table-hover" cellspacing="0">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th>Date</th>
            <th>Expense Reference</th>
            <th>Expense Description</th>
            <th>UoM</th>
            <th style="width: 200px;">QTY</th>
            <th style="width: 200px;">Rate</th>
            <th>Total</th>
            <th>Currency</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        
        @isset($import_request)

        @php
            // Get all expense_ids from the import request
            $existingExpenseIds = $import_request->expenses->pluck('expense_id')->toArray();
            $existing_lpo_expenses_ids = $import_request->expenses->pluck('lpo_expense_id')->toArray();

            // Filter purchase_items to exclude those already in expenses
            $new_items = $purchase_items->filter(function($item) use ($existingExpenseIds) {
                return !in_array($item->id, $existingExpenseIds);
            });
            // Filter purchase_items to exclude those already in expenses
            $new_purchase_order_items = $purchase_order_items->filter(function($item) use ($existing_lpo_expenses_ids) {
                return !in_array($item->id, $existing_lpo_expenses_ids);
            });
        @endphp

        @if (count($import_request->expenses) > 0)
            @foreach ($import_request->expenses as $i => $item)
               @if ($item->expense_id)
                <tr>
                    <td>{{@$item->expense_item->purchase ? dateFormat(@$item->expense_item->purchase->date) : ''}}</td>
                    <td>{{@$item->expense_item->purchase ? @$item->expense_item->purchase->doc_ref : ''}}</td>
                    <td>{{$item->expense_item ? $item->expense_item->description : ''}}</td>
                    <td>{{$item->uom}}</td>
                    <td><input type="text" name="exp_qty[]" value="{{+$item->exp_qty}}" class="form-control exp_qty"></td>
                    <td style="width: 200px;"><input type="text" name="exp_rate[]" value="{{+$item->exp_rate}}" class="exp_rate form-control"></td>
                    <td> <span class="exp_amount">{{+$item->exp_rate*+$item->exp_qty}}</span></td>
                    <td>
                        <div class="row no-gutters">
                            <div class="col-md-6">
                                <select name="currency_id[]" id="currency-{{$i}}" class="custom-select currencys" required>
                                    <option value="">Search Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{$currency->id}}" rate="{{$currency->rate}}" {{$currency->id == $item->currency_id ? 'selected' : ''}}>{{$currency->code}}</option>
                                    @endforeach
                                </select>  
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="fx_curr_rate[]" value="{{numberFormat($item->fx_curr_rate)}}" class="form-control fx_curr_rate">
                            </div>               
                        </div>
                    </td>
                    <td><input type="checkbox" class="form-control tick"></td>
                    <input type="hidden" name="expense_id[]" class="expense_id" value="{{$item->expense_id}}">
                    <input type="hidden" name="uom[]" class="exp_uom" value="{{$item->uom}}">
                    <input type="hidden" name="lpo_expense_id[]" class="lpo_expense_id" value="">
                    <input type="hidden" name="fx_rate[]" class="fx_rate" value="{{$item->fx_rate}}">
                    <input type="hidden" name="e_id[]" class="e_id" value="{{$item->id}}">
                </tr>
               @else
                <tr>
                    <td>{{@$item->lpo_item->purchaseorder ? dateFormat(@$item->lpo_item->purchaseorder->date) : ''}}</td>
                    <td>{{@$item->lpo_item->purchaseorder ? gen4tid('PO-',@$item->lpo_item->purchaseorder->tid) : ''}}</td>
                    <td>{{$item->lpo_item ? $item->lpo_item->description : ''}}</td>
                    <td>{{$item->uom}}</td>
                    <td><input type="text" name="exp_qty[]" value="{{+$item->exp_qty}}" class="form-control exp_qty"></td>
                    <td style="width: 200px;"><input type="text" name="exp_rate[]" value="{{+$item->exp_rate}}" class="exp_rate form-control"></td>
                    <td> <span class="exp_amount">{{+$item->exp_rate*+$item->exp_qty}}</span></td>
                    <td>
                        <div class="row no-gutters">
                            <div class="col-md-6">
                                <select name="currency_id[]" id="currency-{{$i}}" class="custom-select currencys" required>
                                    <option value="">Search Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{$currency->id}}" rate="{{$currency->rate}}" {{$currency->id == $item->currency_id ? 'selected' : ''}}>{{$currency->code}}</option>
                                    @endforeach
                                </select>  
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="fx_curr_rate[]" value="{{numberFormat($item->fx_curr_rate)}}" class="form-control fx_curr_rate">
                            </div>               
                        </div>
                    </td>
                    <td><input type="checkbox" class="form-control tick"></td>
                    <input type="hidden" name="expense_id[]" class="expense_id" value="{{$item->expense_id}}">
                    <input type="hidden" name="lpo_expense_id[]" class="lpo_expense_id" value="{{$item->lpo_expense_id}}">
                    <input type="hidden" name="uom[]" class="exp_uom" value="{{$item->uom}}">
                    <input type="hidden" name="lpo_expense_id[]" class="lpo_expense_id" value="">
                    <input type="hidden" name="fx_rate[]" class="fx_rate" value="{{$item->fx_rate}}">
                    <input type="hidden" name="e_id[]" class="e_id" value="{{$item->id}}">
                </tr>
               @endif
            @endforeach

            {{-- Show purchase items not already in expenses --}}
            @foreach ($new_items as $j => $item)
                @php $index = count($import_request->expenses) + $j; @endphp
                <tr>
                    <td>{{$item->purchase ? dateFormat($item->purchase->date) : ''}}</td>
                    <td>{{$item->purchase ? $item->purchase->doc_ref : ''}}</td>
                    <td>{{$item->description}}</td>
                    <td>{{$item->uom}}</td>
                    <td><input type="text" name="exp_qty[]" value="{{+$item->qty}}" class="form-control exp_qty" disabled></td>
                    <td style="width: 200px;"><input type="text" name="exp_rate[]" value="{{+$item->rate}}" class="exp_rate form-control" disabled></td>
                    <td> <span class="exp_amount">{{+$item->rate*+$item->qty}}</span></td>
                    <td>
                        <div class="row no-gutters">
                            <div class="col-md-6">
                                <select name="currency_id[]" id="currency-{{$index}}" class="custom-select currencys" required disabled>
                                    <option value="">Search Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{$currency->id}}" rate="{{$currency->rate}}">{{$currency->code}}</option>
                                    @endforeach
                                </select>  
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="fx_curr_rate[]" class="form-control fx_curr_rate" disabled>
                            </div>               
                        </div>
                    </td>
                    <td><input type="checkbox" class="form-control tick" checked></td>
                    <input type="hidden" name="expense_id[]" class="expense_id" value="{{$item->id}}" disabled>
                    <input type="hidden" name="uom[]" class="exp_uom" value="{{$item->uom}}" disabled>
                    <input type="hidden" name="lpo_expense_id[]" class="lpo_expense_id" disabled value="">
                    <input type="hidden" name="fx_rate[]" class="fx_rate" value="0" disabled>
                    <input type="hidden" name="e_id[]" class="e_id" value="0" disabled>
                </tr>
            @endforeach
            {{-- Show purchase order items not already in expenses --}}
            @foreach ($new_purchase_order_items as $j => $item)
                @php $index = count($import_request->expenses) + $j; @endphp
                <tr>
                    <td>{{$item->purchaseorder ? dateFormat($item->purchaseorder->date) : ''}}</td>
                    <td>{{$item->purchaseorder ? gen4tid('PO-',$item->purchaseorder->tid) : ''}}</td>
                    <td>{{$item->description}}</td>
                    <td>{{$item->uom}}</td>
                    @php
                        $qty = 0;
                        if(count($item->grn_items) > 0){
                            $qty = $item->grn_items()->sum('qty');
                        }
                    @endphp
                    <td><input type="text" name="exp_qty[]" value="{{+$qty}}" class="form-control exp_qty" disabled></td>
                    <td style="width: 200px;"><input type="text" name="exp_rate[]" value="{{+$item->rate}}" class="exp_rate form-control" disabled></td>
                    <td> <span class="exp_amount">{{+$item->rate*+$item->qty}}</span></td>
                    <td>
                        <div class="row no-gutters">
                            <div class="col-md-6">
                                @php
                                    $currency_id = '';
                                    if($item->purchaseorder){
                                        $currency_id = $item->purchaseorder->supplier ? $item->purchaseorder->supplier->currency_id : '';
                                    }
                                @endphp
                                <select name="currency_id[]" id="currency-{{$index}}" class="custom-select currencys" required disabled>
                                    <option value="">Search Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{$currency->id}}" rate="{{$currency->rate}}" {{$currency->id == $currency_id ? 'selected' : ''}}>{{$currency->code}}</option>
                                    @endforeach
                                </select>  
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="fx_curr_rate[]" class="form-control fx_curr_rate" disabled>
                            </div>               
                        </div>
                    </td>
                    <td><input type="checkbox" class="form-control tick" checked></td>
                    <input type="hidden" name="expense_id[]" class="expense_id" value="" disabled>
                    <input type="hidden" name="lpo_expense_id[]" class="lpo_expense_id" value="{{$item->id}}" disabled>
                    <input type="hidden" name="uom[]" class="exp_uom" value="{{$item->uom}}" disabled>
                    <input type="hidden" name="fx_rate[]" class="fx_rate" value="0" disabled>
                    <input type="hidden" name="e_id[]" class="e_id" value="0" disabled>
                </tr>
            @endforeach

        @else
            {{-- Fallback if no expenses exist --}}
            @foreach ($purchase_items as $i => $item)
                <tr>
                    <td>{{$item->purchase ? dateFormat($item->purchase->date) : ''}}</td>
                    <td>{{$item->purchase ? $item->purchase->doc_ref : ''}}</td>
                    <td>{{$item->description}}</td>
                    <td>{{$item->uom}}</td>
                    <td><input type="text" name="exp_qty[]" value="{{+$item->qty}}" class="form-control exp_qty"></td>
                    <td style="width: 200px;"><input type="text" name="exp_rate[]" value="{{+$item->rate}}" class="exp_rate form-control"></td>
                    <td> <span class="exp_amount">{{+$item->rate*+$item->qty}}</span></td>
                    <td>
                        <div class="row no-gutters">
                            <div class="col-md-6">
                                <select name="currency_id[]" id="currency-{{$i}}" class="custom-select currencys" required>
                                    <option value="">Search Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{$currency->id}}" rate="{{$currency->rate}}">{{$currency->code}}</option>
                                    @endforeach
                                </select>  
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="fx_curr_rate[]" class="form-control fx_curr_rate">
                            </div>               
                        </div>
                    </td>
                    <td><input type="checkbox" class="form-control tick"></td>
                    <input type="hidden" name="expense_id[]" class="expense_id" value="{{$item->id}}">
                    <input type="hidden" name="lpo_expense_id[]" class="lpo_expense_id" value="">
                    <input type="hidden" name="uom[]" class="exp_uom" value="{{$item->uom}}">
                    <input type="hidden" name="fx_rate[]" class="fx_rate" value="0">
                    <input type="hidden" name="e_id[]" class="e_id" value="0">
                </tr>
            @endforeach
            @foreach ($purchase_order_items as $i => $item)
                <tr>
                    <td>{{$item->purchaseorder ? dateFormat($item->purchaseorder->date) : ''}}</td>
                    <td>{{$item->purchaseorder ? gen4tid('PO-',$item->purchaseorder->tid) : ''}}</td>
                    <td>{{$item->description}}</td>
                    <td>{{$item->uom}}</td>
                    @php
                        $qty = 0;
                        if(count($item->grn_items) > 0){
                            $qty = $item->grn_items()->sum('qty');
                        }
                    @endphp
                    <td><input type="text" name="exp_qty[]" value="{{+$qty}}" class="form-control exp_qty"></td>
                    <td style="width: 200px;"><input type="text" name="exp_rate[]" value="{{+$item->rate}}" class="exp_rate form-control"></td>
                    <td> <span class="exp_amount">{{+$item->rate*+$item->qty}}</span></td>
                    <td>
                        <div class="row no-gutters">
                            <div class="col-md-6">
                                @php
                                    $currency_id = '';
                                    if($item->purchaseorder){
                                        $currency_id = $item->purchaseorder->supplier ? $item->purchaseorder->supplier->currency_id : '';
                                    }
                                @endphp
                                <select name="currency_id[]" id="currency-{{$i}}" class="custom-select currencys" required>
                                    <option value="">Search Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{$currency->id}}" rate="{{$currency->rate}}" {{$currency->id == $currency_id ? 'selected' : ''}}>{{$currency->code}}</option>
                                    @endforeach
                                </select>  
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="fx_curr_rate[]" class="form-control fx_curr_rate">
                            </div>               
                        </div>
                    </td>
                    <td><input type="checkbox" class="form-control tick"></td>
                    <input type="hidden" name="expense_id[]" class="expense_id" value="0">
                    <input type="hidden" name="lpo_expense_id[]" class="lpo_expense_id" value="{{$item->id}}">
                    <input type="hidden" name="uom[]" class="exp_uom" value="{{$item->uom}}">
                    <input type="hidden" name="fx_rate[]" class="fx_rate" value="0">
                    <input type="hidden" name="e_id[]" class="e_id" value="0">
                </tr>
            @endforeach
        @endif

           
        @endisset
    </tbody>
</table>