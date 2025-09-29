@extends ('core.layouts.app')

@section('title', 'Edit | Invoice Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.payments-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($payment, ['route' => ['biller.invoices.update_payment', $payment], 'method' => 'PATCH']) }}
                        @include('focus.invoices.payment_form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
@include('focus/invoices/payment_form_js')
<script>
    // default
    const payment = @json($payment);
    $('#person').attr('disabled', true);
    $('#date').datepicker('setDate', new Date(payment.date)); 
    $('#payment_type').attr('disabled', true);
    const amount = accounting.unformat($('#amount').val());
    $('#amount').val(accounting.formatNumber(amount));
    calcTotal();

    // on amount change
    $('#amount').keyup(function() {
        let dueTotal = 0;
        let allocateTotal = 0;
        let amount = accounting.unformat($(this).val());
        const lastCount = $('#invoiceTbl tbody tr').length - 1;
        $('#invoiceTbl tbody tr').each(function(i) {
            if (i == lastCount) return;
            const invAmount = accounting.unformat($(this).find('.inv-amount').text());
            if (invAmount > amount) $(this).find('.paid').val(accounting.formatNumber(amount));
            else if (amount > invAmount) $(this).find('.paid').val(accounting.formatNumber(invAmount));
            else $(this).find('.paid').val(0);
            const paid = accounting.unformat($(this).find('.paid').val());
            amount -= paid;
            dueTotal += invAmount;
            allocateTotal += paid;
        });
        $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
        $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
    }).focusout(function() { 
        if (!$(this).val()) return;
        const amount = accounting.unformat($(this).val());
        $(this).val(accounting.formatNumber(amount));
    }).focus(function() {
        if (!$('#person').val()) $(this).blur();
    });    

    // compute totals
    function calcTotal() {
        let dueTotal = 0;
        let allocateTotal = 0;
        const lastCount = $('#invoiceTbl tbody tr').length - 1;
        $('#invoiceTbl tbody tr').each(function(i) {
            if (i == lastCount) return;
            const invAmount = parseFloat($(this).find('.inv-amount').text().replace(/,/g, '')) || 0;
            const paid = parseFloat($(this).find('.paid').val().replace(/,/g, '')) || 0;
            dueTotal += invAmount;
            allocateTotal += paid;
        });
        $('#allocate_ttl').val(parseFloat(allocateTotal.toFixed(2)).toLocaleString());
        $('#balance').val(parseFloat(dueTotal - allocateTotal).toLocaleString());
    }
</script>
@endsection
