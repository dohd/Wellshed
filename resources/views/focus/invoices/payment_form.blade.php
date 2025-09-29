<div class="row mb-1">
    <div class="col-6">
        <label for="customer" class="caption">Search Customer</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select id="person" name="customer_id" class="form-control select-box" data-placeholder="Search Customer" required>
                @isset ($payment)
                    <option value="{{ $payment->customer_id }}">{{ $payment->customer->company }}</option>
                @endisset
            </select>
        </div>
    </div>

    <div class="col-2">
        <label for="reference" class="caption">Payment No.</label>
        <div class="input-group">
            {{ Form::text('tid', @$payment ? $payment->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
        </div>
    </div> 

    <div class="col-2">
        <label for="date" class="caption">Date</label>
        <div class="input-group">
            {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
        </div>
    </div>     
    
    <div class="col-2">
        <label for="type">Payment Type</label>
        <select name="payment_type" id="payment_type" class="custom-select">
            @foreach (['per_invoice', 'on_account', 'advance_payment'] as $val)
                <option value="{{ $val }}" {{ $val == @$payment->payment_type? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $val)) }}
                </option>
            @endforeach
        </select>
    </div>    
</div> 

<div class="form-group row">  
    <div class="col-2">
        <label for="amount" class="caption">Amount</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div>     
    <div class="col-4">
        <label for="account">Payment Account</label>
        <select name="account_id" id="account" class="custom-select" required>
            <option value="">-- Select Account --</option>
            @foreach ($accounts as $row)

                @if($row->holder !== 'Stock Gain' && $row->holder !== 'Others' && $row->holder !== 'Point of Sale' && $row->holder !== 'Loan Penalty Receivable' && $row->holder !== 'Loan Interest Receivable')
                    <option value="{{ $row->id }}" {{ $row->id == @$payment->account_id? 'selected' : '' }}>
                        {{ $row->holder }}
                    </option>
                @endif

            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="payment_mode">Mode</label>
        <select name="payment_mode" id="payment_mode" class="custom-select" required>
            <option value="">-- Select Mode --</option>
            @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                <option value="{{ $val }}" {{ $val == @$payment->payment_mode? 'selected' : '' }}>
                    {{ strtoupper($val) }}
                </option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="reference" class="caption">Reference No.</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
    </div>                                              
</div>
<div class="row form-group">
    <div class="col-6">
        <label for="payment">Allocate Payment</label>
        <select id="payment" name="payment_id" class="form-control" data-placeholder="Search Payment" disabled>
            <option value="0">None</option>
        </select>
    </div>   
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Due Date</th>
                <th>Invoice No</th>
                <th>Note</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Outstanding</th>
                <th>Allocate</th>
            </tr>
        </thead>
        <tbody>   
            @isset ($payment)
                @foreach ($payment->items as $row)
                    @php
                        $invoice = $row->invoice;
                        if (!$invoice) continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($invoice->invoiceduedate) }}</td>
                        <td>{{ gen4tid('Inv-', $invoice->tid) }}</td>
                        <td>{{ $invoice->notes }}</td>
                        <td>{{ $invoice->status }}</td>
                        <td class="inv-amount">{{ numberFormat($invoice->total) }}</td>
                        <td>{{ numberFormat($invoice->amountpaid) }}</td>
                        <td class="due"><b>{{ numberFormat($invoice->total - $invoice->amountpaid) }}<b></td>
                        <td><input type="text" class="form-control paid" name="paid[]" value="{{ numberFormat($row->paid) }}"></td>
                        <input type="hidden" name="id[]" value="{{ $row->id }}">
                    </tr>
                @endforeach
            @endisset                             
            <tr class="bg-white">
                <td colspan="6"></td>
                <td colspan="2">
                    <div class="col-6 float-right">
                        <label for="total_paid">Total Allocated</label>
                        {{ Form::text('allocate_ttl', null, ['class' => 'form-control ml-1', 'id' => 'allocate_ttl', 'readonly']) }}
                    </div>                                         
                    <div class="col-6 float-right">
                        <label for="total_bill">Total Balance</label>
                        {{ Form::text('balance', null, ['class' => 'form-control ml-1', 'id' => 'balance', 'disabled']) }}
                    </div>
                </td>
            </tr>
        </tbody>                
    </table>
</div>
<div class="form-group row">                            
    <div class="col-12">  
        {{ Form::submit(@$payment? 'Update Payment' : 'Receive Payment', ['class' =>'btn btn-primary btn-lg float-right mr-3']) }}
    </div>
</div>