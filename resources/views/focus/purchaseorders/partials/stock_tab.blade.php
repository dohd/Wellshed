<div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="stockTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white text-center">
                <th width="10%">#</th>
                <th width="35%">{{trans('general.item_name')}}</th>
                <th width="7%">{{trans('general.quantity')}}</th>
                <th width="7%">UoM</th>
                <th width="10%">{{trans('general.rate')}}</th>
                <th width="10%">{{trans('general.tax_p')}}</th>
                <th width="10%">Tax</th>
                <th width="12%">{{trans('general.amount')}}</th>
                <th width="5%" class="pr-2">Action</th>                   
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td><input type="text" class="form-control increment" value="1" id="increment-0" disabled></td>
                <td>
                    <input type="text" class="form-control stockname" name="name[]" placeholder="Product Name" id='stockname-0'>
                    <input type="hidden" id="stockitemid-0" name="item_id[]">
                </td>
                <td><input type="text" class="form-control qty" name="qty[]" id="qty-0" value="1"></td>  
                <td><select name="uom[]" id="uom-0" class="form-control uom" ></select></td> 
                <td><input type="text" class="form-control price" name="rate[]" id="price-0" readonly></td>
                <td>
                    <select class="form-control rowtax" name="itemtax[]" id="rowtax-0">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }}
                            </option>
                        @endforeach                                                    
                    </select>
                </td>
                <td><input type="text" class="form-control taxable" value="0"></td>
                <td class="text-center"><b><span class='amount' id="result-0">0</span></b></td> 
                <td><button type="button" class="btn btn-danger remove d-none"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" class="stocktaxr" name="taxrate[]">
                <input type="hidden" class="stockamountr" name="amount[]">
                <input type="hidden" name="type[]" value="Stock">
                <input type="hidden" name="id[]" value="0">
            </tr>
            <tr>
                <td colspan="2">
                    <textarea id="stockdescr-0" class="form-control descr" name="description[]" placeholder="Product Description"></textarea>
                    <input type="checkbox" name="" id="check"><span>Add Import</span><br>
                    <input type="checkbox" value="0" class="cp_check" id="cp_check-0"><span>Copy PRJ / Non PRJ</span>
                </td>
                <td><input type="text" class="form-control product_code" name="product_code[]" id="product_code-0" readonly></td>
                <td>
                    <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid-0">
                        <option value="">-- Warehouse --</option>
                        @foreach ($warehouses as $row)
                            <option value="{{ $row->id }}">{{ $row->title }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="warehouse_id[]" class="ware" id="ware-0" disabled>
                </td>
                <td colspan="5">
                    <div class="row">
                        <div class="col-6">

                            <input type="text" class="form-control projectstock" id="projectstocktext-0" placeholder="Search Project By Name">
                        </div>
                        <div class="col-6">
                            <select id="stock-budgetline-0" name="milestone_id[]" class="form-control custom-select stock-budgetline">
                                <option value="">-- Select a Budget Line --</option>
                            </select>
                        </div>
                        <input type="hidden" class="milestone_id" name="milestone_id[]" id="milestone_id-0" disabled>
                    </div>

                    <div class="mt-1">
                        <select id="stock_purchase_class-0" name="purchase_class_budget[]" class="custom-select round purchase-class" data-placeholder="Select a Non-Project Class">
                            <option value=""></option>

                            @foreach ($purchaseClasses as $pc)
                                <option value="{{ $pc->id }}">
                                    {{ $pc->name }} - {{ $pc->expenseCategory ? $pc->expenseCategory->name : '' }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" class="purchase_class_budget" name="purchase_class_budget[]" id="purchase_class_budget-0" disabled>
                    </div>
                    <div class="row div_import mt-1 d-none" id="div_import-0">
                        <div class="col-6">
                            <select id="import_request_id-0" name="import_request_id[]" class="form-control import_request_id round" data-placeholder="Search Import Request">
                                <option value=""></option>
                                @foreach ($import_requests as $row)
                                @php
                                    $name = gen4tid('IMP-', $row->tid) . ' - ' . $row->notes;
                                @endphp
                                    <option value="{{ $row->id }}" >{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" id="projectstockval-0">
                    <input type="hidden" class="prod_id" name="product_id[]" id="product_id-0">
                    <input type="hidden" class="supplier_product_id" name="supplier_product_id[]" id="supplier_product_id-0">
                </td>
                <td colspan="6"></td>
            </tr>
            <!-- end layout -->

            <!-- fetched rows -->
            @isset ($po)
                @php $i = 0 @endphp
                @foreach ($po->products as $item)
                    @if ($item->type == 'Stock')
                        <tr>
                            <td><input type="text" class="form-control increment" value="{{$i+1}}" id="increment-0" disabled></td>
                            <td>
                                <input type="text" class="form-control stockname" name="name[]" value="{{ $item->description }}" placeholder="Product Name" id='stockname-{{$i}}'>
                                <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                            </td>
                            <td><input type="text" class="form-control qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="qty-{{$i}}"></td>                    
                            <td>
                                <select name="uom[]" id="uom-{{ $i }}" class="form-control uom">
                                    <option value="{{ $item->uom }}" selected>{{ $item->uom }}</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control price" name="rate[]" value="{{ (float) $item->rate }}" id="price-{{$i}}" readonly></td>
                            <td>
                                <select class="form-control rowtax" name="itemtax[]" id="rowtax-{{$i}}">
                                    @foreach ($additionals as $tax)
                                        <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->itemtax ? 'selected' : ''}}>
                                            {{ $tax->name }}
                                        </option>
                                    @endforeach                                                    
                                </select>
                            </td>
                            <td><input type="text" class="form-control taxable" value="{{ (float) $item->taxrate }}" readonly></td>
                            <td class="text-center"><b><span class='amount' id="result-{{$i}}">{{ (float) $item->amount }}</span></b></td>              
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                            <input type="hidden" class="stocktaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                            <input type="hidden" class="stockamountr" name="amount[]" value="{{ (float) $item->amount }}">
                            <input type="hidden" name="type[]" value="Stock">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        <tr>
                            
                            <td colspan=2>
                                <textarea id="stockdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Product Description">{{ $item->description }}</textarea>
                                <input type="checkbox" name="" id="check"><span>Add Import</span><br>
                                <input type="checkbox" value="0" class="cp_check" id="cp_check-{{ $i }}"><span>Copy PRJ / Non PRJ</span>
                            </td>
                            <td><input type="text" class="form-control product_code" value="{{$item->product_code}}" name="product_code[]" id="product_code-{{$i}}" readonly></td>
                            <td>
                                @php
                                    $disabled = '';
                                    $ware = 'disabled';
                            
                                    if (!empty($item->productvariation) && 
                                        optional($item->productvariation->product)->stock_type === 'service') {
                                        $disabled = 'disabled';
                                        $ware = '';
                                    }
                                @endphp
                                <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid-{{$i}}" {{$disabled}}>
                                    <option value="">-- Warehouse --</option>
                                    @foreach ($warehouses as $row)
                                        <option value="{{ $row->id }}" {{ $row->id == $item->warehouse_id? 'selected' : '' }}>
                                            {{ $row->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="warehouse_id[]" class="ware" id="ware-{{$i}}" {{$ware}}>
                            </td>
                            <td colspan="5">
                                <div class="row">
                                    <div class="col-6">
                                        <input type="text" class="form-control projectstock" value="{{ $item->project_name }}" id="projectstocktext-{{$i}}" placeholder="Search Project By Name">
                                    </div>
                                    <div class="col-6">
                                         @php 
                                            // $budgetLines = App\Models\project\ProjectMilestone::where('project_id', $item->itemproject_id)->select('name', 'id')->get() 
                                            $budgetLines = $item->project ? $item->project->milestones : [];
                                        @endphp
                                        <select id="stock-budgetline-{{$i}}" name="milestone_id[]" class="form-control custom-select stock-budgetline">
                                            <option value="">-- Select a Budget Line --</option>
                                            @foreach($budgetLines as $row)
                                                <option value="{{$row->id}}"  {{ $row->id == $item->milestone_id? 'selected' : '' }}>{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" class="milestone_id" value="{{ $item->milestone_id }}" name="milestone_id[]" id="milestone_id-0" disabled>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <select id="stock_purchase_class-{{$i}}" name="purchase_class_budget[]" class="custom-select round purchase-class" data-placeholder="Select a Non-Project Class">
                                        <option value=""></option>

                                        @foreach ($purchaseClasses as $pc)
                                            <option value="{{ $pc->id }}"
                                                    @if($item->purchaseClassBudget)
                                                        @if($item->purchaseClassBudget->purchase_class_id == $pc->id) selected @endif
                                                    @endif
                                            >
                                                {{ $pc->name }} - {{ $pc->expenseCategory ? $pc->expenseCategory->name : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" class="purchase_class_budget" value="{{ @$item->purchaseClassBudget->purchase_class_id }}" name="purchase_class_budget[]" id="purchase_class_budget-0" disabled>

                                </div>
                                <div class="row div_import mt-1 d-none" id="div_import-0">
                                    <div class="col-6">
                                        <select id="import_request_id-0" name="import_request_id[]" class="form-control import_request_id round" data-placeholder="Search Import Request">
                                            <option value=""></option>
                                            @foreach ($import_requests as $row)
                                            
                                                <option value="{{ $row->id }}" {{$row->id == $item->import_request_id ? 'selected' : ''}}>{{ gen4tid('IMP-', $row->tid) . ' - ' . $row->notes }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="{{ $item->itemproject_id ? $item->itemproject_id : '0' }}" id="projectstockval-{{$i}}">
                                <input type="hidden" class="prod_id" name="product_id[]" id="product_id-{{$i}}" value="{{$item->product_id}}">
                                <input type="hidden" class="supplier_product_id" name="supplier_product_id[]" value="{{$item->supplier_product_id}}" id="supplier_product_id-{{$i}}">
                            </td>
                            <td colspan="6"></td>
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
            <!-- end fetched rows -->

            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="7"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="7" align="right"><b>{{trans('general.total_tax')}}</b></td>                   
                <td align="left" colspan="2"><span id="invtax" class="lightMode">0</span></td>
            </tr>
            <tr class="bg-white">
                <td colspan="7" align="right"><b>Inventory Total</b></td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="stock_grandttl" value="0.00" id="stock_grandttl" readonly>
                    <input type="hidden" name="stock_subttl" value="0.00" id="stock_subttl">
                    <input type="hidden" name="stock_tax" value="0.00" id="stock_tax">
                </td>
            </tr>
        </tbody>
    </table>
</div>
