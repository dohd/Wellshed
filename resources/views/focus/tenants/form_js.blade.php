{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">    
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        tinymce: {
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 300,
            license_key: 'gpl'
        },
        customerSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.tenants.customers') }}",
                dataType: 'json',
                delay: 250,
                method: 'POST',
                data: ({term}) => ({q: term}),
                processResults: data => {
                    return {
                        results: data.map(v => ({
                            id: v.id,
                            text: v.company, 
                            company: v.company,
                            country: v.country,
                            email: v.email,
                            phone: v.phone,
                            is_tax_exempt: v.is_tax_exempt,
                        })),
                    }
                },
            },
        },
    };

    const Form = {
        init() {
            tinymce.init(config.tinymce);
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#package').select2({allowClear: true});
            $('#sales_agent_id').select2({allowClear: true});
            $('#relationship_manager_id').select2({allowClear: true});
            $('#customer, #parentAccount').select2(config.customerSelect2);
            
            $('#customer').change(Form.customerChange);
            $('#package').change(Form.packageChange);
            $('#vatRate, #subscriptionRate, #subscr_term').change(Form.computeCosts);

            const client = @json(@$tenant->package->customer);
            if (client && client.id) {
                const div = $('#vat_rate').parents('div.col-4:first');
                if (client.is_tax_exempt == 1) {
                    $('#vat_rate').val('');  
                    div.addClass('d-none'); 
                } else {
                    div.removeClass('d-none');
                }
                $('#vat_rate').change();
            }

            const package = @json(@$tenant->package);
            if (package && package.id) {
                $('#package').change();
            }
        },

        packageChange() {
            const optionEl = $(this).find(':selected');
            const price = accounting.unformat(optionEl.attr('price'));
            $('#subscriptionRate').val(accounting.formatNumber(price)).change();
        },

        customerChange() {
            const data = $(this).select2('data')[0];
            if (data && data.id) {
                $('#cname').val(data.company);
                $('#country').val(data.country);
                $('#email').val(data.email);
                $('#phone').val(data.phone);
                if (+data.is_tax_exempt) $('#vat_rate').val('').parents('div.col-4').addClass('d-none');
                else $('#vat_rate').val('').parents('div.col-4').removeClass('d-none');
            } else {
                ['cname', 'country', 'email', 'phone'].forEach(v => $('#'+v).val(''));
            }
        },

        computeCosts() {
            const price = accounting.unformat($('#subscriptionRate').val());
            const vatRate = accounting.unformat($('#vatRate').val());
            const term = $('#subscr_term option:selected').val();
            const netCost = term * price;
            const totalCost = term * price * (1 + vatRate / 100);
            const vat = term * price * vatRate / 100;

            $('#net_cost').val(accounting.formatNumber(netCost));
            $('#vat').val(accounting.formatNumber(vat));
            $('#total_cost').val(accounting.formatNumber(totalCost));
            $('.net-cost').html(accounting.formatNumber(netCost));
            $('.vat').html(accounting.formatNumber(vat));
            $('.total-cost').html(accounting.formatNumber(totalCost));
        },
    };

    $(Form.init);

    function confirmDisableClientAccounts(url) {
        if (confirm('Are you sure you want to disable the client accounts? This will prevent all users under this tenant from logging in.')) {
            window.location.href = url;
        }
    }
    function confirmEnableClientAccounts(url) {
        if (confirm('Are you sure you want to enable the client accounts? This will enable all users under this tenant.')) {
            window.location.href = url;
        }
    }
</script> 