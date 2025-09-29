@extends ('core.layouts.app')
@section('title', 'Create | Invoice Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Generate Invoice</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.invoices-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::open(['route' => 'biller.invoices.store_project_invoice', 'method' => 'POST']) }}
            @include('focus.invoices.project_invoice_form')
        {{ Form::close() }}
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
        ajax: {headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
    };

    const Form = {
        invoiceItemRow: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#classlist').select2({allowClear: true});

            $('#tax_id').change(Form.onTaxChange);
            $('#invoice_type').change(Form.onInvoiceTypeChange);

            const quoteCount = @json(count(@$quotes));
            if (quoteCount > 1) $('#invoice_type option:eq(0)').remove();
            // list verified items
            if ($('#invoice_type').val() == 'standard') {
                Form.invoiceItemRow = $('#quoteTbl tbody tr:first').html();
                $('#invoice_type').change();
            } else {
                // consolidated quotes/pi
                $('#tax_id').change();
            }
        },

        onTaxChange() {
            const mainTax = accounting.unformat(this.value);
            const rowTaxeRates = [];
            $('#quoteTbl tbody tr').each(function(i) {
                const taxRate = accounting.unformat($(this).find('.taxrate').val());
                rowTaxeRates.push(taxRate);
            });
            const disjoint = [mainTax, 0].filter(v => !rowTaxeRates.includes(v));
            let isError = false;
            // mixed vat
            if (rowTaxeRates.includes(0)) {
                if (mainTax > 0) {
                    if (disjoint.length && !rowTaxeRates.includes(disjoint[0])) isError = true;
                } else {
                    if (disjoint.length) isError = true;
                }
            } else {
                // single vat
                if (disjoint.length > 1 && disjoint[0] != 0) isError = true;
            }
            if (isError) {
                alert(`${disjoint[0]}% rate not applicable!`);
                $('#tax_id').val(0);
            }
            Form.computeTotals();
        },

        checkLimits() {
            $('#credit_limit').html('')
            $.ajax({
                type: "POST",
                url: "{{route('biller.customers.check_limit')}}",
                data: {
                    customer_id: $('#customer_id').val(),
                },
                success: function (result) {
                    let total = $('#total').val();
                    let number = 0;
                    if (!isNaN(total)){
                        total = 0;
                        number = total;
                    } else {
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
                        $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by: ${accounting.formatNumber(exceeded)}</h4>`);
                    }else{
                        $('#credit_limit').html('')
                    }
                }
            });
        },

        onInvoiceTypeChange() {
            $('#quoteTbl tbody tr').remove();
            ['taxable', 'subtotal', 'tax', 'total'].forEach(v => {
                $(`#${v}`).val('');
            });
            // append preselected quote as line
            if (this.value == 'collective') {
                $('#quoteTbl tbody').append(`<tr>${Form.invoiceItemRow}</tr>`);
            }
            // append quote products as line
            if (this.value == 'standard') {
                const quote = @json(@$quotes->first());
                const prefixes = @json(@$prefixes);
                if (quote && quote.verified_products) {
                    const items = quote.verified_products;
                    items.forEach((v,i) => {
                        $('#quoteTbl tbody').append(`<tr>${Form.invoiceItemRow}</tr>`);
                        const row = $('#quoteTbl tbody tr:last');
                        
                        row.find('.num').text(v.numbering);
                        row.find('.num-val').val(v.numbering);
                        row.find('.row-index').val(v.row_index);                    
                        row.find('.verification-id').text(v.verification_id);

                        const tid = `${quote.tid}`.length < 4? `000${quote.tid}`.slice(-4) : quote.tid;
                        let pfx = quote.bank_id == 0? prefixes[1] : prefixes[2];
                        if(quote.boq_valuation_id) pfx = 'BoQ';
                        row.find('.ref').val(`${pfx}-${tid}`);
                        row.find('.descr').val(v.product_name);
                        row.find('.unit').val(v.unit);
                        row.find('.taxrate').val(+v.tax_rate);
                        row.find('.producttax').val(v.tax_rate * 0.01 * v.product_subtotal);
                        row.find('.taxable').val(accounting.formatNumber(+v.product_subtotal, 4));
                        row.find('.qty').val(+v.product_qty);
                        row.find('.rate').val(accounting.formatNumber(+v.product_subtotal, 4));
                        row.find('.productsubtotal').val(accounting.formatNumber(+v.product_subtotal, 4));
                        row.find('.price').val(accounting.formatNumber(+v.product_price, 4));
                        
                        const amount = v.product_qty * v.product_subtotal * (1 + v.tax_rate * 0.01);
                        row.find('.amount').text(accounting.formatNumber(amount, 4));
                        row.find('.productamount').val(accounting.formatNumber(amount, 4));
                        if(quote.boq_valuation_id){
                            row.find('.boq-id').val(quote.id);
                        }else{

                            row.find('.quote-id').val(quote.id);
                        }
                        row.find('.branch-id').val(quote.branch_id);
                        row.find('.project-id').val(quote.project_quote? quote.project_quote.project_id : '');
                    });
                }
            }
            $('#tax_id').change();
        },

        computeTotals() {
            $("#credit_limit").html('');
            const mainTax = accounting.unformat($('#tax_id').val());
            let taxable = 0;
            let subtotal = 0; 
            let total = 0;

            $('#quoteTbl tbody tr').each(function(i) {
                $(this).find('.row-index').val(i);
                const qty = accounting.unformat($(this).find('.qty').val());
                const rowSubtotal = accounting.unformat($(this).find('.rate').val());
                const rowTaxable = accounting.unformat($(this).find('.taxable').val());
                const rowTaxRate = accounting.unformat($(this).find('.taxrate').val()) * 0.01;
                // standard - inventory products
                if ($('#invoice_type').val() == 'standard') {
                    let rowTax = 0;
                    const mainTaxRate = mainTax * 0.01;
                    if (mainTaxRate != rowTaxRate) rowTax = rowTaxable * rowTaxRate;
                    else rowTax = rowTaxable * mainTaxRate;
                    
                    if (rowTax != 0) {
                        taxable += qty * rowTaxable                        
                        if (mainTaxRate == 0) rowTax = 0;
                    }
                    subtotal += qty * rowSubtotal;
                    price = rowSubtotal + rowTax;
                    total += qty * price;
                    $(this).find('.price').val(accounting.formatNumber(price, 4));
                    $(this).find('.amount').text(accounting.formatNumber(qty * price, 4));
                } else {
                    // bundled quotes
                    taxable += rowTaxable;
                    subtotal += rowSubtotal;  
                    const total = rowSubtotal + (rowTaxable * mainTax * 0.01);
                    $(this).find('.amount').text(accounting.formatNumber(total, 4));
                    $(this).find('.productamount').val(accounting.formatNumber(total, 4));
                }
            });

            const tax = taxable * mainTax/100;
            total = subtotal + tax;
            $('#tax').val(accounting.formatNumber(tax));
            $('#taxable').val(accounting.formatNumber(taxable));
            $('#subtotal').val(accounting.formatNumber(subtotal));
            $('#total').val(accounting.formatNumber(total));
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
            Form.checkLimits();
        }
    };
    
    $(Form.init);
</script>
@endsection