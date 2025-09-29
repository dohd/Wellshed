<!DOCTYPE html>
@extends ('core.layouts.app')
@section ('title', 'Non-Project Class Budget')

@include('tinymce.scripts')
@section('content')
    <style>
        .radius-8 {
            border-radius: 8px;
        }
    </style>

    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h3 class="mb-0">Non-Project Class Budget</h3>
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
                                {{ Form::model($purchaseClassBudget, ['route' => ['biller.purchase-class-budgets.update', $purchaseClassBudget->id], 'method' => 'PATCH']) }}
                                <div class="form-group">
                                    @include('focus.purchase_class_budgets.form')
                                    <div class="edit-form-btn mt-4">
                                        {{ link_to_route('biller.purchase-class-budgets.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-1']) }}
                                        {{ Form::submit('Update', ['class' => 'btn btn-primary btn-md']) }}
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