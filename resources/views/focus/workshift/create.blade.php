@extends ('core.layouts.app')

@section ('title', 'Workshift Management | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Workshift Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.workshift.partials.workshift-header-buttons')
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
                            {{ Form::open(['route' => 'biller.workshifts.store', 'method' => 'POST' ]) }}
                                @include("focus.workshift.form")
                                
                                {{ Form::submit('Create', ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                            
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@include("focus.workshift.form-js")
