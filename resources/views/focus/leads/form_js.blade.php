@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };


    $(document).ready(function() {
        $('#promoCheck').change(function() {
            if ($(this).is(':checked')) {
                $('#promoCodeDiv').slideDown(); // You can use slideDown() for a smooth animation
            } else {

                $('#reservation').val(''); // Use slideUp() to hide with animation
                $('#promoCodeDiv').slideUp(); // Use slideUp() to hide with animation
            }
        });


    });


    const redCodes = @json($redeemableCodes);

    function checkRedCode() {

        let promoCode = $('#reservation').val().toUpperCase().replace(/\s/g, '');
        $('#reservation').val(promoCode);

        if (promoCode === '') {
            $('#promoCodeFeedback').html('');
            return;
        }

        if (promoCode.length >= 4) {

            const found = redCodes.some(code => code === promoCode);

            $('#promoCodeFeedback').html(
                `<span style="color: ${found ? 'green' : 'red'};">
                    ${found ? 'Redeemable code is available!' : 'Redeemable code could not be found.'}
                 </span>`
            );
        }
    }

    $(document).ready(function () {

        checkRedCode(); // Run on page load if there's a pre-filled value
        // Bind both 'input' and 'change' events
        $('#reservation').on('input change', checkRedCode);
    });

    const Form = {
        lead: @json(@$lead),
        branches: @json($branches), 

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customer').select2({allowClear: true});
            $('#branch').select2({allowClear: true});
            $('#category').select2({allowClear: true});
            $('#classlist').select2({allowClear: true});

            $('.client-type').change(Form.clientTypeChange);
            $('#customer').change(Form.customerChange);
            $('#add-reminder').change(Form.reminderChange);
            $('#expectedIncome').change(Form.incomeAmountChange);
                
            // Edit Mode
            const lead = Form.lead;
            if (lead && lead.id) {
                $('#expectedIncome').change();
                $('#customer').val(lead.client_id).change();
                $('#branch').val(lead.branch_id);
                $('#date_of_request').datepicker('setDate', new Date(lead.date_of_request));
                $('#reminder_date').val(lead.reminder_date);
                $('#exact_date').val(lead.exact_date);
                ['reminder_date', 'exact_date'].forEach(v => $('#' + v).attr('disabled', false));
                if (lead.client_id == 0) {
                    ['payer-name', 'client_email', 'client_contact', 'client_address']
                    .forEach(v => $('#' + v).attr('readonly', false));
                    $('#colorCheck3').prop('checked', true);
                }
            } else {
                $('#customer').val('').change();
                $('#branch').val('').change();
            }

            // lead agent
            const agentLead = @json(@$agent_lead);
            if (agentLead && agentLead.id) {
                $('#colorCheck3').prop('checked', true).change();
                $('#payer-name').val(agentLead.client_name);
                $('#client_email').val(agentLead.email);
                $('#client_contact').val(agentLead.phone_no);
                $('input[name="title"]').val(agentLead.project);
                $('textarea[name="note"]').val(agentLead.project + ' || ' + agentLead.product_brand + ' || ' + agentLead.product_spec);
            }
        },

        incomeAmountChange() {
            const value = accounting.unformat(this.value);
            this.value = accounting.formatNumber(value);
        },

        clientTypeChange() {
            if ($(this).val() == 'new') {
                $('#customer').attr('disabled', true).val('').change();
                $('#branch').attr('disabled', true).val('');
                ['payer-name', 'client_email', 'client_contact', 'client_address'].forEach(v => {
                    $('#'+v).attr('readonly', false).val('');
                });
            } else {
                $('#customer').attr('disabled', false).val('');
                $('#branch').attr('disabled', false).val('');
                ['payer-name', 'client_email', 'client_contact', 'client_address'].forEach(v => {
                    $('#'+v).attr('readonly', true).val('');
                });
            }
        },

        reminderChange(){
            if ($(this).is(":checked")) {
                $('#exact_date').attr('disabled', false).val('');
                $('#reminder_date').attr('disabled', false).val('');
            }else{
                $('#exact_date').attr('disabled', true).val('');
                $('#reminder_date').attr('disabled', true).val('');
            }
        },

        customerChange() {
            $('#branch option').remove();
            if ($(this).val()) {
                const customerBranches = Form.branches.filter(v => v.customer_id == $(this).val());
                customerBranches.forEach(v => $('#branch').append(`<option value="${v.id}">${v.name}</option>`));
                $('#branch').attr('disabled', false).val('');
            } else {
                $('#branch').attr('disabled', true);
            }
        },
    };

    $(() => Form.init());


    $(() => {
        $('#source').trigger('change');
    });

    $(document).ready(function () {



        // Initialize Select2 for the reservation input
        const reservationSelect = $('#reservation').select2({
            placeholder: 'Select a reservation',
            allowClear: true,
        });

    });


</script>
@endsection