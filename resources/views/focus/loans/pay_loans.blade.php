@extends ('core.layouts.app')

@section('title', 'Loans Payment')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Loans Payment</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.loans.partials.loans-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.loans.store_loans', 'method' => 'POST']) }}
                        <div class="form-group row">
                            <div class="col-5">
                                <label for="payer" class="caption">Search Lender</label>                                       
                                <select class="form-control" id="lender" name="lender_id" data-placeholder="Search Lender"></select>
                            </div>
                            <div class="col-2">
                                <label for="reference" class="caption">Payment ID</label>
                                <div class="input-group">
                                    {{ Form::text('tid', $last_tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
                                </div>
                            </div> 
                            <div class="col-2">
                                <label for="date" class="caption">Date</label>
                                <div class="input-group">
                                    {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                                </div>
                            </div> 
                            <div class="col-2">
                                <label for="payment_mode">Payment Mode</label>
                                <select name="payment_mode" class="form-control" required>
                                   <option value="">-- Select Mode --</option>
                                    @foreach (['Cash', 'Bank Transfer', 'Cheque', 'Mpesa', 'Card' ] as $val)
                                        <option value="{{ $val }}">{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>                                                                                                                                  
                        </div> 

                        <div class="form-group row">  
                            <div class="col-3">
                                <label for="paid_from">Paid From</label>
                                <select name="bank_id" id="" class="form-control" required>
                                   <option value="">-- Select Bank --</option>
                                    @foreach ($accounts as $row)
                                        @if ($row->account_type == 'Asset')
                                            <option value="{{ $row->id }}">{{ $row->holder }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>                             
                            <div class="col-2">
                                <label for="amount" class="caption">Amount (Ksh.)</label>
                                <div class="input-group">
                                    {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
                                </div>
                            </div>    
                            <div class="col-2">
                                <label for="interest_account">Interest Account (Liability)</label>
                                <select name="interest_id" id="interest" class="form-control">
                                   <option value="">-- Select Account --</option>
                                    @foreach ($accounts as $row)
                                        @if ($row->account_type == 'Liability')
                                            <option value="{{ $row->id }}">{{ $row->holder }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="penalty_account">Penalty Account (Expense)</label>
                                <select name="penalty_id" id="penalty" class="form-control">
                                   <option value="">-- Select Account --</option>
                                    @foreach ($accounts as $row)
                                        @if ($row->account_type == 'Expense')
                                            <option value="{{ $row->id }}">{{ $row->holder }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>                          
                            
                            <div class="col-2">
                                <label for="reference" class="caption">Reference</label>
                                <div class="input-group">
                                    {{ Form::text('ref', null, ['class' => 'form-control', 'required']) }}
                                </div>
                            </div>                                                     
                        </div>

                        <table class="table-responsive tfr my_stripe_single" id="loansTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th width="10%" class="text-center">Date</th>
                                    <th width="5%">Loan Number</th>
                                    <th width="35%" class="text-center">Note</th>
                                    <th width="10%">Status</th>
                                    <th width="10%" class="text-center">Loan Balance</th>
                                    <th width="10%" class="text-center">Paid</th>
                                    <th width="10%" class="text-center">Interest</th>
                                    <th width="10%" class="text-center">Penalty</th>
                                </tr>
                            </thead>
                            <tbody>                                
                                <tr class="bg-white">
                                    <td colspan="6"></td>
                                    <td colspan="2">
                                        <div class="row no-gutters mb-1">
                                            <div class="col-6 pl-3 pt-1"><b>Total Loan:</b></div>
                                            <div class="col-6">
                                                 {{ Form::text('amount_ttl', 0, ['class' => 'form-control', 'id' => 'amount_ttl', 'readonly']) }}
                                            </div>                          
                                        </div>
                                        <div class="row no-gutters">
                                            <div class="col-6 pl-3 pt-1"><b>Total Paid:</b></div>
                                            <div class="col-6">
                                            {{ Form::text('deposit_ttl', 0, ['class' => 'form-control', 'id' => 'deposit_ttl', 'readonly']) }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>                
                        </table>

                        <div class="row mt-1">                            
                            <div class="col-12"> 
                                <button type="button" class="btn btn-primary btn-lg float-right" id="payLoan">Make Payment</button>                       
                            </div>
                        </div>
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
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());

    // submit form
    $('#payLoan').click(function() {
        swal({
            title: 'Are You  Sure?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => $('form').submit());
    });

    // Load lenders
    $('#lender').select2({
        ajax: {
            url: "{{ route('biller.loans.lenders') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({keyword: term}),
            processResults: function(data) {
                return {results: data.map(v => ({id: v.id, text: v.holder}))}; 
            },
        }
    });

    // On adding paid values
    $('#loansTbl').on('change', '.paid, .interest, .penalty', function() {
        if ($(this).is('.paid')) {
            const amount = $(this).parents('tr').find('.amount').text().replace(/,/g, '') * 1;
            const paid = $(this).val().replace(/,/g, '');
            if (paid > amount) $(this).val(amount.toLocaleString());
        }
        $('#interest').attr('required', false);
        $('#penalty').attr('required', false);
        calcTotal();
    });

    // bill row
    function loanRow(v, i) {
        const amount = parseFloat(v.amount - v.amountpaid).toLocaleString();
        return `
            <tr>
                <td class="text-center">${new Date(v.date).toDateString()}</td>
                <td class="text-center">${v.tid}</td>
                <td class="text-center">${v.note}</td>
                <td>${v.status}</td>
                <td class="text-center amount"><b>${amount}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]"></td>
                <td><input type="text" class="form-control interest" name="interest[]"></td>
                <td><input type="text" class="form-control penalty" name="penalty[]"></td>
                <input type="hidden" name="loan_id[]" value="${v.id}">
            </tr>
        `;
    }

    // load bills
    $('#lender').change(function() {
        $.ajax({
            url: "{{ route('biller.loans.lender_loans') }}?id=" + $(this).val(),
            success: result => {
                $('#loansTbl tbody tr:not(:eq(-1))').remove();
                result.forEach((v, i) => {
                    $('#loansTbl tbody tr:eq(-1)').before(loanRow(v, i));
                });
            }
        });
    });

    // On deposit change
    $('#amount').focus(function() { if (!$('#lender').val()) $(this).blur();  });
    $('#amount').change(function(e) {
        let amountSum = 0;
        let depoSum = 0;
        let depo = $(this).val().replace(/,/g, '') * 1;
        $(this).val(parseFloat(depo).toLocaleString());
        $('#loansTbl tbody tr').each(function(i) {
            if ($('#loansTbl tbody tr:last').index() == i) return;
            const amount = $(this).find('.amount').text().replace(/,/g, '') * 1;
            if (depo > amount) $(this).find('.paid').val(amount.toLocaleString()).change();
            else if (depo > 0) $(this).find('.paid').val(depo.toLocaleString()).change();
            else $(this).find('.paid').val(0);
            const paid = $(this).find('.paid').val().replace(/,/g, '');
            depo -= amount;
            amountSum += amount;
            depoSum += paid * 1;
        });
        $('#amount_ttl').val(parseFloat(amountSum.toFixed(2)).toLocaleString());
        $('#deposit_ttl').val(parseFloat(depoSum.toFixed(2)).toLocaleString());
    });

    function calcTotal() {
        let amountSum = 0;
        let depoSum = 0;
        $('#loansTbl tbody tr').each(function(i) {
            if ($('#loansTbl tbody tr:last').index() == i) return;
            const amount = $(this).find('.amount').text().replace(/,/g, '');
            const paid = $(this).find('.paid').val().replace(/,/g, '');
            amountSum += amount * 1;
            depoSum += paid * 1;

            const interest = $(this).find('.interest').val();
            if (interest) $('#interest').attr('required', true);
            const penalty = $(this).find('.penalty').val();
            if (penalty) $('#penalty').attr('required', true);
        });
        $('#amount_ttl').val(parseFloat(amountSum.toFixed(2)).toLocaleString());
        $('#deposit_ttl').val(parseFloat(depoSum.toFixed(2)).toLocaleString());
    }
</script>
@endsection