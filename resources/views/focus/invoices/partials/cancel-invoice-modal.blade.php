<!-- cancel -->
<div id="cancel-invoice-modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cancel Invoice</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['route' => ['biller.invoices.nullify_invoice', $invoice], 'method' => 'POST' ]) }}
                    <div class="alert alert-danger p-1 round">{{trans('general.irreversible_action')}}</div>
                    <div class="modal-footer">                        
                        <button type="button" class="btn btn-info" data-dismiss="modal">{{trans('general.close')}}</button>
                        {{ Form::submit('Cancel Invoice', ['class' => "btn btn-danger"]) }}
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
