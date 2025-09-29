<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.health-and-safety.summary') }}" class="btn btn-info  btn-lighten-2"><i class="fa fa-list-alt"></i> Monthly Calendar</a>

    <a href="{{ route('biller.health-and-safety.index') }}" class="btn btn-info  btn-lighten-2"><i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}</a>
    {{-- @permission( 'business_settings' ) --}}
    <a href="{{ route('biller.health-and-safety.create') }}" class="btn btn-pink  btn-lighten-3"><i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}</a>
    {{-- @endauth --}}
</div>