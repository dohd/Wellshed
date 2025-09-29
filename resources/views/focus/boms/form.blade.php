<div class='form-group row'>
    <div class="col-5">
        <label for="leads">Search Ticket</label>
        <select class="form-control" name="lead_id" id="lead_id" data-placeholder="Search Ticket" required disabled> 
            <option value=""></option>                                                
            @foreach ($leads as $lead)
                @php
                    if (!@$lead->id) continue;
                    $customer_name = '';
                    if ($lead->customer) {
                        $customer_name .= $lead->customer->company;
                        if ($lead->branch) $customer_name .= " - {$lead->branch->name}";
                    } else $customer_name = $lead->client_name;
                    
                    // create mode
                    $prefix = $prefixes[1];
                    if (isset($quote)) $prefix = $prefixes[2]; //edit mode
                @endphp
                <option 
                    value="{{ $lead->id }}" 
                    title="{{ $lead->title }}" 
                    client_ref="{{ $lead->client_ref }}"
                    customer_id="{{ $lead->client_id }}"
                    branch_id="{{ $lead->branch_id }}"
                    assign_to="{{ $lead->assign_to }}"
                    {{ $lead->id == @$bom->lead_id ? 'selected' : '' }}
                >
                    {{ gen4tid("{$prefix}-", $lead->reference) }} - {{ $customer_name }} - {{ $lead->title }}
                </option>
            @endforeach                                                                                             
        </select>
    </div>
    <div class="col-5">
        {{ Form::label( 'name', 'Title',['class' => 'control-label']) }}
        {{ Form::text('name', @$bom->name, ['class' => 'form-control round', 'placeholder' => 'Title', 'readonly']) }}
    </div>
    <div class="col-2">
        <label for="">Select BoQ Sheet</label>
        <select name="boq_sheet_id" id="boq_sheet_id" class="form-control">
            <option value="">--select boq sheet--</option>
            @foreach ($boq_sheets as $item)
                <option value="{{$item->id}}">{{$item->sheet_name}}</option>
            @endforeach
        </select>
    </div>
   
</div>

<div class="mt-3">
    @include('focus.boms.partials.bom_items')
</div>
<div class="form-group row">
    <div class="col-9">
        <a href="javascript:" class="btn btn-success" id="addProduct"><i class="fa fa-plus-square"></i> Add Product</a>
        <a href="javascript:" class="btn btn-primary" id="addTitle"><i class="fa fa-plus-square"></i> Add Title</a>
        <a href="javascript:" class="btn btn-secondary ml-1 d-none" data-toggle="modal" data-target="#skillModal" id="addSkill">
            <i class="fa fa-wrench"></i> Labour
        </a>
        <a href="javascript:" class="btn btn-warning" id="addMisc"><i class="fa fa-plus"></i> Expense & Misc</a>
    </div>
    <div class="col-3">
        <div>
            <label><span class="text-primary">(Total Estimated Cost: <span class="estimate-cost font-weight-bold text-dark">0.00</span>)</span></label>
        </div>
        <label class="mb-0">Subtotal</label>
        <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>
        <label class="mb-0">Taxable</label>
        <input type="text" name="taxable" id="vatable" class="form-control" readonly>
        <label class="mb-0" id="tax-label">{{ trans('general.total_tax') }}</label>
        <label class="mb-0 pl-5" id="tax-label" class="float-right">Print Type:
            <span id="vatText" class="text-primary"></span>
        </label>
        <input type="text" name="tax" id="tax" class="form-control" readonly>
        <label class="mb-0">{{trans('general.grand_total')}}
            <b class="text-primary pl-5">
                (E.P: &nbsp;<span class="text-dark profit">0</span>)
            </b>
        </label>
        <input type="text" name="total" class="form-control" id="total" readonly>
        {{-- {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }} --}}
        <button type="button" class="btn btn-success btn-lg mt-1" id="submitBoqForm">Generate</button>
    </div>
</div>
@section("after-scripts")
    <script type="text/javascript">
        
    </script>
@endsection
