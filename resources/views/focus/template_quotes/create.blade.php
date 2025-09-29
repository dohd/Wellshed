@extends('core.layouts.app')

@php
    $header_title = "Template Quote Management";
    $is_pi = request('page') == 'pi';
    $task = request('task');
    if ($is_pi) $header_title = 'Proforma Invoice Management';
@endphp

@section('title', $header_title)

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        {{-- <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>E.P Margin Not Met!</strong> Check line item rates.
        </div> --}}
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ $header_title }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                @include('focus.template_quotes.partials.quotes-header-buttons')
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
        {{ Form::open(['route' => 'biller.template-quotes.store', 'method' => 'POST', 'id' => 'quoteForm']) }}
            @include('focus.template_quotes.form')
        {{ Form::close() }}
        </div>
    </div> 
</div>
@endsection

@section('extra-scripts')
    @include('focus.template_quotes.create_js')
@endsection
{{-- 
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>    
    // initialize html editor
    editor();

    // ajax config
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    $('#lead_id').select2({
        allowClear: true,
        placeholder: 'Search by No, Client, Branch, Title'
    }).val('').change();

    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#referencedate').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());

    // print type
    $('input[type=radio]').change(function() {
        if ($(this).val() == 'inclusive') $('#vatText').text('(VAT-Inclusive)');
        else $('#vatText').text('(VAT-Exclusive)');
    });

    // On change lead and djc
    const subject = {title: '', djc: ''};
    $('form').on('change', '#lead_id, #reference', function() {
        if ($(this).is('#lead_id')) {
            const opt = $('#lead_id option:selected');
            $('#attention').val(opt.attr('assign_to'));
            $('#subject').val(opt.attr('title'));
            $('#client_ref').val(opt.attr('client_ref'));
            $('#branch_id').val(opt.attr('branch_id'));
            $('#customer_id').val(opt.attr('customer_id'));
            subject.title = opt.attr('title');

            // update price customer based on selected lead
            let priceCustomer = '';
            $('#price_customer option').each(function () {
                if (opt.attr('customer_id') == $(this).val())
                priceCustomer = $(this).val();
            });
            $('#price_customer').val(priceCustomer);
            
        } else subject.djc = $(this).val();
        // subject
        if (subject.title && subject.djc) $('#subject').val(subject.title + ' ; Djc-' + subject.djc);
        else if (subject.title) $('#subject').val(subject.title);
    });

    // calculate profit
    const profitState = {sp_total: 0, bp_subtotal: 0, skill_total: 0, bp_total: 0};
    function calcProfit() {
        const {sp_total, bp_total, skill_total} = profitState;
        const profit = sp_total - (bp_total + skill_total);
        let pcent_profit = profit/(bp_total + skill_total) * 100;
        pcent_profit = isFinite(pcent_profit) ? Math.round(pcent_profit) : 0;

        const profitText = bp_total > 0 ? 
            `${accounting.formatNumber(profit)} : ${pcent_profit}%` : accounting.formatNumber(profit);
        $('.profit').text(profitText);

        if (profit < 0) $('.profit').removeClass('text-dark').addClass('text-danger');
        else $('.profit').removeClass('text-danger').addClass('text-dark');

        // budget limit 30 percent
        if (sp_total < bp_total * 1.3) $('.budget-alert').removeClass('d-none');
        else $('.budget-alert').addClass('d-none');

        // estimate cost
        $('.estimate-cost').text(accounting.formatNumber(bp_total + skill_total));
    }

    /**
     * Table logic
     */
    // add title
    let titleId = 2;
    const titleHtml = $("#titleRow").html();
    $("#titleRow").remove();
    $('#addTitle').click(function() {
        $('#quoteTbl tbody tr.invisible').remove();

        const i = 't'+titleId;
        const newTitleHtml = '<tr>' + titleHtml.replace(/t1/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newTitleHtml);
        titleId++;
        calcTotal();
        adjustTbodyHeight();
    });

    // add product
    let rowId = 1;
    const rowHtml = $("#productRow").html();
    $('#name-p0').autocomplete(autoComp('p0'));
    $('#addProduct').click(function() {
        $('#quoteTbl tbody tr.invisible').remove();

        const i = 'p' + rowId;
        const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-'+i).autocomplete(autoComp(i));
        rowId++;
        calcTotal();
        // trigger lead change to reset client pricelist 
        $('#lead_id').change();     

        adjustTbodyHeight();
    });
    // adjust tbody height to accomodate dropdown menu
    function adjustTbodyHeight(rowCount) {
        rowCount = rowCount || $('#quoteTbl tbody tr').length;
        if (rowCount < 4) {
            const rows = [];
            for (let i = 0; i < 5; i++) {
                const tr = `<tr class="invisible"><td colspan="100%"></td><tr>`
                rows.push(tr);
            }
            $('#quoteTbl tbody').append(rows.join(''));
        }
    }

    // add miscellaneous product
    $('#addMisc').click(function() {
        $('#quoteTbl tbody tr.invisible').remove();

        const i = 'p' + rowId;
        const newRowHtml = `<tr class="misc" style="background-color:rgba(229, 241, 101, 0.4); opacity:0.5;"> ${rowHtml.replace(/p0/g, i)}</tr>`;
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-'+i).autocomplete(autoComp(i));
        $('#misc-'+i).val(1);
        $('#qty-'+i).val(1);
        ['qty', 'rate', 'price', 'amount', 'lineprofit'].forEach(v => {
            $(`#${v}-${i}`).addClass('invisible');
        });
        // $('.miscexpense').removeClass('d-none');
        rowId++;
        calcTotal();
        adjustTbodyHeight();
    });

    // On clicking action drop down
    $("#quoteTbl").on("click", ".up, .down, .delete, .add-title, .add-product, .add-misc", function() {
        const menu = $(this);
        const row = menu.parents("tr:first");
        if (menu.is('.up')) row.insertBefore(row.prev());
        if (menu.is('.down')) row.insertAfter(row.next());
        if (menu.is('.delete') && confirm('Are you sure?')) {
            menu.parents('tr:first').remove();
            $('#quoteTbl tbody tr.invisible').remove();
            adjustTbodyHeight(1);
        }

        // dropdown menus
        if (menu.is('.add-title')) {
            $('#addTitle').click();
            const titleRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.before(titleRow);
        } else if (menu.is('.add-product')) {
            $('#addProduct').click();
            const productRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.after(productRow);
        } else if (menu.is('.add-misc')) {
            $('#addMisc').click();
            const miscRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.after(miscRow);
        }

        calcTotal();
    });

    // on change qty and rate
    $("#quoteTbl").on("change", ".qty, .rate, .buyprice, .estqty, .unit", function() {
        const id = $(this).attr('id').split('-')[1];

        const qty = accounting.unformat($('#qty-'+id).val());
        let buyprice = accounting.unformat($('#buyprice-'+id).val());
        let estqty = accounting.unformat($('#estqty-'+id).val() || '1');
        let rate = accounting.unformat($('#rate-'+id).val());
        
        // uom rate conversion
        if ($(this).is('.unit')) {
            rate = accounting.unformat($('#unit-'+id+' option:selected').attr('product_rate'));
            buyprice = accounting.unformat($('#unit-'+id+' option:selected').attr('purchase_price'));
        }

        // row item % profit
        let price = rate * ($('#tax_id').val()/100 + 1);
        let profit = (qty * rate) - (estqty * buyprice);
        let pcent_profit = profit / (estqty * buyprice) * 100;
        pcent_profit = isFinite(pcent_profit)? Math.round(pcent_profit) : 0;

        $('#buyprice-'+id).val(accounting.formatNumber(buyprice, 4));
        $('#rate-'+id).val(accounting.formatNumber(rate, 4));
        $('#price-'+id).val(accounting.formatNumber(price, 4));
        $('#amount-'+id).text(accounting.formatNumber(qty * price, 4));
        $('#lineprofit-'+id).text(pcent_profit + '%');
        calcTotal();
    });

    // on tax change
    $('#tax_id').change(function() {
        const tax = $(this).val()/100 + 1;
        $('#quoteTbl tbody tr').each(function() {
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                const rate = accounting.unformat($(this).find('.rate').val());
                let price = rate * tax;
                $(this).find('.price').val(accounting.formatNumber(price, 4));
                $(this).find('.rate').change();
            }
        });
    });    


    // on currency change
    let initRate = $('#currency option:selected').attr('currency_rate')*1;
    $('#currency').change(function() {
        const currentRate = $(this).find(':selected').attr('currency_rate')*1;
        if (currentRate > initRate) {
            $('#quoteTbl tbody tr').each(function() {
                let purchasePrice = accounting.unformat($(this).find('.buyprice').val())  * initRate;
                let itemRate = accounting.unformat($(this).find('.rate').val()) * initRate;
                purchasePrice = purchasePrice / currentRate;
                itemRate = itemRate / currentRate;
                $(this).find('.buyprice').val(accounting.formatNumber(purchasePrice, 4));
                $(this).find('.rate').val(accounting.formatNumber(itemRate, 4)).change();
            });
        } else {
            $('#quoteTbl tbody tr').each(function() {
                let purchasePrice = accounting.unformat($(this).find('.buyprice').val())  / currentRate;
                let itemRate = accounting.unformat($(this).find('.rate').val()) / currentRate;
                purchasePrice = purchasePrice * initRate;
                itemRate = itemRate * initRate;
                $(this).find('.buyprice').val(accounting.formatNumber(purchasePrice, 4));
                $(this).find('.rate').val(accounting.formatNumber(itemRate, 4)).change();
            });
        }
        initRate = currentRate;
    }); 
    
    // compute totals
    function calcTotal() {
        let total = 0;
        let subtotal = 0;
        let bp_subtotal = 0;
        $("#quoteTbl tbody tr").each(function(i) {
            const isMisc = $(this).hasClass('misc');
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                if (!isMisc) {
                    const amount = accounting.unformat($(this).find('.amount').text());
                    const rate = accounting.unformat($(this).find('.rate').val());
                    total += amount * 1;
                    subtotal += qty * rate;
                }
                // profit variables
                const buyprice = accounting.unformat($(this).find('.buyprice').val());
                const estqty = $(this).find('.estqty').val();
                bp_subtotal += estqty * buyprice;
            }
            $(this).find('.index').val(i);
        });
        $('#total').val(accounting.formatNumber(total));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber((total - subtotal)));
        profitState.bp_total = bp_subtotal;
        profitState.sp_total = subtotal;
        calcProfit();        
    }
    
    $('#attach-djc').prop("checked", true);
    $('#attach-djc').change(function (){
            if ($(this).is(":checked")) {
                $('#reference').attr('disabled', false);
                $('#referencedate').attr('disabled', false);
            }else{
                $('#reference').attr('disabled', true);
                $('#referencedate').attr('disabled', true);
                
            }
        });
    $('#add-check').prop("checked", true);
    $('#addqproduct').removeClass('d-none');
     $('#add-check').change(function (){
            if ($(this).is(":checked")) {
                $('#addqproduct').removeClass('d-none');
                $('#equipmentsTbl tbody').find('input').attr('disabled', false);
                $('#equipmentsTbl tbody').find('select').attr('disabled', false);
            }else{
                $('#addqproduct').addClass('d-none');
                $('#equipmentsTbl tbody').find('input').attr('disabled', true);
                $('#equipmentsTbl tbody').find('select').attr('disabled', true);
                
            }
        });

    

      // product row
      function productRow(n) {            
        return `
            <tr>
                <td><input type="text" class="form-control unique-id" name="unique_id[]" placeholder="Search Equipment" id="uniqueid-${n}" required></td>
                <td><input type="text" class="form-control eq-tid-row" name="equipment_tid[]" id="eq-tid-${n}" required></td>
                <td><input type="text" class="form-control equip-serial" name="equip_serial[]" id="equipserial-${n}"></td>
                <td><input type="text" class="form-control make-type" name="make_type[]" id="maketype-${n}" required></td>
                <td><input type="text" class="form-control capacity" name="capacity[]" id="capacity-${n}" required></td>
                <td><input type="text" class="form-control location" name="location[]" id="location-${n}" required></td>
                <td>
                    <select class="custom-select fault" name="fault[]" id="fault-${n}">
                        @foreach($faults as $fault)
                        <option value="{{$fault->name}}" selected>{{$fault->name}}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item delete" href="javascript:" data-rowid="${n}" >Remove</a>
                            <a class="dropdown-item up" href="javascript:">Up</a>
                            <a class="dropdown-item down" href="javascript:">Down</a>
                        </div>
                    </div>
                </td>
                <input type="hidden" name="row_index_id[]" value="0" class="row-index" id="rowindex-${n}">
                <input type="hidden" name="item_id[]" value="0" class="item-id" id="itemid-${n}">
            </tr>
        `;
    }

    // equipment row counter;
    let rowIds = 0;
    $('#equipmentsTbl tbody').append(productRow(0));
     $('#uniqueid-0').autocomplete(autocompleteProp(0));
    
    // on clicking addproduct
    $('#addqproduct').on('click', function() {
        rowIds++;
        const i = rowIds;
        $('#equipmentsTbl tbody').append(productRow(i));
         $('#uniqueid-' + i).autocomplete(autocompleteProp(i));
         assignIndex();
    });

    // on clicking equipment drop down options
    $("#equipmentsTbl").on("click", ".up, .down, .delete", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.delete')) $(this).closest('tr').remove();
        assignIndex();
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#customer_id").val(),
                    dataType: "json",
                    method: 'post',
                    data: {
                        keyword: request.term, 
                        customer_id: $('#lead_id option:selected').attr('customer_id'),
                        branch_id: $('#lead_id option:selected').attr('branch_id')
                    },
                    success: data => {
                        data = data.map(v => {
                            for (const key in v) {
                                if (!v[key]) v[key] = '';
                            }
                            const label = `${v.unique_id} ${v.tid} ${v.id} ${v.equip_serial} ${v.make_type} ${v.model} ${v.machine_gas}
                                ${v.capacity} ${v.location} ${v.building} ${v.floor}`;
                            const value = v.unique_id;
                            const data = v;
                            return {label, value, data};
                        })
                        response(data);
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                // console.log(data)
                $('#uniqueid-'+i).val(data.unique_id);
                $('#eq-tid-'+i).val(data.tid);
                $('#itemid-'+i).val(data.id);
                $('#equipserial-'+i).val(data.equip_serial);
                $('#maketype-'+i).val(data.make_type);
                $('#capacity-'+i).val(data.capacity);
                $('#location-'+i).val(data.location);
            }
        };
    }
    // assign row index
    function assignIndex() {
        $('#equipmentsTbl tr').each(function(i) {
            if (i > 0) $(this).find('.row-index').val(i);
        });
    }


    /**
     * Skillset modal logic
     */
    // remove skill row
    $('#skillTbl').on('click', '.rem', function() {
        $(this).parents('tr').remove();
        skillTotal();
    });
    $('#skillTbl').on('change', '.type, .chrg, .hrs, .tech', function() {
        const row = $(this).parents('tr');
        let hrs = row.find('.hrs').val();
        let tech = row.find('.tech').val();
        let chrg = row.find('.chrg');

        // labour type charges
        switch (row.find('.type').val()) {
            case 'casual': chrg.val(250).attr('readonly', true); break;
            case 'contract': chrg.val(250).attr('readonly', true); break;
            case 'attachee': chrg.val(150).attr('readonly', true); break;
            case 'outsourced': chrg.val(chrg.val()).attr('readonly', false); break;
        }
        skillTotal();
    });

    // add skill row
    let skillId = 0;
    const skillHtml = $('#skillTbl tbody tr:first').html();
    $('#skillTbl tbody tr:first').remove();
    $('#addRow').click(function() {
        skillId++;
        const html = skillHtml.replace(/-0/g, '-'+skillId).replace('d-none', '');
        $('#skillTbl tbody').append('<tr>'+html+'</tr>');
    });

    function skillTotal() {
        total = 0;
        $('#skillTbl tbody tr').each(function() {
            const hrs = $(this).find('.hrs').val();
            const tech = $(this).find('.tech').val();
            const chrg = $(this).find('.chrg').val();
            const amount = hrs * chrg * tech;
            total += amount;
            $(this).find('.amount').text(amount);
        });
        $('#skill_total').val(accounting.formatNumber(total));
        profitState.skill_total = total;
        calcProfit();
    }

    // autocomplete function
    function autoComp(i) {
        return {
            source: function(request, response) {
                // stock product
                let term = request.term;
                let url = "{{ route('biller.products.quote_product_search') }}";
                let data = {
                    keyword: term, 
                    price_customer_id: $('#price_customer').val(),
                };
                // maintenance service product 
                const docType = @json(request('doc_type'));
                if (docType == 'maintenance') {
                    const schedule_url = "{{ route('biller.taskschedules.quote_product_search') }}";
                    data.customer_id = $('#lead_id option:selected').attr('customer_id');
                    if ($('#price_customer option:selected').text() == 'Maintenace Schedule') {
                        url = schedule_url;
                    }
                } 
                $.ajax({
                    url, data,
                    method: 'POST',
                    success: result => response(result.map(v => ({label: v.name, value: v.name, data: v}))),
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                
                $('#productid-'+i).val(data.id);
                $('#name-'+i).val(data.name);
                $('#unit-'+i).val(data.unit);                
                $('#qty-'+i).val(1); 
                
                // currency conversion
                const currencyRate = $('#currency option:selected').attr('currency_rate');
                if (currencyRate > 1) {
                    data.purchase_price = parseFloat(data.purchase_price) / currencyRate;
                    data.price = parseFloat(data.price) / currencyRate;
                }
                $('#buyprice-'+i).val(accounting.formatNumber(data.purchase_price)); 
                $('#estqty-'+i).val(1);
                const rate = parseFloat(data.price);
                let price = rate * ($('#tax_id').val()/100 + 1);
                $('#price-'+i).val(accounting.formatNumber(price));                
                $('#amount-'+i).text(accounting.formatNumber(price));
                $('#rate-'+i).val(accounting.formatNumber(rate));
                
                // product units 
                if (data.units) {
                    $('#unit-'+i).html('');
                    data.units.forEach(v => {
                        let product_rate = rate * parseFloat(v.base_ratio);
                        let purchase_price = parseFloat(data.purchase_price) * parseFloat(v.base_ratio);
                        $('#unit-'+i).append(`<option value="${v.code}" purchase_price="${purchase_price}" product_rate="${product_rate}">${v.code}</option>`);
                    });
                }
                if(data.uom) {
                    $('#unit-'+i).append(`<option value="${data.uom}">${data.uom}</option>`);
                }
                $('#rate-'+i).change();
            }
        };
    }    
</script>
@endsection --}}