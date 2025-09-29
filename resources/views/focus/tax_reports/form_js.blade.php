@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Form = {
        salesData: [],
        purchasesData: [],
        taxReport: @json(@$tax_report),
        
        init() {
            $.ajaxSetup(config.ajax);
            // month picker
            $('.datepicker').datepicker({
                autoHide: true,
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                format: 'MM-yyyy',
                onClose: function(dateText, inst) { 
                    $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                }
            });


            $('#record_month').change(function() {
                $('#tax_group').val('');
                Form.fetchSales();
                Form.fetchPurchases();
            });
            $('#tax_group').change(function() {
                Form.saleTaxRateChange();
                Form.purchaseTaxRateChange();
            });
            
            $('form').on('change', '#sale_file_all, #sale_remove_all, .sale-file-row, .sale-remove-row', this.saleRadioChange);
            $('form').on('change', '#purchase_file_all, #purchase_remove_all, .purchase-file-row, .purchase-remove-row', this.purchaseRadioChange);
            $('form').submit(this.formSubmit);

            if (this.taxReport) {
                // edit mode
                this.editTaxReport()
            } else {
                // create mode
                this.fetchSales();
                this.fetchPurchases();
            }
        },

        formSubmit() {
            // filter radio input values   
            $('#saleTbl tbody tr').each(function() {
                const isFiled = $(this).find('.is-filed').val();
                if (!isFiled) {
                    $(this).find('.sale-id').attr('disabled', true); 
                    $(this).find('.type').attr('disabled', true); 
                    $(this).find('.is-filed').attr('disabled', true); 
                }
                $(this).find('.sale-file-row').attr('disabled', true); 
                $(this).find('.sale-remove-row').attr('disabled', true); 
            });
            $('#purchaseTbl tbody tr').each(function() {             
                const isFiled = $(this).find('.is-filed').val();
                if (!isFiled) {
                    $(this).find('.purchase-id').attr('disabled', true); 
                    $(this).find('.type').attr('disabled', true); 
                    $(this).find('.is-filed').attr('disabled', true); 
                }
                $(this).find('.purchase-file-row').attr('disabled', true); 
                $(this).find('.purchase-remove-row').attr('disabled', true); 
            });

            // set required on month select options
            const filedSaleRows = $('#saleTbl').find('.sale-file-row:checked');
            const removedSaleRows = $('#saleTbl').find('.sale-remove-row:checked');
            const filedPurchaseRows = $('#purchaseTbl').find('.purchase-file-row:checked');
            const removedPurchaseRows = $('#purchaseTbl').find('.purchase-remove-row:checked');

            // validation
            const isFileSale = filedSaleRows.length || removedSaleRows.length;
            const isPurchaseFile = filedPurchaseRows.length || removedPurchaseRows.length;
            if (!isFileSale && !isPurchaseFile) {
                event.preventDefault();
                alert('filed returns required! check at least a single record');
                location.reload();
            }
        },

        editTaxReport() {
            // load radios
            $('#saleTbl tbody tr').each(function() {
                const fileInpt = $(this).find('.sale-file-row');
                const removeInpt = $(this).find('.sale-remove-row');
                if (fileInpt.attr('checked')) {
                    fileInpt.prop('checked', true).change();
                } else {
                    removeInpt.prop('checked', true).change();
                }
            });
            $('#purchaseTbl tbody tr').each(function() {
                const fileInpt = $(this).find('.purchase-file-row');
                const removeInpt = $(this).find('.purchase-remove-row');
                if (fileInpt.attr('checked')) {
                    fileInpt.prop('checked', true).change();
                } else {
                    removeInpt.prop('checked', true).change();
                }
            });
        },

        /**
         * sales 
        */
        saleTaxRateChange() {
            let data = Form.salesData;
            const tax = $('#tax_group').val();
            if ($('#tax_group option:selected').attr('key') == '00') {
                data = data.filter(v => parseFloat(v.tax_rate) == tax && v.is_tax_exempt);
            } else if (['16', '8', '0'].includes(tax)) {
                data = data.filter(v => parseFloat(v.tax_rate) == tax && !v.is_tax_exempt);
            }
            
            Form.renderSalesRow(data);
        },
        fetchSales() {
            const url = "{{ route('biller.tax_reports.get_sales') }}";
            $.post(url, {sale_month: $('#record_month').val()}, data => {
                // sort by date
                data.sort((a, b) => new Date(b.invoice_date) - new Date(a.invoice_date)); 
                // data = data.filter(v => (v['tax_pin'] != 0 && v['tax_pin'] != 'null'));
                
                this.salesData = data;
                this.renderSalesRow(data);
                this.saleTaxRateChange();
            });
        },
        renderSalesRow(data = []) {
            $('#saleTbl tbody').html('');
            data.forEach((v,i) => $('#saleTbl tbody').append(this.saleTableRow(v,i)));
        },
        saleTableRow(v={},i=0) {
            return `
                <tr>
                    <td>${i+1}</td>
                    <td>${v.type}</td>
                    <td>${v.tax_pin? v.tax_pin : ''}</td>
                    <td>${v.invoice_date.split('-').reverse().join('-')}</td>
                    <td>${v.customer}</td>
                    <td>${v.invoice_tid}</td>
                    <td>${v.cu_invoice_no}</td>
                    <td>${v.note}</td>
                    <td class="subtotal">${accounting.formatNumber(v.subtotal)}</td>
                    <td>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input sale-file-row" type="radio" name="radio_${i}">
                            <label for="radio_${i}">file</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input sale-remove-row" type="radio" name="radio_${i}">
                            <label for="radio_${i}" class="text-danger">remove</label>
                        </div>
                    </td>
                    <input type="hidden" class="tax" value="${v.tax}">
                    <input type="hidden" class="total" value="${v.total}">
                    <input type="hidden" class="sale-id" name="sale_id[]" value="${v.id}">
                    <input type="hidden"  class="type" name="sale_type[]" value="${v.type}">
                    <input type="hidden" class="is-filed" name="sale_is_filed[]">
                </tr>
            `
        },
        saleRadioChange() {
            const tableRows = $('#saleTbl tbody tr');
            if ($(this).is('#sale_file_all')) {
                tableRows.each(function() {
                    $(this).find('.sale-file-row').prop('checked', true);
                    $(this).find('.sale-remove-row').prop('checked', false);
                });
            } else if ($(this).is('#sale_remove_all')) {
                tableRows.each(function() {
                    $(this).find('.sale-file-row').prop('checked', false);
                    $(this).find('.sale-remove-row').prop('checked', true);
                });
            } else if ($(this).is('.sale-file-row') || $(this).is('.sale-remove-row')) {
                $('#sale_file_all').prop('checked', false);
                $('#sale_remove_all').prop('checked', false);
            }

            tableRows.each(function() {
                if ($(this).find('.sale-file-row:checked').length) {
                    $(this).find('.is-filed').val(1);
                } else if ($(this).find('.sale-remove-row:checked').length) {
                    $(this).find('.is-filed').val(0);
                }
            });

            Form.calcTotals();
        },


        /**
         * purchases
        */
        purchaseTaxRateChange() {
            let data = Form.purchasesData;
            const tax = $('#tax_group').val();
            if ($('#tax_group option:selected').attr('key') == '00') {
                data = [];
            } else if (['16', '8', '0'].includes(tax)) {
                data = data.filter(v => parseFloat(v.tax_rate) == tax);
            }

            Form.renderPurchasesRow(data);
        },
        fetchPurchases() {
            const url = "{{ route('biller.tax_reports.get_purchases') }}";
            $.post(url, {purchase_month: $('#record_month').val()}, data => {
                // sort by date
                data.sort((a, b) => new Date(b.purchase_date) - new Date(a.purchase_date)); 
                data = data.filter(v => (v['tax_pin'] != 0 && v['tax_pin'] != 'null'));

                this.purchasesData = data;
                this.renderPurchasesRow(data);
                this.purchaseTaxRateChange();
            });
        },
        renderPurchasesRow(data = []) {
            $('#purchaseTbl tbody').html('');
            data.forEach((v,i) => $('#purchaseTbl tbody').append(this.purchaseTableRow(v,i))); 
        },
        purchaseTableRow(v={},i=0) {
            return `
                <tr>
                    <td>${i+1}</td>
                    <td>${v.type}</td>
                    <td>${v.tax_pin? v.tax_pin : ''}</td>
                    <td>${v.purchase_date.split('-').reverse().join('-')}</td>
                    <td>${v.supplier}</td>
                    <td>${v.invoice_no}</td>
                    <td>${v.note}</td>
                    <td class="subtotal">${accounting.formatNumber(v.subtotal)}</td>
                    <td>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input purchase-file-row" type="radio" name="radio_p${i}">
                            <label for="radio_p${i}">file</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input purchase-remove-row" type="radio" name="radio_p${i}">
                            <label for="radio_p${i}" class="text-danger">remove</label>
                        </div>
                    </td>
                    <input type="hidden" class="tax" value="${v.tax}">
                    <input type="hidden" class="total" value="${v.total}">
                    <input type="hidden" class="purchase-id" name="purchase_id[]" value="${v.id}">
                    <input type="hidden" class="type" name="purchase_type[]" value="${v.type}">
                    <input type="hidden" class="is-filed" name="purchase_is_filed[]">
                </tr>
            `
        },
        purchaseRadioChange() {
            const tableRows = $('#purchaseTbl tbody tr');
            if ($(this).is('#purchase_file_all')) {
                tableRows.each(function() {
                    $(this).find('.purchase-file-row').prop('checked', true);
                    $(this).find('.purchase-remove-row').prop('checked', false);
                });
            } else if ($(this).is('#purchase_remove_all')) {
                tableRows.each(function() {
                    $(this).find('.purchase-file-row').prop('checked', false);
                    $(this).find('.purchase-remove-row').prop('checked', true);
                });
            } else if ($(this).is('.purchase-file-row') || $(this).is('.purchase-remove-row')) {
                $('#purchase_file_all').prop('checked', false);
                $('#purchase_remove_all').prop('checked', false);
            }

            tableRows.each(function() {
                if ($(this).find('.purchase-file-row:checked').length) {
                    $(this).find('.is-filed').val(1);
                } else if ($(this).find('.purchase-remove-row:checked').length) {
                    $(this).find('.is-filed').val(0);
                }
            });

            Form.calcTotals();
        },

        calcTotals() {
            // sales
            let saleSubtotal = 0;
            let saleTax = 0;
            let saleTotal = 0;
            $('#saleTbl tbody tr').each(function() {
                if ($(this).find('.sale-file-row:checked').length) {
                    saleSubtotal += accounting.unformat($(this).find('.subtotal').text());
                    saleTax += accounting.unformat($(this).find('.tax').val());
                    saleTotal += accounting.unformat($(this).find('.total').val());
                }
            });
            $('#sale_subtotal').val(accounting.formatNumber(saleSubtotal));
            $('#sale_tax').val(accounting.formatNumber(saleTax));
            $('#sale_total').val(accounting.formatNumber(saleTotal));

            // purchases
            let purchaseSubtotal = 0;
            let purchaseTax = 0;
            let purchaseTotal = 0;
            $('#purchaseTbl tbody tr').each(function() {
                if ($(this).find('.purchase-file-row:checked').length) {
                    purchaseSubtotal += accounting.unformat($(this).find('.subtotal').text());
                    purchaseTax += accounting.unformat($(this).find('.tax').val());
                    purchaseTotal += accounting.unformat($(this).find('.total').val());
                }
            });
            $('#purchase_subtotal').val(accounting.formatNumber(purchaseSubtotal));
            $('#purchase_tax').val(accounting.formatNumber(purchaseTax));
            $('#purchase_total').val(accounting.formatNumber(purchaseTotal));
        },
    };

    $(() => Form.init());
</script>
@endsection
