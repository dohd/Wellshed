@extends('core.layouts.app')

@section('title', 'Purchase Order | Reviews')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Purchase Orders Reviews</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchaseorders.partials.purchaseorders-header-buttons')
                </div>
            </div>
        </div>
    </div>    

    <div class="content-body"> 
        <div class="card">
            <div class="card-header">
                {{-- <a href="#" class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#reviewsModal">
                    <i class="fa fa-status" aria-hidden="true"></i> Reviews
                </a> --}}
            </div>
            <div class="card-header pb-0">
                <div id="credit_limit" class="align-center"></div>
                
            </div>
            <div class="card-body">
                {{ Form::model($po, ['route' => ['biller.purchaseorders.lpo_review_comment', $po], 'method' => 'POST', 'files' => true]) }}
                <div class="form-group row mb-3">
                    <div class="col-3">
                        <label for="">LPO Number</label>
                        <input type="text" id="" value="{{gen4tid('PO-', $po->tid)}}" class="form-control" disabled>
                    </div>
                    <div class="col-2">
                        <label for="">LPO Review Date</label>
                        <input type="text" name="review_date" id="review_date" class="form-control datepicker">
                    </div>
                    <div class="col-6">
                        <label for="">General Comments</label>
                        <input type="text" value="{{$po->note}}" name="general_comment" id="comment" class="form-control">
                    </div>
                </div>
                <div class="form-group row d-none">
                    <table class="table-responsive tfr" id="transxnTbl">
                        <thead>
                            <tr class="item_header bg-gradient-directional-blue white">
                                @foreach (['Item', 'Inventory Item', 'Expenses', 'Asset & Equipments', 'Total'] as $val)
                                    <th width="20%" class="text-center">{{ $val }}</th>
                                @endforeach                                                  
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">Line Total</td>
                                @for ($i = 0; $i < 4; $i++)
                                    <td class="text-center">0.00</td>
                                @endfor                                                
                            </tr>                                                  
                            <tr>
                                <td class="text-center">Tax</td>
                                @for ($i = 0; $i < 4; $i++)
                                    <td class="text-center">0.00</td>
                                @endfor                                                
                            </tr>
                            <tr>
                                <td class="text-center">Grand Total</td>
                                @for ($i = 0; $i < 4; $i++)
                                    <td class="text-center">0.00</td>
                                @endfor                                                                                                      
                            </tr>
    
                            <tr class="sub_c" style="display: table-row;">
                                <td align="right" colspan="4">
                                    <p id="milestone_warning" class="text-red ml-2" style="display: inline-block; color: red; font-size: 16px; "> </p>
                                    <p id="budget_warning" class="text-red ml-2" style="display: inline-block; color: red; font-size: 16px; "> </p>
                                </td>
                            </tr>
    
    
                            <tr class="sub_c" style="display: table-row;">
                                <td align="right" colspan="3">
                                    @foreach (['paidttl', 'grandtax', 'grandttl'] as $val)
                                        <input type="hidden" name="{{ $val }}" id="{{ $val }}" value="0"> 
                                    @endforeach 
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @include('focus.purchaseorders.partials.lpo_review_tab')
                <legend>Upload Files</legend><hr>
                <div class="form-group row">
                    <div class="col-10">
                        <div class="table-responsive">
                            <table id="docTbl" class="table">
                                <thead>
                                    <tr>
                                        <th>Caption</th>
                                        <th width="30%">Document</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="caption[]" class="form-control" id="caption-0"></td>
                                        <td><input type="file" name="file_name[]" class="form-control" id="file_name-0"></td>
                                        <td>
                                            <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                                <i class="fa fa-trash fa-lg text-danger"></i>
                                            </button>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="form-group row mt-3">
                    <div class="col-12">
                        <button class="btn btn-success btn-sm ml-2" type="button" id="addDoc">
                            <i class="fa fa-plus-square" aria-hidden="true"></i> Add File
                        </button>
                    </div>    
                </div>
                <div class="d-flex justify-content-center mt-2">
                    {{ Form::submit(@$po? 'LPO Review': 'Generate Purchase Order', ['class' => 'btn btn-success sub-btn btn-lg']) }}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    @include('focus.purchaseorders.partials.review_comments')
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    function select2Config(url, callback) {
        return {
            allowClear: true,
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            },
        }
    }

    $(document).ready(function () {

        $('#purchaseClass').select2({allowClear: true});
        $('.purchase-class').select2({allowClear: true});
        $('#stock_purchase_class-0').select2({allowClear: true});
        $('#exp_purchase_class-0').select2({allowClear: true});

        $('#purchaseClass').on('change', function() {
            if ($(this).val()) {
                $('#project').val(null).trigger('change');
                $('#project').prop('disabled', true);
            } else {
                $('#project').prop('disabled', false);
            }
        });

        $('#project').on('change', function() {
            if ($(this).val()) {
                $('#purchaseClass').val(null).trigger('change');
                $('#purchaseClass').prop('disabled', true);
            } else {
                $('#purchaseClass').prop('disabled', false);
            }
        });

    });

    let docRowId = 0;
    const docRow = $('#docTbl tbody tr').html();
    $('#addDoc').click(function() {
        docRowId++;
        let html = docRow.replace(/-0/g, '-'+docRowId);
        $('#docTbl tbody').append('<tr>' + html + '</tr>');
    });
    // remove schedule row
    $('#docTbl').on('click', '.remove_doc', function() {
        $(this).parents('tr').remove();
        docRowId--;
    });

    // defaults
    $('#ref_type').val("{{ $po->doc_ref_type }}");
    $('#tax').val("{{ $po->tax }}")

    // default datepicker values
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format')}}", autoHide: true})
    $('#date').datepicker('setDate', new Date("{{ $po->date }}"));
    $('#due_date').datepicker('setDate', new Date("{{ $po->due_date }}"));
    $('#user_ids').select2({allowClear:true})

    $('#select_user').click(function(){
        if ($(this).is(':checked', true)) {
            $('.user-remove').removeClass('d-none');
            $('#user_ids').attr('disabled', false);
        } else {
            $('.user-remove').addClass('d-none');
            $('#user_ids').attr('disabled', true);
        }
    });
    requisitionTypeChange();

    function requisitionTypeChange(){
        const requisition_type = $('#requisition_type').val();
        if (requisition_type == 'purchase_requisition') {
            $('.div_purchase_requisition').removeClass('d-none');
            $('.div_rfq').addClass('d-none');
            
            $('#rfq_id').val('').attr('disabled', true);
            $('#purchase_requisition_id').attr('disabled', true);
        }else if (requisition_type == 'rfq')
        {
            $('.div_purchase_requisition').addClass('d-none');
            $('.div_rfq').removeClass('d-none');
            $('#purchase_requisition_id').val('').attr('disabled', true);
            $('#rfq_id').attr('disabled', true);
        }
        $('#requisition_type').attr('disabled', true);
    }

    $('#supplierbox').select2(select2Config("{{ route('biller.suppliers.select') }}", (data) => {
        return {
            results: data.map(v => ({
                id: v.id,
                text: v.name+ (v.email? ' : '+v.email : ''),
                currency_id: v.currency_id,
                currency_code: v.currency?.code,
                currency_rate: v.currency?.rate,
                supplier_name: v.name || v.company,
                tax_id: v.taxid,
            }))
        };
    }));

    // On searching supplier
    $('#supplierbox').change(function() {
        // set currency
        const optData = $(this).select2('data')[0];
        if (optData && optData.currency_id) {
            const rate = accounting.unformat(optData.currency_rate);
            $('#currency').html(`<option value="${optData.currency_id}" rate="${rate}">${optData.currency_code || ''}</option>`);
            $('#currency').val(optData.currency_id);
            $('#fx_curr_rate').val(rate);
            $('#supplier').val(optData.supplier_name);
            $('#taxid').val(optData.tax_id);
            if (rate != 1) $('#fx_curr_rate').attr('readonly', false)
            else $('#fx_curr_rate').attr('readonly', true);
        } else if ($(this).find(':selected')[0]) {
            const {currencyid, currencyrate, currencycode, suppliername, taxid} = $(this).find(':selected')[0].attributes;
            if (currencyid.value) {
                const rate = accounting.unformat(currencyrate.value);
                $('#currency').html(`<option value="${currencyid.value}" rate="${rate}">${currencycode.value || ''}</option>`);
                $('#currency').val(currencyid.value);
                $('#fx_curr_rate').val(rate);
                $('#supplier').val(suppliername.value);
                $('#taxid').val(taxid.value);
                if (rate != 1) $('#fx_curr_rate').attr('readonly', false);
                else $('#fx_curr_rate').attr('readonly', true);
            } else {
                $('#supplier').val('');
                $('#taxid').val('');
                $('#currency').val('');
                $('#fx_curr_rate').val('').attr('readonly', true);
            }
        } else {
            $('#supplier').val('');
            $('#taxid').val('');
            $('#currency').val('');
            $('#fx_curr_rate').val('').attr('readonly', true);
        }

        let priceCustomer = '';
        $('#pricegroup_id option').each(function () {
            if ($('#supplierbox').val() == $(this).val()) {
                priceCustomer = $(this).val();
            }
        });
        $('#pricegroup_id').val(priceCustomer);
        
        $("#credit_limit").removeClass('d-none');
        $('#credit_limit').html('');
        $.ajax({
            type: "POST",
            url: "{{route('biller.suppliers.check_limit')}}",
            data: {
                supplier_id: $(this).val(),
            },
            success: function (result) {
                let total = $('#stock_grandttl').val();
                let number = total.replace(/,/g, '');
                let newTotal = parseFloat(number);
                    let outstandingTotal = parseFloat(result.outstanding_balance);
                    let total_aging = parseFloat(result.total_aging);
                    let credit_limit = parseFloat(result.credit_limit);
                    let total_age_grandtotal = total_aging+newTotal;
                let balance = total_age_grandtotal - outstandingTotal;
                $('#total_aging').val(result.total_aging.toLocaleString());
                $('#credit').val(result.credit_limit.toLocaleString());
                $('#outstanding_balance').val(result.outstanding_balance);
                if(balance > credit_limit && credit_limit > 0){
                    let exceeded = (balance - result.credit_limit).toFixed(2);
                    $("#credit_limit").html(`<h4 class="text-danger">Credit Limit Violated by:  ${accounting.formatNumber(exceeded)}</h4>`);
                } else {
                    $('#credit_limit').html('')
                }
            }
        });
    });
    $('#supplierbox').change();

    // load projects dropdown
    const projectUrl = "{{ route('biller.projects.project_search') }}";
    function projectData(data) {
        data = data.map(v => ({id: v.id, text: v.name, budget: v.budget ? v.budget.budget_total : 0 }));
        loadedProjectDetails = data;
        return {results: data};
    }
    $("#project").select2(select2Config(projectUrl, projectData));
    
    // On Tax change
    let taxIndx = 0;
    $('#tax').change(function() {
        if (taxIndx > 0) return;
        const tax = $(this).val();
        $('#rowtax-0').val(tax).change();
        $('#expvat-0').val(tax).change();
        $('#assetvat-0').val(tax).change();
        taxIndx++;
    });




    function getProjectMilestones(projectId){

        let select = $('#project_milestone');

        $.ajax({
            url: "{{ route('biller.getProjectMileStones') }}",
            method: 'GET',
            data: { projectId: projectId },
            dataType: 'json', // Adjust the data type accordingly
            success: function(data) {
                // This function will be called when the AJAX request is successful

                // Clear any existing options
                select.empty();

                if(data.length === 0){

                    select.append($('<option>', {
                        value: null,
                        text: 'No Milestones Created For This Project'
                    }));

                } else {

                    select.append($('<option>', {
                        value: 0,
                        text: 'Select a Budget Line'
                    }));

                    // Add new options based on the received data
                    for (var i = 0; i < data.length; i++) {

                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        const date = new Date(data[i].due_date);

                        select.append($('<option>', {
                            value: data[i].id,
                            text: data[i].name + ' | Balance: ' +  parseFloat(data[i].balance).toFixed(2) + ' | Due on ' + date.toLocaleDateString('en-US', options)
                        }));
                    }

                    let selectedOptionValue = "{{ @$po->project_milestone }}";
                    if (selectedOptionValue) {
                        select.val(selectedOptionValue);
                    }

                    checkMilestoneBudget(select.find('option:selected').text());

                }

            },
            error: function() {
                // Handle errors here
                console.log('Error loading data');
            }
        });


    }

    let purchaseGrandTotal = 0;

    function checkMilestoneBudget(milestoneString){

        // Get the value of the input field
        let selectedMilestone = milestoneString;

        // Specify the start and end strings
        let startString = 'Balance: ';
        let endString = ' | Due on';

        // Find the index of the start and end strings
        let startIndex = selectedMilestone.indexOf(startString);
        let endIndex = selectedMilestone.indexOf(endString, startIndex + startString.length);

        // Extract the string between start and end
        let milestoneBudget = parseFloat(selectedMilestone.substring(startIndex + startString.length, endIndex)).toFixed(2);

        // console.log("Milestone Budget is " + milestoneBudget + " and purchase total is " + purchaseGrandTotal);

        if(purchaseGrandTotal > milestoneBudget){

            // console.log( "Milestone Budget is " + milestoneBudget );
            // console.log( "Milestone Budget Exceeded" );
            $("#milestone_warning").text("Milestone Budget of " + milestoneBudget + " Exceeded!");
        }
        else {

            $("#milestone_warning").text("");
        }

    }

    $('#project').change(function() {

        getProjectMilestones($(this).val())
    });

    $('#project_milestone').change(function() {

        checkMilestoneBudget($(this).find('option:selected').text());
    });


    let loadedProjectDetails = [];
    let selectedProjectDetails = {};
    let selectedProjectBudget = 0;

    function checkProjectBudget(){

        let selectedProjectIndex = loadedProjectDetails.findIndex((item) => item.id === parseInt($("#project").val()));
        if(selectedProjectIndex !== -1) {

            selectedProjectDetails = loadedProjectDetails[selectedProjectIndex];
            selectedProjectBudget = parseInt(selectedProjectDetails.budget);
        }

        if(purchaseGrandTotal > selectedProjectBudget) $("#budget_warning").text("Project Budget of " + accounting.formatNumber(selectedProjectBudget) + " Exceeded!");
        else $("#budget_warning").text("");
    }



    // On project change
    $("#project").change(function() {

        checkProjectBudget();

        const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
        $('#projectexptext-0').val(projectText);
        $('#projectexpval-0').val($(this).val());
    });
    const projectName = "{{ $po->project? $po->project->name : '' }}";
    const projectId = "{{ $po->project_id }}";
    if (projectId) $('#project').append(new Option(projectName, projectId, true, true)).change();


    // Update transaction table
    const sumLine = (...values) => values.reduce((prev, curr) => {
            // Use 0 if curr is null, undefined, or empty string
            curr = curr ? curr.toString().replace(/,/g, '') : '0';
            return prev + (parseFloat(curr) || 0);  // Convert to float safely, fallback to 0 if NaN
        }, 0);
    function transxnCalc() {
        $('#transxnTbl tbody tr').each(function() {
            let total;
            switch ($(this).index()*1) {
                case 0:
                    $(this).find('td:eq(1)').text($('#stock_subttl').val());
                    $(this).find('td:eq(2)').text($('#exp_subttl').val());
                    $(this).find('td:eq(3)').text($('#asset_subttl').val());
                    total = sumLine($('#stock_subttl').val(), $('#exp_subttl').val(), $('#asset_subttl').val());
                    purchaseGrandTotal = total;
                    $('#paidttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#paidttl').val());
                    break;
                case 1:
                    $(this).find('td:eq(1)').text($('#stock_tax').val());
                    $(this).find('td:eq(2)').text($('#exp_tax').val());
                    $(this).find('td:eq(3)').text($('#asset_tax').val());
                    total = sumLine($('#stock_tax').val(), $('#exp_tax').val(), $('#asset_tax').val());
                    purchaseGrandTotal = total;
                    $('#grandtax').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grandtax').val());
                    break;
                case 2:
                    $(this).find('td:eq(1)').text($('#stock_grandttl').val());
                    $(this).find('td:eq(2)').text($('#exp_grandttl').val());
                    $(this).find('td:eq(3)').text($('#asset_grandttl').val());
                    total = sumLine($('#stock_grandttl').val(), $('#exp_grandttl').val(), $('#asset_grandttl').val());
                    purchaseGrandTotal = total;
                    $('#grandttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grandttl').val());
                    break;
            }

            checkMilestoneBudget($('#project_milestone').find('option:selected').text());
            checkProjectBudget();
        });
    }
    
    // Tax condition
    function taxRule(id, tax) {
        $('#'+ id +' option').each(function() {
            const itemtax = $(this).val();
            $(this).removeClass('d-none');
            if (itemtax != tax && itemtax != 0) $(this).addClass('d-none');
            $(this).attr('selected', false);
            if (itemtax == tax) $(this).attr('selected', true).change();
        }); 
    }


    /**
     * Stock Tab
     */
    let stockRowId = @json(count($po->products));
    const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];
    // $('#stockTbl tbody tr:lt(2)').remove(); 
    const stockUrl = "{{ route('biller.products.purchase_search') }}"
    $('.stockname').autocomplete(predict(stockUrl, stockSelect));
    $('#stockTbl').on('click', '#addstock, .remove', function() {
        if ($(this).is('#addstock')) {
            stockRowId++;
            const i = stockRowId;
            const html = stockHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#stockTbl tbody tr:eq(-3)').before(html);
            $('.stockname').autocomplete(predict(stockUrl, stockSelect));
            taxRule('rowtax-'+i, $('#tax').val());
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            const projectval = $("#project option:selected").val();
            $('#projectstocktext-'+i).val(projectText);
            $('#projectstockval-'+i).val(projectval);
            $("#credit_limit").removeClass('d-none');


            $('#stock_purchase_class-'+i).select2({allowClear: true});
            $('#stock_purchase_class-'+i).val($("#purchaseClass option:selected").val());

            //Add the previous supplier data            
            let priceCustomer = '';
            $('#pricegroup_id option').each(function () {
                if ($('#supplierbox').val() == $(this).val())
                priceCustomer = $(this).val();
            });
            $('#pricegroup_id').val(priceCustomer);
        }

        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcStock();
        }    
    })
    $('#stockTbl').on('change', '.qty, .price, .rowtax, .uom', function() {
        const el = $(this);
        const row = el.parents('tr:first');

        const qty = accounting.unformat(row.find('.qty').val());
        const price = accounting.unformat(row.find('.price').val());

        const rowtax = 1 + row.find('.rowtax').val()/100;
        const amount = qty * price * rowtax;
        const taxable = amount - (qty * price);

        row.find('.price').val(accounting.formatNumber(price));
        row.find('.amount').text(accounting.formatNumber(amount));
        row.find('.taxable').val(accounting.formatNumber(taxable));
        row.find('.stocktaxr').val(accounting.formatNumber(taxable));
        row.find('.stockamountr').val(accounting.formatNumber(amount));
        calcStock();

        if ($(this).is('.price')) {
            row.find('.uom').attr('required', true);
            row.next().find('.descr').attr('required', true);
        } else if ($(this).is('.uom')) {
            const purchasePrice = el.find('option:selected').attr('purchase_price');
            row.find('.price').val(purchasePrice).change();
        }
    });
    $('#qty-0').change();
    calcStock();
    function calcStock() {
        let tax = 0;
        let grandTotal = 0;
        $('#stockTbl tbody tr').each(function() {
            if (!$(this).find('.qty').val()) return;
            const qty = accounting.unformat($(this).find('.qty').val());
            const price = accounting.unformat($(this).find('.price').val());
            const rowtax = $(this).find('.rowtax').val()/100 + 1;

            const amount = qty * price * rowtax;
            const taxable = amount - qty * price;
            tax += taxable;
            grandTotal += amount;
        });
        $('#invtax').text(accounting.formatNumber(tax));
        $('#stock_tax').val(accounting.formatNumber(tax));
        $('#stock_grandttl').val(accounting.formatNumber(grandTotal));
        $('#stock_subttl').val(accounting.formatNumber(grandTotal - tax));
        $("#credit_limit").html('');
        let credit_limit = $('#credit').val() ? $('#credit').val().replace(/,/g, '') : 0;
        let total_aging = $('#total_aging').val() ? $('#total_aging').val().replace(/,/g, '') : 0;
        let outstanding_balance = $('#outstanding_balance').val() ? $('#outstanding_balance').val().replace(/,/g, '') : 0;
        let balance = total_aging.toLocaleString() - outstanding_balance.toLocaleString() + grandTotal;
        if (balance > credit_limit && credit_limit > 0) {
            let exceeded = (balance -credit_limit).toFixed(2);
            $("#credit_limit").html(`<h4 class="text-danger">Credit Limit Violated by:  ${accounting.formatNumber(exceeded)}</h4>`);
        }else{
            $("#credit_limit").html('');
        }
        transxnCalc();
    }

    // stock select autocomplete
    let stockNameRowId = 0;
    function stockSelect(event, ui) {
        const { data } = ui.item;
        const i = stockNameRowId;

        // Check if the necessary properties exist in the data object before accessing them
        const stockItemId = data?.id || '';
        const stockDescr = data?.name || '';
        const productCode = data?.code || data?.product_code || '';
        let product_id = 0;
        let supplier_product_id = 0;

        $('#stockitemid-'+i).val(stockItemId);
        $('#stockdescr-'+i).val(stockDescr);
        $('#product_code-'+i).val(productCode);

        let purchasePrice = +data.purchase_price;
        const forexRate = $('#fx_curr_rate').val(); 
        if (forexRate > 1) purchasePrice = (purchasePrice/forexRate).toFixed(2);
        $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();

        if(data?.code){
            product_id = data.id;
            supplier_product_id = 0;
        }else{
            product_id = data?.product_id;
            supplier_product_id = data?.id;
        }
        $('#product_id-'+i).val(product_id);
        $('#supplier_product_id-'+i).val(supplier_product_id);

        $('#uom-'+i).html('');
        if(data.units)
            data.units.forEach(v => {
                const rate = accounting.unformat(v.base_ratio) * purchasePrice;
                const option = `<option value="${v.code}" purchase_price="${rate}" >${v.code}</option>`;
                $('#uom-'+i).append(option);
            });
        if(data.uom){
            const option = `<option value="${data.uom}"  >${data.uom}</option>`;
            $('#uom-'+i).append(option);
        }
    }
    $('#stockTbl').on('mouseup', '.stockname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.stockname')) stockNameRowId = id;
    });    

    
    /**
     * Expense Tab
     */
    let expRowId = @json(count($po->products));
    const expHtml = [$('#expTbl tbody tr:eq(0)').html(), $('#expTbl tbody tr:eq(1)').html()];
    $('#expTbl tbody tr:lt(2)').remove(); 
    const expUrl = "{{ route('biller.accounts.account_search') }}?type=Expense";
    $('.accountname').autocomplete(predict(expUrl, expSelect));
    $('.projectexp').autocomplete(predict(projectUrl, projectExpSelect));
    $('#expTbl').on('click', '#addexp, .remove', function() {
        if ($(this).is('#addexp')) {
            expRowId++;
            const i = expRowId;
            const html = expHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#expTbl tbody tr:eq(-3)').before(html);
            $('.accountname').autocomplete(predict(expUrl, expSelect));
            $('.projectexp').autocomplete(predict(projectUrl, projectExpSelect));
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');

            $('#projectexptext-'+i).val(projectText);
            $('#projectexpval-'+i).val($("#project option:selected").val());

            $('#exp_purchase_class-'+i).select2({allowClear: true});
            $('#exp_purchase_class-'+i).val($("#purchaseClass option:selected").val());

            taxRule('expvat-'+i, $('#tax').val());
        }
        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcExp();
        }    
    });
    $('#expTbl').on('change', '.exp_qty, .exp_price, .exp_vat', function() {
        const $tr = $(this).parents('tr:first');
        const qty = $tr.find('.exp_qty').val();
        const price = $tr.find('.exp_price').val().replace(/,/g, '') || 0;
        const rowtax = $tr.find('.exp_vat').val()/100 + 1;
        const amount = qty * price * rowtax;
        const taxable = amount - (qty * price);

        $tr.find('.exp_price').val((price*1).toLocaleString());
        $tr.find('.exp_tax').text(taxable.toLocaleString());
        $tr.find('.exp_amount').text(amount.toLocaleString());
        $tr.find('.exptaxr').val(taxable.toLocaleString());
        $tr.find('.expamountr').val(amount.toLocaleString());
        calcExp();

        if ($(this).is('.exp_price')) {
            $tr.next().find('.descr').attr('required', true);
        }
    });
    $('#expqty-0').change();
    function calcExp() {
        let tax = 0;
        let totalInc = 0;
        $('#expTbl tbody tr').each(function() {
            if (!$(this).find('.exp_qty').val()) return;
            const qty = $(this).find('.exp_qty').val();
            const price = $(this).find('.exp_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.exp_vat').val()/100 + 1;

            const amount = qty * price * rowtax;
            const taxable = amount - qty * price;
            tax += parseFloat(taxable.toFixed(2));
            totalInc += parseFloat(amount.toFixed(2));
        });
        $('#exprow_taxttl').text(tax.toLocaleString());
        $('#exp_tax').val(tax.toLocaleString());
        $('#exp_subttl').val((totalInc - tax).toLocaleString());
        $('#exp_grandttl').val((totalInc).toLocaleString());
        transxnCalc();
    }

    // account and project autocomplete
    let accountRowId = 0;
    let projectExpRowId = 0;
    function expSelect(event, ui) {
        const {data} = ui.item;
        const i = accountRowId;
        $('#expitemid-'+i).val(data.id);
    }
    function projectExpSelect(event, ui) {
        const {data} = ui.item;
        const i = projectExpRowId;
        $('#projectexpval-'+i).val(data.id);
    }
    $('#expTbl').on('mouseup', '.accountname, .projectexp', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.accountname')) accountRowId = id;
        if ($(this).is('.projectexp')) projectExpRowId = id;
    });


    /**
     * Asset tab
     */
    let assetRowId = @json(count($po->products));;
    const assetHtml = [$('#assetTbl tbody tr:eq(0)').html(), $('#assetTbl tbody tr:eq(1)').html()];
    $('#assetTbl tbody tr:lt(2)').remove(); 
    const assetUrl = "{{ route('biller.assetequipments.product_search') }}";
    $('.assetname').autocomplete(predict(assetUrl, assetSelect));
    $('#assetTbl').on('click', '#addasset, .remove', function() {
        if ($(this).is('#addasset')) {
            assetRowId++;
            const i = assetRowId;
            const html = assetHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#assetTbl tbody tr:eq(-3)').before(html);
            $('.assetname').autocomplete(predict(assetUrl, assetSelect));
            taxRule('assetvat-'+i, $('#tax').val());
        }
        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcAsset();
        }    
    });    
    $('#assetTbl').on('change', '.asset_qty, .asset_price, .asset_vat', function() {
        const $tr = $(this).parents('tr:first');
        const qty = $tr.find('.asset_qty').val();
        const price = $tr.find('.asset_price').val().replace(/,/g, '') || 0;
        const rowtax = $tr.find('.asset_vat').val()/100 + 1;
        const amount = qty * price * rowtax;
        const taxable = amount - (qty * price);

        $tr.find('.asset_price').val((price*1).toLocaleString());
        $tr.find('.asset_tax').text(taxable.toLocaleString());
        $tr.find('.asset_amount').text(amount.toLocaleString());
        $tr.find('.assettaxr').val(taxable.toLocaleString());
        $tr.find('.assetamountr').val(amount.toLocaleString());
        calcAsset();

        if ($(this).is('.asset_price')) {
            $tr.next().find('.descr').attr('required', true);
        }
    });
    $('#assetqty-0').change();
    function calcAsset() {
        let tax = 0;
        let totalInc = 0;
        $('#assetTbl tbody tr').each(function() {
            if (!$(this).find('.asset_qty').val()) return;
            const qty = $(this).find('.asset_qty').val();
            const price = $(this).find('.asset_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.asset_vat').val()/100 + 1;
            
            const amount = qty * price * rowtax;
            const taxable = amount -  qty * price;
            tax += parseFloat(taxable.toFixed(2));
            totalInc += parseFloat(amount.toFixed(2));
        });
        $('#assettaxrow').text(tax.toLocaleString());
        $('#asset_tax').val(tax.toLocaleString());
        $('#asset_subttl').val((totalInc - tax).toLocaleString());
        $('#asset_grandttl').val((totalInc).toLocaleString());
        transxnCalc();
    }

    // asset and project autocomplete
    let assetNameRowId = 0;
    function assetSelect(event, ui) {
        const {data} = ui.item;
        const i = assetNameRowId;
        $('#assetitemid-'+i).val(data.id);
        $('#assetprice-'+i).val(0).change();
    } 
    $('#assetTbl').on('mouseup', '.assetname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.assetname')) assetNameRowId = id;
    });    

    // autocomplete config method
    function predict(url, callback) {
        return {
            source: function(request, response) {
                $.ajax({
                    url,
                    dataType: "json",
                    method: "POST",
                    data: {keyword: request.term, pricegroup_id: $('#pricegroup_id').val()},
                    success: function(data) {
                        response(data.map(v => ({
                            label: v.name,
                            value: v.name,
                            data: v
                        })));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: callback
        };
    }
</script>
@endsection
