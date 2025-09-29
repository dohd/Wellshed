<div class="modal fade" id="rfqPrintModal" role="dialog" aria-labelledby="rfqPrintModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Print RFQ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($rfq, ['route' => array('biller.print-rfq', $rfq), 'method' => 'POST', 'target' => '_blank' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="supplier">Search Supplier</label>
                        <select name="supplier_id" id="supplier_ids" data-placeholder="Search Suppliers" class="form-control">
                            <option value="">Search Suppliers</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{$supplier->id}}">{{$supplier->company}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Print', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>