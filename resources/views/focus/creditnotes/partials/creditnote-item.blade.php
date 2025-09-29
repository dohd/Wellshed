<tr>
    <td class="d-none"><input type="checkbox" class="check ml-2 mt-1"></td>
    <td><input type="text" class="form-control num" name="numbering[]" value="1" readonly></td>     
    <!-- custom column for Epicenter Africa -->  
    @if (auth()->user()->ins == 85) 
        <td>
            @php
                $projectTypes = [
                    'Project Management', 'Project Management1', 'Project Management2',
                    'Technical Products', 'Technical Products1', 'Technical Products2',
                    'service Center', 'service Center1'
                ];
            @endphp
            <select class="custom-select custom-select project-type" name='cstm_project_type[]'>
                <option value="">-- Code --</option>
                @foreach ($projectTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </td>    
    @endif       
    <!-- End custom column -->                            
    <td><textarea class="form-control name" name="name[]" rows="1" style="height:3em;"></textarea></td>
    <td><input type="text" class="form-control unit" name="unit[]" value="ITEM"></td>
    <td><input type="text" class="form-control qty" name="qty[]"></td>
    <td><input type="text" class="form-control rate" name="rate[]"></td>
    <td>
        <div class="row no-gutters">
            <div class="col-6">
                <select class="custom-select prod-taxid" name='prod_taxid[]'>
                    <option value="">-- VAT --</option>
                    @foreach ($tax_rates as $row)
                        <option value="{{ $row->value }}">
                            {{ $row->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6"><input type="text" class="form-control prod-tax" name="prod_tax[]" readonly></div>
        </div>                  
    </td>
    <td><input type="text" class="form-control prod-total" name="prod_total[]" readonly></td>
    <td>
        <!-- Action Dropdown Menu -->
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                action
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item add-row" href="javascript:"><i class="fa fa-plus"></i> Add Row</a>
                <a class="dropdown-item text-danger remove-row" href="javascript:"><i class="fa fa-trash"></i> Remove</a>
            </div>
        </div> 
    </td>
    <input type="hidden" class="prod-subtotal" name="prod_subtotal[]">
    <input type="hidden" class="prod-taxable" name="prod_taxable[]">
    <input type="hidden" class="prodvar-id" name="productvar_id[]">
    <input type="hidden" class="inv-item-id" name="invoice_item_id[]">
    <input type="hidden" class="prod-id" name="id[]">
    <!-- fx columns -->
    <input type="hidden" class="prod-fx-rate" name="prod_fx_rate[]">
    <input type="hidden" class="prod-fx-taxable" name="prod_fx_taxable[]">
    <input type="hidden" class="prod-fx-subtotal" name="prod_fx_subtotal[]">
    <input type="hidden" class="prod-fx-tax" name="prod_fx_tax[]">
    <input type="hidden" class="prod-fx-total" name="prod_fx_total[]">
    <input type="hidden" class="prod-fx-gain" name="prod_fx_gain[]">
    <input type="hidden" class="prod-fx-loss" name="prod_fx_loss[]">
    <!-- End fx columns -->
</tr>

<!-- Edit Row -->
@if (@$creditnote)
    @foreach ($creditnote->items as $row)
        <tr>
            <td class="d-none"><input type="checkbox" class="check ml-2 mt-1"></td>
            <td><input type="text" class="form-control num" name="numbering[]" value="{{ $row->numbering }}" readonly></td>     
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
                        <option value="">-- Code --</option>
                        @foreach ($project_types as $type)
                            <option value="{{ $type }}" {{ $type == $row->cstm_project_type? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </td>    
            @endif       
            <!-- End custom col for Epicenter Africa -->                              
            <td><textarea class="form-control name" name="name[]" rows="1" style="height:3em;">{{ $row->name }}</textarea></td>
            <td><input type="text" class="form-control unit" name="unit[]" value="{{ $row->unit }}"></td>
            <td><input type="text" class="form-control qty" name="qty[]" value="{{ +$row->qty }}"></td>
            <td><input type="text" class="form-control rate" name="rate[]" value="{{ numberFormat($row->rate) }}"></td>
            <td>
                <div class="row no-gutters">
                    <div class="col-6">
                        <select class="custom-select prod-taxid" name='prod_taxid[]'>
                            <option value="">-- VAT --</option>
                            @foreach ($tax_rates as $tax_rate)
                                <option value="{{ $tax_rate->value }}" {{ $tax_rate->value == $row->tax_id? 'selected' : '' }}>
                                    {{ $tax_rate->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6"><input type="text" class="form-control prod-tax" name="prod_tax[]" readonly></div>
                </div>                  
            </td>
            <td><input type="text" class="form-control prod-total" name="prod_total[]" readonly></td>
            <td>
                <!-- Action Dropdown Menu -->
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        action
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item add-row" href="javascript:"><i class="fa fa-plus"></i> Add Row</a>
                        <a class="dropdown-item text-danger remove-row" href="javascript:"><i class="fa fa-trash"></i> Remove</a>
                    </div>
                </div> 
            </td>
            <input type="hidden" class="prod-subtotal" name="prod_subtotal[]">
            <input type="hidden" class="prod-taxable" name="prod_taxable[]">
            <input type="hidden" class="inv-item-id" name="invoice_item_id[]" value="{{ $row->invoice_item_id }}">
            <input type="hidden" class="prodvar-id" name="productvar_id[]" value="{{ $row->productvar_id }}">
            <input type="hidden" class="prod-id" name="id[]" value="{{ $row->id }}">
            <!-- fx columns -->
            <input type="hidden" class="prod-fx-rate" name="prod_fx_rate[]" value="{{ round($row->rate*$creditnote->fx_curr_rate,4) }}">
            <input type="hidden" class="prod-fx-taxable" name="prod_fx_taxable[]" value="{{ +$row->prod_fx_taxable }}">
            <input type="hidden" class="prod-fx-subtotal" name="prod_fx_subtotal[]" value="{{ +$row->prod_fx_subtotal }}">
            <input type="hidden" class="prod-fx-tax" name="prod_fx_tax[]" value="{{ +$row->prod_fx_tax }}">
            <input type="hidden" class="prod-fx-total" name="prod_fx_total[]" value="{{ +$row->prod_fx_total }}">
            <input type="hidden" class="prod-fx-gain" name="prod_fx_gain[]" value="{{ +$row->prod_fx_gain }}">
            <input type="hidden" class="prod-fx-loss" name="prod_fx_loss[]" value="{{ +$row->prod_fx_loss }}">
            <!-- End fx columns -->
        </tr>
    @endforeach
@endif
<!-- End Edit Row -->