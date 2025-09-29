<div class="tab-pane" id="tab_data9" aria-labelledby="tab9" role="tabpanel">
   
        <div class="card-body">
            
            <table id="invoices-table_p"
                    class="table table-striped table-bordered zero-configuration"
                    cellspacing="0"
                    width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No.</th>
                        <th>{{ trans('customers.customer') }}</th>
                        <th>{{ trans('invoices.invoice_date') }}</th>
                        <th>{{ trans('general.amount') }}</th>
                        <th>Outstanding</th>
                        <th>{{ trans('general.status') }}</th>
                        <th>{{ trans('invoices.invoice_due_date') }}</th>
                        {{-- <th>Action</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $i => $invoice)
                        <tr>
                            @php
                                $customer = $invoice->customer ? $invoice->customer->company : $invoice->customer->name;
                                $status = '<span class="st-' . $invoice->status . '">' . trans('payments.' . $invoice->status) . '</span>';
                            @endphp
                            <td>{{$i+1}}</td>
                            <td>{{ gen4tid("INV-", $invoice->tid)}}</td>
                            <td>{{$customer}}</td>
                            <td>{{dateFormat($invoice->invoicedate)}}</td>
                            <td>{{numberFormat($invoice->total)}}</td>
                            <td>{{numberFormat($invoice->total - $invoice->amountpaid)}}</td>
                            <td>{!!$status!!}</td>
                            <td>{{dateFormat($invoice->invoiceduedate)}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
</div>