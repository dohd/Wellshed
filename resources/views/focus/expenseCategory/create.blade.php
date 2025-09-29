<!DOCTYPE html>

@extends ('core.layouts.app')


@section ('title',  'Create Expense Category')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Create Expense Category</h3>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.expenseCategory.partials.header-buttons')
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
                            {{ Form::open(['route' => 'biller.expense-category.store', 'method' => 'POST', 'id' => 'create-employee-daily-log']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}
                                @include("focus.expenseCategory.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.expense-category.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md']) }}
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
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>


</script>
@endsection