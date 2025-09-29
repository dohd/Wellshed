

<div class="modal fade" id="smsSettingsModal" role="dialog" aria-labelledby="smsSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="smsSettingsModalLabel">Create Supplier PriceList</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            {{ Form::open(['route' => 'biller.send_sms.activate_deactivate_sms']) }}
        
            <div class="form-group row">
                <div class="col-md-12">
                    <label for="">Status</label>
                    <select name="active" id="status" class="form-control">
                        <option value="1">Activate</option>
                        <option value="0">Deactivate</option>
                    </select>
                </div>
                <input type="hidden" name="id" id="setting_id">
            </div>
            
            <div class="edit-form-btn float-right">
                {{ link_to_route('biller.send_sms.index_sms_settings', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                {{ Form::submit('Update', ['class' => 'btn btn-primary btn-md']) }}                                            
            </div>     
            {{ Form::close() }}
            
          </div>
          <div class="modal-footer">
            {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary submit">Submit</button> --}}
          </div>
        </div>
      </div>
