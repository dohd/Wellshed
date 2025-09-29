
<div>

    <div class="row mb-2">
        <div class="col-12 col-lg-8">
            <label for="name" class="mt-2">Name</label>
            <input type="text" id="name" name="name" required class="form-control box-size mb-2" placeholder="Set a suitable name">
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

</div>

@section('extra-scripts')
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
