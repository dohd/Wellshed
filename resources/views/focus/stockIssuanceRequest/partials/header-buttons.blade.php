<div class="btn-group" role="group" aria-label="Basic example">

{{--    <a href="{{ route('biller.edl-subcategory-allocations.allocations') }}" class="btn btn-foursquare mr-1" style="border-radius: 8px;">--}}
{{--        <i class="icon-list"></i> Tasks--}}
{{--    </a>--}}

    <a href="{{ route('biller.stock-issuance-request.index') }}" class="btn btn-adn mr-1" style="border-radius: 8px;">
        <i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}
    </a>

    <a href="{{ route('biller.stock-issuance-request.create') }}" class="btn btn-dropbox" style="border-radius: 8px;">
        <i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}
    </a>
</div>
