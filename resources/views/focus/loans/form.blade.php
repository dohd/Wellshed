<div class='row'>
    <div class='form-group col-4'>
        <div><label for="lending_type">Lending Type<span class="text-danger">*</span></label></div>
        {!! Form::select('lending_type', ['borrow_from_company' => 'Borrow From Company', 'lend_to_company' => 'Lend To Company'], null, [
            'class' => 'custom-select',
            'id' => 'lending_type',
            'required' => 'required',
        ]) !!}
    </div> 

    <div class='form-group col-4 employee-div'>
        <label for="employee">Loan Applicant<span class="text-danger">*</span></label>
        <select name="employee_id" id="employee" class="form-control" required>
            @foreach ($employees as $row)
                <option value="{{ $row->id }}">{{ $row->full_name }}</option>
            @endforeach
        </select>
    </div> 

    <div class='form-group col-4 lender-div'>
        <div><label for="bank">Loan Lender (Credit)<span class="text-danger">*</span></label></div>
        {!! Form::select('lender_id', $lenders, null, [
            'placeholder' => '-- Select Lender --',
            'class' => 'form-control round',
            'id' => 'lender',
            'required' => 'required',
        ]) !!}
    </div> 
 
    <div class='form-group col-4 bank-div'>
        <div><label for="bank_id">Bank Account (Debit)<span class="text-danger">*</span></label></div>
        {!! Form::select('bank_id', $accounts, null, [
            'placeholder' => '-- Select Lending Type --',
            'class' => 'form-control round',
            'id' => 'bank',
            'required' => 'required',
        ]) !!}
    </div> 
</div>

<div class='row'>
    <div class='form-group col-3'>
        <div><label for="tid">Loan ID<span class="text-danger">*</span></label></div>
        {{ Form::text('tid', @$loan? $loan->tid : $tid+1, ['class' => 'form-control round', 'readonly']) }}
    </div>

    <div class='form-group col-3'>
        <div><label for="date">Application Date<span class="text-danger">*</span></label></div>
        <input type="text" name="date" class="form-control datepicker round">
    </div>

    <div class='form-group col-3'>
        <div><label for="date">Loan Period (months)<span class="text-danger">*</span></label></div>
        <input type="number" name="month_period" class="form-control round period" id="month_period">
    </div>

    <div class='form-group col-3 loan-type-div'>
        <div><label for="loan_type">Loan Type</span></label></div>
        {!! Form::select('loan_type', ['emergency_loan' => 'Emergency Loans', 'education_loan' => 'Education Loan'], null, [
            'placeholder' => '-- Select Loan Type --',
            'class' => 'custom-select',
            'id' => 'loan_type',
        ]) !!}
    </div>
    <div class='form-group col-3 payment-day-div'>
        <div><label for="payment_day">Payment Day</label></div>
        {!! Form::select('payment_day', range(1, 31), null, [
            'placeholder' => '-- Pay Day --',
            'class' => 'form-control round',
            'id' => 'payment_day'
        ]) !!}
    </div>
</div>

<div class='row'>
    <div class='form-group col-3'>
        <div><label for="amount">Principal Amount<span class="text-danger">*</span></label></div>
        {{ Form::text('amount', null, ['class' => 'form-control round cash', 'id' => 'amount', 'required']) }}
    </div>
    <div class='form-group col-3'>
        <div><label for="application_fee">Processing Fee</label></div>
        {{ Form::text('fee', null, ['class' => 'form-control round cash', 'id' => 'fee', 'required']) }}
    </div>
 
    <div class='form-group col-3'>
        <div><label for="installment">Monthly Installment<span class="text-danger">*</span></label></div>
        {{ Form::text('month_installment', null, ['class' => 'form-control round cash', 'id' => 'installment', 'readonly']) }}
    </div>
    
    <div class='form-group col-3'>
        <div><label for="amount">Monthly Accrued Interest</label></div>
        {{ Form::text('interest', null, ['class' => 'form-control round cash', 'id' => 'interest', 'required']) }}
    </div>
</div>

<div class='row'> 
    <div class='form-group col-8'>
        <div><label for="note">Note</label></div>
        {{ Form::text('note', null, ['class' => 'form-control round', 'required']) }}
    </div>
</div>

<div class="row">
    <div class="col-2 ml-auto mr-5">
        {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-lg block round']) }}
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        date: {
            autoHide: true,
            format: "{{ config('core.user_date_format') }}"
        },
        select: {allowClear: true},
    };

    const Form = {
        loan: @json(@$loan),

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#employee').select2(config.select);

            if (this.loan) {

            } else {
                $('#employee').val('').change();
            }

            $('#lending_type').change(this.lendingTypeChange).change();
            $('#loan_type').change(this.loanTypeChange).change();
            $('form').on('focusout', '.cash, .period', this.cashChange);
        },

        lendingTypeChange() {
            const type = $(this).val();
            if (type == 'borrow_from_company') {
                $('.employee-div').removeClass('d-none');
                $('.bank-div').addClass('d-none');
                $('.loan-type-div').removeClass('d-none');

                $('#bank').attr('disabled', true);
                $('#employee').attr('disabled', false);

                $('#payment_day').attr('disabled', true);
                $('.payment-day-div').addClass('d-none');
                $('#interest').attr('disabled', true);
            } else {
                $('.bank-div').removeClass('d-none');
                $('.employee-div').addClass('d-none');
                $('.loan-type-div').addClass('d-none');

                $('#bank').attr('disabled', false);
                $('#employee').attr('disabled', true);
                $('#loan_type').attr('disabled', false);

                $('#payment_day').attr('disabled', false);
                $('.payment-day-div').removeClass('d-none');
                $('#interest').attr('disabled', false);
            }
            
        },
        loanTypeChange() {
            const type = $(this).val();
            if (type) {
                $('#fee').val(0).attr('readonly', true);
            } else{
                $('#fee').attr('readonly', false);
            }
            
        },

        cashChange() {
            if ($(this).is('#amount') || $(this).is('#month_period')) {
                const amount = accounting.unformat($('#amount').val());
                const period = accounting.unformat($('#month_period').val());
                if (amount && !period) return alert('Loan period required!');
                const installment = Math.round(amount/period);
                $('#installment').val(accounting.formatNumber(installment));
            } 
            if (!$(this).is('#month_period')) {
                const cash = accounting.unformat($(this).val());
                $(this).val(accounting.formatNumber($(this).val()));
            }
        },
    };
    
    $(() => Form.init());
</script>
@endsection