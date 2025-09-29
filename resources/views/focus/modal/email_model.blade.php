<div id="sendEmail" class="modal fade">
    <div class="modal-dialog modal-l">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{trans('general.email')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <form id="" action="{{route('biller.quotes.send_email')}}" method="POST">
                @csrf
                <div class="modal-body" id="email_body">
                    <div class="form-group ">
                        <div class="col">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="fa fa-envelope-o" aria-hidden="true"></span></div>
                                <input type="text" class="form-control" placeholder="Email" name="mail_to" value="{{@$quote->customer->email}}">
                            </div>
                        </div>
                    <div class=" form-group">
                        <div class="col mb-1">
                            <label for="customer_name">{{trans('customers.name')}}</label>
                            <input type="text" class="form-control" name="customer_name" value="{{@$quote->customer->name}}">
                        </div>
                    </div>
                    <div class=" form-group">
                        <div class="col mb-1">
                            <label for="subject">{{trans('general.subject')}}</label>
                            <input type="text" class="form-control" name="subject" id="subject">
                        </div>
                    </div>
                    <div class=" form-group">
                        <div class="col mb-1">
                            <label for="contents">{{trans('general.body')}}</label>
                            <textarea name="text" class="summernote form-control" id="contents" row="10" title="Contents"></textarea>
                        </div>
                    </div>
                    <input type="hidden" id="quote_id" name="quote_id" value="{{$quote['id']}}">
                    <input type="hidden" id="template_type" name="template_type" value="">
                    <input type="hidden" name="template_category" value="{{$category}}">
                    <input type="hidden" id="action_url" value="{{route('biller.load_template')}}">
                    <input type="hidden" id="action_url_send" value="{{route('biller.send_bill')}}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal">{{trans('general.close')}}</button>
                    <button type="submit" class="btn btn-primary" id="">{{trans('general.send')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>