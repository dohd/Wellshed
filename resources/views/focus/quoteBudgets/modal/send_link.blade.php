<div class="modal fade" id="sendlinkModal" tabindex="0" role="dialog" aria-labelledby="sendlinkModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Send Link</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('biller.send_link_budget')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="store_users">Search Store Users</label>
                        <select class="custom-select" name="store_users[]" id="store_users" data-placeholder="Search Store Users" multiple>
                            <option value="">Search Store Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->fullname }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="technicians">Search Technicians</label>
                        <select class="custom-select" name="technicians[]" id="technicians" data-placeholder="Search Technicians" multiple>
                            <option value="">Search Technicians</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->fullname }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="send_email_sms">Send SMS or Email or Both</label>
                        <select class="custom-select" name="send_email_sms" id="send_email_sms">
                            <option value="sms">SMS</option>                       
                            <option value="email">Email</option>                       
                            <option value="both">Both</option>                       
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        {{ Form::textarea('note', null, ['class' => 'form-control', 'maxlength' => 477]) }}
                        <input type="hidden" name="quote_id" id="quote">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary"]) }}
                </div>
            </form>
        </div>
    </div>
</div>