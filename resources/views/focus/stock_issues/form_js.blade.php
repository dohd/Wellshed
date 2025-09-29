{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        autoCompleteCb: () => {
            return {
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('biller.products.quote_product_search') }}",
                        data: {keyword: request.term, is_stock_issue: 1},
                        method: 'POST',
                        success: result => response(result.map(v => ({
                            label: `${v.name}`,
                            value: v.name,
                            data: v
                        }))),
                    });
                },
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    const {data} = ui.item;
                    let row = Index.currRow;
                    row.find('.prodvar-id').val(data.id);
                    row.find('.qty-onhand').text(accounting.unformat(data.qty));
                    row.find('.qty-onhand-inp').val(accounting.unformat(data.qty));
                    row.find('.qty-rem').text(accounting.unformat(data.qty));
                    row.find('.qty-rem-inp').val(accounting.unformat(data.qty));
                    row.find('.cost').val(accounting.unformat(data.purchase_price));
                    if (data.units && data.units.length) {
                        const unit = data.units[0];
                        row.find('.unit').text(unit.code);
                    }
                    if (data.warehouses && data.warehouses.length) {
                        row.find('.source option:not(:first)').remove();
                        data.warehouses.forEach(v => {
                            const productsQty = accounting.unformat(v.products_qty);
                            row.find('.source').append(`<option value="${v.id}" products_qty="${productsQty}">${v.title} (${productsQty})</option>`)
                        });
                    }
                    if ($('#issue_to').val() == 'Employee') row.find('.assignee').attr('disabled', true);
                    else row.find('.assignee').attr('disabled', false);
                    // on edit
                    row.find('.issue-qty').attr('readonly', false);
                    row.find('.source-inp').remove();
                    row.find('.source').attr('disabled', false);
                }
            };
        },
        invoiceSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.stock_issues.select_invoices') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customer").val()}),
                processResults: data => {
                    return { 
                        results: data.map(v => ({
                            text: v.notes,
                            id: v.id
                        }))
                    }
                },
            }
        },
    };

    const Index = {
        currRow: '',
        sourceTd: '',
        assigneeTd: '',
        quoteOpts: '',
        initRow: '',
        rowIndex: 0,

        init() {
            $('#productsTbl tbody td').css({paddingLeft: '5px', paddingRight: '5px', paddingBottom: 0});
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#account').select2({ allowClear: true });
            $('#invoice').select2(config.invoiceSelect);
            Index.initRow = $('#productsTbl tbody tr:first');
            Index.quoteOpts = $('#quote option:not(:first)').clone();
            ['#employee', '#customer', '#project', '#quote','#purchase_requisition','#assign_to_ids','#finished_goods'].forEach(v => $(v).select2({allowClear: true}));

            Index.sourceTd = $('.td-source:first').clone();
            Index.assigneeTd = $('.td-assignee:first').clone();
            $('#name-1').autocomplete(config.autoCompleteCb());
            ['#source-1', '#assignee-1'].forEach(v => $(v).select2({allowClear: true}));

            $('#add-item').click(Index.addItemClick);
            $('#issue_to').change(Index.issueToChange);
            $('#quote').change(Index.quoteChange);
            $('#customer').change(Index.customerChange);
            $('#project').change(Index.projectChange);
            $('#ref').change(Index.referenceChange).change();
            $('#invoice').change(Index.invoiceChange);
            $('#employee').change(Index.employeeChange);
            $('#finished_goods').change(Index.finishedGoodsChange);

            $('#productsTbl').on('change', '.issue-qty, .source', Index.qtyCostKeyUp);
            $('#productsTbl').on('keyup', '.name', function() { Index.currRow = $(this).parents('tr') });
            $('#productsTbl').on('click', '.remove', Index.removeRowClick);            

            const data = @json(@$stock_issue);
            const data_items = @json(@$stock_issue->items);
            if (data && data_items.length) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                $('#issue_to').val(data.issue_to);
                const requisition_name = "{{ gen4tid('REQ-',@$stock_issue->purchase_request->tid).' '.@$stock_issue->note }}";
                const requisition_id = "{{ @$stock_issue->requisition_id }}";
                $('#requisition').append(new Option(requisition_name, requisition_id, true, true));
                $('#requisition').select2({allowClear: true});
                $('#requisition').attr('disabled', true);
                $('#productsTbl tbody tr').each(function(i) {
                    const row = $(this);
                    const v = data_items[i];
                    if (i > 0) {
                        row.find('.name').autocomplete(config.autoCompleteCb());
                        row.find('.source').select2({allowClear: true});
                        row.find('.assignee').select2({allowClear: true});
                    }
                    row.find('.qty-onhand-inp').val(v.qty_onhand*1);
                    row.find('.qty-rem-inp').val(v.qty_rem*1);
                    row.find('.cost').val(v.cost*1);
                    row.find('.amount').val(v.amount*1);
                    row.find('.prodvar-id').val(v.productvar_id);
                });
                if (data.issue_to == 'Employee') {
                    $('#employee').parents(".select-col").removeClass('d-none');
                    $('#productsTbl .assignee').attr('disabled', true);
                } 
                else if (data.issue_to == 'Customer') {
                    $('#customer').parents(".select-col").removeClass('d-none');
                    $('#employee').parents(".select-col").addClass('d-none');
                    $('#productsTbl .assignee').attr('disabled', false);
                } 
                else if (data.issue_to == 'Finished Goods') {
                    $('#finished_goods').parents(".select-col").removeClass('d-none');
                    $('#employee').parents(".select-col").addClass('d-none');
                    $('#productsTbl .assignee').attr('disabled', false);
                } 
                else {
                    $('#project').parents(".select-col").removeClass('d-none');
                    $('#employee').parents(".select-col").addClass('d-none');
                    $('#productsTbl .assignee').attr('disabled', false);

                }
                Index.calcTotals();
            } else {
                $('#requisition').select2({allowClear: true});
            }

            getBudgetLinesByQuote($('#quote').val());
        },

        referenceChange() {
            if (this.value == 'invoice') {
                $('.quote-col').addClass('d-none');
                $('.invoice-col').removeClass('d-none');
                $('.requisition_col').addClass('d-none');
            } else if(this.value == 'requisition')
            {
                $('.quote-col').addClass('d-none');
                $('.invoice-col').addClass('d-none');
                $('.requisition_col').removeClass('d-none');
            }
            else {
                $('.quote-col').removeClass('d-none');
                $('.invoice-col').addClass('d-none');
                $('.requisition_col').addClass('d-none');
            }
        },

        quoteChange() {
            $('#productsTbl tbody tr:not(:first)').remove();
            $('#productsTbl .remove').click();
            $('#total').val('');
            $('#productsTbl tbody').html('');
            if (this.value) {
                let quote_id = this.value;
                $.post("{{ route('biller.stock_issues.quote_pi_products') }}", { quote_id: quote_id })
                .done(data => {
                    try {
                        data.productvars.forEach((v,i) => {
                            $('#productsTbl tbody').append(Index.productRow(v,i));
                            console.log(i+1);
                            const selectElement = $(`#productsTbl tbody tr:last-child #source-${i + 1}`);
                            const selectElement1 = $(`#productsTbl tbody tr:last-child #assignee-${i + 1}`);
                            // Clear existing options except the first one
                            selectElement.find('option:not(:first)').remove();
                            // selectElement1.find('option:not(:first)').remove();
                            if (v.warehouses && v.warehouses.length) {
                                v.warehouses.forEach(warehouse => {
                                    const productsQty = accounting.unformat(warehouse.products_qty);
                                    selectElement.append(`<option value="${warehouse.id}" products_qty="${productsQty}">${warehouse.title} (${productsQty})</option>`);
                                });
                            }
                            // Reinitialize plugins if necessary
                            if (selectElement.hasClass('source')) {
                                selectElement.select2({
                                    placeholder: selectElement.data('placeholder')
                                });
                            }
                            if (selectElement1.hasClass('assignee')) {
                                selectElement1.select2({
                                    placeholder: selectElement1.data('placeholder')
                                });
                            }
                            if ($('#issue_to').val() == 'Employee') $('#productsTbl tbody tr').find('.assignee').attr('disabled', true);
                            else $('#productsTbl tbody tr').find('.assignee').attr('disabled', false);
                        });
                        $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
                    } catch (error) {
                        console.error("An error occurred while processing the data: ", error);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Request failed: ", textStatus, errorThrown);
                    console.error("Response details: ", jqXHR.responseText);
                    console.error("Request body: ", this.data);
                });

            }
        },

        productRow(v,i){
            let unit = v.unit ? v.unit.code : '';
            return `
                 <tr>
                    <td><textarea id="name-${i+1}" class="form-control name" cols="30" rows="2" autocomplete="off" required readonly>${v.name}</textarea></td>
                    <td><span class="product-code">${v.code}</span></td>
                    <td><span class="unit">${unit}</span></td>
                    <td><span class="budget">${parseFloat(v.budget_qty).toFixed(2)}</span></td>
                    <td><span class="booked">${parseFloat(v.booked_qty).toFixed(2)}</span></td>
                    <td><span class="issued">${parseFloat(v.issued_qty).toFixed(2)}</span></td>
                    <td><span class="requested">${parseFloat(v.requested_qty).toFixed(2)}</span></td>
                    <td><span class="qty-onhand">${accounting.unformat(v.qty)}</span></td>
                    <td><span class="qty-rem">${accounting.unformat(v.qty)}</span></td>
                    <td><input type="text" name="issue_qty[]" class="form-control issue-qty" autocomplete="off"></td>
                    <td class="td-source">
                        <select name="warehouse_id[]" id="source-${i+1}" class="form-control source" data-placeholder="Search Location">
                            <option value=""></option>
                        </select>
                    </td>
                    <td class="td-assignee">
                        
                                <span class="badge badge-danger mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                            
                    </td>
                    <input type="hidden" name="qty_onhand[]" class="qty-onhand-inp" value="${accounting.unformat(v.qty)}">
                    <input type="hidden" name="qty_rem[]" class="qty-rem-inp" value="${accounting.unformat(v.qty)}">
                    <input type="hidden" name="booked_qty[]" class="booked_qty" value="${accounting.unformat(v.booked_qty)}">
                    <input type="hidden" name="budget_item_id[]" value="${v.budget_item_id}">
                    <input type="hidden" name="requisition_item_id[]" value="${v.requisition_item_id}">
                    <input type="hidden" name="item_id[]" class="item_id" value="${v.item_id}">
                    <input type="hidden" name="cost[]" class="cost" value="${accounting.unformat(v.purchase_price)}">
                    <input type="hidden" name="amount[]" class="amount">
                    <input type="hidden" name="productvar_id[]" class="prodvar-id" value="${v.id}">
                </tr>
            `;
        },
        invoiceRow(v,i){
            let unit = v.unit ? v.unit.code : '';
            return `
                 <tr>
                    <td><textarea id="name-${i+1}" class="form-control name" cols="30" rows="2" autocomplete="off" required readonly>${v.name}</textarea></td>
                    <td><span class="product-code">${v.code}</span></td>
                    <td><span class="unit">${v.uom}</span></td>
                    <td><span class="budget">0</span></td>
                    <td><span class="booked">0</span></td>
                    <td><span class="issued">0</span></td>
                    <td><span class="requested">0</span></td>
                    <td><span class="qty-onhand">${accounting.unformat(v.qty)}</span></td>
                    <td><span class="qty-rem">${accounting.unformat(v.qty)}</span></td>
                    <td><input type="text" name="issue_qty[]" class="form-control issue-qty" autocomplete="off"></td>
                    <td class="td-source">
                        <select name="warehouse_id[]" id="source-${i+1}" class="form-control source" data-placeholder="Search Location">
                            <option value=""></option>
                        </select>
                    </td>
                    <td class="td-assignee">
                        
                                <span class="badge badge-danger mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                           
                    </td>
                    <input type="hidden" name="qty_onhand[]" class="qty-onhand-inp" value="${accounting.unformat(v.qty)}">
                    <input type="hidden" name="qty_rem[]" class="qty-rem-inp" value="${accounting.unformat(v.qty)}">
                    <input type="hidden" name="booked_qty[]" class="booked_qty" value="0">
                    <input type="hidden" name="budget_item_id[]" value="0">
                    <input type="hidden" name="requisition_item_id[]" value="0">
                    <input type="hidden" name="item_id[]" class="item_id" value="0">
                    <input type="hidden" name="cost[]" class="cost" value="${accounting.unformat(v.purchase_price)}">
                    <input type="hidden" name="amount[]" class="amount">
                    <input type="hidden" name="productvar_id[]" class="prodvar-id" value="${v.id}">
                </tr>
            `;
        },

        invoiceChange() {
            $('#productsTbl tbody').html('');
            const url = "{{ route('biller.stock_issues.issue_invoice_items') }}";
            const params = {invoice_id: $(this).val()};
            $.post(url, params, data => {
                console.log(data);
                data.forEach((v,i) => {
                    $('#productsTbl tbody').append(Index.invoiceRow(v,i));
                    const selectElement = $(`#productsTbl tbody tr:last-child #source-${i + 1}`);
                    const selectElement1 = $(`#productsTbl tbody tr:last-child #assignee-${i + 1}`);

                    // Clear existing options except the first one
                    selectElement.find('option:not(:first)').remove();
                    // selectElement1.find('option:not(:first)').remove();
                    if (v.warehouses && v.warehouses.length) {
                        v.warehouses.forEach(warehouse => {
                            const productsQty = accounting.unformat(warehouse.products_qty);
                            selectElement.append(`<option value="${warehouse.id}" products_qty="${productsQty}">${warehouse.title} (${productsQty})</option>`);
                        });
                    }

                    // Reinitialize plugins if necessary
                    if (selectElement.hasClass('source')) {
                        selectElement.select2({
                            placeholder: selectElement.data('placeholder')
                        });
                    }
                    if (selectElement1.hasClass('assignee')) {
                        selectElement1.select2({
                            placeholder: selectElement1.data('placeholder')
                        });
                    }
                    if ($('#issue_to').val() == 'Employee') $('#productsTbl tbody tr').find('.assignee').attr('disabled', true);
                    else $('#productsTbl tbody tr').find('.assignee').attr('disabled', false);
                });  
                $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});              
            });
        },

        typeChange(type, value){
            $.ajax({
                url: "{{ route('biller.purchase_requisitions.get_pr_requisitions')}}",
                method: "POST",
                data: {
                    type: type,
                    id: value,
                },
                success: function(data){
                    var select = $('#purchase_requisition');
                    select.empty();
                    select.append($('<option>', {
                        value: '',
                        text: 'Search Purchase Requisition'
                    }));

                    if (data.length === 0) {
                        select.append($('<option>', {
                            value: '',
                            text: 'No Purchase Requisition available'
                        }));
                    } else {
                        $.each(data, function(index, option) {
                            select.append($('<option>', { 
                                value: option.id,
                                text : option.name,
                            }));
                        });
                    }
                }
            });
        },

        finishedGoodsChange(){
            let type = $('#issue_to').val();
            let type_text = $('#finished_goods option:selected').val();
            const value = this.value;
            Index.typeChange(type, value);
            $('#purchase_requisition').val('').change();
        },
        employeeChange(){
            let type = $('#issue_to').val();
            let type_text = $('#issue_to option:selected').text();
            const value = this.value;
            if(type_text == 'Default'){
                Index.typeChange(type_text, value) 
            }else{

                Index.typeChange(type, value)
            }
            $('#purchase_requisition').val('').change();
        },

        customerChange() {
            $('#quote option:not(:first)').remove();

            const customerId = this.value;
            const selectedType = 'standard' // e.g., 'project' or 'standard'

            if (!customerId) return;

            Index.quoteOpts.each(function () {
                const quoteCustomerId = $(this).attr('customer_id');
                const quoteType = $(this).attr('quote_type'); // data-type="project" or "standard"

                if (quoteCustomerId == customerId && quoteType == selectedType) {
                    $('#quote').append($(this));
                }
            });

            const issueTo = $('#issue_to').val();
            Index.typeChange(issueTo, customerId);
            $('#purchase_requisition').val('').change();
        },


        projectChange() {
            $('#quote option:not(:first)').remove();
            let type = $('#issue_to').val();
            if (!this.value) return;
            $('#budget_line').attr('disabled', false);
            let quoteIds = $(this).find(':selected').attr('quote_ids');
            quoteIds = quoteIds.split(',');

            Index.quoteOpts.each(function() {
                let value = $(this).attr('value');
                let quoteType = $(this).attr('quote_type'); // assuming you have this attribute
                if (quoteIds.includes(value) && quoteType === 'project') {
                    $('#quote').append($(this));
                }
            });

            Index.typeChange(type, this.value);
            $('#purchase_requisition').val('').change();
        },


        issueToChange() {
            const data = @json(@$stock_issue);
            $('.select-col').addClass('d-none');
            $('#productsTbl .assignee').attr('disabled', false);
            if(!data){
                $('#ref').attr('disabled', false);
                $('#quote').attr('disabled', false);
            }

            if (this.value == 'Employee') {
                $('#employee').parents(".select-col").removeClass('d-none');
                $('#productsTbl .assignee').attr('disabled', true);
                
                // Clear project and customer select2 values
                $('#project').val(null).trigger('change');
                $('#customer').val(null).trigger('change');
                $('#finished_goods').val(null).trigger('change');
            } 
            else if (this.value == 'Customer') {
                $('#customer').parents(".select-col").removeClass('d-none');
                
                // Clear employee and project select2 values
                $('#employee').val(null).trigger('change');
                $('#project').val(null).trigger('change');
                $('#finished_goods').val(null).trigger('change');
            } 
            else if (this.value == 'Finished Goods') {
                console.log(this.value);
                $('#finished_goods').parents(".select-col").removeClass('d-none');
                
                // Clear employee and project select2 values
                $('#employee').val(null).trigger('change');
                $('#project').val(null).trigger('change');
                $('#customer').val(null).trigger('change');
            } 
            else {
                $('#project').parents(".select-col").removeClass('d-none');
                
                // Clear employee and customer select2 values
                $('#employee').val(null).trigger('change');
                $('#customer').val(null).trigger('change');
                $('#finished_goods').val(null).trigger('change');
            }
        },


        addItemClick() {
            let row = $('#productsTbl tbody tr:last').clone();
            let indx = accounting.unformat(row.find('.name').attr('id').split('-')[1]);
            row.find('input').attr('value', '');
            row.find('textarea').text('');
            row.find('.unit, .qty-onhand, .qty-rem').text('');
            row.find('.name').attr('id', `name-${indx+1}`);

            let sourceTd = Index.sourceTd.clone();
            let assigneeTd = Index.assigneeTd.clone();
            row.find('.td-source').children().remove();
            row.find('.td-assignee').children().remove();
            row.find('.td-source').append(sourceTd.children());
            row.find('.td-assignee').append(assigneeTd.children());
            row.find('.source').attr('id', `source-${indx+1}`);
            row.find('.assignee').attr('id', `assignee-${indx+1}`);

            $('#productsTbl tbody').append(`<tr>${row.html()}</tr>`);
            row = $('#productsTbl tbody tr:last');
            row.find('.name').autocomplete(config.autoCompleteCb());
            row.find('.source').select2({allowClear: true});
            row.find('.assignee').select2({allowClear: true});
            if ($('#issue_to').val() == 'Employee') row.find('.assignee').attr('disabled', true);
            else row.find('.assignee').attr('disabled', false);
        },

        removeRowClick() {
            let row = $(this).parents('tr');
            if (!row.siblings().length) {
                row.find('input, textarea, select').each(function() { $(this).val('').change() });
                row.find('.unit, .qty-onhand, .qty-rem').text('');
                row.find('.source option:not(:first)').remove();
            } else row.remove();
            Index.calcTotals();
        },

        qtyCostKeyUp() {
            const row = $(this).parents('tr');
            const cost = accounting.unformat(row.find('.cost').val());
            const sourceQty = accounting.unformat(row.find('.source option:selected').attr('products_qty'));
            let qtyOnhand = accounting.unformat(row.find('.qty-onhand').text());
            let requested_qty = accounting.unformat(row.find('.requested').text());
            let booked = accounting.unformat(row.find('.booked').text());
            let issued = accounting.unformat(row.find('.issued').text());
            let issueQty = accounting.unformat(row.find('.issue-qty').val());
            if (issueQty < 0) issueQty = 0;
            let qtyRem = 0;

            // console.log({issueQty});
            if (row.find('.source').val()) {
                // console.log({sourceQty});
                qtyOnhand = sourceQty;
                qtyRem = sourceQty;
            }
            if(booked > 0){
                // console.log({issueQty, booked});
                const qty_to_issue = booked + issued;
                if (requested_qty > qty_to_issue && qty_to_issue <= qtyOnhand){
                    issueQty = qty_to_issue;
                } else if (requested_qty <= qtyOnhand) {
                    issueQty = requested_qty;
                }
            }
            // if(qtyOnhand > requested_qty){
            //     // console.log('qtyOnhand > requested_qty', {qtyOnhand, requested_qty, issueQty});
            //     if (requested_qty > issueQty) {
            //         issueQty = requested_qty
            //     } else if (issueQty > qtyOnhand) {
            //         issueQty = qtyOnhand 
            //     }else if(issueQty > requested_qty) {
            //         issueQty = issueQty;
            //     }
            // } else {
            //     // console.log('qtyOnhand < requested_qty');
            //     issueQty = qtyOnhand;
            // }

            if(requested_qty <= issueQty && requested_qty <= qtyOnhand)
            {
                issueQty = requested_qty;
            }
            else if(requested_qty <= issueQty && requested_qty >= qtyOnhand)
            {
                if(qtyOnhand == 0) {
                    issueQty = issueQty
                }else issueQty = qtyOnhand;
            }
            else if(requested_qty >= issueQty && requested_qty <= qtyOnhand)
            {
                issueQty = issueQty;
            }
            else if(requested_qty >= issueQty && requested_qty >= qtyOnhand)
            {
                if(qtyOnhand == 0) {
                    issueQty = issueQty
                }else issueQty = qtyOnhand;
            }
            
            if(qtyOnhand <= 0) issueQty = issueQty
            
            const amount = issueQty * cost;
            qtyRem = qtyOnhand - issueQty;

            row.find('.issue-qty').val(issueQty);
            row.find('.qty-onhand').text(qtyOnhand);
            row.find('.qty-onhand-inp').val(qtyOnhand);
            row.find('.qty-rem').text(qtyRem);
            row.find('.qty-rem-inp').val(qtyRem);
            row.find('.amount').val(accounting.formatNumber(amount));
            Index.calcTotals();
        },

        calcTotals() {
            let total = 0;
            $('#productsTbl tbody tr').each(function() {
                const amount = accounting.unformat($(this).find('.amount').val());
                total += amount;
            });
            $('#total').val(accounting.formatNumber(total));
        },


    };

    function getBudgetLinesByQuote(quoteId = null){
        if(!quoteId) return;
        //console.log(quoteId);
        $("#requisition").empty();
        $.ajax({
            url: "{{ route('biller.getBudgetLinesByQuote') }}",
            method: 'GET',
            data: { quoteId: quoteId},
            dataType: 'json', // Adjust the data type accordingly
            success: function(data) {
                // This function will be called when the AJAX request is successful
                var select = $('#budget_line');
                // Clear any existing options
                select.empty();
                if(data.length) console.table(data);
                else console.log('No Budget Lines Created For This Project');
                if(data.length === 0){
                    select.append($('<option>', {
                        value: null,
                        text: 'No Budget Lines Created For This Project'
                    }));
                    select.append($('<option>', {
                        value: 0,
                        text: 'Unallocated Milestones'
                    }));
                } else {
                    select.append($('<option></option>').attr('value', null).text('Select a Budget Line'));
                    select.append($('<option>', {
                        value: 0,
                        text: 'Unallocated Milestones'
                    }));
                    // Add new options based on the received data
                    for (var i = 0; i < data.length; i++) {
                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        const date = new Date(data[i].end_date);
                        select.append($('<option>', {
                            value: data[i].id,
                            text: data[i].name + ' | Balance: ' +  parseFloat(data[i].balance).toFixed(2) + ' | Due on ' + date.toLocaleDateString('en-US', options)
                        }));
                    }
                    let selectedOptionValue = "{{ @$stock_issue->budget_line }}";
                    if (selectedOptionValue) {
                        select.val(selectedOptionValue);
                    }
                    checkBudgetLine(select.find('option:selected').text());
                }
            },
            error: function(error) {
                // Handle errors here
                console.log(error);
            }
        });
    }

    function checkBudgetLine(budgetLineString){
        // Get the value of the input field
        let selectedBudgetLine = budgetLineString;
        // console.log("SELECTED MILESTONE IS : " + budgetLineString);
        if(budgetLineString === 'Select a Budget Line') {
            // console.log("NO MILESTONE SELECTED!!")
            return false;
        }
        // Specify the start and end strings
        let startString = 'Balance: ';
        let endString = ' | Due on';
        // Find the index of the start and end strings
        let startIndex = selectedBudgetLine.indexOf(startString);
        let endIndex = selectedBudgetLine.indexOf(endString, startIndex + startString.length);
        // Extract the string between start and end
        let budgetLineBudget = parseFloat(parseFloat(selectedBudgetLine.substring(startIndex + startString.length, endIndex)).toFixed(2));
        // //console.log("Budget Line Budget is " + budgetLineBudget + " and purchase total is " + purchaseGrandTotal);
        const stockIssueTotal = parseFloat($("#total").val().replace(/,/g, ''));
        // console.table({budgetLineBudget, stockIssueTotal});
        if(stockIssueTotal > budgetLineBudget){
            // //console.log( "Budget Line Budget is " + budgetLineBudget );
            // //console.log( "Budget Line Budget Exceeded" );
            $("#budget_line_warning").text("Budget Line of " + budgetLineBudget + " Exceeded!");
        }
        else {
            $("#budget_line_warning").text("");
        }
    }

    $('#budget_line').change(function() {
        checkBudgetLine($(this).find('option:selected').text());
        const milestone_id = $(this).find('option:selected').val();
        const quote_id = $('#quote').find('option:selected').val();
        $.ajax({
            method: 'POST',
            url: "{{ route('biller.milestones.get_requisitions') }}",
            data: {
                milestone_id: milestone_id,
                quote_id: quote_id,
            },
            success: function (data){
                var select = $('#requisition');
                select.empty();
                select.append($('<option>', {
                    value: '',
                    text: 'Search Requisition',
                    disabled: true,
                    selected: true
                }));
                $.each(data, function(index, option) {
                    select.append($('<option>', { 
                        value: option.id,
                        text : option.name,
                    }));
                });
            }
        });
    });
    

    $('#purchase_requisition').change(function(){
        const requisition_id = $(this).val();
        $('#productsTbl tbody').html('');
        $.ajax({
            method: 'POST',
            url: "{{ route('biller.purchase_requisitions.get_requisition_items') }}",
            data: {
                requisition_id: requisition_id,
            },
            success: function (data){
                try {
                        data.forEach((v,i) => {
                            $('#productsTbl tbody').append(Index.productRow(v,i));
                            console.log(i+1);
                            const selectElement = $(`#productsTbl tbody tr:last-child #source-${i + 1}`);
                            const selectElement1 = $(`#productsTbl tbody tr:last-child #assignee-${i + 1}`);
                            // Clear existing options except the first one
                            selectElement.find('option:not(:first)').remove();
                            // selectElement1.find('option:not(:first)').remove();
                            if (v.warehouses && v.warehouses.length) {
                                v.warehouses.forEach(warehouse => {
                                    const productsQty = accounting.unformat(warehouse.products_qty);
                                    selectElement.append(`<option value="${warehouse.id}" products_qty="${productsQty}">${warehouse.title} (${productsQty})</option>`);
                                });
                            }
                            // Reinitialize plugins if necessary
                            if (selectElement.hasClass('source')) {
                                selectElement.select2({
                                    placeholder: selectElement.data('placeholder')
                                });
                            }
                            if (selectElement1.hasClass('assignee')) {
                                selectElement1.select2({
                                    placeholder: selectElement1.data('placeholder')
                                });
                            }
                            if ($('#issue_to').val() == 'Employee') $('#productsTbl tbody tr').find('.assignee').attr('disabled', true);
                            else $('#productsTbl tbody tr').find('.assignee').attr('disabled', false);
                        });
                    } catch (error) {
                        console.error("An error occurred while processing the data: ", error);
                    }
            }
        });
    });
    $('#requisition').change(function(){
        const requisition_id = $(this).val();
        $('#productsTbl tbody').html('');
        $.ajax({
            method: 'POST',
            url: "{{ route('biller.purchase_requests.get_requisition_items') }}",
            data: {
                requisition_id: requisition_id,
            },
            success: function (data){
                try {
                        data.forEach((v,i) => {
                            $('#productsTbl tbody').append(Index.productRow(v,i));
                            console.log(i+1);
                            const selectElement = $(`#productsTbl tbody tr:last-child #source-${i + 1}`);
                            const selectElement1 = $(`#productsTbl tbody tr:last-child #assignee-${i + 1}`);
                            // Clear existing options except the first one
                            selectElement.find('option:not(:first)').remove();
                            // selectElement1.find('option:not(:first)').remove();
                            if (v.warehouses && v.warehouses.length) {
                                v.warehouses.forEach(warehouse => {
                                    const productsQty = accounting.unformat(warehouse.products_qty);
                                    selectElement.append(`<option value="${warehouse.id}" products_qty="${productsQty}">${warehouse.title} (${productsQty})</option>`);
                                });
                            }
                            // Reinitialize plugins if necessary
                            if (selectElement.hasClass('source')) {
                                selectElement.select2({
                                    placeholder: selectElement.data('placeholder')
                                });
                            }
                            if (selectElement1.hasClass('assignee')) {
                                selectElement1.select2({
                                    placeholder: selectElement1.data('placeholder')
                                });
                            }
                            if ($('#issue_to').val() == 'Employee') $('#productsTbl tbody tr').find('.assignee').attr('disabled', true);
                            else $('#productsTbl tbody tr').find('.assignee').attr('disabled', false);
                        });
                    } catch (error) {
                        console.error("An error occurred while processing the data: ", error);
                    }
            }
        });
    });

    $('#total').change(function() {
        checkBudgetLine($('#budget_line').find('option:selected').text());
    });
    $('.issue-qty').change(function() {
        checkBudgetLine($('#budget_line').find('option:selected').text());
    });
    $('#quote').change(function() {
        $("#budget_line_warning").text("");
        getBudgetLinesByQuote($(this).val());
    });
    $(Index.init);
</script>
