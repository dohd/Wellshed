@extends('core.layouts.app')

@section('title',  'Manual Journals')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Create Journal Entry</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.journals.partials.journals-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body ">
                            {{ Form::open(['route' => 'biller.journals.store', 'method' => 'post', 'id' => 'journal']) }}
                                @include("focus.journals.form")
                                <div class="edit-form-btn row">
                                    {{ link_to_route('biller.journals.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                                    {{ Form::submit('Create', ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
