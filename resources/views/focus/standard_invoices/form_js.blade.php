{{ Html::script('focus/js/select2.min.js') }}
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        ajax: {headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
        autoComplete: {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}", 
                    data: {keyword: request.term, },
                    method: 'POST',
                    success: result => response(result.map(v => ({label: v.name, value: v.name, data: v}))),
                });
            },
            autoFocus: true,
            select: function(event, ui) {

                const {data} = ui.item;
                const row = $(':focus').parents('tr');
                row.find('.prod-id').val(data.id);
                row.find('.name').val(data.name);
                row.find('.qty').val(1);

                const currencyRate = accounting.unformat($('#fx_curr_rate').val());
                if (currencyRate > 1) data.price = (data.price / currencyRate).toFixed(2);
                row.find('.price').val(accounting.formatNumber(data.price)).trigger('input'); 

                if (data.units) {
                    let units = data.units.filter(v => v.unit_type == 'base');
                    if (units.length) row.find('.unit').val(units[0].code);
                }


                loadReservationData();

            }
        },
    };

    $(document).ready(function () {
        // Initialize Select2 for the customer input
        $('#customer').select2({
            placeholder: 'Choose Customer',
            allowClear: true,
        });

        // Initialize Select2 for the reservation input
        const reservationSelect = $('#reservation').select2({
            placeholder: 'Select a reservation',
            allowClear: true,
        });

        // Load reservations when the customer changes
        $('#customer').on('change', function () {
            const customerId = $(this).val();

            if (customerId) {
                // Fetch reservations for the selected customer
                $.ajax({
                    url: '{{ route("biller.get-customer-reservations-data") }}',
                    type: 'GET',
                    data: { customerId: customerId },
                    dataType: 'json',
                    success: function (data) {

                        // console.clear();
                        // console.table(data);

                        // Clear current options in the reservation dropdown
                        $('#reservation').empty();

                        // Add placeholder as the first option
                        $('#reservation').append(new Option('Select a reservation', '', true, false));

                        // Map data to Select2 format and add new options
                        const options = data.map(item => new Option(item.label, item.uuid, false, false));
                        $('#reservation').append(options);

                        // Refresh the Select2 dropdown
                        $('#reservation').val('').trigger('change');
                    },
                    error: function () {
                        alert('Failed to load reservations. Please try again.');
                    }
                });
            } else {
                // Clear reservation dropdown if no customer is selected
                $('#reservation').empty().append(new Option('Select a reservation', '', true, false)).trigger('change');
            }
        });



        $(document).on('change', '.price, .qty, .taxid, .overall-tax', function () {

            loadReservationData()
        })



    });

    let totalPromoDiscount = 0;

    function loadReservationData() {

        let productIds = $('input[name="product_id[]"]').serializeArray();
        let productQuantities = $('input[name="product_qty[]"]').serializeArray();
        let productPrices = $('input[name="product_price[]"]').serializeArray();
        let productTaxRates = $('.taxid').serializeArray();

        let requestData = {

            productIds: productIds,
            productQuantities: productQuantities,
            productPrices: productPrices,
            productTaxRates: productTaxRates,
            customerId: $('#customer').val(),
            reservationUuid: $('#reservation').val()
        };

        // let requestData = productIds + productQuantities + additionalData;

        console.log("SEARCH DATA");
        console.table(requestData);

        $.ajax({
            url: '{{ route("biller.get-customer-reservations-data") }}',
            type: 'GET',
            data: requestData,
            dataType: 'json',
            success: function (data) {

                // console.clear();
                // console.table(data);


                $('#promoDiscounts').html(data.reservationsData.discountsTable);

                $('#promoDiscountData').val(JSON.stringify(data.reservationsData.discounts));


                totalPromoDiscount = data.overallDiscount;
                $('#totalPromoDiscount').val(accounting.formatNumber(data.overallDiscount));
                $('#totalPromoDiscountedTax').val(accounting.formatNumber(data.overallDiscountedTax));

                setTimeout(() => {

                    let currentTotal = accounting.unformat($('#total').val());
                    $('#total').val(accounting.formatNumber(currentTotal - data.overallDiscount))

                    let currentTax = accounting.unformat($('#tax').val());
                    $('#tax').val(accounting.formatNumber(currentTax - data.overallDiscountedTax))

                }, 4000);

            },
            error: function () {

                console.log("ERAAAA")
                // alert('Failed to load reservations. Please try again.');

            }
        });

    }


    const Form = {
        initRow: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('.select2').select2({allowClear: true});
            $('#classlist').select2({allowClear: true});
            Form.initRow = $('tbody tr:first').clone().html();
            $('tbody .name:first').autocomplete(config.autoComplete);

            $('#customer').change(Form.onChangeCustomer);
            $('#taxid').change(Form.onChangeTax);
            $('#companyName').keyup(function() { $('#clientName').val($(this).val()) });

            $(document).on('click', '.add-row, .remove-row', Form.addRemoveRow);
            $(document).on('input', '.qty, .price, .taxid', Form.onInputAmountAttr);
            $(document).on('change', '.price', Form.onChangeCost);
        },

        onChangeCustomer() {
            const customer_id = $(this).val();
            // set currency
            $('#currency option').text('').attr('value', '');
            $('#currency').val('');
            $('#fx_curr_rate').val('').attr('readonly', true);
            if (customer_id) {
                $.post("{{route('biller.currencies.load')}}", {customer_id})
                .done(data => {
                    if (!data.id) return;
                    $('#currency option').text(data.code).attr('value', data.code);
                    $('#currency').val(data.code);
                    $('#fx_curr_rate').val(+data.rate)
                    if (+data.rate > 1) $('#fx_curr_rate').attr('readonly', false);
                })
                .fail((xhr, status, error) => console.log(error))
            }

            // set credit limit
            $('#credit_limit').html('')
            if (customer_id) {
                $.post("{{route('biller.customers.check_limit')}}", {customer_id})
                .done(data => {
                    let number = accounting.unformat($('#total').val());;
                    let outstandingTotal = parseFloat(data.outstanding_balance);
                    let total_aging = parseFloat(data.total_aging);
                    let credit_limit = parseFloat(data.credit_limit);
                    let total_age_grandtotal = total_aging+number;
                    let balance = total_age_grandtotal - outstandingTotal;
                    $('#total_aging').val(accounting.formatNumber(data.total_aging));
                    $('#credit').val(accounting.formatNumber(data.credit_limit));
                    $('#outstanding_balance').val(data.outstanding_balance);
                    if(balance > credit_limit && credit_limit > 0){
                        let exceeded = balance - data.credit_limit;
                        $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by: ${accounting.formatNumber(exceeded)}</h4>`);
                    } else {
                        $('#credit_limit').html('')
                    }
                })
                .fail((xhr, status, error) => console.log(error));
            }
        },

        addRemoveRow() {
            if ($(this).is('.add-row')) {
                $(this).closest('tr').after(`<tr>${Form.initRow}</tr>`);
                const row = $(this).closest('tr').next();
                row.find('.name').autocomplete(config.autoComplete);

                const mainTax = accounting.unformat($('#taxid').val());
                row.find('option:not(:first)').each(function() {
                    const optVal = accounting.unformat($(this).attr('value'));
                    $(this).attr('selected', false);
                    if (optVal == mainTax || optVal == 0) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            } else {
                const row = $('#products_tbl tbody tr:last');
                if (!row.siblings().length) return;
                row.remove();
            }
            // set numbering
            $('#products_tbl tbody tr').each(function(i) {
                $(this).find('.num').val(i+1)
            });
            Form.calcTotals();
        },

        onChangeTax() {
            const mainTax = accounting.unformat(this.value);
            $('tbody tr').each(function() {
                let isSelected;
                $(this).find('option:not(:first)').each(function() {
                    const optVal = accounting.unformat($(this).attr('value'));
                    if (optVal == mainTax || optVal == 0) {
                        $(this).removeClass('d-none');
                        if (!isSelected) {
                            isSelected = true;
                            $(this).attr('selected', true);
                            $(this).parents('select').val(mainTax).trigger('input');
                        }
                    } else {
                        $(this).attr('selected', false);
                        $(this).addClass('d-none');
                    }
                });
            });
        },

        onInputAmountAttr() {
            const row = $(this).parents('tr');
            const qty = accounting.unformat(row.find('.qty').val());
            const price = accounting.unformat(row.find('.price').val());
            const taxId = accounting.unformat(row.find('.taxid').val());
        
            const subtotal = qty * price;
            const tax = subtotal * taxId * 0.01;
            const taxable = tax > 0? subtotal : 0;
            const total = subtotal + tax;
            
            row.find('.prodtax').val(accounting.formatNumber(tax));
            row.find('.amount').val(accounting.formatNumber(total));
            row.find('.prod-subtotal').val(accounting.formatNumber(price));
            row.find('.prod-subtotal-dis').val(accounting.formatNumber(subtotal));
            row.find('.prod-taxable-dis').val(accounting.formatNumber(taxable));
            Form.calcTotals();
        },

        onChangeCost() {
            const row = $(this).parents('tr');
            const price = accounting.unformat(row.find('.price').val());
            row.find('.price').val(accounting.formatNumber(price));
        },

        calcTotals() {
            let subtotal = 0;
            let tax = 0;
            let taxable = 0;
            let total = 0;
            $('#products_tbl tbody tr').each(function(i) {
                total += accounting.unformat($(this).find('.amount').val());
                subtotal += accounting.unformat($(this).find('.prod-subtotal-dis').val());
                tax += accounting.unformat($(this).find('.prodtax').val());
                taxable += accounting.unformat($(this).find('.prod-taxable-dis').val());
            });
            $('#subtotal').val(accounting.formatNumber(subtotal));
            $('#tax').val(accounting.formatNumber(tax));
            $('#taxable').val(accounting.formatNumber(taxable));
            $('#total').val(accounting.formatNumber(total));

            // set credit limit
            $("#credit_limit").html('');
            let credit_limit = $('#credit').val().replace(/,/g, '');
            let total_aging = $('#total_aging').val().replace(/,/g, '');
            let outstanding_balance = $('#outstanding_balance').val().replace(/,/g, '');
            let balance = total_aging.toLocaleString() - outstanding_balance.toLocaleString() + total;
            if (balance > credit_limit && credit_limit > 0) {
                let exceeded = balance -credit_limit;
                $("#credit_limit").html(`<h4 class="text-danger">Credit Limit Violated by:  ${accounting.formatNumber(exceeded)}</h4>`);
            }else{
                $("#credit_limit").html('');
            }
        },
    };

    $(Form.init);
</script>