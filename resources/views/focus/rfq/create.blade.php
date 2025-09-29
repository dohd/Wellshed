@extends('core.layouts.app')

@section('title', 'RFQ | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Create RFQ</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.rfq.partials.rfq-header-buttons')
                </div>
            </div>
        </div> 
    </div>    

    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => 'biller.rfq.store', 'method' => 'POST']) }}
                    @include('focus.rfq.form')
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
            }
        }
    }
    
    function select2Config2(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            }
        }
    }


    // datepicker
    $('.datepicker')
        .datepicker({format: "{{ config('core.user_date_format')}}", autoHide:true})

    @isset($budget)

        $(document).ready(function () {

            const projectName= "{{ @$budget->quote->project->name}}";
            const projectId = "{{ intval(@$budget->quote->project->id) }}";
            $('#project').append(new Option(projectName, projectId, true, true)).change();
        })

    @endisset

    @isset($purchaseRequest)

        $(document).ready(function () {

            const projectName= "{{ @$purchaseRequest->project->name}}";
            const projectId = "{{ intval(@$purchaseRequest->project->id) }}";

            console.clear();
            console.table({projectId, projectName});

            if(projectName) $('#project').append(new Option(projectName, projectId, true, true)).change();
        })

    @endisset


    // On searching supplier
    $('#supplierbox').change(function() {
        const name = $('#supplierbox option:selected').text().split(' : ')[0];
        const [id, taxId] = $(this).val().split('-');
        $('#taxid').val(taxId);
        $('#supplierid').val(id);
        $('#supplier').val(name);
        
        let priceCustomer = '';
            $('#pricegroup_id option').each(function () {
                if (id == $(this).val())
                priceCustomer = $(this).val();
            });
            
            $('#pricegroup_id').val(priceCustomer);
    });
    // load suppliers
    const supplierUrl = "{{ route('biller.suppliers.select') }}";
    function supplierData(data) {
        return {results: data.map(v => ({id: v.id+'-'+v.taxid, text: v.name+' : '+v.email}))};
    }
    $('#supplierbox').select2(select2Config(supplierUrl, supplierData));

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
            return `
            <tr>
                <td><input type="text" class="form-control stockname" value="${v.queuerequisition_supplier.descr}" name="name[]" placeholder="Product Name" id='stockname-${i+1}'></td>
                <td><input type="text" class="form-control qty" name="qty[]" id="qty-${i+1}" value="${v.qty_balance}"></td>  
                <td><input type="text" name="uom[]" id="uom-${i+1}" value="${v.uom}" class="form-control uom" required></td> 
                <td><input type="text" value="${v.queuerequisition_supplier.rate}" class="form-control  price" name="rate[]" id="price-${i+1}"></td>
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
                <td class="text-center">{{config('currency.symbol')}} <b><span class='amount' id="result-${i+1}">0</span></b></td> 
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
                    <input type="text" id="stockdescr-0" value="${v.system_name}" class="form-control descr" name="description[]" placeholder="Additional Product Specifications">
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
                <td align="left" colspan="2">
                    {{config('currency.symbol')}} <span id="invtax" class="lightMode">0</span>
                </td>
            </tr>
            <tr class="bg-white">
                <td colspan="6" align="right">
                    <b>Inventory Total ({{ config('currency.symbol') }})</b>
                </td>
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
        return {results: data.map(v => ({id: v.id, text: v.name}))};
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

    // On project change
    $("#project").change(function() {
        const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
        $('#projectexptext-0').val(projectText);
        $('#projectexpval-0').val($(this).val());
        $('#projectstocktext-0').val(projectText);
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
                    $('#paidttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#paidttl').val());
                    break;
                case 1:
                    $(this).find('td:eq(1)').text($('#stock_tax').val());
                    $(this).find('td:eq(2)').text($('#exp_tax').val());
                    $(this).find('td:eq(3)').text($('#asset_tax').val());
                    total = sumLine($('#stock_tax').val(), $('#exp_tax').val(), $('#asset_tax').val());
                    $('#grandtax').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grandtax').val());
                    break;
                case 2:
                    $(this).find('td:eq(1)').text($('#stock_grandttl').val());
                    $(this).find('td:eq(2)').text($('#exp_grandttl').val());
                    $(this).find('td:eq(3)').text($('#asset_grandttl').val());
                    total = sumLine($('#stock_grandttl').val(), $('#exp_grandttl').val(), $('#asset_grandttl').val());
                    $('#grandttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grandttl').val());
                    break;
            }
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
    let stockRowId = 0;
    const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];
    const stockUrl = "{{ route('biller.products.purchase_search') }}"
    const projectstockUrl = "{{ route('biller.projects.project_search') }}"
    $('.stockname').autocomplete(predict(stockUrl, stockSelect));
    $('.projectstock').autocomplete(prediction(projectstockUrl,projectstockSelect));
    $('#rowtax-0').mousedown(function() { taxRule('rowtax-0', $('#tax').val()); });

    $('#stockTbl').on('click', '#addstock, .remove', function() {
        if ($(this).is('#addstock')) {
            stockRowId++;
            const i = stockRowId;
            const html = stockHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-' + i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            // Append the new row as the second last row
            $('#stockTbl tbody tr:last').before(html);
            $('.stockname').autocomplete(predict(stockUrl, stockSelect));
            $('.projectstock').autocomplete(prediction(projectstockUrl, projectstockSelect));
            $('#increment-' + i).val(i+1);
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            $('#projectstocktext-' + i).val(projectText);
            taxRule('rowtax-' + i, $('#tax').val());

            // Add the previous supplier data
            let priceCustomer = '';
            $('#pricegroup_id option').each(function() {
                if ($('#supplierid').val() == $(this).val())
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
    });

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
            const projectid = row.find('.stockitemprojectid').attr('id');
            row.find(projectid).val(0);
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
        transxnCalc();
    }

    // stock select autocomplete
    let stockNameRowId = 0;
    function stockSelect(event, ui) {
        const {data} = ui.item;
        const i = stockNameRowId;
        $('#stockitemid-'+i).val(data.id);
        $('#stockdescr-'+i).val(data.name);
        $('#product_code-'+i).val(data.product_code);

        const purchasePrice = accounting.unformat(data.purchase_price);
        $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();

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
    // stock select autocomplete
    let projectStockRowId = 0;
    function projectstockSelect(event, ui) {
        const {data} = ui.item;
        const i = projectStockRowId;
        $('#projectstockval-'+i).val(data.id);
        $('#warehouseid-'+i).attr('readonly', true);
        $(`#warehouseid-${i}`).val('');
        
    }
    $('#stockTbl').on('mouseup', '.stockname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.stockname')) stockNameRowId = id;
    }); 
    $('#stockTbl').on('mouseup', '.projectstock', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.projectstock')) projectStockRowId = id;
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
    $('#expTbl').on('click', '#addexp, .remove', function() {
        if ($(this).is('#addexp')) {
            expRowId++;
            const i = expRowId;
            const html = expHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#expTbl tbody tr:last').before(html);
            $('.accountname').autocomplete(predict(expUrl, expSelect));
            $('.projectexp').autocomplete(predict(projectUrl, projectExpSelect));
            $('#expenseinc-'+i).val(i+1);
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            $('#projectexptext-'+i).val(projectText);
            $('#projectexpval-'+i).val($("#project").val());
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
    const configs = {

    };
    const Index = {
        init(){
            $('#supplier_ids').select2({allowClear: true});
            $('#term_id').select2({allowClear: true});
            $('#purchase_requisition_ids').select2({allowClear: true});
            $('#btnSubmit').on('click', this.onSearchPRItems);
        },

        onSearchPRItems(){
            let purchase_requisition_ids = $('#purchase_requisition_ids').val();
            $('#stockTbl tbody').html('');
            $.ajax({
                url: "{{ route('biller.purchase_requisitions.items') }}", // Your Laravel route
                type: "POST",
                data: {
                    purchase_requisition_ids: purchase_requisition_ids,
                    _token: "{{ csrf_token() }}" // CSRF Token for security
                },
                success: function (response) {
                    // alert(response.message);
                    response.forEach((v,i) => {
                        $('#stockTbl tbody').append(Index.itemsRow(v,i));
                    });
                    $('#stockTbl tbody').append(Index.addRows());
                },
                error: function (xhr) {
                    alert("Something went wrong!");
                }
            });
        },

        itemsRow(v,i){
            return `
                <tr>
                    <td><input type="text" class="form-control increment" value="1" id="increment-0" disabled></td>
                    <td><input type="text" class="form-control stockname" name="name[]" value="${v.product_name}" placeholder="Product Name" id='stockname-0'></td>
                    <td><input type="text" class="form-control qty" name="qty[]" id="qty-0" value="${v.qty}"></td>
                    <td>
                        <select name="uom[]" id="uom-0" class="form-control uom">
                            <option value="${v.uom}">${v.uom}</option>
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" id="stockitemid-0" value="${v.product_id}" name="item_id[]">
                    <input type="hidden" name="type[]" value="Stock">
                    <input type="hidden" name="project_id[]" value="${v.project_id}">
                    <input type="hidden" name="project_milestone_id[]" value="${v.project_milestone_id}">
                    <input type="hidden" name="purchase_requisition_item_id[]" value="${v.purchase_requisition_item_id}">
                    <input type="hidden" name="id[]" value="0">
                </tr>
                <tr>
                    <td colspan=2>
                        <textarea id="stockdescr-0" class="form-control descr" name="description[]" placeholder="Additional Product Specifications">${v.product_name}</textarea>
                    </td>
                    <td colspan="6"></td>
                </tr>
            `;
        },

        addRows(){
            return `
            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="7"></td>
            </tr>
            `;
        }
    };
    $(() => Index.init());
</script>
@endsection
