<div class="form-group row">
    <div class="col-4">
        <label for="supplier">Supplier</label>
        <select name="supplier_id" id="supplier" class="form-control select2" data-placeholder="Choose supplier" required>
            <option value=""></option>
            @foreach ($suppliers as $row)
                <option value="{{ $row->id }}" currencyId="{{ $row->currency_id }}" {{ @$billpayment && $billpayment->supplier_id == $row->id? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label for="employee">Employee</label>
        <select name="employee_id" id="employee" class="form-control select2" data-placeholder="Choose Employee" required>
            <option value=""></option>
            @foreach ($employees as $row)
                <option value="{{ $row->id }}" {{ @$billpayment && $billpayment->employee_id == $row->id? 'selected' : '' }}>
                    {{ $row->fullname }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="tid" class="caption">Remittance No.</label>
        {{ Form::text('tid', @$billpayment ? $billpayment->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
    <div class="col-2">
        <label for="type">Payment Type</label>
        <select name="payment_type" id="payment_type" class="custom-select" {{ @$billpayment? 'disabled' : '' }}>
            @foreach (['per_invoice', 'on_account', 'advance_payment'] as $val)
                <option value="{{ $val }}" {{ $val == @$billpayment->payment_type? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $val)) }}
                </option>
            @endforeach
        </select>
    </div>  
</div> 

<div class="form-group row">
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div> 

    <div class="col-2">
        <label for="payment_mode">Payment Mode</label>
        <select name="payment_mode" id="payment_mode" class="custom-select">
            @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque','pesalink'] as $val)
                <option value="{{ $val }}" {{ @$billpayment->payment_mode == $val? 'selected' : '' }}>{{ strtoupper($val) }}</option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="reference">Reference</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
    </div>  
    
    <div class="col-2">
        <label for="account">Pay From Account</label>
        <select name="account_id" id="account" class="custom-select" required>  
            <option value="">-- select account --</option>                                 
            @foreach ($accounts as $row)
                @if($row->holder !== 'Stock Gain' && $row->holder !== 'Others' && $row->holder !== 'Point of Sale' && $row->holder !== 'Loan Penalty Receivable' && $row->holder !== 'Loan Interest Receivable')
                    <option value="{{ $row->id }}" currencyId="{{ $row->currency_id }}" systemCode="{{ $row->system }}" {{ $row->id == @$billpayment->account_id? 'selected' : '' }}>
                        {{ $row->holder }}
                    </option>
                @endif
            @endforeach
        </select>
    </div>  
    <div class='col-2 d-none div_user_type'>
        {{ Form::label( 'user_type', 'Select User Type',['class' => 'col-12 control-label']) }}
            <select name="user_type" id="user_type" class="round form-control">
            <option value="">--select user type--</option>
            <option value="employee" {{ @$billpayment->user_type == 'employee' ? 'selected' :'' }}>Employee</option>
            <option value="casual" {{ @$billpayment->user_type == 'casual' ? 'selected' :'' }}>Casual Labourer</option>
            <option value="third_party_user" {{ @$billpayment->user_type == 'third_party_user' ? 'selected' :'' }}>Third Party User</option>
        </select>
    </div>
    <div class="col-2">
        @php
            $disabled = ((@$is_allocated_pmt) || (@$is_next_allocation))? 'disabled' : '';
        @endphp
        <label for="amount" class="caption">Amount</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required', $disabled]) }}
    </div>     
</div>

<div class="row form-group">
    <div class="col-4 div_employee d-none">
        <label for="employee">Search Employee</label>
        <select name="petty_employee_id" id="petty_employee" class="form-control select2" data-placeholder="Search Employee">
            <option value="">Search Employee</option>
            @foreach ($users as $employee)
                <option value="{{ $employee->id }}" {{ $employee->id == @$billpayment->petty_employee_id ? 'selected' : '' }}>{{ $employee->fullname }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4 div_casual d-none">
        <label for="casual">Search Casual Labourer</label>
        <select name="casual_id" id="casual" class="form-control select2" data-placeholder="Search Casual Labourer">
            <option value="">Search Casual Labourer</option>
            @foreach ($casuals as $casual)
                <option value="{{ $casual->id }}" {{ $casual->id == @$billpayment->casual_id ? 'selected' : '' }}>{{ $casual->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4 div_third_party_user d-none">
        <label for="third_party_user">Search Third Party User</label>
        <select name="third_party_user_id" id="third_party_user" class="form-control select2" data-placeholder="Search Third Party User">
            <option value="">Search Third Party User</option>
            @foreach ($third_party_users as $third_party_user)
                <option value="{{ $third_party_user->id }}" {{ $third_party_user->id == @$billpayment->third_party_user_id ? 'selected' : '' }}>{{ $third_party_user->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row form-group">
    <div class="col-6">
        <label for="payment">Allocate Payment</label>
        <select id="rel_payment" 
            name="rel_payment_id" 
            class="custom-select" 
            data-placeholder="Search Payment" 
            {{ $unallocated_pmts->count() ? '' : 'disabled' }}
            {{ @$billpayment? 'disabled' : '' }}
        >
            <option value="">None</option>
            @foreach ($unallocated_pmts as $pmt)
                @php
                    $balance = numberFormat($pmt->amount - $pmt->allocate_ttl);
                    $payment_type = ucfirst(str_replace('_', ' ', $pmt->payment_type));
                    $note = $pmt->note;
                    $date = $pmt->date;
                @endphp
                <option 
                    value="{{ $pmt->id }}" 
                    supplier_id="{{ $pmt->supplier_id }}"
                    data="{{ json_encode($pmt) }}"
                >
                    ({{ $balance }} - {{ $payment_type }} : {{ $date }}) - {{ $note }}
                    
                </option>
            @endforeach
        </select>
    </div>  
    <div class="col-6">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>  
</div>
<h5>Filter Options</h5>
<hr>
<div class="row form-group">
    <div class="col-4">
        <label for="supplier">Supplier Names</label>
        <select id="supplier_name" class="form-control select2" data-placeholder="Choose supplier">
            <option value=""></option>   
        </select>
    </div>
    <div class="col-2">
        <label for="bill">Bill Number</label>
        <select id="bill_number" class="form-control select2" data-placeholder="Choose Bill" >
            <option value=""></option>   
        </select>
    </div>
    @php
        $now = date('d-m-Y');
        $start = date('d-m-Y', strtotime("{$now} - 1 months"));
    @endphp
    <div class="col-2">
        <label for="">Start Date</label>
        <input type="text" value="{{ $start }}" id="start_date" class="form-control datepicker">
    </div>
    <div class="col-2">
        <label for="">End Date</label>
        <input type="text" value="{{ $now }}" id="end_date" class="form-control datepicker">
    </div>
    <div class="col-2">
        <input type="button" id="search_btn" value="Search" class="btn btn-info">
    </div>
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="billsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Due Date</th>
                <th>Bill No</th>
                <th>Supplier Name</th>
                <th>Note</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Outstanding</th>
                <th>Allocate</th>
            </tr>
        </thead>
        <tbody>   
            @isset ($billpayment)
                @foreach ($billpayment->items as $item)
                    @php
                        $bill = $item->supplier_bill;
                        if (!$bill) continue;
                    @endphp
                    <tr>
                        <td class="text-center">{{ dateFormat($bill->due_date) }}</td>
                        <td>{{ $bill->tid }}</td>
                        <td>{{ ($bill->purchase? $bill->purchase->suppliername : $bill->supplier)? $bill->supplier->name : '' }}</td>
                        <td class="text-center">{{ $bill->name }}</td>
                        <td>{{ $bill->status }}</td>
                        <td class="amount">{{ numberFormat($bill->total) }}</td>
                        <td>{{ numberFormat($bill->amount_paid) }}</td>
                        <td class="text-center due"><b>{{ numberFormat($bill->total - $bill->amount_paid) }}</b></td>
                        <td><input type="text" class="form-control paid" name="paid[]" value="{{ numberFormat($item->paid) }}" required></td>
                        <input type="hidden" name="bill_id[]" value="{{ $bill->id }}">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                    </tr>
                @endforeach
            @endisset      
        </tbody>                
    </table>
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="balance">Total Balance</label>    
        {{ Form::text('balance', null, ['class' => 'form-control', 'id' => 'balance', 'readonly']) }}
    </div>                          
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="allocate_ttl">Total Allocated Amount</label>    
        {{ Form::text('allocate_ttl', null, ['class' => 'form-control', 'id' => 'allocate_ttl', 'readonly']) }}
    </div>                          
</div>
<div class="row">
    <div class="col-2 ml-auto">
        <label for="total_paid">Total Unallocated</label>
        {{ Form::text('unallocate_ttl', null, ['class' => 'form-control', 'id' => 'unallocate_ttl', 'disabled']) }}
    </div>
</div>
<div class="row mt-1">                            
    <div class="col-2 ml-auto">  
        {{ Form::submit(@$billpayment? 'Update Payment' : 'Make Payment', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>

@section('after-scripts')
@include('focus.billpayments.form_js')
@endsection
