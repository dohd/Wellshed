{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        autoCompleteCb: () => {
            return {
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('biller.products.quote_product_search') }}",
                        data: {keyword: request.term, is_stock_adj: 1},
                        method: 'POST',
                        success: result => response(result.map(v => ({
                            label: `${v.name} (${v.warehouse? v.warehouse.title : ''})`,
                            value: v.name,
                            data: v
                        }))),
                    });
                },
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    const {data} = ui.item;
                    let row = Index.currRow;
                    row.find('.prodvar-id').val(data.id); 
                    row.find('.qty-onhand').text(accounting.unformat(data.qty));
                    row.find('.qty-onhand-inp').val(accounting.unformat(data.qty));
                    row.find('.cost').val(accounting.unformat(data.purchase_price));
                    if (data.units && data.units.length) {
                        const unit = data.units[0];
                        row.find('.unit').text(unit.code);
                    }
                }
            };
        }
    };

    const Index = {
        currRow: '',
        init() {
            $('#productsTbl tbody td').css({paddingLeft: '5px', paddingRight: '5px', paddingBottom: 0});
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#account').select2({allowClear: true});
            $('#name-' + 1).autocomplete(config.autoCompleteCb());

            $('#adj_type').change(Index.adjTypeChange).change();
            $('#add-item').click(Index.addItemClick);
            $('#account').change(Index.accountChange);
            $('#productsTbl').on('keyup', '.new-qty, .qty-diff, .cost', Index.qtyCostKeyUp);
            $('#productsTbl').on('keyup', '.name', function() { Index.currRow = $(this).parents('tr') });
            $('#productsTbl').on('click', '.remove', Index.removeRowClick);
                
            const data = @json(@$stock_adj);
            const data_items = @json(@$stock_adj->items);
            if (data && data.id) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                $('#adj_type').val(data.adj_type);
                $('#account').val(data.account_id);
                $('#productsTbl tbody tr').each(function(i) {
                    const row = $(this);
                    const v = data_items[i];
                    if (i > 0) row.find('.name').autocomplete(config.autoCompleteCb());
                    row.find('.qty-onhand-inp').val(v.qty_onhand*1);
                    row.find('.prodvar-id').val(v.productvar_id);
                });
                Index.calcTotals();
            }
        },

        addItemClick() {
            let row = $('#productsTbl tbody tr:last').clone();
            let indx = accounting.unformat(row.find('.name').attr('id').split('-')[1]);
            row.find('input').attr('value', '');
            row.find('textarea').text('');
            row.find('.unit, .qty-onhand').text('');
            row.find('.name').attr('id', `name-${indx+1}`);
            $('#productsTbl tbody').append(`<tr>${row.html()}</tr>`);
            $(`#name-${indx+1}`).autocomplete(config.autoCompleteCb());
        },

        removeRowClick() {
            let row = $(this).parents('tr');
            if (row.siblings().length) {
                row.remove();
            } else {
                row.find('input, textarea').each(function() { $(this).val(''); });
                row.find('.unit, .qty-onhand').text('');            
            }
            Index.calcTotals();
        },

        adjTypeChange() {
            ['.h-cost', '.h-qty'].forEach(v => $(v).removeClass('d-none'));
            ['.cost', '.new-qty','.qty-diff'].forEach(v => $(v).parents('td').removeClass('d-none'));
            if (this.value == 'Qty') {
                $('.h-cost').addClass('d-none');
                $('.cost').parents('td').addClass('d-none');
            } else if (this.value == 'Cost') {
                $('.h-qty').addClass('d-none');
                ['.new-qty','.qty-diff'].forEach(v => $(v).parents('td').addClass('d-none'));
            } 
            window.scrollTo(0, document.body.scrollHeight);
            window.scrollTo(document.body.scrollHeight, 0);
        },

        accountChange() {
            $('#productsTbl').find('.new-qty, .qty-diff, .amount').val('');
            $('#total').val('');
        },

        qtyCostKeyUp() {
            const row = $(this).parents('tr');
            const qtyOnhand = accounting.unformat(row.find('.qty-onhand').text());
            const cost = accounting.unformat(row.find('.cost').val());
            let qtyDiff = accounting.unformat(row.find('.qty-diff').val());
            let newQty = accounting.unformat(row.find('.new-qty').val());
            let amount = 0;
            const accountType = $('#account option:selected').attr('account_type');
            if ($(this).is('.qty-diff')) {
                let newQty = qtyOnhand + qtyDiff;
                if ((qtyDiff < 0 && accountType == 'Income') || (qtyDiff > 0 && accountType == 'Expense')) {
                    qtyDiff = 0;
                }
                if (qtyDiff == 0) amount = 0;
                else amount = qtyDiff * cost;
                row.find('.new-qty').val(accounting.formatNumber(newQty));
            }
            if ($(this).is('.new-qty')) {
                let qtyDiff = newQty - qtyOnhand;
                if ((qtyDiff < 0 && accountType == 'Income') || (qtyDiff > 0 && accountType == 'Expense')) {
                    qtyDiff = 0;
                }
                if (qtyDiff == 0) amount = 0;
                else amount = qtyDiff * cost;
                row.find('.qty-diff').val(qtyDiff);
            }
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
