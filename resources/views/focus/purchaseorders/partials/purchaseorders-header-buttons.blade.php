<div class="btn-group" role="group" aria-label="purchaseorders">
    @permission('manage-purchase')
    <a href="{{ route( 'biller.purchaseorders.index' ) }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}
    </a>
    @endauth
    @permission('create-purchase')
    <a href="{{ route( 'biller.purchaseorders.create' ) }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}
    </a>
    @endauth
</div>