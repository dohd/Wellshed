{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        currRow: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#receiver').select2({allowClear: true});
            
            $('#productsTbl').on('change', '.qty-rcv', Index.qtyKeyUp);

            const data = @json(@$stock_rcv);
            const data_items = @json(@$stock_rcv->items);
            if (data && data_items.length) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                Index.calcTotals();
            }
        },

        qtyKeyUp() {
            const row = $(this).parents('tr');
            const cost = accounting.unformat(row.find('.cost').val());
            const qtyTransf = accounting.unformat(row.find('.qty-transf').text());
            const qtyAccum = accounting.unformat(row.find('.qty-accum').val());
            let qtyRcv = accounting.unformat(row.find('.qty-rcv').val());
            if (qtyRcv < 0) {
                qtyRcv = 0;
                row.find('.qty-rcv').val(qtyRcv);
            } else if (qtyRcv >= qtyTransf) {
                if (qtyTransf == qtyAccum) qtyRcv = qtyTransf;
                else qtyRcv = qtyTransf - qtyAccum;
                row.find('.qty-rcv').val(qtyRcv);
            } else {
                if (qtyRcv > (qtyTransf - qtyAccum)) qtyRcv = qtyTransf - qtyAccum;
                row.find('.qty-rcv').val(qtyRcv);
            }

            let qtyRem = 0;
            if (qtyTransf == qtyAccum) qtyRem = qtyAccum - qtyRcv;
            else qtyRem = qtyTransf - (qtyAccum + qtyRcv);
            row.find('.qty-rem').text(qtyRem);
            row.find('.qty-rem-inp').val(qtyRem);

            const amount = qtyRcv * cost;
            row.find('.amount').val(accounting.formatNumber(amount));
            Index.calcTotals();
        },  

        calcTotals() {
            let total = 0;
            $('#productsTbl tbody tr').each(function() {
                const amount = accounting.unformat($(this).find('.amount').val());
                total += amount;
            });
            $('#total').val(accounting.formatNumber(total));
        },
    };

    $(Index.init);
</script>
