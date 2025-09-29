<!DOCTYPE html>

@extends ('core.layouts.app')

@section ('title', 'Employee Appraisal')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">

        <div class="content-header-right col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.employeeAppraisal.partials.header-buttons')
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
                            <div class="form-group">
                                {{-- Including Form blade file --}}
                                @include('focus.employeeAppraisal.showForm')
                            </div>
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