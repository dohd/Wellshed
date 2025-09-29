


<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog mw-100" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Update Queue Requisition</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="card">
              <div class="form-group row">
                <label for="project">Project Name</label>
                <input type="text" name="" id="project" class="form-control" readonly>
              </div>
            </div>
            {{ Form::open(['route' => 'biller.queuerequisitions.store']) }}
            <table class="table tfr my_stripe_single text-center mt-5" id="requisitionTbl">
                <thead>
                    <tr class="bg-gradient-directional-blue white">
                        <th>#</th>
                        <th>Product</th>
                        <th>UoM</th>
                        <th>Product Code</th>
                        <th>Qty Approved</th>
                        <th>Qty Issued</th>
                        <th>Requisition Approved</th>
                        <th>Warehouse</th>
                        <th width="10%">Issue Qty</th>
                        <th width="10%">Requisition Qty</th>
                        <th>Requisition Check</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            
            <div class="edit-form-btn float-right">
                {{-- {{ link_to_route('biller.queuerequisitions.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }} --}}
                {{ Form::submit(@$supplier_product? 'Update' : 'Create', ['class' => 'btn btn-primary btn-md']) }}                                            
            </div>     
            {{ Form::close() }}
            
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>