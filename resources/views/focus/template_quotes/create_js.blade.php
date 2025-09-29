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

    $('#lead_id').select2({
        allowClear: true
    });

    // initialize datepicker
    $('.datepicker').datepicker({
        format: "{{ config('core.user_date_format') }}",
        autoHide: true
    })
    $('#referencedate').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());

    // print type
    $('input[type=radio]').change(function() {
        if ($(this).val() == 'inclusive') $('#vatText').text('(Print VAT-Inc)');
        else $('#vatText').text('(Print VAT-Exc)');
    });

    // On change lead and djc
    const subject = {
        title: '',
        djc: ''
    };
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
            $('#price_customer option').each(function() {
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
        let pcent_profit = profit / (bp_total + skill_total) * 100;
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

    // update row tax options
    function updateLineTax(taxSelect) {
        if (taxSelect && taxSelect.length) {
            const mainTax = $('#tax_id').val();
            taxSelect.children().each(function() {
                const value = $(this).attr('value');
                if ((value == mainTax) || (value == 0)) $(this).removeClass('d-none');
                else $(this).addClass('d-none');
            });
            taxSelect.val(mainTax);
        }
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

        const i = 't' + titleId;
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
        console.log(rowHtml);
        $('#name-' + i).autocomplete(autoComp(i));
        // let row = $("#quoteTbl tbody tr:last");
        // updateLineTax(row.find('.tax_rate'));

        // trigger lead change to reset client pricelist 
        $('#lead_id').change();
        adjustTbodyHeight();
        calcTotal();
        rowId++;
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
        const newRowHtml =
            `<tr class="misc" style="background-color:rgba(229, 241, 101, 0.4);"> ${rowHtml.replace(/p0/g, i)} </tr>`;
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-' + i).autocomplete(autoComp(i));
        $('#misc-' + i).val(1);
        $('#qty-' + i).val(1);
        ['qty', 'price', 'rate', 'lineprofit'].forEach(v => {
            $(`#${v}-${i}`).addClass('invisible');
        });
        rowId++;
        calcTotal();
        adjustTbodyHeight();
    });

    // On clicking action drop down
    $("#quoteTbl").on("click", ".up, .down, .delete, .add-title, .add-product, .add-misc", function() {
        const menu = $(this);
        const row = $(this).parents("tr:first");

        if (menu.is('.up')) row.insertBefore(row.prev());
        if (menu.is('.down')) row.insertAfter(row.next());
        if (menu.is('.delete') && confirm('Are you sure?')) {
            row.remove();
            $('#quoteTbl tbody tr.invisible').remove();
            adjustTbodyHeight(1);
        }

        // dropdown menus
        if (menu.is('.add-title')) {
            $('#addTitle').click();
            const titleRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.before(titleRow);
        }
        if (menu.is('.add-product')) {
            $('#addProduct').click();
            const productRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.after(productRow);
            // update tax options
            updateLineTax($("#quoteTbl tbody tr:last").find('.tax_rate'));
        }
        if (menu.is('.add-misc')) {
            $('#addMisc').click();
            const miscRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.after(miscRow);

        }

        calcTotal();
    });

    // on change qty and rate
    $("#quoteTbl").on("change", ".qty, .rate, .buyprice, .estqty, .tax_rate", function() {
        const id = $(this).attr('id').split('-')[1];
        const row = $(this).parents("tr:first");
        if (row.hasClass('misc')) {
            const taxrate = accounting.unformat($('#taxrate-' + id).val());
            let buyprice = accounting.unformat($('#buyprice-' + id).val());
            let estqty = accounting.unformat($('#estqty-' + id).val() || '1');
            price = 0;
            if (taxrate === 0) {
                price = buyprice;
            } else {
                price = buyprice * (taxrate / 100 + 1);
            }

            $('#amount-' + id).text(accounting.formatNumber(estqty * price, 4));
            calcTotal();
        } else {
            const qty = accounting.unformat($('#qty-' + id).val());
            const taxrate = accounting.unformat($('#taxrate-' + id).val());
            let buyprice = accounting.unformat($('#buyprice-' + id).val());
            let estqty = accounting.unformat($('#estqty-' + id).val() || '1');
            let rate = accounting.unformat($('#rate-' + id).val());

            // row item % profit
            let price = rate * (taxrate / 100 + 1);
            let profit = (qty * rate) - (estqty * buyprice);
            let pcent_profit = profit / (estqty * buyprice) * 100;
            pcent_profit = isFinite(pcent_profit) ? Math.round(pcent_profit) : 0;

            $('#buyprice-' + id).val(accounting.formatNumber(buyprice, 4));
            $('#rate-' + id).val(accounting.formatNumber(rate, 4));
            $('#price-' + id).val(accounting.formatNumber(price, 4));
            $('#amount-' + id).text(accounting.formatNumber(qty * price, 4));
            $('#lineprofit-' + id).text(pcent_profit + '%');
            calcTotal();
        }
    });

    // on tax change
    $('#tax_id').change(function() {
        const mainTax = $(this).val();
        $('#quoteTbl tbody tr').each(function() {
            updateLineTax($(this).find('.tax_rate'));
            if ($(this).find('.qty').val() * 1) {
                const itemRate = accounting.unformat($(this).find('.rate').val());
                $(this).find('.price').val(accounting.formatNumber(itemRate * (mainTax / 100 + 1), 4));
                $(this).find('.rate').change();
            }
        });
    }).change();

    // on currency change
    let initRate = $('#currency option:selected').attr('currency_rate') * 1;
    $('#currency').change(function() {
        const currentRate = $(this).find(':selected').attr('currency_rate') * 1;
        if (currentRate > initRate) {
            $('#quoteTbl tbody tr').each(function() {
                let purchasePrice = accounting.unformat($(this).find('.buyprice').val()) * initRate;
                let itemRate = accounting.unformat($(this).find('.rate').val()) * initRate;
                purchasePrice = purchasePrice / currentRate;
                itemRate = itemRate / currentRate;
                $(this).find('.buyprice').val(accounting.formatNumber(purchasePrice, 4));
                $(this).find('.rate').val(accounting.formatNumber(itemRate, 4)).change();
            });
        } else {
            $('#quoteTbl tbody tr').each(function() {
                let purchasePrice = accounting.unformat($(this).find('.buyprice').val()) / currentRate;
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
        let taxable = 0;
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
                    const taxRate = accounting.unformat($(this).find('.tax_rate').val());
                    if (taxRate > 0) taxable += qty * rate;
                    total += amount;
                    subtotal += qty * rate;
                }
                // else {
                if (isMisc) {
                    const buyprice = accounting.unformat($(this).find('.buyprice').val());
                    const estqty = $(this).find('.estqty').val();
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
                // const amount = accounting.unformat($(this).find('.amount').text());

                // }
                // profit variables
                // const buyprice = accounting.unformat($(this).find('.buyprice').val());
                // const estqty = $(this).find('.estqty').val();
                // bp_subtotal += estqty * buyprice;

                // bp_subtotal += estqty * v;
            }
            $(this).find('.index').val(i);
        });
        $('#taxable').val(accounting.formatNumber(taxable));
        // $('#vatable').val(accounting.formatNumber(taxable));
        $('#total').val(accounting.formatNumber(total));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber((total - subtotal)));
        profitState.bp_total = bp_subtotal;
        profitState.sp_total = subtotal;
        calcProfit();
    }

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
                        @foreach ($faults as $fault)
                        <option value="{{ $fault->name }}" selected>{{ $fault->name }}</option>
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


    $('#attach-djc').prop("checked", true);
    $('#attach-djc').change(function() {
        if ($(this).is(":checked")) {
            $('#reference').attr('disabled', false);
            $('#referencedate').attr('disabled', false);
        } else {
            $('#reference').attr('disabled', true);
            $('#referencedate').attr('disabled', true);

        }
    });
    $('#add-check').prop("checked", true);
    $('#addqproduct').removeClass('d-none');
    $('#add-check').change(function() {
        if ($(this).is(":checked")) {
            $('#addqproduct').removeClass('d-none');
            $('#equipmentsTbl tbody').find('input').attr('disabled', false);
            $('#equipmentsTbl tbody').find('select').attr('disabled', false);
        } else {
            $('#addqproduct').addClass('d-none');
            $('#equipmentsTbl tbody').find('input').attr('disabled', true);
            $('#equipmentsTbl tbody').find('select').attr('disabled', true);

        }
    });

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

    // assign row index
    function assignIndex() {
        $('#equipmentsTbl tr').each(function(i) {
            if (i > 0) $(this).find('.row-index').val(i);
        });
    }

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
                            return {
                                label,
                                value,
                                data
                            };
                        })
                        response(data);
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {
                    data
                } = ui.item;
                // console.log(data)
                $('#uniqueid-' + i).val(data.unique_id);
                $('#eq-tid-' + i).val(data.tid);
                $('#itemid-' + i).val(data.id);
                $('#equipserial-' + i).val(data.equip_serial);
                $('#maketype-' + i).val(data.make_type);
                $('#capacity-' + i).val(data.capacity);
                $('#location-' + i).val(data.location);
            }
        };
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
            case 'casual':
                chrg.val(250).attr('readonly', true);
                break;
            case 'contract':
                chrg.val(250).attr('readonly', true);
                break;
            case 'attachee':
                chrg.val(150).attr('readonly', true);
                break;
            case 'outsourced':
                chrg.val(chrg.val()).attr('readonly', false);
                break;
        }
        skillTotal();
    });

    // add skill row
    let skillId = 0;
    const skillHtml = $('#skillTbl tbody tr:first').html();
    $('#skillTbl tbody tr:first').remove();
    $('#addRow').click(function() {
        skillId++;
        const html = skillHtml.replace(/-0/g, '-' + skillId).replace('d-none', '');
        $('#skillTbl tbody').append('<tr>' + html + '</tr>');
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
                    url = "{{ route('biller.taskschedules.quote_product_search') }}";
                    data.customer_id = $('#lead_id option:selected').attr('customer_id');
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
                    $('#productid-' + i).val(data.id);
                    $('#name-' + i).val(data.name);
                    $('#unit-' + i).val(data.unit);
                    $('#qty-' + i).val(1);
                    $('#estqty-' + i).val(1);
                    $('#taxrate-' + i).val(0);

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
                    // $('#rate-' + i).val(accounting.formatNumber(rate)).change();
                    $('#rate-' + i).val(accounting.formatNumber(data.purchase_price)).change();




                    if (data.units) {
                        let units = data.units.filter(v => v.unit_type == 'base');
                        if (units.length) $('#unit-' + i).val(units[0].code);
                    }
                } else {
                    $('#productid-' + i).val(data.id);
                    $('#name-' + i).val(data.name);
                    $('#unit-' + i).val(data.unit);
                    $('#qty-' + i).val(1);

                    const currencyRate = $('#currency option:selected').attr('currency_rate');
                    if (currencyRate > 1) {
                        data.purchase_price = parseFloat(data.purchase_price) / currencyRate;
                        data.price = parseFloat(data.price) / currencyRate;
                    }

                    $('#buyprice-' + i).val(accounting.formatNumber(data.purchase_price));
                    $('#estqty-' + i).val(1);

                    const rate = parseFloat(data.price);
                    let price = rate * ($('#tax_id').val() / 100 + 1);
                    $('#price-' + i).val(accounting.formatNumber(price));
                    $('#amount-' + i).text(accounting.formatNumber(price));
                    $('#rate-' + i).val(accounting.formatNumber(rate)).change();

                    if (data.units) {
                        let units = data.units.filter(v => v.unit_type == 'base');
                        if (units.length) $('#unit-' + i).val(units[0].code);
                    }
                }

            }
        };
    }
</script>
