

<div class="modal fade" id="exampleModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Update Queue Requisition</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            {{ Form::open(['route' => 'biller.queuerequisitions.update_description' ]) }}
            <div class="form-group row">
                <div class="col-6 mt-2">
                    <label for="supplier">System Description</label>
                    <select name="supplier_id" id="supplierbox" class="form-control" data-placeholder="Choose-Product" required>
                    </select>
                    <input type="hidden" name="system_name" id="supplier">
                </div>
                <div class="col-6 mt-2">
                    <label for="product_code">Product Code</label>
                    {{ Form::text('product_code', null, ['class' => 'form-control', 'readonly', 'id' =>'product_code', 'required']) }}
                    <input type="hidden" id="id" name="id">
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
            
            <div class="edit-form-btn float-right">
                {{ link_to_route('biller.queuerequisitions.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                {{ Form::submit(@$supplier_product? 'Create' : 'Update', ['class' => 'btn btn-primary btn-md']) }}                                            
            </div>     
            {{ Form::close() }}
            
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>