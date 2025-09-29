{{ Html::script('focus/js/select2.min.js') }}
<script>
    // initialize html editor
    editor();

    // ajax config
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    $('#lead_id').select2({allowClear: true});
    $('#bom').select2({allowClear: true});
    $('#productvar').select2({allowClear: true});
    $('#classlist').select2({allowClear: true});
    $('#select_header').select2({allowClear: true});
    $('#select_footer').select2({allowClear: true});

    $('#check').click(function(){
        if ($(this).is(':checked', true)) {
            $('.add-image').removeClass('d-none');
            $('#appear_image').attr('disabled', false);
            $('#productvar').attr('disabled', false);
            $('#appear_image').attr('required', true);
            $('#productvar').attr('required', true);
        } else {
            $('.add-image').addClass('d-none');
            $('#appear_image').attr('disabled', true);
            $('#productvar').attr('disabled', true);
            $('#appear_image').attr('required', false);
            $('#productvar').attr('required', false);
        }
    });
    $('#removeUoM').click(function(){
        if ($(this).is(':checked', true)) {
            $('.uom-remove').removeClass('d-none');
            $('#uom_status').attr('disabled', false);
        } else {
            $('.uom-remove').addClass('d-none');
            $('#uom_status').attr('disabled', true);
        }
    });

    $('#refreshButton').click(function () {
        var alphabet = 'A'.charCodeAt(0); // Start at 'A' (65)
        var number = 1; // Start at 1

        $('#quoteTbl tbody tr').each(function () {
            var $row = $(this);
            var type = $row.find('input[name="a_type[]"]').val();

            if (type === "2") {
                // Assign alphabetical value A-Z
                $row.find('.numbering').val(String.fromCharCode(alphabet));
                alphabet++;

                // Reset to 'A' after 'Z'
                if (alphabet > 'Z'.charCodeAt(0)) {
                    alphabet = 'A'.charCodeAt(0);
                }

                number = 1; // Reset numbering for products
            } else {
                // Assign numerical value
                $row.find('.numbering').val(number);
                number++;
            }

            // Sanitize: allow only letters and numbers (removes symbols, etc.)
            var currentVal = $row.find('.numbering').val();
            var sanitizedVal = currentVal.toString().replace(/[^a-zA-Z0-9]/g, '');
            $row.find('.numbering').val(sanitizedVal);
        });
    });

    $(document).ready(function () {
        // Check if the user has view access
        const hasViewAccess = @json(access()->allow('estimate-buying-price'));

        if (!hasViewAccess) {
            // Target table by ID
            const tableId = '#quoteTbl';

            // Hide the <th> with class "est_purchase_price" in <thead>
            $(tableId + ' thead th.est_purchase_price').hide();
            $('.profit').hide();
            $('.estimate-cost').hide();

            // Hide all <td> in <tbody> that contains the "buyprice" input field
            $(tableId + ' tbody tr').each(function () {
                $(this).find('td:has(input.buyprice)').hide();
                $(this).find('span.lineprofit').hide();
            });
        }
    });



    $('#productvar').change(function(){
        const image_name = $('#productvar option:selected').attr('data-image');
        console.log(image_name);
        var imagePath = "{{ asset('storage/img/products') }}/" + image_name;
        // Set the src attribute of the img tag
        $('#dynamicImage').attr('src', imagePath);
    });

    $(document).on('change', '#select_header, #select_footer', function () {
        // Check which element triggered the change
        const changedElement = $(this).attr('id');

        // Prepare data object to send only the changed field
        let data = {};
        if (changedElement === 'select_header') {
            data.header_id = $('#select_header').val();
            data.footer_id = null; // Footer ID is intentionally null
        } else if (changedElement === 'select_footer') {
            data.footer_id = $('#select_footer').val();
            data.header_id = null; // Header ID is intentionally null
        }

        // Send AJAX request with only the relevant data
        $.ajax({
            url: "{{ route('biller.quote_notes.get_notes') }}",
            method: 'POST',
            data: data,
            success: function (response) {
                if (changedElement === 'select_header') {
                    $('#header_text').summernote('code', response.description);
                } else if (changedElement === 'select_footer') {
                    $('#footer_text').summernote('code', response.description);
                }
            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
            }
        });
    });

    // initialize datepicker
    $('.datepicker').datepicker({
        format: "{{ config('core.user_date_format') }}",
        autoHide: true
    })
    $('#referencedate').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());

    // print type
    $('input[type=radio]').change(function() {
        if ($(this).val() == 'inclusive') $('#vatText').text('(Print VAT-Inc)');
        else $('#vatText').text('(Print VAT-Exc)');
    });

    $('#lead_id').change(function() {
        $('#currency option').removeClass('d-none');
        const currencyId = $(this).find(':selected').attr('currencyId');
        if (currencyId) {
            $('#currency').val(currencyId);
            $('#currency option').addClass('d-none');
            $(`#currency option[value="${currencyId}"]`).removeClass('d-none')
        } 
    });

    $('#template_type').change(function() {
        const type = $('#template_type').val();
        if(type == 'bom'){
            $('.div_template_quote').addClass('d-none');
            $('.div_bom').removeClass('d-none');
            $.ajax({
                type: "POST",
                url: "{{ route('biller.boms.select_bom')}}",
                data: {
                    lead_id: $('#lead_id').val(),
                },
                success: function(response) {
                    var select = $('#bom');
                    select.empty();
                    select.append($('<option>', {
                        value: null,
                        text: 'Select Bom'
                    }));
                    $.each(response, function(index, option) {
                        select.append($('<option>', { 
                            value: option.id,
                            text : option.name + ' | BoM-' +  option.tid,
                        }));
                    });
                }
            });
        }else{
            $('.div_template_quote').removeClass('d-none');
            $('.div_bom').addClass('d-none');
        }
    });

    $('#bom').change(function(){
        var bom_id = $('#bom').val();
        $('#quoteTbl tbody').html('');
        const hasViewAccess = @json(access()->allow('estimate-buying-price'));
        $.ajax({
                type: "POST",
                url: "{{ route('biller.boms.get_bom_items')}}",
                data: {
                    bom_id: bom_id,
                },
                success: function(response) {
                    console.log(response);
                    response.forEach((v,i) => {
                        if(v.type == 'product'){

                            $('#quoteTbl tbody').append(productBoMRow(v,i));

                            if (!hasViewAccess) {
                                // Target table by ID
                                const tableId = '#quoteTbl';

                                // Hide the <th> with class "est_purchase_price" in <thead>
                                $(tableId + ' thead th.est_purchase_price').hide();
                                $('.profit').hide();
                                $('.estimate-cost').hide();

                                // Hide all <td> in <tbody> that contains the "buyprice" input field
                                $(tableId + ' tbody tr').each(function () {
                                    $(this).find('td:has(input.buyprice)').hide();
                                    $(this).find('span.lineprofit').hide();
                                });
                            }
                        }else if(v.type == 'title'){
                            $('#quoteTbl tbody').append(titleBoMRow(v,i));
                        }
                    });
                    $('.tax_rate').change();
                }
            });
    });

    function productBoMRow(v,i){
        return `
                <tr id="productRow" class="misc" style="background-color:rgba(229, 241, 101, 0.4);">
                    <td><input type="text" class="form-control numbering" name="numbering[]" id="numbering-p0" value=""></td>
                    <td>
                        <textarea name="product_name[]" id="name-p0" cols="35" rows="2" class="form-control" placeholder="{{ trans('general.enter_product') }}" required>${v.product_name}</textarea>
                    </td>
                    <td><input type="text" name="unit[]" id="unit-p0" class="form-control" value="${v.uom}"></td>
                    <td ><input type="number" class="form-control estqty" name="estimate_qty[]" value="${+v.qty}" id="estqty-p0" step="0.1" style="border:solid #f5a8a2;" required></td>  
                    <td ><input type="text" class="form-control buyprice" name="buy_price[]" value="${+v.rate}" id="buyprice-p0"  style="border:solid #f5a8a2;" readonly></td>  
                    <td><input type="number" class="form-control qty invisible" name="product_qty[]" value="${+v.qty}" id="qty-p0" step="0.1" required ></td>
                    <td><input type="text" class="form-control rate invisible" name="product_subtotal[]" value="${+v.rate}" id="rate-p0" required></td>
                    <td>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <input type="text" class="form-control price" value="${+v.product_subtotal}" name="product_price[]" id="price-p0" readonly>
                            </div>
                            <div class="col-6">
                                <select class="custom-select tax_rate" name="tax_rate[]" id="taxrate-p0">
                                    @foreach ($additionals as $item)
                                        <option value="{{ +$item->value }}">{{ $item->value == 0 ? 'OFF' : +$item->value . '%' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </td>
                    <td class='text-center'>
                        <span class="amount" id="amount-p0">${+v.amount}</span>&nbsp;&nbsp;
                        <span class="lineprofit text-info" id="lineprofit-p0">0%</span>
                    </td>
                    <td class="text-center">
                        @include('focus.quotes.partials.action-dropdown')
                    </td>
                    <input type="hidden" name="misc[]" value="1" id="misc-p0">
                    <input type="hidden" name="product_id[]" value="${v.product_id}" id="productid-p0">
                    <input type="hidden" class="index" name="row_index[]" value="${v.row_index}" id="rowindex-p0">
                    <input type="hidden" name="a_type[]" value="1" id="atype-p0">
                    <input type="hidden" name="id[]" value="0">
                    <input type="hidden" name="product_type[]" value="inventory_product" id="product_type-p0">
                    <input type="hidden" name="client_product_id[]" value="0" id="client_product_id-p0">
                </tr>
        `;
    }

    function titleBoMRow(v, i)
    {
        return `
            <tr id="titleRow">
                <td><input type="text" class="form-control numbering" name="numbering[]" id="numbering-t1" value="" style="font-weight: bold;"></td>
                <td colspan="8">
                    <input type="text"  class="form-control" name="product_name[]" value="${v.product_name}" id="name-t1" style="font-weight: bold;" required>
                </td>
                <td class="text-center">
                    @include('focus.quotes.partials.action-dropdown')
                </td>
                <input type="hidden" name="misc[]" value="0" id="misc-t1">
                <input type="hidden" name="product_id[]" value="0" id="productid-t1">
                <input type="hidden" name="unit[]">
                <input type="hidden" name="product_qty[]" value="0">
                <input type="hidden" name="product_price[]" value="0">
                <input type="hidden" name="tax_rate[]" value="0">
                <input type="hidden" name="product_subtotal[]" value="0">
                <input type="hidden" name="estimate_qty[]" value="0">
                <input type="hidden" name="buy_price[]" value="0">
                <input type="hidden" class="index" name="row_index[]" value="0" id="rowindex-t1">
                <input type="hidden" name="a_type[]" value="2" id="atype-t1">
                <input type="hidden" name="id[]" value="0">
                <input type="hidden" name="product_type[]" value="title" id="product_type-t1">
                <input type="hidden" name="client_product_id[]" value="0" id="client_product_id-t1">
            </tr>
        `;
    }

    // On change lead and djc
    const subject = {
        title: '',
        djc: ''
    };
    $('form').on('change', '#lead_id, #reference', function() {
        if ($(this).is('#lead_id')) {
            const opt = $('#lead_id option:selected');
            $('#attention').val(opt.attr('assign_to'));
            $('#subject').val(opt.attr('title'));
            $('#client_ref').val(opt.attr('client_ref'));
            $('#branch_id').val(opt.attr('branch_id'));
            $('#customer_id').val(opt.attr('customer_id'));
            $('#income_category').val(opt.attr('incomeCategory'));
            $('#total_boq_amount').val(opt.attr('boqTotal'));
            subject.title = opt.attr('title');

            // update price customer based on selected lead
            let priceCustomer = '';
            $('#price_customer option').each(function() {
                if (opt.attr('customer_id') == $(this).val())
                    priceCustomer = $(this).val();
            });
            $('#price_customer').val(priceCustomer);
            $('#credit_limit').html('')
            $.ajax({
                type: "POST",
                url: "{{route('biller.customers.check_limit')}}",
                data: {
                    customer_id: opt.attr('customer_id')
                },
                success: function (result) {
                    let total = $('#total').val();
                    let number = 0;
                    if(!isNaN(total)){
                        total = 0;
                        number = total;
                    }else{
                        number = total.replace(/,/g, '');
                    }
                    
                    let newTotal = parseFloat(number);
                     let outstandingTotal = parseFloat(result.outstanding_balance);
                     let total_aging = parseFloat(result.total_aging);
                     let credit_limit = parseFloat(result.credit_limit);
                     let total_age_grandtotal = total_aging+newTotal;
                    let balance = total_age_grandtotal - outstandingTotal;
                    $('#total_aging').val(result.total_aging.toLocaleString());
                    $('#credit').val(result.credit_limit.toLocaleString());
                    $('#outstanding_balance').val(result.outstanding_balance);
                    if(balance > credit_limit && credit_limit > 0){
                        let exceeded = balance-result.credit_limit;
                        $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by: ${exceeded.toLocaleString()}</h4>`);
                        
                    }else{
                        $('#credit_limit').html('')
                    }
                }
            });

        } else subject.djc = $(this).val();
        // subject
        if (subject.title && subject.djc) $('#subject').val(subject.title + ' ; Djc-' + subject.djc);
        else if (subject.title) $('#subject').val(subject.title);
    });

    // calculate profit
    const profitState = {
        sp_total: 0,
        bp_subtotal: 0,
        skill_total: 0,
        bp_total: 0
    };

    const quote_pi_limit = @json(@$quote_pi_limit->target);
    function calcProfit() {
        const {
            sp_total,
            bp_total,
            skill_total
        } = profitState;
        const profit = sp_total - (bp_total + skill_total);
        let pcent_profit = profit / (sp_total + skill_total) * 100;
        pcent_profit = isFinite(pcent_profit) ? Math.round(pcent_profit) : 0;

        const profitText = bp_total > 0 ?
            `${accounting.formatNumber(profit)} : ${pcent_profit}%` : accounting.formatNumber(profit);
        $('.profit').text(profitText);

        if (profit < 0) $('.profit').removeClass('text-dark').addClass('text-danger');
        else $('.profit').removeClass('text-danger').addClass('text-dark');

        // budget limit 30 percent
        let limit = (accounting.unformat(quote_pi_limit) + 100)/100;
        if(!!quote_pi_limit) {
            limit = limit;
        }else limit = 1.3;
        if (sp_total < bp_total * limit) $('.budget-alert').removeClass('d-none');
        else $('.budget-alert').addClass('d-none');

        // estimate cost
        $('.estimate-cost').text(accounting.formatNumber(bp_total + skill_total));
    }

    // update row tax options
    function updateLineTax(taxSelect) {
        if (taxSelect && taxSelect.length) {
            const mainTax = $('#tax_id').val();
            taxSelect.children().each(function() {
                const value = $(this).attr('value');
                if ((value == mainTax) || (value == 0)) $(this).removeClass('d-none');
                else $(this).addClass('d-none');
            });
            taxSelect.val(mainTax);
        }
    }

    /**
     * Table logic
     */
    // add title
    let titleId = 2;
    const titleHtml = $("#titleRow").html();
    // $("#titleRow").remove();
    $('#addTitle').click(function() {
        $('#quoteTbl tbody tr.invisible').remove();

        const i = 't' + titleId;
        const newTitleHtml = '<tr>' + titleHtml.replace(/t1/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newTitleHtml);
        titleId++;
        calcTotal();
        adjustTbodyHeight();
    });

    // add product
    let rowId = 1;
    const rowHtml = $("#productRow").html();
    $('#name-p0').autocomplete(autoComp('p0'));
    const hasViewAccess = @json(access()->allow('estimate-buying-price'));

    // Function to hide columns if the user lacks permission
    function hideEstPurchasePriceColumn(row) {
        if (!hasViewAccess) {
            // Hide <td> in the new row corresponding to the "est_purchase_price" column
                row.find('td:has(input.buyprice)').hide();
                row.find('span.lineprofit').hide();
        }
    }
    $('#addProduct').click(function() {
        $('#quoteTbl tbody tr.invisible').remove();
        const i = 'p' + rowId;
        const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-' + i).autocomplete(autoComp(i));
        let row = $("#quoteTbl tbody tr:last");
        updateLineTax(row.find('.tax_rate'));
        // Hide the column for the new row if the user lacks access
        hideEstPurchasePriceColumn(row);

        // trigger lead change to reset client pricelist 
        $('#lead_id').change();
        adjustTbodyHeight();
        calcTotal();
        rowId++;
    });
    // adjust tbody height to accomodate dropdown menu
    function adjustTbodyHeight(rowCount) {
        rowCount = rowCount || $('#quoteTbl tbody tr').length;
        if (rowCount < 4) {
            const rows = [];
            for (let i = 0; i < 5; i++) {
                const tr = `<tr class="invisible"><td colspan="100%"></td><tr>`
                rows.push(tr);
            }
            $('#quoteTbl tbody').append(rows.join(''));
        }
    }

    // add miscellaneous product
    $('#addMisc').click(function() {
        $('#quoteTbl tbody tr.invisible').remove();

        const i = 'p' + rowId;
        const newRowHtml =
            `<tr class="misc" style="background-color:#ffffff; opacity:0.3;"> ${rowHtml.replace(/p0/g, i)} </tr>`;
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-' + i).autocomplete(autoComp(i));
        $('#misc-' + i).val(1);
        $('#qty-' + i).val(1);
        ['qty', 'price', 'rate', 'lineprofit'].forEach(v => {
            $(`#${v}-${i}`).addClass('invisible');
        });
        if (!hasViewAccess) {
            const newRow = $("#quoteTbl tbody tr:last");
            newRow.find('td:has(input.buyprice)').hide();
            newRow.find('span.lineprofit').hide();
        }
        rowId++;
        calcTotal();
        adjustTbodyHeight();
    });

    // On clicking action drop down
    $("#quoteTbl").on("click", ".up, .down, .delete, .add-title, .add-product, .add-misc", function() {
        const menu = $(this);
        const row = $(this).parents("tr:first");

        if (menu.is('.up')) row.insertBefore(row.prev());
        if (menu.is('.down')) row.insertAfter(row.next());
        if (menu.is('.delete') && confirm('Are you sure?')) {
            row.remove();
            $('#quoteTbl tbody tr.invisible').remove();
            adjustTbodyHeight(1);
        }

        // dropdown menus
        if (menu.is('.add-title')) {
            $('#addTitle').click();
            const titleRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.before(titleRow);
        }
        if (menu.is('.add-product')) {
            $('#addProduct').click();
            const productRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.after(productRow);
            // update tax options
            updateLineTax($("#quoteTbl tbody tr:last").find('.tax_rate'));
            $("#quoteTbl .pname").each(function() {
                let id = $(this).attr('id').split('-')[1];
                $(this).autocomplete(autoComp(id));
            });
        }
        if (menu.is('.add-misc')) {
            $('#addMisc').click();
            const miscRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.after(miscRow);
            $("#quoteTbl .pname").each(function() {
                let id = $(this).attr('id').split('-')[1];
                $(this).autocomplete(autoComp(id));
            });

        }

        calcTotal();
    });

    // on change qty and rate
    $("#quoteTbl").on("change", ".qty, .rate, .buyprice, .estqty, .tax_rate", function() {
        const id = $(this).attr('id').split('-')[1];
        const row = $(this).parents("tr:first");
        if (row.hasClass('misc')) {
            const taxrate = accounting.unformat(row.find('.tax_rate').val());
            let buyprice = accounting.unformat(row.find('.buyprice').val());
            let estqty = accounting.unformat(row.find('.estqty').val() || '1');
            price = 0;
            if (taxrate === 0) price = buyprice;
            else price = buyprice * (taxrate / 100 + 1);
            row.find('.amount').text(accounting.formatNumber(estqty * price, 2));
            calcTotal();
        } else {
            const qty = accounting.unformat(row.find('.qty').val());
            const taxrate = accounting.unformat(row.find('.tax_rate').val());
            let buyprice = accounting.unformat(row.find('.buyprice').val());
            let estqty = accounting.unformat(row.find('.estqty').val() || '1');
            let rate = accounting.unformat(row.find('.rate').val());

            // row item % profit
            let price = rate * (taxrate / 100 + 1);
            let profit = (qty * rate) - (estqty * buyprice);
            let pcent_profit = profit / (qty * rate) * 100;
            pcent_profit = isFinite(pcent_profit) ? Math.round(pcent_profit) : 0;

            row.find('.buyprice').val(accounting.formatNumber(buyprice,2));
            row.find('.rate').val(accounting.formatNumber(rate,2));
            row.find('.price').val(accounting.formatNumber(price,2));
            row.find('.amount').text(accounting.formatNumber(qty * price,2));
            row.find('.lineprofit').text(pcent_profit + '%');
            calcTotal();
        }
    });

    // on tax change
    $('#tax_id').change(function() {
        const mainTax = $(this).val();
        $('#quoteTbl tbody tr').each(function() {
            updateLineTax($(this).find('.tax_rate'));
            if ($(this).find('.qty').val() * 1) {
                const itemRate = accounting.unformat($(this).find('.rate').val());
                $(this).find('.price').val(accounting.formatNumber(itemRate * (mainTax / 100 + 1), 2));
                $(this).find('.rate').change();
            }
        });
    }).change();

    // on currency change
    let initRate = $('#currency option:selected').attr('rate');
    $('#currency').change(function() {
        const currentRate = $(this).find(':selected').attr('rate') * 1;
        if (currentRate > initRate) {
            $('#quoteTbl tbody tr').each(function() {
                let purchasePrice = accounting.unformat($(this).find('.buyprice').val()) * initRate;
                let itemRate = accounting.unformat($(this).find('.rate').val()) * initRate;
                purchasePrice = purchasePrice / currentRate;
                itemRate = itemRate / currentRate;
                $(this).find('.buyprice').val(accounting.formatNumber(purchasePrice,2));
                $(this).find('.rate').val(accounting.formatNumber(itemRate,2)).change();
            });
        } else {
            $('#quoteTbl tbody tr').each(function() {
                let purchasePrice = accounting.unformat($(this).find('.buyprice').val()) / currentRate;
                let itemRate = accounting.unformat($(this).find('.rate').val()) / currentRate;
                purchasePrice = purchasePrice * initRate;
                itemRate = itemRate * initRate;
                $(this).find('.buyprice').val(accounting.formatNumber(purchasePrice,2));
                $(this).find('.rate').val(accounting.formatNumber(itemRate,2)).change();
            });
        }
        initRate = currentRate;
    });

    // compute totals
    function calcTotal() {
        let taxable = 0;
        let total = 0;
        let subtotal = 0;
        let bp_subtotal = 0;
        $("#quoteTbl tbody tr").each(function(i) {
            const isMisc = $(this).hasClass('misc');
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                if (isMisc) {
                    const buyprice = accounting.unformat($(this).find('.buyprice').val());
                    const estqty = $(this).find('.estqty').val();
                    const taxrate = accounting.unformat($(this).find('.tax_rate').val());
                    v = 0;
                    if (taxrate === 0) {
                        v = buyprice;
                    } else {
                        v = buyprice * (taxrate / 100 + 1);
                    }
                    bp_subtotal += v * estqty;

                } else {
                    const amount = accounting.unformat($(this).find('.amount').text());
                    const rate = accounting.unformat($(this).find('.rate').val());
                    const taxRate = accounting.unformat($(this).find('.tax_rate').val());
                    if (taxRate > 0) taxable += qty * rate;
                    total += amount;
                    subtotal += qty * rate;

                    const buyprice = accounting.unformat($(this).find('.buyprice').val());
                    const estqty = $(this).find('.estqty').val();
                    bp_subtotal += estqty * buyprice;
                }
            }
            $(this).find('.index').val(i);
        });
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#vatable').val(accounting.formatNumber(taxable));
        $('#total').val(accounting.formatNumber(total));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber((total - subtotal)));
        profitState.bp_total = bp_subtotal;
        profitState.sp_total = subtotal;
        $("#credit_limit").html('');
        let credit_limit = $('#credit').val().replace(/,/g, '');
        let total_aging = $('#total_aging').val().replace(/,/g, '');
        let outstanding_balance = $('#outstanding_balance').val().replace(/,/g, '');
        let balance = total_aging.toLocaleString() - outstanding_balance.toLocaleString() + total;
        if (balance > credit_limit && credit_limit > 0) {
            let exceeded = balance -credit_limit;
            $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by:  ${accounting.formatNumber(exceeded)}</h4>`);
        }else{
            $("#credit_limit").html('');
        }
        calcProfit();
    }

    // product row
    function productRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control unique-id" name="unique_id[]" placeholder="Search Equipment" id="uniqueid-${n}" required></td>
                <td><input type="text" class="form-control eq-tid-row" name="equipment_tid[]" id="eq-tid-${n}" required></td>
                <td><input type="text" class="form-control equip-serial" name="equip_serial[]" id="equipserial-${n}"></td>
                <td><input type="text" class="form-control make-type" name="make_type[]" id="maketype-${n}" required></td>
                <td><input type="text" class="form-control capacity" name="capacity[]" id="capacity-${n}" required></td>
                <td><input type="text" class="form-control location" name="location[]" id="location-${n}" required></td>
                <td>
                    <select class="custom-select fault" name="fault[]" id="fault-${n}">
                        @foreach ($faults as $fault)
                        <option value="{{ $fault->name }}" selected>{{ $fault->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item delete" href="javascript:" data-rowid="${n}" >Remove</a>
                            <a class="dropdown-item up" href="javascript:">Up</a>
                            <a class="dropdown-item down" href="javascript:">Down</a>
                        </div>
                    </div>
                </td>
                <input type="hidden" name="row_index_id[]" value="0" class="row-index" id="rowindex-${n}">
                <input type="hidden" name="item_id[]" value="0" class="item-id" id="itemid-${n}">
            </tr>
        `;
    }

    // attach Site Survey Report Details
    $('#attach-djc').change(function() {
        if ($(this).prop("checked")) {
            $('#reference, #referencedate').attr('disabled', false);
        } else {
            $('#reference, #referencedate').attr('disabled', true);
        }
    });
    // Attach Repair Equipment
    $('#add-check').change(function() {
        $(() => {
            if ($(this).prop("checked")) {
                $('#addqproduct').removeClass('d-none');
                $('#equipmentsTbl tbody').find('input, select').attr('disabled', false);
            } else {
                $('#addqproduct').addClass('d-none');
                $('#equipmentsTbl tbody').find('input, select').attr('disabled', true);
            }
        });
    });
    $('#attach-djc').prop("checked", false).change();
    $('#add-check').prop("checked", false).change();

    // equipment row counter;
    let rowIds = 0;
    $('#equipmentsTbl tbody').append(productRow(0));
    $('#uniqueid-0').autocomplete(autocompleteProp(0));

    // on clicking addproduct
    $('#addqproduct').on('click', function() {
        rowIds++;
        const i = rowIds;
        $('#equipmentsTbl tbody').append(productRow(i));
        $('#uniqueid-' + i).autocomplete(autocompleteProp(i));
        assignIndex();
    });

    // on clicking equipment drop down options
    $("#equipmentsTbl").on("click", ".up, .down, .delete", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.delete')) $(this).closest('tr').remove();
        assignIndex();
    });

    // assign row index
    function assignIndex() {
        $('#equipmentsTbl tr').each(function(i) {
            if (i > 0) $(this).find('.row-index').val(i);
        });
    }

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#customer_id").val(),
                    dataType: "json",
                    method: 'post',
                    data: {
                        keyword: request.term,
                        customer_id: $('#lead_id option:selected').attr('customer_id'),
                        branch_id: $('#lead_id option:selected').attr('branch_id')
                    },
                    success: data => {
                        data = data.map(v => {
                            for (const key in v) {
                                if (!v[key]) v[key] = '';
                            }
                            const label = `${v.unique_id} ${v.tid} ${v.id} ${v.equip_serial} ${v.make_type} ${v.model} ${v.machine_gas}
                                ${v.capacity} ${v.location} ${v.building} ${v.floor}`;
                            const value = v.unique_id;
                            const data = v;
                            return {
                                label,
                                value,
                                data
                            };
                        })
                        response(data);
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {
                    data
                } = ui.item;
                $('#uniqueid-' + i).val(data.unique_id);
                $('#eq-tid-' + i).val(data.tid);
                $('#itemid-' + i).val(data.id);
                $('#equipserial-' + i).val(data.equip_serial);
                $('#maketype-' + i).val(data.make_type);
                $('#capacity-' + i).val(data.capacity);
                $('#location-' + i).val(data.location);
            }
        };
    }

    /**
     * Skillset modal logic
     */
    // remove skill row
    $('#skillTbl').on('click', '.rem', function() {
        $(this).parents('tr').remove();
        skillTotal();
    });
    $('#skillTbl').on('change', '.type, .chrg, .hrs, .tech', function() {
        const row = $(this).parents('tr');
        let hrs = row.find('.hrs').val();
        let tech = row.find('.tech').val();
        let chrg = row.find('.chrg');

        // labour type charges
        switch (row.find('.type').val()) {
            case 'casual':
                chrg.val(250).attr('readonly', true);
                break;
            case 'contract':
                chrg.val(250).attr('readonly', true);
                break;
            case 'attachee':
                chrg.val(150).attr('readonly', true);
                break;
            case 'outsourced':
                chrg.val(chrg.val()).attr('readonly', false);
                break;
        }
        skillTotal();
    });

    // add skill row
    let skillId = 0;
    const skillHtml = $('#skillTbl tbody tr:first').html();
    $('#skillTbl tbody tr:first').remove();
    $('#addRow').click(function() {
        skillId++;
        const html = skillHtml.replace(/-0/g, '-' + skillId).replace('d-none', '');
        $('#skillTbl tbody').append('<tr>' + html + '</tr>');
    });

    function skillTotal() {
        total = 0;
        $('#skillTbl tbody tr').each(function() {
            const hrs = $(this).find('.hrs').val();
            const tech = $(this).find('.tech').val();
            const chrg = $(this).find('.chrg').val();
            const amount = hrs * chrg * tech;
            total += amount;
            $(this).find('.amount').text(amount);
        });
        $('#skill_total').val(accounting.formatNumber(total));
        profitState.skill_total = total;
        calcProfit();
    }

    // autocomplete function
    function autoComp(i) {
        return {
            source: function(request, response) {
                // stock product
                let term = request.term;
                let url = "{{ route('biller.products.quote_product_search') }}";
                let data = {
                    keyword: term,
                    price_customer_id: $('#price_customer').val(),
                };
                // maintenance service product 
                const docType = @json(request('doc_type'));
                if (docType == 'maintenance') {
                    url = "{{ route('biller.taskschedules.quote_product_search') }}";
                    data.customer_id = $('#lead_id option:selected').attr('customer_id');
                }
                $.ajax({
                    url,
                    data,
                    method: 'POST',
                    success: result => response(result.map(v => ({
                        label: v.name,
                        value: v.name,
                        data: v
                    }))),
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {
                    data
                } = ui.item;


                const row = $(this).parents("tr:first");

                if (row.hasClass('misc')) {
                    $('#productid-' + i).val(data.product_id);
                    $('#name-' + i).val(data.name);
                    $('#unit-' + i).val(data.unit);
                    $('#qty-' + i).val(1);
                    $('#estqty-' + i).val(1);
                    $('#taxrate-' + i).val(0);
                    $('#product_type-' + i).val(data.product_type);
                    $('#client_product_id-' + i).val(data.client_product_id);

                    const currencyRate = $('#currency option:selected').attr('rate');
                    if (currencyRate > 1) {
                        data.purchase_price = parseFloat(data.purchase_price) / currencyRate;
                        data.price = parseFloat(data.price) / currencyRate;
                    }

                    $('#buyprice-' + i).val(accounting.formatNumber(data.purchase_price));
                    // $('#estqty-' + i).val(1);

                    // const rate = parseFloat(data.price);
                    // let price = rate * ($('#tax_id').val() / 100 + 1);
                    // $('#price-' + i).val(accounting.formatNumber(price));
                    $('#price-' + i).val(accounting.formatNumber(data.purchase_price));
                    $('#amount-' + i).text(accounting.formatNumber(data.purchase_price));
                    // $('#rate-' + i).val(accounting.formatNumber(rate)).change();
                    $('#rate-' + i).val(accounting.formatNumber(data.purchase_price)).change();

                    


                    if (data.units) {
                        let units = data.units.filter(v => v.unit_type == 'base');
                        if (units.length) $('#unit-' + i).val(units[0].code);
                    }
                } else {
                    $('#productid-' + i).val(data.product_id);
                    $('#name-' + i).val(data.name);
                    $('#unit-' + i).val(data.unit);
                    $('#qty-' + i).val(1);
                    $('#product_type-' + i).val(data.product_type);
                    $('#client_product_id-' + i).val(data.client_product_id);

                    const currencyRate = $('#currency option:selected').attr('rate');
                    if (currencyRate > 1) {
                        data.purchase_price = parseFloat(data.purchase_price) / currencyRate;
                        data.price = parseFloat(data.price) / currencyRate;
                    }

                    $('#buyprice-' + i).val(accounting.formatNumber(data.purchase_price));
                    $('#estqty-' + i).val(1);

                    const rate = parseFloat(data.price);
                    let price = rate * ($('#tax_id').val() / 100 + 1);
                    $('#price-' + i).val(accounting.formatNumber(price));
                    $('#amount-' + i).text(accounting.formatNumber(price));
                    $('#rate-' + i).val(accounting.formatNumber(rate)).change();

                    if (data.units) {
                        let units = data.units.filter(v => v.unit_type == 'base');
                        if (units.length) $('#unit-' + i).val(units[0].code);
                    }
                }

            }
        };
    }

    $('#template_quote_id').change(function() {
        const template_quote_id = $(this).val();

        $.ajax({
            url: "{{ route('biller.template-quote-details') }}",
            dataType: "json",
            method: 'post',
            data: {
                template_quote_id: template_quote_id,
            },
            success: function(data) {
                $('#taxable').val(accounting.formatNumber(data[0].taxable));
                $('#vatable').val(accounting.formatNumber(data[0].taxable));
                $('#total').val(accounting.formatNumber(data[0].total));
                $('#subtotal').val(accounting.formatNumber(data[0].subtotal));
                $('#tax').val(accounting.formatNumber((data[0].total - data[0].subtotal)));
                
                $('#subject').val(data[0].notes);
                $('#quoteTbl tbody').html('');
                data[0].products.forEach(function(v, i) {
                    if (v.a_type === 1 && v.misc === 0) {
                        $('#quoteTbl tbody').append(
                            `<tr id="productRow">
                            <td><input type="text" class="form-control numbering" name="numbering[]" id="numbering-p0" value="${v.numbering}"></td>
                            <td>
                                <textarea name="product_name[]" id="name-p0" cols="35" rows="2" class="form-control" placeholder="{{ trans('general.enter_product') }}" required>${v.product_name}</textarea>
                            </td>
                            <td><input type="text" name="unit[]" id="unit-p0" class="form-control" value="${v.unit}"></td>
                            <td ><input type="number" class="form-control estqty" name="estimate_qty[]" value="${+v.estimate_qty}" id="estqty-p0" step="0.1" style="border:solid #f5a8a2;" required></td>  
                            <td ><input type="text" class="form-control buyprice" name="buy_price[]" value="${+v.buy_price}" id="buyprice-p0"  style="border:solid #f5a8a2;" readonly></td>  
                            <td><input type="number" class="form-control qty" name="product_qty[]" value="${+v.product_qty}" id="qty-p0" step="0.1" required></td>
                            <td><input type="text" class="form-control rate" name="product_subtotal[]" value="${+v.product_subtotal}" id="rate-p0" required></td>
                            <td>
                                <div class="row no-gutters">
                                    <div class="col-6">
                                        <input type="text" class="form-control price" value="${+v.product_price}" name="product_price[]" id="price-p0" readonly>
                                    </div>
                                    <div class="col-6">
                                        <select class="custom-select tax_rate" name="tax_rate[]" id="taxrate-p0">
                                            @foreach ($additionals as $item)
                                                <option value="{{ +$item->value }}" ${accounting.unformat(v.tax_rate) == {{ +$item->value }} ? 'selected' : ''}>{{ $item->value == 0 ? 'OFF' : +$item->value . '%' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td class='text-center'>
                                <span class="amount" id="amount-p0">${+v.product_amount}</span>&nbsp;&nbsp;
                                <span class="lineprofit text-info" id="lineprofit-p0">0%</span>
                            </td>
                            <td class="text-center">
                                @include('focus.quotes.partials.action-dropdown')
                            </td>
                            <input type="hidden" name="misc[]" value="0" id="misc-p0">
                            <input type="hidden" name="product_id[]" value="${v.product_id}" id="productid-p0">
                            <input type="hidden" class="index" name="row_index[]" value="${v.row_index}" id="rowindex-p0">
                            <input type="hidden" name="a_type[]" value="1" id="atype-p0">
                            <input type="hidden" name="product_type[]" value="inventory_product" id="product_type-p0">
                            <input type="hidden" name="client_product_id[]" value="0" id="client_product_id-p0">
                            <input type="hidden" name="id[]" value="0">
                        </tr>`
                        );
                    } else if (v.a_type === 1 && v.misc === 1) {
                        $('#quoteTbl tbody').append(
                            `<tr id="productRow" class="misc" style="background-color:rgba(229, 241, 101, 0.4);">
                            <td><input type="text" class="form-control numbering" name="numbering[]" id="numbering-p0" value="${v.numbering}"></td>
                            <td>
                                <textarea name="product_name[]" id="name-p0" cols="35" rows="2" class="form-control" placeholder="{{ trans('general.enter_product') }}" required>${v.product_name}</textarea>
                            </td>
                            <td><input type="text" name="unit[]" id="unit-p0" class="form-control" value="${v.unit}"></td>
                            <td ><input type="number" class="form-control estqty" name="estimate_qty[]" value="${+v.estimate_qty}" id="estqty-p0" step="0.1" style="border:solid #f5a8a2;" required></td>  
                            <td ><input type="text" class="form-control buyprice" name="buy_price[]" value="${+v.buy_price}" id="buyprice-p0"  style="border:solid #f5a8a2;" readonly></td>  
                            <td><input type="number" class="form-control qty invisible" name="product_qty[]" value="${+v.product_qty}" id="qty-p0" step="0.1" required ></td>
                            <td><input type="text" class="form-control rate invisible" name="product_subtotal[]" value="${+v.product_subtotal}" id="rate-p0" required></td>
                            <td>
                                <div class="row no-gutters">
                                    <div class="col-6">
                                        <input type="text" class="form-control price" value="${+v.product_price}" name="product_price[]" id="price-p0" readonly>
                                    </div>
                                    <div class="col-6">
                                        <select class="custom-select tax_rate" name="tax_rate[]" id="taxrate-p0">
                                            @foreach ($additionals as $item)
                                                <option value="{{ +$item->value }}" ${accounting.unformat(v.tax_rate) == {{ +$item->value }} ? 'selected' : ''}>{{ $item->value == 0 ? 'OFF' : +$item->value . '%' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td class='text-center'>
                                <span class="amount" id="amount-p0">${+v.product_amount}</span>&nbsp;&nbsp;
                                <span class="lineprofit text-info" id="lineprofit-p0">0%</span>
                            </td>
                            <td class="text-center">
                                @include('focus.quotes.partials.action-dropdown')
                            </td>
                            <input type="hidden" name="misc[]" value="1" id="misc-p0">
                            <input type="hidden" name="product_id[]" value="${v.product_id}" id="productid-p0">
                            <input type="hidden" class="index" name="row_index[]" value="${v.row_index}" id="rowindex-p0">
                            <input type="hidden" name="a_type[]" value="1" id="atype-p0">
                            <input type="hidden" name="product_type[]" value="inventory_product" id="product_type-p0">
                            <input type="hidden" name="client_product_id[]" value="0" id="client_product_id-p0">
                            <input type="hidden" name="id[]" value="0">
                        </tr>`);
                    } else if (v.a_type === 2) {
                        $('#quoteTbl tbody').append(`
                        <tr id="titleRow">
                            <td><input type="text" class="form-control numbering" name="numbering[]" id="numbering-t1" value="${v.numbering}" style="font-weight: bold;"></td>
                            <td colspan="8">
                                <input type="text"  class="form-control" name="product_name[]" value="${v.product_name}" id="name-t1" style="font-weight: bold;" required>
                            </td>
                            <td class="text-center">
                                @include('focus.quotes.partials.action-dropdown')
                            </td>
                            <input type="hidden" name="misc[]" value="0" id="misc-t1">
                            <input type="hidden" name="product_id[]" value="0" id="productid-t1">
                            <input type="hidden" name="unit[]">
                            <input type="hidden" name="product_qty[]" value="0">
                            <input type="hidden" name="product_price[]" value="0">
                            <input type="hidden" name="tax_rate[]" value="0">
                            <input type="hidden" name="product_subtotal[]" value="0">
                            <input type="hidden" name="estimate_qty[]" value="0">
                            <input type="hidden" name="buy_price[]" value="0">
                            <input type="hidden" class="index" name="row_index[]" value="0" id="rowindex-t1">
                            <input type="hidden" name="a_type[]" value="2" id="atype-t1">
                            <input type="hidden" name="product_type[]" value="title" id="product_type-t1">
                            <input type="hidden" name="client_product_id[]" value="0" id="client_product_id-t1">
                            <input type="hidden" name="id[]" value="0">
                        </tr>
                        `);
                    }
                });
                calcTotal();
                $('#tax_id').change();
            }
        });
    });
</script>
