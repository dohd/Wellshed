@extends ('core.layouts.app')

@section ('title', 'Job Verification')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Verification Management</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group float-right" role="group" aria-label="quotes">
                        <a href="{{ route('biller.quotes.verification') }}" class="btn btn-info  btn-lighten-2">
                            <i class="fa fa-list-alt"></i> {{trans('general.list')}}
                        </a>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        @php
            $query_str = request()->getQueryString();
            $link = route('biller.quotes.storeverified');
            if ($query_str == 'page=pi') $link = route('biller.quotes.storeverified', 'page=pi');
        @endphp
        {{ Form::model($quote, ['url' => $link, 'class' => 'form-horizontal', 'method' => 'POST']) }}                   
            @include('focus.quotes_verification.form')
        {{ Form::close() }}
    </div>
</div>
@endsection
