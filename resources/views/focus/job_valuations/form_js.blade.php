{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>    
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        autoComplete: {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}",
                    data: {keyword: request.term},
                    method: 'POST',
                    success: result => response(result.map(v => ({
                        label: `${v.name}`,
                        value: v.name,
                        data: v
                    }))),
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                const tr = $(':focus').parents('tr');
                tr.find('.prodvar-id').val(data.id);
                tr.find('.item-code').text(data.code);
                tr.find('.qty-inp').val(1);
                tr.find('.price-inp').val(accounting.formatNumber(+data.price));
                tr.find('.amount').text(accounting.formatNumber(+data.price));
                tr.find('.valued-bal').text(accounting.formatNumber(+data.price));
                tr.find('.subtotal-inp').val(accounting.formatNumber(+data.price));
                tr.find('.amount-inp').val(accounting.formatNumber(+data.price));
                tr.find('.perc-val').val(100).keyup();
                if (data?.units.length) {
                    const unit = data.units[0];
                    tr.find('.unit').text(unit.code);
                    tr.find('.unit-inp').val(unit.code);
                    
                }
            }
        },
        equipAutoComplete: {
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
                            return {label, value: v.unique_id, data: v};
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
        },
    };

    // ajax setup
    $.ajaxSetup(config.ajax);
    $('#employee_ids').select2({allowClear:true})
    // Intialize datepicker
    $('.datepicker').each(function() {
        let d = $(this).attr('value') ? $(this).attr('value') : new Date();
        $(this).datepicker(config.date).datepicker('setDate', new Date(d));
    });
    $('#completion_date').datepicker(config.date);

    // auto-allocate % valuated
    $('#orderItemsPerc, #materialExpPerc, #directExpPerc').on('change', function() {
        this.value = accounting.unformat(this.value);
        if (this.value > 100) this.value = 100;
        else if (this.value < 0) this.value = 0;
        const value = this.value;
        if ($(this).is('#orderItemsPerc')) {
            $('#productsTbl .perc-val').each(function() {
                $(this).val(value).keyup();
            });
        } else if ($(this).is('#materialExpPerc')) {
            $('#materialsTbl .perc-val').each(function() {
                $(this).val(value).keyup();
            });
        } else if ($(this).is('#directExpPerc')) {
            $('#expensesTbl .perc-val').each(function() {
                $(this).val(value).keyup();
            });
        } 
    });

    // on keyup % retention
    $('#percRetention, #valSubtotal, #retention').on('keyup', function() {
        if (!$(this).is('#retention')) {
            const percValue = accounting.unformat($('#percRetention').val());
            const valuedSubtotal = accounting.unformat($('#valSubtotal').val());
            const retention = percValue / 100 * valuedSubtotal;
            $('#retention').val(accounting.formatNumber(retention));            
        }
        if (!$(this).is('#percRetention')) {
            const retention = accounting.unformat($('#retention').val());
            const valuedSubtotal = accounting.unformat($('#valSubtotal').val());
            let percValue = retention / valuedSubtotal * 100;
            if (!(percValue >= 0 && percValue <= 100)) percValue = 0;
            $('#percRetention').val(accounting.formatNumber(percValue));            
        }
        // retention note condition
        const percValue = accounting.unformat($('#percRetention').val());
        const retention = accounting.unformat($('#retention').val());
        if (percValue > 0 || retention > 0) {
            $('#retentionNote').attr('required', true);
        } else {
            $('#retentionNote').removeAttr('required');
        }
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

    $('#select_completion').click(function(){
        if ($(this).is(':checked', true)) {
            $('.completion').removeClass('d-none');
            $('#user_ids').attr('disabled', false);
        } else {
            $('.completion').addClass('d-none');
            $('#user_ids').attr('disabled', true);
        }
    });

    $('#dlp_reminder').change(function(){
        let dlp_reminder = $(this).val();
        let dlp_period = $('#dlp_period').val();
        let dlp_days = 30.42*dlp_period;
        if(dlp_reminder > dlp_days){
            $('#dlp_reminder').val(0);
        }

    })

    // on keyup order-items % valuated
    $('#productsTbl').on('keyup', '.perc-val, .amount-val', function() {
        $(this).attr('readonly', false);

        let value = accounting.unformat(this.value);
        const tr = $(this).parents('tr');
        const taxRate = accounting.unformat(tr.find('.tax-rate').val());
        const valuedBal = accounting.unformat(tr.find('.valued-bal').text());
        const tax = valuedBal * taxRate / 100;
        tr.find('.tax').val(accounting.formatNumber(tax));

        if ($(this).is('.perc-val')) {
            if (value > 100 || value < 0) {
                value = value > 100? 100 : (value < 100? 0 : value);
                this.value = value;
            }
            const amountValued = valuedBal * value / 100;
            tr.find('.amount-val').val(accounting.formatNumber(amountValued));
            tr.find('.valued-bal-inp').val(accounting.formatNumber(valuedBal - amountValued));
            tr.find('.amount-val').attr('readonly', true);
        } 
        if ($(this).is('.amount-val')) {
            if (value < 0) {
                value = 0;
                this.value = value;
            }
            let percValued = value / valuedBal * 100;
            if (percValued > 100 || percValued < 0) {
                percValued = percValued > 100? 100 : (percValued < 0? 0 : percValued);
                value = percValued / 100 * valuedBal;
                this.value = accounting.formatNumber(value);
            }
            tr.find('.tax').val(accounting.formatNumber(tax));
            tr.find('.perc-val').val(accounting.formatNumber(percValued,4));
            tr.find('.valued-bal-inp').val(accounting.formatNumber(valuedBal - value));
            tr.find('.perc-val').attr('readonly', true);
        }
        calcOrderItemTotals();
    });

    // tax rate change
    $('#productsTbl').on('change', '.tax-rate', function() {
        $(this).parents('tr').find('.perc-val').keyup();
    });
    $('#tax-id').change(function() {
        const taxRate = this.value;
        $('#productsTbl .tax-rate option').each(function() {
            $(this).removeClass('d-none');
            if (taxRate) {
                const optionVal = +$(this).attr('value');
                if (+taxRate === 0 && optionVal !== 0) {
                    $(this).addClass('d-none');
                } else if (+taxRate && ![+taxRate, 0].includes(optionVal)) {
                    $(this).addClass('d-none');
                }
            }
        });
        $('#productsTbl .tax-rate').each(function() {
            $(this).val(taxRate).change();
        });
    });

    // set product rows
    const itemRowHtml = $('#productsTbl .product-templ').clone().html();
    const titleRowHtml = $('#productsTbl .title-templ').clone().html();
    const itemRowHtml2 = $('#productsTbl .product-templ-add').clone().html();
    const titleRowHtml2 = $('#productsTbl .title-templ-add').clone().html();
    $('#productsTbl tbody').html('');
    const orderItems = @json($quote->orderItems);
    orderItems.forEach((v,i) => {
        const tbody = $('#productsTbl tbody');
        let tr;
        // product type
        if (v.a_type == 1) {
            tbody.append(`<tr>${itemRowHtml}</tr>`);
            tr =  tbody.find('tr:last');            
            tr.find('.item-code').text(v.product_variation?.code || '');
            tr.find('.unit').text(v.unit || '');
            tr.find('.unit-inp').val(v.unit || '');
            tr.find('.qty').text(+v.product_qty);
            tr.find('.qty-inp').val(+v.product_qty);

            const subtotal = +v.product_subtotal;
            const amount = v.product_qty * subtotal;
            const valuedBal = +v.valued_bal;
            tr.find('.valued-bal').text(accounting.formatNumber(valuedBal));
            tr.find('.valued-bal-inp').val(accounting.formatNumber(valuedBal));
            tr.find('.price').text(accounting.formatNumber(subtotal));
            tr.find('.price-inp').val(accounting.formatNumber(subtotal));
            tr.find('.subtotal-inp').val(accounting.formatNumber(subtotal));
            tr.find('.amount').text(accounting.formatNumber(amount));
            tr.find('.amount-inp').val(accounting.formatNumber(subtotal));
            tr.find('.prodvar-id').val(v.product_id);
        } else {
            tbody.append(`<tr>${titleRowHtml}</tr>`);
            tr =  tbody.find('tr:last');
        }
        tr.find('.quoteitem-id').val(v.id);
        tr.find('.num').text(v.numbering || '');
        tr.find('.num-inp').val(v.numbering || '');
        tr.find('.descr').text(v.product_name);
        tr.find('.descr-inp').val(v.product_name);
        tr.find('.type-inp').val(v.a_type);
    });
    calcOrderItemTotals();

    // add title or product
    $(document).on('click', '.add-product, .add-title, .del-row', function(e) {
        const tr = $(this).parents('tr');
        if ($(this).is('.add-product')) {
            tr.after(`<tr class="added-row">${itemRowHtml2}</tr>`);
            const newRow = tr.next();
            newRow.find('.descr-inp').autocomplete(config.autoComplete);
        } else if ($(this).is('.add-title')) {
            tr.after(`<tr class="added-row">${titleRowHtml2}</tr>`);            
        } else {
            if (tr.siblings().length) tr.remove();
        }
    });
    $(document).on('keyup', '.qty-inp, .price-inp', function() {
        const tr = $(this).parents('tr.added-row');
        if (!tr.length) return;
        const qty = accounting.unformat(tr.find('.qty-inp').val());
        const price = accounting.unformat(tr.find('.price-inp').val());
        const subtotal = qty * price;
        tr.find('.subtotal-inp').val(accounting.formatNumber(subtotal));
        tr.find('.amount-inp').val(accounting.formatNumber(subtotal));
        tr.find('.amount').text(accounting.formatNumber(subtotal));
        tr.find('.valued-bal').text(accounting.formatNumber(subtotal));
        tr.find('.perc-val').keyup();
    });

    // keyup on expense perc-val 
    $('#materialsTbl, #expensesTbl').on('keyup', '.perc-val', function() {
        const value = +this.value;
        if (value > 100) this.value = 100;
        if (value < 0) this.value = 0;
        const row = $(this).parents('tr');
        const percValued = accounting.unformat(this.value);
        const valuedBal = accounting.unformat(row.find('.valued-bal').text());
        const amountValued = valuedBal * percValued / 100;
        row.find('.amount-val').val(accounting.formatNumber(amountValued));
        row.find('.valued-bal-inp').val(accounting.formatNumber(valuedBal - amountValued));

        if (value) row.find('.amount-val').attr('readonly', true);
        else row.find('.amount-val').attr('readonly', false);
        calcExpenseItemTotals();
    });
    // keyup on expense amount-val 
    $('#materialsTbl, #expensesTbl').on('keyup', '.amount-val', function() {
        const value = +this.value;
        if (value < 0) this.value = 0;
        const row = $(this).parents('tr');
        const amountValued = accounting.unformat(this.value);
        const valuedBal = accounting.unformat(row.find('.valued-bal').text());
        let percValued = amountValued / valuedBal * 100;
        if (percValued > 100 || percValued < 0) {
            this.value = 0;
            percValued = 0;
        }
        row.find('.perc-val').val(accounting.formatNumber(percValued,4));
        row.find('.valued-bal-inp').val(accounting.formatNumber(valuedBal - amountValued));

        if (value) row.find('.perc-val').attr('readonly', true);
        else row.find('.perc-val').attr('readonly', false);
        calcExpenseItemTotals();
    });

    // expense summary totals
    function calcExpenseItemTotals() {
        let aggrExpense = 0, aggrValuedAmount = 0;
        let milestoneAmt = 0, noMilestoneAmt = 0;
        $('#materialsTbl tbody tr').each(function() {
            const percValued = accounting.unformat($(this).find('.perc-val').val());
            const valuedBal = accounting.unformat($(this).find('.valued-bal').text());
            if (percValued) aggrValuedAmount += valuedBal * percValued / 100;
            aggrExpense += valuedBal;
            // check milestone
            const amountValued = accounting.unformat($(this).find('.amount-val').val());
            if ($(this).find('.budget-line-id').val()) milestoneAmt += amountValued;
            else noMilestoneAmt += amountValued;
        });

        let milestoneAmt2 = 0, noMilestoneAmt2 = 0;
        $('#expensesTbl tbody tr').each(function() {
            const percValued = accounting.unformat($(this).find('.perc-val').val());
            const valuedBal = accounting.unformat($(this).find('.valued-bal').text());
            if (percValued) aggrValuedAmount += valuedBal * percValued / 100;
            aggrExpense += valuedBal;
            // check milestone
            const amountValued = accounting.unformat($(this).find('.amount-val').val());
            if ($(this).find('.budget-line-id').val()) milestoneAmt2 += amountValued;
            else noMilestoneAmt2 += amountValued;
        });

        const tbody1 = $('#materialSummaryTbl tbody');
        tbody1.find('tr:eq(0) td:eq(0)').text(accounting.formatNumber(noMilestoneAmt));
        tbody1.find('tr:eq(1) td:eq(0)').text(accounting.formatNumber(milestoneAmt));
        tbody1.find('tr:eq(2) td:eq(0)').text(accounting.formatNumber(milestoneAmt + noMilestoneAmt));

        const tbody2 = $('#serviceSummaryTbl tbody');
        tbody2.find('tr:eq(0) td:eq(0)').text(accounting.formatNumber(noMilestoneAmt2));
        tbody2.find('tr:eq(1) td:eq(0)').text(accounting.formatNumber(milestoneAmt2));
        tbody2.find('tr:eq(2) td:eq(0)').text(accounting.formatNumber(milestoneAmt2 + noMilestoneAmt2));

        // main summary
        const percValued = (aggrValuedAmount / aggrExpense * 100).toFixed(2);
        const valuedBal = aggrExpense - aggrValuedAmount;
        const tbody = $('#expSummaryTbl tbody');
        tbody.find('tr:eq(0) td:eq(1)').text(accounting.formatNumber(aggrExpense));
        tbody.find('tr:eq(1) td:eq(1)').text(accounting.formatNumber(aggrValuedAmount));
        tbody.find('tr:eq(1) td:eq(2)').text(accounting.formatNumber(percValued));
        tbody.find('tr:eq(1) td:eq(3)').text(accounting.formatNumber(valuedBal));
        $('#expBalance').val(accounting.formatNumber(valuedBal));
        $('#expTotal').val(accounting.formatNumber(aggrExpense));
        $('#expValuated').val(accounting.formatNumber(aggrValuedAmount));
        $('#expValuatedPerc').val(accounting.formatNumber(percValued));
    }

    // order-items summary totals
    function calcOrderItemTotals() {
        let subtotal = 0, taxable = 0, tax = 0;
        let valuedTax = 0, valuedTaxable = 0, valuedSubtotal = 0;
        $('#productsTbl tbody tr').each(function(i) {
            $(this).find('.index-inp').val(i);
            const valuedBal = accounting.unformat($(this).find('.valued-bal').text());
            const rowTax = accounting.unformat($(this).find('.tax').val());
            const taxRate = accounting.unformat($(this).find('.tax-rate').val());
            const amountValued = accounting.unformat($(this).find('.amount-val').val());
            subtotal += valuedBal;
            valuedSubtotal += amountValued;
            if (rowTax > 0) {
                tax += rowTax;
                taxable += valuedBal;
                valuedTax += amountValued * taxRate/100;
                valuedTaxable += amountValued;
            }
        });
        const percValued = (valuedSubtotal/subtotal * 100).toFixed(2);
        const valuedBal = subtotal - valuedSubtotal;

        const tbody = $('#summaryTbl tbody');
        // quote
        tbody.find('tr:eq(0) td:eq(1)').text(accounting.formatNumber(taxable));
        tbody.find('tr:eq(0) td:eq(2)').text(accounting.formatNumber(tax));
        tbody.find('tr:eq(0) td:eq(3)').text(accounting.formatNumber(subtotal));
        // valuation
        tbody.find('tr:eq(1) td:eq(1)').text(accounting.formatNumber(valuedTaxable));
        tbody.find('tr:eq(1) td:eq(2)').text(accounting.formatNumber(valuedTax));
        tbody.find('tr:eq(1) td:eq(3)').text(accounting.formatNumber(valuedSubtotal));
        tbody.find('tr:eq(1) td:eq(4)').text(accounting.formatNumber(percValued));
        tbody.find('tr:eq(1) td:eq(5)').text(accounting.formatNumber(valuedBal));

        const total = subtotal + tax;
        const valuedTotal = valuedSubtotal + valuedTax;

        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber(tax));     
        $('#total').val(accounting.formatNumber(total));
        // valuation
        $('#balance').val(accounting.formatNumber(valuedBal));
        $('#valTaxable').val(accounting.formatNumber(valuedTaxable));
        $('#valSubtotal').val(accounting.formatNumber(valuedSubtotal)).keyup();
        $('#valTax').val(accounting.formatNumber(valuedTax));     
        $('#valTotal').val(accounting.formatNumber(valuedTotal));
        $('#valPerc').val(accounting.formatNumber(percValued));
    }
    

    /**
     * Equipments
     **/
    $('#hasJobcard').change(function() {
        if ($(this).prop('checked')) {
            $('.job-card-ctn').removeClass('d-none');
        } else {
            $('.job-card-ctn').addClass('d-none');
        }
    });

    // on change row type
    $('#jobcardsTbl').on('change', '.jc_type', function() {
        const row = $(this).parents('tr');
        // value 2 is dnote, else jobcard
        if ($(this).val() == 2) ['.jc_fault', '.jc_equip', '.jc_loc'].forEach((v) => row.find(v).addClass('d-none'));
        else ['.jc_fault', '.jc_equip', '.jc_loc'].forEach((v) => row.find(v).removeClass('d-none'));
    });
    
    // add job card row
    const initJobcardRow = $('#jobcardsTbl tbody tr:first').clone();
    $('.jc_equip:first').autocomplete(config.equipAutoComplete);
    $('#addJobcard').click(function() {
        $('#jobcardsTbl tbody').append(`<tr>${initJobcardRow.html()}</tr>`);
        const el = $('#jobcardsTbl tbody tr:last');
        el.find('.jc_equip').autocomplete(config.equipAutoComplete);
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
</script>
