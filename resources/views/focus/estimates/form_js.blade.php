{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        quoteSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.estimates.quote_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({keyword: term, customer_id: $("#customer").val()}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        },
    };

    const Index = {
        initRow: '',

        init() {
            $('#productsTbl tbody td').css({paddingLeft: '5px', paddingRight: '5px'});
            $('.est-qty, .est-rate, .est-amount').css({height: '2.3em', paddingTop: 0, paddingBottom: 0});

            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customer').select2({allowClear: true});
            $('#quote').select2(config.quoteSelect);
            Index.initRow = $('#productsTbl tbody tr:first');

            $('#quote').change(Index.quoteChange);
            $('#productsTbl').on('keyup', '.est-qty, .est-amount', Index.qtyAmountKeyUp);
            $('#productsTbl').on('change', '.est-amount', Index.amountChange);
                
            const data = @json(@$estimate);
            const data_items = @json(@$estimate->items);
            if (data && data_items.length) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                if (!data['is_editable']) {
                    $('#customer, #quote').attr({disabled: true, required: false});
                    $('.est-qty, .est-amount').attr('readonly', true);
                }
                Index.calcTotals();
            }
        },

        quoteChange() {
            $('#total').val('');
            $('#est_total').val('');
            $('#balance').val('');
            $('#productsTbl tbody tr').remove();
            const quote_id = this.value;
            if (quote_id > 0) {
                $.post("{{ route('biller.estimates.verified_products') }}", {quote_id}, data => {
                    data.forEach((v,i) => {
                        const row = Index.initRow.clone();
                        row.removeClass('d-none');
                        const remQty = accounting.unformat(v.rem_qty);
                        const remAmount = accounting.unformat(v.rem_amount);
                        const qty = accounting.unformat(v.product_qty);
                        const tax = accounting.unformat(v.product_tax);
                        let rate = accounting.unformat(v.product_price);
                        rate -= tax;
                        const amount = qty * rate;
                        
                        row.find('.num').text(v.numbering);
                        row.find('.name').text(v.product_name);
                        row.find('.unit').text(v.unit);
                        row.find('.rate').text(accounting.formatNumber(rate));
                        if (remAmount !== 0) {
                            row.find('.qty').text(remQty);
                            row.find('.amount').text(accounting.formatNumber(remAmount));
                            row.find('.qty-inp').val(remQty);
                            row.find('.amount-inp').val(accounting.formatNumber(remAmount));
                        } else {
                            row.find('.qty').text(qty);
                            row.find('.amount').text(accounting.formatNumber(amount));
                            row.find('.qty-inp').val(qty);
                            row.find('.amount-inp').val(accounting.formatNumber(amount));
                        }
                        row.find('.tax').text(tax);
                        row.find('.est-rate').val(accounting.formatNumber(rate));
                        row.find('.indx').val(i);
                        row.find('.vrfitem-id').val(v.id); 
                        row.find('.prodvar-id').val(accounting.unformat(v.product_id) || ''); 
                        row.find('.tax').val(tax);
                        row.find('.rate-inp').val(accounting.formatNumber(rate));
                        
                        $('#productsTbl tbody').append(row);
                        Index.calcTotals();
                    });
                });
            }
        },

        qtyAmountKeyUp() {
            const row = $(this).parents('tr');
            const qty = accounting.unformat(row.find('.qty').text());
            const amount = accounting.unformat(row.find('.amount').text());
            const estAmount = accounting.unformat(row.find('.est-amount').val());
            const estQty = accounting.unformat(row.find('.est-qty').val());
            let estRate = 0;
            if (estQty == 0) estRate = estAmount;
            else estRate = accounting.unformat(estAmount / estQty);

            const remQty = qty - estQty;
            const remAmount = amount - estAmount;
            
            row.find('.rem-qty').val(accounting.formatNumber(remQty));
            row.find('.rem-amount').val(accounting.formatNumber(remAmount));
            row.find('.est-rate').val(accounting.formatNumber(estRate));
            if ($(this).is('.est-amount')) {
                if (estQty == 0) row.find('.est-qty').val(1);
                if (estAmount > amount) row.find('.est-amount').val(accounting.formatNumber(amount));
            } 
            Index.calcTotals();
        },  

        amountChange() {
            $(this).val(accounting.formatNumber(accounting.unformat($(this).val())));
        },  

        calcTotals() {
            let total = 0;
            let estTotal = 0;
            let balance = 0;
            $('#productsTbl tbody tr').each(function() {
                const amount = accounting.unformat($(this).find('.amount').text());
                const estAmount = accounting.unformat($(this).find('.est-amount').val());
                if (estAmount !== 0) {
                    total += amount;
                    estTotal += estAmount;
                    balance += (amount-estAmount)
                }
            });
            $('#total').val(accounting.formatNumber(total));
            $('#est-total').val(accounting.formatNumber(estTotal));
            $('#balance').val(accounting.formatNumber(balance));
        },
    };

    $(Index.init);
</script>
