@php 
    $is_pi = request('page') == 'pi'; 
    $index = 'biller.quotes.index';
    $create = 'biller.quotes.create';
@endphp
<div class="btn-group" role="group" aria-label="quotes">
    @if (access()->allowMultiple(['manage-quote', 'manage-pi']))
        <a href="{{ $is_pi ? route($index, 'page=pi') : route($index) }}" class="btn btn-info  btn-lighten-2">
            <i class="fa fa-list-alt"></i> {{trans('general.list')}}
        </a>
    @endif
    @if ($is_pi)
        @permission('create-pi')
        <a href="{{ route($create, 'page=pi') }}" class="btn btn-pink  btn-lighten-3 ">
            <i class="fa fa-plus-circle"></i> PI
        </a>
        @endauth
        @permission('manage-quote')
        <a href="{{ route($index) }}" class="btn btn-success ml-1">
            <i class="fa fa-list-alt"></i> Quote
        </a>
        @endauth
    @else
        @permission('create-quote')
        <a href="{{ route($create) }}" class="btn btn-pink  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> Quote
        </a>
        @endauth
        @permission('manage-pi')
        <a href="{{ route($index, 'page=pi') }}" class="btn btn-success ml-1">
            <i class="fa fa-list-alt"></i> PI
        </a>
        @endauth
    @endif
    &nbsp;
    @permission('manage-project')
    <a href="{{ route('biller.projects.index') }}" class="btn btn-cyan">
        <i class="fa fa-list-alt"></i> Project
    </a>
    @endauth
</div>