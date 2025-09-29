<div class="tab-pane fade in" id="active2" aria-labelledby="link-tab2" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="expTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-danger white">
                <th width="10%">#</th>
                <th width="30%" class="text-center">Ledger Name</th>
                <th width="25%" class="text-center">{{trans('general.quantity')}}</th>
                <th width="25%" class="text-center">UoM</th>
                {{-- <th width="10%" class="text-center">{{trans('general.rate')}}</th> --}}
                {{-- <th width="10%" class="text-center">{{trans('general.tax_p')}}</th> --}}
                {{-- <th width="8%" class="text-center">Tax</th> --}}
                {{-- <th width="10%" class="text-center">{{trans('general.amount')}}</th> --}}
                <th width="10%" class="text-center">{{trans('general.action')}}</th>
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td><input type="text" class="form-control" value="1" id="expenseinc-0" disabled></td>
                <td><input type="text" class="form-control accountname" name="name[]" placeholder="Enter Ledger" id="accountname-0"></td>
                <td><input type="text" class="form-control exp_qty" name="qty[]" id="expqty-0" value="1"></td>
                <td><input type="text" class="form-control uom" name="uom[]" id="uom-0"></td>                    
                {{-- <td><input type="text" class="form-control exp_price" name="rate[]" id="expprice-0"></td> --}}
                {{-- <td>
                    <select class="form-control exp_vat" name="itemtax[]" id="expvat-0">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }}
                            </option>
                        @endforeach                                                    
                    </select>
                </td>   --}}
                {{-- <td class="text-center"><span class="exp_tax" id="exptax-0">0</span></td> --}}
                {{-- <td>{{config('currency.symbol')}} <b><span class="exp_amount" id="expamount-0">0</span></b></td> --}}
                <td><button type="button" class="btn btn-danger remove d-none"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" id="expitemid-0" name="item_id[]">

                <input type="hidden" name="type[]" value="Expense">
                <input type="hidden" name="project_id[]" value="0">
                <input type="hidden" name="project_milestone_id[]" value="0">
                <input type="hidden" name="purchase_requisition_item_id[]" value="0">
                <input type="hidden" name="id[]" value="0">
            </tr>
            <tr>
                <td colspan="3">
                    <textarea id="expdescr-0" class="form-control descr" name="description[]" placeholder="Additional Details or Requirements..."></textarea>
                </td>

            </tr>
            <!-- end layout -->

            <!-- fetched rows -->
            @isset ($rfq)
                @php ($i = 0)
                @foreach ($rfq->items as $item)
                    @if ($item->type === 'EXPENSE')
                        <tr>
                            <td><input type="text" class="form-control" value="{{$i+1}}" id="expenseinc-{{$i}}" disabled></td>
                            <td><input type="text" class="form-control accountname" name="name[]" value="{{ @$item->account->holder }}" placeholder="Enter Ledger" id="accountname-{{$i}}"></td>
                            <td><input type="text" class="form-control exp_qty" name="qty[]" value="{{ number_format($item->quantity, 1) }}" id="expqty-{{$i}}"></td>
                            <td><input type="text" class="form-control uom" name="uom[]" value="{{ $item->uom }}" id="uom-0"></td>                    


                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                            <input type="hidden" id="expitemid-{{$i}}" name="item_id[]"value="{{ $item->expense_account_id }}" >

                            <input type="hidden" name="type[]" value="Expense">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        <tr>
                            <td colspan="3">
                                <textarea id="expdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Additional Details or Requirements...">{{ $item->description }}</textarea>
                            </td>

                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
            <!-- end fetched rows -->

            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addexp">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="6"></td>
            </tr>


        </tbody>
    </table>
</div>
