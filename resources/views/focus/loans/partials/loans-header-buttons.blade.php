<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.loans.pay_loans') }}" class="btn btn-purple  btn-lighten-3 mr-1">
        <i class="fa fa-money"></i> Pay
    </a>
    <a href="{{ route('biller.loans.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans( 'general.list' )}}
    </a>
    <a href="{{ route('biller.loans.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{ trans( 'general.create')}}
    </a>
</div>