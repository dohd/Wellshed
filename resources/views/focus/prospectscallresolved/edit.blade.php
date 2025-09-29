@extends ('core.layouts.app')

@php
    $query_str = request()->getQueryString();
    $part_title = preg_match('/page=copy/', $query_str) ? 'Copy' : 'Edit';
    $url = $query_str == 'page=copy' ? 'biller.prospects.store' : 'biller.prospects.update';
@endphp

@section ('title', 'Prospects Management | ' . $part_title)

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Prospects Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-auto float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.prospects.partials.prospects-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            @if ($query_str == 'page=copy')
                                {{ Form::model($prospect, ['route' => 'biller.prospects.store', 'method' => 'POST' ]) }}
                                    @include("focus.prospects.form")
                                    {{ Form::submit('Copy Ticket', ['class' => 'btn btn-primary btn-lg pull-right mb-2']) }}
                                {{ Form::close() }}
                            @else
                                {{ Form::model($prospect, ['route' => ['biller.prospects.update', $prospect], 'method' => 'PATCH', 'id' => 'edit-prospect']) }}
                                    @include("focus.prospects.form")
                                    <div class="edit-form-btn row">
                                        {{ link_to_route('biller.prospects.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                                        {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                                    </div>                                
                                {{ Form::close() }}
                            @endif                                    
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection