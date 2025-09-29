@extends ('core.layouts.app')
@section ('title', 'Create | Labour Allocation Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Labour Creation</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.labour_allocations.partials.labour_allocation-header-buttons')
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <select class="form-control select2" id="customerFilter" data-placeholder="Search Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <select class="form-control select2" id="branchFilter" data-placeholder="Search Branch">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div> 
                    </div>
                </div>    
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <table id="labour_allocationTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>#Project No.</th>
                                    <th>Project Title</th>
                                    <th>#QT/PI No.</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Start Date</th>
                                    <th>Deadline</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="100%" class="text-center text-success font-large-1">
                                        <i class="fa fa-spinner spinner"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>    
    </div>
    <!-- End Content -->
</div>

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>
@include('focus.labour_allocations.modals.labour_hours_modal')
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customerFilter").val()}),
                processResults: (data) => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) };
                },
            }
        },
        casualSelect: {
            dropdownParent: $('#employeesTbl'),            
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
        }
    };

    // form submit callback
    function trigger(res) {
        // $(data.row).prependTo("table > tbody");
        // $("#data_form_project").trigger('reset');
        $('#labour_allocation-table').DataTable().destroy();
        Form.drawDataTable();
    }

    function getProjectMilestones(projectId){
        $.ajax({
            url: "{{ route('biller.getProjectMileStones') }}",
            method: 'GET',
            data: { projectId: projectId},
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
                    let selectedOptionValue = "{{ @$purchase->project_milestone }}";
                    if (selectedOptionValue) {
                        select.val(selectedOptionValue);
                    }
                    // checkMilestoneBudget(select.find('option:selected').text());
                }
            },
            error: function() {
                // Handle errors here
                console.log('Error loading data');
            }
        });
    }

    function checkMilestoneBudget(milestoneString){
        // Get the value of the input field
        let selectedMilestone = milestoneString;
        // Specify the start and end strings
        let startString = 'Balance: ';
        let endString = ' | Due on';
        // Find the index of the start and end strings
        let startForm = selectedMilestone.indexOf(startString);
        let endForm = selectedMilestone.indexOf(endString, startForm + startString.length);
        // Extract the string between start and end
        let milestoneBudget = parseFloat(selectedMilestone.substring(startForm + startString.length, endForm)).toFixed(2);
        if(purchaseGrandTotal > milestoneBudget){
            $("#milestone_warning").text("Milestone Budget of " + milestoneBudget + " Exceeded!");
        } else {
            $("#milestone_warning").text("");
        }        
    }

    const Form = {
        rowHtml: $('#employeesTbl tbody tr:first').clone().html(),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);
            $('#date').datepicker('setDate', new Date());
            $("#customerFilter").select2({allowClear: true}).change(Form.onChangeCustomer);
            $("#branchFilter").select2(config.branchSelect).change(Form.onChangeBranch);
            $('#employeesTbl .casual-labourer:first').select2(config.casualSelect);
            Form.drawDataTable();

            $("#submit-data_project").on("click", Form.onSubmitProject);
            $('#AddEmployeeModal').on('shown.bs.modal', Form.onShowAttachLabourModal);
            $('#labour_allocationTbl').on('click', '.labour', Form.onClickAddLabour);
            $('#project_milestone').on('change', Form.getTask);
            $('#task').on('change', Form.taskChange);
            $(document).on('change', '#percent_qty, #percent_type', Form.projectPercentageChange);

            $(document).on('change', '#sharedHrs, #weeklyHrs', Form.changeHourType);
            $(document).on('click', '.del-row', Form.clickDeleteRow);
            $('#addRowBtn').click(Form.clickAddRow);
            $('#employeesTbl').on('change', '.casual-labourer', Form.changeCasualLabourer);
            $('#employeesTbl').on('keyup', '.reg-hrs, .ot-hrs', Form.keyupHours);
            $('#csvUpload').change(Form.onChangeCsvUpload);
        },

        onChangeCsvUpload(e) {
            const file = e.target.files[0];
            {{-- const reader = new FileReader();
            reader.onload = function (event) {
                const text = event.target.result;
                const rows = text.split("\n").map(row => row.split(","));
                const bodyRows = rows.slice(1); 
                Form.uploadIds = bodyRows.map(v => v[0] ?? null);
                Form.uploadRows = rows;
                Form.fetchCasuals();
            };
            reader.readAsText(file); --}}
            Form.file = file;
            Form.fetchCasuals();
        },

        fetchCasuals() {
            const formData = new FormData();
            formData.append("csv_file", Form.file);

            $.ajax({
                url: "{{ route('biller.labour_allocations.get_casuals') }}",
                type: 'POST',
                data: formData,
                contentType: false,        // prevent jQuery from setting content type
                processData: false,        // prevent jQuery from processing the data
                success: function (data) {
                    $('#employeesTbl tbody').html('');
                    data.forEach((v,i) => {
                        const tr = $(`<tr>${Form.rowHtml}</tr>`);
                        tr.find('.index').html(i+1);
                        tr.find('.empl-id').html(v.id_number);
                        tr.find('.casual-labourer')
                        .append( `<option value="${v.id}" selected>${v.name}</option>`)
                        .select2(config.casualSelect);
                        tr.find('.casual-labourer')
                        tr.find('.reg-hrs').each(function(i) {
                            $(this).val(v.regular_hours[i] || 0);
                        });
                        tr.find('.ot-hrs').each(function(i) {
                            $(this).val(v.overtime_hours[i] || 0);
                        });
                        $('#employeesTbl tbody').append(tr);
                        $('#employeesTbl .ot-hrs:last').keyup();
                    });
                },
                error: function (xhr, status, error) {
                  console.error("Upload failed:", error);
                  alert('Error loading data! Try again later or contact admin');
                }
            });
        },

        changeHourType() {
            if ($(this).is('#sharedHrs')) {
                $('.shared-hrs-ctn').removeClass('d-none');
                const ctn = $('.weekly-hrs-ctn');
                ctn.find('input').val('');
                ctn.find('select').val('').change();
                ctn.addClass('d-none')
            } else if ($(this).is('#weeklyHrs')) {
                $('.weekly-hrs-ctn').removeClass('d-none');
                const ctn = $('.shared-hrs-ctn');
                ctn.find('input').val('');
                ctn.find('select').val('').change();
                ctn.addClass('d-none');
            }
        },

        keyupHours() {
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
        },

        changeCasualLabourer() {
            const [data] = $(this).select2('data');
            if (data && data.id) {
                const tr = $(this).parents('tr:first');
                tr.find('.empl-id').html(data.id_number);
            }
        },

        clickDeleteRow() {
            $(this).parents('tr:first').remove();
            $('#employeesTbl tbody tr').each(function(i) {
                $(this).find('.index').html(i+1);
            });
        },

        clickAddRow() {
            $('#employeesTbl tbody').append(`<tr>${Form.rowHtml}</tr>`);
            $('#employeesTbl .casual-labourer:last').select2(config.casualSelect);
            $('#employeesTbl tbody tr').each(function(i) {
                $(this).find('.index').html(i+1);
            });
        },
        
        onSubmitProject() {
            e.preventDefault();
            let form_data = {};
            form_data['form'] = $("#data_form_project").serialize();
            form_data['url'] = $('#action-url').val();
            $('#AddEmployeeModal').modal('toggle');
            addObject(form_data, true);
        },

        projectPercentageChange() {
            let percent_value = parseFloat($('#percent_qty').val());
            let task_completion = parseFloat($('#task_percent').val());
            let expected_percent_value = 0;
            let combined_percent_value = 0;
            if($('#percent_type').val() == 'increment' && task_completion >= 0){
                combined_percent_value = percent_value + task_completion;
                if(combined_percent_value > 100){
                    expected_percent_value = 100 - task_completion;
                    $('#percent_qty').val(expected_percent_value);
                }
            }else if($('#percent_type').val() == 'decrement' && task_completion >= 0){
                combined_percent_value = task_completion - percent_value;
                if(combined_percent_value < 1){
                    expected_percent_value = task_completion;
                    $('#percent_qty').val(expected_percent_value);
                }
            }
        },

        taskChange(){
            let percentage = $(this).find('option:selected').data('percentage');
            $('#task_percent').val(percentage);
        },

        getTask(){
            let milestone_id = this.value;
            console.log(milestone_id);
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
                        value: null,
                        text: 'Select a Task'
                    }));

                    if (data.length === 0) {
                        select.append($('<option>', {
                            value: null,
                            text: 'No tasks available'
                        }));
                    } else {
                        $.each(data, function(index, option) {
                            select.append($('<option>', { 
                                value: option.id,
                                text : option.name + ' | Progress: ' + parseFloat(option.task_completion).toFixed(2),
                                "data-percentage": option.task_completion 
                            }));
                        });
                    }
                }
            });
        },

        onChangeCustomer() {
            $("#branchFilter option:not(:eq(0))").remove();
            $('#labour_allocationTbl').DataTable().destroy();
            Form.drawDataTable();
        },

        onChangeBranch() {
            $('#labour_allocationTbl').DataTable().destroy();
            Form.drawDataTable(); 
        },
        
        onChangeProject(){
            $('#labour').removeAttr('data-id');
        },
        
        onClickAddLabour() {
            const projectId = $(this).attr('data-id');
            $('#project_id').val(projectId);
            
            // fetch expected hours
            $('#expectedHrs').html(`(Rem: ${0})`);
            $.get("{{ route('biller.labour_allocations.expected_hours') }}?project_id=" + projectId, function(data) {
                $('#expectedHrs').html(`(Rem: ${data.hours})`);
                $('#project_name').html(`${data.project_tid}: <span class="text-primary">${data.quote_tid}</span>`);
                $('#task_percent').val(data.progress)

                getProjectMilestones(data.project_id)
                let select = $('#task');
                select.empty();
                select.append($('<option>', {
                    value: 0,
                    text: 'No Tasks Created For This Milestone',
                }));
            });
        },

        onShowAttachLabourModal() {
            $("#employee").select2();
            $("#casual_labourers").select2();
            $("#person").select2({allowClear: true, dropdownParent: $('#AddEmployeeModal .modal-body')});
            
            // job type change
            $('#type').change(function() {
                if (this.value == 'diagnosis') {
                    $('#is_payable').val(0);
                    $('#percent_qty').attr('readonly', false);
                }else if (this.value == 'special_movement_allowance' || this.value == 'standby_time'){
                    $('#is_payable').val(1);
                    $('#percent_qty').val(0);
                    $('#percent_qty').attr('readonly', true);
                }
                else {
                    $('#is_payable').val(1);
                    $('#percent_qty').attr('readonly', false);
                }
            });
        },

        drawDataTable() {
            $('#labour_allocationTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                ajax: {
                    url: "{{ route('biller.labour_allocations.get_labour') }}",
                    type: 'POST',
                    data: {
                        customer_id: $("#customerFilter").val(),
                        branch_id: $("#branchFilter").val(),
                    }
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...['tid', 'name','main_quote_id', 'priority', 'status','start_date'].map(v => ({data: v, name: v})),
                    {data: 'end_date', name: 'end_date', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(Form.init);
</script>
@endsection
