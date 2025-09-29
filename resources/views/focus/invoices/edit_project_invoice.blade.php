@extends ('core.layouts.app')
@section('title', 'Edit Project Invoice')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Edit Invoice Details</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.invoices-header-buttons')
            </div>
        </div>
    </div>
    <div class="content-body">
        {{ Form::model($invoice, ['route' => ['biller.invoices.update_project_invoice', $invoice], 'method' => 'POST']) }}
            @php 
                $customer = @$invoice->customer; 
            @endphp
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

    $('.datepicker').datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});
    $('#classlist').select2({allowClear: true});

    const invoice = @json($invoice);
    if (invoice && invoice.id) {
        if (invoice.invoicedate) $('#invoicedate').datepicker('setDate', new Date(invoice.invoicedate));
        else $('#invoicedate').val('');
        $('#fx_curr_rate').val(+invoice.fx_curr_rate);
    }

    // On tax change
    $('#tax_id').change(function() {
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
        computeTotals();
    });
    $('#tax_id').change();

    function checkLimits() {
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
    }

    /**
     * Standard Invoice
     * */
    const isStdInvoice = @json(@$invoice->is_standard);
    if (isStdInvoice) {
        const labelIndx = $('.ref-label').index();
        $('.ref-label').remove();
        $(`#quoteTbl tbody tr`).each(function() {
            $(this).find(`td:eq(${labelIndx})`).remove();
            $(this).find('.qty').attr('readonly',false);
            $(this).find('.rate').attr('readonly',false);
        });
    }

    $('#quoteTbl').on('change', '.qty, .rate', function(){
        computeTotals();
    });

    // compute totals
    function computeTotals() {
        let taxable = 0;
        let subtotal = 0; 
        const mainTax = accounting.unformat($('#tax_id').val());
        $('#quoteTbl tbody tr').each(function(i) {
            $(this).find('.row-index').val(i);
            const qty = accounting.unformat($(this).find('.qty').val());
            const rowSubtotal = accounting.unformat($(this).find('.rate').val());
            const rowTaxable = accounting.unformat($(this).find('.taxable').val());
            const rowTaxRate = accounting.unformat($(this).find('.taxrate').val());
            if (+rowTaxRate > 0) taxable += qty * rowTaxable;
            subtotal += qty * rowSubtotal;  
            const total = (qty * rowSubtotal) + (qty * rowTaxable * mainTax * 0.01);
            $(this).find('.amount').html(accounting.formatNumber(total,4));
            $(this).find('.productamount').val(accounting.formatNumber(total,4));
        });
        const tax = taxable * mainTax/100;
        const total = subtotal + tax;
        $('#tax').val(accounting.formatNumber(tax));
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#total').val(accounting.formatNumber(total));
    }
</script>
@endsection