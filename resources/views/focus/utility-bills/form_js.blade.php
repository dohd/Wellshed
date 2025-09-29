{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Form = {
        utilityBill: @json(@$utility_bill),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);
            $('#supplier').select2({allowClear: true});

            $('#supplier').change(Form.supplierChange);
            $('#currency').change(Form.onCurrencyChange);
            $('#tax_rate').change(() => Form.columnTotals());
            $('#documentsTbl').on('click', '.delete', Form.deleteRow);
            Form.columnTotals();

            const bill = @json(@$utility_bill);
            if (bill && bill.id) {
                $('#supplier').attr('disabled', true);
                $('#currency').attr('disabled', true);
                $('#fx_curr_rate').attr('readonly', false);
                if (bill.date) $('#date').datepicker('setDate', new Date(bill.date));
                else $('#date').val('');
                if (bill.due_date) $('#due_date').datepicker('setDate', new Date(bill.due_date));
                else $('#due_date').val('');
            } else {
                $('#supplier').val('').change();
                $('#currency').change();
            }
        },

        onCurrencyChange() {
            const rate = $(this).find('option:selected').attr('rate');
            $('#fx_curr_rate').val(rate);
            if (rate == 1) {
                $('#fx_curr_rate').attr('readonly', true);
            } else {
                $('#fx_curr_rate').attr('readonly', false);
            }

            $('#documentsTbl tbody tr').remove();
            const supplier_id = $('#supplier').val();
            if (supplier_id) {
                // fetch supplier grn items
                const grnUrl = "{{ route('biller.utility_bills.goods_receive_note') }}";
                $.post(grnUrl, {supplier_id, currency_id: $('#currency').val()}, data => {
                    data.forEach((v,i) => $('#documentsTbl tbody').append(Form.billItemRow(v,i)));
                    Form.columnTotals();
                });
            }
        },

        deleteRow() {
            const row = $(this).parents('tr');
            row.remove();
            if (!$('table tbody tr:first').length) 
                $('#supplier').val('').change();

            Form.columnTotals();
        },

        supplierChange() {
            $('#documentsTbl tbody tr').remove();
            const supplier_id = $(this).val();
            if (supplier_id) {
                // fetch supplier grn items
                const grnUrl = "{{ route('biller.utility_bills.goods_receive_note') }}";
                $.post(grnUrl, {supplier_id, currency_id: $('#currency').val()}, data => {
                    data.forEach((v,i) => $('#documentsTbl tbody').append(Form.billItemRow(v,i)));
                    Form.columnTotals();
                });
            }
        },

        billItemRow(v,i) {
            v.tax = +v.tax;
            const total = +v.tax > 0? (v.qty * v.rate) * (1 + v.tax * 0.01) : +v.total;
            return `
                <tr>
                    <td>${i+1}</td>
                    <td>${v.date? v.date.split('-').reverse().join('-') : ''}</td>
                    <td>${v.note}</td>
                    <td><input type="text" name="item_qty[]" value="${+v.qty}" class="form-control qty"></td>
                    <td><input type="text" name="item_subtotal[]" value="${+v.rate}" class="form-control rate" readonly></td>
                    <td><input type="text" name="item_tax[]" value="${+v.tax}" class="form-control tax" readonly></td>
                    <td><input type="text" name="item_total[]" value="${+v.total}" class="form-control total" readonly></td>
                    <td><a href="#" class="btn btn-link pt-0 delete"><i class="fa fa-trash fa-2x text-danger"></i></a></td>
                    <input type="hidden" name="item_ref_id[]" value="${v.id}">
                    <input type="hidden" name="item_note[]" value="${v.note}"  class="note">
                    <input type="hidden" name="rfx_rate[]" value="${+v.fx_rate}" class="rfx-subtotal">
                    <input type="hidden" name="rfx_subtotal[]" value="${+v.fx_subtotal}" class="rfx-subtotal">
                    <input type="hidden" name="rfx_taxable[]" value="${+v.fx_taxable}" class="rfx-taxable">
                    <input type="hidden" name="rfx_tax[]" value="${+v.fx_tax}" class="rfx-tax">
                    <input type="hidden" name="rfx_total[]" value="${+v.fx_total}" class="rfx-total">
                </tr>
            `;
        },

        columnTotals() {
            let subtotal = 0;
            let total = 0;
            let tax = 0;
            let fxSubtotal = 0;
            let fxTaxable = 0;
            let fxTax = 0;
            let fxTotal = 0;
            $('#documentsTbl tbody tr').each(function() {
                const row = $(this);
                const rate = accounting.unformat(row.find('.rate').val());
                const qty = accounting.unformat(row.find('.qty').val());

                const rowSubtotal = qty * rate;
                const rowAmount = rowSubtotal * (1 + $('#tax_rate').val() * 0.01);
                const rowTax = rowSubtotal * $('#tax_rate').val() * 0.01;
                tax += rowTax;
                subtotal += rowSubtotal
                total += rowAmount;
                row.find('.tax').val(accounting.formatNumber(rowTax));
                row.find('.total').val(accounting.formatNumber(rowAmount));

                // fx row totals
                const fxCurrRate = accounting.unformat($('#fx_curr_rate').val());
                const fxRowRate = accounting.unformat((rate * fxCurrRate).toFixed(4));
                const fxRowSubtotal = accounting.unformat((rowSubtotal * fxCurrRate).toFixed(4));
                const fxRowTaxable = accounting.unformat((rowSubtotal * fxCurrRate).toFixed(4));
                const fxRowTax = accounting.unformat((rowTax * fxCurrRate).toFixed(4));
                const fxRowTotal = accounting.unformat((rowAmount * fxCurrRate).toFixed(4));

                fxSubtotal += fxRowSubtotal;
                fxTaxable += fxRowTaxable;
                fxTax += fxRowTax;
                fxTotal += fxRowTotal;
                row.find('.rfx-rate').val(accounting.formatNumber(fxRowRate, 4));
                row.find('.rfx-subtotal').val(accounting.formatNumber(fxRowSubtotal, 4));
                row.find('.rfx-taxable').val(accounting.formatNumber(fxRowTaxable, 4));
                row.find('.rfx-tax').val(accounting.formatNumber(fxRowTax, 4));
                row.find('.rfx-total').val(accounting.formatNumber(fxRowTotal, 4));
            });
            $('#subtotal').val(accounting.formatNumber(subtotal));
            $('#tax').val(accounting.formatNumber(tax));
            $('#total').val(accounting.formatNumber(total));   
            // fx totals
            $('#fx_subtotal').val(accounting.formatNumber(fxSubtotal, 4));
            $('#fx_tax').val(accounting.formatNumber(fxTax, 4));
            $('#fx_taxable').val(accounting.formatNumber(fxTaxable, 4));
            $('#fx_total').val(accounting.formatNumber(fxTotal, 4));   
        },
    }

    $(Form.init);
</script>