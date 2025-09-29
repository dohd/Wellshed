<script type="text/javascript">
    const config = {
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    }

    const Form = {
        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

            $('#employee').select2({allowClear: true});
            $('#casual').select2({allowClear: true});
            $('#third_party_user').select2({allowClear: true});
            $('#source-account, #dest-account, #amount').on('input', Form.computeAmount);
            $('#bank-rate').on('input', Form.inputBankRate);
            $('#source-account, #dest-account, #bank-rate, #amount').on('input', Form.inputReceiptAmount);
            $('#source-account').change(Form.changeSourceAccount);
            $('#dest-account').change(Form.changeDestAccount);
            $('#amount').focusout(Form.changeAmount);

            /** Edit Mode */
            const data = @json(@$banktransfer);
            if (data && data.id) {
                if (data.transaction_date) $('.datepicker').datepicker('setDate', new Date(data.transaction_date));
                else $('.datepicker').val('');

                $('#source-account, #dest-account').change();
                $('#amount').trigger('input');
                $('#bank-rate').val(data.bank_rate).trigger('input');
            }
            $('#user_type').change(Form.userTypeChange);
            $('#select_user').click(function(){
                if ($(this).is(':checked', true)) {
                    $('.div_users').removeClass('d-none');
                    $('#employee').attr('disabled',false);
                    $('#employee-inp').val('').attr('disabled',true);
                    $('#third_party_user').attr('disabled',false);
                    $('#third_party_user-inp').val('').attr('disabled',true);
                    $('#casual').attr('disabled',false);
                    $('#casual-inp').val('').attr('disabled',true);
                    $('#user_type').attr('required',true);
                    $('#user_type').change();
                } else {
                    $('.div_users').addClass('d-none');
                    $('#employee').attr('disabled',true);
                    $('#employee-inp').val('').attr('disabled',false);
                    $('#third_party_user').attr('disabled',true);
                    $('#third_party_user-inp').val('').attr('disabled',false);
                    $('#casual').attr('disabled',true);
                    $('#casual-inp').val('').attr('disabled',false);
                    $('#user_type').val('');
                    $('#user_type').attr('required',false);
                }
            });
        },

        changeAmount() {
            this.value = accounting.formatNumber(accounting.unformat($(this).val()));
        },

        // add currency code to label
        changeSourceAccount() {
            const currencyCode = $(this).find('option:selected').attr('currencyCode');
            if ($(this).val()) $('label[for="amount"]').html(`Transfer Amount (${currencyCode})`);
            else $('label[for="amount"]').html(`Transfer Amount`);
            const sourceText = $(this).find('option:selected').attr('systemCode')?.toLowerCase() || '';
            const destText = $('#dest-account option:selected').attr('systemCode')?.toLowerCase() || '';

            if (sourceText.includes('pool_petty') && !destText.includes('pool_petty')) {
                if (!$('#select_user').is(':checked')) {
                    $('#select_user').trigger('click');
                }
            } else if (!sourceText.includes('pool_petty') && !destText.includes('pool_petty')) {
                if ($('#select_user').is(':checked')) {
                    $('#select_user').trigger('click');
                }
            }
        },

        // add currency code to label
        changeDestAccount() {
            const currencyCode = $(this).find('option:selected').attr('currencyCode');
            if ($(this).val()) $('label[for="receipt-amount"]').html(`Receipt Amount (${currencyCode})`);
            else $('label[for="receipt-amount"]').html(`Receipt Amount`);
            const destText = $(this).find('option:selected').attr('systemCode')?.toLowerCase() || '';
            const sourceText = $('#source-account option:selected').attr('systemCode')?.toLowerCase() || '';

            if (destText.includes('pool_petty') && !sourceText.includes('pool_petty')) {
                if (!$('#select_user').is(':checked')) {
                    $('#select_user').trigger('click');
                }
            } else if (!destText.includes('pool_petty') && !sourceText.includes('pool_petty')) {
                if ($('#select_user').is(':checked')) {
                    $('#select_user').trigger('click');
                }
            }
        },

        // set foreign currency equivalent values and foreign gain/loss
        computeForeignAmount() {
            const sourceCurrencyRate = $('#source-account').find('option:selected').attr('currencyRate');
            const destCurrencyRate = $('#dest-account').find('option:selected').attr('currencyRate');
            if (sourceCurrencyRate && destCurrencyRate) {
                let sourceLocalAmount=0, destLocalAmount=0, diff;
                const amount = accounting.unformat($('#amount').val());
                const receiptAmount = accounting.unformat($('#rcpt-amount').val());

                if (sourceCurrencyRate == 1 || destCurrencyRate == 1) {
                    // const defaultCurrencyRate = accounting.unformat($('#default-rate').val());
                    const bankRate = accounting.unformat($('#bank-rate').val());
                    // sourceLocalAmount = amount * defaultCurrencyRate;
                    sourceLocalAmount = amount * bankRate;
                    destLocalAmount = amount * bankRate;
                } else {
                    sourceLocalAmount = amount * sourceCurrencyRate;
                    destLocalAmount = receiptAmount * destCurrencyRate;
                }        
                $('#source_amount_fx').val(accounting.formatNumber(sourceLocalAmount,4));
                $('#dest_amount_fx').val(accounting.formatNumber(destLocalAmount,4));

                diff = parseFloat(sourceLocalAmount-destLocalAmount).toFixed(4);
                if (Math.round(diff) > 0) {
                    $('#fx_gain_total').val(0);
                    $('#fx_loss_total').val(accounting.formatNumber(Math.abs(diff),4));
                } else if (Math.round(diff) < 0) {
                    $('#fx_gain_total').val(accounting.formatNumber(Math.abs(diff),4));
                    $('#fx_loss_total').val(0);
                }
            }
        },

        // set home currency amount
        inputReceiptAmount() {
            let homeAmount = accounting.unformat($('#rcpt-amount').val());
            const destCurrencyRate = $('#dest-account').find('option:selected').attr('currencyRate');
            if (destCurrencyRate != 1) homeAmount = homeAmount * destCurrencyRate;
            $('#home-amount').html(accounting.formatNumber(homeAmount));
        },

        // on change of bank-rate, recompute the receipt amount 
        inputBankRate() {
            const amount = accounting.unformat($('#amount').val());
            const destCurrencyCode = $('#dest-account').find('option:selected').attr('currencyCode');
            const srcCurrencyCode = $('#source-account').find('option:selected').attr('currencyCode');
            let receiptAmount = amount;
            let bankRate = accounting.unformat($('#bank-rate').val());
            
            if (destCurrencyCode != srcCurrencyCode) {
                const sourceCurrencyRate = $('#source-account').find('option:selected').attr('currencyRate');
                const destCurrencyRate = $('#dest-account').find('option:selected').attr('currencyRate');
                if (sourceCurrencyRate == 1) {
                    receiptAmount = amount * bankRate;
                } else if (destCurrencyRate == 1) {
                    receiptAmount = amount * bankRate;
                } else if (sourceCurrencyRate != destCurrencyRate) {
                    receiptAmount = amount * bankRate;
                }  
            } else {
                this.value = 1;
            }
            $('#rcpt-amount').val(accounting.formatNumber(receiptAmount,4));
            Form.computeForeignAmount();
        },

        // compute receipt amount
        computeAmount() {
            const amount = accounting.unformat($('#amount').val());
            const sourceCurrencyRate = $('#source-account').find('option:selected').attr('currencyRate');
            const destCurrencyRate = $('#dest-account').find('option:selected').attr('currencyRate');
            let receiptAmount = amount;
            let bankRate;
            
            if (sourceCurrencyRate == 1 && destCurrencyRate == 1) {
                bankRate = 1
            } else {
                if (sourceCurrencyRate == destCurrencyRate) {
                    bankRate = sourceCurrencyRate;
                    receiptAmount = amount;
                } else if (sourceCurrencyRate == 1) {
                    bankRate = 1/destCurrencyRate;
                    receiptAmount = amount * bankRate;
                } else if (destCurrencyRate == 1) {
                    bankRate = sourceCurrencyRate;
                    receiptAmount = amount * bankRate;
                } else {
                    bankRate = sourceCurrencyRate/destCurrencyRate;
                    receiptAmount = amount * bankRate;
                }
            }
            $('#default-rate').val(accounting.formatNumber(bankRate,4));
            $('#bank-rate').val(accounting.formatNumber(bankRate,4));
            $('#rcpt-amount').val(accounting.formatNumber(receiptAmount,4));
            Form.computeForeignAmount();
        },

        userTypeChange(){
            let user_type = $('#user_type').val();
            if(user_type == 'employee'){
                $('.div_casual').addClass('d-none');
                $('.div_employee').removeClass('d-none');
                $('.div_third_party_user').addClass('d-none');
                //disabled
                $('#employee').attr('disabled',false);
                $('#employee-inp').attr('disabled',true);
                $('#casual').attr('disabled',true);
                $('#casual-inp').val('').attr('disabled',false);
                $('#third_party_user').attr('disabled',true);
                $('#third_party_user-inp').val('').attr('disabled',false);
            }else if(user_type == 'casual')
            {
                $('.div_casual').removeClass('d-none');
                $('.div_employee').addClass('d-none');
                $('.div_third_party_user').addClass('d-none');
                //disabled
                $('#employee').attr('disabled',true);
                $('#employee-inp').val('').attr('disabled',false);
                $('#third_party_user').attr('disabled',true);
                $('#third_party_user-inp').val('').attr('disabled',false);
                $('#casual').attr('disabled',false);
                $('#casual-inp').attr('disabled',true);

            }else if(user_type == 'third_party_user')
            {
                $('.div_casual').addClass('d-none');
                $('.div_employee').addClass('d-none');
                $('.div_third_party_user').removeClass('d-none');
                //disabled
                $('#employee').attr('disabled',true);
                $('#employee-inp').val('').attr('disabled',false);
                $('#casual').attr('disabled',true);
                $('#casual-inp').val('').attr('disabled',false);
                $('#third_party_user').attr('disabled',false);
                $('#third_party_user-inp').attr('disabled',true);
            }
        },
    };

    $(Form.init);
</script>