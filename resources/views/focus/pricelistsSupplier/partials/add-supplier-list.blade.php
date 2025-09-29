

<div class="modal fade" id="exampleModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Create Supplier PriceList</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            {{ Form::open(['route' => 'biller.pricelistsSupplier.store']) }}
            <div class="form-group row">
                <div class="col-6 mt-2">
                    <label for="supplier">Supplier</label>
                    <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Choose-Supplier" required>
                        @foreach($suppliers as $row)
                            <option value="{{ $row->id }}" {{ @$supplier_product && $supplier_product->supplier_id == $row->id? 'selected' : '' }}>
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
                    <label for="description">Supplier Description</label>
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
                    {{ Form::hidden('product_id', null, ['class' => 'form-control', 'readonly', 'id'=> 'product_id']) }}
                </div>
                <div class="col-3 mt-2">
                    <label for="row_number">Row No.</label>
                    {{ Form::text('row_num', null, ['class' => 'form-control']) }}
                </div>

                <div class="col-3 mt-2">
                    <label for="rate">Rate (VAT Exclusive)</label>
                    {{ Form::text('rate', null, ['class' => 'form-control', 'id' => 'rate', 'required']) }}
                </div>
            </div>
            
            <div class="edit-form-btn float-right">
                {{ link_to_route('biller.pricelistsSupplier.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                {{ Form::submit(@$supplier_product? 'Update' : 'Create', ['class' => 'btn btn-primary btn-md']) }}                                            
            </div>     
            {{ Form::close() }}
            
          </div>
          <div class="modal-footer">
            {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary submit">Submit</button> --}}
          </div>
        </div>
      </div>

{{-- 
      @section('after-scripts')
      {{ Html::script('focus/js/select2.min.js') }}
      <script>
          const config = {
              ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
          };
          
          const Form = {
              supplierProduct: @json(@$supplier_product),
      
              init() {
      
                  if ($('#contract').val()) {
                      $('#supplier').select2({allowClear: true}).attr('disabled', true);
                      $('#contract').attr('readonly', true);
                  }
                  $('#supplier').select2({allowClear: true});
                  $('#rate').focusout(this.rateChange);
      
                  if (this.supplierProduct) {
                      $('#rate').trigger('focusout');
                  } else {
                      $('#supplier').val('').trigger('change');
                  }
              },
      
              rateChange() {
                  const value = accounting.unformat($(this).val());
                  $(this).val(accounting.formatNumber(value));
              },
          };
      
          $(() => Form.init());
      </script>
      @endsection --}}