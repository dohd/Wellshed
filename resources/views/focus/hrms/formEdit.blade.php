<div class="card-content">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                   aria-selected="true">Bio Data</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab"
                   aria-selected="false">Residence</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3" role="tab"
                   aria-selected="false">Next Of Kin</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4" role="tab"
                   aria-selected="false">Education</a>
            </li>
          
            <li class="nav-item">
                <a class="nav-link" id="base-tab5" data-toggle="tab" aria-controls="tab5" href="#tab5" role="tab"
                   aria-selected="false">{{trans('hrms.hrms')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab6" data-toggle="tab" aria-controls="tab6" href="#tab6" role="tab"
                   aria-selected="false">Bank</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab7" data-toggle="tab" aria-controls="tab7" href="#tab7" role="tab"
                   aria-selected="false">Statutory</a>
            </li>
        
            <li class="nav-item">
                <a class="nav-link" id="base-tab8" data-toggle="tab" aria-controls="tab8" href="#tab8" role="tab"
                   aria-selected="false">Health</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9" role="tab"
                   aria-selected="false">Roles & Permissions</a>
            </li>


        </ul>
      
        <div class="tab-content px-1 pt-1">

              <!---Biodata tab-->
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                <div class='form-group'>
                    {{ Form::label( 'employee_no', 'Employee Number',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('employee_no', null, ['class' => 'form-control round', 'placeholder' => 'Enter Number'.'*','required'=>'required', 'readonly' => 'readonly']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'first_name', trans('hrms.first_name'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('first_name', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.first_name').'*','required'=>'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'last_name', 'Other Names',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('last_name', null, ['class' => 'form-control round', 'placeholder' => 'Other Names']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'id_number', 'ID Number',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('id_number', null, ['class' => 'form-control round', 'placeholder' => 'ID Number *','required'=>'required' ]) }}
                    </div>
                </div>
                <div class='form-group'>
                    <label for="dob" class="col-lg-2 control-label">Date Of Birth</label>
                    <div class="col-lg-10">
                        <input type="text" name="dob" id="dob"
                               @if(!empty($hrms)) value="{{$hrms->dob}}" @endif
                               class="form-control datepicker round"
                               placeholder="Date Of Birth *" required
                               data-date-start-date="1999-03-01">
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'primary_contact', 'Personal Contact (MPESA)',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('primary_contact', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.phone').'*','required'=>'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'alternative_contact', 'Alternative Contact', ['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('alternative_contact', null, ['class' => 'form-control round', 'placeholder' => 'Alternative Contact *','required'=>'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'secondary_contact', 'Office Contact',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('secondary_contact', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.phone')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'gender', 'Gender*',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {!! Form::select('gender', ['Male'=>'Male','Female'=>'Female'], null, [
                            'placeholder' => '-- Select Gender --',
                            'class' => ' form-control round',
                            'id' => 'gender',
                            'required' => 'required',
                        ]) !!}

                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'marital_status', 'Marital Status*',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>

                        {!! Form::select('marital_status', ['Married'=>'Married','Single'=>'Single'], null, [
                            'placeholder' => '-- Select Department --',
                            'class' => ' form-control round',
                            'id' => 'marital_status',
                            'required' => 'required',
                        ]) !!}
                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( 'email', 'Employee Personal Email (for payslip remittance)',['class' => 'col-lg-6 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('email', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.email').'*','required'=>'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'personal_email', ' Official/Business Email',['class' => 'col-lg-6 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('personal_email', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.email').'*']) }}
                    </div>
                </div>
                <div class='form-group hide_picture'>
                    {{ Form::label( 'id_front', 'ID Front',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-6'>
                        {!! Form::file('id_front', array('class'=>'input' )) !!}  @if(@$hrms->id)
                            <small>{{trans('hrms.blank_field')}}</small>
                        @endif
                    </div>
                </div>
                <div class='form-group hide_picture'>
                    {{ Form::label( 'id_back', 'ID Back',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-6'>
                        {!! Form::file('id_back', array('class'=>'input' )) !!}  @if(@$hrms->id)
                            <small>{{trans('hrms.blank_field')}}</small>
                        @endif
                    </div>
                </div>

                <div class='form-group hide_picture'>
                    {{ Form::label( 'picture', 'Profile Picture',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-6'>
                        {!! Form::file('picture', array('class'=>'input' )) !!}  @if(@$hrms->id)
                            <small>{{trans('hrms.blank_field')}}</small>
                        @endif
                    </div>
                </div>
                <div class='form-group hide_picture'>
                    {{ Form::label( 'signature', trans('hrms.signature'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-6'>
                        {!! Form::file('signature', array('class'=>'input' )) !!}  @if(@$hrms->id)
                            <small>{{trans('hrms.blank_field')}}</small>
                        @endif
                    </div>
                </div>
             

            </div>


              <!---Residence tab-->
            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
               

                <div class='form-group'>
                    {{ Form::label( 'home_county', 'Home County',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('home_county', null, ['class' => 'form-control round', 'placeholder' => 'County*','required'=>'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'home_address', 'Home Address',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('home_address', null, ['class' => 'form-control round', 'placeholder' => "Home Address*","required"=>"required"]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'residential_address', 'Current Residential Address',['class' => 'col-lg-6 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('residential_address', null, ['class' => 'form-control round', 'placeholder' => 'Residential Address*']) }}
                    </div>
                </div>
        

            </div>


               <!---Next Of Kin-->
            <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                <div class='form-group'>
                    {{ Form::label( 'kin_name', 'Name',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('kin_name', null, ['class' => 'form-control round', 'placeholder' => 'Name']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'kin_contact', 'Contact',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('kin_contact', null, ['class' => 'form-control round', 'placeholder' => 'Phone']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'kin_relationship', 'Relationship',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {!! Form::select('kin_relationship', ['Wife'=>'Wife','Husband'=>'Husband','Father'=>'Father','Mother'=>'Mother','Brother'=>'Brother','Sister'=>'Sister', 'Son' => 'Son', 'Daughter' => 'Daughter'], null, [
                            'placeholder' => '-- Select Relationship --',
                            'class' => ' form-control round',
                            'id' => 'kin_relationship',
                            'required' => 'required',
                        ]) !!}
                    </div>
                </div>

               
               
            </div>


            <!---Education tab-->
            <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                <div class='form-group'>
                    {{ Form::label( 'highest_education_level', 'Highest Level Of Education*',['class' => 'col-lg-6 control-label']) }}
                    <div class='col-lg-10'>
                        {!! Form::select('highest_education_level', ['KCPE'=>'KCPE','KCSE'=>'KCSE','KCSE','Certificate'=>'Certificate','Diploma'=>'Diploma','Degree'=>'Degree','Masters'=>'Masters','PHD'=>'PHD'], null, [
                            'placeholder' => '-- Select Highest Level Of Education --',
                            'class' => ' form-control round',
                            'id' => 'highest_education_level',
                            'required' => 'required',
                        ]) !!}

                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( 'institution', 'Institution',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('institution', null, ['class' => 'form-control round', 'placeholder' => 'Institution*','required']) }}
                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( 'course', 'Course ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('course', null, ['class' => 'form-control round', 'placeholder' => 'Course*','required']) }}
                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( 'award', 'Award ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('award', null, ['class' => 'form-control round', 'placeholder' => 'Award*','required']) }}
                    </div>
                </div>


                <div class='form-group'>
                    {{ Form::label( '1st_award_year', 'First Award Year ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::number('1st_award_year', null, ['class' => 'form-control round', 'placeholder' => 'Award*','required', 'step' => '1']) }}
                    </div>
                </div>

                <hr>

                <div class='form-group'>
                    {{ Form::label( '2nd_education_level', 'Second Second Highest Level Of Education*',['class' => 'col-lg-6 control-label']) }}
                    <div class='col-lg-10'>
                        {!! Form::select('2nd_education_level', ['KCPE'=>'KCPE','KCSE'=>'KCSE','KCSE','Certificate'=>'Certificate','Diploma'=>'Diploma','Degree'=>'Degree','Masters'=>'Masters','PHD'=>'PHD'], null, [
                            'placeholder' => '-- Select Second Highest Level Of Education --',
                            'class' => ' form-control round',
                            'id' => '2nd_education_level',
                        ]) !!}

                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( '2nd_institution', 'Second Institution',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('2nd_institution', null, ['class' => 'form-control round', 'placeholder' => 'Institution']) }}
                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( '2nd_course', 'Second Course ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('2nd_course', null, ['class' => 'form-control round', 'placeholder' => 'Course']) }}
                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( '2nd_award', 'Second Award ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('2nd_award', null, ['class' => 'form-control round', 'placeholder' => 'Award']) }}
                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( '2nd_award_year', 'Second Award Year ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::number('2nd_award_year', null, ['class' => 'form-control round', 'placeholder' => 'Award', 'step' => '1']) }}
                    </div>
                </div>

                <hr>



                <div class='form-group hide_picture'>
                    {{ Form::label( 'cv', 'Curriculum Vitae',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-6'>
                        {!! Form::file('cv', array('class'=>'input' )) !!}  @if(@$hrms->id)
                            <small>{{trans('hrms.blank_field')}}</small>
                        @endif
                    </div>
                </div>




            </div>


            <!---HRM tab-->
            <div class="tab-pane" id="tab5" role="tabpanel" aria-labelledby="base-tab5">
                 <div class='form-group'>

                     <div class='col-lg-10'>
                         <label for="classlist">Select Classlist</label>
                         <select name="classlist_id" class="form-control select2-input" id="classlist_id" data-placeholder="Select Classlist">
                             <option value="">Select Classlist</option>
                             @foreach ($classLists as $item)
                                 <option value="{{ $item->id }}" {{ @$hrms->classlist_id === $item->id? 'selected' : '' }}>
                                     {{ $item->name }} {{ $item->parent_class? '('. $item->parent_class->name .')' : '' }}
                                 </option>
                             @endforeach
                         </select>
                     </div>


                    <div class='col-lg-10 mt-1'>
                        {{ Form::label( 'department', trans('departments.department'),['class' => 'col-lg-2 control-label']) }}
                        {!! Form::select('department_id', @$departments, null, [
                            'placeholder' => '-- Select Department --',
                            'class' => ' form-control round',
                            'id' => 'department',
//                            'required' => 'required',
                        ]) !!}
                    </div>
                </div>


                <div class="form-group ml-1">
                    <label for="job_title_id">Job Title</label>
                    <select name="job_title_id" id="job_title_id" class="form-control col-lg-10">
                        <option value="">Select Job Title</option>
                        @foreach ($jobTitles as $title)
                            <option value="{{ $title->id }}" {{ $title->id === $hrms->job_title_id ? 'selected' : '' }} data-attribute="{{ $title->job_grade }}"
>
                                {{ $title->name }}
                            </option>
                        @endforeach
                    </select>
                </div>


                <div class="form-group ml-1">
                    <label for="employee_id">Job Grade</label>
                    <input type="text" name="job_grade" id="job_grade" disabled class="form-control col-lg-10">
                </div>


                <div class='form-group'>
                    <div class='col-lg-10'>
                        <label for="employment_type">Employment Type</label>
                        <select id="employment_type" name="employment_type" class="form-control">
                            @php
                                $types = ['Permanent', 'Casual', 'Fixed term', 'Part time', 'Fixed Date', 'Probation'];
                            @endphp

                            <option value=""> Select an Employment Type </option>
                            @foreach($types as $t)
                                <option value="{{ $t }}" @if(@$hrms->employment_type === $t) selected @endif>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class='form-group'>
                    {{ Form::label( 'employement_date', 'Date Of Employement',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('employement_date', null, ['class' => 'form-control datepicker box-size','id'=>'employement_date', 'placeholder' => 'Employement Date']) }}
                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( 'employment_end_date', 'End of Employment Date',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('employment_end_date', null, ['class' => 'form-control datepicker box-size','id'=>'employment_end_date', 'placeholder' => 'End of Employment Date']) }}
                    </div>
                </div>


                <div class='form-group'>
                    {{ Form::label( 'previous_employer', 'Previous Employer',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('previous_employer', null, ['class' => 'form-control box-size', 'placeholder' => 'Previous Employer']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'workshift', "Select WorkShift",['class' => 'col control-label']) }}
                    <div class='col-lg-10'>
                        <select name="workshift_id" id="workshift_id" class="form-control">
                            <option value="">--select workshift--</option>
                            @foreach ($workshifts as $shift)
                                <option value="{{$shift->id}}" {{$shift->id == @$hrms->meta['workshift_id'] ? 'selected' : ''}}>{{$shift->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- <div class='form-group'>
                    {{ Form::label( 'entry_time', trans('hrms.entry_time'),['class' => 'col control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::time('entry_time', @$hrms->meta['entry_time'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.entry_time')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'exit_time', trans('hrms.exit_time'),['class' => 'col control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::time('exit_time', @$hrms->meta['exit_time'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.exit_time')]) }}
                    </div>
                </div> --}}
                <div class='form-group'>
                    {{ Form::label( 'sales_commission', trans('hrms.sales_commission'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('commission', @$hrms->meta['commission'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.sales_commission')]) }}
                    </div>
                </div>

                <div class="mt-1 col-lg-10">
                    <label for="employee_notes" class="caption" style="display: inline-block;">Notes</label>
                    <textarea name="employee_notes" id="employee_notes"  class="form-control tinyinput" placeholder="Employee Notes">
                        {{ @$hrms->employee_notes }}
                    </textarea>
                </div>

            </div>


            <!---Bank Details-->
            <div class="tab-pane" id="tab6" role="tabpanel" aria-labelledby="base-tab6">
           

                <div class='form-group'>
                    {{ Form::label( 'bank_name', 'Bank Name',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('bank_name', null, ['class' => 'form-control round', 'placeholder' => 'Bank Name*','required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'account_name', 'A/C Name ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('account_name', null, ['class' => 'form-control round', 'placeholder' => 'A/C Name*','required']) }}
                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( 'account_number', 'A/C Number ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('account_number', null, ['class' => 'form-control round', 'placeholder' => 'A/C Number*','required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'branch', 'Bank Branch ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('branch', null, ['class' => 'form-control round', 'placeholder' => 'Branch*','required']) }}
                    </div>
                </div>
            
           
            
           
            </div>


            <div class="tab-pane" id="tab7" role="tabpanel" aria-labelledby="base-tab7">
                <div class='form-group'>
                    {{ Form::label( 'salary', 'KRA PIN',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('kra_pin', null, ['class' => 'form-control round', 'placeholder' => 'KRA PIN*','required'=>'required']) }}
                    </div>
                </div>

                <div class='form-group'>
                    {{ Form::label( 'nssf', 'NSSF NUMBER ',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('nssf', null, ['class' => 'form-control round', 'placeholder' => 'NSSF NUMBER*','required'=>'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'nhif', 'SHIF',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('nhif', null, ['class' => 'form-control round', 'placeholder' => 'SHIF NUMBER*','required'=>'required']) }}
                    </div>
                </div>
           
            
           
            </div>


            <div class="tab-pane" id="tab8" role="tabpanel" aria-labelledby="base-tab8">
                <div class='form-group'>
                    {{ Form::label( 'blood_group', 'Blood Group',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {!! Form::select('blood_group', ['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','O+'=>'O+','O-'=>'O-','AB+'=>'AB+','AB-'=>'AB-'], null, [
                            'placeholder' => '-- Select Blood Group --',
                            'class' => ' form-control round',
                            'id' => 'blood_group',
                            'required' => 'required',
                        ]) !!}

                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'is_cronical', 'Do You Suffer From Any Chronical Desease?',['class' => 'col-lg-6 control-label']) }}
                    <div class='col-lg-10'>
                        {!! Form::select('is_cronical', ['0'=>'No','1'=>'Yes'], null, [
                            'placeholder' => '-- Select Blood Group --',
                            'class' => ' form-control round',
                            'id' => 'is_cronical',
                            'required' => 'required',
                        ]) !!}

                   
                    </div>
                </div>


                <div class='form-group'>
                    {{ Form::label( 'specify', 'If Yes Specify',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('specify', null, ['class' => 'form-control box-size', 'placeholder' => 'Specify']) }}
                    </div>
                </div>
             
              
            
            </div>


            {{-- Roles and Permissions --}}
            <div class="tab-pane" id="tab9" role="tabpanel" aria-labelledby="base-tab9">


                <div class="form-group">

                    <div class="row">
                        <div class='col-12 col-lg-4'>
                            <label for="status">User Visibility Status</label>
                            <select id="status" name="status" class="form-control">
                                @php
                                    $statuses = ['Active' => 1, 'Deactivated' => 0];
                                @endphp

                                @foreach($statuses as $st => $val)
                                    <option value="{{ $val }}" @if(@$hrms->status === $val) selected @endif>{{ $st }}</option>
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
                                    <option value="{{ $val }}" @if(@$hrms->login_access === $val) selected @endif>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
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
    
    

    $(document).ready(function () {

        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 300,
        });

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

        let selectedJobTitle = $('#job_title_id').find('option:selected');
        if (selectedJobTitle) $('#job_grade').val(selectedJobTitle.attr('data-attribute'));
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    $('#job_title_id').on('change', function() {

        let selectedOption = $(this).find('option:selected');
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
    // $('#employement_date').datepicker('setDate', new Date());
    const hrm = @json(@$hrms);
    if (hrm) {
        {{--const dob = @json(dateFormat(@$hrm->dob));--}}
        {{--const employement_date = @json(dateFormat(@$hrm->employement_date));--}}
        {{--if (dob) $('#dob').val(dob);--}}
        {{--if (employement_date) $('#employement_date').val(employement_date);--}}

        // refresh roles
        if (hrm.role) {
            fresh_permission(hrm.role.id);
            $('#emp_role').change();
        } else fresh_permission(2);
    }
</script>
@endsection
