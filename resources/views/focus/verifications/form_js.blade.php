{{ Html::script(mix('js/dataTable.js')) }}
<script>    
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };
    // ajax setup
    $.ajaxSetup(config.ajax);

    // Intialize datepicker
    $('.datepicker').each(function() {
        let d = $(this).attr('value') ? $(this).attr('value') : new Date();
        $(this).datepicker(config.date).datepicker('setDate', new Date(d));
    });

    // on qty, price, tax_rate change
    $('#productsTbl').on('change', '.qty, .price, .taxid', function() {
        const row = $(this).parents('tr');
        const qty = row.find('.qty').val()*1;
        const price = accounting.unformat(row.find('.price').val());
        const taxId = row.find('.taxid').val()*1;

        const tax = qty * price * taxId/100;
        row.find('.prodtax').val(accounting.formatNumber(tax, 4));

        const amount = (qty * price) + tax;
        row.find('.amount').val(accounting.formatNumber(amount, 4));
        row.find('.price').val(accounting.formatNumber(price, 4));
        console.log(price, amount)
        calcTotals();

        // required attr
        if ($(this).is('.qty') || $(this).is('.price')) 
            row.find('.remark').attr('required', true);
    });

    // set product rows
    const initProductRow = $('#productsTbl tbody tr:first').html();
    const initTitleRow = $('#productsTbl tbody tr:last').html();
    $('#productsTbl tbody tr').remove();
    const verification = @json(@$verification);
    if (verification) {
        const verificationItems = @json(@$verification->items);
        verificationItems.forEach((v,i) => {
            // product type
            if (v.a_type == 1) {
                $('#productsTbl tbody').append(`<tr>${initProductRow}</tr>`);
                const el =  $('#productsTbl tbody tr:last');
                el.find('.index').val(i);
                el.find('.num').val(v.numbering);
                el.find('.prodname').val(v.product_name).autocomplete(productAutocomplete());
                el.find('.unit').val(v.unit);
                el.find('.qty').val(v.product_qty*1);

                const price = v.product_subtotal*1;
                el.find('.price').val(accounting.formatNumber(v.product_subtotal, 4));

                const taxId = @json($quote->tax_id)*1;
                el.find('.taxid').val(taxId);

                const lineTax = v.product_qty * price * taxId/100;
                el.find('.prodtax').val(accounting.formatNumber(lineTax, 4));

                const amount = (v.product_qty * price) + lineTax; 
                el.find('.amount').val(accounting.formatNumber(amount, 4));
                el.find('.qt-itemid').val(v.quote_item_id);
                el.find('.prodid').val(v.product_id);
                el.find('.itemid').val(v.id);
            } else {
                $('#productsTbl tbody').append(`<tr>${initTitleRow}</tr>`);
                const el =  $('#productsTbl tbody tr:last');
                el.find('.index').val(i);
                el.find('.num').val(v.numbering);
                el.find('.prodname').val(v.product_name);
                el.find('.qt-itemid').val(v.quote_item_id);
                el.find('.itemid').val(v.id);
            }
        });

        const initJcRow = $('#jobcardsTbl tbody tr:first').html();
        $('#jobcardsTbl tbody tr').remove();
        const verificationJcs = @json(@$verification->jc_items);
        verificationJcs.forEach(v => {
            $('#jobcardsTbl tbody').append(`<tr>${initJcRow}</tr>`);
            const el =  $('#jobcardsTbl tbody tr:last');
            el.find('.jc_itemid').val(v.id);
            el.find('.jc_type').val(v.type);
            el.find('.jc_ref').val(v.reference);
            el.find('.jc_date').datepicker(config.date).datepicker('setDate', new Date(v.date));
            el.find('.jc_tech').val(v.technician);
            el.find('.jc_equip').val(v.equipment?.name);
            el.find('.jc_loc').val(v.location);
            el.find('.jc_fault').val(v.fault || 'none');
        });
    } else {
        const quoteItems = @json($quote->products);
        quoteItems.forEach((v,i) => {
            // product type
            if (v.a_type == 1) {
                $('#productsTbl tbody').append(`<tr>${initProductRow}</tr>`);
                const el =  $('#productsTbl tbody tr:last');
                el.find('.index').val(i);
                el.find('.num').val(v.numbering);
                el.find('.prodname').val(v.product_name).autocomplete(productAutocomplete());
                el.find('.unit').val(v.unit);
                el.find('.qty').val(v.product_qty*1);

                const price = v.product_subtotal*1;
                el.find('.price').val(accounting.formatNumber(v.product_subtotal, 4));

                const taxId = @json($quote->tax_id)*1;
                el.find('.taxid').val(taxId);

                const lineTax = v.product_qty * price * taxId/100;
                el.find('.prodtax').val(accounting.formatNumber(lineTax, 4));

                const amount = (v.product_qty * price) + lineTax; 
                el.find('.amount').val(accounting.formatNumber(amount, 4));
                el.find('.qt-itemid').val(v.id);
                el.find('.prodid').val(v.product_id);
            } else {
                // title type
                $('#productsTbl tbody').append(`<tr>${initTitleRow}</tr>`);
                const el =  $('#productsTbl tbody tr:last');
                el.find('.index').val(i);
                el.find('.num').val(v.numbering);
                el.find('.prodname').val(v.product_name);
                el.find('.qt-itemid').val(v.id);
            }
        });
    }
    calcTotals();  

    // on add product
    $('#addProduct').click(function() {
        $('#productsTbl tbody').append(`<tr>${initProductRow}</tr>`);
        const el =  $('#productsTbl tbody tr:last');
        el.find('.prodname').autocomplete(productAutocomplete());
        el.find('.index').val(el.index());
    });

    // on add title
    $('#add-title').click(function() {
        $('#productsTbl tbody').append(`<tr>${initTitleRow}</tr>`);
        const el =  $('#productsTbl tbody tr:last');
        el.find('.index').val(el.index());
    });

    // on clicking product row drop down menu
    $("#productsTbl").on("click", ".up, .down, .remove", function() {
        const row = $(this).parents("tr");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.remove')) {
            if (confirm('Are you sure?') && row.siblings().length) row.remove(); 
            calcTotals();           
        }
        // re-order indexes
        $("#productsTbl tbody tr").each(function(i) { $(this).find('.index').val(i) });
    });

    // totals
    function calcTotals() {
        let taxable = 0;
        let subtotal = 0;
        let tax = 0;
        let total = 0;
        $('#productsTbl tbody tr').each(function(i) {
            $(this).find('.index').val(i);
            const qty = accounting.unformat($(this).find('.qty').val());
            const price = accounting.unformat($(this).find('.price').val());
            const taxId = accounting.unformat($(this).find('.taxid').val());
            const lineTotal = accounting.unformat($(this).find('.amount').val());

            subtotal += qty * price;
            total += lineTotal;
            if (taxId > 0) {
                taxable += qty * price;
                tax += qty * price * taxId/100;
            }
        });
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber(tax));        
        $('#total').val(accounting.formatNumber(total));
    }

    // product name autocomplete
    let focusProductRow;
    $('#productsTbl').on('keyup', '.prodname', function() {
        focusProductRow = $(this).parents('tr');
    });
    function productAutocomplete() {
        return {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}",
                    method: 'POST',
                    data: {keyword: request.term},
                    success: result => response(result.map(v => ({
                        label: v.name,
                        value: v.name,
                        data: v
                    })))
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                const el = focusProductRow;

                el.find('.prodname').val(data.name);
                el.find('.unit').val(data.unit);
                el.find('.qty').val(1);

                const price = data.price*1;
                el.find('.price').val(accounting.formatNumber(price, 4));

                const taxId = @json($quote->tax_id);
                el.find('.taxid').val(taxId);
                const lineTax = price * taxId/100;
                el.find('.prodtax').val(accounting.formatNumber(lineTax, 4));

                const amount = price + lineTax; 
                el.find('.amount').val(accounting.formatNumber(amount, 4));
                el.find('.prodid').val(data.id);
                calcTotals();
            }
        };
    }
    

    /**
     * Equipments
     **/
    // on change row type
    $('#jobcardsTbl').on('change', '.jc_type', function() {
        const el = $(this).parents('tr');
        if ($(this).val() == 2) {
            // dnote row
            el.find('.jc_fault').addClass('invisible');
            el.find('.jc_equip').addClass('invisible');
            el.find('.jc_loc').addClass('invisible');
        } else {
            // jobcard row
            el.find('.jc_fault').removeClass('invisible');
            el.find('.jc_equip').removeClass('invisible');
            el.find('.jc_loc').removeClass('invisible');
        }
    });
    
    // add job card row
    const initJobcardRow = $('#jobcardsTbl tbody tr:first').html();
    $('.jc_equip:first').autocomplete(equipmentAutocomplete());
    $('#addJobcard').click(function() {
        $('#jobcardsTbl tbody').append(`<tr>${initJobcardRow}</tr>`);
        const el = $('#jobcardsTbl tbody tr:last');
        el.find('.jc_equip').autocomplete(equipmentAutocomplete());
        el.find('.jc_date').datepicker(config.date).datepicker('setDate', new Date());
    });
    // remove job card row
    $('#jobcardsTbl').on('click', '.remove', function() {
        const row = $(this).parents('tr');
        if (confirm('Are you sure ?')) {
            if (!row.siblings().length) $('#addJobcard').click();
            row.remove();
        }
    });

    // equipment autocomplete
    let focusEquipmentRow;
    $('#jobcardsTbl').on('keyup', '.jc_equip', function() {
        focusEquipmentRow = $(this).parents('tr');
    });
    function equipmentAutocomplete() {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    method: 'POST',
                    data: {
                        keyword: request.term, 
                        customer_id: "{{ $quote->customer_id }}",
                        branch_id: "{{ $quote->branch_id }}",
                    },
                    success: data => {
                        data = data.map(v => {
                            for (const key in v) {
                                if (!v[key]) v[key] = '';
                            }
                            const label = `${v.unique_id} ${v.equip_serial} ${v.make_type} ${v.model} ${v.machine_gas}
                                ${v.capacity} ${v.location} ${v.building} ${v.floor}`;
                            const value = v.unique_id;
                            const data = v;
                            return {label, value, data};
                        });
                        response(data);
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: (event, ui) => {
                const {data} = ui.item;
                const el = focusEquipmentRow;
                el.find('.jc_equipid').val(data.id);
                el.find('.jc_equip').val(data.make_type);
                el.find('.jc_loc').val(data.location);
            }
        };
    }    
</script>
