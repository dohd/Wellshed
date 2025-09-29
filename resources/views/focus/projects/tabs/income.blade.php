<div class="tab-pane" id="tab_data10" aria-labelledby="tab10" role="tabpanel">
    @if($project->creator->id == auth()->user()->id)
        <div class="card-body">
            <button type="button" class="btn btn-info float-right mr-2" id="addinvoice" data-toggle="modal"
                    data-target="#AddDetachedInvoiceModal">
                    <i class="fa fa-plus-circle"></i> Attach Detached Invoice
            </button>
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
                        <th>{{ trans('general.status') }}</th>
                        <th>{{ trans('invoices.invoice_due_date') }}</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endif
</div>
