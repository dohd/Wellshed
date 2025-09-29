<head>
    <style>
        .green-circle {
            width: 20px; /* Adjust size as needed */
            height: 20px; /* Adjust size as needed */
            background-color: #55FF00;
            border-radius: 50%;
        }
    </style>
</head>

<div class="form-group row">
    <div class="col-md-3">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' =>'labour_date']) }}
        {{ Form::hidden('id', null, ['class' => 'form-control']) }}
    </div>
    <div class="col-md-3">
        <label for="type">Job Type</label>
        <select name="type" id="type" class="custom-select" required>
            <option value="">-- Select Job Type --</option>
            @foreach(['diagnosis', 'repair', 'maintenance', 'installation', 'supply', 'special_movement_allowance', 'paid_idle_time', 'others'] as $value)
                <option value="{{ $value }}" {{ $value == $labour_allocation->type? 'selected' : '' }}>
                    @php
                        if ($value == 'diagnosis') echo 'Diagnosis / Site Survey';
                        elseif ($value == 'special_movement_allowance') echo 'Special Movement Allowance (> 3Hrs)';
                        elseif ($value == 'paid_idle_time') echo 'Paid Idle Time';
                        else echo ucfirst($value);
                    @endphp
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="job_card">Job Card / DNote</label>
        <div class="row no-gutters">
            <div class="col-md-6">
                <select name="ref_type" class="custom-select">
                    @foreach (['jobcard' => 'Job Card', 'dnote' => 'Delivery Note'] as $key => $value)
                        <option value="{{ $key }}" {{ $key == $labour_allocation->ref_type? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                {{ Form::text('job_card', null, ['class' => 'form-control', 'id' =>'job_card']) }}
            </div>
        </div>
    </div>
</div>

<div class="form-group row">
    <div class="col-md-3">
        <label for="project_milestone" class="caption" style="display: inline-block;">Project Budget Line</label>
        <select id="project_milestone" name="project_milestone" class="form-control">
            <option value="">Select a Budget Line</option>
        </select>
    </div>
    <div class="col-md-3">
        <label for="task" class="caption" style="display: inline-block;">Tasks</label>
        <select id="task" name="task_id" class="form-control">
            <option value="0">Select a Task</option>
        </select>
    </div>
        
    <div class="col-md-2">
        <label for="task_percent">% Progress</label>
        @php
            $percent = 0;
            if($labour_allocation->task){
                $percent = $labour_allocation->task->task_completion;
            }elseif($labour_allocation->budgetLine){
                $percent = $labour_allocation->budgetLine->milestone_completion;
            }else{
                $percent = $labour_allocation->project ? $labour_allocation->project->progress : 0;
            }
        @endphp
        <input type="text" id="task_percent" value="{{$percent}}" readonly class="form-control">
    </div>
    <div class="col-md-2">
        <label for="percent_type">Progress Action</label>
        <select name="percent_type" id="percent_type" class="form-control">
            <option value="">-----Select Type -------</option>
            @foreach (['increment','decrement'] as $item)
                <option value="{{$item}}" {{@$labour_allocation->task_item->type == $item ? 'selected' : ''}}>{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label for="percent_qty">% Progress Value</label>
        <input type="text" id="percent_qty" value="{{@$labour_allocation->task_item->percent_qty}}" name="percent_qty"  class="form-control">
    </div>
</div>

<div class="form-group row">
    <div class="col-md-6">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' =>'note']) }}
    </div>
</div>

<div class="row mb-1">
    <div class="col-md-6">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" id="sharedHrs" name="hour_type"> 
            <label class="form-check-label" for="sharedHrs">Shared Daily Hours</label>
        </div> 
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" id="weeklyHrs" name="hour_type"> 
            <label class="form-check-label" for="weeklyHrs">Weekly Casual Hours</label>
        </div>                     
    </div>
</div>

<!-- Shared Casual Hours -->
<fieldset class="border p-1 mb-3 shared-hrs-ctn d-none">
    <legend class="w-auto float-none h5">Shared Casuals Hours</legend>
    <div class="form-group row">
        <div class="col-md-2">
            <label for="hrs">Hours</label>
            {{ Form::text('hrs', null, ['class' => 'form-control', 'required']) }}
        </div>
        <div class="col-md-1">
            <label for="hrs">Is Payable</label>
            <select name="is_payable" id="is_payable" class="custom-select">
                @foreach(['1' => 'Yes','0' => 'No'] as $key => $value)
                    <option value="{{ $key }}" {{ $key == $labour_allocation->is_payable? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-6">
            <label for="assigns">Employees</label>
            <select class="form-control select-box" name="employee_id[]" id="employee" data-placeholder="Search for an Employee" multiple>
                <option value=""></option>
                @php $employeeIds = $labour_allocation->items->pluck('employee_id')->toArray() @endphp
                @foreach($employees as $employee)
                    <option value="{{ $employee['id'] }}" {{ in_array($employee['id'], $employeeIds)? 'selected' : '' }}>
                        {{ $employee->fullname }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label for="casual_labourers">Casual Labourers</label>
            <select class="form-control select-box" name="casual_labourer_id[]" id="casual_labourers" data-placeholder="Search for a Casual Labourer" multiple>
                <option value=""></option>
                @php 
                    $casualLabourerIds = $labour_allocation->casualLabourers->pluck('id')->toArray();
                    if ($labour_allocation->casualWeeklyHrs->count()) {
                        $casualLabourerIds = [];
                    }
                @endphp
                @foreach($casualLabourers as $cL)
                    <option value="{{ $cL['id'] }}" {{ in_array($cL['id'], $casualLabourerIds)? 'selected' : '' }}>
                        {{ $cL->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>    
</fieldset>    

<!-- Weekly Casual Hours -->
@include('focus.labour_allocations.partials.weekly_hours')

@section('extra-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        casualSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.labour_allocations.casuals_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({term}),
                processResults: (data) => {
                    return { 
                        results: data.map(v => ({
                            text: v.name + ` - ${v.id_number}`, 
                            id: v.id,
                            id_number: v.id_number || '',
                        })) 
                    };
                },
            }
        },
    };

    $.ajaxSetup(config.ajax);
    $('#employee, #casual_labourers').select2({allowClear: true});

    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});
    $('#labour_date').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());
    const date = "{{ $labour_allocation->date }}";
    if (date) $('#labour_date').datepicker('setDate', new Date(date));
    const periodFrom = "{{ $labour_allocation->period_from }}";
    const periodTo = "{{ $labour_allocation->period_to }}";
    if (periodFrom || periodTo) {
        $('#period_from').datepicker('setDate', new Date(periodFrom));
        $('#period_to').datepicker('setDate', new Date(periodTo));
    }

    $(document).on('change', '#sharedHrs, #weeklyHrs', changeHourType);
    const weeklyHrs = @json(@$labour_allocation->casualWeeklyHrs);
    if (weeklyHrs && weeklyHrs.length) {
        $('#weeklyHrs').prop('checked', true);
        $('.weekly-hrs-ctn').removeClass('d-none');
    } else {
        $('#sharedHrs').prop('checked', true);
        $('.shared-hrs-ctn').removeClass('d-none');
    }
    function changeHourType() {
        if ($(this).is('#sharedHrs')) {
            $('.shared-hrs-ctn').removeClass('d-none');
            const ctn = $('.weekly-hrs-ctn');
            // ctn.find('input').val('');
            // ctn.find('select').val([]).change();
            ctn.addClass('d-none')
        } else if ($(this).is('#weeklyHrs')) {
            $('.weekly-hrs-ctn').removeClass('d-none');
            const ctn = $('.shared-hrs-ctn');
            // ctn.find('input').val('');
            // ctn.find('select').val([]).change();
            ctn.addClass('d-none');
        }
    }

    /** Manage Weekly Casuals **/
    const rowHtml = $('#employeesTbl tbody tr:last').clone().html();
    $('#employeesTbl tbody tr:last').remove();
    $('#employeesTbl .casual-labourer').each(function() {
        $(this).select2(config.casualSelect);
    });
    $('#addRowBtn').click(function() {
        $('#employeesTbl tbody').append(`<tr>${rowHtml}</tr>`);
        $('#employeesTbl .casual-labourer:last').select2(config.casualSelect);
        $('#employeesTbl tbody tr').each(function(i) {
            $(this).find('.index').html(i+1);
        });
    });
    $(document).on('click', '.del-row', function() {
        $(this).parents('tr:first').remove();
        $('#employeesTbl tbody tr').each(function(i) {
            $(this).find('.index').html(i+1);
        });
    });
    $('#employeesTbl').on('change', '.casual-labourer', function() {
        const [data] = $(this).select2('data');
        if (data && data.id) {
            const tr = $(this).parents('tr:first');
            tr.find('.empl-id').html(data.id_number);
        }
    });
    $('#employeesTbl').on('keyup', '.reg-hrs, .ot-hrs', function() {
        let regRowTtl = 0;
        let otRowTtl = 0;
        const tr = $(this).parents('tr:first');
        tr.find('.reg-hrs').each(function() {
            regRowTtl += accounting.unformat($(this).val());
        });
        tr.find('.ot-hrs').each(function() {
            otRowTtl += accounting.unformat($(this).val());
        });
        tr.find('.reg-total').val(regRowTtl);
        tr.find('.ot-total').val(otRowTtl);
        tr.find('.total-hrs').val(otRowTtl + regRowTtl);
    });
    $('#employeesTbl tbody tr').each(function() {
        $(this).find('.reg-hrs:first').keyup();
    });

    /** Manage Project & Tasks*/
    const projectId = "{{ $labour_allocation->project_id  }}";
    const milestone = "{{ $labour_allocation->project_milestone  }}";
    getProjectMilestones(projectId);
    getTask(milestone);
    // task change
    $('#task').on('change', function(){
        let percentage = $(this).find('option:selected').data('percentage');
        $('#task_percent').val(percentage);
        $('#percent_qty').val('');
    });
    // milestone change
    $('#project_milestone').on('change', function(){
        let milestone_id = this.value;
        $.ajax({
            url: "{{ route('biller.tasks.get_milestone')}}",
            method: 'POST',
            data: {
                milestone_id: milestone_id
            },
            success: function(data){
                $('#task_percent').val(accounting.unformat(data.milestone_completion));
            }
        });
        $.ajax({
            url: "{{ route('biller.tasks.get_tasks')}}",
            method: 'POST',
            data: {
                milestone_id: milestone_id
            },
            success: function(data){
                var select = $('#task');
                select.empty();
                select.append($('<option>', {
                    value: 0,
                    text: 'Select a Task'
                }));
                $.each(data, function(index, option) {
                    select.append($('<option>', { 
                        value: option.id,
                        text : option.name + ' | Progress: ' +  parseFloat(option.task_completion).toFixed(2),
                        "data-percentage": option.task_completion 
                    })).change();
                });
                
            }
        })
    });
    // % progress change
    $(document).on('change', '#percent_qty, #percent_type', function() {
        let percent_value = parseFloat($('#percent_qty').val());
        let task_completion = parseFloat($('#task_percent').val());
        let expected_percent_value = 0;
        let combined_percent_value = 0;
        if($('#percent_type').val() == 'increment'){
            combined_percent_value = percent_value + task_completion;
            if(combined_percent_value > 100){
                expected_percent_value = 100 - task_completion;
                $('#percent_qty').val(expected_percent_value);
            }
        }else if($('#percent_type').val() == 'decrement'){
            combined_percent_value = task_completion - percent_value;
            if(combined_percent_value < 1){
                expected_percent_value = task_completion;
                $('#percent_qty').val(expected_percent_value);
            }
        }
    });
    // job type change
    $('#type').change(function() {
        if (this.value == 'diagnosis') $('#is_payable').val(0);
        else $('#is_payable').val(1);
    });

    function getProjectMilestones(projectId){
        $.ajax({
            url: "{{ route('biller.getProjectMileStones') }}",
            method: 'GET',
            data: { projectId },
            dataType: 'json', // Adjust the data type accordingly
            success: function(data) {
                // This function will be called when the AJAX request is successful
                var select = $('#project_milestone');
                // Clear any existing options
                select.empty();
                if(data.length === 0){
                    select.append($('<option>', {
                        value: null,
                        text: 'No Milestones Created For This Project'
                    }));
                } else {
                    select.append($('<option>', {
                        value: null,
                        text: 'Select a Budget Line'
                    }));
                    // Add new options based on the received data
                    for (var i = 0; i < data.length; i++) {
                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        const date = new Date(data[i].due_date);
                        select.append($('<option>', {
                            value: data[i].id,
                            text: data[i].name + ' | Balance: ' +  parseFloat(data[i].balance).toFixed(2) + ' | Due on ' + date.toLocaleDateString('en-US', options)
                        }));
                    }
                }

                // default selected option
                select.find('option').each(function() {
                    const milestoneId = "{{ $labour_allocation->project_milestone }}";
                    if ($(this).attr('value') == milestoneId) {
                        $(this).attr('selected', true);
                        select.val(milestoneId);
                    } else {
                        $(this).attr('selected', false);
                    }
                });
            },
            error: function() {
                // Handle errors here
                console.log('Error loading data');
            }
        });
    }

    function getTask(milestone){
        let milestone_id = milestone;
        $.ajax({
            url: "{{ route('biller.tasks.get_tasks')}}",
            method: 'POST',
            data: {
                milestone_id,
            },
            success: function(data){
                var select = $('#task');
                // select.empty();
                select.append($('<option>', {
                    value: null,
                    text: 'Select a Task'
                }));

                $.each(data, function(index, option) {
                    select.append($('<option>', { 
                        value: option.id,
                        text : option.name + ' | Progress: ' +  parseFloat(option.task_completion).toFixed(2),
                        "data-percentage": option.task_completion 
                    }));
                });

                // default selected option
                select.find('option').each(function() {
                    const taskId = "{{ $labour_allocation->task_id }}";
                    if ($(this).attr('value') == taskId) {
                        $(this).attr('selected', true);
                        select.val(taskId);
                    } else {
                        $(this).attr('selected', false);
                    }
                });
            }
        })
    }
</script>
@endsection
