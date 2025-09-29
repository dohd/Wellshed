<div class="btn-group" role="group" aria-label="Basic example">

    <a href="{{ route('biller.edl-subcategory-allocations.allocations') }}" class="btn btn-foursquare mr-1" style="border-radius: 8px;">
        <i class="icon-list"></i> Key Activities
    </a>

    <a href="{{ route('biller.employee-daily-log.index') }}" class="btn btn-adn mr-1" style="border-radius: 8px;">
        <i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}
    </a>
    @permission('create-daily-logs')
    <a href="{{ route('biller.employee-daily-log.create') }}" class="btn btn-dropbox" style="border-radius: 8px;">
        <i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}
    </a>
    @endauth
</div>
