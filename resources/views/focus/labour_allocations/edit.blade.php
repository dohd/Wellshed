@extends('core.layouts.app')

@section('title', 'Edit | Labour Allocation Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Labour Allocation Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right"> 
                    @include('focus.labour_allocations.partials.labour_allocation-header-buttons')                   
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                {{ Form::model($labour_allocation, ['route' => ['biller.labour_allocations.update', $labour_allocation], 'method' => 'PATCH']) }}
                    @include('focus.labour_allocations.form')
                    <div class="edit-form-btn row ml-1">
                        {{ link_to_route('biller.labour_allocations.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 mr-1']) }}
                        {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                    </div>  
                {{ Form::close() }}
            </div>             
        </div>
    </div>
</div>
@endsection
