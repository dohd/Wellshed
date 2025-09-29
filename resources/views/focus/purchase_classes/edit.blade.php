<!DOCTYPE html>

@extends ('core.layouts.app')

@section ('title', 'Edit Non-Project Class')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Edit Non-Project Class</h3>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchase_classes.partials.header-buttons')
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
                            {{ Form::model($purchaseClass, ['route' => ['biller.purchase-classes.update', $purchaseClass->id], 'method' => 'PATCH']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}

                                <div>

                                    <div class="row mb-2">
                                        <div class="col-12 col-lg-8">
                                            <label for="name" class="mt-2">Name</label>
                                            <input type="text" id="name" name="name" required class="form-control box-size mb-2"
                                                   @if(!empty($purchaseClass)) value="{{$purchaseClass['name']}}" @endif
                                                    placeholder="Set a suitable name"
                                            >
                                        </div>

                                        <div class="col-12 col-lg-8">
                                            <label for="expense_category" class="caption" style="display: inline-block;"> Expense Category </label>
                                            <select id="expense_category" name="expense_category" class="custom-select round" data-placeholder="Select an Expense Category">
                                                <option value="" @if(@$purchaseClass->expense_category)) selected @endif>Select an Expense Category</option>
                                                @foreach ($expenseCategories as $eC)
                                                    <option value="{{ $eC->id }}"
                                                            data-description="{{ $eC->description }}"
                                                            @if(@$purchaseClass->expense_category === $eC->id) selected @endif
                                                    >
                                                        {{ $eC->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <!-- Label to display the description -->
                                            <label id="expense_description" class="text-muted font-italic"></label>
                                        </div>

                                    </div>


                                    <div class="edit-form-btn">
                                        {{ link_to_route('biller.purchase-classes.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-1']) }}
                                        {{ Form::submit('Update', ['class' => 'btn btn-primary btn-md']) }}
                                        <div class="clearfix"></div>
                                    </div>

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


@section('after-scripts')

    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        $(document).ready(function () {
            $('#expense_category').select2({ allowClear: true });

            // Function to update the description label
            function updateDescription() {
                const selectedOption = $('#expense_category option:selected');
                const description = selectedOption.data('description') || '';
                $('#expense_description').text(description);
            }

            // Call the function on page load in case an option is pre-selected
            updateDescription();

            // Update the description whenever the selection changes
            $('#expense_category').on('change', updateDescription);
        });

    </script>

@endsection

<style>
    .radius-8 {
        border-radius: 8px;
    }
</style>