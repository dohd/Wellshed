<div class="modal fade" id="inventoryModal" role="dialog" aria-labelledby="inventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="inventoryModalLabel">Attach Inventory</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            {{ Form::open(['route' => 'biller.client_products.store_code']) }}
            <div class="form-group row">
                <div class="col-6 mt-2">
                    <label for="inventory">System Description</label>
                    <select name="inventory_id" id="inventorybox" class="form-control" data-placeholder="Choose-Product" required>
                    </select>
                    <input type="hidden" name="system_name" id="inventory">
                </div>
                <div class="col-6 mt-2">
                    <label for="product_code">Product Code</label>
                    {{ Form::text('product_code', null, ['class' => 'form-control', 'readonly', 'id' =>'product_code', 'required']) }}
                    <input type="hidden" id="id" name="id">
                    <input type="hidden" id="item_id" name="item_id">
                </div>
               
            </div>
            <div class="form-group row">
                <div class="col-6 mt-2">
                    <label for="description">Item Description</label>
                    {{ Form::text('descr', null, ['class' => 'form-control', 'readonly', 'required', 'id'=>'descr']) }}
                </div>
                <div class="col-6 mt-2">
                    <label for="item_qty">Quantity</label>
                    {{ Form::text('item_qty', null, ['class' => 'form-control', 'readonly', 'id'=>'item_qty']) }}
                </div>
                
            </div>
            <div class="form-group row">
              <div class="col-6 mt-2">
                  <label for="client_uom">Client Pricelist Uom</label>
                  {{ Form::text('client_uom', null, ['class' => 'form-control', 'readonly', 'required', 'id'=>'client_uom']) }}
              </div>
              <div class="col-6 mt-2">
                  <label for="client_price">Client Pricelist Purchase Price</label>
                  {{ Form::text('client_price', null, ['class' => 'form-control', 'readonly', 'id'=>'client_price']) }}
              </div>
              
          </div>
          <div class="form-group row">
            <div class="col-6 mt-2">
                <label for="uom">Inventory Uom</label>
                {{ Form::text('uom', null, ['class' => 'form-control', 'readonly', 'required', 'id'=>'uom']) }}
            </div>
            <div class="col-6 mt-2">
                <label for="purchase_price">Buying Price</label>
                {{ Form::text('purchase_price', null, ['class' => 'form-control', 'readonly', 'id'=>'purchase_price']) }}
            </div>
            
        </div>
            
            <div class="edit-form-btn float-right">
                {{ link_to_route('biller.client_products.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                {{ Form::submit(@$client_product? 'Update' : 'Create', ['class' => 'btn btn-primary btn-md']) }}                                            
            </div>     
            {{ Form::close() }}
            
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>    
