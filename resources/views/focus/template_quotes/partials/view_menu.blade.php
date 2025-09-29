@php
    $route_name = 'biller.quotes.edit';
    $doc_type = $quote->is_repair? '' : 'doc_type=maintenance';
    $edit_link = request('page') == 'pi' ? route($route_name, [$quote, 'page=pi', $doc_type]) : route($route_name, [$quote, $doc_type]);
    $copy_link = $quote->bank_id ? route($route_name, [$quote, 'task=pi_to_quote']) : route($route_name, [$quote, 'page=pi&task=quote_to_pi']);
    $valid_token = token_validator('', 'q' . $quote->id . $quote->tid, true);
@endphp
<div class="row">
    <div class="col">
        <a href="{{ $edit_link }}" class="btn btn-warning mb-1"><i class="fa fa-pencil"></i> Edit</a>
        <a href="{{ $copy_link }}" class="btn btn-cyan mb-1"><i class="fa fa-clone"></i></i>
            {{ $quote->bank_id ? 'Copy to Quote' : 'Copy to PI' }}            
        </a>
        @if (access()->allow('delete-quote'))
            <a class="btn btn-danger mb-1 quote-delete" href="javascript:void(0);"><i class="fa fa-trash"></i> Delete
                {{ Form::open(['route' => ['biller.quotes.destroy', $quote], 'method' => 'delete']) }} {{ Form::close() }}               
            </a>
        @endif
        <div class="btn-group">
            <button type="button" class="btn btn-large btn-blue mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-check"></i> {{trans('general.change_status')}}
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
        <div  class="d-inline-block ml-5">
            <div class="btn-group">
                <button type="button" class="btn btn-facebook dropdown-toggle mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-envelope-o"></span> {{trans('customers.email')}}
                </button>
                <div class="dropdown-menu">
                    <a href="#sendEmail" data-toggle="modal" data-remote="false" class="dropdown-item send_bill" data-type="6" data-type1="proposal">
                        {{trans('general.quote_proposal')}}
                    </a>
                </div>
            </div>
            <!-- SMS -->
            <div class="btn-group">
                <button type="button" class="btn btn-blue dropdown-toggle mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-mobile"></span> {{trans('general.sms')}}
                </button>
                <div class="dropdown-menu">
                    <a href="#sendSMS" data-toggle="modal" data-remote="false" class="dropdown-item send_sms" data-type="16" data-type1="proposal">
                        {{trans('general.quote_proposal')}}                            
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>