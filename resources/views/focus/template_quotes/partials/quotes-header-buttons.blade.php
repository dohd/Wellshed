@php 
    $index = 'biller.template-quotes.index';
    $create = 'biller.template-quotes.create';
@endphp
<div class="btn-group" role="group" aria-label="template-quotes">
    <a href="{{ route($index) }}" class="btn btn-info ml-1">
        <i class="fa fa-list-alt"></i> List
    </a>
    <a href="{{ route($create) }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> Create
    </a>


</div>