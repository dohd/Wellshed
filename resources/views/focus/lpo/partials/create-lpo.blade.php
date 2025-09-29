<div class="modal fade" id="AddLpoModal" tabindex="-1" role="dialog" aria-labelledby="AddLpoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create LPO</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      {{ Form::open(['route' => 'biller.lpo.store', 'method' => 'POST', 'id' => 'createLpoForm']) }}
        @include('focus.lpo.form')
      {{ Form::close() }}      
    </div>
  </div>
</div>