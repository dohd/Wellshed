<div class="modal fade" id="exampleModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Create Client PriceList</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            {{ Form::open(['route' => 'biller.client_products.store']) }}
            <div class="form-group row">
                <div class="col-6 mt-2">
                    <input type="hidden" name="item_id" id="item_id">
                    <label for="client">Client</label>
                    <select name="customer_id" id="client" class="form-control" data-placeholder="Choose-Client" required>
                        @foreach($customers as $row)
                            <option value="{{ $row->id }}" {{ @$client_product && $client_product->client_id == $row->id? 'selected' : '' }}>
                                {{ $row->company }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 mt-2">
                    <label for="contract">Contract Title</label>
                    {{ Form::text('contract', null, ['class' => 'form-control', 'id' =>'contract', 'required']) }}
                </div>
               
            </div>
            <div class="form-group row">
                <div class="col-6 mt-2">
                    <label for="description">Client Description</label>
                    {{ Form::text('descr', null, ['class' => 'form-control', 'required', 'id'=>'descr']) }}
                </div>
                <div class="col-6 mt-2">
                    <label for="description">System/Inventory Description</label>
                    {{ Form::text('description', null, ['class' => 'form-control', 'readonly', 'id'=>'description']) }}
                </div>
                
            </div>
            <div class="form-group row">
                <div class="col-3 mt-2">
                    <label for="code">Product Code.</label>
                    <input type="text" class="form-control" readonly name="product_code" id="code">
                </div>
                <div class="col-3 mt-2">
                    <label for="uom">Unit of Measure (UoM)</label>
                    {{ Form::text('uom', null, ['class' => 'form-control', 'readonly', 'id'=> 'uom']) }}
                </div>
                <div class="col-3 mt-2">
                    <label for="row_number">Row No.</label>
                    {{ Form::text('row_num', null, ['class' => 'form-control']) }}
                </div>

                <div class="col-3 mt-2">
                    <label for="rate">Rate (Ksh.) VAT Exclusive</label>
                    {{ Form::text('rate', null, ['class' => 'form-control', 'id' => 'rate', 'required']) }}
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
