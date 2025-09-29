@extends ('core.layouts.app')

@section ('title', config('core.cname'))

@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h1 class="text-center purple font-weight-bold" style="font-size: 4em">~{{ config('core.cname') }}~</h1>
                <hr>
                <h1 class="text-center">{{ trans('strings.backend.welcome') }}</h1>
            </div>
        </div>
    </div>
@endsection
