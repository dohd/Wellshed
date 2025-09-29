<div class="btn-group">
    <a href="{{ route('biller.purchase-classes.index') }}{{ @$is_debit ? '?is_debit=1' : '' }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>
    <a href="{{ route('biller.purchase-classes.create') }}{{ @$is_debit ? '?is_debit=1' : '' }}" class="btn btn-pink  btn-lighten-3">
    <i class="fa fa-plus-circle"></i> Create
    </a>
</div>