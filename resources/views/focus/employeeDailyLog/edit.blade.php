<!DOCTYPE html >

@include('tinymce.scripts')

@extends ('core.layouts.app')

@section ('title',  'Employee Daily Log')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class="mb-0">Edit Employee Daily Log</h2>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.employeeDailyLog.partials.edl-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body" id="taskDiv">

                            {{ Form::open(['route' => ['biller.employee-daily-log.update', $edlNumber], 'method' => 'PUT', 'id' => 'edit-employee-log']) }}


                            <h2 class="font-weight-bold mb-2">EDL</h2>
                            @foreach($data['edl'] as $edl)

                                <div class="row">

{{--                                    <div class="col-md-4">--}}
{{--                                        <label for="edl_number">EDL Number:</label>--}}
{{--                                        <input type="text" id="edl_number" name="edl_number" readonly value="{{ $edl['edl_number'] }}" class="form-control box-size mb-2">--}}
{{--                                    </div>--}}
                                    <div class="col-md-4">
                                        <label for="employee" >Employee:</label>
                                        <input type="text" id="employee" name="employee" readonly value="{{ $edl['employee'] }}" class="form-control box-size mb-2">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="date">Date:</label>
                                        <input type="text" id="date" name="date" readonly value="{{ $edl['date'] }}" class="form-control box-size mb-2">
                                    </div>

                                </div>
                            @endforeach

                            <h2 class="font-weight-bold mb-2">Key Activities</h2>

                            @php
                                $i = 21;
                            @endphp

                            @foreach($data['edlTasks'] as $task)

                                <div class="row mb-2">

                                    <h3 class="col-12 mb-1">Key Activity #{{ $i }} <span id="key_activity-{{$i}}">{{$task->key_activities}}</span></h3>

                                    <div class="col-6 col-lg-6">
                                        <label for="subcategory{{$task['et_number']}}" >Key Performance Indicator:</label>

                                        <select id="subcategory{{$task['et_number']}}" data-id="{{$i}}" @if($i === 1) required @endif class="form-control box-size tasking" name="subcategory{{$task['et_number']}}">
                                            @if(empty($taskCategories[0]))
                                                <option value=""> No Key Performance Indicators Allocated to You </option>
                                            @else
                                                <option value="">-- Select KPI: --</option>
                                                @foreach ($taskCategories as $cat)
                                                    <option value="{{ $cat['value'] }}" @if ($cat['value'] === $task['subcategory']) selected @endif>
                                                        {{ array_search($cat ,$taskCategories) + 1 . '. ' . $cat['label'] . '  |  ' . $cat['frequency'] }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <code>{{$task['category']}}</code>
                                    </div>

                                    <div class="col-4 col-lg-1">
                                        <label for="hours{{$task['et_number']}}">Actual Hours:</label>
                                        <input type="number" max="9" id="hours{{$task['et_number']}}" name="hours{{$task['et_number']}}" @if($i === 1) required @endif value="{{ $task['hours'] }}" step="0.01" class="form-control box-size hours">
                                    </div>

                                    <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                                        <label for="performance{{$task['et_number']}}" >Performance:</label>
                                        <input type="text" id="performance-{{$i}}" value="{{ number_format($task['performance'], 2) }}" name="performance{{$task['et_number']}}" class="form-control box-size performance" step="0.01" >
                                    </div>
                                    <div class="col-5 col-lg-2 mt-1 mt-lg-0">
                                        <label for="target{{$task['et_number']}}" >Target / UoM</label>
                                        <div class="row no-gutters">
                                            <div class="col-5">
                                                <input type="text" id="target-{{$i}}" class="form-control box-size" value="{{number_format($task->target, '2')}}" step="0.01" readonly>
                                            </div>
                                            <div class="col-5">
                                                <input type="text" id="uom-{{$i}}" class="form-control box-size" step="0.01" value="{{$task->uom}}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                                        <label for="frequency{{$task['et_number']}}" >Frequency:</label>
                                        <input type="text" id="frequency-{{$i}}" class="form-control box-size frequency" value="{{$task->frequency}}" step="0.01" readonly>
                                    </div>
                                    <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                                        <label for="work_done{{$task['et_number']}}" >(%) Performance</label>
                                        <input type="text" id="work_done-{{$i}}" value="{{ number_format($task['work_done'], 2) }}" name="work_done{{$task['et_number']}}" class="form-control box-size work_done" step="0.01" readonly>
                                    </div>

                                    <div  class="col-12 col-lg-9">
                                        <label for="description{{$task['et_number']}}">Description:</label>
                                        <textarea id="description{{$task['et_number']}}" name="description{{$task['et_number']}}" @if($i === 1) required @endif class="form-control box-size" rows="3">{{ $task['description'] }}</textarea>
                                    </div>

                                </div>

                                <hr class="col-10 mt-2 mb-3 ml-2">

                                @php
                                    $i++;
                                @endphp


                            @endforeach


                            <div class="mb-3">
                                @for($i = 0; $i < 20; $i++)
                                    <div class="row" id="task{{$i}}">

                                        <div class="col-12 mb-1">{{ 'New Key Activity ' . ($i + 1) }} <span id="key_activity-{{$i}}"></span></div>


                                        <div class="col-6 col-lg-6">
                                            <label for="subcategory{{$i}}">Key Performance Indicator:</label>
                                            <select class="form-control box-size new_task" id="subcategory-{{$i}}" name="subcategory{{$i}}">
                                                <option value="">-- Select KPI: --</option>
                                                @if(empty($taskCategories[0]))
                                                    <option value="">No Categories Created for Your Department</option>
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
                                            <input type="number" max="9" id="hours{{$i}}" name="hours{{$i}}" step="0.01" class="form-control box-size hours">
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
                                            <label for="work_done{{$i}}" >(%) Performance</label>
                                            <input type="text" id="work_done-{{$i}}" name="work_done{{$i}}" class="form-control box-size work_done" step="0.01" readonly>
                                        </div>

                                        <div class="col-12 col-lg-9 mt-1 mt-lg-1">
                                            <label for="description{{$i}}">Description:</label>
                                            <textarea id="description{{$i}}" name="description{{$i}}" class="form-control box-size mb-2" rows="4"></textarea>
                                        </div>

                                        <div id="removeButton{{$i}}" class="float-right mt-4 ml-3" >
                                            <button type="button" class="btn btn-danger"> Remove </button>
                                        </div>

                                        <hr class="col-10 mt-2 ml-2">

                                    </div>
                                @endfor

                                <button id="toggleButton" type="button" class="btn btn-secondary ml-2 mb-2">Add a Key Activity</button>
                            </div>



                            <a href="{{ route('biller.employee-daily-log.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update EDL</button>

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

    tinymce.init({
        selector: 'textarea',
        menubar: 'file edit view format table tools',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 230,
    });


    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#purchase_date').datepicker('setDate', new Date());
    $('#warranty_expiry_date').datepicker('setDate', new Date());

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        // Initially hide the textarea
        for(let i = 0; i < 20; i++ ) {
            $('#task' + i).hide();
        }

        for(let i = 0; i < 20; i++ ) {
            $('#removeButton' + i).click(function () {
                $('#task' + i).hide();

                $('#category' + i).val('');
                $('#hours' + i).val('');
                $('#description' + i).val('');
            });
        }

        // Attach a click event handler to the toggle button
        let taskNumber = 0;
        $('#toggleButton').click(function() {
            // Toggle the visibility of the textarea
            $('#task' + taskNumber).show();
            taskNumber++;
        });

        
        $("#taskDiv").on("change", ".tasking", function(e) {
            var subcategory_id = $(this).val();
            var taskId = e.target.getAttribute('data-id');
            $.ajax({
                url: "{{route('biller.employee-task-subcategories.get_data')}}",
                method: 'POST',
                data : {
                    subcategory_id : subcategory_id
                },
                success: function(response) {
                    $('#key_activity-'+taskId).text(response.key_activities);
                    $('#target-'+taskId).val(response.target);
                    $('#uom-'+taskId).val(response.uom);
                    $('#frequency-'+taskId).val(response.frequency);
                }
            });
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