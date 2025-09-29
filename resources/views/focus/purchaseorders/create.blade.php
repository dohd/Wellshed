
@extends('core.layouts.app')

@section('title', 'Purchase Order | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Purchase Orders Management</h4>
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
            <div class="card-header pb-0">
                <div id="credit_limit" class="align-center"></div>
            </div>
            <div class="card-body">
                {{ Form::open(['route' => 'biller.purchaseorders.store', 'method' => 'POST']) }}
                    @include('focus.purchaseorders.form')
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    function select2Config(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            },
            allowClear: true,
        }
    }
    function select2Config2(url, callback) {
        return select2Config(url, callback);
    }

    // datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format')}}", autoHide:true});
    $('#user_ids').select2({allowClear:true})
        
    $('#currency').change(function() {
        const rate = accounting.unformat($(this).find('option:selected').attr('rate'));
        $('#fx_curr_rate').val(rate || '');
        if (rate == 1) {
            $('#fx_curr_rate').attr('readonly', true);
        } else {
            $('#fx_curr_rate').attr('readonly', false);
        }
    });


    $(document).ready(function () {

        $('#purchase_class').select2({allowClear: true});
        $('#stock_purchase_class-0').select2({allowClear: true});
        $('#exp_purchase_class-0').select2({allowClear: true});

        $('#purchase_class').on('change', function() {
            if ($(this).val()) {
                $('#project').val(null).trigger('change');
                $('#project').prop('disabled', true);
            } else {
                $('#project').prop('disabled', false);
            }
        });

        $('#project').on('change', function() {
            if ($(this).val()) {
                $('#purchase_class').val(null).trigger('change');
                $('#purchase_class').prop('disabled', true);
            } else {
                $('#purchase_class').prop('disabled', false);
            }
        });

    });

    $('#active2').on('input', '[id^="item_purchase_class-"]', function() {
        var inputId = this.id;
        var numberAtEnd = inputId.match(/\d+$/);
        let rowId = null
        if (numberAtEnd) rowId = parseInt(numberAtEnd[0]);
        $('#projectexptext-' + rowId).val('');
    });
    $('#active2').on('input', '[id^="projectexptext-"]', function() {
        var inputId = this.id;
        var numberAtEnd = inputId.match(/\d+$/);
        let rowId = null
        if (numberAtEnd) rowId = parseInt(numberAtEnd[0]);
        $('#item_purchase_class-' + rowId).val(''); 
    });

    // load suppliers
    const supplierUrl = "{{ route('biller.suppliers.select') }}";
    function supplierData(data) {
        return {results: data.map(v => ({
            id: v.id, 
            text: v.name+ (v.email? ' : '+v.email : ''),
            currency_id: v.currency_id,
            currency_code: v.currency?.code,
            currency_rate: v.currency?.rate,
            supplier_name: v.name || v.company,
            tax_id: v.taxid,
        }))};
    }
    $('#supplierbox').select2(select2Config(supplierUrl, supplierData));


    // On searching supplier
    $('#supplierbox').change(function() {
        // set currency
        const optData = $(this).select2('data')[0]
        if (optData && optData.currency_id) {
            const rate = accounting.unformat(optData.currency_rate);
            $('#currency').html(`<option value="${optData.currency_id}" rate="${rate}">${optData.currency_code || ''}</option>`);
            $('#currency').val(optData.currency_id);
            $('#fx_curr_rate').val(rate);
            $('#supplier').val(optData.supplier_name);
            $('#taxid').val(optData.tax_id);
            if (rate != 1) $('#fx_curr_rate').attr('readonly', false)
            else $('#fx_curr_rate').attr('readonly', true);
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
        
        $('#credit_limit').html('');
        $.post("{{route('biller.suppliers.check_limit')}}", {supplier_id: $('#supplierbox').val()})
        .done(result => {
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
                let exceeded = (balance-result.credit_limit).toFixed(2);
                $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by: ${accounting.formatNumber(exceeded)}</h4>`);
            }else{
                $('#credit_limit').html('')
            }
        })
        .fail((xhr, status, error) => console.log(error))
    });

    //Select Quote From quotes
    $('#quotebox').change(function() {
        const name = $('#quotebox option:selected').text().split(' : ')[0];
        const [id, quote_no] = $(this).val().split('-');
        $('#quoteid').val(quoteid);
        //$('#quoteid').val(id);
        $('#quote').val(name);
        purchaseorderChange();
    });
     // load suppliers
     const quoteUrl = "{{ route('biller.queuerequisitions.select_queuerequisition') }}";
    function quoteData(data) {
        return {results: data.map(v => ({id: v.id+'-'+v.quote_no, text: 'Qt-'+v.quote_no+' : '+v.client_branch}))};
    }
    $('#quotebox').select2(select2Config2(quoteUrl, quoteData));

    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        select2: {
            allowClear: true,
        },
        fetchLpoGoods: (queuerequisition_id, pricelist) => {
            return $.ajax({
                url: "{{ route('biller.queuerequisitions.goods') }}",
                type: 'POST',
                quietMillis: 50,
                data: {queuerequisition_id, pricelist},
            });
        }
    };
    $('#quoteselect').change(function () { 
        const name = $('#quoteselect option:selected').val();
        const pricelist = $('#pricegroup_id').val();
        purchaseorderChange(name, pricelist);
        
    });

    function purchaseorderChange(value, pricelist) {
        const el = value;
        $('#stockTbl tbody').html('');
        if (!value) return;
        config.fetchLpoGoods(value, pricelist).done(data => {
            data.forEach((v,i) => {
                $('#stockTbl tbody').append(this.productRow(v,i));
                $('.projectstock').autocomplete(prediction(projectstockUrl,projectstockSelect));
            });
            if(data.length > 0){
                $('#stockTbl tbody').append(this.addRow());
                
            }
        });
    }

    function productRow(v,i) {
        let rate = v.queuerequisition_supplier.rate;
        return `
            <tr>
                <td><input type="text" class="form-control stockname" value="${v.queuerequisition_supplier.descr}" name="name[]" placeholder="Product Name" id='stockname-${i+1}'></td>
                <td><input type="text" class="form-control qty" name="qty[]" id="qty-${i+1}" value="${v.qty_balance}"></td>  
                <td><input type="text" name="uom[]" id="uom-${i+1}" value="${v.uom}" class="form-control uom" required></td> 
                <td><input type="text" value="${rate}" class="form-control price" name="rate[]" id="price-${i+1}"></td>
                <td>
                    <select class="form-control rowtax" name="itemtax[]" id="rowtax-${i+1}">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }}
                            </option>
                        @endforeach                                                    
                    </select>
                </td>
                <td><input type="text" class="form-control taxable" value="0"></td>
                <td class="text-center"><b><span class='amount' id="result-${i+1}">0</span></b></td> 
                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" id="stockitemid-0" value="${v.id}" name="item_id[]">
                <input type="hidden" class="stocktaxr" name="taxrate[]">
                <input type="hidden" class="stockamountr" name="amount[]">
                <input type="hidden" name="type[]" value="Requisit">
                <input type="hidden" name="id[]" value="0">
                <input type="hidden" value="${i+1}">
            </tr>
            <tr>
                <td colspan="2">
                    <input type="text" id="stockdescr-0" value="${v.system_name}" class="form-control descr" name="description[]" placeholder="Product Description">
                </td>
                <td><input type="text" class="form-control product_code" value="${v.product_code}" name="product_code[]" id="product_code-${i+1}" readonly></td>
                <td>
                    <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid">
                        <option value="">-- Warehouse --</option>
                        @foreach ($warehouses as $row)
                            <option value="{{ $row->id }}">{{ $row->title }}</option>
                        @endforeach
                    </select>
                </td>
                <td colspan="3">
                    <input type="text" class="form-control projectstock" value="${v.quote_no}" id="projectstocktext-${i+1}" placeholder="Search Project By Name">
                    <input type="hidden" name="itemproject_id[]" id="projectstockval-${i+1}" >
                </td>
                <td colspan="6"></td>
            </tr>
        `;
    }
    function addRow(){
        return `
            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="7"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="6" align="right"><b>{{trans('general.total_tax')}}</b></td>                   
                <td align="left" colspan="2"><span id="invtax" class="lightMode">0</span></td>
            </tr>
            <tr class="bg-white">
                <td colspan="6" align="right"><b>Inventory Total</b></td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="stock_grandttl" value="0.00" id="stock_grandttl" readonly>
                    <input type="hidden" name="stock_subttl" value="0.00" id="stock_subttl">
                    <input type="hidden" name="stock_tax" value="0.00" id="stock_tax">
                </td>
            </tr>
        `;
    }

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

    $('#tax').change(function () {
        // if (taxIndx > 0) return;
        const tax = $(this).val();

        $('[id^=rowtax-]').each(function () {
            $(this).val(tax).change();
        });

        $('[id^=expvat-]').each(function () {
            $(this).val(tax).change();
        });

        $('[id^=assetvat-]').each(function () {
            $(this).val(tax).change();
        });

        taxIndx++;
    });


    function getProjectMilestones(projectId){
        let select = $('#project_milestone');
        $.ajax({
            url: "{{ route('biller.getProjectMileStones') }}",
            method: 'GET',
            data: { projectId: projectId },
            dataType: 'json',
            success: function(data) {
                select.empty();
                if(data.length === 0){
                    select.append($('<option>', {
                        value: null,
                        text: 'No Milestones Created For This Project'
                    }));
                } else {
                    select.append($('<option>', {
                        value: null,
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
                console.log('Error loading data');
            }
        });
    }

    function milestones(select2Input, projectId){
        // let select = $('#project_milestone');
        let select = select2Input;
        $.ajax({
            url: "{{ route('biller.getProjectMileStones') }}",
            method: 'GET',
            data: { projectId: projectId, is_expense: 1},
            dataType: 'json', // Adjust the data type accordingly
            success: function(data) {

                // Clear any existing options
                select.empty();
                if(data.length === 0){
                    select.append($('<option>', {
                        value: null,
                        text: 'No Milestones Created For This Project'
                    }));
                } else {
                    select.append($('<option>', {
                        value: null,
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
                    let selectedOptionValue = "{{ @$purchase->project_milestone }}";
                    if (selectedOptionValue) {
                        select.val(selectedOptionValue);
                    }
                    checkMilestoneBudget(select.find('option:selected').text());
                }
            },
            error: function() {
                // Handle errors here
                //console.log('Error loading data');
            }
        });

        destroySelect2(select);
        select.attr('data-placeholder', 'Select a Budget Line');
        select.select2({ allowClear: true, placeholder: "Select a Budget Line"});
    }

    function destroySelect2(el) {
        el.removeClass('select2-hidden-accessible').removeAttr('data-select2-id');
        el.find('option').removeAttr('data-select2-id');
        el.siblings('.select2').remove();
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
        if(purchaseGrandTotal > milestoneBudget){
            $("#milestone_warning").text("Milestone Budget of " + milestoneBudget + " Exceeded!");
        } else {
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
        $('#projectstocktext-0').val(projectText);
        $('#projectstockval-0').val($(this).val());
        milestones($('#stock-budgetline-0'), $(this).val());
    });
    // Update transaction table
    const sumLine = (...values) => values.reduce((prev, curr) => prev + curr.replace(/,/g, '')*1, 0);
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

    $('#select_user').click(function(){
        if ($(this).is(':checked', true)) {
            $('.user-remove').removeClass('d-none');
            $('#user_ids').attr('disabled', false);
        } else {
            $('.user-remove').addClass('d-none');
            $('#user_ids').attr('disabled', true);
        }
    });


    /**
     * Stock Tab
     */
    let stockRowId = 0;
    const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];
    const stockUrl = "{{ route('biller.products.purchase_search') }}"
    const projectstockUrl = "{{ route('biller.projects.project_search') }}"
    $('.stockname').autocomplete(predict(stockUrl, stockSelect));
    $('.projectstock').autocomplete(prediction(projectstockUrl,projectstockSelect));
    $('#rowtax-0').mousedown(function() { taxRule('rowtax-0', $('#tax').val()); });
    $('#import_request_id-0').select2({ allowClear: true });
    $('#stockTbl').on('click', '#addstock, .remove, #check, .cp_check', function() {
        if ($(this).is('#addstock')) {
            stockRowId++;
            const i = stockRowId;
            const html = stockHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#stockTbl tbody tr:eq(-3)').before(html);
            $('.stockname').autocomplete(predict(stockUrl, stockSelect));
            $('.projectstock').autocomplete(prediction(projectstockUrl,projectstockSelect));
            $('#increment-'+i).val(i+1);
            $('#import_request_id-'+i).select2({ allowClear: true });
            $('#div_import-'+i).addClass('d-none');
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            const projectval = $("#project option:selected").val();
            $('#projectstocktext-'+i).val(projectText);
            $('#projectstockval-'+i).val(projectval);

            $('#stock_purchase_class-'+i).select2({allowClear: true});
            $('#stock_purchase_class-'+i).val($("#purchase_class option:selected").val());

            taxRule('rowtax-'+i, $('#tax').val());

            //Add the previous supplier data            
            let priceCustomer = '';
            $('#pricegroup_id option').each(function () {
                if ($('#supplierbox').val() == $(this).val())
                priceCustomer = $(this).val();
            });
            $('#pricegroup_id').val(priceCustomer);
            $('#stock-budgetline-'+i).select2({ allowClear: true });
            if (projectval) {
                milestones($('#stock-budgetline-'+i), projectval);
            }
            // Add copy prj checkbox
            let y = i-1;
            if($('#cp_check-'+y).val() == 1){
                if($("#projectstockval-"+y).val()){
                   $('#projectstocktext-'+i).val($("#projectstocktext-"+y).val());
                   $('#projectstockval-'+i).val($("#projectstockval-"+y).val());
                   milestones($('#stock-budgetline-'+i), $("#projectstockval-"+y).val());
                   $('#stock_purchase_class-'+i).prop('disabled',true);
                   $('#purchase_class_budget-'+i).prop('disabled',false);
                }else if($("#stock_purchase_class-"+y).val())
                {
                    let sourceVal = $(`#stock_purchase_class-${y}`).val();
                    let $targetSelect = $(`#stock_purchase_class-${i}`);
                    // Set selected option directly if it exists
                    $targetSelect.val(sourceVal).trigger('change');
                }
            }
        }

        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcStock();
        } 
        if($(this).is('#check')){
            const row = $(this).parents('tr:first');
            if ($(this).is(':checked', true)) {
                row.find('.div_import').removeClass('d-none');
            } else {
                row.find('.div_import').addClass('d-none');
                $('#import_request_id-'+i).val('');
            }
        }  
        if($(this).is('.cp_check')){
            const row = $(this).parents('tr:first');
            if ($(this).is(':checked', true)) {
                row.find('.cp_check').val(1);
            } else {
                row.find('.cp_check').val(0);
            }
        }  
    })
    $('#stockTbl').on('change', '.qty, .price, .rowtax, .uom, #quoteselect, .warehouse', function() {
        const el = $(this);
        const row = el.parents('tr:first');

        const qty = accounting.unformat(row.find('.qty').val());
        const price = accounting.unformat(row.find('.price').val());

        const rowtax = 1 + row.find('.rowtax').val()/100;
        const amount = qty * price * rowtax;
        const taxable = qty * price * (rowtax - 1);

        row.find('.price').val(accounting.formatNumber(price));
        row.find('.amount').text(accounting.formatNumber(amount));
        row.find('.taxable').val(accounting.formatNumber(taxable));
        row.find('.stocktaxr').val(accounting.formatNumber(taxable));
        row.find('.stockamountr').val(accounting.formatNumber(amount));
        calcStock();

        if (el.is('.price')) {
            row.next().find('.descr').attr('required', true);
        } else if (el.is('.uom')) {
            const purchasePrice = el.find('option:selected').attr('purchase_price');
            row.find('.price').val(purchasePrice).change();
        }else if(el.is('.warehouse')){
            row.find('.projectstock').val('').attr('disabled', true);
            row.find('.purchase-class').val('').attr('disabled', true);
            row.find('.purchase_class_budget').val('').attr('disabled', false);
            const projectid = row.find('.stockitemprojectid').attr('id');
            row.find('.stockitemprojectid').val(0);
            row.find('.stock-budgetline').prop('disabled',true);
            row.find('.milestone_id').val('').prop('disabled',false);
        }
    });
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
        let credit_limit = $('#credit').val().replace(/,/g, '');
        let total_aging = $('#total_aging').val().replace(/,/g, '');
        let outstanding_balance = $('#outstanding_balance').val().replace(/,/g, '');
        let balance = total_aging.toLocaleString() - outstanding_balance.toLocaleString() + grandTotal;
        if (balance > credit_limit && credit_limit > 0) {
            let exceeded = (balance - credit_limit).toFixed(2);
            $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by:  ${accounting.formatNumber(exceeded)}</h4>`);
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
        let productCode = data?.code || data?.product_code || '';
        let product_id = 0;
        let supplier_product_id = 0;
        
        $('#stockitemid-'+i).val(stockItemId);
        $('#stockdescr-'+i).val(stockDescr);
        $('#product_code-'+i).val(productCode);

        let purchasePrice = +data.purchase_price;
        const forexRate = $('#fx_curr_rate').val(); 
        if (forexRate > 1) purchasePrice = (purchasePrice/forexRate).toFixed(2);
        $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();

        $('#uom-'+i).html('');
        if(data?.code){
            product_id = data.id;
            supplier_product_id = 0;
        }else{
            product_id = data?.product_id;
            supplier_product_id = data?.id;
        }

        if(data.product){
            if(data.product.stock_type == 'service'){
                $('#warehouseid-'+i).attr('disabled',true);
                $('#ware-'+i).attr('disabled',false);
                $('#price-'+i).attr('readonly',false);
            }else {
                $('#ware-'+i).attr('disabled',true);
                $('#warehouseid-'+i).attr('disabled',false);
                $('#price-'+i).attr('readonly',true);
            }
        }
        $('#product_id-'+i).val(product_id);
        $('#supplier_product_id-'+i).val(supplier_product_id);
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


    // stock select autocomplete
    let projectStockRowId = 0;
    function projectstockSelect(event, ui) {
        const {data} = ui.item;
        const i = projectStockRowId;
        $('#projectstockval-'+i).val(data.id);
        $('#warehouseid-'+i).attr('disabled', true);
        $(`#ware-${i}`).val('').prop('disabled',false);
        milestones($('#stock-budgetline-'+i), data.id);
        $('#stock_purchase_class-'+i).prop('disabled',true);
        $('#purchase_class_budget-'+i).prop('disabled',false);
        
    }
    $('#stockTbl').on('mouseup', '.stockname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.stockname')) stockNameRowId = id;
    }); 
    $('#stockTbl').on('mouseup', '.projectstock', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.projectstock')) projectStockRowId = id;
    });    
    $('#stockTbl').on('change', '.purchase-class', function() {
        const el = $(this);
        const row = el.parents('tr:first');
        row.find('.projectstock').prop('disabled',true);
        row.find('.stock-budgetline').prop('disabled',true);
        row.find('.milestone_id').prop('disabled',false);
        row.find('.warehouse').prop('disabled',true);
        row.find('.ware').val('').prop('disabled',false);
    });    

    /**
     * Expense Tab
     */
    let expRowId = 0;
    const expHtml = [$('#expTbl tbody tr:eq(0)').html(), $('#expTbl tbody tr:eq(1)').html()];
    const expUrl = "{{ route('biller.accounts.account_search') }}?type=Expense";
    $('.accountname').autocomplete(predict(expUrl, expSelect));
    $('.projectexp').autocomplete(predict(projectUrl, projectExpSelect));
    $('#expvat-0').mousedown(function() { taxRule('expvat-0', $('#tax').val()); });
    $('#expTbl').on('click', '#addexp, .remove, #check', function() {
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
            $('#expenseinc-'+i).val(i+1);
            $('#import_request_id-'+i).select2({ allowClear: true });
            $('#div_import-'+i).addClass('d-none');
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');

            $('#projectexptext-'+i).val(projectText);
            $('#projectexpval-'+i).val($("#project").val());

            $('#exp_purchase_class-'+i).select2({allowClear: true});
            $('#exp_purchase_class-'+i).val($("#purchase_class option:selected").val());

            taxRule('expvat-'+i, $('#tax').val());
        }
        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcExp();
        }   
        if($(this).is('#check')){
            const row = $(this).parents('tr:first');
            if ($(this).is(':checked', true)) {
                row.find('.div_import').removeClass('d-none');
            } else {
                row.find('.div_import').addClass('d-none');
                $('#import_request_id-'+i).val('');
            }
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
    let assetRowId = 0;
    const assetHtml = [$('#assetTbl tbody tr:eq(0)').html(), $('#assetTbl tbody tr:eq(1)').html()];
    const assetUrl = "{{ route('biller.assetequipments.product_search') }}";
    $('.assetname').autocomplete(predict(assetUrl, assetSelect));
    $('#assetvat-0').mousedown(function() { taxRule('assetvat-0', $('#tax').val()); });
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
            $('#assetinc-'+i).val(i+1);
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
    function prediction(url, callback) {
        return {
            source: function(request, response) {
                $.ajax({
                    url,
                    dataType: "json",
                    method: "POST",
                    data: {keyword: request.term, projectstock: $('#projectstock').val()},
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
<script>
    const configs = {};
    const Forms = {
        init(){
            $('#rfq_id').select2({allowClear: true});
            $('#purchase_requisition_id').select2({allowClear: true});
            $('#requisition_type').change(this.requisitionTypeChange);
            $('#rfq_id').change(this.rfqChange);
            $('#purchase_requisition_id').change(this.purchaseRequisitionChange);
        },
        requisitionTypeChange(){
            const requisition_type = $(this).val();
            if (requisition_type == 'purchase_requisition') {
                $('.div_purchase_requisition').removeClass('d-none');
                $('.div_rfq').addClass('d-none');
                
                $('#rfq_id').val('');
            }else if (requisition_type == 'rfq')
            {
                $('.div_purchase_requisition').addClass('d-none');
                $('.div_rfq').removeClass('d-none');
                $('#purchase_requisition_id').val('');
            }
        },
        rfqChange(){
            const rfq_id = $(this).val();
            $('#tax').change();
            $('#stockTbl tbody').html('');
            $.ajax({
                url: "{{ route('biller.rfq.get_items')}}",
                method: "POST",
                data: {
                    rfq_id: rfq_id
                },
                success: function(data){
                    console.log(data.exempted_generic_count)
                    if(data.exempted_generic_count > 0){

                        alert(data.exempted_generic_count+' items have been omited of Generic Type')
                    }
                    data.items.forEach((v,i) => {
                        $('#stockTbl tbody').append(Forms.stockProductRow(v,i));
                    });
                    $('#stockTbl tbody').append(Forms.lastTotalRow());
                    setTimeout(() => {
                        $('#stockTbl tbody .rowtax').trigger('change'); // Trigger change on all `.rowtax` elements
                    }, 100);
                }
            })
        },
        purchaseRequisitionChange(){
            const purchase_requisition_id = $(this).val();
            $('#stockTbl tbody').html('');
            $.ajax({
                url: "{{ route('biller.purchase_requisitions.get_items')}}",
                method: "POST",
                data: {
                    purchase_requisition_id: purchase_requisition_id
                },
                success: function(data){
                    if(data.exempted_generic_count > 0){

                        alert(data.exempted_generic_count+' items have been omited of Generic Type')
                    }
                    data.items.forEach((v,i) => {
                        $('#stockTbl tbody').append(Forms.stockProductRow(v,i));
                    });
                    $('#stockTbl tbody').append(Forms.lastTotalRow());
                    setTimeout(() => {
                        $('#stockTbl tbody .rowtax').trigger('change'); // Trigger change on all `.rowtax` elements
                    }, 100);
                }
            })
        },

        stockProductRow(v,i){
            return `
                <tr>
                    <td><input type="text" class="form-control increment" value="${i+1}" id="increment-0" disabled></td>
                    <td>
                        <input type="text" class="form-control stockname" name="name[]" placeholder="Product Name" value="${v.product_name}" id='stockname-0'>
                        <input type="hidden" id="stockitemid-0" name="item_id[]" value="${v.product_id}">
                    </td>
                    <td><input type="text" class="form-control qty" name="qty[]" id="qty-0" value="${v.qty}"></td>  
                    <td><select name="uom[]" id="uom-0" class="form-control uom" >
                        <option value="${v.uom}">${v.uom}</option>
                        </select>
                    </td> 
                    <td><input type="text" class="form-control price" name="rate[]" id="price-0" value="${v.price}" required></td>
                    <td>
                        <select class="form-control rowtax" name="itemtax[]" id="rowtax-0">
                            @foreach ($additionals as $tax)
                                <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                    {{ $tax->name }}
                                </option>
                            @endforeach                                                    
                        </select>
                    </td>
                    <td><input type="text" class="form-control taxable" value="0"></td>
                    <td class="text-center"><b><span class='amount' id="result-0">0</span></b></td> 
                    <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" class="stocktaxr" name="taxrate[]">
                    <input type="hidden" class="stockamountr" name="amount[]">
                    <input type="hidden" name="type[]" value="Stock">
                    <input type="hidden" name="id[]" value="0">
                </tr>
                <tr>
                    <td colspan="2">
                        <textarea id="stockdescr-0" class="form-control descr" name="description[]" placeholder="Product Description">${v.product_name}</textarea>
                    </td>
                    <td><input type="text" class="form-control product_code" name="product_code[]" id="product_code-0" value="${v.product_code}" readonly></td>
                    <td>
                        <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid-0">
                            <option value="">-- Warehouse --</option>
                            @foreach ($warehouses as $row)
                                <option value="{{ $row->id }}">{{ $row->title }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="warehouse_id[]" class="ware" id="ware-0" disabled>
                    </td>
                    <td colspan="5">
                        <input type="text" class="form-control projectstock" id="projectstocktext-0" placeholder="Search Project By Name" value="${v.project_name}">

                        <div class="mt-1">
                            <select id="stock_purchase_class-0" name="purchase_class_budget[]" class="custom-select round purchase-class" data-placeholder="Select a Non-Project Class">
                                <option value=""></option>

                                @foreach ($purchaseClasses as $pc)
                                    <option value="{{ $pc->id }}">
                                        {{ $pc->name }}
                                    </option>
                                @endforeach
                            </select>
                             <input type="hidden" class="purchase_class_budget" name="purchase_class_budget[]" id="purchase_class_budget-0" disabled>
                        </div>

                        <input type="hidden" name="import_request_id[]" id="">
                        <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" id="projectstockval-0" value="${v.project_id}">
                        <input type="hidden" class="prod_id" name="product_id[]" id="product_id-0" value="${v.product_id}">
                        <input type="hidden" class="supplier_product_id" name="supplier_product_id[]" value="0" id="supplier_product_id-0">
                    </td>
                    <td colspan="6"></td>
                </tr>
            `;
        },
        lastTotalRow(){
            return `
                 <tr class="bg-white">
                    <td>
                        <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                            <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                        </button>
                    </td>
                    <td colspan="7"></td>
                </tr>
                <tr class="bg-white">
                    <td colspan="7" align="right"><b>{{trans('general.total_tax')}}</b></td>                   
                    <td align="left" colspan="2"><span id="invtax" class="lightMode">0</span></td>
                </tr>
                <tr class="bg-white">
                    <td colspan="7" align="right"><b>Inventory Total</b></td>
                    <td align="left" colspan="2">
                        <input type="text" class="form-control" name="stock_grandttl" value="0.00" id="stock_grandttl" readonly>
                        <input type="hidden" name="stock_subttl" value="0.00" id="stock_subttl">
                        <input type="hidden" name="stock_tax" value="0.00" id="stock_tax">
                    </td>
                </tr>
            `;
        }
    }
    $(() => Forms.init());
</script>
@endsection