<div class="row">     
    {{-- <div class="form-group row"> --}}
        <div class="col-10">
            <label for="subject" >Subject / Title</label>
            {{ Form::text('notes', null, ['class' => 'form-control', 'id' => 'subject', 'required']) }}
        </div>
    {{-- </div> --}}
</div>
<br>
<!-- quotes item table -->
{{-- @include('focus.quotes.partials.quote-items-table') --}}
@include('focus.template_quotes.partials.quote_items')
<!-- footer -->
<div class="form-group row">
    <div class="col-9">
        <a href="javascript:" class="btn btn-success" id="addProduct"><i class="fa fa-plus-square"></i> Add Product</a>
        <a href="javascript:" class="btn btn-primary" id="addTitle"><i class="fa fa-plus-square"></i> Add Title</a>
        <a href="javascript:" class="btn btn-secondary ml-1 d-none" data-toggle="modal" data-target="#skillModal" id="addSkill">
            <i class="fa fa-wrench"></i> Labour
        </a>
        <a href="javascript:" class="btn btn-warning" id="addMisc"><i class="fa fa-plus"></i> Expense & Misc</a>
        <a href="javascript:" class="btn btn-purple ml-1" data-toggle="modal" data-target="#extrasModal" id="addExtras">
            <i class="fa fa-plus"></i> Header & Footer
        </a>
        {{-- <div class="form-group row mt-2">
            <div class='col-md-12'>
                <div class='col m-1'>
                                            
                    <input type="checkbox" id="attach-djc" value="checked">
                    <label for="client-type" class="font-weight-bold">Attach Site Survey Report Details</label>
                </div>
            </div>
            <div class="col-4">
                <label for="reference" >Site Survey Report Reference</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('reference', null, ['class' => 'form-control round', 'placeholder' => 'Site Survey Report Reference', 'id' => 'reference', 'required']) }}
                </div>
            </div>
            <div class="col-4">
                <label for="reference_date" >Site Survey Report Reference Date</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('reference_date', null, ['class' => 'form-control round datepicker', 'id' => 'referencedate']) }}
                </div>
            </div>
        </div> --}}
        {{-- <div class="form-group row">
            <div class='col-md-12'>
                <div class='col'>
                                            
                    <input type="checkbox" id="add-check" value="checked">
                    <label for="client-type" class="font-weight-bold">Attach Repair Equipment</label>
                </div>
            </div>
        </div>
        @include('focus.quotes.partials.equipments') --}}
    </div>
    <div class="col-3">
        <div>
            <label><span class="text-primary">(Total Estimated Cost: <span class="estimate-cost font-weight-bold text-dark">0.00</span>)</span></label>
        </div>
        <label>SubTotal</label>
        <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>
        <label>Taxable</label>
        <input type="text" name="taxable" id="taxable" class="form-control" readonly>
        <label id="tax-label">{{ trans('general.total_tax') }}
            {{-- <span id="vatText" class="text-primary">(VAT-Exc)</span> --}}
        </label>
        <label id="tax-label" class="float-right">Print Type:
            <span id="vatText" class="text-primary"></span>
        </label>
        <input type="text" name="tax" id="tax" class="form-control" readonly>
        <label>
            {{trans('general.grand_total')}}
            <b class="text-primary">
                (E.P: &nbsp;<span class="text-dark profit">0</span>)
            </b>
        </label>
        <input type="text" name="total" class="form-control" id="total" readonly>
        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }}
    </div>
</div>
<!-- repair or maintenance type  -->
@if (request('doc_type') == 'maintenance') 
    {{ Form::hidden('is_repair', 0) }}
@endif
{{-- @include('focus.template_quotes.partials.skillset-modal') --}}
@include('focus.template_quotes.partials.extras_modal')