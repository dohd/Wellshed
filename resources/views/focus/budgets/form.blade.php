{{ Form::hidden('quote_id', $quote->id) }}  
<div class="form-group row">
    <div class="col-12">
        <h3 class="title">Generate Budget</h3>  
    </div>
</div>

<div class="row">
    <div class="col-12 cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">                        
            <div class="form-group row"> 
                <div class="col-5">
                    <label for="customer" class="caption">Customer</label>                                       
                    {{ Form::text('customer', $quote->customer? $quote->customer->company : '', ['class' => 'form-control', 'disabled']) }}
                </div> 
                <div class="col-3">
                    <label for="branch" class="caption">Branch</label>                                       
                    {{ Form::text('branch', $quote->branch? $quote->branch->name : '', ['class' => 'form-control', 'disabled']) }}
                </div> 
                <div class="col-2">
                    <label >Serial No</label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                        {{ Form::text('tid', gen4tid($quote->bank_id ? 'PI-' : 'QT-', $quote->tid), ['class' => 'form-control round', 'disabled']) }}
                    </div>
                </div>
                <div class="col-2"><label for="invoicedate" class="caption">{{trans('general.date')}}</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('date', null, ['class' => 'form-control round datepicker', 'id' => 'date', 'disabled']) }}
                    </div>
                </div>                                                               
            </div> 
        </div>
    </div>
</div>        

<div class="form-group row">
    <div class="col-10">
        <label for="subject" class="caption">Subject / Title</label>
        {{ Form::text('notes', null, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
    </div>
    <div class="col-2">
        <label for="client_ref" class="caption">Client Ref / Callout ID</label>                                       
        {{ Form::text('client_ref', null, ['class' => 'form-control', 'id' => 'client_ref', 'disabled']) }}
    </div> 
</div>
@if (auth()->user()->ins == 125)
    
<button id="fill-btn" type="button" class="btn btn-sm btn-success float-right">Fill Empty UoM</button>
@endif
<div class="form-group">
    <button type="button" class="btn btn-sm btn-primary" id="refreshButton">Numbering</button>
    <table id="productsTbl" class="table-responsive tfr my_stripe_single" style="min-height: 150px;">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white">
                <th width="6%" class="text-center">#</th>
                <th width="33%" class="text-center">Product</th>
                <th width="5%" class="text-center">Code</th>
                <th width="8%" class="text-center">Quoted Qty</th>                                
                <th width="15%" class="text-center">UoM</th>
                <th width="8%" class="text-center">Qty</th>     
                <th width="12%" class="text-center">Buy Price</th>
                <th width="12%" class="text-center">Amount</th>
                <th width="7%" class="text-center">Action</th>                             
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>                        

<div class="form-group row">
    <div class="col-12">
        <div class="input-group">
            <button type="button" class="btn btn-success mr-1" id="add-product">
                <i class="fa fa-plus-square"></i> Add Product
            </button>
            <button type="button" class="btn btn-primary" id="add-title">
                <i class="fa fa-plus-square"></i> Add Title
            </button>
            <a href="javascript:" class="btn btn-warning px-3 ml-2" id="addMisc"><i class="fa fa-plus"></i> Expense & Mtrls Take Off</a>
        </div>
    </div>                            
</div>

<div class="form-group row">
    <div class="col-8">
        {{-- <table id="skill-item" class="table-responsive tfr my_stripe_single">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white">
                    <th class="text-center">#</th>
                    <th width="20%" class="text-center">Skill Type</th>
                    <th width="15%" class="text-center">Charge</th>
                    <th width="15%" class="text-center">Work Hr</th>
                    <th width="15%" class="text-center">No. of Technicians</th> 
                    <th width="15%" class="text-center">Amount</th>
                    <th width="10%" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button type="button" class="btn btn-success mt-1" id="add-skill">
            <i class="fa fa-plus-square"></i> Add Skill
        </button>
        <div class="form-group float-right mt-1">
            <div><label for="budget-total">Total Amount</label></div>
            <div><input type="text" value="0" class="form-control" id="labour-total" name="labour_total" readonly></div>
        </div> --}}
    </div> 
    <div class="col-4">
        <div class="form-group">
            <div><label for="tool">Tools Required and Special Notes</label></div>
            <textarea name="note" id="note" cols="45" rows="6" class="form-control html_editor" required>
                @isset($budget)
                    {{ $budget->note }}
                @endisset
            </textarea>   
        </div>                        
        <div class="form-group">
            <div>
                <label for="quote_total">Quote Total</label>
                <span class="text-danger">(VAT Exc)</span>
            </div>
            {{ Form::text('quote_total', numberFormat($quote->subtotal), ['class' => 'form-control', 'id' => 'quote_total', 'readonly']) }}
        </div>
        <div class="form-group">
            <div>
                <label for="budget-total">Budget Total</label>&nbsp;
                <span class="text-primary font-weight-bold">
                    (E.P: &nbsp;<span class="text-dark profit">0</span>)
                </span>
            </div>
            <input type="text" value="0" class="form-control" id="budget-total" name="budget_total" readonly>
        </div>          
        
        @if (strpos(url()->previous(), 'projects') !== false)
            <a href="{{ url()->previous() }}" class="btn btn-danger btn-lg">{{ trans('buttons.general.cancel') }}</a>
        @else
            {{ link_to_route('biller.projects.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-lg']) }}
        @endif  
        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg']) }}
    </div>                              
</div> 
