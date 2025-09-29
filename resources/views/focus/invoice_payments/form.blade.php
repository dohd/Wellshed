<div class="card">
    <div class="card-content">
        <div class="card-body mb-0 pb-0">
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="customer" class="caption">Search Customer</label>
                    <div class="input-group">
                        <select id="customer" name="customer_id" class="form-control select-box" data-placeholder="Search Customer" required>
                            <option value=""></option>
                            @foreach ($customers as $customer)
                                <option 
                                    {{ $customer->id == @$invoice_payment->customer_id? 'selected' : '' }} 
                                    value="{{ $customer->id }}" 
                                    currencyId="{{ $customer->currency_id }}"
                                    currencyCode="{{ @$customer->currency->code }}"
                                    currencyRate="{{ @$customer->currency->rate }}"
                                    phoneNumber="{{ $customer->phone }}"
                                    email="{{ $customer->email }}"
                                >
                                    {{ $customer->company }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="type">Payment Type</label>
                    <select name="payment_type" id="payment_type" class="custom-select">
                        @foreach (['per_invoice', 'on_account', 'advance_payment'] as $val)
                            <option value="{{ $val }}" {{ $val == @$invoice_payment->payment_type? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $val)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="reference" class="caption">#Serial No.</label>
                    <div class="input-group">
                        {{ Form::text('tid', @$tid, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
                    </div>
                </div> 
                <div class="col-md-2">
                    <label for="date" class="caption">Date</label>
                    <div class="input-group">
                        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => date('d-m-Y'), 'id' => 'date', 'required' => 'required']) }}
                    </div>
                </div>   
                <div class="col-md-2">            
                    <label for="currency_code">Currency Rate</label>
                    <div class="row no-gutters">
                        <div class="col-md-6"> 
                            {{ Form::text(null, null, ['class' => 'form-control', 'id' => 'currency', 'disabled' => 'disabled']) }}
                            {{ Form::hidden('currency_id', null, ['id' => 'currency-id']) }}
                        </div>
                        <div class="col-md-6">
                            {{ Form::text('fx_curr_rate', null, ['class' => 'form-control', 'id' => 'fx_curr_rate', 'required' => 'required', 'readonly' => 'readonly']) }}
                        </div>
                    </div>
                </div>    
            </div> 
            <div class="form-group row">  
                <div class="col-md-4">
                    <label for="account">Receive On Bank (Ledger Account)</label>
                    <select name="account_id" id="account" class="custom-select" required>
                        <option value="">-- Select Account --</option>
                            @foreach ($accounts as $row)
                                <option value="{{ $row->id }}" currencyId="{{ $row->currency_id }}">
                                    {{ $row->holder }}
                                </option>
                            @endforeach
                    </select>
                </div>     
                <div class="col-md-2">
                    <label for="payment_mode">Payment Mode</label>
                    <select name="payment_mode" id="payment_mode" class="custom-select" required>
                        <option value="">-- Select Mode --</option>
                        @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque','pesalink'] as $val)
                            <option value="{{ $val }}">{{ strtoupper($val) }}</option>
                        @endforeach
                    </select>
                </div>  
                <div class="col-md-2">
                    <label for="reference" class="caption">Reference No.</label>
                    {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
                </div>       
                <div class="col-md-2">
                    <label for="amount" class="caption">Amount</label>
                    {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
                </div>                                         
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <label for="note">Note</label>
                    {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
                </div>  
                <div class="col-md-2"></div>
                <div class="col-md-4 project-container d-none">
                    <label for="project">Search Project</label>
                    <select id="project" name="project_id" class="form-control" data-placeholder="Search Project">
                        <option value=""></option>
                        @if (@$invoice_payment->project)
                            @php $project = $invoice_payment->project @endphp
                            <option value="{{ $project->id }}" selected>
                                {{ gen4tid('PRJ-', $project->tid) }}-{{ $project->name }}
                            </option>
                        @endif
                    </select>
                </div>
            </div> 
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="payment">Allocate Balance (On Account & Advance)</label>
                    <select id="rel_payment" name="rel_payment_id" class="form-control custom-select" style="height:2em;" disabled>
                        <option value="">None</option>
                    </select>
                </div> 
                {{-- <div class="col-2">
                    <label for="send_link">Send Payment Receipt</label>
                    <select name="send_link" id="send_link" class="form-control col-3">
                        <option value="no" {{@$invoice_payment->send_link == 'no' ? 'selected' : ''}}>No</option>
                        <option value="yes" {{@$invoice_payment->send_link == 'yes' ? 'selected' : ''}}>Yes</option>
                    </select>
                </div> 
                <div class="col-2 div_phone d-none" >
                    <label for="phone_number">Phone Number</label>
                    <input type="text" name="phone_number" value="{{@$invoice_payment->phone_number}}" id="phone_number" class="form-control">
                </div> 
                <div class="col-2 div_email d-none">
                    <label for="email">Email</label>
                    <input type="text" name="email" value="{{@$invoice_payment->email}}" id="email" class="form-control">
                </div>  --}}
            </div>

            <div class="table-responsive mb-2" style="max-height: 80vh">
                <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th>Due Date</th>
                            <th>#Invoice No.</th>
                            <th width="25%">Note</th>
                            <th>Status</th>
                            <th>Original Amt.</th>
                            <th>Amt. Paid</th>
                            <th>Amt. Due</th>
                            <th width="10%">Amt. Allocated</th>
                            <th width="10%">WH. VAT</th>
                            <th width="10%">WH. TAX</th>
                        </tr>
                    </thead>
                    <tbody>   
                        @isset ($invoice_payment)
                            @foreach ($invoice_payment->items as $row)
                                @php $invoice = $row->invoice @endphp
                                @if ($invoice)    
                                    <tr>
                                        <td>{{ dateFormat($invoice->invoiceduedate) }}</td>
                                        <td>{{ gen4tid('', $invoice->tid) }}</td>
                                        <td>{{ $invoice->notes }}</td>
                                        <td>{{ $invoice->status }}</td>
                                        <td class="inv-amount">{{ numberFormat($invoice->total) }}</td>
                                        <td>{{ numberFormat($invoice->amountpaid) }}</td>
                                        <td class="due"><b>{{ numberFormat($invoice->total - $invoice->amountpaid) }}<b></td>
                                        <td><input type="text" class="form-control paid" name="paid[]" value="{{ numberFormat($row->paid) }}"></td>
                                        <td><input type="text" class="form-control wh-vat" name="wh_vat[]" value="{{ numberFormat($row->wh_vat) }}"></td>
                                        <td><input type="text" class="form-control wh-tax" name="wh_tax[]" value="{{ numberFormat($row->wh_tax) }}"></td>
                                        <input type="hidden" name="invoice_id[]" value="{{ $row->invoice_id }}">
                                        <input type="hidden" name="id[]" value="{{ $row->id }}">
                                    </tr>
                                @endif
                            @endforeach
                        @endisset
                    </tbody>                
                </table>
            </div>

            <div class="row">
                <div class="col-md-2 ml-auto">
                    <label for="total_paid" class="mb-0">Total Allocated Amount</label>
                    {{ Form::text('allocate_ttl', null, ['class' => 'form-control', 'id' => 'allocate_ttl', 'readonly']) }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 ml-auto">
                    <label for="total_paid" class="mb-0">Total Unallocated Amount</label>
                    {{ Form::text('unallocate_ttl', null, ['class' => 'form-control', 'id' => 'unallocate_ttl', 'disabled']) }}
                </div>
            </div>
            
            <input type="hidden" name="wh_vat_amount" id="wh-vat-amount">
            <input type="hidden" name="wh_tax_amount" id="wh-tax-amount">

            <div class="edit-form-btn row mt-2">
                {{ link_to_route('biller.invoice_payments.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                {{ Form::submit(@$invoice_payment? 'Update Payment' : 'Receive Payment', ['class' => 'btn btn-primary btn-md mr-2']) }}                                           
            </div>
        </div>
    </div>
</div>
