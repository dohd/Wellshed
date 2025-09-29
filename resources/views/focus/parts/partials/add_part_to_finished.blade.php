<div class="modal fade" id="addpartModal" tabindex="-1" role="dialog" aria-labelledby="addpartModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Add Finished Product To Inventory</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($part, ['route' => array('biller.parts.add_finished_product', $part), 'method' => 'POST' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Search Product</label>
                        <select class="custom-select" name="product_id" id="product" data-placeholder="Search Product">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ @$part && $part->product_id == $product->id? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        {{ Form::text('note', null, ['class' => 'form-control']) }}
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