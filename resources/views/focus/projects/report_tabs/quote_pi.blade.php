<div class="tab-pane in" id="tab_data13" aria-labelledby="tab13" role="tabpanel">
    <div class="card">
        <div class="card-body">
            <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Quote/PI No.</th>
                        <th>Customer - Branch</th>
                        <th>Title</th>
                        <th>Amount</th>
                        <th>Approval Date</th>
                        <th>Client Ref</th>
                        <th>Ticket No</th>
                        <th>Invoice No</th>
                    </tr>
                </thead>
                <tbody>
                    
                        @foreach ($project->quotes as $i => $quote)
                        @if ($quote)
                        <tr>
                            @php
                                 $customer = '';
                                if ($quote->customer) {
                                    $customer .= $quote->customer->company;
                                    if ($quote->branch) $customer .= " - {$quote->branch->name}";
                                } elseif ($quote->lead) {
                                    $customer .= $quote->lead->client_name;
                                }
                                $approval_date = $quote->approved_date? dateFormat($quote->approved_date) : '';
                                $lead = $quote->lead ? gen4tid("TKT-", $quote->lead->reference) : '';
                                $inv_product = $quote->invoice_product;
                                $invoice_no = '';
                                if (@$inv_product->invoice) $invoice_no = gen4tid("INV-", $inv_product->invoice->tid);
                                elseif(@$quote->invoice_quote) $invoice_no = gen4tid("INV-", $quote->invoice_quote->tid);
                            @endphp 
                            <td>{{$i+1}}</td>
                            <td>{{ gen4tid($quote->bank_id? "PI-" : "QT-", $quote->tid)}}</td>
                            <td>{{$customer}}</td>
                            <td>{{$quote->notes}}</td>
                            <td>{{numberFormat($quote->total)}}</td>
                            <td>{{$approval_date}}</td>
                            <td>{{$quote->client_ref}}</td>
                            <td>{{$lead}}</td>
                            <td>{{$invoice_no}}</td>
                        </tr>
                            
                        @endif
                           
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
</div>
