<div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="stockTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white ">
                <th width="10%">#</th>
                <th width="35%" class="text-center">{{trans('general.item_name')}}</th>
                <th width="25%" class="text-center">{{trans('general.quantity')}}</th>
                <th width="20%" class="text-center">UoM</th>
                {{-- <th width="10%" class="text-center">{{trans('general.rate')}}</th>
                <th width="10%" class="text-center">{{trans('general.tax_p')}}</th>
                <th width="10%" class="text-center">Tax</th>
                <th width="12%" class="text-center">{{trans('general.amount')}}</th> --}}
                <th width="10%" class="text-center">Action</th>                   
            </tr>
        </thead>
        <tbody>

        @php

            $loadedItems = [];

            if (@$budgetItems) $loadedItems = @$budgetItems;
            else if(@$purchaseRequestItems) $loadedItems = @$purchaseRequestItems;

        @endphp

        @if(empty($loadedItems))

            <!-- layout -->
            <tr>
                <td><input type="text" class="form-control increment" value="1" id="increment-0" disabled></td>
                <td><input type="text" class="form-control stockname" name="name[]" placeholder="Product Name" id='stockname-0'></td>
                <td><input type="text" class="form-control qty" name="qty[]" id="qty-0" value="1"></td>
                <td>
                    <select name="uom[]" id="uom-0" class="form-control uom">
                    </select>
                </td>
                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" id="stockitemid-0" name="item_id[]">

                <!--<input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="0">-->
                <input type="hidden" name="type[]" value="Stock">
                <input type="hidden" name="project_id[]" value="0">
                <input type="hidden" name="project_milestone_id[]" value="0">
                <input type="hidden" name="purchase_requisition_item_id[]" value="0">
                <input type="hidden" name="id[]" value="0">
            </tr>
            <tr>
                <td colspan=2>
                    <textarea id="stockdescr-0" class="form-control descr" name="description[]" placeholder="Additional Product Specifications"></textarea>
                </td>
                <td colspan="6"></td>
            </tr>
            <!-- end layout -->

        @endif

            <!-- fetched rows -->
            @isset ($rfq)
                @php ($i = 0)
                @foreach ($rfq->items as $item)
                    @if ($item->type === 'STOCK')
                        <tr>
                            <td><input type="text" class="form-control increment" value="{{$i+1}}" id="increment-0" disabled></td>
                            <td><input type="text" class="form-control stockname" name="name[]" value="{{ $item->product->name }}" placeholder="Product Name" id='stockname-{{$i}}'></td>
                            <td><input type="text" class="form-control qty" name="qty[]" value="{{ number_format($item->quantity, 1) }}" id="qty-{{$i}}"></td>
                            <td>
                                <select name="uom[]" id="uom-{{ $i }}" class="form-control uom">
                                    <option value="{{ $item->uom }}" selected>{{ $item->uom }}</option>
                                </select>
                            </td>
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                            <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->product_id }}">

                            <!--<input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="0">-->
                            <input type="hidden" name="type[]" value="Stock">
                            <input type="hidden" name="project_id[]" value="{{$item->project_id}}">
                            <input type="hidden" name="project_milestone_id[]" value="{{$item->project_milestone_id}}">
                            <input type="hidden" name="purchase_requisition_item_id[]" value="{{$item->purchase_requisition_item_id}}">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        <tr>
                            <td colspan=2>
                                <textarea id="stockdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Additional Product Specifications">{{ $item->description }}</textarea>
                            </td>
                            <td colspan="6"></td>
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
            <!-- end fetched rows -->


            <!-- budget-fetched rows -->
            @isset ($loadedItems)
                @php ($i = 0)
                @foreach ($loadedItems as $item)
{{--                    @if ($item->type === 'STOCK')--}}
                        <tr>
                            <td><input type="text" class="form-control increment" value="{{$i+1}}" id="increment-0" disabled></td>
                            <td><input type="text" class="form-control stockname" name="name[]" value="{{ $item->product_name }}" placeholder="Product Name" id='stockname-{{$i}}'></td>
                            <td><input type="text" class="form-control qty" name="qty[]" value="{{ @$budgetItems ? number_format($item->new_qty, 1) : number_format($item->qty, 1) }}" id="qty-{{$i}}"></td>
                            <td>
                                <select name="uom[]" id="uom-{{ $i }}" class="form-control uom">
                                    @if($budgetItems)
                                        <option value="{{ $item->unit }}" selected>{{ $item->unit }}</option>
                                    @elseif($purchaseRequestItems)
                                        <option value="{{ $item->unit->title }}" selected>{{ $item->unit->title }}</option>
                                    @endif
                                </select>
                            </td>
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                            <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->product_id }}">

                            <!--<input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="0">-->
                            <input type="hidden" name="type[]" value="Stock">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        <tr>
                            <td colspan=2>
                                <textarea id="stockdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Additional Product Specifications">{{ $item->product_name }}</textarea>
                            </td>
                            <td colspan="6"></td>
                        </tr>
                        @php ($i++)
{{--                    @endif--}}
                @endforeach
            @endisset
            <!-- end budget-fetched rows -->

            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="7"></td>
            </tr>


        </tbody>
    </table>
</div>
