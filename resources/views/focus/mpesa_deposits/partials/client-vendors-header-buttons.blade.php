<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.client_vendors.index' ) }}" class="btn btn-info  btn-lighten-2"><i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}</a>
    @permission('create-client' ) 
        <a href="{{ route('biller.client_vendors.create' ) }}" class="btn btn-pink  btn-lighten-3"><i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}</a> 
    @endauth
</div>
