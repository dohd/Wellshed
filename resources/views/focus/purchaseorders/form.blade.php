<div class="row">
    <div class="col-md-6 cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">
            <input type="hidden" value="0" id="credit">
            <input type="hidden" value="0" id="total_aging">
            <input type="hidden" value="0" id="outstanding_balance">
            <h3 class="title">Purchase Order </h3>                                                                
            <div class="form-group row">
                <div class="col-12">
                    <label for="payer" class="caption">Search Supplier</label>                                       
                    <select name="supplier_id" class="form-control" id="supplierbox" data-placeholder="Search Supplier">
                        <option value=""></option>
                        @if (@$po->supplier)
                            <option 
                                value="{{ $po->supplier_id }}" 
                                suppliername="{{ @$po->supplier->name ?: @$po->supplier->company }}" 
                                taxid="{{ @$po->supplier->taxid }}" 
                                currencyrate="{{ (float) $po->fx_curr_rate ?: @$po->supplier->currency->rate }}"
                                currencyid="{{ (int) $po->currency_id ?: @$po->supplier->currency->id }}"
                                currencycode="{{ @$po->supplier->currency->code }}"
                                selected
                            >
                                {{ ($po->supplier->company ?: $po->supplier->name) }} : {{ @$po->supplier->email }}
                            </option>
                        @endif
                    </select>
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-md-8">
                    <label for="payer" class="caption">Supplier Name*</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>                                            
                        {{ Form::text('suppliername', null, ['class' => 'form-control round', 'placeholder' => 'Supplier Name', 'id' => 'supplier', 'disabled']) }}
                    </div>
                </div>
                <div class="col-md-4"><label for="taxid" class="caption">Tax ID</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('supplier_taxid', null, ['class' => 'form-control round', 'placeholder' => 'Tax Id', 'id' => 'taxid', 'disabled']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <table class="table-responsive tfr" id="transxnTbl">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            @foreach (['Item', 'Inventory Item', 'Expenses', 'Asset & Equipments', 'Total'] as $val)
                                <th width="20%" class="text-center">{{ $val }}</th>
                            @endforeach                                                  
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">Line Total</td>
                            @for ($i = 0; $i < 4; $i++)
                                <td class="text-center">0.00</td>
                            @endfor                                                
                        </tr>                                                  
                        <tr>
                            <td class="text-center">Tax</td>
                            @for ($i = 0; $i < 4; $i++)
                                <td class="text-center">0.00</td>
                            @endfor                                                
                        </tr>
                        <tr>
                            <td class="text-center">Grand Total</td>
                            @for ($i = 0; $i < 4; $i++)
                                <td class="text-center">0.00</td>
                            @endfor                                                                                                      
                        </tr>

                        <tr class="sub_c" style="display: table-row;">
                            <td align="right" colspan="4">
                                <p id="milestone_warning" class="text-red ml-2" style="display: inline-block; color: red; font-size: 16px; "> </p>
                                <p id="budget_warning" class="text-red ml-2" style="display: inline-block; color: red; font-size: 16px; "> </p>
                            </td>
                        </tr>


                        <tr class="sub_c" style="display: table-row;">
                            <td align="right" colspan="3">
                                @foreach (['paidttl', 'grandtax', 'grandttl'] as $val)
                                    <input type="hidden" name="{{ $val }}" id="{{ $val }}" value="0"> 
                                @endforeach 
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6 cmp-pnl">
        <div class="inner-cmp-pnl">
            <h3 class="title">{{trans('purchaseorders.properties')}}</h3>
            <div class="form-group row">
                <div class="col-md-4">
                    <label for="tid" class="caption">Order No.</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('tid', gen4tid("{$prefixes[0]}-", @$po? $po->tid : $last_tid+1), ['class' => 'form-control round', 'disabled']) }}
                        {{ Form::hidden('tid', @$po? $po->tid : $last_tid+1) }}
                    </div>
                </div>
                <div class="col-md-4"><label for="transaction_date" class="caption">Order Date*</label>
                    <div class="input-group">                                            
                        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date','required']) }}
                    </div>
                </div>
                <div class="col-md-4"><label for="due_date" class="caption">Due Date*</label>
                    <div class="input-group">                                            
                        {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'due_date','required']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-4">
                    <label for="taxFormat" class="caption">Tax</label>
                    <select class="custom-select" name="tax" id="tax">
                        @foreach ($additionals as $row)
                            <option value="{{ +$row->value }}" {{ $row->is_default ? 'selected' : ''}}>
                                {{ $row->name }} 
                            </option>
                        @endforeach                                                    
                    </select>
                </div>

                <div class="col-4">
                    <label for="currency">Currency</label>                      
                    <div class="row no-gutters">
                        <div class="col-md-6">
                            <select name="currency_id" id="currency" class="custom-select" required>
                                @if (@$po->currency)
                                    <option value="{{ $po->currency_id }}" rate="{{ $po->fx_curr_rate }}" selected>
                                        {{ @$po->currency->code }}
                                    </option>
                                @endif
                            </select>  
                        </div>
                        <div class="col-md-6">
                            {{ Form::text('fx_curr_rate', null, ['class' => 'form-control', 'id' => 'fx_curr_rate', 'readonly' => 'readonly', 'required' => 'required']) }}
                        </div>               
                    </div>
                </div>

                <div class="col-4">
                    <label for="pricing" >Pricing</label>                    
                    <select id="pricegroup_id" name="pricegroup_id" class="custom-select">
                        <option value="0" selected>Default </option>
                        @foreach($price_supplier as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>                    
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-md-8">
                    <label for="toAddInfo" class="caption">Subject*</label>
                    {{ Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => trans('general.note'), 'rows'=>'1', 'required' => 'required']) }}
                </div>
                <div class="col-4">
                    <label for="terms">Terms</label>
                    <select name="term_id" class="form-control">
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ $term->id == @$po->term_id ? 'selected' : ''}}>
                                {{ $term->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-6 d-none">
                    <div class="form-group">
                        <label for="project" class="caption">Project Expenses</label>
                        <select class="form-control" name="project_id" id="project" data-placeholder="Search Project by Name, Customer, Branch">
                        </select>
                    </div>
                </div>

                <div class="col-6 d-none">
                    <label for="project_milestone" class="caption">Project Budget Line</label>
                    
                    <select id="project_milestone" name="project_milestone" class="form-control">
                        <option value="">Select a Budget Line</option>
                    </select>
                </div>

                <div class="col-6 d-none">
                    <label for="purchase_class" class="caption" style="display: inline-block;">Non-project Expenses</label>
                    <select id="purchase_class" name="purchase_class" class="custom-select round" data-placeholder="Select a Non-Project Class">

                        <option value=""></option>
                        @foreach ($purchaseClasses as $pc)
                            <option value="{{ $pc->id }}"
                                @if(@$po->purchaseClassBudget)
                                    @if(@$po->purchaseClassBudget->purchase_class_id == $pc->id) selected @endif
                                @endif
                            >
                                {{ $pc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>


                {{-- <div class="col-6"> --}}
                    {{-- <label for="payer" class="caption">Requisition Items</label>                                       
                    <select class="form-control" id="quoteselect" data-placeholder="Search Quote">
                        <option value="">-----Select Requisition Items-----</option>
                        <option value="all">All Items</option>
                    </select>
                    <input type="hidden" name="quote_id" value="0" id="quoteid"> --}}
                {{-- </div> --}}

                <div class="col-6 mt-2">
                    <label for="requisition_type">Requisition Type</label>
                    <select name="requisition_type" id="requisition_type" class="form-control">
                        <option value="">--select requisition type--</option>
                        <option value="rfq" {{@$po->requisition_type == 'rfq' ? 'selected' : ''}}>RFQ</option>
                        <option value="purchase_requisition" {{@$po->requisition_type == 'purchase_requisition' ? 'selected' : ''}}>Purchase Requisition (PR)</option>
                    </select>
                </div>

                <div class="col-6 mt-2 div_rfq">
                    <label for="rfq_id">Search RFQ</label>
                    <select name="rfq_id" id="rfq_id" class="form-control" data-placeholder="Search RFQ">
                        <option value="">Search RFQ/PR</option>
                        @foreach ($rfqs as $rfq)
                            @php
                                $name = gen4tid('RFQ-', $rfq->tid) . '-' . $rfq->subject;
                            @endphp
                            <option value="{{$rfq->id}}" {{@$po->rfq_id == $rfq->id ? 'selected' : ''}}>{{$name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 mt-2 d-none div_purchase_requisition">
                    <label for="purchase_requisition_id">Search Purchase Requisition</label>
                    <select name="purchase_requisition_id" id="purchase_requisition_id" class="form-control" data-placeholder="Search Purchase Requisition">
                        <option value="">Search Purchase Requisition</option>
                        @foreach ($purchase_requisitions as $purchase_requisition)
                            @php
                                $pr_tid = gen4tid('PR-', $purchase_requisition->tid);
                                $pr_name = $purchase_requisition->note;
                                $project_tid = $purchase_requisition->project ? gen4tid('PRJ-',$purchase_requisition->project->tid) : '';
                                $project_name = $purchase_requisition->project ? $purchase_requisition->project->name : '';
                                $mr_tid = $purchase_requisition->purchase_request ? gen4tid('REQ-',$purchase_requisition->purchase_request->tid) : '';
        
                                $full = $pr_tid . ' | ' .$pr_name. ' | '. $mr_tid . ' | '.$project_tid.' | '.$project_name;
                                $name = gen4tid('PR-', $purchase_requisition->tid) . '-' . $purchase_requisition->note;
                            @endphp
                            <option value="{{$purchase_requisition->id}}" {{@$po->purchase_requisition_id == $purchase_requisition->id ? 'selected' : ''}}>{{$full}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 mt-2">
                    
                        <label for="">Select Users to Notify on PO</label>
                        <input type="checkbox" id="select_user">
                    
                </div>
                <div class="col-12 user-remove d-none">
                        <label for="">Search Users</label>
                        <select name="user_ids[]" id="user_ids" class="form-control" data-placeholder="Search Users" multiple disabled>
                            <option value="">Search Users</option>
                            @foreach ($users as $user)
                            @php
                                $ids = explode(',', @$po->user_ids);
                            @endphp
                                <option value="{{$user->id}}" {{ in_array($user->id, (@$ids ?: []))? 'selected' : '' }}>{{$user->fullname}}</option>
                            @endforeach
                        </select>
                </div><br>
                <div class="col-md-12 mt-2">
                    <label for="approval_note">Note to Approvers</label>
                    {{ Form::text('approval_note', null, ['class' => 'form-control', 'id' => 'approval_note']) }}
                </div> 

            </div>
        </div>
    </div>
</div>

<!-- Tab Menus -->
<ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
    <li class="nav-item bg-gradient-directional-blue">
        <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Inventory/Stock Items</a>
    </li>
    <li class="nav-item bg-danger d-none">
        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Expenses</a>
    </li>
    <li class="nav-item bg-success d-none">
        <a class="nav-link text-danger" id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Assets & Equipments</a>
    </li>
    {{-- <li class="nav-item bg-secondary">
        <a class="nav-link" id="active-tab4" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">Queued Requisition Items</a>
    </li> --}}
</ul>
<div class="tab-content px-1 pt-1">
    <!-- tab1 -->
    @include('focus.purchaseorders.partials.stock_tab')
    <!-- tab2 -->
    @include('focus.purchaseorders.partials.expense_tab')
    <!-- tab3 -->
    @include('focus.purchaseorders.partials.asset_tab')
    <!-- tab4 -->
    {{-- @include('focus.purchaseorders.partials.queue_stock') --}}
</div>

<input type="hidden" name="supplier_type" value="supplier">

<div class="d-flex justify-content-center mt-2">
    @php
        $disabled = '';

        if (isset($po)) {
            $lastStatus = $po->statuses()->latest()->first(); // Get the latest status regardless of value
            if ($lastStatus && $lastStatus->approval_status === 'approved') {
                $disabled = 'disabled';
            }
        }
    @endphp

    {{ Form::submit(@$po? 'Update Purchase Order': 'Generate Purchase Order', ['class' => 'btn btn-success sub-btn btn-lg', $disabled]) }}
</div>
