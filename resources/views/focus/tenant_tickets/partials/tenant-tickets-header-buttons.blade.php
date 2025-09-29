<div class="btn-group" role="group" aria-label="Basic example">
    @permission('manage-client-area-ticket')
    <a href="{{ route('biller.tenant_tickets.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>         
    @endauth   
    @permission('create-client-area-ticket')
    <a href="{{ route('biller.tenant_tickets.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{ trans('general.create') }}
    </a>
    @endauth
</div>