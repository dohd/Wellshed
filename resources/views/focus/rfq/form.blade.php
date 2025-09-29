<div class="row">

    @php

        $loadedItems = [];

        if (@$budgetItems) $loadedItems = @$budgetItems;
        else if(@$purchaseRequestItems) $loadedItems = @$purchaseRequestItems;

    @endphp


    <div class="col-sm-12 cmp-pnl">
        <div class="inner-cmp-pnl">
            <h3 class="title">Properties</h3>
            <div class="form-group row">
                <div class="col-sm-4">
                    <label for="tid" class="caption">RFQ No.</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('tid', gen4tid("RFQ-", @$rfq? $rfq->tid : $last_tid+1), ['class' => 'form-control round', 'disabled']) }}
                        {{ Form::hidden('tid', @$rfq? $rfq->tid : $last_tid+1) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="transaction_date" class="caption">RFQ Date*</label>
                    <div class="input-group">
                        {{ Form::text('date', isset($loadedItems) ? (new DateTime())->format('d-m-Y') : @$rfq->date, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="due_date" class="caption">Due Date*</label>
                    <div class="input-group">                                            
                        {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'due_date', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-12">
                    <label for="supplier">Search Supplier</label>
                    <select name="supplier_ids[]" id="supplier_ids" data-placeholder="Search Suppliers" class="form-control" multiple>
                        <option value="">Search Suppliers</option>
                        @foreach ($suppliers as $supplier)
                        @php
                            $ids = explode(',', @$rfq->supplier_ids);
                        @endphp
                            <option value="{{$supplier->id}}" {{ in_array($supplier->id, (@$ids ?: []))? 'selected' : '' }}>{{$supplier->company}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-4">
                    <label for="terms">Terms</label>
                    <select name="term_id" class="form-control" id="term_id" data-placeholder="Search Terms">
                        <option value="">Search Terms</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ $term->id == @$rfq->term_id ? 'selected' : ''}}>
                                {{ $term->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-8">
                    <label for="">Stock Availability and Credit Terms</label>
                    {{ Form::textarea('credit_terms', null, ['class' => 'form-control', 'placeholder' => 'Stock Availability and Credit Terms', 'rows'=>'2', 'required']) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="toAddInfo" class="caption">Subject*</label>
                    {{ Form::textarea('subject', null, ['class' => 'form-control', 'placeholder' => trans('general.note'), 'rows'=>'1', 'required']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col">
                    <label for="purchase_requisition">Purchase Requisition</label>
                    <select name="purchase_requisition_ids[]" id="purchase_requisition_ids" data-placeholder="Search Purchase Requisitions" class="form-control" multiple>
                        <option value="">Search Purchase Requisitions</option>
                        @foreach ($purchase_requisitions as $item)
                            
                         @php
                            $pr_tid = gen4tid('PR-', $item->tid);
                            $pr_name = $item->note;
                            $project_tid = $item->project ? gen4tid('PRJ-',$item->project->tid) : '';
                            $project_name = $item->project ? $item->project->name : '';
                            $mr_tid = $item->purchase_request ? gen4tid('REQ-',$item->purchase_request->tid) : '';
    
                            $full = $pr_tid . ' | ' .$pr_name. ' | '. $mr_tid . ' | '.$project_tid.' | '.$project_name;
                            $name = gen4tid('PR-', $item->tid) . ' - '. $item->note;
                        @endphp
                            <option value="{{$item->id}}">{{$full}}</option>
                        @endforeach
                    </select>
                    <button type="button" id="btnSubmit" class="btn btn-sm btn-success">Load</button>
                </div>
            </div>
            {{-- <div class="form-group row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="project" class="caption">Project</label>
                        <select class="form-control" name="project_id" id="project" data-placeholder="Search Project by Name, Customer, Branch">
                        </select>
                    </div>
                </div>
            </div> --}}
            
        </div>
    </div>
</div>

<!-- Tab Menus -->
<ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
    <li class="nav-item bg-gradient-directional-blue">
        <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Inventory/Stock Items</a>
    </li>
    <li class="nav-item bg-danger">
        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Expenses</a>
    </li>
{{--    <li class="nav-item bg-success">--}}
{{--        <a class="nav-link text-danger" id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Assets & Equipments</a>--}}
{{--    </li>--}}
    {{-- <li class="nav-item bg-secondary">
        <a class="nav-link" id="active-tab4" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">Queued Requisition Items</a>
    </li> --}}
</ul>
<div class="tab-content px-1 pt-1">
    <!-- tab1 -->
    @include('focus.rfq.partials.stock_tab')
    <!-- tab2 -->
    @include('focus.rfq.partials.expense_tab')
    <!-- tab3 -->
{{--    @include('focus.rfq.partials.asset_tab')--}}
    <!-- tab4 -->
    {{-- @include('focus.purchaseorders.partials.queue_stock') --}}

</div>

<div class="mt-2">
    {{ Form::submit('Generate RFQ', ['class' => 'btn btn-success sub-btn btn-lg']) }}
</div>

<input type="hidden" name="supplier_type" value="supplier">