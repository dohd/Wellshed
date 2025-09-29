<div class="form-group row">
    <div class="col-md-2">
        <label for="tid" class="caption">Import No.</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            {{ Form::text('tid', gen4tid("IMP-", @$import_request? $import_request->tid : $last_tid+1), ['class' => 'form-control round', 'disabled']) }}
            {{ Form::hidden('tid', @$import_request? $import_request->tid : $last_tid+1) }}
        </div>
    </div>
    <div class="col-4">
        <label for="supplier">Supplier</label>
        {{ Form::text('supplier_name', null, ['class' => 'form-control', 'id' => 'supplier_name', 'required' => 'required']) }}
        {{-- <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Search Supplier">
            <option value="">Search Supplier</option>
            @foreach ($suppliers as $supplier)
                <option value="{{$supplier->id}}" 
                    currencyId="{{$supplier->currency->id}}" currency_rate="{{$supplier->currency->rate}}" 
                    currency_code="{{$supplier->currency->code}}"
                    {{$supplier->id == @$import_request->supplier_id ? 'selected' : ''}}
                    >{{$supplier->company}}</option>
            @endforeach
        </select> --}}
    </div>
    {{-- <div class="col-2">
        <label for="currency">Currency</label>                      
        <div class="row no-gutters">
            <div class="col-md-6">
                <select name="currency_id" id="currency" class="custom-select" required>
                    @if (@$import_request->currency)
                        <option value="{{ $import_request->currency_id }}" rate="{{ $import_request->fx_curr_rate }}" selected>
                            {{ @$import_request->currency->code }}
                        </option>
                    @endif
                </select>  
            </div>
            <div class="col-md-6">
                {{ Form::text('fx_curr_rate', null, ['class' => 'form-control', 'id' => 'fx_curr_rate', 'readonly' => 'readonly', 'required' => 'required']) }}
            </div>               
        </div>
    </div> --}}
    <div class="col-2">
        <label for="date">Date</label>
        <input type="text" name="date" id="date" class="form-control datepicker">
    </div>
    <div class="col-2">
        <label for="due_date">Due Date</label>
        <input type="text" name="due_date" id="due_date" class="form-control datepicker">
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

                $ids = explode(',', @$import_request->purchase_requisition_ids);
            @endphp
                <option value="{{$item->id}}" {{ in_array($item->id, (@$ids ?: []))? 'selected' : '' }}>{{$full}}</option>
            @endforeach
        </select>
        <button type="button" id="btnSubmit" class="btn btn-sm btn-success">Load</button>
    </div>
</div>
<div class="form-group row">
    <div class="col-8">
        <label for="subject" >Subject / Title</label>
        {{ Form::text('notes', null, ['class' => 'form-control', 'id' => 'subject', 'required']) }}
    </div>
</div>
<div class="form-group row mt-2">
    @include('focus.import_requests.partials.import_items')
</div>
<div class="form-group row">
    <a href="javascript:" class="btn btn-success addProduct" id="addProduct"><i class="fa fa-plus-square"></i> Add Product</a>
</div>