<div class="row mb-1 form-group">
    <div class="col-md-1 col-12">
        <label for="tid">#Issue No.</label>
        {{ Form::text('tid', @$tid ?: @$stock_issue->tid, ['class' => 'form-control','disabled' => 'disabled']) }}
    </div>
    <div class="col-md-2 col-12">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div>
    <div class="col-md-2 col-12">
        <label for="ref_no">Reference No.</label>
        {{ Form::text('ref_no', null, ['class' => 'form-control', 'id' => 'ref_no']) }}
    </div>
    <div class="col-md-2 col-12">
        <label for="issue_to">Issue To</label>
        <select name="issue_to" id="issue_to" class="custom-select" autocomplete="off">
            <option value="">Default</option>
            @foreach (['Customer', 'Employee', 'Project', 'Finished Goods'] as $value)
                <option value="{{ $value }}">
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-5 col-12 select-col">
        <label for="employee">Employee</label>
        <select name="employee_id" id="employee" class="form-control" data-placeholder="Search Employee" autocomplete="off">
            <option value=""></option>
            @foreach ($employees as $row)
                <option value="{{ $row->id }}" {{ @$stock_issue->employee_id == $row->id? 'selected' : ''}}>
                    {{ $row->first_name }} {{ $row->last_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-5 col-12 select-col d-none">
        <label for="customer">Customer</label>
        <select name="customer_id" id="customer" class="form-control d-none" data-placeholder="Search Customer" autocomplete="off">
            <option value=""></option>
            @foreach ($customers as $row)
                <option value="{{ $row->id }}" {{ @$stock_issue->customer_id == $row->id? 'selected' : ''}}>
                    {{ $row->company ?: $row->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-5 col-12 select-col d-none">
        <label for="project">Project</label>
        <select name="project_id" id="project" class="form-control d-none" data-placeholder="Search Project" autocomplete="off">
            <option value=""></option>
            @foreach ($projects as $row)
                <option
                    value="{{ $row->id }}"
                    quote_ids="{{ implode(',', $row->quote_ids) }}"
                    {{ @$stock_issue->project_id == $row->id? 'selected' : ''}}
                >
                    {{ gen4tid('PRJ-', $row->tid) }} - {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-5 col-12 select-col d-none">
        <label for="finished_goods">Finished Goods</label>
        <select name="finished_good_id" id="finished_goods" class="form-control d-none" data-placeholder="Search Finished Goods" autocomplete="off">
            <option value=""></option>
            @foreach ($finished_goods as $row)
                <option
                    value="{{ $row->id }}"
                    {{ @$stock_issue->finished_good_id == $row->id? 'selected' : ''}}
                >
                    {{ gen4tid('FG-', $row->tid) }} - {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row form-group">
    <div class="col-md-6 col-12">
        <label for="issue_to_third_party">Issue To Third Party/Unlisted</label>
        {{ Form::text('issue_to_third_party', null, ['class' => 'form-control', 'id' => 'issue_to_third_party', 'placeholder' => 'Delivery Driver/Service, e.t.c']) }}
    </div>
    <div class="col-md-2 col-12">
        <label for="tid">Reference</label>
    <select name="reference" id="ref" class="custom-select" disabled>
            @foreach (['quote', 'invoice', 'requisition'] as $item)
                <option value="{{$item}}" {{$item == @$stock_issue->reference ? 'selected' : ''}}>{{ ucfirst($item) }}</option>
            @endforeach
    </select>
    </div>
    <div class="col-md-4 col-12 quote-col">
        <label for="quote">Load Items From Quote / PI</label>
        <select
                name="quote_id"
                id="quote"
                class="form-control"
                data-placeholder="Search Quote / PI Number"
                autocomplete="off" disabled
                @if(!empty($stock_issue)) disabled @endif
        >
            <option value=""></option>
            @foreach ($quotes as $row)
                <option
                    value="{{ $row->id }}"
                    customer_id="{{ $row->customer_id }}"
                    quote_type="{{ $row->quote_type }}"
                    {{ @$stock_issue->quote_id == $row->id? 'selected' : ''}}
                >
                    {{ gen4tid($row->bank_id? 'PI-' : 'QT-', $row->tid) }} {{ $row->notes }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 col-12 invoice-col d-none">
        <label for="invoice">Invoice</label>
        <select name="invoice_id" id="invoice" class="form-control" data-placeholder="Search Invoice" autocomplete="off">
            <option value=""></option>
            @if (isset($stock_issue->invoice))
                <option value="{{ $stock_issue->invoice_id }}" selected>
                    {{ gen4tid('INV-', $stock_issue->invoice->tid) . ' ' . $stock_issue->invoice->notes }}
                </option>
            @endif
        </select>
    </div>
    <div class="col-md-4 col-12 requisition_col d-none">
        <label for="purchase_requisition">Search Purchase Requistion (PR)</label>
        <select name="purchase_requisition_id" id="purchase_requisition" class="form-control" data-placeholder="Search Purchase Requisition" @if(!empty($stock_issue)) disabled @endif>
            <option value=""></option>
            @foreach ($purchase_requisitions as $purchase_requisition)
                @php
                    $pr_tid = '';
                    if ($purchase_requisition->pr_parent_id) {
                        $pr_tid = gen4tid('PR-', $purchase_requisition->pr_parent->tid) . 'B';
                    }elseif ($purchase_requisition->pr_child) {
                        $pr_tid = gen4tid('PR-', $purchase_requisition->tid) . 'A';
                    }else{
                        $pr_tid = gen4tid('PR-', $purchase_requisition->tid);
                    }
                    $pr_name = $purchase_requisition->note;
                    $project_tid = $purchase_requisition->project ? gen4tid('PRJ-',$purchase_requisition->project->tid) : '';
                    $project_name = $purchase_requisition->project ? $purchase_requisition->project->name : '';
                    $mr_tid = $purchase_requisition->purchase_request ? gen4tid('REQ-',$purchase_requisition->purchase_request->tid) : '';

                    $full = $pr_tid . ' | ' .$pr_name. ' | '. $mr_tid . ' | '.$project_tid.' | '.$project_name;
                    $name = gen4tid('PR-', $purchase_requisition->tid) . ' - '. $purchase_requisition->note;
                @endphp
                <option value="{{$purchase_requisition->id}}" {{@$stock_issue->purchase_requisition_id == $purchase_requisition->id ? 'selected' : ''}}>{{$full}}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row form-group">
    <div class="col-md-8 col-12">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required' => 'required']) }}
    </div>
    <div class="col-md-4 col-12">
        <label for="budget_line" class="caption" style="display: inline-block;">Project Budget Line</label>
        <select id="budget_line" name="budget_line" class="form-control" disabled>
            <option value="">Load Budget Lines from a Project Quote</option>
        </select>
        <p id="budget_line_warning" class="text-red ml-2" style="color: red; font-size: 16px; "> </p>
    </div>
</div>
<div class="row form-group">
    <div class="col-md-4 col-12">
        <label for="ledger" class="caption">Consumable Account (Office Supplies, Short-term Tools)</label>
        <select id="account" name="account_id" class="form-control" data-placeholder="Search Expense Account">
            <option value=""></option>
            @foreach ($accounts as $account)
                <option value="{{$account->id}}" {{$account->id == @$stock_issue->account_id ? 'selected' : ''}}>{{$account->holder}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="assign_to">Assign To</label>
        <select name="assign_to_ids[]" id="assign_to_ids" class="form-control assign_to_ids" data-placeholder="Search Employee" multiple>
            <option value="">Search Employee</option>
            @foreach ($employees as $row)
            @php
                $ids = explode(',', @$stock_issue->assign_to_ids);
            @endphp
                <option value="{{ $row->id }}" {{ in_array($row->id, (@$ids ?: []))? 'selected' : '' }}>
                    {{ $row->first_name }} {{ $row->last_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="table-responsive">
    <table id="productsTbl" class="table table-sm tfr my_stripe_single text-center">
       <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="25%">Stock Item</th>
                <th width="10%">Prod. Code</th>
                <th width="6%">Unit</th>
                <th width="8%">Budgeted</th>
                <th width="8%">Booked</th>
                <th width="8%">Issued</th>
                <th width="8%">Requested</th>
                <th width="8%">Qty On-Hand</th>
                <th width="8%">Qty Rem</th>
                <th width="8%">Issue Qty</th>
                <th width="10%">Issue From</th>
                <th width="7%">Action</th>
            </tr>
        </thead>
        <tbody>
            @if (@$stock_issue)
                @foreach ($stock_issue->items as $i => $item)
                    <tr>
                        @php
                            $budget_qty = 0;
                            $requisition_qty = 0;
                            if(!empty($budgetDetails[$i])){
                                $budget_qty = $budgetDetails[$i]['new_qty'];
                            }else if($item->budget_item){
                                $budget_qty = $item->budget_item->new_qty;
                            }
                            if($item->requisition_item){
                                $requisition_qty = $item->requisition_item->qty;
                            }
                        @endphp
                        <td style="min-width:200px;"><textarea id="name-{{$i+1}}" class="form-control name" cols="30" rows="2" autocomplete="off" required readonly>{{ @$item->productvar->name }}</textarea></td>
                        <td><span class="product-code">{{ @$item->productvar->code }}</span></td>
                        <td><span class="unit">{{ @$item->productvar->product->unit->code }}</span></td>
                        <td><span class="budget">{{ number_format(@$budget_qty, 2) }}</span></td>
                        <td><span class="booked">{{numberFormat($item->booked_qty)}}</span></td>
                        <td><span class="issued">{{ number_format(@$budgetDetails[$i]['issue_qty'], 2) }}</span></td>
                        <td><span class="requested">{{number_format($requisition_qty, 2)}}</span></td>
                        <td><span class="qty-onhand">{{ +$item->qty_onhand }}</span></td>
                        <td><span class="qty-rem">{{ +$item->qty_rem }}</span></td>
                        <td><input type="text" name="issue_qty[]" value="{{ +$item->issue_qty }}" class="form-control issue-qty" autocomplete="off"></td>
                        <td class="td-source">
                            <input type="hidden" name="warehouse_id[]" value="{{ $item->warehouse_id }}" class="source-inp">
                            <select name="warehouse_id[]" id="source-{{$i+1}}" class="form-control source" data-placeholder="Search Location" disabled>
                                <option value=""></option>
                                <option value="{{ $item->warehouse_id }}" products_qty="{{ +$item->qty_onhand }}" selected>
                                    {{ @$item->warehouse->title }} ({{ +$item->qty_onhand }})
                                </option>
                            </select>
                        </td>
                        <td class="td-assignee">
                            {{-- <div class="row no-gutters"> --}}
                                {{-- <div class="col-md-10">
                                    <select name="assignee_id[]" id="assignee-{{$i+1}}" class="form-control assignee" data-placeholder="Search Employee" disabled>
                                        <option value=""></option>
                                        @foreach ($employees as $row)
                                            <option value="{{ $row->id }}" {{ $item->assignee_id == $row->id? 'selected' : '' }}>
                                                {{ $row->first_name }} {{ $row->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div> --}}
                                {{-- <div class="col-md-2"> --}}
                                    <span class="badge badge-danger mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                                {{-- </div> --}}
                            {{-- </div> --}}
                        </td>
                        <input type="hidden" name="qty_onhand[]" value="{{ +$item->qty_onhand }}" class="qty-onhand-inp">
                        <input type="hidden" name="qty_rem[]" value="{{ +$item->qty_rem }}" class="qty-rem-inp">
                        <input type="hidden" name="cost[]" value="{{ +$item->cost }}" class="cost">
                        <input type="hidden" name="amount[]" value="{{ +$item->amount }}" class="amount">
                        <input type="hidden" name="productvar_id[]" value="{{ $item->productvar_id }}" class="prodvar-id">
                        <input type="hidden" name="booked_qty[]" class="booked_qty" value="{{+$item->booked_qty}}">
                        <input type="hidden" name="budget_item_id[]" value="{{$item->budget_item_id}}">
                        <input type="hidden" name="requisition_item_id[]" value="{{$item->requisition_item_id}}">
                        <input type="hidden" name="item_id[]" class="item_id" value="{{$item->item_id}}">
                    </tr>
                @endforeach
            @else
                <tr>
                    <td><textarea style="min-width:200px;" id="name-1" class="form-control name" cols="30" rows="1" autocomplete="off" required readonly></textarea></td>
                    <td><span class="product-code"></span></td>
                    <td><span class="unit"></span></td>
                    <td><span class="budget"></span></td>
                    <td><span class="booked"></span></td>
                    <td><span class="issued"></span></td>
                    <td><span class="requested"></span></td>
                    <td><span class="qty-onhand"></span></td>
                    <td><span class="qty-rem"></span></td>
                    <td><input type="text" name="issue_qty[]" class="form-control issue-qty" autocomplete="off"></td>
                    <td class="td-source">
                        <select name="warehouse_id[]" id="source-1" class="form-control source" data-placeholder="Search Location">
                            <option value=""></option>
                        </select>
                    </td>
                    <td class="td-assignee">
                        {{-- <div class="row no-gutters"> --}}
                            {{-- <div class="col-md-10">
                                <select name="assignee_id[]" id="assignee-1" class="form-control assignee" data-placeholder="Search Employee" disabled>
                                    <option value=""></option>
                                    @foreach ($employees as $row)
                                        <option value="{{ $row->id }}">
                                            {{ $row->first_name }} {{ $row->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div> --}}
                            {{-- <div class="col-md-2"> --}}
                                <span class="badge badge-danger mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                            {{-- </div> --}}
                        {{-- </div> --}}
                    </td>
                    <input type="hidden" name="qty_onhand[]" class="qty-onhand-inp">
                    <input type="hidden" name="qty_rem[]" class="qty-rem-inp">
                    <input type="hidden" name="cost[]" class="cost">
                    <input type="hidden" name="amount[]" class="amount">
                    <input type="hidden" name="productvar_id[]" class="prodvar-id">
                </tr>
            @endif
        </tbody>
    </table>
</div>
{{-- <div class="row mt-1">
    <div class="col-6">
        <button type="button" class="btn btn-success" id="add-item">
            <i class="fa fa-plus-square"></i> Item
        </button>
    </div>
</div>              --}}
{{ Form::hidden('total', null, ['id' => 'total']) }}

@section('extra-scripts')
@include('focus.stock_issues.form_js')
@endsection
