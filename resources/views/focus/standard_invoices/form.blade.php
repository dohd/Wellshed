<div class="row mb-1">
    <div class="col-6">
        <div>
            <button type="button" class="btn btn-blue btn-sm round float-right add-customer" data-toggle="modal" data-target="#addCustomerModal">
                <i class="fa fa-plus-circle"></i> customer
            </button>
            <label for="payer" class="caption">Customer Name</label>                      
        </div>
        
        <div class="input-group">
            <select class="form-control select2" name='customer_id' id="customer" data-placeholder="Choose Customer" required>
                <option value=""></option>
                @foreach ($customers as $row)
                    <option value="{{ $row->id }}">
                        {{ $row->company }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" value="0" id="credit">
            <input type="hidden" value="0" id="total_aging">
            <input type="hidden" value="0" id="outstanding_balance">
        </div>
    </div>

    <div class="col-2">
        <label for="tid" class="caption">Invoice No.</label>
        {{ Form::text('tid', @$tid, ['class' => 'form-control round', 'readonly']) }}
    </div>

    <div class="col-2">
        <label for="invoicedate" class="caption">Invoice Date</label>
        {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'id' => 'invoicedate', 'required' => 'required', 'readonly' => 'readonly']) }}
    </div>

    <div class="col-2">
        <label for="tid" class="caption">VAT Rate*</label>
        <select class="custom-select round overall-tax" name='tax_id' id="taxid" required>
            <option value="">-- VAT --</option>
            @foreach ($tax_rates as $row)
                <option value="{{ +$row->value }}" {{ @$invoice->tax_id == $row->value? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>        
    </div>   
</div>

<div class="form-group row">
    <div class="col-md-2">
        <label for="classlists" class="caption">Search Class</label>
        <select id="classlist" name="classlist_id" class="custom-select" data-placeholder="Search Class or Sub-class">
            <option value=""></option>
            @foreach ($classlists as $item)
                <option value="{{ $item->id }}" {{ @$invoice->classlist_id == $item->id? 'selected' : '' }}>
                    {{ $item->name }} {{ $item->parent_class? '('. $item->parent_class->name .')' : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"> 
        <label for="refer_no" class="caption">Payment Account*</label>                                   
        <select class="custom-select" name="bank_id" id="bank_id" required>
            <option value="">-- Select Bank --</option>
            @foreach ($banks as $bank)
                <option value="{{ $bank->id }}" {{ $bank->id == @$invoice->bank_id ? 'selected' : '' }}>
                    {{ $bank->bank }} - {{ $bank->note }}
                </option>
            @endforeach
        </select>                               
    </div>
    <div class="col-md-2">
        <label for="validity" class="caption">Credit Period</label>
        <select class="custom-select" name="validity" id="validity">
            @foreach ([0, 14, 30, 45, 60, 90] as $val)
            <option value="{{ $val }}" {{ !$val ? 'selected' : ''}} {{ @$invoice->validity == $val ? 'selected' : '' }}>
                {{ $val ? 'Valid For ' . $val . ' Days' : 'On Receipt' }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label for="income_category" class="caption">Income Category*</label>
        <select class="custom-select" name="account_id" required>
            <option value="">-- Select Category --</option>                                        
            @foreach ($accounts as $row)
                @if($row->holder !== 'Stock Gain' && $row->holder !== 'Others' && $row->holder !== 'Point of Sale' && $row->holder !== 'Loan Penalty Receivable' && $row->holder !== 'Loan Interest Receivable')
                    <option value="{{ $row->id }}" {{ $row->id == @$invoice->account_id ? 'selected' : '' }}>
                        {{ $row->holder }}
                    </option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="col-md-2">            
        <label for="currency_code">Currency Rate</label>
        <div class="row no-gutters">
            <div class="col-md-6"> 
                <select name="currency_id" id="currency" class="custom-select" disabled>
                    <option value=""></option>
                </select>                
            </div>
            <div class="col-md-6">
                {{ Form::text('fx_curr_rate', null, ['class' => 'form-control', 'id' => 'fx_curr_rate', 'required' => 'required', 'readonly' => 'readonly']) }}
            </div>
        </div>
    </div> 
    <div class="col-md-2">
        <label for="terms">Terms</label>
        <select name="term_id" class="custom-select">
            @foreach ($terms as $term)
            <option value="{{ $term->id }}" {{ $term->id == @$invoice->term_id ? 'selected' : ''}}>
                {{ $term->title }}
            </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-1">
    <div class="col-md-10">
        <div class="input-group"><label for="title" class="caption">Note</label></div>
        {{ Form::text('notes', null, ['class' => 'form-control']) }}
    </div>
    <div class="col-md-2">
        <label for="cu_invoice_no">CU Invoice No. </label>
        <input type="text" id="cu_invoice_no" name="cu_invoice_no" class="form-control box-size" @if(!empty($newCuInvoiceNo)) value="{{ $newCuInvoiceNo }}" @endif>
    </div>
</div>


<div class="row mb-1">

    <div class="col-12">
        <label for="reservation">Select Promo Code Reservation</label>
        <select id="reservation" name="reservation" class="form-control select2">

                    <!-- Options will be dynamically loaded via AJAX -->

        </select>
        <input type="hidden" id="promoDiscountData" name="promo_discount_data">
    </div>

</div>


<div class="table-responsive">
    <table id="products_tbl" class="table tfr my_stripe_single pb-1">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white">
                <th width="5%">#</th>
                <!-- custom col for Epicenter Africa -->  
                @if (auth()->user()->ins == 85) 
                    <th width="10%">Item Code</th>
                @endif
                <th width="35%">Item Name</th>
                <th >UoM</th>
                <th width="5%">Qty</th>
                <th>Unit Price</th>
                <th>Tax Rate</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input class="form-control num" name="numbering[]" value="1" readonly></td>     
                <!-- custom col for Epicenter Africa -->  
                @if (auth()->user()->ins == 85) 
                    <td>
                        @php
                            $project_types = [
                                'Project Management', 'Project Management1', 'Project Management2',
                                'Technical Products', 'Technical Products1', 'Technical Products2',
                                'service Center', 'service Center1'
                            ];
                        @endphp
                        <select class="custom-select custom-select project-type" name='cstm_project_type[]'>
                            <option value="">-- Project --</option>
                            @foreach ($project_types as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </td>    
                @endif                                   
                <td><textarea class="form-control name" name="description[]" id="" cols="30" rows="1"></textarea></td>
                <td><input type="text" class="form-control unit" name="unit[]" value="ITEM"></td>
                <td><input type="text" class="form-control qty" name="product_qty[]"></td>
                <td><input type="text" class="form-control price" name="product_price[]"></td>
                <td>
                    <div class="row no-gutters">
                        <div class="col-6">
                            <select class="custom-select taxid" name='tax_rate[]'>
                                <option value="">-- VAT --</option>
                                @foreach ($tax_rates as $row)
                                    <option value="{{ +$row->value }}" {{ @$invoice && $invoice->tax_id == $row->value? 'selected' : '' }}>
                                        {{ $row->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6"><input type="text" class="form-control prodtax" name="product_tax[]" readonly></div>
                    </div>                  
                </td>
                <td><input type="text" class="form-control amount" name="product_amount[]" readonly></td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item add-row" href="javascript:"><i class="fa fa-plus"></i> Add Row</a>
                            <a class="dropdown-item text-danger remove-row" href="javascript:">Remove</a>
                        </div>
                    </div> 
                </td>
                <input type="hidden" class="form-control prod-id" name="product_id[]">
                <input type="hidden" class="prod-subtotal" name="product_subtotal[]">
                <input type="hidden" class="prod-subtotal-dis">
                <input type="hidden" class="prod-taxable-dis">
            </tr>
        </tbody>
    </table>
</div>



<div class="row mt-4 mb-4">

    <label id="promoDiscounts" class="mt-1 col-12"></label>
    {{ Form::hidden('total_promo_discount', null, ['class' => 'form-control', 'id' => 'totalPromoDiscount', 'readonly']) }}
    {{ Form::hidden('total_promo_discounted_tax', null, ['class' => 'form-control', 'id' => 'totalPromoDiscountedTax', 'readonly']) }}

</div>


<!-- Totals Summary -->
<div class="form-group">
    <div class="col-2 ml-auto">
        <label for="taxable" class="mb-0">Taxable Amount</label>
        {{ Form::text('taxable', null, ['class' => 'form-control', 'id' => 'taxable', 'readonly']) }}
    </div>
    <div class="col-2 ml-auto">
        <label for="subtotal" class="mb-0">Subtotal</label>
        {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
    </div>
    <div class="col-2 ml-auto">
        <label for="tax" class="mb-0">Total Tax</label>
        {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
    </div>
    <div class="col-2 ml-auto">
        <label for="grandtotal" class="mb-0">Grand Total</label>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div>      

    <div class="edit-form-btn col-4 ml-auto mr-auto">
        {{ link_to_route('biller.invoices.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md mr-1']) }}
        {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}                                            
    </div>
</div>

@section('extra-scripts')
@include('focus.standard_invoices.form_js')
@stop
