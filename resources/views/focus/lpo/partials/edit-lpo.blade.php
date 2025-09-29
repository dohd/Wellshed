<div class="modal fade" id="updateLpoModal" tabindex="-1" role="dialog" aria-labelledby="updateLpoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update LPO</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        {{ Form::open(['route' => 'biller.lpo.update_lpo', 'method' => 'POST', 'id' => 'updateLpoForm']) }}
          @include('focus.lpo.form')
        {{ Form::close() }}
      </div>
    </div>
  </div>
</div>