<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});
    // html editor
    editor();

    const quote = @json($quote);
    $('.datepicker').datepicker({ format: "{{ config('core.user_date_format') }}" })
    if (quote.date) $('#date').datepicker('setDate', new Date(quote.date));

    $('#refreshButton').click(function() {
        var alphabet = 'A'.charCodeAt(0); // Start at 'a'
        var number = 1;                   // Start at 1

        $('#productsTbl tbody tr').each(function(index) {
            // Check the hidden input value to determine if it's a title or product
            var type = $(this).find('input[name="a_type[]"]').val(); 
            
            if (type === "2") {
                // Assign alphabetical value for titles
                $(this).find('.numbering').val(String.fromCharCode(alphabet));
                alphabet++; // Move to the next letter
                number = 1; // Reset numbering for the next section
            } else {
                // Assign numerical value for products
                $(this).find('.numbering').val(number);
                number++; // Increment the number
            }
        });
    });

    // skill row html
    function skillRow(n) {
        return `
            <tr>
                <td class="text-center">${n+1}</td>
                <td>
                    <select class="form-control update" name="skill[]" id="skill-${n}">
                        <option value="" class="text-center">-- Select Skill Type --</option>                        
                        <option value="casual">Casual</option>
                        <option value="contract">Contract</option>
                        <option value="attachee">Attachee</option>
                        <option value="outsourced">Outsourced</option>
                    </select>
                </td>
                <td><input type="number" class="form-control update" name="charge[]" id="charge-${n}" readonly></td>
                <td><input type="number" class="form-control update" name="hours[]" id="hours-${n}" ></td>               
                <td><input type="number" class="form-control update" name="no_technician[]" id="notech-${n}"></td>
                <td class="text-center"><span>0</span></td>
                <td><button type="button" class="btn btn-primary removeItem">Remove</button></td>
                <input type="hidden" name="skillitem_id[]" value="0" id="skillitemid-${n}">
            </tr>
        `;
    }

    // row dropdown menu
    function dropDown(n) {
        return `
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item up" href="javascript:void(0);">Up</a>
                    <a class="dropdown-item down" href="javascript:void(0);">Down</a>
                    <a class="dropdown-item removeItem text-danger" onclick="return confirm('Are you Sure?');" href="javascript:void(0);">Remove</a>
                </div>
            </div>            
        `;
    }

    // product row html
    function productRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control numbering no" name="numbering[]" id="numbering-${n}" required></td>
                <td><input type="text" class="form-control name" name="product_name[]" id="itemname-${n}" required></td>
                <td><span id="product_code-${n}"></span></td>
                <td><input type="number" class="form-control qty" name="product_qty[]" value="0" id="amount-${n}" readonly></td>                
                <td>
                    <div class="row no-gutters">
                        <div class="col-6"><input type="text" class="form-control unit" name="unit[]" id="unit-${n}"></div>
                        <div class="col-6">
                            <select type="text" class="custom-select unit-select" name="unit[]" id="unitselect-${n}">
                                <option value="">None</option>
                            </select>
                        </div>
                    </div>
                </td>                
                <td><input type="text" class="form-control new-qty" name="new_qty[]" id="newqty-${n}" required></td>
                <td><input type="text" class="form-control price" name="price[]" id="price-${n}" required readonly></td>
                <td class="text-center amount">0</td>
                <td>${dropDown()}</td>
                <input type="hidden" name="product_id[]" value="0" id="productid-${n}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
                <input type="hidden" class="row-index" name="row_index[]" value="${n}" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${n}"> 
                <input type="hidden" name="misc[]" value="0" id="misc-${n}"> 
            </tr>
        `;
    }

    // title row html
    function titleRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control numbering" name="numbering[]" id="numbering-${n}" required></td>
                <td colspan="7"><input type="text" class="form-control" name="product_name[]" id="itemname-${n}"></td>
                <td>${dropDown()}</td>
                <input type="hidden" name="product_id[]" value="0" id="productid-${n}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
                <input type="hidden" class="form-control" name="product_qty[]" value="0" id="amount-${n}">               
                <input type="hidden" class="form-control" name="unit[]" id="unit-${n}">               
                <input type="hidden" class="form-control update" name="new_qty[]" value="0" id="newqty-${n}">
                <input type="hidden" class="form-control update" name="price[]" value="0" id="price-${n}">
                <input type="hidden" class="row-index" name="row_index[]" value="${n}" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="2" id="atype-${n}"> 
                <input type="hidden" name="misc[]" value="0" id="misc-${n}"> 
            </tr>
        `;
    }

    $("#fill-btn").click(function() {
        let valueToFill = "TBD"; // Change this to any desired value
        $("#productsTbl .unit").each(function() {
            if ($(this).val().trim() === "") {
                $(this).val(valueToFill);
                $(this).change();
            }
        });
    });
    
    // add miscellaneous product
    let rowId = 1;
    const rowHtml = $("#productRow").html();
    $('#addMisc').click(function() {
        $('#productsTbl tbody tr.invisible').remove();

        const i = 'p' + rowId;
        //const newRowHtml = `<tr class="misc"> ${rowHtml.replace(/p0/g, i)} </tr>`;
        $('#productsTbl tbody').append(productRow(i));
        // $('#name-'+i).autocomplete(autoComp(i));
        $('#itemname-'+i).autocomplete(autocompleteProp(i));
        $('#misc-'+i).val(1);
        $('#qty-'+i).val(1);
        ['qty', 'rate', 'amount', 'lineprofit'].forEach(v => {
            $(`#${v}-${i}`).addClass('invisible');
        });
        rowId++;
        // calcTotal();
        // adjustTbodyHeight();
    });

    // On skill-item update
    $('#skill-item').on('change', '.update', function() {
        const id = $(this).attr('id');
        const i = id.split('-')[1]; 

        const labourCharge = $('#charge-'+i);
        const labourType = $('#skill-'+i);
         
        switch (labourType.val()) {
            case 'casual': labourCharge.val(200).attr('readonly', true); break;
            case 'contract': labourCharge.val(350).attr('readonly', true); break;
            case 'attachee': labourCharge.val(150).attr('readonly', true); break;
            case 'outsourced': labourCharge.attr('readonly', false); break;
        }
        
        const amount = $('#hours-'+i).val() * $('#notech-'+i).val() * $('#charge-'+i).val();
        $(this).parents('tr:first').find('span').text(accounting.formatNumber(amount));
        budgetTotal();
    });

    // default skill row
    let skillIndx = 0;
    let skillItems = @json($quote->skill_items);
    let budgetSkillItems = @json(@$budget->skillsets);
    if (budgetSkillItems) skillItems = budgetSkillItems;
    if (skillItems.length) {
        skillItems.forEach(v => {
            let i = skillIndx;
            $('#skill-item tbody').append(skillRow(i));
            $('#skillitemid-'+i).val(v.id);
            $('#skill-'+i).val(v.skill);
            $('#charge-'+i).val(v.charge);
            $('#hours-'+i).val(v.hours);
            $('#notech-'+i).val(v.no_technician);
            skillIndx++;
        });
        $('#charge-0').change();
    } else $('#skill-item tbody').append(skillRow(0));
    // on adding skill
    $('#add-skill').click(function() {
        $('#skill-item tbody').append(skillRow(skillIndx));
        skillIndx++;
    });
    // Remove skill row
    $('#skill-item').on('click', '.removeItem', function() {
        $(this).closest('tr').remove();
        budgetTotal();
    });

    // products table change
    $('#productsTbl').on('change', '.unit, .unit-select, .new-qty, .price', function() {
        const el = $(this);
        const row = el.parents('tr:first');

        const newQty = accounting.unformat(row.find('.new-qty').val());
        const price = accounting.unformat(row.find('.price').val());
        const amount = newQty * price;

        row.find('.new-qty').val(newQty);
        row.find('.price').val(accounting.formatNumber(price));
        row.find('.amount').text(accounting.formatNumber(amount));
        budgetTotal();

        if (el.is('.unit')) {
            if (el.val()) row.find('.unit-select').attr('disabled', true);
            else row.find('.unit-select').attr({
                disabled: false,
                required: true
            });
        } else if (el.is('.unit-select')) {
            if (el.val()) {
                const purchasePrice = el.find('option:selected').attr('purchase_price');
                row.find('.price').val(purchasePrice).change();
                row.find('.unit').attr('disabled', true);
            } else {
                row.find('.unit').attr({
                    disabled: false,
                    required: true
                });
            }
        }
    });

    // set default product rows
    let productIndx = 0;
    let quoteItems = @json($quote->products()->orderByRow()->with('product')->get());  
    let budgetItems = @json(@$budget->items);
    if (budgetItems) quoteItems = budgetItems;
    quoteItems.forEach(v => {
        let i = productIndx;
        if (v.a_type == 1) {
            // product type
            $('#productsTbl tbody').append(productRow(i));
            $('#itemname-'+i).autocomplete(autocompleteProp(i));

            $('#numbering-'+i).val(v.numbering);
            $('#itemid-'+i).val(v.id);
            $('#productid-'+i).val(v.product_id);
            $('#itemname-'+i).val(v.product_name);
            if(v.product) $('#product_code-'+i).text(v.product.code);
            $('#amount-'+i).val(parseFloat(v.product_qty));
            $('#newqty-'+i).val(parseFloat(v.estimate_qty || v.new_qty));
            $('#unit-'+i).val(v.unit).change();
            $('#price-'+i).val(accounting.formatNumber(v.buy_price || v.price)).change();
            $('#misc-'+i).val(v.misc);
        } else if (v.a_type == 2) {
            // title type
            $('#productsTbl tbody').append(titleRow(i));
            $('#numbering-'+i).val(v.numbering);
            $('#itemid-'+i).val(v.id);
            $('#itemname-'+i).val(v.product_name);
            $('#misc-'+i).val(v.misc);
        }
        productIndx++;
    });

    // add product row
    $('#add-product').click(function() {
        const i = productIndx;
        $('#productsTbl tbody').append(productRow(i));
        $('#itemname-'+i).autocomplete(autocompleteProp(i));
        productIndx++;
    });
    // add title row
    $('#add-title').click(function() {
        const i = productIndx;
        $('#productsTbl tbody').append(titleRow(i));
        productIndx++;
    });
    // on click product row menus
    $('#productsTbl').on('click', '.up, .down, .removeItem', function() {
        const $row = $(this).parents("tr:first");
        if ($(this).is('.up')) $row.insertBefore($row.prev());
        if ($(this).is('.down')) $row.insertAfter($row.next());        
        if ($(this).is('.removeItem')) $(this).closest('tr').remove();
        budgetTotal();
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}",
                    method: 'POST',
                    data: 'keyword=' + request.term,
                    success: function(data) {
                        response(data.map(v => ({
                            label: v.name,
                            value: v.name,
                            data: v
                        })));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                $('#productid-'+i).val(data.id);
                $('#itemname-'+i).val(data.name);
                $('#product_code-'+i).text(data.code);
                $('#unit-'+i).val('').change();    
                
                const purchasePrice = parseFloat(data.purchase_price);
                $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();

                $('#unitselect-' + i + ' option:not(:eq(0))').remove();
                data.units.forEach(v => {
                    const rate = parseFloat(v.base_ratio) * purchasePrice;
                    const option = `<option value="${v.code}" purchase_price="${rate}">${v.code}</option>`;
                    $('#unitselect-'+i).append(option);
                });
            }
        };
    }

    // total budget
    function budgetTotal() {
        let total = 0;
        let labourTotal = 0;
        $('#productsTbl tbody tr').each(function(i) {
            const amount = accounting.unformat($(this).find('.amount').text());
            total += amount;
            $(this).find('.row-index').val(i);
        });

        $('#skill-item tbody tr').each(function() {
            const spanText = $(this).find('td').eq(5).children().text();
            const amount = accounting.unformat(spanText);
            total += amount;
            labourTotal += amount;
        });
        $('#budget-total').val(accounting.formatNumber(total));
        $('#labour-total').val(accounting.formatNumber(labourTotal));

        // profit
        const quoteTotal = accounting.unformat($('#quote_total').val());
        const profit = quoteTotal - total;
        let pcent = profit/quoteTotal * 100;
        pcent = isFinite(pcent)? Math.round(pcent) : 0;
        $('.profit').text(accounting.formatNumber(profit) + ' : ' + pcent + '%');

        // budget limit
        $('.budget-alert').addClass('d-none');
        // if (total >= (quoteTotal * 0.7)) {
        //     $('.budget-alert').removeClass('d-none');
        //     scroll(0, 0);
        // }
    }
</script>
