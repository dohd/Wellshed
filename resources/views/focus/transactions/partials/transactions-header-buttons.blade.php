@php
    $link = request('rel_type', 0) ? route('biller.accounts.index') : route('biller.transactions.index');
@endphp
<div class="btn-group" role="group">
    <a href="{{ $link }}" class="btn btn-info btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>
    @permission('transaction-data')
        <a href="{{ route('biller.journals.create') }}" class="btn btn-pink  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> {{ trans('general.create') }}
        </a>
    @endauth
</div>