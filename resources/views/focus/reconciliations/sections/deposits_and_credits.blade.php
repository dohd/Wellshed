<h5><b>Deposits And Other Credits Cleared (<span id="deposits-cleared">0</span>)</b></h5>
<div class="table-responsive mb-2" style="max-height: 60vh">        
    <table id="deposits-cleared-tbl" class="table text-center">
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
            @foreach ($reconciliation->items->whereNotNull('checked') as $item)
                @if ($item->journal && $item->journal_item)
                    @php
                        $journal = $item->journal;
                        $journal_item = $item->journal_item;
                        if ($journal_item->credit > 0) continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($journal->date) }}</td>
                        <td>{{ $journal_item->debit == 0? 'cash-out' : 'cash-in' }}</td>
                        <td>{{ gen4tid('JNL-', $journal->tid) }}</td>
                        <td>{{ @$journal_item->customer->company ?: @$journal_item->customer->name }}</td>
                        <td>{{ $journal->note }}</td>
                        <td>{{ numberFormat($journal_item->debit) }}</td>
                        <td></td>
                    </tr>
                @elseif ($item->deposit)
                    @php $deposit = $item->deposit @endphp
                    <tr>
                        <td>{{ dateFormat($deposit->date) }}</td>
                        <td>{{ 'cash-in' }}</td>
                        <td>{{ gen4tid('PMT-', $deposit->tid) }}</td>
                        <td>{{ @$deposit->customer->company }}</td>
                        <td>{{ $deposit->note }}</td>
                        <td>{{ numberFormat($deposit->amount) }}</td>
                        <td></td>
                    </tr>
                @elseif ($item->bank_transfer)
                    @php 
                        $bank_transfer = $item->bank_transfer;
                        if ($reconciliation->account_id == $bank_transfer->account_id) continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($bank_transfer->transaction_date) }}</td>
                        <td>{{ $reconciliation->account_id == $bank_transfer->account_id? 'cash-out' : 'cash-in' }}</td>
                        <td>{{ gen4tid('XFER-', $bank_transfer->tid) }}</td>
                        <td></td>
                        <td>{{ $bank_transfer->note }}</td>
                        <td>{{ numberFormat($bank_transfer->amount) }}</td>
                        <td></td>
                    </tr>                                 
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th><h5 class="font-weight-bold">Total</h5></th>
                <th colspan="4"></th>
                <th><h5 class="font-weight-bold deposits-cleared-total">0.00</h5></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>