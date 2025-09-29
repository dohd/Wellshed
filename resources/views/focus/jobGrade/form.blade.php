
<div>

    <p style="font-size: 16px" class="mb-2">
        This form allows you to set the salary ranges for each job grade. Please ensure that the lower limit for each
        grade is not higher than the upper limit. <br>
        If you choose not to use a particular job grade, you can leave its salary limits set to 0.00. This helps
        maintain clarity and accuracy in the job grading system.
    </p>

    @for ($i = 1; $i <= 10; $i++)
        <div class="form-group row mb-3">

            <!-- Grade A Section -->
            <div class="col-12 col-lg-6">
                <h5>Grade {{ $i }}A</h5>

                <div class="row">
                    <!-- a_upper -->
                    <div class="col-6">
                        <label for="{{ $i }}a_upper">Upper Limit</label>
                        <input type="number" name="{{ $i }}a_upper" id="{{ $i }}a_upper" value="{{ $jobGrades[$i.'a_upper'] ?? '' }}" class="form-control" step="0.01" required>
                    </div>

                    <!-- a_lower -->
                    <div class="col-6">
                        <label for="{{ $i }}a_lower">Lower Limit</label>
                        <input type="number" name="{{ $i }}a_lower" id="{{ $i }}a_lower" value="{{ $jobGrades[$i.'a_lower'] ?? '' }}" class="form-control" step="0.01" required>
                    </div>
                </div>

                <hr>
            </div>

            <!-- Grade B Section -->
            <div class="col-12 col-lg-6">
                <h5>Grade {{ $i }}B</h5>

                <div class="row">
                    <!-- b_upper -->
                    <div class="col-6">
                        <label for="{{ $i }}b_upper">Upper Limit</label>
                        <input type="number" name="{{ $i }}b_upper" id="{{ $i }}b_upper" value="{{ $jobGrades[$i.'b_upper'] ?? '' }}" class="form-control" step="0.01" required>
                    </div>

                    <!-- b_lower -->
                    <div class="col-6">
                        <label for="{{ $i }}b_lower">Lower Limit</label>
                        <input type="number" name="{{ $i }}b_lower" id="{{ $i }}b_lower" value="{{ $jobGrades[$i.'b_lower'] ?? '' }}" class="form-control" step="0.01" required>
                    </div>
                </div>

                <hr>
            </div>

        </div>
    @endfor

</div>

@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>

        $(document).ready(function() {
            // Set default value to 0.00 when the input is left empty
            $('input[type="number"]').each(function() {
                $(this).on('blur', function() {
                    if ($(this).val() === '') {
                        $(this).val('0.00');
                    }
                });
            });
        });


        // Compare upper and lower limits
        // Set default value to 0.00 when the input is left empty
        $('input[type="number"]').on('blur', function() {
            if ($(this).val() === '') {
                $(this).val('0.00');
            }
            checkLimits($(this));
        });

        // Compare upper and lower limits on blur
        function checkLimits(input) {
            const inputId = input.attr('id');

            const num = inputId.match(/\d+/)[0]; // Extract the number from the input ID

            const underscoreIndex = inputId.indexOf('_');
            const gradeNumber = underscoreIndex !== -1 ? inputId.substring(0, underscoreIndex) : inputId;

            const upperValue = $('#' + gradeNumber + '_upper').val();
            const lowerValue = $('#' + gradeNumber + '_lower').val();

            let isOk = true;

            if(parseInt(upperValue) < parseInt(lowerValue)) {

                $('#' + gradeNumber + '_lower').val('0.00');
                isOk = false;
            }

            console.clear()
            console.table({inputId, gradeNumber, upperValue, lowerValue, isOk});

            // const upperInputs = $(`#${num}a_upper, #${num}b_upper`);
            // const lowerInputs = $(`#${num}a_lower, #${num}b_lower`);
            //
            // upperInputs.each(function() {
            //     const upperValue = parseFloat($(this).val()) || 0; // Convert to float or 0
            //     lowerInputs.each(function() {
            //         const lowerValue = parseFloat($(this).val()) || 0; // Convert to float or 0
            //         if (lowerValue >= upperValue) {
            //             $(this).val('0.00'); // Set lower input to 0.00 if it's not valid
            //         }
            //     });
            // });
        }
    </script>
@endsection
