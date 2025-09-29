<!DOCTYPE html>
@extends ('core.layouts.app')
@section('title', 'Non-Project Class Budgets')

@include('tinymce.scripts')
@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h3 class="mb-0">Create Budget</h3>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.purchase_class_budgets.partials.header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-radius: 8px;">
                        <div class="card-content">
                            <div class="card-body">
                                {{ Form::open(['route' => 'biller.purchase-class-budgets.store', 'method' => 'POST']) }}
                                <div class="form-group">
                                    @include("focus.purchase_class_budgets.form")
                                    <div class="edit-form-btn">
                                        {{ link_to_route('biller.purchase-class-budgets.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md']) }}
                                        {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                        <div class="clearfix"></div>
                                    </div>
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