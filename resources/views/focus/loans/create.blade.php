@extends('core.layouts.app')

@section('title',  'Loans Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Loans Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.loans.partials.loans-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card round">
                    <div class="card-content">
                        <div class="card-body ">
                            {{ Form::open(['route' => 'biller.loans.store', 'method' => 'post', 'class' => 'pl-1']) }}
                                @include("focus.loans.form")
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection