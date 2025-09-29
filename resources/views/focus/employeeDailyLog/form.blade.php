@include('tinymce.scripts')

<div class="row">
    @csrf <!-- CSRF protection -->

    <div class="col-3">

        <label for="date">Date:</label>
        <input type="text" id="date" name="date" required class="datepicker form-control box-size mb-2">

    </div>
{{--    <label for="email">Email:</label>--}}
{{--    <input type="email" id="email" name="email" required class="form-control box-size">--}}


    <div class="col-12 mt-3" id="taskDiv">

        <h2>Key Activities:</h2>

        <hr>

        @for($i = 0; $i < 20; $i++)
            <div class="row" id="task{{$i}}">

                <h3 class="col-12 mb-1">Key Activity #{{$i + 1}} <span id="key_activity-{{$i}}"></span></h3>
                

                <div class="col-6">
                    <label for="subcategory{{$i}}">Key Performance Indicator:</label>
                    <select class="form-control box-size new_task" id="subcategory-{{$i}}" name="subcategory{{$i}}" >
                        @if(empty($taskCategories[0]))
                            <option value=""> No Key Performance Indicators Allocated to You </option>
                        @else
                            <option value="">-- Select KPI: --</option>
                        @endif

                        @foreach ($taskCategories as $cat)
                            <option value="{{ $cat['value'] }}">
                                {{ array_search($cat ,$taskCategories) + 1 . '. ' . $cat['label'] . '  |  ' . $cat['frequency'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                    <label for="hours{{$i}}">Actual Hours:</label>
                    <input type="number" max="9" id="hours-{{$i}}" name="hours{{$i}}" class="form-control box-size hours" step="0.01" >
                </div>
                <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                    <label for="performance{{$i}}" >Performance:</label>
                    <input type="text" id="performance-{{$i}}" name="performance{{$i}}" class="form-control box-size performance" step="0.01" >
                </div>
                <div class="col-5 col-lg-2 mt-1 mt-lg-0">
                    <label for="target{{$i}}" >Target / UoM</label>
                    <div class="row no-gutters">
                        <div class="col-5">
                            <input type="text" id="target-{{$i}}" class="form-control box-size" step="0.01" readonly>
                        </div>
                        <div class="col-5">
                            <input type="text" id="uom-{{$i}}" class="form-control box-size" step="0.01" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                    <label for="frequency{{$i}}" >Frequency:</label>
                    <input type="text" id="frequency-{{$i}}" class="form-control box-size frequency" step="0.01" readonly>
                </div>
                <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                    <label for="work_done{{$i}}" > (%) Performance</label>
                    <input type="text" id="work_done-{{$i}}" name="work_done{{$i}}" class="form-control box-size work_done" step="0.01" readonly>
                </div>

                <div class="col-12 col-lg-9 mt-1 mt-lg-1">
                    <label for="description{{$i}}">Description:</label>
                    <textarea id="description{{$i}}" name="description{{$i}}" class="form-control box-size mb-2" rows="4" ></textarea>
                </div>

                <div id="removeButton{{$i}}" class="float-right mt-4 ml-3" @if($i === 0) hidden="true" @endif>
                    <button type="button" class="btn btn-danger"> Remove </button>
                </div>

                <hr class="col-10 mt-2 mb-3 ml-2">

            </div>
        @endfor

    </div>


    <button id="toggleButton" type="button" class="btn btn-secondary ml-2 mb-2">Add a Key Activity</button>


</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">


        tinymce.init({
            selector: 'textarea',
            menubar: 'file edit view format table tools',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 230,
        });



    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        {{--const taskCategories = @json($taskCategories);--}}
        {{--const subcategories = @json($taskSubcategories);--}}

        // // Function to populate the task select box based on the selected department
        // function populateTasks(taskNumber) {
        //
        //     const selectedCategory = $("#category" + taskNumber).val();
        //     const subcategorySelect = $("#subcategory" + taskNumber);
        //     subcategorySelect.empty(); // Clear existing options
        //
        //     if (selectedCategory in subcategories) {
        //         const subs = subcategories[selectedCategory];
        //
        //         subcategorySelect.append(new Option('-- Select Subcategory --', ''));
        //         subs.forEach(function (task) {
        //             subcategorySelect.append(new Option(task, task));
        //         });
        //     }
        //
        // }
        //
        // // Populate the task select box initially
        // populateTasks();
        //
        // for(let i = 0; i < 20; i++ ){
        //     $("#category" + i).on("change", function() {
        //         populateTasks(i);
        //     });
        // }

        // Initially hide the textarea
        for(let i = 1; i < 20; i++ ) {
            $('#task' + i).hide();
        }

        for(let i = 1; i < 20; i++ ) {
            $('#removeButton' + i).click(function () {
                $('#task' + i).hide();

                $('#category' + i).val('');
                $('#hours' + i).val('');
                $('#description' + i).val('');
            });
        }

        // Attach a click event handler to the toggle button
        let taskNumber = 1;
        $('#toggleButton').click(function() {
            // Toggle the visibility of the textarea
            $('#task' + taskNumber).show();
            taskNumber++;
        });

        $("#taskDiv").on("change", ".new_task", function() {
            const id = $(this).attr('id').split('-')[1];
            let subcategory_id = $('#subcategory-'+id).val();
            $.ajax({
                url: "{{route('biller.employee-task-subcategories.get_data')}}",
                method: 'POST',
                data : {
                    subcategory_id : subcategory_id
                },
                success: function(response) {
                    $('#key_activity-'+id).text(response.key_activities);
                    $('#target-'+id).val(response.target);
                    $('#uom-'+id).val(response.uom);
                    $('#frequency-'+id).val(response.frequency);
                }
            });
        });
        $("#taskDiv").on("change", ".performance", function() {
            const id = $(this).attr('id').split('-')[1];
            let performance = $('#performance-'+id).val();
            let target = $('#target-'+id).val();
            if(target === ''){
                alert('No Category Selected');
                $('#performance-'+id).val('');
            }
            let percentage = performance / target * 100;
            if(percentage > 140){
                alert('Percentage must be between 0 and 140');
                $('#work_done-'+id).val(140);
            }
            else $('#work_done-'+id).val(percentage);
        });
        function updateTotalHours(currentInput) {
            let totalHours = 0;

            // Iterate over all inputs with class .hours within #taskDiv
            $('#taskDiv .hours').each(function() {
                const hoursValue = parseFloat($(this).val());
                if (!isNaN(hoursValue)) {
                    totalHours += hoursValue; // Sum only valid numeric values
                }
            });

            // Check if total hours exceed 13
            if (totalHours > 13) {
                alert("Total hours cannot exceed 13 hours.");
                currentInput.val(0); // Reset the current input to zero
            }
        }

        // Attach input event to all hour inputs within #taskDiv
        $('#taskDiv').on('input', '.hours', function() {
            updateTotalHours($(this)); // Pass the current input to the function
        });

        // Initial check on page load
        updateTotalHours($('#taskDiv .hours').first()); // Pass the first input for initial check
    });

</script>
@endsection
