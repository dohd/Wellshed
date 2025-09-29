<div class="modal fade" id="productModal"  role="dialog" aria-labelledby="productModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Link to An Inventory Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('biller.sell_prices.product_link')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product">Search Product</label>
                        <select class="custom-select" name="product_id" id="product" data-placeholder="Search Product">
                            <option value="">Search Product</option>
                            @foreach ($products as $product)
                               <option value="{{$product->id}}">{{$product->name}}</option> 
                            @endforeach                            
                        </select>
                    </div>
                </div>
                <input type="hidden" name="id" id="item_id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary"]) }}
                </div>
            </form>
        </div>
    </div>
</div>