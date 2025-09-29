{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        ajax: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} },
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
        customerSelect2: {
            ajax: {
                url: "{{ route('biller.customers.select') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term}),
                processResults: result => {
                    return { results: result.map(v => ({text: `${v.company} - ${v.taxid}`, id: v.id }))};
                }      
            },
            allowClear: true
        },
    };

    // invoice row template
    function invoiceRow(v, i) {
        const receiptAmount = +v.receipt_amount || '';
        const cstmAttr = receiptAmount > 0 && $('#certificate').val() == 'vat' ? 
            'readonly' : (receiptAmount > 0 && $('#certificate').val() == 'tax'? 'disabled' : '');

        return `
            <tr>
                <td class="text-center">${v.invoiceduedate? v.invoiceduedate.split('-').reverse().join('-') : ''}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.notes || ''}</td>
                <td>${v.status}</td>
                <td>${accounting.formatNumber(+v.total)}</td>
                <td>${accounting.formatNumber(+v.amountpaid)}</td>
                <td class="text-center due"><b>${accounting.formatNumber(v.total - v.amountpaid)}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]" value="${receiptAmount}" ${cstmAttr}></td>
                <input type="hidden" name="invoice_id[]" value="${v.id}">
                <input type="hidden" class="rcpt-item-id" name="paid_invoice_item_id[]" value="${v.paid_invoice_item_id || ''}">
            </tr>
        `;
    }

    $.ajaxSetup(config.ajax);
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
    // $('#customer').select2(config.customerSelect2);
    $('#customer').select2({allowClear: true});

    $('form').submit(function() {
        // filter unallocated inputs
        $('#invoiceTbl tbody tr').each(function() {
            let paidInp = $(this).find('.paid');
            if (paidInp.length && accounting.unformat(paidInp.val()) == 0) {
                $(this).remove(); 
            } 
        });
    });

    // on customer change
    $('#customer').change(function() {
        $.post("{{ route('biller.withholdings.select_invoices') }}", {customer_id: $(this).val(), 'cert_type': $('#certificate').val()})
        .then(data => {
            $('#amount').val('');
            $('#balance').val('');
            $('#allocate_ttl').val('');
            $('#invoiceTbl tbody').html('');
            if (data.length) data.forEach((v, i) => $('#invoiceTbl tbody').append(invoiceRow(v, i)));
            calcTotal();
        });
    }).trigger('change');

    // on change certificate
    $('#certificate').change(function() {
        if ($(this).val() == 'vat') {
            $('#withholding_cert').attr('disabled', true);
            $('#withholding_cert').val('').change();
        } else  {
            $('#withholding_cert').attr('disabled', false);
            $('#withholding_cert option:not(:eq(0))').remove();

            // fetch unallocated wh_tax
            $.post("{{ route('biller.withholdings.select_unallocated_wh_tax') }}", {customer_id: $('#customer').val()})
            .then(data => {
                if (data.length) {
                    data.forEach(v => {
                        const date = v.tr_date? v.tr_date.split('-').reverse().join('-') : '';
                        const htmlText = `(${accounting.formatNumber(+v.amount)} - Payment Date: ${date}) - ${v.reference}`;
                        $('#withholding_cert').append(`<option value="${v.id}"></option>`);
                        $('#withholding_cert option:last')
                        .text(htmlText)
                        .attr('certDate', v.cert_date)
                        .attr('amount', +v.amount)
                        .attr('allocateTotal', +v.allocate_ttl)
                        .attr('reference', v.reference)
                        .attr('trDate', v.tr_date)
                        .attr('note', v.note);
                    });
                }
            });

            // fetch invoices
            $.post("{{ route('biller.withholdings.select_invoices') }}", {customer_id: $('#customer').val(), 'cert_type': $('#certificate').val()})
            .then(data => {
                $('#amount').val('');
                $('#balance').val('');
                $('#allocate_ttl').val('');
                $('#invoiceTbl tbody').html('');
                if (data.length) data.forEach((v, i) => $('#invoiceTbl tbody').append(invoiceRow(v, i)));
                calcTotal();
            });
        } 
    });    

    // On allocating amount on invoices
    $('#invoiceTbl').on('change', '.paid', function() {
        const row = $(this).parents('tr');
        const due = accounting.unformat(row.find('.due').text());
        const paid = accounting.unformat($(this).val());
        if (paid > due) $(this).val(accounting.formatNumber(due));
        else $(this).val(accounting.formatNumber(paid));
        calcTotal();
        // check if amount is less than allocated
        const amount = accounting.unformat($('#amount').val());
        let allocatedTotal = 0;
        $('.paid').each(function() {
            if (!$(this).attr('readonly')) allocatedTotal += accounting.unformat($(this).val());
        });

        if (amount < allocatedTotal) {
            alert('Cannot allocate more than withheld amount!');
            $(this).val(0);
        } 
    });

    // On amount keyup
    $('#amount').keyup(function() {
        let dueTotal = 0;
        let allocatedTotal = 0;
        $('#invoiceTbl tbody tr').each(function() {
            const due = accounting.unformat($(this).find('.due').text());
            const paid = accounting.unformat($(this).find('.paid').val());
            dueTotal += due;
            allocatedTotal += paid;
        });
        const balance = dueTotal - allocatedTotal;
        $('#balance').val(accounting.formatNumber(balance));
        $('#allocate_ttl').val(accounting.formatNumber(allocatedTotal));
        const unallocatedAmount = accounting.unformat($('#amount').val()) - allocatedTotal;
        $('#unallocated_ttl').val(accounting.formatNumber(unallocatedAmount));
    }).focusout(function() {
        if (+this.value) $(this).val(accounting.formatNumber(accounting.unformat($(this).val())));
    }).focus(function() {
        if (!$('#customer').val()) $(this).blur();
    });

    // on certificate change
    $('#withholding_cert').change(function() {
        if (this.value > 0) {
            ['cert_date', 'reference', 'tr_date', 'note'].forEach(v => $('#'+v).attr('readonly', true));
            const opt = $(this).find(':selected');
            $('#cert_date').datepicker('setDate', new Date(opt.attr('certDate')));
            $('#reference').val(opt.attr('reference'));
            $('#tr_date').datepicker('setDate', new Date(opt.attr('trDate')));
            $('#note').val(opt.attr('note'));
            const remAmount = opt.attr('amount') - opt.attr('allocateTotal');
            $('#amount').val(remAmount).keyup().focusout();

            $('#invoiceTbl tbody tr').each(function() {
                if ($(this).find('.rcpt-item-id').val() > 0) {
                    $(this).find('.paid').attr({disabled: false, readonly: true});
                }
            });
        } else {
            ['amount', 'reference', 'note', 'cert_date', 'tr_date'].forEach(v => $('#'+v).attr('readonly', false).val(''));
            ['cert_date', 'tr_date'].forEach(v => $('#'+v).datepicker('setDate', new Date()));

            $('#invoiceTbl tbody tr').each(function() {
                if ($(this).find('.rcpt-item-id').val() > 0) {
                    $(this).find('.paid').attr({disabled: true, readonly: false});
                }
            });
        }
    });   

    function calcTotal() {
        let dueTotal = 0;
        let allocatedTotal = 0;
        $('#invoiceTbl tbody tr').each(function() {
            const due = accounting.unformat($(this).find('.due').text());
            const paid = accounting.unformat($(this).find('.paid').val());
            dueTotal += due;
            allocatedTotal += paid;
        });
        const balance = dueTotal - allocatedTotal;
        $('#balance').val(accounting.formatNumber(balance));
        $('#allocate_ttl').val(accounting.formatNumber(allocatedTotal));
        const unallocatedAmount = accounting.unformat($('#amount').val()) - allocatedTotal;
        $('#unallocated_ttl').val(accounting.formatNumber(unallocatedAmount));
    }
</script>