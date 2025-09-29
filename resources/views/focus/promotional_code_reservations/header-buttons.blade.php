<div class="btn-group" role="group" aria-label="Basic example">

    <a href="{{ route( 'biller.reserve-promo-codes.index' ) }}" class="btn btn-info  btn-lighten-2 round"><i
                class="fa fa-list-alt"></i> {{trans( 'general.list' )}}</a>
    <a href="{{ route('biller.reserve-customer-promo-code') }}" class="btn btn-pink  btn-lighten-3 round">
        <i class="fa fa-plus-circle"></i> Customers
    </a>
    <a href="{{ route('biller.reserve-3p-promo-code') }}" class="btn btn-success  btn-lighten-3 round">
        <i class="fa fa-plus-circle"></i> Third Parties
    </a>

</div>