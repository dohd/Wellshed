<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.taskschedules.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i>{{ trans('general.list') }}
    </a>
    @permission('create-schedule')
    <a href="{{ route('biller.taskschedules.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> Load Equipment
    </a>
    @endauth
</div>