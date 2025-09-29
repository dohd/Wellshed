{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        parentAccountSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.accounts.search_parent_account') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({term, account_type: $('#account-type').val()}),
                processResults: data => {
                    return {results: data.map(v => ({text: v.holder, id: v.id})) }
                },
            }
        },
        detailTypeSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.accounts.search_detail_type') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({term, account_type_id: $('#account-type option:selected').attr('key')}),
                processResults: data => {
                    return {results: data.map(v => ({text: v.name, id: v.id, description: v.description}))}
                },
            }
        },
    };

    const Form = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#account-type').select2({allowClear: true});
            $('#parent_id').select2(config.parentAccountSelect2);
            $('#detail-type').select2(config.detailTypeSelect2);

            $('#detail-type').change(Form.changeDetailType);
            $('#account-type').change(Form.changeAccountType);
            $('#is_sub_account').change(Form.changeIsSubaccount);
            $('#is_manual_journal').change(Form.changeIsManualJournal);
            $("#opening-balance").change(function() { 
                $(this).val(accounting.formatNumber(accounting.unformat(this.value)));
            });

            /** Editing Account */
            const account = @json(@$account);
            if (account && account.id) {
                const OBDate = account.opening_balance_date;
                const typeDetail = account.account_type_detail;
                if (typeDetail && typeDetail.id) {
                    $('.detail-type-descr').html(`<b>${typeDetail.name || ''}:</b> ${typeDetail.description || ''}`);
                }
                if (account.is_sub_account) {
                    $('#parent_id').attr('disabled', false);
                    $('#is_sub_account').prop('checked', true);
                }
                if (account.is_manual_journal) $('#is_manual_journal').prop('checked', true);
                if (!account.parent_id && !account.is_sub_account) {
                    // $('#detail-type, #currency, #is_sub_account, #is_manual_journal, #opening-balance, #date').prop('disabled', true);
                }
                
                $('#date').val('');
                if (account.system == 'stock') $('#date').parents('.form-group.row').addClass('d-none');
                else if (OBDate) $('#date').datepicker('setDate', new Date(OBDate));
            }
        },

        changeIsSubaccount() {
            $('#parent_id').val('').change();
            $('#parent_id option:not(:first)').remove();
            if ($(this).prop('checked')) {
                $('#sub_account').val(1);
                $('#parent_id').prop('disabled', false);
            } else {
                $('#sub_account').val(0);
                $('#parent_id').prop('disabled', true);
            }
        },

        changeIsManualJournal() {
            if ($(this).prop('checked')) $('#manual_journal').val(1);
            else $('#manual_journal').val(0);
        },

        changeAccountType() {
            $('#detail-type').val('').change();
            $('#detail-type option:not(:first)').remove();
            $('.detail-type-descr').html('');

            const option = $(this).find('option:selected');
            const accountTypeId = option.attr('key');
            $('#account-type-id').val(accountTypeId);
            $('#is-multiple').val(option.attr('is-multiple'));
            
            $.post("{{ route('biller.accounts.search_next_account_no') }}", {account_type: $(this).val()})
            .done(data => $('#account_number').val(data.account_number || ''))
            .fail((xhr, status, error) => console.log(error));
        },

        changeDetailType() {
            if (this.value) {
                const data = $(this).select2('data')[0];
                $('.detail-type-descr').html(`<b>${data.text || ''}:</b> ${data.description || ''}`);
            }
        },
    }

    $(Form.init);
</script>
