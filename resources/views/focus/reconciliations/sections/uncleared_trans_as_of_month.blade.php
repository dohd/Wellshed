<h5><b>Uncleared Transactions As Of {{ dateFormat($model->ending_period) }} (<span id="uncleared-tr-ep">0</span>)</b></h5>
<div class="table-responsive mb-3" style="max-height: 60vh">        
    <table id="uncleared-tr-ep-tbl" class="table text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Date</th>
                <th>Type</th>
                <th>Trans. Ref</th>
                <th>Payer / Payee</th>
                <th>Note</th>
                <th width="15%">Debit</th>
                <th width="15%">Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reconciliation->items->whereNull('checked') as $item)
                @if ($item->journal && $item->journal_item)
                    @php
                        $journal = $item->journal;
                        $journal_item = $item->journal_item;
                        if ($journal_item->debit > 0) continue;
                        $periodStart = $carbon::parse($model->ending_period)->startOfMonth();
                        $periodEnd = $carbon::parse($model->ending_period);
                        if ($carbon::parse($journal->date)->lt($periodStart) || $carbon::parse($journal->date)->gt($periodEnd)) 
                            continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($journal->date) }}</td>
                        <td>{{ $journal_item->debit == 0? 'cash-out' : 'cash-in' }}</td>
                        <td>{{ gen4tid('JNL-', $journal->tid) }}</td>
                        <td>{{ @$journal_item->supplier->name }}</td>
                        <td>{{ $journal->note }}</td>
                        @if ($journal_item->debit > 0)
                            <td>{{ numberFormat($journal_item->debit) }}</td>
                            <td></td>
                        @elseif ($journal_item->credit > 0)
                            <td></td>
                            <td>{{ numberFormat($journal_item->credit) }}</td>
                        @endif
                    </tr>
                @elseif ($item->deposit)
                    @php 
                        $deposit = $item->deposit;
                        $periodStart = $carbon::parse($model->ending_period)->startOfMonth();
                        $periodEnd = $carbon::parse($model->ending_period);
                        if ($carbon::parse($deposit->date)->lt($periodStart) || $carbon::parse($deposit->date)->gt($periodEnd)) 
                            continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($deposit->date) }}</td>
                        <td>{{ 'cash-in' }}</td>
                        <td>{{ gen4tid('PMT-', $deposit->tid) }}</td>
                        <td>{{ @$deposit->customer->company }}</td>
                        <td>{{ $deposit->note }}</td>
                        <td>{{ numberFormat($deposit->amount) }}</td>
                        <td></td>
                    </tr>
                @elseif ($item->payment)
                    @php 
                        $payment = $item->payment;
                        $periodStart = $carbon::parse($model->ending_period)->startOfMonth();
                        $periodEnd = $carbon::parse($model->ending_period);
                        if ($carbon::parse($payment->date)->lt($periodStart) || $carbon::parse($payment->date)->gt($periodEnd))
                            continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($payment->date) }}</td>
                        <td>{{ 'cash-out' }}</td>
                        <td>{{ gen4tid('RMT-', $payment->tid) }}</td>
                        <td>{{ @$payment->supplier->name }}</td>
                        <td>{{ $payment->note }}</td>
                        <td></td>
                        <td>{{ numberFormat($payment->amount) }}</td>
                    </tr>
                @elseif ($item->creditnote)
                    @php 
                        $creditnote = $item->creditnote;
                        $periodStart = $carbon::parse($model->ending_period)->startOfMonth();
                        $periodEnd = $carbon::parse($model->ending_period);
                        if ($carbon::parse($creditnote->date)->lt($periodStart) || $carbon::parse($creditnote->date)->gt($periodEnd))
                            continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($creditnote->date) }}</td>
                        <td>{{ 'cash-out' }}</td>
                        <td>{{ gen4tid('CN-', $creditnote->tid) }}</td>
                        <td>{{ @$creditnote->customer->company }}</td>
                        <td>{{ $creditnote->note }}</td>
                        <td></td>
                        <td>{{ numberFormat($creditnote->amount) }}</td>
                    </tr>
                @elseif ($item->bank_transfer)
                    @php 
                        $bank_transfer = $item->bank_transfer;
                        if ($reconciliation->account_id != $bank_transfer->account_id) continue;
                        $periodStart = $carbon::parse($model->ending_period)->startOfMonth();
                        $periodEnd = $carbon::parse($model->ending_period);
                        if ($carbon::parse($bank_transfer->transaction_date)->lt($periodStart) || $carbon::parse($bank_transfer->transaction_date)->gt($periodEnd))
                            continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($bank_transfer->transaction_date) }}</td>
                        <td>{{ $reconciliation->account_id == $bank_transfer->account_id? 'cash-out' : 'cash-in' }}</td>
                        <td>{{ gen4tid('XFER-', $bank_transfer->tid) }}</td>
                        <td></td>
                        <td>{{ $bank_transfer->note }}</td>
                        @if ($reconciliation->account_id == $bank_transfer->account_id)
                            <td></td>
                            <td>{{ numberFormat($bank_transfer->amount) }}</td>
                        @else
                            <td>{{ numberFormat($bank_transfer->amount) }}</td>
                            <td></td>
                        @endif
                    </tr>     
                @elseif ($item->charge)
                    @php 
                        $charge = $item->charge;
                        $periodStart = $carbon::parse($model->ending_period)->startOfMonth();
                        $periodEnd = $carbon::parse($model->ending_period);
                        if ($carbon::parse($charge->date)->lt($periodStart) || $carbon::parse($charge->date)->gt($periodEnd))
                            continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($charge->date) }}</td>
                        <td>{{ 'cash-out' }}</td>
                        <td>{{ gen4tid('CHRG-', $charge->tid) }}</td>
                        <td></td>
                        <td>{{ $charge->note }}</td>
                        <td></td>
                        <td>{{ numberFormat($charge->amount) }}</td>
                    </tr>                                
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th><h5 class="font-weight-bold">Total: <span id="uncleared-tr-ep-total">0.00</span></h5></th>
                <th colspan="4"></th>
                <th><h5 class="font-weight-bold uncleared-tr-ep-dtotal">0.00</h5></th>
                <th><h5 class="font-weight-bold uncleared-tr-ep-ctotal">0.00</h5></th>
            </tr>
        </tfoot>
    </table>
</div>
