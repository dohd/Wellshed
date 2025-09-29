@extends ('core.layouts.app')

@section('title', 'Create | BoQ Valuation')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">BoQ Valuation</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.boq_valuations.partials.boq_valuations-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::open(['route' => 'biller.boq_valuations.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
            @include('focus.boq_valuations.form')
        {{ Form::close() }}
        
    </div>
</div>  
@endsection
