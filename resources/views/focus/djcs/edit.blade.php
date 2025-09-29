@extends ('core.layouts.app')

@php
    $part_title = request('page') == 'copy'? 'Copy' : 'Edit';
@endphp
@section ('title', $part_title . ' | Site Survey Report')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Site Survey Report Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.djcs.partials.djcs-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    @if (request('page') == 'copy')
                        {{ Form::model($djc, ['route' => ['biller.djcs.store', $djc], 'method' => 'POST', 'files' => true]) }}
                            @include('focus.djcs.form')
                        {{ Form::close() }}
                    @else
                        {{ Form::model($djc, ['route' => ['biller.djcs.update', $djc], 'method' => 'PATCH', 'files' => true]) }}
                            @include('focus.djcs.form')
                        {{ Form::close() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
@include('focus.djcs.form_js')
@endsection
