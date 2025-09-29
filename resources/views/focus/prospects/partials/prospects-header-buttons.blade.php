<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.prospects.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>
    @permission('create-client')
        <a href="{{ route('biller.prospects.create') }}" class="btn btn-pink  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> Prospect
        </a>
        <a href="{{ route('biller.prospectscallresolved.index') }}" class="btn btn-success  btn-lighten-3">
            <i class="fa fa-arrow-up"></i> Follow_up
        </a>
        &nbsp;&nbsp;
        <a href="{{ route('biller.leads.create') }}" class="btn btn-success  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> Ticket
        </a>
        &nbsp;&nbsp;

        <a href="{{ route('biller.calllists.index') }}" class="btn btn-info  btn-lighten-2">
            <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
        </a>
        <a href="{{ route('biller.calllists.create') }}" class="btn btn-pink  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> CallList
        </a>
       
        <a href="{{ route('biller.calllists.mytoday') }}" class="btn btn-success  btn-lighten-3">
            <i class="fa fa-list-alt"></i>Today Call List
        </a>&nbsp;
        <a href="{{ route('biller.calllists.previous_call_list') }}" class="btn btn-success  btn-lighten-3">
            <i class="fa fa-list-alt"></i>Previous Call List
        </a>&nbsp;
        <a href="{{ route('biller.calllists.reasign_call_list') }}" class="btn btn-info  btn-lighten-3">
            <i class="fa fa-list-alt"></i>Assign Prospects
        </a>&nbsp;
    @endauth

    
    
       
        

   
</div>
