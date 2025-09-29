<div class="btn-group" role="group" aria-label="rfq">
    <a href="{{ route( 'biller.rfq.index' ) }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}
    </a>
    @permission('create-rfq')
    <a href="{{ route( 'biller.rfq.create' ) }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}
    </a>
    @endauth
</div>