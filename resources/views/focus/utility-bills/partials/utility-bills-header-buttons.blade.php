<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.utility_bills.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>
    @if(empty(Auth::user()->supplier_id))
    <a href="{{ route('biller.utility_bills.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{ trans('general.create') }}
    </a>
    @endauth
</div>