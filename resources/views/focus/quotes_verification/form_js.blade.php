{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>    
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    $.ajaxSetup(config.ajax);
    
    // Intialize datepicker
    $('.datepicker').datepicker(config.date);
    $('#date-0').datepicker('setDate', new Date());
    $('#project_closure_date').datepicker('setDate', new Date());
    $('#project_closure_date').val('');
    $('#reference-date').val('');
    $('#user').select2({allowClear: true});
    
    const quote = @json($quote);
    if (quote && quote.id) {
        if (quote.reference_date) $('#reference-date').datepicker('setDate', new Date(quote.reference_date));
        if (quote.date) $('#date').datepicker('setDate', new Date(quote.date));
        if (quote.project_closure_date) $('#project_closure_date').datepicker('setDate', new Date(quote.project_closure_date));
        $('#gen_remark').val(quote.gen_remark);
    }
    
    // Undo verification 
    $('#reset-items').click(function() {
        swal({
            title: 'Are you sure ?',
            text: 'Undo previous verification',
            type: 'warning',
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => {
            $.ajax({
                url: baseurl + 'quotes/reset_verified/' + "{{ $quote->id }}",
                success: () => location.reload(),
            });
        });
    });

    // set general remark required if quote total not equal to verified total
    $(function() {
        const total = accounting.unformat($('#total').val());
        const quoteTotal = accounting.unformat($('#quote_total').val());
        if (total != quoteTotal) $('#gen_remark').attr('required', true);
        else $('#gen_remark').attr('required', false);
    });
    $('#total').change(function() {
        const total = accounting.unformat($(this).val());
        const quoteTotal = accounting.unformat($('#quote_total').val());
        if (total != quoteTotal) $('#gen_remark').attr('required', true);
        else $('#gen_remark').attr('required', false);
    });

    // Job card row template
    $('#hasJobcard').change(function() {
        if ($(this).prop('checked')) {
            $('.jc-ctn').removeClass('d-none');
        } else {
            $('.jc-ctn').addClass('d-none');
        }
    });
    const verifiedJcs = @json($quote->verified_jcs);
    if (verifiedJcs && verifiedJcs.length) {
        $('#hasJobcard').prop('checked', true).change();
    }

    const jobCardRowNodeMain = $('#jobcardTbl tbody tr').clone();
    $('#jobcardTbl tbody tr').remove();
    jobCardRowNodeMain.removeClass('d-none');
    function jobCardRow(n) {
        const jobCardRowNode = jobCardRowNodeMain.clone();
        jobCardRowNode.find('input').each(function() {
            $(this).attr('id', $(this).attr('id') + '-' + n);
        });
        jobCardRowNode.find('select').each(function() {
            $(this).attr('id', $(this).attr('id') + '-' + n);
        });
        jobCardRowNode.find('textarea').each(function() {
            $(this).attr('id', $(this).attr('id') + '-' + n);
        });
        jobCardRowNode.find('span').each(function() {
            $(this).attr('id', $(this).attr('id') + '-' + n);
        });
        const html = jobCardRowNode.html();
        return '<tr>' + html + '</tr>';
    }
    
    // on change jobcard row type
    $('#jobcardTbl').on('change', '.type', function() {
        let i = $(this).attr('id').split('-')[1];
        // type 2 is dnote
        if ($(this).val() == 2) {
            $('#fault-'+i).addClass('invisible');
            $('#equip-'+i).addClass('invisible');
            $('#location-'+i).addClass('invisible');
            $('#jobhrs-'+i).addClass('invisible');
        } else {
            $('#fault-'+i).removeClass('invisible');
            $('#equip-'+i).removeClass('invisible');
            $('#location-'+i).removeClass('invisible');
            $('#jobhrs-'+i).removeClass('invisible');
        }
    });
    
    // addjob card row
    let jcIndex = 0;
    $('#add-jobcard').click(function() {
        const i = jcIndex;
        $('#jobcardTbl tbody').append(jobCardRow(i));
        $('#equip-'+i).autocomplete(autocompleteEquip(i));
        $('#date-'+i).datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
        .datepicker('setDate', new Date());
        jcIndex++;
        // scroll to focus
        const scrollable = $('#jobcardTbl').parents('div.table-responsive');
        scrollable.scrollTop(scrollable[0].scrollHeight);
    });

    // remove job card row
    $('#jobcardTbl').on('click', '.del', function() {
        const row = $(this).parents('tr:first');
        if (confirm('Are you sure ?')) row.remove();
    });

    // load presaved jobcards
    const jobcards = @json($jobcards);
    jobcards.forEach((v, i) => {
        jcIndex++;
        $('#jobcardTbl tbody').append(jobCardRow(i));                    
        $('#jcitemid-'+i).val(v.id);
        $('#reference-'+i).val(v.reference);
        $('#type-'+i).val(v.type).change();
        $('#technician-'+i).val(v.technician);
        $('#equip-'+i).autocomplete(autocompleteEquip(i)).val(v.equipment?.make_type);
        $('#equipmentid-'+i).val(v.equipment_id);
        $('#location-'+i).val(v.equipment?.location);
        $('#fault-'+i).val(v.fault)
        $('#date-'+i).datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date(v.date));
        // hidden dnote fields 
        if (v.type == 2) {
            $('#equip-'+i).addClass('invisible');
            $('#location-'+i).addClass('invisible');
            $('#fault-'+i).addClass('invisible');
        }
    });
    
    // Add Job Labour Hours
    let activeRow = '';
    $('#job_date').datepicker('setDate', new Date());
    $("#employee").select2({allowClear: true, dropdownParent: $('#attachLabourModal .modal-body')});
    $('#jobcardTbl').on('click', '.add_labour', function() {
        const tr = $(this).parents('tr');
        activeRow = tr;
        $('#job_date').val(tr.find('.date').val());
        $('#job_ref_type').val(tr.find('.job_ref_type').val());
        $('#job_card').val(tr.find('.ref').val());
        $('#job_type').val(tr.find('.job_type').val());
        $('#hrs').val(tr.find('.job_hrs').val());
        $('#is_payable').val(tr.find('.job_is_payable').val());
        $('#note').val(tr.find('.job_note').val());
        
        // set selected employees
        let employee_ids = tr.find('.job_employee').val() || '';
        employee_ids = employee_ids.split(',');
        $('#employee').val(employee_ids).change();
        
        // fetch expected labour hours
        $('#expectedHrs').html(`(Rem: ${0})`);
        $.get("{{ route('biller.labour_allocations.expected_hours') }}?quote_id=" + quote.id, function(data) {
            $('#expectedHrs').html(`(Rem: ${data.hours})`);
            $('#project_name').html(`${data.project_tid}: <span class="text-primary">${data.quote_tid}</span>`);
        });
    });
    // job type change
    $('#job_type').change(function() {
        if (this.value == 'diagnosis') $('#is_payable').val(0);
        else $('#is_payable').val(1);
    });
    // on submit labour hours
    $('#attachLabourForm').submit(function(e) {
        event.preventDefault();
        $('#attachLabourModal').modal('toggle');
        const data = {};
        const employee_ids = [];
        $.each($('#attachLabourForm').serializeArray(), function(i, field) {
            data[field.name] = field.value;
            if (field.name == 'mdl_employee') employee_ids.push(field.value);
        });
        const tr = activeRow;
        tr.find('.job_date').val(data.mdl_date);
        tr.find('.job_type').val(data.mdl_job_type);
        tr.find('.job_employee').val(employee_ids.join(','));
        tr.find('.job_ref_type').val(data.mdl_ref_type);
        tr.find('.job_jobcard_no').val(data.mdl_jobcard);
        tr.find('.job_hrs').val(data.mdl_hrs);
        tr.find('.jobhrs').text(data.mdl_hrs? data.mdl_hrs : '_');
        tr.find('.job_is_payable').val(data.mdl_is_payable);
        tr.find('.job_note').val(data.mdl_note);
    });
    
    
    // row dropdown menu
    function dropDown() {
        return `
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item up" href="javascript:void(0);">Up</a>
                    <a class="dropdown-item down" href="javascript:void(0);">Down</a>
                    <a class="dropdown-item removeProd text-danger" href="javascript:void(0);">Remove</a>
                </div>
            </div>            
        `;
    }

    // product row
    function productRow(v) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${v}" autocomplete="off"></td>
                <td><input type="text" class="form-control" name="product_name[]" placeholder="{{trans('general.enter_product')}}" id='itemname-${v}'></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${v}" value=""></td>                
                <td><input type="text" class="form-control qty" name="product_qty[]" id="qty-${v}" autocomplete="off"></td>
                <td><input type="text" class="form-control rate" name="product_subtotal[]" id="rate-${v}" autocomplete="off"></td>
                <td>
                    <input type="hidden" class="form-control rateinc" name="product_price[]" id="rateinc-${v}">
                    <div class="row no-gutters">
                        <div class="col-5">
                            <select class="custom-select tax-rate" name="tax_rate[]" id="tax-rate-${v}">
                                <option value="">VAT</option>
                                @foreach ($additionals as $item)
                                    <option value="{{ +$item->value }}">{{ $item->value == 0 ? 'OFF' : +$item->value . '%' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-7">
                            <input type="text" class="form-control tax" name="product_tax[]" id="tax-${v}" value="100" autocomplete="off">
                        </div>
                    </div>
                </td>
                <td><strong><span class='ttlText' id="result-${v}">0</span></strong></td>
                <td><textarea class="form-control" name="remark[]" id="remark-${v}" rows="1"></textarea></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${v}">
                <input type="hidden" name="product_id[]" value=0 id="productid-${v}">
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${v}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${v}">
            </tr>
        `;
    }

    // product title row
    function productTitleRow(v) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${v}" autocomplete="off" ></td>
                <td colspan="7"><input type="text"  class="form-control" name="product_name[]" id="itemname-${v}" placeholder="Enter Title Or Heading"></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="remark[]" id="remark-${v}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${v}">
                <input type="hidden" name="product_id[]" value="${v}" id="productid-${v}">
                <input type="hidden" name="unit[]" value="">
                <input type="hidden" name="product_qty[]" value="0">
                <input type="hidden" name="product_price[]" value="0">
                <input type="hidden" name="product_subtotal[]" value="0">
                <input type="hidden" name="product_tax[]" value=0>
                <input type="hidden" name="tax_rate[]" value=0>
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${v}">
                <input type="hidden" name="a_type[]" value="2" id="atype-${v}">
            </tr>
        `;
    }

    // on rate change
    $('#quotation').on('keyup', '.rate', function() {
        const row = $(this).parents('tr');
        const rate = accounting.unformat(this.value);
        const qty = accounting.unformat(row.find('.qty').val());
        const taxRate = accounting.unformat(row.find('.tax-rate').val());
        const rateInc = rate * (1 + taxRate * 0.01);
        const productTotal = qty * rateInc;
        const tax = qty * rate * taxRate * 0.01;
        row.find('.tax').val(accounting.formatNumber(tax));
        row.find('.rateinc').val(accounting.formatNumber(rateInc));
        row.find('.ttlText').text(accounting.formatNumber(productTotal));
        calcTotals();
    });
    $('#quotation').on('change', '.qty, .rate', function() {
        this.value = accounting.formatNumber(accounting.unformat(this.value));
        // set required remark
        const i = $(this).attr('id').split('-')[1];
        $('#remark-'+i).attr('required', true);
    });
    // on qty, vat change
    $('#quotation').on('change', '.qty, .tax-rate', function() {
        $(this).parents('tr').find('.rate').keyup();
    });

    // set default product rows
    let rowIndx = 0;
    const products = @json($products);
    products.forEach(v => {
        const i = rowIndx;        
        // check if item type is product
        if (v.a_type == 1) {
            $('#quotation tbody').append(productRow(rowIndx));
            $('#itemname-'+rowIndx).autocomplete(autocompleteProp(rowIndx));
            $('#itemid-'+i).val(v.id);
            $('#productid-'+i).val(v.product_id);
            $('#numbering-'+i).val(v.numbering);
            $('#itemname-'+i).val(v.product_name);
            $('#unit-'+i).val(v.unit); 
            $('#remark-'+i).val(v.remark);
            $('#qty-'+i).val(accounting.formatNumber(+v.product_qty));
            $('#rate-'+i).val(accounting.formatNumber(+v.product_subtotal)).attr('readonly', false);
            $('#tax-rate-'+i).val(+v.tax_rate).change();
        } else {
            $('#quotation tbody').append(productTitleRow(rowIndx));
            $('#itemid-'+i).val(v.id);
            $('#productid-'+i).val(v.product_id);
            $('#numbering-'+i).val(v.numbering);
            $('#itemname-'+i).val(v.product_name);
        }
        rowIndx++;
        calcTotals();
    });    

    // On click Add Product
    $('#add-product').click(function() {
        $('#quotation tbody').append(productRow(rowIndx));
        $('#itemname-'+rowIndx).autocomplete(autocompleteProp(rowIndx));
        rowIndx++;
        calcTotals();
        // scroll to focus
        const scrollable = $('#quotation').parents('div.table-responsive');
        scrollable.scrollTop(scrollable[0].scrollHeight);
    });
    // on click Add Title button
    $('#add-title').click(function() {
        $('#quotation tbody').append(productTitleRow(rowIndx));
        rowIndx++;
        calcTotals();
        // scroll to focus
        const scrollable = $('#quotation').parents('div.table-responsive');
        scrollable.scrollTop(scrollable[0].scrollHeight);
    });

    // on clicking Product row drop down menu
    $("#quotation").on("click", ".up, .down, .removeProd", function() {
        const row = $(this).parents("tr:first");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.removeProd')) {
            if (confirm('Are you sure to delete this item?')) row.remove();            
        }
        calcTotals();
    });    


    // totals
    function calcTotals() {
        let taxable = 0;
        let subtotal = 0;
        let total = 0;
        $('#quotation tbody tr').each(function(i) {
            const row = $(this);
            row.find('input[name="row_index[]"]').val($(this).index());
            const qty = accounting.unformat(row.find('.qty').val());
            const rate = accounting.unformat(row.find('.rate').val());
            const taxRate = accounting.unformat(row.find('.tax-rate').val());
            if (taxRate) taxable += qty * rate;
            subtotal += qty * rate;
            total += qty * rate * (1 + taxRate * 0.01);
        });
        const tax = total - subtotal;
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber(tax));        
        $('#total').val(accounting.formatNumber(total));

        let expense = accounting.unformat($('#expense').val());
        let diff = subtotal - expense;
        let percent_profit = subtotal > 0 ? (diff / subtotal) * 100 : 0;
        if(diff < 0) percent_profit = 0;
        $('.profit')
            .text(accounting.formatNumber(diff))
            .toggleClass('text-danger', diff < 0)
            .toggleClass('text-success', diff >= 0); // optional for positive profits

        // Update percent profit text
        $('.percent_profit')
            .text(accounting.formatNumber(percent_profit))
            .toggleClass('text-danger', diff < 0)
            .toggleClass('text-success', diff >= 0);

        const tr = $('#summaryTbl tbody tr');
        tr.find('td:eq(1)').html(accounting.formatNumber(taxable));
        tr.find('td:eq(2)').html(accounting.formatNumber(subtotal));
        tr.find('td:eq(3)').html(accounting.formatNumber(tax));
        tr.find('td:eq(4)').html(accounting.formatNumber(total));
    }

    // product autocomplete
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}",
                    method: 'POST',
                    data: {
                        keyword: request.term,
                    },
                    success: result => response(result.map(v => ({
                        label: v.name,
                        value: v.name,
                        data: v
                    })))
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                $('#productid-'+i).val(data.id);
                $('#itemname-'+i).val(data.name);
                $('#unit-'+i).val(data.unit);                
                $('#qty-'+i).val(1);
                const tax = @json(+$quote->tax_id);
                const rateInc = data.rate * (1 + tax * 0.01);
                $('#rate-'+i).val(accounting.formatNumber(+data.rate)).attr('readonly', true);
                $('#rateinc-'+i).val(accounting.formatNumber(rateInc));                
                $('#result-'+i).text(accounting.formatNumber(rateInc));
                calcTotals();
            }
        };
    }
    
    // equipment autocomplete
    function autocompleteEquip(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    method: 'POST',
                    data: {
                        keyword: request.term, 
                        customer_id: "{{ $quote->customer_id }}",
                        branch_id: "{{ $quote->branch_id }}",
                    },
                    success: data => {
                        data = data.map(v => {
                            for (const key in v) {
                                if (!v[key]) v[key] = '';
                            }
                            const label = `${v.unique_id} ${v.equip_serial} ${v.make_type} ${v.model} ${v.machine_gas}
                                ${v.capacity} ${v.location} ${v.building} ${v.floor}`;
                            const value = v.unique_id;
                            const data = v;
                            return {label, value, data};
                        });
                        response(data);
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: (event, ui) => {
                const {data} = ui.item;
                $('#equipmentid-'+i).val(data.id);
                $('#equip-'+i).val(data.make_type);
                $('#location-'+i).val(data.location);
            }
        };
    }    
</script>