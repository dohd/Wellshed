@php
    $label = $quote->bank_id ? 'PI' : 'Quote';
    $prefixes = prefixesArray(['quote', 'proforma_invoice'], $quote->ins);
@endphp

<div class="card">
    <div class="card-content">
        <div class="card-body pb-0">
            <input type="hidden" name="quote_id" value="{{ $quote->id }}">
            <div class="row mb-1">
                <div class="col-2">                                        
                    <label for="serial_no" class="caption mb-0">Project No.</label>
                    <div class="input-group">
                        {{ Form::text('tid_prj', gen4tid('PRJ-', @$quote->project->tid), ['class' => 'form-control', 'id' => 'tid', 'disabled']) }}
                    </div>
                </div>  
                <div class="col-2">                                        
                    <label for="serial_no" class="caption mb-0">Quote/PI No.</label>
                    <div class="input-group">
                        @php $tid = gen4tid($label == 'PI'? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid); @endphp
                        {{ Form::text('tid', $tid . $quote->revision, ['class' => 'form-control', 'id' => 'tid', 'disabled']) }}
                    </div>
                </div>  
                <div class="col-3">
                    <label for="client" class="caption mb-0">Customer - Branch</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client', @$quote->customer->name . ' - ' . @$quote->branch->name, ['class' => 'form-control round', 'id' => 'client', 'disabled']) }}
                        <input type="hidden" name="customer_id" value="{{ @$quote->customer_id }}" id="client_id">
                        <input type="hidden" name="branch_id" value="{{ @$quote->branch_id }}" id="branch_id">
                    </div>
                </div>
                <div class="col-5">
                    <label for="subject" class="caption mb-0">Subject / Title</label>
                    {{ Form::text('notes', @$quote->notes, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
                </div>
            </div>
            
            <div class="row mb-1">
                <div class="col-2">
                    <label for="date" class="caption mb-0">Date</label>
                    {{ Form::text('valuation_date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
                </div>
                <div class="col-10">
                    <label for="note" class="caption mb-0">Note</label>
                    {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
                </div>
            </div>  
            
            <div class="row mb-1">
                <div class="col-4">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="hasJobcard"> 
                        <label class="form-check-label" for="hasJobcard">Has Job-cards / D-Notes</label>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jobcards / dnotes -->
@include('focus.job_valuations.partials.jobcards')

<!-- Order Items and Materials -->
<div class="card">
    <div class="card-content">
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
            <div class="tab-content px-1 pt-1 mb-1">
                <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                    <!-- Order Items -->
                    @include('focus.job_valuations.partials.order_items')                    
                </div>
                <div class="tab-pane in" id="active2" aria-labelledby="active-tab2" role="tabpanel">
                    <!-- Material Expense -->
                    @include('focus.job_valuations.partials.material_expense')
                    <!-- Direct Expense -->
                    @include('focus.job_valuations.partials.service_expense')
                </div>
            </div>  
            <div class="form-group row mb-1">
                <div class="col-md-3">
                    <div class="row no-gutters">
                        <div class="col-md-6">
                            <label for="retention">% Retention</label>
                            {{ Form::text('perc_retention', null, ['class' => 'form-control', 'id' => 'percRetention', 'style' => 'min-width: 120px;']) }}
                        </div>
                        <div class="col-md-6">
                            <label for="retentionAmount">Retention Amount</label>
                            {{ Form::text('retention', null, ['class' => 'form-control', 'id' => 'retention', 'style' => 'min-width: 120px;']) }}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="retention">Retention Note</label>
                    {{ Form::text('retention_note', null, ['class' => 'form-control', 'id' => 'retentionNote']) }}
                </div>
            </div>
            <!-- Valuation Summary -->
            @include('focus.job_valuations.partials.summary')   
            @include('focus.job_valuations.partials.upload_interim_cert')   
            
            <div class="edit-form-btn mt-1">
                {{ link_to_route('biller.job_valuations.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 mr-2 ml-1']) }}
                {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-md col-1 mr-auto']) }}                                           
            </div>       
        </div>
    </div>
</div>

@section('after-scripts')
@include('focus.job_valuations.form_js')
@endsection
