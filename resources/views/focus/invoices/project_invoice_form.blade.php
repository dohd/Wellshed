<div class="card" style="margin-bottom: 1em;">
    <div class="card-header mb-0 pb-0" style="padding-top: 1em;">
        <div id="credit_limit" class="align-center"></div>
    </div>
    <div class="card-content">
        <div class="card-body mt-0 pt-0 pb-0">
            <div class="row mb-1">
                <div class="col-md-6"><label for="payer" class="caption">Customer Name</label>
                    <div class="input-group">
                        @php
                            use App\Http\Controllers\Focus\promotions\PromoCodeReservationController;
                            $customer_name = '';
                            if (!$customer->company && $quotes->count() == 1) {
                                $quote = $quotes->first();
                                if ($quote->customer) $customer_name = $quote->customer->company;
                                elseif ($quote->lead) $customer_name = $quote->lead->client_name;
                            } else $customer_name = $customer->company;
                        @endphp
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('customer_name', $customer_name, ['class' => 'form-control round', 'id' => 'customername', 'readonly']) }}
                        <input type="hidden" name="customer_id" value="{{ $customer->id ?: 0 }}" id="customer_id">
                        {{ Form::hidden('taxid', $customer->taxid) }}
                        <input type="hidden" value="0" id="credit">
                        <input type="hidden" value="0" id="total_aging">
                        <input type="hidden" value="0" id="outstanding_balance">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="tid" class="caption">Invoice No.</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        @php
                            // $label = gen4tid("{$prefixes[0]}-", @$last_tid+1);
                            $tid = @$last_tid+1; 
                            if (isset($invoice)) {
                                // $label = gen4tid("{$prefixes[0]}-", $invoice->tid);
                                $tid = $invoice->tid;
                            }
                        @endphp
                        {{-- Epicenter Africe or Wright Trading --}}
                        @if (auth()->user()->ins == 85 || auth()->user()->ins == 82)
                            {{ Form::text('tid', $tid, ['class' => 'form-control round']) }}
                        @else
                            {{ Form::text('tid', $tid, ['class' => 'form-control round', 'readonly' => 'readonly']) }}
                        @endif
                        {{-- <input type="hidden" name="tid" value={{ $tid }}> --}}
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="invoicedate" class="caption">Invoice Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'id' => 'invoicedate']) }}
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="tid" class="caption">VAT Rate*</label>
                    <div class="input-group">
                        <select class="custom-select" name='tax_id' id="tax_id" required>
                            <option value="">-- Select VAT --</option>
                            @foreach ($additionals as $row)
                                <option value="{{ +$row->value }}" {{ @$invoice && $invoice->tax_id == $row->value? 'selected' : '' }}>
                                    {{ $row->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-2">
                    <label for="classlists" class="caption">Search Class</label>
                    <select id="classlist" name="classlist_id" class="custom-select"
                            data-placeholder="Search Class or Sub-class">
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
                    <div class="input-group">
                        <select class="custom-select" name="bank_id" id="bank_id" required>
                            <option value="">-- Select Bank --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->id }}" {{ $bank->id == @$invoice->bank_id ? 'selected' : '' }}>
                                    {{ $bank->bank }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="validity" class="caption">Credit Period</label>
                    <div class="input-group">
                        <select class="custom-select" name="validity" id="validity">
                            @foreach ([0, 14, 30, 45, 60, 90] as $val)
                                <option value="{{ $val }}" {{ !$val ? 'selected' : ''}} {{ @$invoice->validity == $val ? 'selected' : '' }}>
                                    {{ $val ? 'Valid For ' . $val . ' Days' : 'On Receipt' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="income_category" class="caption">Income/Revenue Category</label>
                    <select class="custom-select" name="account_id" required>
                        <option value="">-- Select Category --</option>
                        @foreach ($accounts as $row)
                            <option value="{{ $row->id }}" {{ $row->id == @$invoice->account_id ? 'selected' : '' }}>
                                {{ $row->holder }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($currency->rate == 1)
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
                    {{ Form::hidden('fx_curr_rate', +$currency->rate) }}
                @else
                    <div class="col-md-2">
                        <label for="currency_code">Currency</label>
                        <div class="row no-gutters">
                            <div class="col-6">
                                {{ Form::text('currency_code', $currency->code, ['class' => 'form-control', 'id' => 'currency_code', 'disabled' => 'disabled']) }}
                            </div>
                            <div class="col-6">
                                {{ Form::text('fx_curr_rate', +$currency->rate, ['class' => 'form-control', 'id' => 'fx_curr_rate']) }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="row mb-1">
                <div class="col-md-10">
                    <div class="input-group"><label for="title" class="caption">Note</label></div>
                    {{ Form::text('notes', @$quotes[0]['notes'], ['class' => 'form-control','required']) }}
                </div>
                <div class="col-md-2">
                    <label for="cu_invoice_no">CU Invoice No.</label>
                    <input type="text" id="cu_invoice_no" name="cu_invoice_no" class="form-control box-size"
                           @if(!empty($invoice->cu_invoice_no))
                               value="{{$invoice->cu_invoice_no}}"
                            @endif
                    >
                </div>
            </div>
            <!-- job valuation -->
            @if (@$quotes && @$quotes[0]['job_valuation_id'] > 0)
                <input type="hidden" name="job_valuation_id" value="{{ $quotes[0]['job_valuation_id'] }}">
            @endif
            <!-- Boq valuation -->
            @if (@$quotes && @$quotes[0]['boq_valuation_id'] > 0)
                <input type="hidden" name="boq_valuation_id" value="{{ $quotes[0]['boq_valuation_id'] }}">
            @endif
        </div>
    </div>
</div>

<!-- Line items -->
<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-1">
                <div class="col-md-2">
                    <label class="mb-0" for="invoice-type">Invoice Line Type</label>
                    <select name="invoice_type" class="custom-select" id="invoice_type" style="height:2em;" required>
                        @foreach (['standard' => 'Standard', 'collective' => 'Consolidated'] as $key => $val)
                            <option value="{{ $key }}" {{ $key == @$invoice->invoice_type? 'selected' : ''}}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 80vh">
                <table id="quoteTbl" class="table tfr my_stripe_single pb-1">
                    <thead>
                    <tr class="item_header bg-gradient-directional-blue white">
                        <th width="5%">#</th>
                        <!-- Custom col for Epicenter Africa -->
                        @if (auth()->user()->ins == 85)
                            <th width="15%">Item Code</th>
                        @endif
                        <th width="25%" class="ref-label">Reference</th>
                        <th width="35%">Item Description</th>
                        <th width="10%">UoM</th>
                        <th width="10%">Qty</th>
                        <th width="10%">Rate (VAT Exc)</th>
                        <th width="10%">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (isset($quotes))
                        @foreach($quotes as $k => $val)
                            @php
                                // Reference details
                                $tid = gen4tid($val->bank_id? "{$prefixes[2]}-" : "{$prefixes[1]}-", $val->tid);
                                if($val->boq_valuation_id) $tid = $val->name .' ('.gen4tid('BoQ-',$val->tid).')';
                                if ($val->revision) $tid .= $val->revision;
                                $lpo_no = $val->lpo ? "{$prefixes[3]}-{$val->lpo->lpo_no}" : '';
                                $client_ref = $val->client_ref;
                                $branch_name = $val->branch? "{$val->branch->name} ({$val->branch->branch_code})" : '';
                                $djc_ref = $val->reference? "Djc-{$val->reference}" : '';

                                // Description details
                                $jcs = [];
                                foreach($val->verified_jcs as $jc) {
                                    if ($jc->type == 2) $jcs[] = "{$prefixes[4]}-{$jc->reference}";
                                    else $jcs[] = "{$prefixes[5]}-{$jc->reference}";
                                }

                                // Table values
                                $title = $val->notes;
                                $jcs = implode(', ', $jcs);
                                $description = implode(';', [$title, $djc_ref, $jcs]);
                                $reference = '' . implode('; ', [$branch_name, $tid, $lpo_no, $client_ref]);
                                $project_id = $val->project_quote ? $val->project_quote->project_id : '';
                                if (!$project_id && $val->boq_valuation_id) {
                                    $project = $val->bom->quote->project ?? null;
                                    if ($project) {
                                        $project_id = $project->id;
                                    }
                                }

                                // $taxable = $val->verified_products()->where('product_tax', '>', 0)->sum(DB::raw('product_qty * product_subtotal'));
                                // $subtotal = $val->verified_products()->sum(DB::raw('product_qty * product_subtotal'));
                                $taxable = 0;
                                $subtotal = 0;
                                $taxtotal = 0;
                                foreach ($val->verified_products as $item) {
                                    if ($item->product_tax) $taxable += $item->product_qty * $item->product_subtotal;
                                    $subtotal += $item->product_qty * $item->product_subtotal;
                                    $taxtotal += $item->product_tax;
                                }
                                //browserLog($taxable . ' ' . $subtotal);
                            @endphp
                            <tr>
                                <td class="num pl-2">{{ $k+1 }}</td>
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
                                        <select class="custom-select custom-select project-type"
                                                name='cstm_project_type[]'>
                                            <option value="">-- Project --</option>
                                            @foreach ($project_types as $type)
                                                <option value="{{ $type }}">{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endif
                                <td><textarea class="form-control ref" name="reference[]" id="reference-{{ $k }}"
                                              rows="3" readonly>{{ $reference }}</textarea></td>
                                <td><textarea class="form-control descr" name="description[]" id="description-{{ $k }}"
                                              rows="3">{{ $description }}</textarea></td>
                                <td><input type="text" class="form-control unit" name="unit[]" id="unit-{{ $k }}"
                                           value="Lot" readonly></td>
                                <td><input type="text" class="form-control qty" name="product_qty[]"
                                           id="product_qty-{{ $k }}" value="1" readonly></td>
                                <td><input type="text" class="form-control rate" name="product_price[]"
                                           value="{{ number_format($subtotal, 4) }}" id="product_price-{{ $k }}"
                                           readonly></td>
                                <td class="text-center"><strong><span class='ttlText amount'
                                                                      id="result-{{ $k }}">{{ number_format($subtotal + $val->verified_tax, 4) }}</span></strong>
                                </td>
                                <input type="hidden" class="subtotal" value="{{ round($subtotal, 4) }}"
                                       id="initprice-{{ $k }}" disabled>
                                <input type="hidden" class="num-val" name="numbering[]" id="num-{{ $k }}">
                                <input type="hidden" class="row-index" name="row_index[]" id="rowindex-{{ $k }}">
                                @if (@$quotes && @$quotes[0]['boq_valuation_id'] > 0)
                                    <input type="hidden" class="boq-id" name="boq_id[]" value="{{ $val->id }}" id="boqid-{{ $k }}">
                                @else
                                <input type="hidden" class="quote-id" name="quote_id[]" value="{{ $val->id }}"
                                       id="quoteid-{{ $k }}">
                                @endif
                                <input type="hidden" class="branch-id" name="branch_id[]" value="{{ $val->branch_id }}"
                                       id="branchid-{{ $k }}">
                                <input type="hidden" class="project-id" name="project_id[]" value="{{ $project_id }}"
                                       id="projectid-{{ $k }}">
                                <input type="hidden" class="taxable" value="{{ round($taxable, 4) }}">
                                <input type="hidden" class="producttax" name="product_tax[]"
                                       value="{{ round($taxtotal, 4) }}" id="producttax-{{ $k }}">
                                <input type="hidden" class="taxrate" name="tax_rate[]" value="{{ +$val->tax_id }}"
                                       id="taxrate-{{ $k }}">
                                <input type="hidden" class="productsubtotal" name="product_subtotal[]"
                                       value="{{ round($subtotal, 4) }}" id="productsubtotal-{{ $k }}">
                                <input type="hidden" class="productamount" name="product_amount[]"
                                       value="{{ number_format($subtotal + $val->verified_tax, 4) }}">
                                <input type="hidden" class="price" value="0" id="price-{{ $k }}">
                            </tr>
                        @endforeach
                    @else
                        <!-- Edit Invoice Items -->
                        @foreach ($invoice->products as $k => $item)
                            <tr>
                                <td class="num pl-2">{{ $k+1 }}</td>
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
                                        <select class="custom-select custom-select project-type"
                                                name='cstm_project_type[]'>
                                            <option value="">-- Project --</option>
                                            @foreach ($project_types as $type)
                                                <option value="{{ $type }}" {{ $type == $item->cstm_project_type? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endif
                                <td><textarea class="form-control ref" name="reference[]" id="reference-{{ $k }}"
                                              rows="3">{{ $item->reference }}</textarea></td>
                                <td><textarea class="form-control descr" name="description[]" id="description-{{ $k }}"
                                              rows="3">{{ $item->description }}</textarea></td>
                                <td><input type="text" class="form-control unit" name="unit[]" id="unit-{{ $k }}"
                                           value="{{ $item->unit }}" readonly></td>
                                <td><input type="text" class="form-control qty" name="product_qty[]"
                                           id="product_qty-{{ $k }}" value="{{ +$item->product_qty }}" readonly></td>
                                @php
                                    $unit_cost = number_format($item->product_price, 4);
                                    $net_cost = number_format($item->product_price * $item->product_qty, 4);
                                @endphp
                                <td><input type="text" class="form-control rate" name="product_price[]"
                                           value="{{ $unit_cost }}" id="product_price-{{ $k }}" readonly></td>
                                <td class="text-center"><strong><span class='ttlText amount'
                                                                      id="result-{{ $k }}">{{ $net_cost }}</span></strong>
                                </td>
                                <input type="hidden" class="subtotal" value="{{ +$item->product_price }}"
                                       id="initprice-{{ $k }}" disabled>
                                <input type="hidden" class="num-val" name="numbering[]" value="{{ $item->numbering }}"
                                       id="num-{{ $k }}">
                                <input type="hidden" class="row-index" name="row_index[]" value="{{ $item->row_index }}"
                                       id="rowindex-{{ $k }}">
                                <input type="hidden" class="quote-id" name="quote_id[]" value="{{ $item->quote_id }}"
                                       id="quoteid-{{ $k }}">
                                <input type="hidden" class="branch-id" name="branch_id[]" value="{{ $item->branch_id }}"
                                       id="branchid-{{ $k }}">
                                <input type="hidden" class="project-id" name="project_id[]"
                                       value="{{ $item->project_id }}" id="projectid-{{ $k }}">
                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                <input type="hidden" class="taxable" value="{{ +$item->product_price }}">
                                <input type="hidden" class="producttax" name="product_tax[]"
                                       value="{{ $item->product_price * $item->tax_rate * 0.01 }}" id="producttax-{{ $k }}">
                                <input type="hidden" class="taxrate" name="tax_rate[]" value="{{ +$item->tax_rate }}"
                                       id="taxrate-{{ $k }}">
                                <input type="hidden" class="productsubtotal" name="product_subtotal[]"
                                       value="{{ +$item->product_price }}" id="productsubtotal-{{ $k }}">
                                <input type="hidden" class="productamount" name="product_amount[]"
                                       value="{{ +$item->product_amount }}">
                                <input type="hidden" class="price" value="0" id="price-{{ $k }}">
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>

            @php
                $promoDiscounts = @$invoice->promoDiscounts;
                $discountsTable = null;
                if($promoDiscounts) $discountsTable = (new PromoCodeReservationController())->generateDiscountsTable($promoDiscounts)
            @endphp
            <p>{{$discountsTable}}</p>
            @if($discountsTable)
                <div class="row mt-4 mb-4">
                    <div class="col-12">
                        <label id="promoDiscounts" class="mt-1 col-12"> {!! $discountsTable !!}</label>
                    </div>
                </div>
            @endif

            <div class="form-group">
                <div class="col-2 ml-auto">
                    <label for="taxable" class="mb-0">Taxable</label>
                    {{ Form::text('taxable', null, ['class' => 'form-control', 'id' => 'taxable', 'readonly']) }}
                </div>
                <div class="col-2 ml-auto">
                    <label for="subtotal" class="mb-0">Subtotal</label>
                    {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
                </div>
                <div class="col-2 ml-auto">
                    <label for="totaltax" class="mb-0">Total VAT</label>
                    {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
                </div>
                <div class="col-2 ml-auto">
                    <label for="grandtotal" class="mb-0">Grand Total</label>
                    {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
                </div>
                <div class="row no-gutters mt-1">
                    <div class="col-1 ml-auto pl-1">
                        <a href="{{ route('biller.invoices.uninvoiced_quote') }}"
                           class="btn btn-danger block">Cancel</a>
                    </div>
                    <div class="col-1 ml-1">
                        {{ Form::submit(@$invoice? 'Update' : 'Generate', ['class' => 'btn btn-primary block text-white mr-1']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
