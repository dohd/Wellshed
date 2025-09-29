{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        ajax: {headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        ledgerSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.journals.journal_accounts') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({term}),
                processResults: data => {
                    data = data.map(v => ({id: v.id, text: `${v.number} - ${v.holder}`, system: v.system || ''}));
                    return {results: data}; 
                },
            }
        },
        nameSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.journals.account_names') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({
                    term, 
                    account_id: $('#select-customer').val() || $('#select-supplier').val(),
                    is_customer: $('#select-customer').val(),
                    is_supplier: $('#select-supplier').val(),
                }),
                processResults: data => {
                    data = data.map(v => ({id: v.id, text: `${v.company} - ${v.name}`}));
                    return {results: data}; 
                },
            }
        },
        projectSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.journals.project_search') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({term}),
                processResults: data => {
                    data = data.map(v => ({id: v.id, text: `${v.tid} - ${v.name}`}));
                    return {results: data}; 
                },
            }
        },
    };


    // edit journal
    const journal = @json(@$journal);
    const journalItems = @json(@$journal->items);

    $.ajaxSetup(config.ajax);
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
    if (journal && journal.date) $('.datepicker').datepicker('setDate', new Date(journal.date));
    
    $('#journal').submit(function(e) {
        const debitTotal = accounting.unformat($('#debitTtl').val());
        const creditTotal = accounting.unformat($('#creditTtl').val());
        if (debitTotal != creditTotal) {
            e.preventDefault();
            return alert(`Total Debit ${accounting.formatNumber(debitTotal)} must be equal to Total Credit ${accounting.formatNumber(creditTotal)}`);
        }
        if (!debitTotal || !creditTotal) {
            e.preventDefault();
            return alert('Net difference between Debit and Credit must be greater than 0!');
        }
    });

    // on account change
    $('#ledgerTbl').on('change', '.account', function() {
        const row = $(this).parents('tr');
        const system = $(this).select2('data')[0].system;
        if (system) {
            row.find('.name').attr('disabled', false).val('').change();
            $('#select-customer').val('');
            $('#select-supplier').val('');
            if (system == 'receivable') $('#select-customer').val($(this).val());
            else if (system == 'payable') $('#select-supplier').val($(this).val());
            else row.find('.name').attr('disabled', true).val('').change();
        } else {
            row.find('.name').attr('disabled', true).val('').change();
        }
    });

    // on change debit or credit 
    $('#ledgerTbl').on('change', '.debit, .credit', function() {
        const row = $(this).parents('tr');
        row.find('.credit, .debit').attr('readonly', false);
        const value = accounting.unformat($(this).val());
        if (value) {
            if ($(this).is('.debit')) {
                row.find('.credit').val('').attr('readonly', true);
            }
            if ($(this).is('.credit')) {
                row.find('.debit').val('').attr('readonly', true);
            }
        } 
        $(this).val(accounting.format(value));
    });
    $('#ledgerTbl').on('keyup', '.debit, .credit', function() {
        if (+this.value < 0) {
            this.value = -this.value;
        }
        calcTotals();
    });

    // on change name
    $('#ledgerTbl').on('change', '.name', function() {
        const row = $(this).parents('tr');
        row.find('.customer-id').val('');
        row.find('.supplier-id').val('');
        if ($('#select-customer').val()) {
            row.find('.customer-id').val(this.value);
        }
        if ($('#select-supplier').val()) {
            row.find('.supplier-id').val(this.value);
        }
    });

    // remove button
    $('#ledgerTbl').on('click', '.remove', function() {
        const row = $(this).parents('tr');
        if ($('#ledgerTbl .remove').length == 2) {
            row.siblings().find('.remove').addClass('d-none');
        }
        row.remove();
        calcTotals();
    });

    // click add ledger button
    let rowId = 0;
    const rowHtml = $('#ledgerTbl tbody tr:first').html();
    $('#account-0').select2(config.ledgerSelect2);
    $('#name-0').select2(config.nameSelect2);
    $('#project-0').select2(config.projectSelect2);
    $('#add-row').click(function() {
        rowId++;
        const html = rowHtml.replace(/-0/g, '-'+rowId).replace(/d-none/, '');
        $('#ledgerTbl tbody').append('<tr>'+html+'</tr>');
        $('#account-'+rowId).select2(config.ledgerSelect2);
        $('#name-'+rowId).select2(config.nameSelect2);
        $('#project-'+rowId).select2(config.projectSelect2);
    });
    if (journalItems.length) {
        rowId = journalItems.length;
        $('#ledgerTbl tbody tr:first').remove();
        $('#ledgerTbl tbody tr').each(function(i) {
            if (i == 0) $(this).find('.remove').removeClass('d-none');
            const accountId = $(this).find('.account').attr('id');
            const name = $(this).find('.name').attr('id');
            const project = $(this).find('.project').attr('id');
            $('#'+accountId).select2(config.ledgerSelect2);
            $('#'+name).select2(config.nameSelect2);
            $('#'+project).select2(config.projectSelect2);
        });
        calcTotals();
    }

    // totals
    function calcTotals() {
        let debitTotal = 0;
        let creditTotal = 0;
        $('#ledgerTbl tbody tr').each(function() {
            const row = $(this);
            const credit = accounting.unformat(row.find('.credit').val());
            const debit = accounting.unformat(row.find('.debit').val());
            creditTotal += credit;
            debitTotal += debit;
        });
        $('#debitTtl').val(accounting.formatNumber(debitTotal));
        $('#creditTtl').val(accounting.formatNumber(creditTotal));
    }
</script>