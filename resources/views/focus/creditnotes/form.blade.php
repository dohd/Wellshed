{{ Form::hidden('is_debit', @$is_debit? 1 : 0) }}
<div class="card">
    <div class="card-content">
        <div class="card-body pb-0">
            <div class="form-group row">
                <div class="col-4">
                    <label for="customer">Search Customer</label>
                    <select name="customer_id" id="customer" class="form-control" data-placeholder="Seach Customer" required>
                        <option value=""></option>
                        @isset($creditnote)
                            <option value="{{ $creditnote->customer_id }}" selected>
                                {{ $creditnote->customer->company ?: $creditnote->customer->name }}
                            </option>
                        @endisset
                    </select>                          
                </div>
                <div class="col-2">
                    <label for="tid">#Serial No.</label>
                    {{ Form::text('tid', $tid, ['class' => 'form-control', 'readonly']) }}
                </div>
                <div class="col-2">
                    <div><label for="date">Date</label></div>
                    {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
                </div>
                 
                <div class="col-2">
                    <label for="cu_invoice_no">Search Class</label>
                    <select id="classlist" name="classlist_id" class="custom-select" data-placeholder="Search Class or Sub-class">
                        <option value=""></option>
                        @foreach ($classlists as $item)
                            <option value="{{ $item->id }}" {{ @$creditnote->classlist_id == $item->id? 'selected' : '' }}>
                                {{ $item->name }} {{ $item->parent_class? '('. $item->parent_class->name .')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-2">
                    <label for="tax">VAT</label>
                    <select name="tax_id" id="taxid" class="custom-select">
                        @foreach ($tax_rates as $row)
                            <option value="{{ $row->value }}" {{ $row->value == @$creditnote->tax_id? 'selected' : '' }}>
                                {{ $row->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-4">
                    <label for="invoice">Invoice</label>
                    <select name="invoice_id" id="invoice" class="form-control" data-placeholder="Choose Invoice" required>
                        <option value=""></option>
                        @isset($creditnote->invoice)
                            <option value="{{ $creditnote->invoice_id }}" selected>
                                {{ gen4tid('Inv-', $creditnote->invoice->tid) }} - {{ $creditnote->invoice->notes }}
                            </option>
                        @endisset
                    </select>
                </div>
                <div class="col-2">
                    <label for="cu_invoice_no">CU Invoice Number</label>
                   {{ Form::text('cu_invoice_no', @$newCuInvoiceNo, ['class' => 'form-control', 'id' => 'cu_invoice_no']) }}
                </div>
                <div class="col-2">
                    <label for="currency">Currency FX Rate</label>
                    <div class="row no-gutters">
                        <div class="col-md-6"> 
                            <select name="currency_id" id="currency" class="custom-select">
                                <option value="">-- Currency --</option>
                                @foreach ($currencies as $item)
                                    <option value="{{ $item->id }}" rate="{{ $item->rate }}" {{ $item->id == @$creditnote->currency_id? 'selected' : '' }}>
                                        {{ $item->code }}
                                    </option>
                                @endforeach
                            </select>                
                        </div>
                        <div class="col-md-6">
                            {{ Form::text('fx_curr_rate', null, ['class' => 'form-control', 'id' => 'fx_curr_rate', 'required' => 'required', 'readonly' => 'readonly']) }}
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <label for="note">Note</label>
                    {{ Form::textarea('note', null, ['id' => 'note', 'class' => 'form-control', 'rows' => '1', 'style' => 'height:3em;']) }}
                </div>  
            </div>

            @if (config('services.efris.base_url'))
                <div class="row mb-1">
                    <div class='col-4'>
                        <label for="reason_code">EFRIS Credit Reason <span class="text-danger">*</span></label>
                        <select name="efris_reason_code" id="efrisReasonCode" class="custom-select" required>
                            <option value="">-- Select Reason--</option>
                            @php
                                $reasons = [
                                    '101' => 'Return of products due to expiry or damage, etc.',
                                    '102' => 'Cancellation of the purchase',
                                    '103' => 'Invoice amount wrongly stateted due to miscalculations',
                                    '104' => 'Partial or complete waive-off of the invoice',
                                    '105' => 'Others',
                                ];
                            @endphp
                            @foreach ($reasons as $key => $item)
                                <option value="{{ $key }}">{{ $key }}:{{ $item }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="efris_reason_code_name" id="efrisReasonCodeName">
                    </div>
                </div>
            @endif
            
            <!-- Credit Note Refund -->
            @if (!$is_debit)
                <div class="row mb-1">
                    <div class="col-4">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="issue_refund" autocomplete="false">
                            <label class="form-check-label" for="issue-refund">Issue Refund</label>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="card refund-card d-none">
    <div class="card-content">
        <div class="card-body pb-0">
            <div class="form-group row">
                <div class="col-4">
                    <label for="refund_account">Refund From Account</label>
                    <select name="account_id" id="account" class="form-control" data-placeholder="Choose Bank" autocomplete="false">
                        <option value=""></option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" {{ @$creditnote->account_id == $account->id? 'selected' : '' }}>
                                {{ $account->holder }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-2">
                    <label for="payment_mode">Payment Method</label>
                    <select name="payment_mode" id="payment_mode" class="custom-select">
                        <option value="">-- Mode --</option>
                        @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque','pesalink'] as $mode)
                            <option value="{{ $mode }}" {{ @$creditnote->payment_mode == $mode? 'selected' : '' }}>
                                {{ strtoupper($mode) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-2">
                    <label for="ref_no">Reference No.</label>
                   {{ Form::text('reference_no', null, ['class' => 'form-control', 'id' => 'reference_no', 'autocomplete' => "false"]) }}
                </div>
            </div>
        </div>
    </div>
</div>        

<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-1">
                <div class="col-2">
                    <div><label for="load_invoice_items">Load Items From</label></div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input radio-item" type="radio" name="load_items_from" id="radio-stock" value="0" checked>
                        <label class="form-check-label" for="inlineRadio1">Inventory</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input radio-item" type="radio" name="load_items_from" id="radio-inv" value="1">
                        <label class="form-check-label" for="inlineRadio2">Invoice</label>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="table-responsive">
                <table id="products_tbl" class="table tfr my_stripe_single pb-1">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            <th width="3%" class="d-none"></th>
                            <th width="5%">#</th>
                            <!-- custom col for Epicenter Africa -->  
                            @if (auth()->user()->ins == 85) 
                                <th width="10%">Item Code</th>
                            @endif
                            <th width="30%">Item Name</th>
                            <th >UoM</th>
                            <th width="5%">Qty</th>
                            <th>Cost</th>
                            <th>Tax Rate</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @include('focus.creditnotes.partials.creditnote-item')
                    </tbody>
                </table>
            </div>
            <!--End Products Table -->
            
            <div class="row">
                <div class="col-2 ml-auto">
                    <label for="subtotal" class="mb-0">Taxable</label>
                    {{ Form::text('taxable', null, ['class' => 'form-control', 'id' => 'taxable', 'readonly' => 'readonly']) }}
                </div>  
            </div>
            <div class="row">
                <div class="col-2 ml-auto">
                    <label for="subtotal" class="mb-0">Subtotal</label>
                    {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly' => 'readonly']) }}
                </div>  
            </div>
            <div class="row">
                <div class="col-2 ml-auto">
                    <label for="tax" class="mb-0">Tax</label>
                    {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly' => 'readonly']) }}
                </div>  
            </div>
            <div class="row mb-1">
                <div class="col-2 ml-auto">
                    <label for="total" class="mb-0">Grand Total</label>
                    {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly' => 'readonly']) }}
                </div> 
            </div>
            <!-- Aggregate fx columns -->
            <input type="hidden" name="fx_subtotal" id="fx-subtotal">
            <input type="hidden" name="fx_taxable" id="fx-taxable">
            <input type="hidden" name="fx_tax" id="fx-tax">
            <input type="hidden" name="fx_total" id="fx-total">
            <input type="hidden" name="fx_gain" id="fx-gain">
            <input type="hidden" name="fx_loss" id="fx-loss">
            <!-- End Aggregate fx columns -->

            <div class="row">
                <div class="col-2 ml-auto mr-auto">
                    <div class="edit-form-btn row">
                        {{ link_to_route('biller.creditnotes.index', trans('buttons.general.cancel'), $is_debit? compact('is_debit') : [], ['class' => 'btn btn-danger btn-md ml-auto mr-1']) }}
                        {{ Form::submit(@$creditnote? 'Update' : 'Create', ['class' => 'btn btn-primary btn-md mr-2']) }}                                           
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('after-scripts')
@include('focus.creditnotes.form_js')
@endsection