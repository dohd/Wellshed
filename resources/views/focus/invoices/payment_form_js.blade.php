<script>
    const config = {
        ajax: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} },
        date: "{{config('core.user_date_format')}}",
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
    };

    const Form = {
        invoicePayment: @json(@invoice_payment),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker')
                .datepicker({format: config.date, autoHide: true})
                .datepicker('setDate', new Date());
            $('#person').select2(config.select2);

            $('#person').change(this.customerChange);
            $('#payment_type').change(this.paymentTypeChange);
            $('#rel_payment').change(this.paymentChange);

            $('#invoiceTbl').on('change', '.paid', this.allocationChange);
            $('#amount').keyup(this.amountChange)
                .focusout(this.amountFocusOut)
                .focus(this.amountFocus);

            $('form').submit(this.formSubmit);
            this.loadUnallocatedPayments();

            if (this.invoicePayment) {
                const payment = this.invoicePayment;
                console.log(payment)
            }
        },

        formSubmit() {
            // filter unallocated inputs
            $('#invoiceTbl tbody tr').each(function() {
                let paymentInp = $(this).find('.paid');
                if (accounting.unformat(paymentInp.val()) == 0) {
                    $(this).remove();
                } 
            });
            if (Form.invoicePayment && $('#payment_type').val() == 'per_invoice' && !$('#invoiceTbl tbody tr').length) {
                if (!confirm('Unallocating all line items destroys this instance! Are you sure?')) {
                    event.preventDefault();
                    location.reload();
                }
            }
            // check if payment amount >= allocated amount
            const pmtAmount = accounting.unformat($('#amount').val());
            const allocAmount = accounting.unformat($('#allocate_ttl').val());
            if (allocAmount > pmtAmount) {
                event.preventDefault();
                alert('Total Allocated Amount must be less or equal to payment Amount!');
            }
        },

        invoiceRow(v, i) {
            return `
                <tr>
                    <td class="text-center">${v.invoiceduedate.split('-').reverse().join('-')}</td>
                    <td>${v.tid}</td>
                    <td class="text-center">${v.notes}</td>
                    <td>${v.status}</td>
                    <td>${accounting.formatNumber(v.total)}</td>
                    <td>${accounting.formatNumber(v.amountpaid)}</td>
                    <td class="text-center due"><b>${accounting.formatNumber(v.total - v.amountpaid)}</b></td>
                    <td><input type="text" class="form-control paid" name="paid[]"></td>
                    <input type="hidden" name="invoice_id[]" value="${v.id}">
                </tr>
            `;
        },

        customerChange() {
            $('#amount').val('');
            $('#allocate_ttl').val('');
            $('#balance').val('');
            $('#invoiceTbl tbody tr').remove();
            if ($(this).val()) {
                // fetch invoices
                const url = "{{ route('biller.invoices.client_invoices') }}?customer_id=" + $(this).val();
                $.get(url, data => {
                    data.forEach((v, i) => {
                        $('#invoiceTbl tbody').append(Form.invoiceRow(v, i));
                    });
                });
            }
        },

        loadUnallocatedPayments() {
            $('#rel_payment').attr('disabled', false).change();
            const payments = @json($unallocated_pmts);
            payments.forEach(v => {
                const str = `
                    ${v.date.split('-').reverse().join('-')}: 
                    (${v.payment_type} ${accounting.formatNumber(v.amount)})
                    ${v.payment_mode} - ${v.reference}
                `;
                
                const option = `
                    <option
                        value=${v.id}
                        amount=${v.amount}
                        allocateTotal=${v.allocate_ttl}
                        accountId=${v.account_id}
                        paymentMode=${v.payment_mode}
                        reference=${v.reference}
                        date=${v.date}
                        >
                        ${str}
                    </option>
                    `;
                $('#rel_payment').append(option);
            });
        },

        paymentTypeChange() {
            $('#amount').val('');
            $('#allocate_ttl').val('');
            $('#balance').val('');
            if ($(this).val() == 'per_invoice') {
                $('#rel_payment').val(0).attr('disabled', false).change();
                $('#person').change();
                const payments = @json($unallocated_pmts);
                if (payments.length) Form.loadUnallocatedPayments();
            } else {
                $('#invoiceTbl tbody tr').remove();
                $('#rel_payment').val(0).attr('disabled', true);
                $('#rel_payment option:not(:first)').remove();
                $('#amount').val('').attr('readonly', false);
                $('#account').val('').attr('disabled', false);
                $('#payment_mode').val('').attr('disabled', false);
                $('#reference').val('').attr('readonly', false);
                $('#allocate_ttl').val('');
                $('#balance').val('');
            }
        },

        allocationChange() {
            const paid = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(paid));
            Form.calcTotal();
        },

        amountChange() {
            let dueTotal = 0;
            let allocateTotal = 0;
            let amount = accounting.unformat($(this).val());
            const lastCount = $('#invoiceTbl tbody tr').length - 1;
            $('#invoiceTbl tbody tr').each(function(i) {
                if (i == lastCount) return;
                const due = accounting.unformat($(this).find('.due').text());
                if (due > amount) $(this).find('.paid').val(accounting.formatNumber(amount));
                else if (amount > due) $(this).find('.paid').val(accounting.formatNumber(due));
                else $(this).find('.paid').val(0);
                const paid = accounting.unformat($(this).find('.paid').val());
                amount -= paid;
                dueTotal += due;
                allocateTotal += paid;
            });
            $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
        },

        amountFocus() {
            if (!$('#person').val()) $(this).blur();
        },

        amountFocusOut() {
            const amount = accounting.unformat($(this).val());
            if (amount) $(this).val(accounting.formatNumber(amount));
        },

        paymentChange() {
            if ($(this).val() == 0) {
                ['amount', 'reference'].forEach(v => $('#'+v).val('').attr('readonly', false));
                ['account', 'payment_mode'].forEach(v => $('#'+v).val('').attr('disabled', false));
                $('#date').datepicker('setDate', new Date()).attr('readonly', false);
                // loadInvoice();
            } else {
                $('#person').change();
                const opt = $(this).find(':selected');
                $('#date').datepicker('setDate', new Date(opt.attr('date'))).attr('readonly', true);
                $('#reference').val(opt.attr('reference')).attr('readonly', true);
                $('#account').val(opt.attr('accountId')).attr('disabled', true);
                $('#payment_mode').val(opt.attr('paymentMode')).attr('disabled', true);
                // execute after ajax async call
                const balance = accounting.unformat(opt.attr('amount') - opt.attr('allocateTotal'));
                setTimeout(() => $('#amount').val(balance).keyup().focusout().attr('readonly', true), 100);
            }
        },

        calcTotal() {
            let dueTotal = 0;
            let allocateTotal = 0;
            const lastCount = $('#invoiceTbl tbody tr').length - 1;
            $('#invoiceTbl tbody tr').each(function(i) {
                if (i == lastCount) return;
                const due = accounting.unformat($(this).find('.due').text());
                const paid = accounting.unformat($(this).find('.paid').val());
                dueTotal += due;
                allocateTotal += paid;
            });
            $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
        },
    };    

    $(() => Form.init());
</script>
