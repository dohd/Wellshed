<div class="card">
    <div class="card-body">
        @php
            $label = $quote->bank_id ? 'PI' : 'Quote';
            $prefixes = prefixesArray(['quote', 'proforma_invoice'], $quote->ins);
        @endphp
        {{ Form::hidden('id', $quote->id) }}
        <div class="row">
            <div class="col-6 cmp-pnl">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th width="40%">{{$label}} Serial</th>
                            <td class="pl-1">
                                @php
                                    $tid = gen4tid($label == 'PI'? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                                    echo $tid . $quote->revision;
                                @endphp                                
                            </td>
                        </tr>
                        <tr>
                            <th width="40%">{{$label}} Date</th>
                            <td class="pl-1">{{ dateFormat($quote->date) }}</td>
                        </tr>
                        <tr>
                            <th width="40%">Customer</th>
                            <td class="pl-1">{{ implode(' - ', array_filter([@$quote->client->name, @$quote->branch->name])) }}</td>
                        </tr>
                        <tr>
                            <th width="40%">Subject / Title</th>
                            <td class="pl-1">{{ $quote->notes }}</td>
                        </tr>                        
                        <tr>
                            <th width="40%">Client Ref / Callout ID</th>
                            <td class="pl-1">{{ $quote->client_ref }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        
            <div class="col-6 cmp-pnl">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th width="40%">Djc Reference</th>
                            <td class="pl-1">{{ $quote->reference }}</td>
                        </tr>
                        <tr>
                            <th width="40%">Reference Date</th>
                            <td class="pl-1">{{ $quote->reference_date? dateFormat($quote->reference_date) : '' }}</td>
                        </tr>
                        <tr>
                            <th width="40%">{{$label}} Taxable</th>
                            <td class="pl-1">{{ numberFormat($quote->taxable > 0? $quote->taxable : $quote->subtotal) }}</td>
                        </tr>
                        <tr>
                            <th width="40%">{{$label}} Subtotal</th>
                            <td class="pl-1">{{ numberFormat($quote->subtotal) }}</td>
                        </tr>
                        <tr>
                            <th width="40%">{{$label}} Total</th>
                            <td class="pl-1">{{ numberFormat($quote->total) }}</td>
                        </tr>
                    </tbody>
                </table>                
            </div>                        
        </div>  
        <div class="row">
            <div class="col-md-6">                
                <div class="form-group row">
                    <div class="col-12">
                        <label for="subject" class="caption">Users notified on verification</label>
                        <select name="user_id[]" id="user" class="form-control" data-placeholder="Search Employee" multiple>
                            <option value="">Search Employee</option>
                            @foreach ($employees as $user)
                                <option value="{{$user->id}}">{{$user->fullname}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>                    
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="{{ $quote->quote_type == 'project'? 'col-9' : 'col-12' }}">
                        <label for="gen_remark" class="caption">General Remark</label>
                        {{ Form::text('gen_remark', null, ['class' => 'form-control', 'id' => 'gen_remark']) }}
                    </div>
                    @if ($quote->quote_type == 'project')
                        <div class="col-3">
                            <label for="project_closure_date" class="caption">Project Closure Date</label>
                            {{ Form::text('project_closure_date', null, ['class' => 'form-control datepicker', 'id' => 'project_closure_date', 'placeholder' => date('d-m-Y'), 'required' => 'required']) }}
                        </div>
                    @endif
                </div>   
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-danger" id="reset-items" {{ $quote->verified != 'Yes'? 'disabled' : '' }}>
                        <i class="fa fa-trash"></i> Reset Verification
                    </button>
                </div>
            </div>
        </div>                
    </div>
</div> 

<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs nav-top-border nav-justified" role="tablist">
            <li class="nav-item">
                <a class="nav-link active h4 pt-1" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
                    Order Items
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link h4 pt-1" id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
                    Expenses
                </a>
            </li>
        </ul>

        <!-- Content -->
        <div class="tab-content pt-1 mb-1">
            <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                <div class="row mb-1">
                    <div class="col-4 ml-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="hasJobcard"> 
                            <label class="form-check-label" for="hasJobcard">Include Job-cards / D-Notes</label>
                        </div>                    
                    </div>
                </div>                
                <!-- jobcards/dnote -->
                @include('focus.quotes_verification.partials.jobcards')
                <!-- products table -->
                @include('focus.quotes_verification.partials.order_items')                    
            </div>
            <div class="tab-pane in" id="active2" aria-labelledby="active-tab2" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <!-- Material Expense -->
                        @include('focus.quotes_verification.partials.material_expense')
                        <!-- Direct Expense -->
                        @include('focus.quotes_verification.partials.service_expense')
                    </div>
                </div>
            </div>
        </div> 

        <!-- Verification Summary -->
        @include('focus.quotes_verification.partials.summary')  
        <div class="row form-group">
            <div class="col-3">

            </div>
            <div class="col-6">
                <div class="table-responsive">
                    <table id="profitTbl" class="table table-bordered text-center">
                        <thead>
                            <th width="50%">&nbsp;</th>
                            <th>Total</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td><b> Current Profit Amount</b></td>
                                <td><span class="profit"></span></td>                   
                            </tr>
                            <tr>
                                <td><b> Percentage Profit</b></td>
                                <td><span class="percent_profit"></span>%</td>                   
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-3"></div>
        </div>
        <div class="edit-form-btn row mt-1">
            {{ link_to_route('biller.quotes.verification', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
            {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
        </div>
    </div>
</div>

<!-- attach labour modal -->
@include('focus.quotes_verification.modals.attach_labour_modal') 

@section('extra-scripts')
@include('focus.quotes_verification.form_js')
@endsection