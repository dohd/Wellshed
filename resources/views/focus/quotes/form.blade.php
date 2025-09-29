<div class="row">
    <!-- Quote -->
    <div class="col-6">
        <h3 class="form-group">
            @php
                $title_arr = explode(' ', $words['title']);
                $title = implode(' ', [$title_arr[0], ...array_splice($title_arr, 1)]);
            @endphp
            {{ $title }}
        </h3>
        <div class="form-group row">
            <div class="col-md-8">
                <label for="ticket">Ticket</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="form-control" name="lead_id" id="lead_id" data-placeholder="Search Ticket" required> 
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

                                $boq_total = 0;
                                if($lead->boq){
                                    $boq = $lead->boq()->latest()->first();
                                    if($boq) $boq_total = numberFormat($boq->total_boq_amount);
                                }
                            @endphp
                            <option 
                                value="{{ $lead->id }}" 
                                title="{{ $lead->title }}" 
                                client_ref="{{ $lead->client_ref }}"
                                customer_id="{{ $lead->client_id }}"
                                branch_id="{{ $lead->branch_id }}"
                                assign_to="{{ $lead->assign_to }}"
                                currencyId="{{ @$lead->customer->currency_id }}"
                                boqTotal="{{ @$boq_total }}"
                                incomeCategory="{{ @$lead->category }}"
                                {{ $lead->id == @$quote->lead_id ? 'selected' : '' }}
                            >
                                {{ gen4tid("{$prefix}-", $lead->reference) }} - {{ $customer_name }} - {{ $lead->title }}
                            </option>
                        @endforeach                                                                                             
                    </select>
                    <input type="hidden" name="branch_id" id="branch_id" value="0">
                    <input type="hidden" name="customer_id" id="customer_id" value="0">
                    <input type="hidden" value="0" id="credit">
                    <input type="hidden" value="0" id="total_aging">
                    <input type="hidden" value="0" id="outstanding_balance">
                </div>
            </div>
            @if ($classlists->count())
                <div class="col-sm-4">
                    <label for="classlist">Search Class</label>
                    <div class="input-group">
                        <select id="classlist" name="classlist_id" class="form-control" data-placeholder="Choose Class or Subclass">
                            <option value=""></option>
                            @foreach ($classlists as $item)
                                <option value="{{ $item->id }}" {{ $item->id == @$quote->classlist_id? 'selected' : '' }}>
                                    {{ $item->name }} {{ $item->parent_class? '('. $item->parent_class->name .')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>

        <div class="form-group row">
            <div class='col-4'>
                <label for="print_type" >Print Type</label>
                <div>                    
                    <div class="d-inline-block custom-control custom-checkbox mr-1">
                        <input type="radio" class="custom-control-input bg-primary" name="print_type" value="inclusive" id="colorCheck6">
                        <label class="custom-control-label" for="colorCheck6">VAT-Inclusive</label>
                    </div>
                    <div class="d-inline-block custom-control custom-checkbox">
                        <input type="radio" class="custom-control-input bg-purple" name="print_type" value="exclusive" id="colorCheck7" checked>
                        <label class="custom-control-label" for="colorCheck7">VAT-Exclusive</label>
                    </div>
                </div>
            </div>
            
            <div class="col-4">
                <label for="customer">Pre-agreed Pricing</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    <select id="price_customer" name="price_customer_id" class="custom-select">
                        <option value="">Default</option>
                        <option value="0">Maintenace Schedule</option>
                        @foreach($price_customers as $row)
                        <option value="{{ $row->id }}">{{ $row->company }}</option>
                    @endforeach
                    </select>
                </div>
            </div>
            <div class="col-4">
                <label for="income_category" class="caption">Income Category</label>
                <select class="custom-select" name="account_id" id="income_category">
                    <option value="">-- Select Category --</option>                                        
                    @foreach ($income_accounts as $row)

                        @if($row->holder !== 'Stock Gain' && $row->holder !== 'Others' && $row->holder !== 'Point of Sale' && $row->holder !== 'Loan Penalty Receivable' && $row->holder !== 'Loan Interest Receivable')
                            <option value="{{ $row->id }}"  @if($row->id == @$quote->account_id) selected @endif>
                                {{ $row->holder }}
                            </option>
                        @endif

                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-4">
                <label for="attention">Attention</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('attention', null, ['class' => 'form-control round', 'placeholder' => 'Attention', 'id' => 'attention']) }}
                </div>
            </div>
            <div class="col-4">                
                <label for="prepared_by">Prepared By</label>
                <select name="prepared_by_user" id="prepared_by_user" class="form-control">
                @foreach($employees as $employee)                
                    <option value="{{ $employee['id'] }}"
                            @if(@$quote)
                                {{$employee['id'] == @$quote->prepared_by_user ? 'selected' : '' }}
                            @else
                                @if($employee['id'] === \Illuminate\Support\Facades\Auth::user()->id) selected @endif
                            @endif
                    >
                        {{ $employee->fullname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-4">
                <label for="quote_type">{{ $is_pi? 'Proforma Invoice' : 'Quote' }} Type</label>
                <select name="quote_type" id="quote_type" class="custom-select" required>
                    @if (@$quote->quote_type)
                        @foreach (['standard', 'project'] as $val)
                            <option value="{{ $val }}" {{ @$quote->quote_type == $val? 'selected' : '' }}>{{ ucfirst($val) }}</option>
                        @endforeach
                    @elseif (optional(auth()->user()->business)->default_quote_type)
                        @foreach (['standard', 'project'] as $val)
                            <option value="{{ $val }}" {{ optional(auth()->user()->business)->default_quote_type == $val? 'selected' : '' }}>{{ ucfirst($val) }}</option>
                        @endforeach
                    @else 
                        @foreach (['standard', 'project'] as $val)
                            <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                        @endforeach
                    @endif                    
                </select>
            </div>
        </div>        
    </div>

    <!-- Properties -->
    <div class="col-6">
        <h3 class="form-group">{{ $is_pi ? 'Proforma Invoice Properties' : trans('quotes.properties')}}</h3>
         <div class="form-group row">
            <div class="col-4">
                <label for="serial_no" >Quote/Proforma Invoice No.</label>
                <div class="input-group">
                    <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                    @php
                        $tid = isset($words['edit_mode'])? $quote->tid : $lastquote->tid+1;
                        $tid_prefix = !isset($words['edit_mode'])? $prefixes[0] : ($quote->bank_id? $prefixes[1] : $prefixes[0]);
                    @endphp
                    {{ Form::text('tid', gen4tid("{$tid_prefix}-", $tid), ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                    <input type="hidden" name="tid" value="{{ $tid }}">
                </div>
            </div>
            <div class="col-4">
                <label for="date">{{trans('general.date')}}</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('date', null, ['class' => 'form-control round datepicker', 'id' => 'date']) }}
                </div>
            </div> 
            <div class="col-4"><label for="validity" >Validity Period</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="custom-select round" name="validity" id="validity">
                        @php
                            $selected = '';
                        @endphp
                        @foreach ([0, 14, 21, 30, 45, 60, 90, 120] as $val)
                            @php
                                if (isset($quote)) $selected =  $val == @$quote->validity? 'selected' : '';
                                else $selected = $val == 0? 'selected' : '';
                            @endphp
                            <option value="{{ $val }}" {{ $selected }}>
                                {{ $val ? 'Valid For '.$val.' Days' : 'On Receipt' }}
                            </option>
                        @endforeach                                                
                    </select>
                </div>
            </div>
            </div>

        <div class="form-group row">
            <div class="col-2">
                <label for="currency">Currency</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="custom-select" name="currency_id" id="currency" data-placeholder="{{trans('tasks.assign')}}" required>
                        <option value="">-- Currency --</option>
                        @foreach ($currencyList as $currency)
                            <option value="{{ $currency->id }}" rate="{{ floatval($currency->rate) }}"
                                    @if (empty(@$quote) && $currency->code == 'KES')
                                        selected
                                    @elseif(!empty(@$quote) && $currency->id == @$quote->currency_id)
                                        selected
                                    @endif
                            >
                                {{ $currency->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-2"><label for="client_ref">Client Ref / Callout ID</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('client_ref', null, ['class' => 'form-control round', 'id' => 'client_ref']) }}
                </div>
            </div> 
            <div class="col-2">
                <label for="type" >Template Type</label>
                <select class="custom-select" name="template_type" id="template_type">
                    <option value="">-- Select Template Type --</option>
                    <option value="bom" {{"bom" == @$quote->template_type ? 'selected' : ''}}>MTO</option>   
                    <option value="template_quote" {{"template_quote" == @$quote->template_type ? 'selected' : ''}}>Template Quote</option>                                         
                </select>
            </div>      
            <div class="col-6 div_template_quote">
                <label for="template-quotes" >Template Quote</label>
                <select class="custom-select" name='template_quote_id' id="template_quote_id">
                    <option value="">-- Select Template Quote --</option>
                    @foreach ($templateQuotes as $templateQuote)
                    <option value="{{ $templateQuote->id }}" {{ $templateQuote->id == @$quote->templateQuote ? 'selected' : '' }}>
                        {{ $templateQuote->notes }}
                    </option>
                    @endforeach                                            
                </select>
            </div>  
            <div class="col-6 d-none div_bom">
                <label for="boms">BOM / MTO</label>
                <select name="bom_id" id="bom" class="form-control" data-placeholder="Search BoM / MTO">
                    <option value="">Search BoM / MTO</option>
                </select>
            </div>                                                                  
        </div>
        <div class="form-group row">
            <div class="col-4">
                <label for="terms">Terms</label>
                <select id="term_id" name="term_id" class="custom-select" required>
                    <option value="">-- Select Term --</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $term->id == @$quote->term_id ? 'selected' : '' }}>
                            {{ $term->title }}
                        </option>
                    @endforeach
                </select>               
            </div>
            <div class="col-4">
                <label for="taxFormat">Tax</label>
                <select class="custom-select" name='tax_id' id="tax_id">
                    @foreach ($additionals as $row)
                        <option value="{{ +$row->value }}"
                            @if(empty(@$quote) && $row->is_default == 1)
                                selected
                            @elseif(!empty(@$quote) && round($row->value) == @$quote->tax_id)
                                selected
                            @endif
                        >
                            {{ $row->name }}
                        </option>
                    @endforeach                                            
                </select>
                <input type="hidden" name="tax_format" value="exclusive" id="tax_format">
            </div>
            

            @if (isset($banks))
                <div class="col-3">
                    <label for="bank" >Bank</label>
                    <select class="custom-select" name='bank_id' id="bank_id" required>
                        <option value="">-- Select Bank --</option>
                        @foreach ($banks as $bank)
                        <option value="{{ $bank->id }}" {{ $bank->id == @$quote->bank_id ? 'selected' : '' }}>
                            {{ $bank->bank }} - {{ $bank->note }}
                        </option>
                        @endforeach                                            
                    </select>
                </div>
            @endif
        </div>
    </div>                        
</div>
<div class="form-group row">
    @if (isset($revisions))
        <div class="col-8">
            <label for="subject" >Subject / Title</label>
            {{ Form::text('notes', null, ['class' => 'form-control', 'id' => 'subject', 'required']) }}
        </div>
        <div class="col-2">
            <label for="revision" >Revision</label>
            <select class="custom-select" name='revision' id="rev">
                <option value="">-- Select Revision --</option>
                @foreach ($revisions as $val)
                    <option value="_r{{ $val }}" {{ @$quote->revision == '_r'.$val ? 'selected' : '' }}>
                        R{{ $val }}
                    </option>
                @endforeach                                            
            </select>
        </div>
        <div class="col-2">
            <label for="">Unapproved Reminder Date</label>
            {{ Form::text('unapproved_reminder_date', null, ['class' => 'form-control datepicker', 'id' => 'unapproved_reminder_date' ]) }}

        </div>
    @else
        <div class="col-8">
            <label for="subject" >Subject / Title</label>
            {{ Form::text('notes', null, ['class' => 'form-control', 'id' => 'subject', 'placeholder' => 'Subject or Title', 'required']) }}
        </div>
        <div class="col-2">
            <label for="">Total BoQ Amount</label>
            <input type="text" id="total_boq_amount" class="form-control" disabled>
        </div>
        <div class="col-2">
            <label for="">Unapproved Reminder Date</label>
            {{ Form::text('unapproved_reminder_date', null, ['class' => 'form-control datepicker', 'id' => 'unapproved_reminder_date' ]) }}

        </div>
    @endif
</div>
<div class="form-group row">
    <div class="col-4">
        <label for="">Add Image to Header/Footer</label>
        <input type="checkbox" id="check">
    </div>
</div>
<div class="form-group row add-image d-none">
    <div class="col-4">
        <label for="">Search Product</label>
        <select name="productvar_id" id="productvar" class="form-control" disabled>
            <option value="">Search Product</option>
            @foreach ($products as $product)
                <option value="{{$product->id}}" data-image="{{$product->image}}" {{@$quote->productvar_id == $product->id ? 'selected' : ''}}>{{$product->name}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label for="">Display Image</label><br>
        <img id="dynamicImage" src="" alt="Image" class="img-fluid border rounded" style="max-height: 200px; object-fit: cover;" />
    </div>
    <div class="col-4">
        <label for="">Where Image will appear</label>
        <select name="appear_image" id="appear_image" class="form-control" disabled>
            <option value="">--select where image will appear--</option>
            @foreach (['header','footer'] as $item)
                <option value="{{$item}}" {{@$quote->appear_image == $item ? 'selected' : ''}}>{{$item}}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <div class="col-4">
        <label for="">Remove UoM from Printing</label>
        <input type="checkbox" id="removeUoM">
    </div>
</div>
<div class="form-group row uom-remove d-none">
    <div class="col-3">
        <label for="">uom printing status</label>
        <select name="uom_status" id="uom_status" class="form-control" disabled>
            <option value="">--select uom printing status--</option>
            @foreach (['no','yes'] as $item)
                <option value="{{$item}}" {{@$quote->uom_status == $item ? 'selected' : ''}}>{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
</div>
<!-- quotes item table -->
{{-- @include('focus.quotes.partials.quote-items-table') --}}
@include('focus.quotes.partials.quote_items')
<!-- footer -->
<div class="form-group row">
    <div class="col-9">
        <a href="javascript:" class="btn btn-success" id="addProduct"><i class="fa fa-plus-square"></i> Add Product</a>
        <a href="javascript:" class="btn btn-primary" id="addTitle"><i class="fa fa-plus-square"></i> Add Title</a>
        <a href="javascript:" class="btn btn-secondary ml-1 d-none" data-toggle="modal" data-target="#skillModal" id="addSkill">
            <i class="fa fa-wrench"></i> Labour
        </a>
        <a href="javascript:" class="btn btn-warning" id="addMisc"><i class="fa fa-plus"></i> Expense & Mtrls Take Off</a>
        <a href="javascript:" class="btn btn-purple ml-1" data-toggle="modal" data-target="#extrasModal" id="addExtras">
            <i class="fa fa-plus"></i> Header & Footer
        </a>
        <div class="form-group row mt-2">
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
                    {{ Form::text('reference', null, ['class' => 'form-control round', 'placeholder' => 'Site Survey Report Reference', 'id' => 'reference']) }}
                </div>
            </div>
            <div class="col-4">
                <label for="reference_date" >Site Survey Report Reference Date</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('reference_date', null, ['class' => 'form-control round datepicker', 'id' => 'referencedate' ]) }}
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class='col-md-12'>
                <div class='col'>
                                            
                    <input type="checkbox" id="add-check" value="checked">
                    <label for="client-type" class="font-weight-bold">Attach Repair Equipment</label>
                </div>
            </div>
        </div>
        @include('focus.quotes.partials.equipments')
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
        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }}
    </div>
</div>
<!-- repair or maintenance type  -->
@if (request('doc_type') == 'maintenance') 
    {{ Form::hidden('is_repair', 0) }}
@endif
@include('focus.quotes.partials.skillset-modal')
@include('focus.quotes.partials.extras_modal')