{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        monthDate: {
            autoHide: true,
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            format: 'MM-yyyy',
            onClose: function(dateText, inst) { 
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
        },
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        initRow: '',
        reconciliation: @json(@$reconciliation),

        init() {
            $.ajaxSetup(config.ajax);
            $('#reconciled_on').datepicker(config.date).datepicker('setDate', new Date());
            $('#ending_period').datepicker(config.date);
            $('#end_date').datepicker(config.monthDate).datepicker('setDate', "{{ date('m-Y') }}");
            Index.initRow = $('#transactions tbody tr:first');

            $('#recon-form').submit(Index.onFormSubmit);
            $('#finish-btn').click(function() { window.submitButtonId = $(this).attr('id') });

            $('#end_balance').keyup(Index.onEndBalKeyUp);
            $('#end_balance').change(Index.onEndBalChange);
            $('#check-all').change(Index.onCheckAllChange);
            $('#account, #end_date').on('change', Index.onAccountChange);
            $('#transactions').on('change', '.check', Index.onCheckBoxChange);
            $('#reconciled_on, #ending_period').on('change', Index.computeAccountBalance);
            
            /** Edit Mode */
            const data = @json(@$reconciliation);
            if (data && data.id) {
                if (+data.begin_balance) {
                    $('#begin_balance').val(accounting.formatNumber(+data.begin_balance));
                    $('.begin-bal').text(accounting.formatNumber(+data.begin_balance));
                }
                $('#account').attr('disabled', true);
                if (data.reconciled_on) $('#reconciled_on').datepicker('setDate', new Date(data.reconciled_on));
                if (data.ending_period) $('#ending_period').datepicker('setDate', new Date(data.ending_period));
                $('#end_date').val(data.end_date).attr('disabled', true);                
                $('#end_balance').keyup().change();
                $('#check-all').prop('checked', true).change();
                Index.fetchUnclearedRecords();                
            }
        },

        async onFormSubmit(e) {
            const balanceDiff = accounting.unformat($('#balance_diff').val());
            const msg = 'Balance Difference is Not Zero! Your Transactions Do Not Match Your Statement. Are you sure to proceed?';
            if (balanceDiff != 0 && !confirm(msg)) e.preventDefault();

            // on finish reconciliation
            if (window.submitButtonId == 'finish-btn' && balanceDiff == 0 && !$('#is_done').val()) {
                e.preventDefault();
                await Index.confirmAction(e);
                if ($('#is_done').val() == 1) $('#finish-btn').click();
            }
        },
        async confirmAction(e) {
            const isOk = await swal({
                title: 'Are You Sure?',
                text: "Once applied, you will not be able to undo!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            });
            if (isOk) $('#is_done').val(1);
            else e.preventDefault();
        },

        onAccountChange() {
            $('#transactions tbody tr').remove();
            $('#begin_balance').val('0.00');
            $('.begin-bal').text('0.00');
            if (!this.value) return;

            // fetch current uncleared items
            Index.fetchCurrentRecords();
            // fetch previous uncleared items
            Index.fetchPrevUnclearedRecords();
        },

        fetchCurrentRecords() {
            const url = "{{ route('biller.reconciliations.account_items') }}";
            const params = {account_id: $('#account').val(), end_date: $('#end_date').val(), is_create: 1};
            $.post(url, params)
            .done(data => {
                if (!data.length) return;
                data.forEach((v,i) => {
                    if(i == 0) {
                        $('#begin_balance').val(accounting.formatNumber(+v.begin_balance));
                        $('.begin-bal').text(accounting.formatNumber(+v.begin_balance));
                    }
                    const row = Index.initRow.clone();
                    row.removeClass('d-none');
                    row.find('.journalitem-id').val(v.journal_item_id);
                    row.find('.journal-id').val(v.man_journal_id);
                    row.find('.pmt-id').val(v.payment_id);
                    row.find('.dep-id').val(v.deposit_id);
                    row.find('.charge-id').val(v.charge_id);
                    row.find('.bankxfer-id').val(v.bank_transfer_id);
                    row.find('.cnote-id').val(v.creditnote_id);
                    row.find('.date').text(v.date? v.date.split('-').reverse().join('-') : '');
                    row.find('.type').text(v.type);
                    row.find('.trans-ref').text(v.trans_ref);
                    row.find('.client-supplier').text(v.client_supplier);
                    row.find('.note').text(v.note);
                    row.find('.cash').text(accounting.formatNumber(+v.amount));
                    if (v.payment_id) row.find('.credit').text(accounting.formatNumber(+v.amount));
                    if (v.deposit_id) row.find('.debit').text(accounting.formatNumber(+v.amount));
                    if (v.bank_transfer_id || v.charge_id || v.journal_item_id || v.creditnote_id) {
                        if (v.type == 'cash-out') row.find('.credit').text(accounting.formatNumber(+v.amount));
                        else row.find('.debit').text(accounting.formatNumber(+v.amount));
                    }
                    $('#transactions tbody').append(row);
                });
            })
            .fail((xhr, status, error) => console.log(error));
        },

        fetchUnclearedRecords() {
            // Cleared record ids
            let bankTransferIds, chargeIds, depositIds, journalItemIds, paymentIds, creditNoteIds;
            @if (@$reconciliation) 
                bankTransferIds = @json($reconciliation->items->pluck('bank_transfer_id')->filter()->implode(','));
                chargeIds = @json($reconciliation->items->pluck('charge_id')->filter()->implode(','));
                depositIds = @json($reconciliation->items->pluck('deposit_id')->filter()->implode(','));
                journalItemIds = @json($reconciliation->items->pluck('journal_item_id')->filter()->implode(','));
                paymentIds = @json($reconciliation->items->pluck('payment_id')->filter()->implode(','));
                creditNoteIds = @json($reconciliation->items->pluck('creditnote_id')->filter()->implode(','));                
            @endif

            const url = "{{ route('biller.reconciliations.account_items') }}";
            const params = {
                account_id: $('#account').val(), 
                end_date: $('#end_date').val(),
                bank_transfer_ids: bankTransferIds,
                charge_ids: chargeIds,
                deposit_ids: depositIds,
                journal_item_ids: journalItemIds,
                payment_ids: paymentIds,
                creditnote_ids: creditNoteIds,
            };
            $.post(url, params)
            .done(data => {
                if (!data.length) return;
                data.forEach((v,i) => {
                    const amount = accounting.unformat(v.amount);
                    const row = Index.initRow.clone();
                    row.removeClass('d-none');
                    row.find('.debit, .credit').html('');
                    row.find('.journalitem-id').val(v.journal_item_id);
                    row.find('.journal-id').val(v.man_journal_id);
                    row.find('.pmt-id').val(v.payment_id);
                    row.find('.dep-id').val(v.deposit_id);
                    row.find('.charge-id').val(v.charge_id);
                    row.find('.bankxfer-id').val(v.bank_transfer_id);
                    row.find('.cnote-id').val(v.creditnote_id);
                    row.find('.date').text(v.date? v.date.split('-').reverse().join('-') : '');
                    row.find('.type').text(v.type);
                    row.find('.trans-ref').text(v.trans_ref);
                    row.find('.client-supplier').text(v.client_supplier);
                    row.find('.note').text(v.note);
                    row.find('.cash').text(accounting.formatNumber(amount));
                    if (v.payment_id) row.find('.credit').html(accounting.formatNumber(amount));
                    if (v.deposit_id) row.find('.debit').html(accounting.formatNumber(amount));
                    if (v.bank_transfer_id || v.charge_id || v.journal_item_id || v.creditnote_id) {
                        if (v.type == 'cash-out') row.find('.credit').html(accounting.formatNumber(amount));
                        else if (v.type == 'cash-in') row.find('.debit').html(accounting.formatNumber(amount));
                    }
                    row.find('.check').prop('checked', false);
                    row.find('.check-inp').val('');
                    $('#transactions tbody').append(row);
                });
                // compute balance
                Index.computeUnclearedBalance();
            })
            .fail((xhr, status, error) => console.log(error));
        },

        fetchPrevUnclearedRecords() {
            // const isEdit = Index.reconciliation?.id;
            // const today = "{{ date('Y-m-d') }}";
            $.post("{{ route('biller.reconciliations.prev_uncleared_account_items') }}", {
                account_id: $('#account').val(), 
                // end_date: isEdit? today : $('#end_date').val(),
                end_date: $('#end_date').val(),
            })
            .done(data => {
                if (!data.length) return;
                data.forEach((v,i) => {
                    const amount = accounting.unformat(v.amount);
                    const row = Index.initRow.clone();
                    row.removeClass('d-none');
                    row.find('.debit, .credit').html('');
                    row.find('.journalitem-id').val(v.journal_item_id);
                    row.find('.journal-id').val(v.man_journal_id);
                    row.find('.pmt-id').val(v.payment_id);
                    row.find('.dep-id').val(v.deposit_id);
                    row.find('.charge-id').val(v.charge_id);
                    row.find('.bankxfer-id').val(v.bank_transfer_id);
                    row.find('.cnote-id').val(v.creditnote_id);
                    row.find('.date').text(v.date? v.date.split('-').reverse().join('-') : '');
                    row.find('.type').text(v.type);
                    row.find('.trans-ref').text(v.trans_ref);
                    row.find('.client-supplier').text(v.client_supplier);
                    row.find('.note').text(v.note);
                    row.find('.cash').text(accounting.formatNumber(amount));
                    if (v.payment_id) row.find('.credit').html(accounting.formatNumber(amount));
                    if (v.deposit_id) row.find('.debit').html(accounting.formatNumber(amount));
                    if (v.bank_transfer_id || v.charge_id || v.journal_item_id || v.creditnote_id) {
                        if (v.type == 'cash-out') row.find('.credit').html(accounting.formatNumber(amount));
                        else if (v.type == 'cash-in') row.find('.debit').html(accounting.formatNumber(amount));
                    }
                    row.find('.check').prop('checked', false);
                    row.find('.check-inp').val('');
                    $('#transactions tbody').append(row);
                });
                // compute balance
                Index.computeUnclearedBalance();
            })
            .fail((xhr, status, error) => console.log(error));
        },

        onEndBalChange() {
            const value = accounting.unformat(this.value);
            $(this).val(accounting.formatNumber(value));
        },

        onEndBalKeyUp() {
            const endBal = accounting.unformat(this.value);
            const clearedBal = accounting.unformat($('.cleared-bal').html());
            const balanceDiff = endBal - clearedBal;

            $('.endin-bal').text(accounting.formatNumber(endBal));
            $('.bal-diff').text(accounting.formatNumber(balanceDiff));
            $('#balance_diff').val(accounting.formatNumber(balanceDiff));
        },

        onCheckBoxChange() {
            const row = $(this).parents('tr');
            const type = row.find('.type').html();
            const endBal = accounting.unformat($('.endin-bal').html());
            const beginBal = accounting.unformat($('.begin-bal').html());
            const cash = accounting.unformat(row.find('.cash').html());
            let cashIn = accounting.unformat($('.cash-in').html());
            let cashOut = accounting.unformat($('.cash-out').html());
            if (type == 'cash-in') {
                if ($(this).is(':checked')) cashIn += cash;
                else cashIn -= cash;
            }
            if (type == 'cash-out') {
                if ($(this).is(':checked')) cashOut += cash;
                else cashOut -= cash;
            }

            if ($(this).is(':checked')) row.find('.check-inp').val(1);
            else row.find('.check-inp').val('');

            $('.cash-in').text(accounting.formatNumber(cashIn));
            $('.cash-out').text(accounting.formatNumber(cashOut));
            $('#cash_in').val(accounting.formatNumber(cashIn));
            $('#cash_out').val(accounting.formatNumber(cashOut));

            const clearedBal = beginBal - cashOut + cashIn
            $('.cleared-bal').text(accounting.formatNumber(clearedBal));
            $('#cleared_balance').val(accounting.formatNumber(clearedBal));

            const balDiff = endBal - clearedBal;
            $('.bal-diff').text(accounting.formatNumber(balDiff));
            $('#balance_diff').val(accounting.formatNumber(balDiff));

            // compute balances
            Index.computeUnclearedBalance();
            Index.computeUnclearedBalanceAfterEP();
        },

        checkAllCount: 0,
        onCheckAllChange() {
            let cashIn = 0, cashOut = 0;
            Index.checkAllCount++;
            if ($(this).is(':checked')) {
                $('#transactions tbody tr').each(function() {
                    const row = $(this);
                    const data = @json(@$reconciliation);
                    if (data && data.id && Index.checkAllCount == 1) {
                        if (row.find('.check-inp').val() == 1) row.find('.check').prop('checked', true);
                        else row.find('.check').prop('checked', false);
                    } else {
                        row.find('.check').prop('checked', true);
                        row.find('.check-inp').val(1);
                    }

                    if (row.find('.check').prop('checked')) {
                        const cash = accounting.unformat(row.find('.cash').html());
                        const type = row.find('.type').html();
                        if (type == 'cash-in') cashIn += cash;
                        if (type == 'cash-out') cashOut += cash;
                    }
                });
            } else {
                $('#transactions tbody tr').each(function() {
                    const row = $(this);
                    row.find('.check').prop('checked', false);
                    row.find('.check-inp').val('');
                });
            }
            $('.cash-in').text(accounting.formatNumber(cashIn));
            $('.cash-out').text(accounting.formatNumber(cashOut));
            $('#cash_in').val(accounting.formatNumber(cashIn));
            $('#cash_out').val(accounting.formatNumber(cashOut));

            const endBal = accounting.unformat($('.endin-bal').html());
            const beginBal = accounting.unformat($('.begin-bal').html());

            const clearedBal = beginBal - cashOut + cashIn;
            $('.cleared-bal').text(accounting.formatNumber(clearedBal));
            $('#cleared_balance').val(accounting.formatNumber(clearedBal));

            const balDiff = endBal - clearedBal;
            $('.bal-diff').text(accounting.formatNumber(balDiff));
            $('#balance_diff').val(accounting.formatNumber(balDiff));

            // compute balances
            Index.computeUnclearedBalance();
            Index.computeUnclearedBalanceAfterEP();
        }, 

        computeUnclearedBalance() {
            let cashIn = 0, cashOut = 0, totalDebits = 0, totalCredits = 0;
            $('#transactions tbody tr').each(function() {
                const row = $(this);
                const cash = accounting.unformat(row.find('.cash').html());
                const type = row.find('.type').html();
                if (type == 'cash-in') totalDebits += cash;
                if (type == 'cash-out') totalCredits += cash;
                // unchecked transactions
                if (!row.find('.check').prop('checked')) {
                    if (type == 'cash-in') cashIn += cash;
                    if (type == 'cash-out') cashOut += cash;
                } 
            });
            const unclearedBalance = cashIn-cashOut;
            $('.uncleared-bal').html(accounting.formatNumber(unclearedBalance));
            $('#ep_uncleared_balance').val(accounting.formatNumber(unclearedBalance));
            $('.dtotal').html(accounting.formatNumber(totalDebits));
            $('.ctotal').html(accounting.formatNumber(totalCredits));
        },
        computeUnclearedBalanceAfterEP() {
            let cashIn = 0, cashOut = 0;
            const endingPeriod = $('#ending_period').val();
            $('#transactions tbody tr').each(function() {
                const row = $(this);
                const itemDate = row.find('.date').html();
                if (endingPeriod && itemDate && !row.find('.check').prop('checked')) {
                    if ((new Date(itemDate)) > (new Date(endingPeriod))) {
                        const cash = accounting.unformat(row.find('.cash').html());
                        const type = row.find('.type').html();
                        if (type == 'cash-in') cashIn += cash;
                        if (type == 'cash-out') cashOut += cash;
                    }
                }
            });
            const unclearedBalanceAfterEP = cashIn-cashOut;
            $('.uncleared-bal-after-ep').html(accounting.formatNumber(unclearedBalanceAfterEP));
            $('#uncleared_balance_after_ep').val(accounting.formatNumber(unclearedBalanceAfterEP));
        },
        computeAccountBalance() {
            const url = "{{ route('biller.reconciliations.account_balance') }}";
            $.post(url, {
                account_id: $('#account').val(),
                reconciled_on: $('#reconciled_on').val(), 
                ending_period: $('#ending_period').val(),
            })
            .then(data => {
                $('.ep-account-bal').html(accounting.formatNumber(data.ending_period || ''));
                $('.recon-on-bal').html(accounting.formatNumber(data.reconciled_on || ''));
                $('#ep_account_balance').val(accounting.formatNumber(data.ending_period || ''));
                $('#ro_account_balance').val(accounting.formatNumber(data.reconciled_on || ''));
            })
            .fail((xhr, status, error) => console.log(error));
        },
    }

    $(Index.init);
</script>