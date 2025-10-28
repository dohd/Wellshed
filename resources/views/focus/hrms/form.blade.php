<div class="card-content">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                   aria-selected="true">User Details</a>
            </li>            
            <li class="nav-item">
                <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9" role="tab"
                   aria-selected="false">Roles & Permissions</a>
            </li>
        </ul>
      
        <div class="tab-content px-1 pt-1">
              <!---Biodata tab-->
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                <div class="row">
                    <div class='form-group col-md-6'>
                        {{ Form::label( 'first_name', trans('hrms.first_name'),['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('first_name', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.first_name').'*','required'=>'required']) }}
                        </div>
                    </div>
                    <div class='form-group col-md-6'>
                        {{ Form::label( 'last_name', 'Other Names',['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('last_name', null, ['class' => 'form-control round', 'placeholder' => 'Other Names']) }}
                        </div>
                    </div>                    
                </div>
                
                <div class="row">                    
                    <div class='form-group col-md-4'>
                        {{ Form::label( 'secondary_contact', 'Office Contact',['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('secondary_contact', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.phone')]) }}
                        </div>
                    </div>
                    <div class='form-group col-md-4'>
                        {{ Form::label( 'primary_contact', 'Personal Contact (MPESA)', ['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('primary_contact', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.phone').'*','required'=>'required']) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class='form-group col-md-6'>
                        {{ Form::label( 'email', ' Official/Business Email ',['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('email', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.email').'*','required'=>'required']) }}
                        </div>
                    </div>
                    <div class='form-group col-md-6'>
                        {{ Form::label( 'personal_email', 'Personal Email',['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('personal_email', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.email').'*']) }}
                        </div>
                    </div>                    
                </div>

                <div class="row">
                    <div class='form-group hide_picture col-md-3'>
                        {{ Form::label( 'id_front', 'ID Front',['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-6'>
                            {!! Form::file('id_front', array('class'=>'input' )) !!}  @if(@$hrms->id)
                                <small>{{trans('hrms.blank_field')}}</small>
                            @endif
                        </div>
                    </div>
                    <div class='form-group hide_picture col-md-3'>
                        {{ Form::label( 'id_back', 'ID Back',['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-6'>
                            {!! Form::file('id_back', array('class'=>'input' )) !!}  @if(@$hrms->id)
                                <small>{{trans('hrms.blank_field')}}</small>
                            @endif
                        </div>
                    </div>                    
                </div>

                <div class="row">
                    <div class='form-group hide_picture col-md-3'>
                        {{ Form::label( 'picture', 'Profile Picture',['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-6'>
                            {!! Form::file('picture', array('class'=>'input' )) !!}  @if(@$hrms->id)
                                <small>{{trans('hrms.blank_field')}}</small>
                            @endif
                        </div>
                    </div>
                    <div class='form-group hide_picture col-md-3'>
                        {{ Form::label( 'signature', trans('hrms.signature'),['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-6'>
                            {!! Form::file('signature', array('class'=>'input' )) !!}  @if(@$hrms->id)
                                <small>{{trans('hrms.blank_field')}}</small>
                            @endif
                        </div>
                    </div>                    
                </div>
            </div>
            
            {{-- Roles and Permissions --}}
            <div class="tab-pane" id="tab9" role="tabpanel" aria-labelledby="base-tab9">
                <div class="row form-group">
                    <div class='col-12 col-lg-4'>
                        <label for="status">User Visibility Status</label>
                        <select id="status" name="status" class="form-control">
                            @php
                                $statuses = ['Active' => 1, 'Deactivated' => 0];
                            @endphp
                            @foreach($statuses as $st => $val)
                                <option value="{{ $val }}" >{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
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
                                <option value="{{$role['id']}}" @if(@$hrms->role['id']==$role['id']) selected @endif>
                                    {{$role['name']}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="permission_result">   
                        @if(@$hrms->role['id'])
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
                'Please note that this will allow the employee to access your company information on the ERP. \nAre you sure the employee needs the access?'
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
        $.ajax({
            url: '{{ route("biller.hrms.role_permission") }}',
            type: 'post',
            dataType: 'html',
            data: {'rid': pid, 'create': '{{$general['create']}}'},
            success: function (data) {
                $('#permission_result').html(data)
            }
        });
    }
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});
    // $('#dob').datepicker('setDate', new Date());
    $('#employement_date').datepicker('setDate', new Date());
    const hrm = @json(@$hrms);
    if (hrm) {
        const dob = @json(dateFormat(@$hrm->dob));
        const employement_date = @json(dateFormat(@$hrm->employement_date));
        if (dob) $('#dob').val(dob);
        if (employement_date) $('#employement_date').val(employement_date);
        // refresh roles
        if (hrm.role) {
            fresh_permission(hrm.role.id);
            $('#emp_role').change();
        } else fresh_permission(2);
    }
</script>
@endsection
