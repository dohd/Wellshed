{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        invoiceSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.sale_returns.select_invoices') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customer").val()}),
                processResults: data => {
                    return { 
                        results: data.map(v => ({
                            text: v.notes,
                            id: v.id
                        }))
                    }
                },
            }
        },
        quoteSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.sale_returns.select_quotes') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customer").val(), reference: $('#ref').val()}),
                processResults: data => {
                    return { 
                        results: data.map(v => ({
                            text: v.notes,
                            id: v.id
                        }))
                    }
                },
            }
        },
    };

    const Index = {
        initRow: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customer').select2({allowClear: true});
            $('#invoice').select2(config.invoiceSelect);
            $('#quote').select2(config.quoteSelect);
            Index.initRow = $('#productsTbl tbody tr:first');

            $('#customer').change(Index.customerChange);
            $('#ref').change(Index.referenceChange);
            $('#invoice').change(Index.invoiceChange);
            $('#quote').change(Index.quoteChange);
            
            $('#productsTbl').on('keyup', '.return-qty', Index.returnQtyChange);
                
            const data = @json(@$sale_return);
            if (data && data.id) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                $('#customer').attr('disabled', true);
                $('#invoice').attr('disabled', true);
                $('#quote').attr('disabled', true);
                $('#ref').attr('disabled', true);
                Index.calcTotals();
            } else {
                $('#ref').change();
            }
        },

        customerChange() {
            $('#invoice').val('').change();
            $('#quote').val('').change();
        },

        referenceChange() {
            if (this.value == 'invoice') {
                $('.quote-col').addClass('d-none');
                $('.invoice-col').removeClass('d-none');
            } else {
                $('.quote-col').removeClass('d-none');
                $('.invoice-col').addClass('d-none');
            }
            $('#invoice').val('').change();
            $('#quote').val('').change();
        },

        quoteChange() {
            $('#productsTbl tbody').html('');
            const url = "{{ route('biller.sale_returns.issued_stock_items') }}";
            const params = {quote_id: $(this).val()};
            $.post(url, params, data => {
                data.forEach((v,i) => {
                    const row = Index.initRow.clone();
                    row.find('.serial').text(i+1);
                    const refName = v.quote_item_name || v.invoice_item_name || v.verified_item_name || '';
                    row.find('.ref-name').text(refName);
                    row.find('.name').text(v.name);
                    row.find('.product-code').text(v.code);
                    row.find('.unit').text(v.uom);
                    row.find('.qty-onhand').text(+v.qty);
                    row.find('.qty-onhand-inp').val(+v.qty);
                    row.find('.cost').val(+v.purchase_price);
                    row.find('.prodvar-id').val(v.id);
                    row.find('.verified-item-id').val(v.verified_item_id);
                    $('#productsTbl tbody').append(row);
                });                
            });
        },

        invoiceChange() {
            $('#productsTbl tbody').html('');
            const url = "{{ route('biller.sale_returns.issued_stock_items') }}";
            const params = {invoice_id: $(this).val()};
            $.post(url, params, data => {
                data.forEach((v,i) => {
                    const row = Index.initRow.clone();
                    row.find('.serial').text(i+1);
                    row.find('.name').text(v.name);
                    row.find('.product-code').text(v.code);
                    row.find('.unit').text(v.uom);
                    row.find('.qty-onhand').text(+v.qty);
                    row.find('.qty-onhand-inp').val(+v.qty);
                    row.find('.cost').val(+v.purchase_price);
                    row.find('.prodvar-id').val(v.id);
                    row.find('.verified-item-id').val(v.verified_item_id);
                    $('#productsTbl tbody').append(row);
                });                
            });
        },

        returnQtyChange() {
            this.value = accounting.unformat(this.value);
            if (this.value < 0) this.value *= -1;
            const row = $(this).parents('tr');
            const cost = accounting.unformat(row.find('.cost').val());
            const qtyOnHand = accounting.unformat(row.find('.qty-onhand').text());
            const returnQty = accounting.unformat(this.value);
            const newQty = qtyOnHand + returnQty;
            row.find('.new-qty').text(newQty);
            row.find('.new-qty-inp').val(newQty);
            row.find('.amount').val(cost * returnQty);

            // edit mode
            const data = @json(@$sale_return);
            if (data && data.id) {
                const originValue = accounting.unformat(row.find('.return-qty').attr('origin-value'));
                if (returnQty == originValue) {
                    row.find('.new-qty').text(qtyOnHand);
                    row.find('.new-qty-inp').val(qtyOnHand);
                } 
                if (returnQty < originValue) {
                    row.find('.new-qty').text(qtyOnHand-returnQty);
                    row.find('.new-qty-inp').val(qtyOnHand-returnQty);
                } 
                if (returnQty > originValue) {
                    row.find('.new-qty').text(qtyOnHand - originValue + returnQty);
                    row.find('.new-qty-inp').val(qtyOnHand - originValue + returnQty);
                } 
            }
            Index.calcTotals();
        },

        calcTotals() {
            let total = 0;
            $('#productsTbl tbody tr').each(function() {
                const row = $(this);
                const qty = accounting.unformat(row.find('.return-qty').val());
                if (qty > 0) {
                    const cost = accounting.unformat(row.find('.cost').val());
                    total += qty * cost;
                }
            });
            $('#total').val(accounting.formatNumber(total));
        },
    };

    $(Index.init);
</script>
