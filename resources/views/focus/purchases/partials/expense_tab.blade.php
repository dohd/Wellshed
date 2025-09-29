@php use App\Models\project\ProjectMileStone; @endphp
<div class="tab-pane active" id="active2" aria-labelledby="link-tab2" role="tabpanel">
    <div class="table-responsive">
        <table class="table table-borderless tfr my_stripe" id="expTbl">
            <thead>
                <tr class="item_header bg-gradient-directional-danger white text-center">
                    <th width="5%">#</th>
                    <th width="30%">Ledger Name</th>
                    <th width="6%">{{trans('general.quantity')}}</th>
                    <th width="6%">UoM</th>
                    <th width="15%">{{trans('general.rate')}}</th>
                    <th width="10%">VAT %</th>
                    <th width="10%">VAT</th>
                    <th width="10%">{{trans('general.amount')}}</th>
                    <th width="5%">{{trans('general.action')}}</th>
                </tr>
            </thead>
            <tbody>
                <!-- layout -->
                <tr>
                    <td><input type="text" class="form-control" value="1" id="expenseinc-0" disabled></td>
                    <td>
                        <input type="text" class="form-control accountname" name="name[]" id="accountname-0" placeholder="Enter Ledger Account" autocomplete="off">
                        <input type="hidden" id="expitemid-0" name="item_id[]">
                    </td>
                    <td><input type="text" class="form-control exp_qty" name="qty[]" id="expqty-0" value="1"></td>
                    <td><input type="text" class="form-control exp_uom" name="uom[]" id="expuom-0"></td>
                    <td><input type="text" class="form-control exp_price" name="rate[]" id="expprice-0"></td>
                    <td>
                        <select class="form-control exp_vat custom-select" name="itemtax[]" id="expvat-0">
                            <option value="">-- VAT --</option>
                            @foreach ($additionals as $tax)
                                <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                    {{ $tax->name }}
                                </option>
                            @endforeach                                                    
                        </select>
                    </td>  
                    <td class="text-center"><span class="exp_tax" id="exptax-0">0</span></td>
                    <td class="text-center"><b><span class="exp_amount" id="expamount-0">0</span></b></td>
                    <td><button type="button" class="btn btn-danger remove d-none"><i class="fa fa-minus-square"></i></button></td>
                    <input type="hidden" class="exptaxr" name="taxrate[]">
                    <input type="hidden" class="expamountr" name="amount[]">
                    <input type="hidden" name="type[]" value="Expense">
                    <input type="hidden" name="id[]" value="0">
                    <input type="hidden" name="warehouse_id[]">
                    <input type="hidden" name="supplier_product_id[]">
                </tr>
                <tr>
                    <td colspan="4">
                        <textarea id="expdescr-0" class="form-control descr" name="description[]" rows="4" placeholder="Enter Description"></textarea>
                        <input type="checkbox" name="" id="check"><span>Add Import</span><br>
                         <input type="checkbox" value="0" class="cp_check" id="cp_check-0"><span>Copy PRJ / Non PRJ</span>
                    </td>
                    <td colspan="5">
                        <div class="row">
                            <div class="col-8">
                                <input type="text" class="form-control projectexp" id="projectexptext-0" placeholder="Search Project By Name">
                                <input type="hidden" name="itemproject_id[]" id="projectexpval-0">
                            </div>
                            <div class="col-4">
                                <select id="exp-budgetline-0" name="budget_line_id[]" class="form-control custom-select exp-budgetline">
                                    <option value="">-- Select a Budget Line --</option>
                                </select>
                                <input type="hidden" class="exp_budget_line_id" name="budget_line_id[]" id="exp_budget_line_id-0" disabled>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-6">
                                <select id="expense-class-budget-0" name="purchase_class_budget[]" class="form-control expense-class-budget round" data-placeholder="Search Non-Project Class">
                                    <option value=""></option>
                                    @foreach ($purchase_classes as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }} - {{ $row->expenseCategory ? $row->expenseCategory->name : '' }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="exp_purchase_class_budget" name="purchase_class_budget[]" id="exp_purchase_class_budget-0" disabled>
                            </div>
                            <div class="col-6">
                                <select id="expense-classlist-id-0" name="item_classlist_id[]" class="form-control round select2 expense-classlist" data-placeholder="Search Branch/ Dept ">
                                    <option value=""></option>
                                    @foreach ($classlists as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="exp_classlist" name="item_classlist_id[]" id="exp_classlist-0" disabled>
                            </div>
                        </div>
                        <div class="row div_import mt-1 d-none" id="div_import-0">
                            <div class="col-6">
                                <select id="import_request_id-0" name="import_request_id[]" class="form-control import_request_id round" data-placeholder="Search Import Request">
                                    <option value=""></option>
                                    @foreach ($import_requests as $row)
                                    @php
                                        $name = gen4tid('IMP-', $row->tid) . ' - ' . $row->notes;
                                    @endphp
                                        <option value="{{ $row->id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </td>
                </tr>
                <!-- end layout -->
    
                <!-- fetched rows -->
                @isset ($purchase)
                    @php $i = 1; @endphp
                    @foreach ($purchase->products as $item)
                        @if ($item->type == 'Expense')
                            <tr>
                                <td><input type="text" class="form-control" value="{{$i}}" id="expenseinc-{{$i}}" disabled></td>
                                <td><input type="text" class="form-control accountname" name="name[]" value="{{ @$item->account->holder }}" id="accountname-{{$i}}" placeholder="Enter Ledger"></td>
                                <td><input type="text" class="form-control exp_qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="expqty-{{$i}}"></td>
                                <td><input type="text" class="form-control exp_uom" name="uom[]" value="{{ $item->uom }}" id="expuom-{{$i}}"></td>
                                <td><input type="text" class="form-control exp_price" name="rate[]" value="{{ +$item->rate }}" id="expprice-{{$i}}"></td>
                                <td>
                                    <select class="form-control exp_vat custom-select" name="itemtax[]" id="expvat-{{$i}}">
                                        <option value="">-- VAT --</option>
                                        @foreach ($additionals as $tax)
                                            <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->itemtax ? 'selected' : ''}}>
                                                {{ $tax->name }}
                                            </option>
                                        @endforeach                  
                                    </select>
                                </td>                          
                                <td class="text-center"><span class="exp_tax" id="exptax-{{$i}}">{{ +$item->taxrate }}</span></td>
                                <td class="text-center"><b><span class="exp_amount" id="expamount-{{$i}}">{{ +$item->amount }}</span></b></td>
                                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square"></i></button></td>
                                <input type="hidden" id="expitemid-{{$i}}" name="item_id[]"value="{{ $item->item_id }}" >
                                <input type="hidden" class="exptaxr" name="taxrate[]" value="{{ +$item->taxrate }}">
                                <input type="hidden" class="expamountr" name="amount[]" value="{{ +$item->amount }}">
                                <input type="hidden" name="type[]" value="Expense">
                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                <input type="hidden" name="warehouse_id[]">
                                <input type="hidden" name="supplier_product_id[]">
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <textarea id="expdescr-{{$i}}" class="form-control descr" name="description[]" rows="4" placeholder="Enter Description">{{ $item->description }}</textarea>
                                    <input type="checkbox" name="" id="check"><span>Add Import</span><br>
                                    <input type="checkbox" value="0" class="cp_check" id="cp_check-{{ $i }}"><span>Copy PRJ / Non PRJ</span>
                                </td>
                                <td colspan="5">

                                    <div class="row">
                                        <div class="col-7">
                                            <input type="text" class="form-control projectexp" value="{{ $item->project_name ?: '' }}" id="projectexptext-{{$i}}" placeholder="Search Project By Name">
                                            <input type="hidden" name="itemproject_id[]" value="{{ $item->itemproject_id }}" id="projectexpval-{{$i}}">
                                        </div>
                                        <div class="col-5">
                                            @php $budgetLines = ProjectMilestone::where('project_id', $item->itemproject_id)->select('name', 'id')->get() @endphp
                                            <select id="exp-budgetline-{{$i}}" name="budget_line_id[]" class="form-control custom-select exp-budgetline">
                                                <option value="">-- Select a Budget Line --</option>
                                                @foreach($budgetLines as $row)
                                                    <option value="{{$row->id}}"  {{ $row->id == $item->budget_line_id? 'selected' : '' }}>{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" class="exp_budget_line_id" value="{{ $item->budget_line_id }}" name="budget_line_id[]" id="exp_budget_line_id-{{ $i }}" disabled>
                                        </div>
                                    </div>

                                    <div class="row mt-1">
                                        <div class="col-6">
                                            <select id="expense-class-budget-{{$i}}" name="purchase_class_budget[]" class="form-control expense-class-budget round" data-placeholder="Search Non-Project Class">
                                                <option value=""></option>
                                                @foreach ($purchase_classes as $row)
                                                    <option value="{{ $row->id }}" {{ @$item->purchaseClassBudget->purchase_class_id == $row->id? 'selected' : '' }}>{{ $row->name }} - {{ $row->expenseCategory ? $row->expenseCategory->name : '' }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" class="exp_purchase_class_budget" value="{{ @$item->purchaseClassBudget->purchase_class_id }}" name="purchase_class_budget[]" id="exp_purchase_class_budget-{{ $i }}" disabled>
                                        </div>
                                        <div class="col-6">
                                            <select id="expense-classlist-id-{{$i}}" name="item_classlist_id[]" class="form-control round expense-classlist" data-placeholder="Search Branch/ Dept">
                                                <option value=""></option>
                                                @foreach ($classlists as $row)
                                                    <option value="{{ $row->id }}" {{ $item->classlist_id == $row->id? 'selected' : '' }}>{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" class="exp_classlist" value="{{ $item->classlist_id }}" name="item_classlist_id[]" id="exp_classlist-{{ $i }}" disabled>
                                        </div>
                                    </div>
                                    <div class="row div_import mt-1 d-none" id="div_import-0">
                                        <div class="col-6">
                                            <select id="import_request_id-0" name="import_request_id[]" class="form-control import_request_id round" data-placeholder="Search Import Request">
                                                <option value=""></option>
                                                @foreach ($import_requests as $row)
                                                @php
                                                    $name = gen4tid('IMP-', $row->tid) . ' - ' . $row->notes;
                                                @endphp
                                                    <option value="{{ $row->id }}" {{$row->id == $item->import_request_id ? 'selected' : ''}}>{{ $name }}</option>
                                                @endforeach
                                            </select>
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
                        <button type="button" class="btn btn-success btn-sm" aria-label="Left Align" id="addexp">
                            <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                        </button>
                    </td>
                    <td colspan="7"></td>
                </tr>
                <tr class="bg-white">
                    <td colspan="7" align="right"><b>{{trans('general.total_tax')}}</b></td>
                    <td align="left" colspan="2"><span id="exprow_taxttl" class="lightMode">0</span></td>
                </tr>
                <tr class="bg-white">
                    <td colspan="7" align="right"><b>Expense Total</b></td>
                    <td align="left" colspan="2">
                        <input type="text" class="form-control" name="expense_grandttl" value="0.00" id="exp_grandttl" readonly>
                        <input type="hidden" name="expense_subttl" value="0.00" id="exp_subttl">
                        <input type="hidden" name="expense_tax" value="0.00" id="exp_tax">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
