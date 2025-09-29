<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 80vh">
                <table class="table tfr text-center" id="transactions">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th>Date</th>
                            <th>Type</th>
                            <th>Trans. Ref</th>
                            <th>Payer / Payee</th>
                            <th>Note</th>
                            <th class="d-none" width="15%">Amount</th>
                            <th width="15%">Debit</th>
                            <th width="15%">Credit</th>
                            <th><input id="check-all" type="checkbox" autocomplete="off"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($reconciliation->items))
                            @foreach ($reconciliation->items as $item)
                                @if ($item->journal && $item->journal_item)
                                    @php
                                        $journal = $item->journal;
                                        $journal_item = $item->journal_item;
                                        $journal_item->credit = +$journal_item->credit;
                                        $journal_item->debit = +$journal_item->debit;
                                    @endphp
                                    <tr>
                                        <td class="date">{{ dateFormat($journal->date) }}</td>
                                        <td class="type">{{ $journal_item->credit > 0? 'cash-out' : ($journal_item->debit > 0? 'cash-in' : '') }}</td>
                                        <td class="trans-ref">{{ gen4tid('JNL-', $journal->tid) }}</td>
                                        <td class="client-supplier"></td>
                                        <td class="note">{{ $journal->note }}</td>
                                        <td class="d-none"><span class="cash">{{ $journal_item->credit > 0? numberFormat($journal_item->credit) : numberFormat($journal_item->debit) }}</span></td>
                                        @if ($journal_item->debit > 0)
                                            <td><span class="debit">{{ numberFormat($journal_item->debit) }}</span></td>
                                            <td><span class="credit"></span></td>
                                        @elseif ($journal_item->credit > 0)
                                            <td><span class="debit"></span></td>
                                            <td><span class="credit">{{ numberFormat($journal_item->credit) }}</span></td>
                                        @endif
                                        <td><input class="check" type="checkbox" autocomplete="off"></td>
                                        <input type="hidden" name="checked[]" value="{{ $item->checked }}" class="check-inp">
                                        <input type="hidden" name="man_journal_id[]" value="{{ $journal->id }}" class="journal-id">
                                        <input type="hidden" name="journal_item_id[]" value="{{ $journal_item->id }}" class="journalitem-id">
                                        <input type="hidden" name="payment_id[]" class="pmt-id">
                                        <input type="hidden" name="deposit_id[]" class="dep-id">
                                        <input type="hidden" name="bank_transfer_id[]" class="bankxfer-id">
                                        <input type="hidden" name="charge_id[]" class="charge-id">
                                        <input type="hidden" name="creditnote_id[]" class="cnote-id">
                                    </tr> 
                                @elseif ($item->payment)
                                    @php $payment = $item->payment; @endphp
                                    <tr>
                                        <td class="date">{{ dateFormat($payment->date) }}</td>
                                        <td class="type">{{ 'cash-out' }}</td>
                                        <td class="trans-ref">{{ gen4tid('RMT-', $payment->tid) }}</td>
                                        <td class="client-supplier">{{ @$payment->supplier->name }}</td>
                                        <td class="note">{{ $payment->note }}</td>
                                        <td class="d-none"><span class="cash">{{ numberFormat($payment->amount) }}</span></td>
                                        <td><span class="debit"></span></td>
                                        <td><span class="credit">{{ numberFormat($payment->amount) }}</span></td>
                                        <td><input class="check" type="checkbox" autocomplete="off"></td>
                                        <input type="hidden" name="checked[]" value="{{ $item->checked }}" class="check-inp">
                                        <input type="hidden" name="man_journal_id[]" class="journal-id">
                                        <input type="hidden" name="journal_item_id[]" class="journalitem-id">
                                        <input type="hidden" name="payment_id[]" value="{{ $payment->id }}" class="pmt-id">
                                        <input type="hidden" name="deposit_id[]" class="dep-id">
                                        <input type="hidden" name="bank_transfer_id[]" class="bankxfer-id">
                                        <input type="hidden" name="charge_id[]" class="charge-id">
                                        <input type="hidden" name="creditnote_id[]" class="cnote-id">
                                    </tr> 
                                @elseif (@$item->deposit)
                                    @php $deposit = $item->deposit; @endphp
                                    <tr>
                                        <td class="date">{{ dateFormat($deposit->date) }}</td>
                                        <td class="type">{{ 'cash-in' }}</td>
                                        <td class="trans-ref">{{ gen4tid('PMT-', $deposit->tid) }}</td>
                                        <td class="client-supplier">{{ @$deposit->customer->company }}</td>
                                        <td class="note">{{ $deposit->note }}</td>
                                        <td class="d-none"><span class="cash">{{ numberFormat($deposit->amount) }}</span></td>
                                        <td><span class="debit">{{ numberFormat($deposit->amount) }}</span></td>
                                        <td><span class="credit"></span></td>
                                        <td><input class="check" type="checkbox" autocomplete="off"></td>
                                        <input type="hidden" name="checked[]" value="{{ $item->checked }}" class="check-inp">
                                        <input type="hidden" name="man_journal_id[]" class="journal-id">
                                        <input type="hidden" name="journal_item_id[]" class="journalitem-id">
                                        <input type="hidden" name="payment_id[]" class="pmt-id">
                                        <input type="hidden" name="bank_transfer_id[]" class="bankxfer-id">
                                        <input type="hidden" name="charge_id[]" class="charge-id">
                                        <input type="hidden" name="creditnote_id[]" class="cnote-id">
                                        <input type="hidden" name="deposit_id[]" value="{{ $deposit->id }}" class="dep-id">
                                    </tr> 
                                @elseif (@$item->creditnote)
                                    @php $creditnote = $item->creditnote; @endphp
                                    <tr>
                                        <td class="date">{{ dateFormat($creditnote->date) }}</td>
                                        <td class="type">{{ 'cash-out' }}</td>
                                        <td class="trans-ref">{{ gen4tid('CN-', $creditnote->tid) }}</td>
                                        <td class="client-supplier">{{ @$creditnote->customer->company }}</td>
                                        <td class="note">{{ $creditnote->note }}</td>
                                        <td class="d-none"><span class="cash">{{ numberFormat($creditnote->amount) }}</span></td>
                                        <td><span class="debit"></span></td>
                                        <td><span class="credit">{{ numberFormat($creditnote->amount) }}</span></td>
                                        <td><input class="check" type="checkbox" autocomplete="off"></td>
                                        <input type="hidden" name="checked[]" value="{{ $item->checked }}" class="check-inp">
                                        <input type="hidden" name="man_journal_id[]" class="journal-id">
                                        <input type="hidden" name="journal_item_id[]" class="journalitem-id">
                                        <input type="hidden" name="payment_id[]" class="pmt-id">
                                        <input type="hidden" name="bank_transfer_id[]" class="bankxfer-id">
                                        <input type="hidden" name="charge_id[]" class="charge-id">
                                        <input type="hidden" name="deposit_id[]" class="dep-id">
                                        <input type="hidden" name="creditnote_id[]" value="{{ $creditnote->id }}" class="cnote-id">
                                    </tr> 

                                @elseif ($item->bank_transfer)
                                    @php $bank_transfer = $item->bank_transfer @endphp
                                    <tr>
                                        <td class="date">{{ dateFormat($bank_transfer->transaction_date) }}</td>
                                        <td class="type">{{ $reconciliation->account_id == $bank_transfer->account_id? 'cash-out' : 'cash-in' }}</td>
                                        <td class="trans-ref">{{ gen4tid('XFER-', $bank_transfer->tid) }}</td>
                                        <td class="client-supplier"></td>
                                        <td class="note">{{ $bank_transfer->note }}</td>
                                        <td class="d-none"><span class="cash">{{ numberFormat($bank_transfer->amount) }}</span></td>
                                        <td><span class="debit">{{ $reconciliation->account_id == $bank_transfer->account_id? '' : numberFormat($bank_transfer->amount) }}</span></td>
                                        <td><span class="credit">{{ $reconciliation->account_id == $bank_transfer->account_id? numberFormat($bank_transfer->amount) : '' }}</span></td>
                                        <td><input class="check" type="checkbox" autocomplete="off"></td>
                                        <input type="hidden" name="checked[]" value="{{ $item->checked }}" class="check-inp">
                                        <input type="hidden" name="man_journal_id[]" class="journal-id">
                                        <input type="hidden" name="journal_item_id[]" class="journalitem-id">
                                        <input type="hidden" name="payment_id[]" class="pmt-id">
                                        <input type="hidden" name="bank_transfer_id[]" value="{{ $bank_transfer->id }}" class="bankxfer-id">
                                        <input type="hidden" name="charge_id[]" class="charge-id">
                                        <input type="hidden" name="deposit_id[]" class="dep-id">
                                        <input type="hidden" name="creditnote_id[]" class="cnote-id">
                                    </tr> 
                                @elseif ($item->charge)
                                    @php $charge = $item->charge @endphp
                                    <tr>
                                        <td class="date">{{ dateFormat($charge->date) }}</td>
                                        <td class="type">cash-out</td>
                                        <td class="trans-ref">{{ gen4tid('CHRG-', $charge->tid) }}</td>
                                        <td class="client-supplier"></td>
                                        <td class="note">{{ $charge->note }}</td>
                                        <td class="d-none"><span class="cash">{{ numberFormat($charge->amount) }}</span></td>
                                        <td><span class="debit"></span></td>
                                        <td><span class="credit">{{ numberFormat($charge->amount) }}</span></td>
                                        <td><input class="check" type="checkbox" autocomplete="off"></td>
                                        <input type="hidden" name="checked[]" value="{{ $item->checked }}" class="check-inp">
                                        <input type="hidden" name="man_journal_id[]" class="journal-id">
                                        <input type="hidden" name="journal_item_id[]" class="journalitem-id">
                                        <input type="hidden" name="payment_id[]" class="pmt-id">
                                        <input type="hidden" name="bank_transfer_id[]" class="bankxfer-id">
                                        <input type="hidden" name="charge_id[]" value="{{ $charge->id }}" class="charge-id">
                                        <input type="hidden" name="deposit_id[]" class="dep-id">
                                        <input type="hidden" name="creditnote_id[]" class="cnote-id">
                                    </tr> 
                                @endif
                            @endforeach
                        @else
                            <tr class="d-none">
                                <td class="date"></td>
                                <td class="type"></td>
                                <td class="trans-ref"></td>
                                <td class="client-supplier"></td>
                                <td class="note"></td>
                                <td class="d-none"><span class="cash"></span></td>
                                <td><span class="debit"></span></td>
                                <td><span class="credit"></span></td>
                                <td><input class="check" type="checkbox" autocomplete="off"></td>
                                <input type="hidden" name="checked[]" class="check-inp">
                                <input type="hidden" name="man_journal_id[]" class="journal-id">
                                <input type="hidden" name="journal_item_id[]" class="journalitem-id">
                                <input type="hidden" name="payment_id[]" class="pmt-id">
                                <input type="hidden" name="deposit_id[]" class="dep-id">
                                <input type="hidden" name="bank_transfer_id[]" class="bankxfer-id">
                                <input type="hidden" name="charge_id[]" class="charge-id">
                                <input type="hidden" name="creditnote_id[]" class="cnote-id">
                            </tr> 
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th colspan="4"></th>
                            <th class="dtotal">0.00</th>
                            <th class="ctotal">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>