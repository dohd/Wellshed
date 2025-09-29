<!--Action Button-->
<div>
    <div class="btn-group" role="group" aria-label="Basic example">
        <a href="{{ route('biller.djcs.index') }}" class="btn btn-info  btn-lighten-2">
            <i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}
        </a>
        @permission('create-client') 
            <a href="{{ route('biller.djcs.create') }}" class="btn btn-pink  btn-lighten-3">
                <i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}
            </a>&nbsp;&nbsp;
            <a href="{{ route('biller.quotes.index') }}" class="btn btn-success  btn-lighten-3">
                <i class="fa fa-list-alt"></i> Quote
            </a>&nbsp;
            <a href="{{ route('biller.quotes.index', 'page=pi') }}" class="btn btn-warning">
                <i class="fa fa-list-alt"></i> PI
            </a>&nbsp;
        @endauth
    </div>
</div>
