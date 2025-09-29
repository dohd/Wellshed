{{ Html::script('focus/js/select2.min.js') }}
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        fetchSupplierOrders: (supplier_id) => {
            return $.ajax({
                url: "{{ route('biller.suppliers.purchaseorders') }}",
                type: 'POST',
                quietMillis: 50,
                data: {supplier_id, type: 'grn', currency_id: $('#currency').val()},
            });
        },
        fetchOrderProducts: (purchaseorder_id) => {
            return $.ajax({
                url: "{{ route('biller.purchaseorders.goods') }}",
                type: 'POST',
                quietMillis: 50,
                data: {purchaseorder_id, currency_id: $('#currency').val()},
            });
        },
        prediction: (url, callback) => {
            return {
                source: function(request, response) {
                    $.ajax({
                        url,
                        dataType: "json",
                        method: "POST",
                        data: {keyword: request.term, projectstock: $('#projectstock').val()},
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
                select: callback
            };
        },
        predict(url, callback) {
            return {
                source: function(request, response) {
                    $.ajax({
                        url,
                        dataType: "json",
                        method: "POST",
                        data: {keyword: request.term, pricegroup_id: $('#pricegroup_id').val(), is_expense: 1},
                        success: data => {
                            if (url.includes('accounts_select')) response(data.map(v => ({label: `${v.number} - ${v.holder} (${v.account_type})`, value: v.name, data: v})));
                            else response(data.map(v => ({label: v.name, value: v.name, data: v})));
                        }
                    });
                },
                autoFocus: true,
                minLength: 0,
                select: callback
            };
        }
    }
    

    const Form = {
        grn: @json(@$goodsreceivenote),
        projectStockRowId: 1,
        accountRowId: 1,
        projectstockUrl: "{{ route('biller.projects.project_search') }}",
        expUrl : "{{ route('biller.purchases.accounts_select') }}",

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#supplier').select2({allowClear: true});
            $('#purchaseorder').select2({allowClear: true});

            $('#supplier').change(Form.onSupplierChange);
            $('#currency').change(Form.onCurrencyChange);
            $('#purchaseorder').change(Form.purchaseorderChange);
            $('#tax_rate').change(() => Form.columnTotals());
            $('#invoice_status').change(Form.invoiceStatusChange);
            $('#productTbl').on('change', '.qty', Form.onQtyChange);
            // $('#productTbl').on('focus', '.projectstock', Form.projectMouse);
            $('#productTbl').on('change', '.warehouse', Form.warehouseChange);
            $('#productTbl').on('keyup', '.projectstock', Form.projectChange);
            $('#productTbl').on('keyup', '.accountname', Form.expenseChange);
            $('#jobcard').on('input', function () {
                if ($(this).val().trim() !== '') {
                    $('#dnote').removeAttr('required');
                } else {
                    $('#dnote').attr('required', 'required');
                }
            });
            Form.columnTotals();

            const grn = @json(@$goodsreceivenote);
            if (grn && grn.id) {
                if (grn.date) $('#date').datepicker('setDate', new Date(grn.date));
                else $('#date').val('');
                if (grn.invoice_date) $('#invoice_date').datepicker('setDate', new Date(grn.invoice_date));
                else $('#invoice_date').val('');
                $('#supplier').attr('disabled', true);
                $('#purchaseorder').attr('disabled', true);
                $('#currency').attr('disabled', true);
                $('#fx_curr_rate').attr('readonly', false);
                $('.projectstock').autocomplete(config.prediction(Form.projectstockUrl, Form.projectstockSelect));
                $('.accountname').autocomplete(config.predict(Form.expUrl, Form.expSelect));
                if (grn.invoice_no) {
                    $('#invoice_no').attr('disabled', false);
                    $('#invoice_date').attr('disabled', false);
                    $('#invoice_status option:eq(0)').remove();
                } else {
                    $('#invoice_status option:eq(1)').remove();
                }
            } 
        },

        expSelect(event, ui) {
            const {data} = ui.item;
            const row = $(this).parents('tr:first');
            if($(this).is('.accountname')) {
                // $('#expitemid-'+Form.accountRowId).val(data.id);
                row.find('.expitemid').val(data.id).change();
                row.find('.accountname').change();
            }
        },

        expenseChange() {
            const row = $(this).parents('tr:first');
            row.find('.stockitemprojectid').val(0).attr('readonly', false);
            row.find('.projectstock').val('').attr('readonly', true);
            row.find('.warehouse').val(0).attr('readonly', false);
            if ($(this).val()) {
                const expUrl = "{{ route('biller.purchases.accounts_select') }}";
                row.find('.accountname').autocomplete(config.predict(expUrl, Form.expSelect));
            } 
        },

        onCurrencyChange() {
            const rate = $(this).find('option:selected').attr('rate');
            $('#fx_curr_rate').val(rate);
            if (rate == 1) {
                $('#fx_curr_rate').attr('readonly', true);
            } else {
                $('#fx_curr_rate').attr('readonly', false);
            }
        },

        invoiceStatusChange() {
            const el = $(this);
            if (el.val() == 'with_invoice') {
                $('#invoice_no').val('').attr({'disabled': false, 'required': true});
                $('#invoice_date').val('').attr({'disabled': false, 'required': true});
            } else {
                $('#invoice_no').val('').attr({'disabled': true, 'required': false});
                $('#invoice_date').val('').attr({'disabled': true, 'required': false});
            }
        },

        onSupplierChange() {
            $('#purchaseorder option:not(:eq(0))').remove();
            $('#productTbl tbody').html('');
            $('#credit_limit').html('');

            // set currency
            const {currencyid, currencycode, currencyrate} = $(this).find(':selected')[0].attributes;
            $('#currency').html(`<option value="${currencyid?.value}" rate="${+currencyrate?.value}">${currencycode?.value}</option>`);
            $('#currency').val(currencyid?.value).change();

            const supplierId = this.value
            if (!supplierId) return;

            config.fetchSupplierOrders(supplierId).done(data => {
                data.forEach(v => {
                    let tid = `${v.tid}`.length < 4? `000${v.tid}`.slice(-4) : v.tid;
                    $('#purchaseorder').append(`<option value="${v.id}">PO-${tid} - ${v.note}</option>`);
                });
                $('#purchaseorder').change();
            });

            $.ajax({
                type: "POST",
                url: "{{route('biller.suppliers.check_limit')}}",
                data: {
                    supplier_id: supplierId,
                },
                success: function (result) {
                    let total = $('#total').val();
                    let number = total.replace(/,/g, '');
                    let newTotal = parseFloat(number);
                    let outstandingTotal = parseFloat(result.outstanding_balance);
                    let total_aging = parseFloat(result.total_aging);
                    let credit_limit = parseFloat(result.credit_limit);
                    let total_age_grandtotal = total_aging+newTotal;
                    let balance = total_age_grandtotal - outstandingTotal;
                    $('#total_aging').val(accounting.formatNumber(total_aging));
                    $('#credit').val(accounting.formatNumber(credit_limit));
                    $('#outstanding_balance').val(accounting.formatNumber(outstanding_balance));
                    if(balance > credit_limit && credit_limit > 0){
                        let exceeded = balance-result.credit_limit;
                        $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by: ${accounting.formatNumber(exceeded)}</h4>`);
                    }else{
                        $('#credit_limit').html('');
                    }
                }
            });
        },

        purchaseorderChange() {
            $('#productTbl tbody').html('');
            if (!this.value) return;
            config.fetchOrderProducts(this.value).done(data => {
                data.forEach((v,i) => {
                    $('#productTbl tbody').append(Form.productRow(v,i));
                    $('#productTbl tbody tr').find(`#warehouseid-${i+1} option`).each(function() {
                        if ($(this).val() == v.warehouse_id) {
                        $(this).prop("selected", true);
                        return false; // break out of the loop
                        }
                    });
                    let $warehouseSelect = $(`#warehouseid-${i+1}`);
                    $warehouseSelect.find('option').each(function () {
                        if ($(this).val() == v.warehouse_id) {
                            $(this).prop("selected", true);
                            return false;
                        }
                    });

                    // Hide the warehouse select if stock_type is "service"
                    if (v.stock_type === "service") {
                        $warehouseSelect.addClass('d-none');
                    } else {
                        $warehouseSelect.removeClass('d-none'); // Ensure it's visible for non-service types
                    }
                });

            });
        },

        // stock select autocomplete
        projectstockSelect(event, ui) {
            const {data} = ui.item;
            // const i = Form.projectStockRowId;
            const row = $(this).parents('tr:first');
            if($(this).is('.projectstock')) {
                row.find('.stockitemprojectid').val(data.id).change();
            }
        },
        
        projectMouse() {
            const id = $(this).attr('id').split('-')[1];
            if ($(this).is('.projectstock')) {
                Form.projectStockRowId = id;
                const row = $(this).parents('tr:first');
                row.find('.warehouse').val('0').attr('readonly', true);
            }
        },

        productRow(v,i) {
            const qty = accounting.formatNumber(v.qty);
            const received = accounting.formatNumber(v.qty_received);
            const due = v.qty - v.qty_received;
            const balance = accounting.formatNumber(due > 0? due : 0);
            const project_id = v.itemproject_id? v.itemproject_id : 0;
            return `
                <tr>
                    <td>${i+1}</td>    
                    <td class="text-left">${v.description}</td>
                    <td><input class="form-control projectstock" value="${v.project_tid}" id="projectstocktext-${i+1}" placeholder="Search Project By Name"></input></td>  
                    <td>
                        <select name="warehouse_id[]" class="form-control custom-select warehouse" id="warehouseid-${i+1}">
                            <option value="0">Select Warehouse</option>
                            @foreach ($warehouses as $row)
                                <option value="{{ $row->id }}">{{ $row->title }}</option>
                            @endforeach
                        </select>
                    </td> 
                    <td>
                        <input type="text" class="form-control accountname" name="name[]" id="accountname-0" placeholder="Enter Ledger Account" autocomplete="off">
                    </td>    
                    <td>${v.uom}</td>    
                    <td class="qty_ordered">${qty}</td>    
                    <td class="qty_received">${received}</td>    
                    <td class="qty_due">${balance}</td>    
                    <td><input name="qty[]" id="qty" class="form-control qty"></td>    
                    <input type="hidden" name="purchaseorder_item_id[]" value="${v.id}">
                    <input type="hidden" class="product_code" name="product_code[]" value="${v.product_code}">
                    <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="${project_id}" id="projectstockval-${i+1}" >
                    <input type="hidden" name="rate[]" value="${+v.rate}" class="rate">
                    <input type="hidden" id="expitemid-0" class="expitemid" name="account_id[]">
                    <input type="hidden" name="item_id[]" value="${v.product_id}">
                </tr>
            `;
        },

        warehouseChange() {
            const row = $(this).parents('tr:first');
            row.find('.projectstock').val('').attr('readonly', false);
            row.find('.stockitemprojectid').val(0);
            row.find('.accountname').val('').attr('readonly', false);
            row.find('.expitemid').val(0);
            if (+$(this).val() > 0) {
                row.find('.projectstock').attr('readonly', true);
            } 
        },

        projectChange() {
            const row = $(this).parents('tr:first');
            row.find('.stockitemprojectid').val(0);
            row.find('.warehouse').val(0).attr('readonly', false);
            row.find('.accountname').val('').attr('readonly', false);
            row.find('.expitemid').val(0);
            if ($(this).val()) {
                const projectstockUrl = "{{ route('biller.projects.project_search') }}";
                row.find('.projectstock').autocomplete(config.prediction(projectstockUrl, Form.projectstockSelect));
            } 
        },
        
        onQtyChange() {
            
            // limit qty on goods received
            let row = $(this).parents('tr');
            let qty = accounting.unformat(row.find('.qty').val());
            let qtyDue = accounting.unformat(row.find('.qty_due').text());
            let qtyOrdered = accounting.unformat(row.find('.qty_ordered').text());
            let qtyReceived = accounting.unformat(row.find('.qty_received').text());
            if (!Form.grn) {
                if (qty > qtyDue) qty = qtyDue;
            } else {
                let limit = qty;
                let originQty = accounting.unformat($(this).attr('origin'));
                if (qtyDue && qtyReceived < qtyOrdered) {
                    limit = originQty + qtyDue;
                } else {
                    if (qtyReceived > qtyOrdered) {
                        if (originQty < qtyReceived) limit = originQty - (qtyOrdered - qtyReceived);
                        if (originQty == qtyReceived) limit = qtyOrdered;
                    } else {
                        limit = qtyOrdered;
                    }
                }
                if (qty > limit) qty = limit;
            }

            Form.value = accounting.formatNumber(qty);
            row.find('.qty').val(accounting.formatNumber(qty));
            Form.columnTotals();
        },

        columnTotals() {
            subtotal = 0;
            total = 0;
            const tax_rate = 1 + $('#tax_rate').val() / 100;
            $('#productTbl tbody tr').each(function() {
                const row = $(this);
                const qty = accounting.unformat(row.find('.qty').val());
                const rate = accounting.unformat(row.find('.rate').val());
                subtotal += qty * rate;
                total += qty * rate * tax_rate;
            });
            $('#subtotal').val(accounting.formatNumber(subtotal));
            $('#tax').val(accounting.formatNumber(total - subtotal));
            $('#total').val(accounting.formatNumber(total));
            $("#credit_limit").html('');
            let credit_limit = $('#credit').val().replace(/,/g, '');
            let total_aging = $('#total_aging').val().replace(/,/g, '');
            let outstanding_balance = $('#outstanding_balance').val().replace(/,/g, '');
            let balance = total_aging.toLocaleString() - outstanding_balance.toLocaleString() + total;
            if (balance > credit_limit && credit_limit > 0) {
                let exceeded = balance -credit_limit;
                $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by:  ${parseFloat(exceeded).toFixed(2)}</h4>`);
            }else{
                $("#credit_limit").html('');
            }
        },
    }

    $(Form.init);
</script>

