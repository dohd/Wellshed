<div class="btn-group">
    <a href="{{ route('biller.expense-category.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>

    @permission('create-expense-category')
        <a href="{{ route('biller.expense-category.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> Create
        </a>
    @endauth

</div>