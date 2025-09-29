@php use App\Models\project\ProjectMileStone; @endphp
<div class="tab-pane fade in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
    <div class="table-responsive">
        <table class="table table-borderless tfr my_stripe" id="stockTbl">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white text-center">
                    <th width="8%">#</th>
                    <th width="35%">{{trans('general.item_name')}}</th>
                    <th width="10%">{{trans('general.quantity')}}</th>
                    <th width="10%">UoM</th>
                    <th width="15%">{{trans('general.rate')}}</th>
                    <th width="10%">VAT %</th>
                    <th width="10%">VAT</th>
                    <th width="15%">{{trans('general.amount')}}</th>
                    <th>Action</th>                   
                </tr>
            </thead>
            <tbody>
                <!-- layout -->
                <tr>
                    <td><input type="text" class="form-control increment" value="1" id="increment-0" disabled></td>
                    <td><input type="text" class="form-control stockname" name="name[]" placeholder="Product Name" id='stockname-0' autocomplete="off"></td>
                    <td><input type="text" class="form-control qty" name="qty[]" id="qty-0" value="1"></td>  
                    <td><select name="uom[]" id="uom-0" class="form-control uom custom-select"></select></td>  
                    <td><input type="text" class="form-control price" name="rate[]" id="price-0"></td>
                    <td>
                        <select class="form-control rowtax" name="itemtax[]" id="rowtax-0">
                            <option value="">-- VAT --</option>
                            @foreach ($additionals as $tax)
                                <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                    {{ $tax->name }}
                                </option>
                            @endforeach                                                    
                        </select>
                    </td>
                    <td class="text-center"><input type="text" class="form-control taxable" value="0" readonly></td>
                    <td class="text-center"><b><span class='amount' id="result-0">0</span></b></td>              
                    <td><button type="button" class="btn btn-danger remove d-none"><i class="fa fa-minus-square"></i></button></td>
                    <input type="hidden" id="stockitemid-0" name="item_id[]">
                    <input type="hidden" class="stocktaxr" name="taxrate[]">
                    <input type="hidden" class="stockamountr" name="amount[]">
                    <input type="hidden" name="type[]" value="Stock">
                    <input type="hidden" name="id[]" value="0">
                    <input type="hidden" class="supplier_product_id" name="supplier_product_id[]" id="supplier_product_id-0">
                </tr>
                <tr>
                    <td colspan="3">
                        <textarea id="stockdescr-0" class="form-control descr" name="description[]" rows="1" placeholder="Product Description"></textarea>
                        <input type="checkbox" value="0" class="cp_check" id="cp_check-0"><span>Copy PRJ / Non PRJ</span>
                    </td>
                    <td>
                        <select name="warehouse_id[]" class="form-control warehouse custom-select" id="warehouseid">
                            <option value="">Location</option>
                            @foreach ($warehouses as $row)
                                <option value="{{ $row->id }}">{{ $row->title }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td colspan="5">
                        <div class="row">
                            <div class="col-7">
                                <input type="text" class="form-control projectstock" id="projectstocktext-0" placeholder="Search Project By Name">
                                <input type="hidden" name="itemproject_id[]" id="projectstockval-0">
                            </div>
                            <div class="col-5">
                                <select id="stock-budgetline-0" name="budget_line_id[]" class="form-control custom-select stock-budgetline">
                                    <option value="">-- Select a Budget Line --</option>
                                </select>
                                <input type="hidden" class="stock_budget_line_id" name="budget_line_id[]" id="stock_budget_line_id-0" disabled>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-6">
                                <select id="stock-class-budget-0" name="purchase_class_budget[]" class="form-control stock-class-budget round item-pcb" data-placeholder="Search Non-Project Class">
                                    <option value=""></option>
                                    @foreach ($purchase_classes as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="stock_purchase_class_budget" name="purchase_class_budget[]" id="stock_purchase_class_budget-0" disabled>
                            </div>
                            <div class="col-6">
                                <select id="stock-classlist-id-0" name="item_classlist_id[]" class="form-control stock-classlist round item-pcl" data-placeholder="Search Class/Sub-class/Branch/Department">
                                    <option value=""></option>
                                    @foreach ($classlists as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="stock_classlist" name="item_classlist_id[]" id="stock_classlist-0" disabled>
                            </div>
                        </div>
                        <input type="hidden" name="import_request_id[]" id="">
                    </td>
                </tr>
                <!-- end layout -->
                <!-- fetched rows -->
                @isset ($purchase)
                    @php $i = 0 @endphp
                    @foreach ($purchase->products as $item)
                        @if ($item->type == 'Stock')
                            <tr>
                                <td><input type="text" class="form-control increment" value="{{$i+1}}" id="increment-{{$i}}" disabled></td>
                                <td><input type="text" class="form-control stockname" name="name[]" value="{{ $item->description }}" placeholder="Product Name" id='stockname-{{$i}}'></td>
                                <td><input type="text" class="form-control qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="qty-{{$i}}"></td>
                                <td>
                                    <select name="uom[]" id="uom-{{ $i }}" class="form-control uom custom-select">
                                        <option value="{{ $item->uom }}">{{ $item->uom }}</option>
                                    </select>
                                </td>
                                <td><input type="text" class="form-control price" name="rate[]" value="{{ numberFormat($item->rate) }}" id="price-{{$i}}"></td>
                                <td>
                                    <select class="form-control rowtax" name="itemtax[]" id="rowtax-{{$i}}">
                                        <option value="">-- VAT --</option>
                                        @foreach ($additionals as $tax)
                                            <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->itemtax ? 'selected' : ''}}>
                                                {{ $tax->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center"><input type="text" class="form-control taxable" value="{{ (float) $item->taxrate }}" readonly></td>
                                <td class="text-center"><b><span class='amount' id="result-{{$i}}">{{ (float) $item->amount }}</span></b></td>
                                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square"></i></button></td>
                                <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                                <input type="hidden" class="stocktaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                                <input type="hidden" class="stockamountr" name="amount[]" value="{{ (float) $item->amount }}">
                                <input type="hidden" name="type[]" value="Stock">
                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                <input type="hidden" name="import_request_id[]" id="">
                                <input type="hidden" class="supplier_product_id" name="supplier_product_id[]" value="{{$item->supplier_product_id}}" id="supplier_product_id-{{$i}}">
                            </tr>
                            <tr>
                                <td colspan=3>
                                    <textarea id="stockdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Product Description">{{ $item->description }}</textarea>
                                    <input type="checkbox" value="0" class="cp_check" id="cp_check-{{ $i }}"><span>Copy PRJ / Non PRJ</span>
                                </td>
                                <td>
                                    <select name="warehouse_id[]" class="form-control warehouse custom-select" id="warehouseid-{{$i}}">
                                        <option value="">Location</option>
                                        @foreach ($warehouses as $row)
                                            <option value="{{ $row->id }}" {{ $row->id == $item->warehouse_id ? 'selected' : '' }}>
                                                {{ $row->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td colspan="6">
                                    <div class="row">
                                        <div class="col-7">
                                            <input type="text" class="form-control projectstock" value="{{ $item->project ? $item->project->name : '' }}" id="projectstocktext-{{$i}}" placeholder="Search Project By Name">
                                            <input type="hidden" name="itemproject_id[]" value="{{ $item->itemproject_id }}" id="projectstockval-{{$i}}">
                                        </div>
                                        <div class="col-5">
                                            @php $budgetLines = ProjectMilestone::where('project_id', $item->itemproject_id)->select('name', 'id')->get() @endphp
                                            <select id="stock-budgetline-{{$i}}" name="budget_line_id[]" class="form-control custom-select stock-budgetline" data-placeholder="Select a Budget Line">
                                                <option value="">-- Select a Budget Line --</option>
                                                @foreach($budgetLines as $bL)
                                                    <option value="{{$bL->id}}" {{ $bL->id === $item->budget_line_id? 'selected' : '' }}>{{$bL->name}}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" class="stock_budget_line_id" value="{{ $item->budget_line_id }}" name="budget_line_id[]" id="stock_budget_line_id-{{ $i }}" disabled>
                                        </div>
                                    </div>
                                    <div class="row mt-1">
                                        <div class="col-6">
                                            <select id="stock-class-budget-{{ $i }}" name="purchase_class_budget[]" class="form-control stock-class-budget round pc-select" data-placeholder="Search Non-Project Class">
                                                <option value=""></option>
                                                @foreach ($purchase_classes as $row)
                                                    <option value="{{ $row->id }}" {{ @$item->purchaseClassBudget->purchase_class_id == $row->id? 'selected' : '' }}>{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" class="stock_purchase_class_budget" value="{{ @$item->purchaseClassBudget->purchase_class_id }}" name="purchase_class_budget[]" id="stock_purchase_class_budget-{{ $i }}" disabled>
                                        </div>
                                        <div class="col-6">
                                            <select id="stock-classlist-id-{{ $i }}" name="item_classlist_id[]" class="form-control round pc-select stock-classlist" data-placeholder="Search Class/Sub-class/Branch/Department">
                                                <option value=""></option>
                                                @foreach ($classlists as $row)
                                                    <option value="{{ $row->id }}" {{ $item->classlist_id == $row->id? 'selected' : '' }}>{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" class="stock_classlist" value="{{ $item->classlist_id }}" name="item_classlist_id[]" id="stock_classlist-{{ $i }}" disabled>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @php ($i++)
                        @endif
                    @endforeach
                @endisset
                <!-- end fetched rows -->
                <tr class="bg-white">
                    <td colspan="2">
                        <button type="button" class="btn btn-success btn-sm" aria-label="Left Align" id="addstock">
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
                    <td colspan="7" align="right">
                        <b>Inventory Total</b>
                    </td>
                    <td align="left" colspan="2">
                        <input type="text" class="form-control" name="stock_grandttl" value="0.00" id="stock_grandttl" readonly>
                        <input type="hidden" name="stock_subttl" value="0.00" id="stock_subttl">
                        <input type="hidden" name="stock_tax" value="0.00" id="stock_tax">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
