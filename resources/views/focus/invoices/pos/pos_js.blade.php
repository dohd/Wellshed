<script type="text/javascript">
    const posConfig = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {
            autoHide: true,
            format: "{{ config('core.user_date_format') }}"
        }
    };

    $.ajaxSetup(posConfig.ajax);
    $('[data-toggle="datepicker"]').datepicker(posConfig.date);
    $('[data-toggle="datepicker"]').datepicker('setDate', new Date());

    $('form input').keydown(function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
    $('#keyword').keyup(delay(function (e) {
        if (this.value.length > 2) load_pos($(this).val());
    }, 500));

    $(document).on('click', '.payment_row_add', function (e) {
        $("#amount_row").append($("#payment_row").clone());
        calcPaymentTotals();
    });

    $(document).on('change', '#s_warehouses, #s_category', function (e) {
        load_pos();
    });

    // on change claim tax
    $('#is_claim').change(function() {
        if ($(this).val() == 'yes') {
            $('.tax-pin-col, .company-col').removeClass('d-none');
            $('#tax_pin, #claimer_company').attr('required', true);
        } else {
            $('.tax-pin-col, .company-col').addClass('d-none');
            $('#tax_pin, #claimer_company').attr('required', false);
        }
    });

    /**
     * Payment Modal Shown
    */
    $("#pos_payment").on("show.bs.modal", function () {
        $('#is_pay').val(1);
        $('.p_amount:first').val($('#mahayog').text());
        calcPaymentTotals();

        // on click pay later
        $('#pos_future_pay').click(function() {
            $('#is_pay').val('');
            $('#pos_basic_pay').click();
        });
    });

    function update_pay_pos() {
        calcPaymentTotals();
    }
    
    // compute payment totals
    function calcPaymentTotals() {
        let sumRowAmount = 0;
        $('.p_amount').each(function() {
            sumRowAmount += accounting.unformat(this.value);
        });

        const orderPanelTotal = accounting.unformat($('#mahayog').text());
        let dueAmount = orderPanelTotal - sumRowAmount;
        if (dueAmount < 0) {
            dueAmount *= -1;
            $('#balance1').val(0);
            $('#change_p').val(accounting.formatNumber(dueAmount));
        } else {
            $('#balance1').val(accounting.formatNumber(dueAmount));
            $('#change_p').val(0);
        }
    }

    // After form submit callback 
    function trigger(data) {
        // print receipt ajax call
        $.ajax({
            url: "{{ route('biller.pos.browser_print') }}",
            dataType: "html",
            method: 'get',
            data: {invoice_id: data.invoice.id},
            success: res => {
                // toggle receipt modal
                $('#print_section').html(res);
                $('#pos_print').modal('toggle');
                $("#print_section").printThis({
                    // beforePrint: function (e) {$('#pos_print').modal('hide');},
                    printDelay: 500,
                    afterPrint: null
                });
            }
        });
    }

    @php
        $pmt= payment_methods();
        array_push($pmt, "Change");
    @endphp
    function loadRegister(show = true) {
        $.ajax({
            url: '{{route('biller.register.load')}}',
            dataType: "json",
            method: 'get',
            success: function (data) {
                $('#register_items').html('@foreach($pmt as $row)<div class="col-6"><div class="form-group  text-bold-600 green"><label for="' + data.pm_{{$loop->iteration}}+ '">{{$row}}</label><input type="text" class="form-control green" id="' + data.pm_{{$loop->iteration}}+ '" value="' + data.pm_{{$loop->iteration}}+ '" readonly="" ></div></div>@endforeach');
                $('#r_date').html(data.open)
            }
        });
        if (show) $('#pos_register').modal('toggle');
    }

    function print_it() {
        $("#print_section").printThis({
            printDelay: 333,
            afterPrint: null,
        });
    }
</script>