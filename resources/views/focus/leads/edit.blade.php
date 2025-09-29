@extends ('core.layouts.app')

@php
    $query_str = request()->getQueryString();
    $part_title = preg_match('/page=copy/', $query_str) ? 'Copy' : 'Edit';
    $url = $query_str == 'page=copy' ? 'biller.leads.store' : 'biller.leads.update';
@endphp

@section ('title', 'Tickets Management | ' . $part_title)

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tickets Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.leads.partials.leads-header-buttons')
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
                                {{ Form::model($lead, ['route' => 'biller.leads.store', 'method' => 'POST' ]) }}
                                    @include("focus.leads.form")
                                    {{ Form::submit('Copy Ticket', ['class' => 'btn btn-primary btn-lg pull-right mb-2']) }}
                                {{ Form::close() }}
                            @else
                                {{ Form::model($lead, ['route' => ['biller.leads.update', $lead], 'method' => 'PATCH', 'id' => 'edit-lead']) }}
                                    @include("focus.leads.form")
                                    <div class="edit-form-btn row">
                                        {{ link_to_route('biller.leads.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
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