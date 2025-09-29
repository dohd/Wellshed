<div class="modal fade" id="changeModal" role="dialog" aria-labelledby="changeModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Product Attachment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($supplier_product, ['route' => array('biller.pricelistsSupplier.change_attachment', $supplier_product), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product_id">Search Inventory Product</label>
                        <select class="custom-select" name="product_id" id="product_id" data-placeholder="Search Inventory Product" required>
                            <option value="">Search Inventory Product</option>
                            @foreach ($products as $product)
                                <option value="{{$product->id}}" product_code="{{$product->code}}" uom="{{@$product->product->unit->code}}" qty="{{$product->qty}}">{{$product->name}}</option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="product_code">Product Code</label>
                        <input type="text" id="product_code"  class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="uom">Unit of Measure</label>
                        <input type="text" id="uom" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="qty">Quantity</label>
                        <input type="number" id="qty"  class="form-control" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>