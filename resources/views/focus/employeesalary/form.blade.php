<div class="row">
    <!-- Quote -->
    <div class="col-6">
        <h3 class="form-group">Set Salary</h3>
        <div class="form-group row">
            <div class="col-12">
                <label for="ticket">Employee<span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="form-control" name="user_id" id="user_id" required>   
                        @foreach ($users as $user)
                        <option 
                        value="{{ $user->id }}"
                        {{ $user->id == @$employeesalary->user_id ? 'selected' : '' }}
                        >
                        {{ $user->first_name.' '.$user->last_name }}

                    </option>
                        
                       @endforeach
                                                                                                                 
                    </select>
                   
                </div>
            </div>
        </div>
    

        <div class="form-group row">
       
            <div class="col-6">
                <label for="salary" >Basic Salary <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('salary', null, ['class' => 'form-control round', 'placeholder' => 'Basic Pay', 'id'=>'attention', 'required']) }}
                </div>
            </div>
            <div class="col-6">
                <label for="nssf_id">NSS Rate <span class="text-danger">*</span></label>
                {!! Form::select('nssf_id', $nssfrates, null, [
                    'placeholder' => '-- Select NSSF Rates --',
                    'class' => 'custom-select',
                    'id' => 'employement_type',
                    'required' => 'required',
                ]) !!}

                            
            </div>
        
        </div>

             
    </div>

    <!-- Properties -->
    <div class="col-6">
        <h3 class="form-group">Details</h3>
        <div class="form-group row">
            <div class="col-6">
                <label for="effective_date">Payroll Start Date</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('effective_date', null, ['class' => 'form-control round datepicker', 'id' => 'date']) }}
                </div>
            </div>   
            <div class="col-6">
                <label for="terms">Employement Type <span class="text-danger">*</span></label>
                {!! Form::select('employement_type', ['Probation'=>'Probation','Contract'=>'Contract','Permanent'=>'Permanent'], null, [
                    'placeholder' => '-- Select Employement Type --',
                    'class' => 'custom-select',
                    'id' => 'employement_type',
                    'required' => 'required',
                ]) !!}

                            
            </div>
            

           
         
                                          
        </div>

        <div class="form-group row">
            <div class="col-6">
                <label for="contact_duration" >Contract Reviewed After(In Months)</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('contact_duration', null, ['class' => 'form-control round', 'placeholder' => 'Contract  Reviewed After', 'id' => 'contract_renewal_duration', 'required']) }}
                </div>
            </div>
            <div class="col-6">                
                <label for="note">Note</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => 'Note']) }}
                </div>
            </div>
         
                                                                                
        </div>
 
    </div>                        
</div>

<!-- employeesalary item table -->
@include('focus.employeesalary.partials.employeesalary-items-table')

<!-- footer -->
<div class="form-group row">
    <div class="col-9">
        
    </div>
    <div class="col-3">

        {{ Form::hidden('taxable_allowance', null, ['id' => 'total_taxable_allowance']) }}
        {{ Form::hidden('untaxable_allowance', null, ['id' => 'total_untaxable_allowance']) }}
        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }}
    </div>
</div>
