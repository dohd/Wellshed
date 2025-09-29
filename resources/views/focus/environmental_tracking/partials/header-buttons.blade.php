<div class="btn-group" role="group" aria-label="Basic example">
{{--    <a href="{{ route('biller.environmental-tracking.summary') }}" class="btn btn-info  btn-lighten-2"><i class="fa fa-list-alt"></i> Monthly Calendar</a>--}}

    <a href="{{ route('biller.environmental-tracking.index') }}" class="btn btn-info  btn-lighten-2"><i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}</a>
    {{-- @permission( 'business_settings' ) --}}
    <a href="{{ route('biller.environmental-tracking.create') }}" class="btn btn-pink  btn-lighten-3"><i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}</a>
    {{-- @endauth --}}
</div>