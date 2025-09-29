<div class="btn-group" role="group" aria-label="Basic example">
    @permission('manage-refill-customer')
    <a href="{{ route('biller.refill_customers.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>    
    @endauth        
    @permission('create-refill-customer')
    <a href="{{ route('biller.refill_customers.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{ trans('general.create') }}
    </a>
    @endauth
</div>