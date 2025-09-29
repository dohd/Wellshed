<div class="card-content">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                   aria-selected="true">Bio Data</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9" role="tab"
                   aria-selected="false">Roles & Permissions</a>
            </li>


        </ul>
      
        <div class="tab-content px-1 pt-1">


              <!---Biodata tab-->
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                <div class="form-group" hidden>
                    <input type="number" id="is_stakeholder" name="is_stakeholder" class="form-control round" value="1">
                    <input type="number" id="status" name="status" class="form-control round" value="0">
                </div>

                <!-- Row for First Name and Last Name -->
                <div class="form-group row">
                    <div class="col-lg-4">
                        <label for="first_name" class="control-label">{{ trans('hrms.first_name') }}</label>
                        <input type="text" id="first_name" name="first_name" class="form-control round" placeholder="{{ trans('hrms.first_name') }} *" required value="{{@$stakeholder->first_name}}">
                    </div>
                    <div class="col-lg-4">
                        <label for="last_name" class="control-label">Other Names</label>
                        <input type="text" id="last_name" name="last_name" class="form-control round" placeholder="Other Names" value="{{@$stakeholder->last_name}}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_number" class="col-lg-2 control-label">ID Number</label>
                    <div class="col-lg-8">
                        <input type="text" id="id_number" name="sh_id_number" class="form-control round" placeholder="ID Number *" required value="{{@$stakeholder->sh_id_number}}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="gender" class="col-lg-2 control-label">Gender *</label>
                    <div class="col-lg-8">
                        <select id="gender" name="sh_gender" class="form-control round" required>
                            <option value="">-- Select Gender --</option>

                            @foreach (['Male', 'Female'] as $g)
                                <option
                                    value="{{$g}}"
                                    @if(@$stakeholder->sh_gender === $g) selected @endif
                                >
                                    {{$g}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="primary_contact" class="col-lg-2 control-label">Phone</label>
                    <div class="col-lg-8">
                        <input type="text" id="primary_contact" name="sh_primary_contact" class="form-control round" placeholder="{{ trans('hrms.phone') }} *" required value="{{@$stakeholder->sh_primary_contact}}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="secondary_contact" class="col-lg-2 control-label">Alternative Contact</label>
                    <div class="col-lg-8">
                        <input type="text" id="secondary_contact" name="sh_secondary_contact" class="form-control round" placeholder="{{ trans('hrms.phone') }}" value="{{@$stakeholder->sh_secondary_contact}}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="col-lg-2 control-label">Official/Business Email</label>
                    <div class="col-lg-8">
                        <input type="text" id="email" name="email" class="form-control round" placeholder="{{ trans('hrms.email') }} *" required value="{{@$stakeholder->email}}">
                    </div>
                </div>

                <!-- Fields for Company, Designation, and Access Reason -->
                <div class="form-group">
                    <label for="sh_company" class="col-lg-2 control-label">Company</label>
                    <div class="col-lg-8">
                        <input type="text" id="sh_company" name="sh_company" class="form-control round" placeholder="Company Name" value="{{@$stakeholder->sh_company}}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="sh_designation" class="col-lg-2 control-label">Designation</label>
                    <div class="col-lg-8">
                        <input type="text" id="sh_designation" name="sh_designation" class="form-control round" placeholder="Designation" value="{{@$stakeholder->sh_designation}}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="sh_access_reason" class="col-lg-2 control-label">Access Reason</label>
                    <div class="col-lg-8">
                        <textarea id="sh_access_reason" name="sh_access_reason" class="form-control round" placeholder="Access Reason"> {{ @$stakeholder->sh_access_reason }} </textarea>
                    </div>
                </div>

                <div class="col-12 col-lg-8 mb-1">
                    <label for="name" class="mt-2">Authorized By</label>
                    <select name="sh_authorizer_id" id="sh_authorizer_id" class="form-control" required>
                        <option value=""> Select Authorizer </option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp['id'] }}" @if(@$stakeholder->sh_authorizer_id === $emp['id']) selected @endif>{{ $emp['first_name'] . " " . $emp['last_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Row for Access Start and End Fields -->
                <div class="form-group row">
                    <div class="col-lg-4">
                        <label for="sh_access_start" class="control-label">Access Start</label>
                        <input type="datetime-local" id="sh_access_start" name="sh_access_start" class="form-control round" value="{{@$stakeholder->sh_access_start}}">
                    </div>
                    <div class="col-lg-4">
                        <label for="sh_access_end" class="control-label">Access End</label>
                        <input type="datetime-local" id="sh_access_end" name="sh_access_end" class="form-control round" value="{{@$stakeholder->sh_access_end}}">
                    </div>
                </div>

            </div>



            {{-- Roles and Permissions --}}
            <div class="tab-pane" id="tab9" role="tabpanel" aria-labelledby="base-tab9">

                <div class="row form-group">

                    <div class='col-12 col-lg-4'>
                        <label for="login_access">User Login Access Status</label>
                        <select id="login_access" name="login_access" class="form-control">
                            @php
                                $statuses = ['Deactivated' => 0, 'Active' => 1];
                            @endphp

                            @foreach($statuses as $st => $val)
                                <option value="{{ $val }}" >{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class='form-group'>
                    <label for="role" class="ml-2">Role <input type="checkbox" name="check_all" id="check_all" class="check_all"></label>
                    <div class='col-lg-10'>
                        <select class="form-control" name="role" id="{{ $general['create'] == 1 ? "new_emp_role" : "emp_role" }}">
                            @foreach($roles as $role)
                                <option value="{{$role['id']}}" @if(@$stakeholder->role['id']==$role['id']) selected @endif>
                                    {{$role['name']}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="permission_result">   
                        @if(@$stakeholder->role['id'])
                            <div class="row p-1">
                                @foreach($permissions_all as $row)
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="permission[]" value="{{ $row['id'] }}" class="permission_check" @if(in_array_r($row['id'], @$permissions)) checked="checked" @endif>
                                            <label>{{ trans('permissions.' . $row['name']) }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

@section('after-scripts')
{{ Html::script('focus/js/jquery.password-validation.js') }}
{{ Html::script('focus/js/select2.min.js') }}
<script>

    $('#login_access').change(function () {

        if (parseInt($(this).val()) === 1) {
            let confirmed = confirm(
                'Please note that this will allow the Stakeholder to access your company information on the ERP. \nAre you sure the employee needs the access?'
            );

            if (!confirmed) $(this).val(0);
        }
    });



        // check all roles
    $('#check_all').change(function() {
        if ($(this).prop('checked')) {
            $('.permission').each(function(i) {
                $(this).prop('checked', true);
            })
        } else {
            $('.permission').each(function(i) {
                $(this).prop('checked', false);
            })
        }
    });
    
    $('.select2-input').select2({allowClear: true});

    $(document).ready(function () {
        $("#u_password").passwordValidation({
            minLength: 6,
            minUpperCase: 1,
            minLowerCase: 1,
            minDigits: 1,
            minSpecial: 1,
            maxRepeats: 5,
            maxConsecutive: 3,
            noUpper: false,
            noLower: false,
            noDigit: false,
            noSpecial: false,
            failRepeats: true,
            failConsecutive: true,
            confirmField: undefined
        }, function (element, valid, match, failedCases) {
            $("#errors").html("<pre>" + failedCases.join("\n") + "</pre>");
            if (valid) $(element).css("border", "2px solid green");
            if (!valid) {
                $(element).css("border", "2px solid red");
                $("#e_btn").prop('disabled', true);
            }
            if (valid && match) {
                $("#u_password").css("border", "2px solid green");
                $("#e_btn").prop('disabled', false);
            }
            if (!valid || !match) $("#u_password").css("border", "2px solid red");
        });


        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 300,
        });

    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    $('#job_title_id').on('change', function() {

        var selectedOption = $(this).find('option:selected');
        $('#job_grade').val(selectedOption.attr('data-attribute'));
    });

    $(document.body).on('change', '#emp_role', function (e) {
        var pid = $(this).val();

        console.table({'rid': pid, 'create': '{{$general['create']}}'});

        $.ajax({
            url: '{{ route("biller.hrms.related_permission") }}',
            type: 'post',
            dataType: 'html',
            data: {'rid': pid, 'create': '{{$general['create']}}'},
            success: function (data) {
                $('#permission_result').html(data)
            }
        });
    });

    $(document.body).on('change', '#new_emp_role', function (e) {
        var pid = $(this).val();
        fresh_permission(pid);
    });

    function fresh_permission(pid = 1) {

        console.table({'rid': pid, 'create': '{{$general['create']}}'});

        $.ajax({
            url: '{{ route("biller.hrms.role_permission") }}',
            type: 'post',
            dataType: 'html',
            data: {'rid': pid, 'create': '{{$general['create']}}'},
            success: function (data) {

                console.log(data);

                $('#permission_result').html(data);


            },
            error: function (xhr, status, error) {
                console.error("An error occurred:");
                console.error("Status: " + status); // Log the status (e.g., "error" or "timeout")
                console.error("Error: " + error);   // Log the error message
                console.error("Response: " + xhr.responseText); // Log the server's response (if any)

                // Optionally show an error message to the user
                $('#permission_result').html('<p style="color: red;">An error occurred while loading permissions. Please try again.</p>');
            }
        });
    }

    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});
    // $('#dob').datepicker('setDate', new Date());

    $(document).ready(function () {

        const stakeholder = @json(@$stakeholder);
        if (stakeholder) {

            console.log("CURRENT STAKEHOLDER:")
            console.log(stakeholder);

            // refresh roles
            if (stakeholder.role) {
                fresh_permission(stakeholder.role.id);
                $('#emp_role').change();
            } else fresh_permission(2);
        }
    });

</script>
@endsection
