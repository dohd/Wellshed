<h5><b>Checks And Payments Cleared (<span id="payments-cleared">0</span>)</b></h5>
<div class="table-responsive mb-1" style="max-height: 60vh">        
    <table id="payments-cleared-tbl" class="table text-center">
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
                @if ($item->payment && $item->journal_item)
                    @php
                        $journal = $item->journal;
                        $journal_item = $item->journal_item;
                        if ($journal_item->debit > 0) continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($journal->date) }}</td>
                        <td>{{ $journal_item->debit == 0? 'cash-out' : 'cash-in' }}</td>
                        <td>{{ gen4tid('JNL-', $journal->tid) }}</td>
                        <td>{{ @$journal_item->supplier->name }}</td>
                        <td>{{ $journal->note }}</td>
                        <td></td>
                        <td>{{ numberFormat($journal_item->credit) }}</td>
                    </tr>
                @elseif ($item->payment)
                    @php $payment = $item->payment @endphp
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
                    @php $creditnote = $item->creditnote @endphp
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
                    @endphp
                    <tr>
                        <td>{{ dateFormat($bank_transfer->transaction_date) }}</td>
                        <td>{{ $reconciliation->account_id == $bank_transfer->account_id? 'cash-out' : 'cash-in' }}</td>
                        <td>{{ gen4tid('XFER-', $bank_transfer->tid) }}</td>
                        <td></td>
                        <td>{{ $bank_transfer->note }}</td>
                        <td></td>
                        <td>{{ numberFormat($bank_transfer->amount) }}</td>
                    </tr>     
                @elseif ($item->charge)
                    @php $charge = $item->charge @endphp
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
                <th><h5 class="font-weight-bold">Total</h5></th>
                <th colspan="5"></th>
                <th><h5 class="font-weight-bold payments-cleared-total">0.00</h5></th>
            </tr>
        </tfoot>
    </table>
</div>