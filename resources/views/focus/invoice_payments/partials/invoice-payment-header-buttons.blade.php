<div class="btn-group" role="group" aria-label="Basic example">
    @permission('manage-invoice')
    <a href="{{ route('biller.invoice_payments.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>     
    @endauth
    @permission('create-invoice')       
        <a href="{{ route('biller.invoice_payments.create') }}" class="btn btn-pink  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> {{ trans('general.create') }}
        </a>
    @endauth
</div>
