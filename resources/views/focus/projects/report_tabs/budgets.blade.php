<div class="tab-pane in" id="tab_data3" aria-labelledby="tab3" role="tabpanel">
    <div class="card-body">
        <table id="budgetsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tr No.</th>
                    <th>Customer - Branch</th>
                    <th>Quoted Amount</th>
                    <th>Budgeted Amount</th>
                    {{-- <th>Actions</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($budgets as $i => $budget)
                    <tr>
                        @php
                            $customer = '';
                            $tid = '';
                            $quoted_amount = 0;
                            $budget_total = 0;
                             if ($budget->quote) {
                                $quote = $budget->quote;
                                $customer = $quote->customer? $quote->customer->company : '';
                                if ($quote->branch) $customer .= " - {$quote->branch->name}";
                                $tid = gen4tid($quote->bank_id? "PI-" : "QT-", $quote->tid);
                                $quoted_amount = numberFormat($budget->quote->total);
                                $budget_total = $budget->items()->sum(DB::raw('round(new_qty*price)'));
                            }
                        @endphp
                        <td>{{$i+1}}</td>
                        <td>{{$tid}}</td>
                        <td>{{$customer}}</td>
                        <td>{{$quoted_amount}}</td>
                        <td>{{numberFormat($budget_total)}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>