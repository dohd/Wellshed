{{ Html::script('focus/js/select2.min.js') }}
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    config = {
        ajax: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} },
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
        customerSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.customers.select') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term}),
                processResults: result => {
                    return { results: result.map(v => ({
                        text: v.company && v.name? `${v.company} - ${v.name}` : v.company || v.name, 
                        id: v.id,
                        currency_id: v.currency_id,
                    }))};
                }      
            }
        },
        invoiceSelect2Data: {},
        invoiceSelect2: {
            allowClear: true, 
            ajax: {
                url: "{{ route('biller.creditnotes.search_invoice') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term, customer_id: $("#customer").val(), currency_id: $("#currency").val()}),
                processResults: data => {
                    return { 
                        results: data.map(v => {
                            let tid = v.tid + '';
                            if (tid.length < 4) tid = '0000'.slice(0, 4 - tid.length) + tid;
                            const processData = {
                                text: v.tid && v.notes? `Inv-${tid} - ${v.notes}` : `Inv-${tid}`, 
                                id: v.id,
                                total: v.total,
                                subtotal: v.subtotal,
                                currency_id: v.currency_id,
                                fx_curr_rate: +v.fx_curr_rate,
                            };
                            config.invoiceSelect2Data = processData;
                            return processData; 
                        })
                    }
                },
            }
        },

        autoComplete: {
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
                    url, data,
                    method: 'POST',
                    success: result => response(result.map(v => ({label: v.name, value: v.name, data: v}))),
                });
            },
            autoFocus: true,
            select: function(event, ui) {
                const {data} = ui.item;
                const row = $(':focus').parents('tr');
                row.find('.prodvar-id').val(data.id);
                row.find('.name').val(data.name);
                row.find('.qty').val(1);
                row.find('.rate').val(accounting.formatNumber(+data.price)); 

                // foreign currency
                const fxRate = accounting.unformat($('#fx_curr_rate').val());
                if (fxRate > 1) {
                    const price = (+data.price / fxRate).toFixed(2);
                    row.find('.rate').val(accounting.formatNumber(price)); 
                    row.find('.prod-fx-rate').val(accounting.formatNumber(+data.price)); 
                }
                row.find('.prod-taxid').val($('#taxid').val()).trigger('input');

                // units (uom)
                if (data.units) {
                    const units = data.units.filter(v => v.unit_type == 'base');
                    if (units.length) row.find('.unit').val(units[0].code || '');
                }
            }
        },
    };

    const Form = {
        initRow: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customer').select2(config.customerSelect2);
            $('#invoice').select2(config.invoiceSelect2);
            $('#classlist, #account').select2({allowClear: true});
            Form.initRow = $('#products_tbl tbody tr:first').clone().html();
            $('#products_tbl tbody tr:first .name').autocomplete(config.autoComplete);

            $('#efrisReasonCode').change(Form.onChangeReasonCode);
            $('#taxid').change(Form.onChangeTax);
            $('#currency').change(Form.onChangeCurrency);
            $('#issue_refund').change(Form.onChangeIssueRefund);
            $('#fx_curr_rate').change(Form.onChangeFxCurrRate);
            
            $('#customer').on('select2:select', Form.onChangeCustomer);
            $(document).on('click', '.add-row, .remove-row', Form.addRemoveRow);
            $(document).on('input', '.qty, .rate, .prod-taxid', Form.onInputAmountAttr);
            $(document).on('change', '.check', Form.onChangeRowCheck);
            $(document).on('change', '.radio-item', Form.onChangeLoadItemsFrom);

            // edit mode
            Form.editModeHandler();
        },

        editModeHandler() {
            const cnote = @json(@$creditnote);
            if (cnote && cnote.id) {
                $('#products_tbl tbody tr:first').remove(); // remove template row
                $('#radio-stock, #radio-inv, #customer, #invoice').prop('disabled', true);
                const invoice = @json(@$creditnote->invoice);
                config.invoiceSelect2Data.fx_curr_rate = invoice.fx_curr_rate;
                config.invoiceSelect2Data.currency_id = invoice.currency_id;

                const rate = accounting.unformat($('#currency option:selected').attr('rate'));
                if (rate == 1 || rate == 0) $('#fx_curr_rate').attr('readonly', true);
                else $('#fx_curr_rate').attr('readonly', false);
                $('#fx_curr_rate').val(+cnote.fx_curr_rate);

                $('#efrisReasonCode').val(cnote.efris_reason_code).change();
                if (cnote.date) $('#date').datepicker('setDate', new Date(cnote.date));
                if (cnote.cu_invoice_no) $('#cu_invoice_no').val(cnote.cu_invoice_no);
                if (cnote.account_id) {
                    $('#issue_refund').prop('checked', true).change();
                    $('#account').val(cnote.account_id).change();
                    $('#payment_mode').val(cnote.payment_mode);
                    $('#reference_no').val(cnote.reference_no);
                }
                if (cnote.is_inv_items) {
                    // invoice items
                    $('#radio-stock').prop('checked', false);
                    $('#radio-inv').prop('checked', true);
                    $('thead th:first').removeClass('d-none');
                    $('thead th:last').addClass('d-none');
                } else {
                    // inventory items
                    $('#radio-stock').prop('checked', true);
                    $('#radio-inv').prop('checked', false);
                }

                $('tbody tr').each(function() {
                    if (cnote.is_inv_items) {
                        $(this).find('td:first').removeClass('d-none')
                        $(this).find('td:last').addClass('d-none')
                        $(this).find('.check').attr('checked', true);
                    } else {
                        $(this).find('.name').autocomplete(config.autoComplete);
                    }
                    // trigger totals
                    $(this).find('.qty').trigger('input');
                });
            } 
        },

        onChangeReasonCode() {
            const text = $(this).find(':selected').text();
            if (text.includes(':')) $('#efrisReasonCodeName').val(text.split(':')[1]);
            else $('#efrisReasonCodeName').val('');
            if ($(this).val() == '105') $('#note').attr('required', true);
            else $('#note').removeAttr('required');
            if (text.includes(':')) $('#note').val(text.split(':')[1]);
        },

        onChangeCustomer(e) {
            const data = e.params.data; 
            if (data.currency_id) $('#currency').val(data.currency_id).change();
            else $('#currency').val('');
        }, 

        onChangeCurrency() {
            const rate = accounting.unformat($(this).find(':selected').attr('rate'));
            if ($(this).val()) $('#fx_curr_rate').val(rate);
            else $('#fx_curr_rate').val('');
            if (rate == 1 || rate == 0) $('#fx_curr_rate').attr('readonly', true);
            else $('#fx_curr_rate').attr('readonly', false);
        },  

        onChangeFxCurrRate() {
            const fxRate = accounting.unformat($(this).val());
            if ($('#currency').val() && !fxRate) $(this).val($('#currency option:selected').attr('rate'));
            $('#products_tbl tbody .qty:not(:disabled)').each(function() {
                $(this).trigger('input');
            });
        },

        onChangeIssueRefund() {
            $('#account, #payment_mode, #reference_no').val('').change();
            if ($(this).prop('checked')) {
                $('.refund-card').removeClass('d-none');
            } else {
                $('.refund-card').addClass('d-none');
            }
        },

        onInputAmountAttr() {
            const row = $(this).parents('tr');
            const qty = accounting.unformat(row.find('.qty').val());
            const rate = accounting.unformat(row.find('.rate').val());
            const taxId = accounting.unformat(row.find('.prod-taxid').val());

            const subtotal = qty * rate;
            const tax = subtotal * taxId * 0.01;
            const taxable = tax > 0? subtotal : 0;
            const total = subtotal + tax;
            row.find('.prod-tax').val(accounting.formatNumber(tax));
            row.find('.prod-taxable').val(accounting.formatNumber(taxable));
            row.find('.prod-subtotal').val(accounting.formatNumber(subtotal));
            row.find('.prod-total').val(accounting.formatNumber(total));

            // foreign currency
            row.find('.prod-fx-gain').val(0);
            row.find('.prod-fx-loss').val(0);
            if ($('#radio-stock').prop('checked')) {
                const prodFxRate = accounting.unformat(row.find('.prod-fx-rate').val()); // currency fx rate
                if (prodFxRate) {
                    const fxSubtotal = qty * prodFxRate;
                    const fxTax = fxSubtotal * taxId * 0.01;
                    const fxTaxable = fxTax > 0? fxSubtotal : 0;
                    const fxTotal = fxSubtotal + fxTax;
                    row.find('.prod-fx-tax').val(accounting.formatNumber(fxTax));
                    row.find('.prod-fx-taxable').val(accounting.formatNumber(fxTaxable));
                    row.find('.prod-fx-subtotal').val(accounting.formatNumber(fxSubtotal));
                    row.find('.prod-fx-total').val(accounting.formatNumber(fxTotal));
                }
            } else {
                const invFxRate = accounting.unformat(config.invoiceSelect2Data.fx_curr_rate);
                const currFxRate = accounting.unformat($('#fx_curr_rate').val());
                if (currFxRate > 1) {
                    const fxSubtotal = subtotal * currFxRate;
                    const fxTax = fxSubtotal * taxId * 0.01;
                    const fxTaxable = fxTax > 0? fxSubtotal : 0;
                    const fxTotal = fxSubtotal + fxTax;
                    row.find('.prod-fx-tax').val(accounting.formatNumber(fxTax));
                    row.find('.prod-fx-taxable').val(accounting.formatNumber(fxTaxable));
                    row.find('.prod-fx-subtotal').val(accounting.formatNumber(fxSubtotal));
                    row.find('.prod-fx-total').val(accounting.formatNumber(fxTotal));
                    // gain or loss
                    if (invFxRate != currFxRate) {
                        const fxSubtotal2 = subtotal * invFxRate;
                        const fxTax2 = fxSubtotal2 * taxId * 0.01;
                        const fxTotal2 = fxSubtotal2 + fxTax2;
                        const diff = (fxTotal2-fxTotal).toFixed(4);
                        if (diff > 0) row.find('.prod-fx-gain').val(diff);
                        else row.find('.prod-fx-loss').val(-diff);
                    }
                }
            }

            Form.calcTotals();
        },
        
        onChangeTax() {
            const mainTax = accounting.unformat($(this).val());
            $('#products_tbl tbody tr').each(function () {
                let optionSelected;
                $(this).find('.prod-taxid option').each(function() {
                    const value = accounting.unformat($(this).attr('value'));
                    if ([mainTax, 0].includes(value)) $(this).removeClass('d-none');
                    else $(this).addClass('d-none');

                    $(this).attr('selected', false);
                    if (!optionSelected && mainTax == value) {
                        $(this).attr('selected', true);
                        $(this).parents('select').val(mainTax).trigger('input');
                        optionSelected = true;
                    }
                });
            });
        },

        onChangeRowCheck() {
            const row = $(this).parents('tr');
            const elems = 'input[type="text"], input[type="hidden"], select, textarea';
            if ($(this).prop('checked')) row.find(elems).attr('disabled', false);
            else row.find(elems).attr('disabled', true);
            row.find('.qty').trigger('input');
        },

        onChangeLoadItemsFrom() {
            // load invoice items
            if ($(this).val() == 1) {
                $('#products_tbl th:first').removeClass('d-none'); // show checkbox label
                $('#products_tbl th:last').addClass('d-none'); // hide action label
                $('#products_tbl tbody').html('');
                if (!$('#invoice').val()) return; 
                
                const tbody = $('#products_tbl tbody');
                $.post("{{ route('biller.creditnotes.load_invoice_items') }}", {invoice_id: $('#invoice').val()})
                .done(data => {
                    if (!data.length) return;
                    data.forEach(v => {
                        tbody.append(`<tr>${Form.initRow}</tr>`);
                        const row = tbody.find('tr:last');
                        row.find('td:first').removeClass('d-none'); // show checkbox
                        row.find('td:last').addClass('d-none'); // hide action button
                        row.find('.inv-item-id').val(v.id);
                        row.find('.num').val(v.numbering);
                        row.find('.project-type').val(v.cstm_project_type || '');
                        row.find('.name').val(v.description);
                        row.find('.unit').val(v.unit);
                        row.find('.qty').val(+v.product_qty);
                        row.find('.rate').val(+v.product_price);
                        const fxRate = accounting.unformat($('#fx_curr_rate').val());
                        const prodFxRate = (+v.product_price * fxRate).toFixed(4);
                        row.find('.prod-fx-rate').val(prodFxRate);  // fx field
                        row.find('.prod-taxid').val($('#taxid').val());
                        row.find('input[type="text"], input[type="hidden"], select, textarea').attr('disabled', true);
                    });
                })
                .fail((xhr, status, error) => console.log(error))
            } else {
                // load inventory items
                $('#products_tbl tbody').html('').append(`<tr>${Form.initRow}</tr>`);
                $('#products_tbl tbody tr:last .name').autocomplete(config.autoComplete);
                $('#products_tbl th:first').addClass('d-none'); // hide checkbox label
                $('#products_tbl th:last').removeClass('d-none'); // show action label
            }
            Form.calcTotals();
        },

        addRemoveRow() {
            if ($(this).is('.add-row')) {
                $(this).closest('tr').after(`<tr>${Form.initRow}</tr>`);
                const row = $(this).closest('tr').next();
                row.find('.name').autocomplete(config.autoComplete);
                // limit tax on line
                const mainTax = accounting.unformat($('#taxid').val());
                row.find('.prod-taxid option').each(function() {
                    const value = accounting.unformat($(this).attr('value'));
                    if ([mainTax, 0].includes(value)) $(this).removeClass('d-none');
                    else $(this).addClass('d-none');
                });
            } else {
                const row = $('#products_tbl tbody tr:last');
                if (!row.siblings().length) return;
                row.remove();
            }
            // set row numbering
            $('#products_tbl tbody tr').each(function(i) {
                $(this).find('.num').val(i+1)
            });
            Form.calcTotals();
        },

        calcTotals() {
            let aggrTaxable = 0;
            let aggrSubtotal = 0;
            let aggrTax = 0;
            let aggrTotal = 0;
            // fx
            let aggrFxTaxable = 0;
            let aggrFxSubtotal = 0;
            let aggrFxTax = 0;
            let aggrFxTotal = 0;
            let aggrFxGain = 0;
            let aggrFxLoss = 0;

            $('#products_tbl tbody tr').each(function() {
                const row = $(this);
                aggrTaxable += accounting.unformat(row.find('.prod-taxable:not(:disabled)').val());
                aggrSubtotal += accounting.unformat(row.find('.prod-subtotal:not(:disabled)').val());
                aggrTax += accounting.unformat(row.find('.prod-tax:not(:disabled)').val());
                aggrTotal += accounting.unformat(row.find('.prod-total:not(:disabled)').val());
                // fx
                aggrFxTaxable += accounting.unformat(row.find('.prod-fx-taxable:not(:disabled)').val());
                aggrFxSubtotal += accounting.unformat(row.find('.prod-fx-subtotal:not(:disabled)').val());
                aggrFxTax += accounting.unformat(row.find('.prod-fx-tax:not(:disabled)').val());
                aggrFxTotal += accounting.unformat(row.find('.prod-fx-total:not(:disabled)').val());
                aggrFxGain += accounting.unformat(row.find('.prod-fx-gain:not(:disabled)').val());
                aggrFxLoss += accounting.unformat(row.find('.prod-fx-loss:not(:disabled)').val());
            });
            $('#taxable').val(accounting.formatNumber(aggrTaxable));
            $('#subtotal').val(accounting.formatNumber(aggrSubtotal));
            $('#tax').val(accounting.formatNumber(aggrTax));
            $('#total').val(accounting.formatNumber(aggrTotal));
            // fx
            $('#fx-taxable').val(accounting.formatNumber(aggrFxTaxable));
            $('#fx-subtotal').val(accounting.formatNumber(aggrFxSubtotal));
            $('#fx-tax').val(accounting.formatNumber(aggrFxTax));
            $('#fx-total').val(accounting.formatNumber(aggrFxTotal));
            $('#fx-gain').val(accounting.formatNumber(aggrFxGain));
            $('#fx-loss').val(accounting.formatNumber(aggrFxLoss));
        },
    };

    $(Form.init);
</script>