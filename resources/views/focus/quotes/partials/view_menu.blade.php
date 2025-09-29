@php
    $route_name = 'biller.quotes.edit';
    $doc_type = $quote->is_repair? '' : 'doc_type=maintenance';
    $edit_link = request('page') == 'pi' ? route($route_name, [$quote, 'page=pi', $doc_type]) : route($route_name, [$quote, $doc_type]);
    $copy_link = $quote->bank_id ? route($route_name, [$quote, 'task=pi_to_quote']) : route($route_name, [$quote, 'page=pi&task=quote_to_pi']);
    $valid_token = token_validator('', 'q' . $quote->id . $quote->tid, true);
@endphp
<div class="row">
    <div class="col">
        @if (!auth()->user()->customer_id)
            <a href="{{ $edit_link }}" class="btn btn-warning mb-1"><i class="fa fa-pencil"></i> Edit</a>
            <a href="{{ $copy_link }}" class="btn btn-cyan mb-1"><i class="fa fa-clone"></i></i>
                {{ $quote->bank_id ? 'Copy to Quote' : 'Copy to PI' }}            
            </a>
            @if (access()->allow('delete-quote'))
                <a class="btn btn-danger mb-1 mr-5 quote-delete" href="javascript:void(0);"><i class="fa fa-trash"></i> Delete
                    {{ Form::open(['route' => ['biller.quotes.destroy', $quote], 'method' => 'delete']) }} {{ Form::close() }}               
                </a>
            @endif
            @if(!$quote->customer_id)
                <a href="{{ route('biller.leads.create_client', @$quote->lead_id) }}" target="_blank" class="btn btn-primary btn-large mb-1" >Create Customer</a>
            @endif
            <div class="btn-group">
                <button type="button" class="btn btn-large btn-blue mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-check"></i> Approve QT/PI
                </button>
                <div class="dropdown-menu">
                    <a href="#pop_model_1" data-toggle="modal" data-remote="false" class="dropdown-item quote-approve" title="Change Status">
                        Approve
                    </a>
                </div>
            </div>
            @if ($quote->status == 'approved')
                <a href="#pop_model_4" data-toggle="modal" data-remote="false" class="btn btn-large btn-cyan mb-1" title="Add LPO">
                    <span class="fa fa-retweet"></span> Add LPO
                </a>
            @else
                <button class="btn btn-large btn-cyan mb-1" disabled><span class="fa fa-retweet"></span> Add LPO</button>
            @endif
        @endif

        <div class="btn-group">
            {{ Form::open(['route' => ['biller.quotes.quote_download'], 'method' => 'post']) }}
                <input type="hidden" name="quote_id" id="quote_id" value="{{$quote->id}}">
                <button type="submit" class="btn btn-purple mb-1">
                    <i class="fa fa-download" aria-hidden="true"></i> Download QT/PI
                </button>
            {{ Form::close() }}             
        </div>
        <a @if (!auth()->user()->customer_id) disabled @endif href="#" class="btn btn-success mb-1 mr-5" data-toggle="modal" data-target="#attachFileModal">
            <i class="fa fa-file" aria-hidden="true"></i> Attachments
        </a>   

        <div class="btn-group">
            <button type="button" class="btn btn-facebook mb-1 dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="fa fa-envelope-o"></span> {{trans('customers.email')}}
            </button>
            <div class="dropdown-menu">
                <a href="#sendEmail" data-toggle="modal" data-remote="false" class="dropdown-item send_bill" data-type="6" data-type1="proposal">
                    {{trans('general.quote_proposal')}}
                </a>
            </div>            
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-blue mb-1 dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="fa fa-mobile"></span> SMS / WhatsApp
            </button>
            <div class="dropdown-menu">
                <a href="#sendSMS" data-toggle="modal" data-remote="false" class="dropdown-item send_sms" data-type="16" data-type1="proposal">
                    {{trans('general.quote_proposal')}}                            
                </a>
            </div>            
        </div>      
    </div>
</div>