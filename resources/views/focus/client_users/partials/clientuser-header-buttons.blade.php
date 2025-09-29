<div class="btn-group" role="group" aria-label="Basic example">
    @permission('manage-crm-user')
    <a href="{{ route('biller.client_users.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>            
    @endauth
    @permission('create-crm-user')
    <a href="{{ route('biller.client_users.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{ trans('general.create') }}
    </a>
    @endauth
</div>