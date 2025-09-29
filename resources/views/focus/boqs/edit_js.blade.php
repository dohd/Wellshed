{{ Html::script('focus/js/select2.min.js') }}
<script>
    // initialize html editor
    editor();

    // ajax config
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    $('#lead_id').select2();


    $(document).ready(function(){

        const opt = $('#lead_id option:selected');
        let priceCustomer = '';
        $('#price_customer option').each(function() {
            if (opt.attr('customer_id') == $(this).val())
                priceCustomer = $(this).val();
        });
        $('#price_customer').val(priceCustomer);

    });

    // initialize datepicker
    $('.datepicker').each(function() {
        const d = $(this).val();
        $(this).datepicker({
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            })
            .datepicker('setDate', new Date(d))
    });

    $('#submitBoqForm').click(function(e) {
        e.preventDefault();

        let selected = $('#vat_type').val();

        if (!selected) {
            alert('Select Type of Vat Required before submitting.');
            return; // 🚫 stop submission
        }

        let formData = {
            _method: 'PATCH',
            _token: '{{ csrf_token() }}',
            data: $('#boqForm').serializeArray()
        };

        $.ajax({
            url: "{{ route('biller.boqs.update', ['boq' => $boqs]) }}",
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                console.log('Form data successfully submitted', response);
                if (response.success) {
                    // Display success message
                    $('#message').html('<div class="alert alert-success">' + response.success + '</div>');
                    alert(response.success);
                } else {
                    // Display error message
                    $('#message').html('<div class="alert alert-danger">' + response.error + '</div>');
                    alert(response.error);
                }
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error occurred while submitting form', error);
            }
        });
    });
   

    // calculate profit
    const profitState = {
        sp_total: 0,
        bp_subtotal: 0,
        skill_total: 0,
        bp_total: 0
    };

    function calcProfit() {
        const {
            sp_total,
            bp_total,
            skill_total
        } = profitState;
        const profit = sp_total - (bp_total + skill_total);
        let pcent_profit = profit / (sp_total + skill_total) * 100;
        pcent_profit = isFinite(pcent_profit) ? Math.round(pcent_profit) : 0;

        const profitText = bp_total > 0 ?
            `${accounting.formatNumber(profit)} : ${pcent_profit}%` : accounting.formatNumber(profit);
        $('.profit').text(profitText);

        if (profit < 0) $('.profit').removeClass('text-dark').addClass('text-danger');
        else $('.profit').removeClass('text-danger').addClass('text-dark');

        // budget limit 30 percent
        if (sp_total < bp_total * 1.3) $('.budget-alert').removeClass('d-none');
        $('.budget-alert').addClass('d-none');

        // estimate cost
        $('.estimate-cost').text(accounting.formatNumber(bp_total + skill_total));
    }


    /**
     * Table logic
     */
    // default autocomplete
    $("#boqTbl tbody tr").each(function() {
        const id = $(this).find('.pname').attr('id');
        if (id > 0) {
            const i = id.split('-')[1];
            $('#name-' + i).autocomplete(autoComp(i));
        }
    });

    // add title
    const titleHtml = $("#titleRow").html();
    $("#titleRow").remove();
    let titleId = $("#boqTbl tbody tr").length;
    $('#addTitle').click(function() {
        $('#boqTbl tbody tr.invisible').remove();

        const i = 't' + titleId;
        const newTitleHtml = '<tr>' + titleHtml.replace(/t1/g, i) + '</tr>';
        $("#boqTbl tbody").append(newTitleHtml);
        titleId++;
        calcTotal();
        adjustTbodyHeight();
    });

    // add product

    const rowHtml = $("#productRow").html();
    $("#productRow").remove();
    let rowId = $("#boqTbl tbody tr").length;
    $('#addProduct').click(function() {
        $('#boqTbl tbody tr.invisible').remove();

        const i = 'p' + rowId;
        const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
        $("#boqTbl tbody").append(newRowHtml);
        $('#name-' + i).autocomplete(autoComp(i));
        rowId++;
        calcTotal();
        // trigger lead change to reset client pricelist 
        $('#lead_id').change();
        adjustTbodyHeight();
    });
    // adjust tbody height to accomodate dropdown menu
    function adjustTbodyHeight(rowCount) {
        rowCount = rowCount || $('#boqTbl tbody tr').length;
        if (rowCount < 4) {
            const rows = [];
            for (let i = 0; i < 5; i++) {
                const tr = `<tr class="invisible"><td colspan="100%"></td><tr>`
                rows.push(tr);
            }
            $('#boqTbl tbody').append(rows.join(''));
        }
    }

    // add miscellaneous product
    $('#addMisc').click(function() {
        $('#boqTbl tbody tr.invisible').remove();
        const i = 'p' + rowId;
        const newRowHtml =
            `<tr class="misc" style="background-color:rgba(229, 241, 101, 0.4);"> ${rowHtml.replace(/p0/g, i)} </tr>`;
        $("#boqTbl tbody").append(newRowHtml);
        $('#name-' + i).autocomplete(autoComp(i));
        $('#misc-' + i).val(1);
        $('#new_qty-' + i).val(1).addClass('invisible');
        $('#uom-' + i).val(1).addClass('invisible');
        // $('#rate-' + i).addClass('invisible');
        // $('#price-' + i).addClass('invisible');
        // $('#amount-'+i).addClass('invisible');
        $('#lineprofit-' + i).addClass('invisible');
        rowId++;
        calcTotal();
        adjustTbodyHeight();
    });

    // On clicking action drop down
    $("#boqTbl").on("click", ".up, .down, .delete, .add-title, .add-product, .add-misc", function() {
        const menu = $(this);
        const row = menu.parents("tr:first");
        rowId = $("#boqTbl tbody tr").length;
        if (menu.is('.up')) row.insertBefore(row.prev());
        if (menu.is('.down')) row.insertAfter(row.next());
        if (menu.is('.delete') && confirm('Are you sure?')) {
            menu.parents('tr:first').remove();
            $('#boqTbl tbody tr.invisible').remove();
            adjustTbodyHeight(1);
        }

        // drop down menus
        if (menu.is('.add-title')) {
            $('#addTitle').click();
            const titleRow = $("#boqTbl tbody tr:last");
            $("#boqTbl tbody tr:last").remove();
            row.before(titleRow);
        } else if (menu.is('.add-product')) {
            $('#addProduct').click();
            const productRow = $("#boqTbl tbody tr:last");
            $("#boqTbl tbody tr:last").remove();
            row.after(productRow);
            $("#boqTbl .pname").each(function() {
                let id = $(this).attr('id').split('-')[1];
                $(this).autocomplete(autoComp(id));
            });
        } else if (menu.is('.add-misc')) {
            $('#addMisc').click();
            const miscRow = $("#boqTbl tbody tr:last");
            $("#boqTbl tbody tr:last").remove();
            row.after(miscRow);
            $("#boqTbl .pname").each(function() {
                let id = $(this).attr('id').split('-')[1];
                $(this).autocomplete(autoComp(id));
            });
        }
        calcTotal();
    });

    $("#boqTbl").on("change", ".qty, .rate, .buyprice, .estqty, .tax_rate", function() {
        const id = $(this).attr('id').split('-')[1];
        const row = $(this).parents("tr:first");
        if (row.hasClass('misc')) {
            const taxrate = accounting.unformat($('#taxrate-' + id).val());
            let buyprice = accounting.unformat($('#rate-' + id).val());
            let estqty = accounting.unformat($('#qty-' + id).val() || '1');
            price = 0;
            if (taxrate === 0) {
                price = buyprice;
            } else {
                price = buyprice * (taxrate / 100 + 1);
            }

            $('#amount-' + id).text(accounting.formatNumber(estqty * price,2));
            $('#price-' + id).val(accounting.formatNumber(price,2));
            $('#total_amount-' + id).val(accounting.formatNumber(estqty * price,2));
            calcTotal();
        } else {
            const qty = accounting.unformat($('#qty-' + id).val());
            const new_qty = accounting.unformat($('#new_qty-' + id).val());
            const taxrate = accounting.unformat($('#taxrate-' + id).val());
            let buyprice = accounting.unformat($('#buyprice-' + id).val());
            let estqty = accounting.unformat($('#estqty-' + id).val() || '1');
            let rate = accounting.unformat($('#rate-' + id).val());

            // row item % profit
            let price = rate * (taxrate / 100 + 1);
            let profit = (qty * rate) - (estqty * buyprice);
            let pcent_profit = profit / (estqty * buyprice) * 100;
            pcent_profit = isFinite(pcent_profit) ? Math.round(pcent_profit) : 0;

            $('#buyprice-' + id).val(accounting.formatNumber(buyprice,2));
            $('#rate-' + id).val(accounting.formatNumber(rate,2));
            $('#price-' + id).val(accounting.formatNumber(price,2));
            $('#amount-' + id).text(accounting.formatNumber(qty * price,2));
            $('#total_amount-' + id).val(accounting.formatNumber(qty * price,2));
            $('#lineprofit-' + id).text(pcent_profit + '%');
            calcTotal();
        }
    });

    // on tax change
    $('#tax_id').change(function() {
        const mainTax = this.value;
        $('#boqTbl tbody tr').each(function() {
            const el = $(this).find('.tax_rate');
            if (el.length) el.val(mainTax).change();
        });
    });
    $('#boqTbl tbody tr').each(function() {
        const el = $(this).find('.tax_rate');
        if (el.length) el.change();
    });
    
    
    // on currency change
    let initRate = $('#currency option:selected').attr('currency_rate') * 1;
    $('#currency').change(function() {
        const currentRate = $(this).find(':selected').attr('currency_rate') * 1;
        if (currentRate > initRate) {
            $('#boqTbl tbody tr').each(function() {
                let purchasePrice = accounting.unformat($(this).find('.buyprice').val()) * initRate;
                let itemRate = accounting.unformat($(this).find('.rate').val()) * initRate;
                purchasePrice = purchasePrice / currentRate;
                itemRate = itemRate / currentRate;
                $(this).find('.buyprice').val(accounting.formatNumber(purchasePrice,2));
                $(this).find('.rate').val(accounting.formatNumber(itemRate,2)).change();
            });
        } else {
            $('#boqTbl tbody tr').each(function() {
                let purchasePrice = accounting.unformat($(this).find('.buyprice').val()) / currentRate;
                let itemRate = accounting.unformat($(this).find('.rate').val()) / currentRate;
                purchasePrice = purchasePrice * initRate;
                itemRate = itemRate * initRate;
                $(this).find('.buyprice').val(accounting.formatNumber(purchasePrice,2));
                $(this).find('.rate').val(accounting.formatNumber(itemRate,2)).change();
            });
        }
        initRate = currentRate;
    });

    // compute totals
    function calcTotal() {
        let taxable = 0;
        let total = 0;
        let subtotal = 0;
        let bp_subtotal = 0;
        $("#boqTbl tbody tr").each(function(i) {
            const isMisc = $(this).hasClass('misc');
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                if (!isMisc) {
                    const amount = accounting.unformat($(this).find('.amount').text());
                    const rate = accounting.unformat($(this).find('.rate').val());
                    const taxRate = accounting.unformat($(this).find('.tax_rate').val());
                    if (taxRate > 0) taxable += qty * rate;
                    let price = rate * (taxRate / 100 + 1);
                    total += qty * price * 1;
                    subtotal += qty * rate;
                }
                // profit variables

                if (isMisc) {
                    const buyprice = accounting.unformat($(this).find('.rate').val());
                    const estqty = $(this).find('.qty').val();
                    const taxrate = accounting.unformat($(this).find('.tax_rate').val());
                    v = 0;
                    if (taxrate === 0) {
                        v = buyprice;
                    } else {
                        v = buyprice * (taxrate / 100 + 1);
                    }
                    bp_subtotal += v * estqty;

                } else {
                    const buyprice = accounting.unformat($(this).find('.buyprice').val());
                    const estqty = $(this).find('.estqty').val();
                    bp_subtotal += estqty * buyprice;
                }



            }
            $(this).find('.index').val(i);
        });
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#vatable').val(accounting.formatNumber(taxable));
        $('#total').val(accounting.formatNumber(total));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber((total - subtotal)));
        profitState.bp_total = bp_subtotal;
        profitState.sp_total = subtotal;
        calcProfit();
    }

    calcBoQTotal();

    function calcBoQTotal() {
        let boq_taxable = 0;
        let boq_total = 0;
        let boq_subtotal = 0;
        $("#boqTbl tbody tr").each(function(i) {
            const qty = accounting.unformat($(this).find('.new_qty').val()) * 1;
            const rate = accounting.unformat($(this).find('.boq_rate').val());
            boq_subtotal += rate * qty;
            boq_total += rate * qty;
            
            $(this).find('.index').val(i);
        });
        $('#boq_taxable').val(accounting.formatNumber(boq_taxable));
        $('#boq_vatable').val(accounting.formatNumber(boq_taxable));
        $('#boq_total').val(accounting.formatNumber(boq_total));
        $('#boq_subtotal').val(accounting.formatNumber(boq_subtotal));
        $('#boq_tax').val(accounting.formatNumber((boq_total - boq_subtotal)));
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
                    url,
                    data,
                    method: 'POST',
                    success: result => response(result.map(v => ({
                        label: v.name,
                        value: v.name,
                        data: v
                    }))),
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {
                    data
                } = ui.item;

                const row = $(this).parents("tr:first");

                if (row.hasClass('misc')) {
                    $('#productid-' + i).val(data.product_id);
                    $('#name-' + i).val(data.name);
                    $('#unit-' + i).val(data.unit);
                    $('#qty-' + i).val(1);
                    $('#estqty-' + i).val(1);
                    $('#taxrate-' + i).val(0);
                    $('#product_type-' + i).val(data.product_type);
                    $('#client_product_id-' + i).val(data.client_product_id);

                    const currencyRate = $('#currency option:selected').attr('currency_rate');
                    if (currencyRate > 1) {
                        data.purchase_price = parseFloat(data.purchase_price) / currencyRate;
                        data.price = parseFloat(data.price) / currencyRate;
                    }

                    $('#buyprice-' + i).val(accounting.formatNumber(data.purchase_price));
                    // $('#estqty-' + i).val(1);

                    // const rate = parseFloat(data.price);
                    // let price = rate * ($('#tax_id').val() / 100 + 1);
                    // $('#price-' + i).val(accounting.formatNumber(price));
                    $('#price-' + i).val(accounting.formatNumber(data.purchase_price));
                    $('#amount-' + i).text(accounting.formatNumber(data.purchase_price));
                    $('#total_amount-' + i).val(accounting.formatNumber(data.purchase_price));
                    // $('#rate-' + i).val(accounting.formatNumber(rate)).change();
                    $('#rate-' + i).val(accounting.formatNumber(data.purchase_price)).change();

                    


                    if (data.units) {
                        let units = data.units.filter(v => v.unit_type == 'base');
                        if (units.length) {
                            $('#unit-' + i).val(units[0].code);
                            $('#unit_id-' + i).val(units[0].id);
                        }
                    }
                } else {
                    $('#productid-' + i).val(data.product_id);
                    $('#name-' + i).val(data.name);
                    $('#unit-' + i).val(data.unit);
                    $('#qty-' + i).val(1);
                    $('#product_type-' + i).val(data.product_type);
                    $('#client_product_id-' + i).val(data.client_product_id);

                    const currencyRate = $('#currency option:selected').attr('currency_rate');
                    if (currencyRate > 1) {
                        data.purchase_price = parseFloat(data.purchase_price) / currencyRate;
                        data.price = parseFloat(data.price) / currencyRate;
                    }

                    $('#buyprice-' + i).val(accounting.formatNumber(data.purchase_price));
                    $('#estqty-' + i).val(1);

                    const rate = parseFloat(data.purchase_price);
                    let price = rate * ($('#tax_id').val() / 100 + 1);
                    $('#price-' + i).val(accounting.formatNumber(price));
                    $('#amount-' + i).text(accounting.formatNumber(price));
                    $('#total_amount-' + i).val(accounting.formatNumber(price));
                    $('#rate-' + i).val(accounting.formatNumber(rate)).change();

                    if (data.units) {
                        let units = data.units.filter(v => v.unit_type == 'base');
                        if (units.length){ 
                            $('#unit-' + i).val(units[0].code);
                            $('#unit_id-' + i).val(units[0].id);
                        }
                    }

                    
                }

                if (data.uom) {
                    $('#unit-' + i).append(`<option value="${data.uom}">${data.uom}</option>`);
                }
                $('#rate-' + i).change();
            }
        };
    }
    // attach autocomplete to preloaded items
    $("#boqTbl .pname").each(function() {
        let id = $(this).attr('id').split('-')[1];
        $(this).autocomplete(autoComp(id));
    });

    // assign row index

    $('#boq_sheet_id').change(function(){
        let boq = "{{$boqs->id}}";
        let boq_sheet_id = $(this).val();
        let boq_id = boq;
        $('#boqTbl tbody').html('');
        $.ajax({
            url: "{{ route('biller.boqs.get_boq_items')}}",
            method: "POST",
            data: {
                boq_id: boq_id,
                boq_sheet_id: boq_sheet_id,
            },
            success: function(data){
                data.forEach((v,i) => {
                    if(v.type == 'product'){

                        $('#boqTbl tbody').append(boqProduct(v,i));

                    }else if(v.type == 'title'){
                        $('#boqTbl tbody').append(boqTitle(v,i));
                    }
                });
                calcBoQTotal();
                // $('.tax_rate').change();
            }
        });
    });

    function boqProduct(v,i){
        return `
            <tr class="">
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-p${i}" value="${v.numbering ? v.numbering : ''}"></td>
                <td>
                    <textarea name="description[]" id="description-p${i}" cols="35" rows="10" class="form-control" placeholder="Description" readonly>${v.description ? v.description : ''}</textarea>
                </td>                        
                <td>
                    <textarea name="product_name[]" id="name-p${i}" cols="35" rows="2" class="form-control pname" placeholder="{{trans('general.enter_product')}}" style="font-weight: bold; ${ v.is_imported == 0 ? 'color: blue;' : '' }" ${ v.is_imported == 1 ? 'readonly' : '' }>${v.product ? v.product.name : ''}</textarea>
                </td>
                <td><input type="text" name="uom[]" value="${v.uom ? v.uom : ''}" id="uom-p${i}" class="form-control ${v.misc == 0 ? '' : 'invisible'}" readonly></td>
                <td><input type="text" name="unit[]" id="unit-p${i}" value="${v.unit ? v.unit : ''}" class="form-control" readonly></td>
                <td><input type="text" class="form-control new_qty ${v.misc == 0 ? '' : 'invisible'}" name="new_qty[]" value="${accounting.formatNumber(v.new_qty)}" id="new_qty-p${i}" step="0.1" required readonly></td>
                <td><input type="text" class="form-control qty" value="${accounting.formatNumber(v.qty)}" name="qty[]" id="qty-p${i}" step="0.1" readonly></td>
                <td><input type="text" class="form-control boq_rate" name="boq_rate[]" id="boq_rate-p0" value="${accounting.formatNumber(v.boq_rate)}" readonly></td>
                <td><input type="text" class="form-control rate" value="${accounting.formatNumber(v.rate)}" name="rate[]" id="rate-p${i}" readonly></td>
                <td>
                    <div class="row no-gutters">
                        <div class="col-6">
                            <input type="text" class="form-control price" name="product_subtotal[]" value="${accounting.formatNumber(v.product_subtotal)}" id="price-p${i}" readonly>
                        </div>
                        <div class="col-6">
                            <select class="custom-select tax_rate ${ v.is_imported == 1 ? 'd-none' : '' }" name="tax_rate[]" id="taxrate-p${i}">
                                @foreach ($additionals as $add_item)
                                    <option value="{{ $add_item->value }}" ${accounting.unformat(v.tax_rate) == {{ +$add_item->value }} ? 'selected' : ''}>
                                        {{ $add_item->value == 0? 'OFF' : (+$add_item->value) . '%' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </td>
                <td class='text-center'>
                    <span class="amount" id="amount-p${i}">${v.is_imported === 0 ? accounting.formatNumber(v.amount) : accounting.formatNumber(v.boq_amount) }</span>&nbsp;&nbsp;
                    {{-- <span class="lineprofit text-info" id="lineprofit-p${i}">0%</span> --}}
                </td>
                <td class="text-center">
                    @include('focus.boqs.partials.action-dropdown')
                </td>
                <input type="hidden" name="misc[]" value="${ v.misc }" id="misc-p${i}">
                <input type="hidden" name="product_id[]" value="${v.product_id}" id="productid-p${i}">
                <input type="hidden" name="unit_id[]" value="${v.unit_id}" id="unit_id-p${i}">
                <input type="hidden" class="index" name="row_index[]" value="${ v.row_index }" id="rowindex-p${i}">
                <input type="hidden" class="total_amount" name="amount[]" value="${v.amount}" id="total_amount-p${i}">
                <input type="hidden" class="" name="boq_amount[]" value="${v.boq_amount}" id="boq_amount-p${i}">
                <input type="hidden" name="type[]" value="${v.type}" id="type-p${i}">
                <input type="hidden" name="id[]" value="${v.id}">
            </tr>
        `;
    }

    function boqTitle(v,i){
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-t${i}" value="${v.numbering ? v.numbering : ''}" style="font-weight: bold; ${ v.is_imported == 0 ? 'color: green;' : '' }"></td>
                <td colspan="9">
                    <input type="text" value="${v.description}" class="form-control" name="description[]" placeholder="Enter Title Or Heading" id="description-t${i}" style="font-weight: bold; ${ v.is_imported == 0 ? 'color: green;' : '' }" ${ v.is_imported == 1 ? 'readonly' : '' } required>
                </td>
                <td>
                    ${accounting.formatNumber(v.boq_amount)}
                </td>
                <td class="text-center">
                    @include('focus.boqs.partials.action-dropdown')
                </td>
                <input type="hidden" name="misc[]" value="${ v.misc }" id="misc-t${i}">
                <input type="hidden" name="product_id[]" value="0" id="productid-t${i}">
                <input type="hidden" name="unit_id[]" value="0" id="unit_id-t${i}">
                <input type="hidden" name="uom[]">
                <input type="hidden" name="unit[]">
                <input type="hidden" name="product_name[]">
                <input type="hidden" name="qty[]" value="0">
                <input type="hidden" name="boq_rate[]" value="0">
                <input type="hidden" name="boq_amount[]" value="${v.boq_amount}">
                <input type="hidden" name="rate[]" value="0">
                <input type="hidden" name="product_subtotal[]" value="0">
                <input type="hidden" name="tax_rate[]" value="0">
                <input type="hidden" name="new_qty[]" value="0">
                <input type="hidden" name="amount[]" value="${v.amount}" id="total_amount-t${i}">
                <input type="hidden" class="index" name="row_index[]" value="${ v.row_index }" id="rowindex-t${i}">
                <input type="hidden" name="type[]" value="${v.type}" id="type-t${i}">
                <input type="hidden" name="id[]" value="${v.id}">
            </tr>
        `;
    }
    
</script>
