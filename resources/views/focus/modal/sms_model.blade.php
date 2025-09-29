<div id="sendSMS" class="modal fade">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{trans('general.sms')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            {{-- <div id="request_sms" class="m-2 text-center">
                <span class="fa fa-hourglass-half spinner font-large-2 blue" aria-hidden="true"></span>
            </div> --}}
            <form action="{{route('biller.quotes.send_single_sms')}}" method="POST">
                <div class="modal-body" id="sms_body">
                    @csrf
                    @php
                        $clientname = @$quote->lead->client_name ?: '';
                        $address = @$quote->lead->client_address ?: '';
                        $email = @$quote->lead->client_email ?: '';
                        $cell = @$quote->lead->client_contact ?: '';
                    @endphp
                    <div class="row">
                        <div class="col">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="fa fa-mobile" aria-hidden="true"></span></div>
                                <input type="text" class="form-control" placeholder="Mobile" name="sms_to" value="{{@$quote->customer ? @$quote->customer->phone : $cell}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="customer_name">{{trans('customers.name')}}</label>
                            <input type="text" class="form-control" name="customer_name" value="{{@$quote->customer ? @$quote->customer->name : $clientname}}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="contents">{{trans('general.body')}}</label>
                            <textarea name="subject" title="Contents" id="sms_message" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <input type="hidden" id="bill_id" name="id" value="{{$quote['id']}}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal">{{trans('general.close')}}</button>
                    <button type="submit" class="btn btn-primary">{{trans('general.send')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>