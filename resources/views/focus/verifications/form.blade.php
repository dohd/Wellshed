@php
    $label = $quote->bank_id ? 'PI' : 'Quote';
    $prefixes = prefixesArray(['quote', 'proforma_invoice'], $quote->ins);
@endphp

<div class="row">
    <input type="hidden" name="quote_id" value="{{ $quote->id }}">

    <div class="col-6 cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="fcol-sm-12">
                    <h3 class="title pl-1">Verify {{ $label }}</h3>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-6">                                        
                    <label for="serial_no" class="caption">{{ $label . ' ' . trans('general.serial_no') }}</label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>                                           
                        @php
                            $tid = gen4tid($label == 'PI'? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                        @endphp
                        {{ Form::text('tid', $tid . $quote->revision, ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                    </div>
                </div>    
                <div class="col-6">
                    <label for="date" class="caption">{{ $label }} Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('date', @$quote->date, ['class' => 'form-control round datepicker', 'id'=>'date', 'disabled']) }}
                    </div>
                </div>                                
            </div>

            <div class="form-group row">                                    
                <div class="col-10">
                    <label for="client" class="caption">Customer Branch</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client', @$quote->customer->name . ' ' . @$quote->branch->name, ['class' => 'form-control round', 'id' => 'client', 'disabled']) }}
                        <input type="hidden" name="customer_id" value="{{ @$quote->customer_id }}" id="client_id">
                        <input type="hidden" name="branch_id" value="{{ @$quote->branch_id }}" id="branch_id">
                    </div>
                </div>
            </div> 
            <div class="form-group row">
                <div class="col-12">
                    <label for="subject" class="caption">Subject / Title</label>
                    {{ Form::text('notes', @$quote->notes, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
                </div>
            </div>  
        </div>
    </div>

    <div class="col-6 cmp-pnl">
        <div class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="col-sm-12">
                    <h3 class="title">Properties</h3>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-4">
                    <label for="client_ref" class="caption">Client Ref / Callout ID</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('client_ref', @$quote->client_ref, ['class' => 'form-control round', 'placeholder' => 'Client Reference', 'id' => 'client_ref', 'disabled']) }}
                    </div>
                </div>   
                <div class="col-4">
                    <label for="invocieno" class="caption">Djc Reference</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('reference', @$quote->reference, ['class' => 'form-control round', 'disabled']) }}
                    </div>
                </div>
                <div class="col-4">
                    <label for="reference_date" class="caption">Reference Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('reference_date', @$quote->reference_date, ['class' => 'form-control round datepicker', 'id'=>'reference-date', 'disabled']) }}
                    </div>
                </div>             
            </div>
            <div class="form-group row">
                <div class="col-4">
                    <label for="quote_subtotal">{{ $label }} Subtotal</label>
                    {{ Form::text('quote_subtotal', numberFormat($quote->subtotal), ['class' => 'form-control', 'id' => 'quote_subtotal',  'disabled']) }}
                </div>
                <div class="col-4">
                    <label for="quote_subtotal">{{ $label }} Total</label>
                    {{ Form::text('quote_total', numberFormat($quote->total), ['class' => 'form-control', 'id' => 'quote_total', 'disabled']) }}
                </div>    
                <div class="col-4">
                    <label for="currency">Currency</label>
                    {{ Form::text('currency', $quote->currency? $quote->currency->code : '', ['class' => 'form-control', 'id' => 'currency', 'disabled']) }}
                </div>              
            </div>   
            <div class="form-group row">
                <div class="col-8">
                    <label for="note" class="caption">General Remark</label>
                    {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
                </div>
                <div class="col-4">
                    <label for="date" class="caption">Date</label>
                    {{ Form::text('verification_date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
                </div>
            </div>   
        </div>
    </div>                        
</div>                  

<div class="table-responsive">                            
    <table id="productsTbl" class="table tfr my_stripe_single pb-2 text-center">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white">
                <th width="5%">#</th>
                <th width="20%">Item Name</th>
                <th width="7%">UoM</th>
                <th width="7%">Qty</th>
                <th width="10%">Rate</th>
                <th width="15%">Tax</th>
                <th width="10%">Amount</th>
                <th width="12%">Remark</th>
                <th width="5%">Action</th>
            </tr>
        </thead>
        <tbody>
            {{-- Product Row Template --}}
            <tr>
                <td><input type="text" class="form-control num" name="numbering[]"></td>
                <td>
                    <textarea class="form-control prodname" name="product_name[]" placeholder="{{trans('general.enter_product')}}"></textarea>  
                </td>
                <td><input type="text" class="form-control unit" name="unit[]"></td>                
                <td><input type="text" class="form-control qty" name="product_qty[]"></td>
                <td><input type="text" class="form-control price" name="product_subtotal[]"></td>
                <td>
                    <div class="row no-gutters">
                        <div class="col-6">
                            <select class="custom-select taxid" name='tax_rate[]'>
                                @foreach ($additionals as $row)
                                    <option value="{{ +$row->value }}">
                                        {{ $row->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6"><input type="text" class="form-control prodtax" name="product_tax[]" readonly></div>
                    </div>                  
                </td>
                <td><input type="text" class="form-control amount" name="product_total[]" readonly></td>
                <td><textarea class="form-control remark" name="remark[]"></textarea></td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item up" href="javascript:">Up</a>
                            <a class="dropdown-item down" href="javascript:">Down</a>
                            <a class="dropdown-item remove text-danger" href="javascript:">Remove</a>
                        </div>
                    </div>   
                </td>
                <input type="hidden" name="row_index[]" class="index">
                <input type="hidden" name="a_type[]" value="1" class="type">
                <input type="hidden" name="product_id[]" class="prodid">
                <input type="hidden" name="quote_item_id[]" class="qt-itemid">
                <input type="hidden" name="item_id[]" class="itemid">
            </tr>

            {{-- Title Row Template --}}
            <tr>
                <td><input type="text" class="form-control num" name="numbering[]"></td>
                <td colspan="7"><input type="text" class="form-control prodname" name="product_name[]" placeholder="Enter Title Or Heading"></td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item up" href="javascript:">Up</a>
                            <a class="dropdown-item down" href="javascript:">Down</a>
                            <a class="dropdown-item remove text-danger" href="javascript:">Remove</a>
                        </div>
                    </div>   
                </td>
                <input type="hidden" name="row_index[]" class="index">
                <input type="hidden" name="product_id[]">
                <input type="hidden" name="remark[]">
                <input type="hidden" name="unit[]">
                <input type="hidden" name="tax_rate[]">
                <input type="hidden" name="product_qty[]">
                <input type="hidden" name="product_tax[]">
                <input type="hidden" name="product_total[]">
                <input type="hidden" name="product_subtotal[]">
                <input type="hidden" name="a_type[]" value="2">
                <input type="hidden" name="quote_item_id[]" class="qt-itemid">
                <input type="hidden" name="item_id[]" class="itemid">
            </tr>
        </tbody>
    </table>
</div>


<div class="row">
    <div class="col-10 col-xs-7">
        <a href="javascript:" class="btn btn-success mr-1" aria-label="Left Align" id="addProduct">
            <i class="fa fa-plus-square"></i> Product
        </a>
        <a href="javascript:" class="btn btn-primary" aria-label="Left Align" id="add-title">
            <i class="fa fa-plus-square"></i> Title
        </a>
        <br>
        <div class="form-group row pt-5">
            <div class="col-sm-11">
                <div class="table-responsive">
                    <table id="jobcardsTbl" class="table pb-2 tfr text-center">
                        <thead class="bg-gradient-directional-blue white pb-1">
                            <tr>
                                <th width="10%">Type</th>
                                <th width="12%">Ref No</th>                                                    
                                <th width="12%">Date</th>
                                <th width="15%">Technician</th>
                                <th width="15%">Equipment</th>
                                <th width="12%">Location</th>
                                <th width="16%">Fault</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Jobacard/DNote Row Template --}}
                            <tr>
                                <td>
                                    <select class="custom-select jc_type" name="type[]">
                                        <option value="jobcard" selected>Jobcard</option>
                                        <option value="dnote">DNote</option> 
                                    </select>
                                </td>
                                <td><input type="text" class="form-control jc_ref" name="reference[]"></td>
                                <td><input type="text" class="form-control datepicker jc_date" name="date[]"></td>
                                <td><input type="text" class="form-control jc_tech" name="technician[]"></td>
                                <td><textarea class="form-control jc_equip" name="equipment[]"></textarea>
                                <td><input type="text" class="form-control jc_loc" name="location[]"></td>
                                <td>
                                    <select class="custom-select jc_fault" name="fault[]">
                                        <option value="none">None</option>
                                        <option value="faulty_compressor">Faulty Compressor</option>
                                        <option value="faulty_pcb">Faulty PCB</option>
                                        <option value="leakage_arrest">Leakage Arrest</option>
                                        <option value="electrical_fault">Electrical Fault</option>
                                        <option value="drainage">Drainage</option>
                                        <option value="other">Other</option>
                                    </select>
                                </td>
                                <td><a href="javascript:" class="btn btn-primary btn-md remove" type="button">Remove</a></td>
                                <input type="hidden" name="equipment_id[]" class="jc_equipid">
                                <input type="hidden" name="jcitem_id[]" class="jc_itemid">
                            </tr>
                        </tbody>
                    </table>
                    <a href="javascript:" class="btn btn-success" aria-label="Left Align" id="addJobcard">
                        <i class="fa fa-plus-square"></i>  Jobcard / DNote
                    </a> 
                </div>
            </div>
        </div>     
    </div>

    <div class="col-2 col-xs-5 invoice-block pull-right">
        <div>
            <label class="m-0">Taxable Amount</label>
            {{ Form::text('taxable', null, ['class' => 'form-control', 'id' => 'taxable', 'readonly']) }}
        </div>
        <div>
            <label class="m-0">Subtotal</label>
            {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
        </div>
        <div>
            <label class="m-0">Tax</label>
            {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
        </div>
        <div>
            <label class="m-0">Total</label>
            {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
        </div>
    </div>
    
</div>
<div class="row justify-content-end mt-1">
    <div class="col-2">
        {{ link_to_route(@$verification? 'biller.verifications.index' : 'biller.verifications.quote_index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md block']) }}
    </div>
    <div class="col-2">
        {{ Form::submit(@$verification? 'Update' : 'Generate', ['class' => 'btn btn-primary btn-md block']) }}
    </div>
</div>

@section('after-scripts')
@include('focus.verifications.form_js')
@endsection
