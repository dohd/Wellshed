<div class="row form-group">
    <div class="col-6">
        <label for="customer">Search Customer</label>
        <select id="customer" name="customer_id" class="form-control" data-placeholder="Search Customer" required>
            <option value=""></option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" {{ $customer->id == @$creditnote->customer_id? 'selected' : '' }}>
                    {{ $customer->company }}
                </option>
            @endforeach
        </select>
    </div>                            
    <div class="col-1">
        <label for="reference" class="caption">#WH No.</label>
        {{ Form::text('tid', @$last_tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly' => 'readonly']) }}
    </div> 
    <div class="col-1">
        <label for="certificate" class="caption">Cert. Type</label>
        <select name="certificate" id="certificate" class="custom-select">
            @foreach (['vat', 'tax'] as $val)
                <option value="{{ $val }}">{{ strtoupper($val) }}</option>
            @endforeach                                    
        </select>
    </div>  
    <div class="col-2">
        <label for="date" class="caption">Cert. Date</label>
        {{ Form::text('cert_date', null, ['class' => 'form-control datepicker', 'id' => 'cert_date', 'required' => 'required']) }}
    </div>   
    <div class="col-2">
        <label for="reference" class="caption">Cert. Serial No.</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required' => 'required']) }}
    </div>                                                                                                    
</div> 
<div class="row form-group">  
    <div class="col-6">
        <label for="note" class="caption">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'placeholder' => 'e.g Gross Amount & Tax Rate', 'id' => 'note']) }}
    </div>    
    <div class="col-2">
        <label for="date" class="caption">Payment Date</label>
        {{ Form::text('tr_date', null, ['class' => 'form-control datepicker', 'id' => 'tr_date', 'required' => 'required']) }}
    </div>                          
                
    <div class="col-2">
        <label for="amount" class="caption">Amount</label>
        {{ Form::text('amount', null, ['class' => 'form-control cash', 'id' => 'amount', 'required' => 'required']) }}
    </div>                                           
</div>
<div class="row form-group mt-4 mb-1">
    <div class="col-6">
        <label for="withholding" class="mb-0">Allocate Balance (WH. Tax)</label>
        <select id="withholding_cert" name="withholding_tax_id" class="form-control custom-select" style="height:2em" disabled>
            <option value="">None</option>
        </select>
    </div>   
</div>

<div class="table-responsive mb-2" style="max-height: 80vh">
    <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Due Date</th>
                <th>#Invoice No</th>
                <th width="40%">Note</th>
                <th>Status</th>
                <th>Original Amt.</th>
                <th>Amt. Paid</th>
                <th>Amt. Due</th>
                <th>Amt. Allocated</th>
            </tr>
        </thead>
        <tbody>                                
        </tbody>                
    </table>
</div>

<div class="row">
    <div class="col-2 ml-auto">
        <label for="total_paid" class="mb-0">Total Allocated Amount</label>
        {{ Form::text('allocate_ttl', 0, ['class' => 'form-control', 'id' => 'allocate_ttl', 'readonly' => 'readonly']) }}
    </div>   
</div>
<div class="row">
    <div class="col-2 ml-auto">
        <label for="total_paid" class="mb-0">Total Unallocated Amount</label>
        {{ Form::text('unallocate_ttl', 0, ['class' => 'form-control', 'id' => 'unallocated_ttl', 'disabled' => 'disabled']) }}
    </div>   
</div>

                           
<div class="edit-form-btn row mt-2">
    {{ link_to_route('biller.withholdings.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
    {{ Form::submit(@$withholding? 'Update' : 'Create', ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
</div>
