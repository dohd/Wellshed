{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        ajax: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} },
        date: {format: "{{config('core.user_date_format')}}", autoHide: true}, 
        select2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.customers.select') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term}),
                processResults: result => {
                    return { results: result.map(v => ({text: `${v.name} - ${v.company}`, id: v.id }))};
                }      
            },
        },
        projectSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.projects.project_search') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({
                    search: term,
                    customer_id: $('#customer').val(),
                }),
                processResults: result => {
                    return { results: result.map(v => ({text: `${v.name}`, id: v.id }))};
                }      
            },
        },
    };

    const Form = {
        invoicePayment: @json(@$invoice_payment),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);
            $('#customer').select2({allowClear: true});

            $('#customer').change(Form.customerChange);
            $('#payment_type').change(Form.paymentTypeChange);
            $('#rel_payment').change(Form.relatedPaymentChange);
            $('#currency').change(Form.currencyChange);
            // $('#send_link').change(Form.sendLinkChange);
            
            $('#invoiceTbl').on('change', '.paid, .wh-vat, .wh-tax', Form.allocationChange);
            $('#amount').keyup(Form.allocateAmount)
                .focusout(Form.amountFocusOut)
                .focus(Form.amountFocus);

            $('form').submit(Form.formSubmit);
            
            // edit mode
            const pmt = @json(@$invoice_payment);
            if (pmt && pmt.id) {
                $('#customer, #payment_type, #rel_payment, #currency').attr('disabled', true);
                if (['advance_payment', 'on_account'].includes($('#payment_type').val())) {
                    $('.project-container').removeClass('d-none');
                    $('#project').show().select2(config.projectSelect2);
                }

                if (pmt.date) $('#date').datepicker('setDate', new Date(pmt.date));
                else $('#date').val('');
                if (pmt.note) $('#note').val(pmt.note);
                if (pmt.currency?.id)  $('#currency').val(pmt.currency.code);
                $('#fx_curr_rate').val(+pmt.fx_curr_rate).attr('readonly', false);
                $('#amount').val(accounting.formatNumber(pmt.amount*1));
                $('#account').val(pmt.account_id);
                $('#payment_mode').val(pmt.payment_mode);
                $('#reference').val(pmt.reference);
                // $('#email').attr('readonly', true);
                // $('#phone_number').attr('readonly', true);
                // if(pmt.send_link == 'yes'){
                //     $('.div_phone').removeClass('d-none');
                //     $('.div_email').removeClass('d-none');
                // }
                Form.calcTotal();
                // allocation
                if (pmt.rel_payment_id) {
                    $('#account, #payment_mode, #reference').attr('disabled', true);
                    $('#rel_payment').after(`<input type="hidden" name="rel_payment_id" value="${pmt.rel_payment_id}">`);
                }
            }
        },

        // sendLinkChange(){
        //     var value = $(this).val();
        //     var opt = $('#customer option:selected');
        //     var phone = opt.attr('phoneNumber');
        //     var email = opt.attr('email');
        //     if (value == 'yes'){
        //         $('.div_phone').removeClass('d-none');
        //         $('.div_email').removeClass('d-none');
        //         $('#phone_number').val(phone);
        //         $('#email').val(email);

        //     }else{
        //         $('.div_phone').addClass('d-none');
        //         $('.div_email').addClass('d-none');
        //         $('#phone_number').val('');
        //         $('#email').val('');
        //     }
        // },

        formSubmit() {
            // filter unallocated inputs
            $('#invoiceTbl tbody tr').each(function() {
                let allocatedAmount = $(this).find('.paid').val();
                if (accounting.unformat(allocatedAmount) == 0) {
                    $(this).remove();
                } 
            });
            if (Form.invoicePayment && $('#payment_type').val() == 'per_invoice' && !$('#invoiceTbl tbody tr').length) {
                if (!confirm('Allocating zero on line items will reset this payment! Are you sure?')) {
                    event.preventDefault();
                    location.reload();
                }
            }
            // check if payment amount = allocated amount
            let amount = accounting.unformat($('#amount').val());
            let allocatedTotal = accounting.unformat($('#allocate_ttl').val());
            if (amount != allocatedTotal && $('#payment_type').val() == 'per_invoice') {
                event.preventDefault();
                alert('Total Allocated Amount must be equal to Payment Amount!');
            }
            // clear disabled attributes
            $(this).find(':disabled').attr('disabled', false);
        },

        currencyChange() {
            const rate = +$(this).find('option:selected').attr('rate');
            $('#fx_curr_rate').val(rate);
            if (rate == 1) $('#fx_curr_rate').attr('readonly', true);
            else $('#fx_curr_rate').attr('readonly', false);
            $('#customer').change();
        },

        customerChange() {
            $('#amount').val('');
            $('#allocate_ttl').val('');
            $('#balance').val('');
            $('#invoiceTbl tbody').html('');
            $('#rel_payment option:not(:first)').remove();
            Form.loadUnallocatedPayments();

            // set currency
            const opt = $(this).find(':selected');
            const rate = +opt.attr('currencyRate') || '';
            const currency_id = opt.attr('currencyId') || '';
            $('#currency').val(opt.attr('currencyCode') || '');
            $('#fx_curr_rate').val(rate).attr('readonly', rate > 1? false : true);
            $('#currency-id').val(currency_id);

            // set bank
            if (this.value) {
                $('#account option').each(function() {
                    if ($(this).attr('currencyId') == currency_id) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            } else {
                $('#account option').removeClass('d-none');
            }

            // fetch invoices
            const customer_id = this.value;
            if (customer_id && $('#payment_type').val() == 'per_invoice') {
                $('#rel_payment').val('');
                $.get("{{ route('biller.invoices.client_invoices') }}", {customer_id, currency_id})
                .done(data => {
                    data.forEach((v, i) => $('#invoiceTbl tbody').append(Form.invoiceRow(v, i)));
                })
                .fail((xhr, status, error) => console.log(error));
            }
        },

        invoiceRow(v, i) {
            var dueDate = v.invoiceduedate || '';
            if (dueDate) dueDate = dueDate.split('-').reverse().join('-');
            return `
                <tr>
                    <td class="text-center">${dueDate}</td>
                    <td>${v.tid}</td>
                    <td>${v.notes}</td>
                    <td>${v.status}</td>
                    <td>${accounting.formatNumber(v.total)}</td>
                    <td>${accounting.formatNumber(v.amountpaid)}</td>
                    <td class="text-center due"><b>${accounting.formatNumber(v.total - v.amountpaid)}</b></td>
                    <td><input type="text" class="form-control paid" name="paid[]"></td>
                    <td><input type="text" class="form-control wh-vat" name="wh_vat[]"></td>
                    <td><input type="text" class="form-control wh-tax" name="wh_tax[]"></td>
                    <input type="hidden" name="invoice_id[]" value="${v.id}">
                </tr>
            `;
        },

        loadUnallocatedPayments() {
            if ($('#customer').val() && $('#payment_type').val() == 'per_invoice') {
                $('#rel_payment').attr('disabled', false).val('').change();
            } else {
                $('#rel_payment').attr('disabled', true).val('').change();
            }
            // fetch unallocated payments
            $.post("{{ route('biller.invoice_payments.select_unallocated_payments') }}", {customer_id: $('#customer').val()})
            .then(data => {
                if (data.length) {
                    data.forEach(v => {
                        let balance = accounting.unformat(v.amount - v.allocate_ttl);
                        balance = accounting.formatNumber(balance);
                        let paymentType = v.payment_type.split('_').join(' ');
                        paymentType = paymentType.charAt(0).toUpperCase() + paymentType.slice(1);
                        const date = v.date? v.date.split('-').reverse().join('-') : '';
                        const text = `(${balance} - ${paymentType}: ${date}) - ${v.payment_mode.toUpperCase()} ${v.reference}`;
                        
                        $('#rel_payment').append(`
                            <option
                                value=${v.id}
                                customer_id=${v.customer_id}
                                amount=${+v.amount}
                                allocateTotal=${+v.allocate_ttl}
                                accountId=${v.account_id}
                                paymentMode=${v.payment_mode}
                                reference=${v.reference}
                                date=${v.date}
                            >
                                ${text}
                            </option>
                        `);
                    });
                }
            });
        },

        paymentTypeChange() {
            // project field
            if (['advance_payment', 'on_account'].includes($(this).val())) {
                $('.project-container').removeClass('d-none');
                $('#project').val('').change();
                $('#project').show().select2(config.projectSelect2);
            } else {
                $('.project-container').addClass('d-none');
                $('#project').val('').change();
                $('#project').select2('destroy').hide();
            }


            $('#amount, #allocate_ttl, #balance').val('');
            if ($(this).val() == 'per_invoice') {
                $('#customer').change();
                Form.loadUnallocatedPayments();
            } else {
                $('#invoiceTbl tbody tr').remove();
                $('#rel_payment').val('').attr('disabled', true);
                $('#rel_payment option:not(:first)').remove();
                $('#amount').val('').attr('readonly', false);
                $('#account').val('').attr('disabled', false);
                $('#payment_mode').val('').attr('disabled', false);
                $('#reference').val('').attr('readonly', false);
                $('#allocate_ttl, #balance').val('');
            }
        },

        allocationChange() {
            const row = $(this).parents('tr');
            const paid = row.find('.paid').val();
            const whVat = row.find('.wh-vat').val();
            const whTax = row.find('.wh-tax').val();
            row.find('.paid').val(accounting.formatNumber(accounting.unformat(paid)));
            row.find('.wh-vat').val(accounting.formatNumber(accounting.unformat(whVat)));
            row.find('.wh-tax').val(accounting.formatNumber(accounting.unformat(whTax)));
            Form.calcTotal();
        },

        allocateAmount() {
            if ($('#rel_payment').val()) {
                const lumpsomeAmount = accounting.unformat($('#rel_payment option:selected').attr('amount'));
                const currAmount = accounting.unformat($(this).val());
                if (currAmount > lumpsomeAmount) $(this).val(accounting.formatNumber(lumpsomeAmount));
            }

            let dueTotal = 0;
            let allocateTotal = 0;
            let decrAmount = accounting.unformat($(this).val());
            const pmt = @json(@$invoice_payment);
            if (pmt && pmt.id) {
                $('#invoiceTbl tbody tr').each(function(i) {
                    const paid = accounting.unformat($(this).find('.paid').val());
                    decrAmount -= paid;
                    allocateTotal += paid;
                });
                const amount = accounting.unformat($(this).val());
                $('#unallocate_ttl').val(accounting.formatNumber(amount - allocateTotal));
                $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            } else {
                $('#invoiceTbl tbody tr').each(function(i) {
                    const due = accounting.unformat($(this).find('.due').html());
                    if (due > decrAmount) $(this).find('.paid').val(accounting.formatNumber(decrAmount));
                    else if (decrAmount >= due) $(this).find('.paid').val(accounting.formatNumber(due));
                    else $(this).find('.paid').val(0);
                    const paid = accounting.unformat($(this).find('.paid').val());
                    decrAmount -= paid;
                    dueTotal += due;
                    allocateTotal += paid;
                });
                $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
                $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
                const amount = accounting.unformat($(this).val());
                $('#unallocate_ttl').val(accounting.formatNumber(amount - allocateTotal));
            }
        },

        amountFocus() {
            if (!$('#customer').val()) $(this).blur();
        },

        amountFocusOut() {
            const amount = accounting.unformat($(this).val());
            if (amount) $(this).val(accounting.formatNumber(amount));
        },

        relatedPaymentChange() {
            if (+this.value) {
                const opt = $(this).find(':selected');
                $('#date').datepicker('setDate', new Date(opt.attr('date'))).attr('readonly', true);
                $('#reference').val(opt.attr('reference')).attr('readonly', true);
                $('#account').val(opt.attr('accountId')).attr('disabled', true);
                $('#payment_mode').val(opt.attr('paymentMode')).attr('disabled', true);

                let balance = parseFloat(opt.attr('amount')) - parseFloat(opt.attr('allocateTotal'));
                const unallocated = accounting.unformat(balance);
                $('#amount').prop('readonly', false).val(unallocated).keyup().focusout();
            } else {
                ['amount', 'reference'].forEach(v => $('#'+v).val('').attr('readonly', false).keyup());
                ['account', 'payment_mode'].forEach(v => $('#'+v).val('').attr('disabled', false));
            }
        },

        calcTotal() {
            let dueTotal = 0;
            let allocateTotal = 0;
            let whVatTotal = 0;
            let whTaxTotal = 0;
            $('#invoiceTbl tbody tr').each(function(i) {
                const due = accounting.unformat($(this).find('.due').text());
                const paid = accounting.unformat($(this).find('.paid').val());
                const whvat = accounting.unformat($(this).find('.wh-vat').val());
                const whtax = accounting.unformat($(this).find('.wh-tax').val());
                dueTotal += due;
                allocateTotal += paid;
                whVatTotal += whvat;
                whTaxTotal += whtax;
            });
            const amount = accounting.unformat($('#amount').val());
            $('#unallocate_ttl').val(accounting.formatNumber(amount - allocateTotal));
            $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
            $('#wh-vat-amount').val(accounting.formatNumber(whVatTotal));
            $('#wh-tax-amount').val(accounting.formatNumber(whTaxTotal));
        },
    };    

    $(Form.init);
</script>
