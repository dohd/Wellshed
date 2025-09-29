@extends ('core.layouts.app')
@section ('title', 'View | Reconciliation Management')

@php $model = $reconciliation @endphp
@inject('carbon', 'Carbon\Carbon')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ @$model->account->holder }} | Reconciliation Summary | Period Ending {{ dateFormat($model->ending_period) }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.reconciliations.partials.reconciliations-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body pr-3 pl-3">
            <div class="button-group mb-1">
                <a href="{{ route('biller.reconciliations.print_pdf', $model) }}?type=detail" class="btn btn-purple btn-sm mr-1" target="_blank">
                    <i class="fa fa-print"></i> Detail Report
                </a>
                <a href="{{ route('biller.reconciliations.print_pdf', $model) }}?type=summary" class="btn btn-purple btn-sm mr-1" target="_blank">
                    <i class="fa fa-print"></i> Summary Report
                </a>
            </div>

            <!-- Summary Details -->
            <div class="row">
                <div class="col-6">
                    <table id="summaryTbl" class="table table-lg table-bordered zero-configuration mb-2" cellspacing="0" width="100%">
                        <tbody>
                            @php
                                $recon_details = [
                                    'Statement Beginning Balance' => $model->begin_balance,   
                                    'Checks And Payments' => $model->cash_out,                     
                                    'Deposits And Other Credits' => $model->cash_in,   
                                    'Cleared Balance' => $model->cleared_balance,
                                    'Statement Ending Balance' => $model->end_balance,     
                                    'Difference' => $model->balance_diff,     
                                ];
                            @endphp
                            @foreach ($recon_details as $key => $val)
                                <tr>
                                    <td width="40%" class="pb-0">{{ $key }}</td>
                                    <th class="pb-0">{{ numberFormat($val) }}</th>
                                </tr> 
                            @endforeach                                      
                        </tbody>
                    </table>
                </div>
                <div class="col-6">
                    <table id="summary1Tbl" class="table table-lg table-bordered zero-configuration mb-2" cellspacing="0" width="100%">
                        <tbody>
                            @php
                                $model = $reconciliation;
                                $endingPeriod = dateFormat($model->ending_period);
                                $recon_details = [
                                    'Uncleared Transactions As Of ' . $endingPeriod => $model->ep_uncleared_balance,
                                    'Account Balance As Of ' . $endingPeriod => $model->ep_account_balance,
                                    'Cleared Transactions After ' . $endingPeriod => $model->cleared_balance_after_ep,
                                    'Uncleared Transactions After ' . $endingPeriod => $model->uncleared_balance_after_ep,
                                    'Account Balance As Of ' .  dateFormat($model->reconciled_on) => $model->ro_account_balance,   
                                ];
                            @endphp
                            @foreach ($recon_details as $key => $val)
                                <tr>
                                    <td width="40%" class="pb-0">{{ $key }}</td>
                                    <th class="pb-0">{{ numberFormat($val) }}</th>
                                </tr> 
                            @endforeach                                      
                        </tbody>
                    </table>
                </div>
            </div>

            <h5>Details</h5>
            <!-- Checks And Payments Cleared -->
            @include('focus.reconciliations.sections.checks_and_payments')

            <!-- Deposits and other credits cleared -->
            @include('focus.reconciliations.sections.deposits_and_credits')

            <h5>Other Details</h5>
            <!-- Uncleared transactions as of ending period -->
            @include('focus.reconciliations.sections.uncleared_trans_as_of_month')

            <!-- Cleared transactions after ending period -->
            @include('focus.reconciliations.sections.cleared_trans_after_month')

            <!-- Uncleared transactions after ending period -->
            @include('focus.reconciliations.sections.uncleared_trans_after_month')
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
    };

    const View = {
        init() {
            $.ajaxSetup(config.ajax);

            // count records per category
            View.checksAndPaymentsCleared();
            View.depositsAndOtherCreditsCleared();
            View.unclearedTransactionsAsOfEndingPeriod();    
            View.clearedTransactionsAfterEndingPeriod();

            View.fetchUnclearedRecords();
            View.fetchUnclearedRecordsAfterEndMonth();
        },

        // checks and payments cleared
        checksAndPaymentsCleared() {
            let rows1 = 0;
            let creditTotal = 0;
            $('#payments-cleared-tbl tbody tr').each(function() {
                rows1++;
                const credit = accounting.unformat($(this).find('td:last').html());
                creditTotal += credit;
            });
            $('#payments-cleared').html(rows1);
            $('.payments-cleared-total').html(accounting.formatNumber(creditTotal));        
        },

        // deposists and other credits cleared
        depositsAndOtherCreditsCleared() {
            let rows2 = 0;
            let debitTotal = 0;
            $('#deposits-cleared-tbl tbody tr').each(function() {
                rows2++;
                const debit = accounting.unformat($(this).find('td:eq(5)').html());
                debitTotal += debit;
            });
            $('#deposits-cleared').html(rows2);
            $('.deposits-cleared-total').html(accounting.formatNumber(debitTotal));
        },

        // uncleared transactions as of ending period
        unclearedTransactionsAsOfEndingPeriod() {
            let rows3 = 0;
            debitTotal = 0;
            creditTotal = 0;
            $('#uncleared-tr-ep-tbl tbody tr').each(function() {
                rows3++;
                const debit = accounting.unformat($(this).find('td:eq(5)').html());
                const credit = accounting.unformat($(this).find('td:last').html());
                debitTotal += debit;
                creditTotal += credit;
            });
            $('#uncleared-tr-ep').html(rows3);
            $('.uncleared-tr-ep-dtotal').html(accounting.formatNumber(debitTotal));
            $('.uncleared-tr-ep-ctotal').html(accounting.formatNumber(creditTotal));
            $('#uncleared-tr-ep-total').html(accounting.formatNumber(debitTotal-creditTotal));        
        },

        // cleared transactions after ending period
        clearedTransactionsAfterEndingPeriod() {
            let rows4 = 0;
            debitTotal = 0;
            creditTotal = 0;
            $('#cleared-tr-after-ep-tbl tbody tr').each(function() {
                rows4++;
                const debit = accounting.unformat($(this).find('td:eq(5)').html());
                const credit = accounting.unformat($(this).find('td:last').html());
                debitTotal += debit;
                creditTotal += credit;
            });
            $('#cleared-tr-after-ep').html(rows4);
            $('.cleared-tr-after-ep-dtotal').html(accounting.formatNumber(debitTotal));
            $('.cleared-tr-after-ep-ctotal').html(accounting.formatNumber(creditTotal));
            $('#cleared-tr-after-ep-total').html(accounting.formatNumber(debitTotal-creditTotal));        
        },

        // uncleared transactions after ending period
        unclearedTransactionsAfterEndingPeriod() {
            let rows5 = 0;
            debitTotal = 0;
            creditTotal = 0;
            $('#uncleared-tr-after-ep-tbl tbody tr').each(function() {
                rows5++;
                const debit = accounting.unformat($(this).find('td:eq(5)').html());
                const credit = accounting.unformat($(this).find('td:last').html());
                debitTotal += debit;
                creditTotal += credit;
            });
            const total = debitTotal-creditTotal;
            $('#uncleared-tr-after-ep').html(rows5);
            $('.uncleared-tr-after-ep-dtotal').html(accounting.formatNumber(debitTotal));
            $('.uncleared-tr-after-ep-ctotal').html(accounting.formatNumber(creditTotal));
            $('#uncleared-tr-after-ep-total').html(accounting.formatNumber(total));        
            $('#summary1Tbl th:eq(3)').html(accounting.formatNumber(total));
        },

        // Fetch uncleared records as of ending month
        fetchUnclearedRecords() {
            // cleared record ids
            const bankTransferIds = @json($model->items->pluck('bank_transfer_id')->filter()->implode(','));
            const chargeIds = @json($model->items->pluck('charge_id')->filter()->implode(','));
            const depositIds = @json($model->items->pluck('deposit_id')->filter()->implode(','));
            const journalItemIds = @json($model->items->pluck('journal_item_id')->filter()->implode(','));
            const paymentIds = @json($model->items->pluck('payment_id')->filter()->implode(','));
            const creditNoteIds = @json($model->items->pluck('creditnote_id')->filter()->implode(','));

            const url = "{{ route('biller.reconciliations.account_items') }}";
            const params = {
                account_id: "{{ $model->account_id }}", 
                end_date: "{{ $model->end_date }}", 
                bank_transfer_ids: bankTransferIds,
                charge_ids: chargeIds,
                deposit_ids: depositIds,
                journal_item_ids: journalItemIds,
                payment_ids: paymentIds,
                creditnote_ids: creditNoteIds,
            };
            $.post(url, params).done(data => {
                if (!data.length) return;
                data.forEach((v,i) => {
                    const amount = accounting.unformat(v.amount);
                    const date = v.date? v.date.split('-').reverse().join('-') : '';
                    const row = `<tr>
                        <td>${date}</td>
                        <td>${v.type}</td>
                        <td>${v.trans_ref}</td>
                        <td>${v.client_supplier || ''}</td>
                        <td>${v.note || ''}</td>
                        <td>${v.type == 'cash-in'? accounting.formatNumber(amount) : ''}</td>
                        <td>${v.type == 'cash-out'? accounting.formatNumber(amount) : ''}</td>
                    </tr>`;
                    $('#uncleared-tr-ep-tbl tbody').append(row);
                });
                // compute balance
                View.unclearedTransactionsAsOfEndingPeriod();
            })
            .fail((xhr, status, error) => console.log(error));
        },

        // Fetch uncleared records after ending month
        fetchUnclearedRecordsAfterEndMonth() {
            const url = "{{ route('biller.reconciliations.post_uncleared_account_items') }}";
            const params = {
                account_id: "{{ $model->account_id }}", 
                end_date: "{{ $model->end_date }}", 
            };
            $.post(url, params).done(data => {
                (data.length? data : []).forEach((v,i) => {
                    const amount = accounting.unformat(v.amount);
                    const date = v.date? v.date.split('-').reverse().join('-') : '';
                    const row = `<tr>
                        <td>${date}</td>
                        <td>${v.type}</td>
                        <td>${v.trans_ref}</td>
                        <td>${v.client_supplier || ''}</td>
                        <td>${v.note || ''}</td>
                        <td>${v.type == 'cash-in'? accounting.formatNumber(amount) : ''}</td>
                        <td>${v.type == 'cash-out'? accounting.formatNumber(amount) : ''}</td>
                    </tr>`;
                    $('#uncleared-tr-after-ep-tbl tbody').append(row);
                });
                // compute balance
                View.unclearedTransactionsAfterEndingPeriod();
            })
            .fail((xhr, status, error) => console.log(error));
        },
    }

    $(View.init);
</script>
@endsection