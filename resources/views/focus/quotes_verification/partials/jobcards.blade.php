<fieldset class="border p-1 mb-3 jc-ctn d-none">
    <legend class="w-auto float-none h5">Job-cards / Delivery Notes</legend>
    <div class="table-responsive" style="max-height: 80vh">
        <table id="jobcardTbl" class="pb-2 tfr text-center">
            <thead class="bg-gradient-directional-blue white pb-1">
                <tr>
                    <th width="8%">Type</th>
                    <th width="12%">Ref No</th>                                                    
                    <th width="12%">Date</th>
                    <th width="15%">Technician</th>
                    <th width="10%">Job Hrs</th>
                    <th width="15%">Equipment</th>
                    <th width="12%">Location</th>
                    <th width="16%">Fault</th>
                    <th class="pr-1">Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- row template -->
                <tr class="d-none">
                    <td>
                        <select class="custom-select type" name="type[]" id="type">
                            <option value="1" selected>Jobcard</option>
                            <option value="2">DNote</option> 
                        </select>
                    </td>
                    <td><input type="text" class="form-control ref" name="reference[]" id="reference" required></td>
                    <td><input type="text" class="form-control datepicker date" name="date[]" id="date" required></td>
                    <td><input type="text" class="form-control tech" name="technician[]" id="technician" required></td>
                    <td><span class="jobhrs" id="jobhrs">_</span></td>
                    <td><textarea class="form-control equip" name="equipment[]" id="equip"></textarea>
                    <td><input type="text location" class="form-control" name="location[]" id="location"></td>
                    <td>
                        <select class="custom-select fault" name="fault[]" id="fault">
                            @php
                                $details = ['none' => 'None', 'faulty_compressor' => 'Faulty Compressor', 'faulty_pcb' => 'Faulty PCB', 'leakage_arrest' => 'Leakage Arrest', 'electrical_fault' => 'Electrical Fault', 'drainage' => 'Drainage', 'other' => 'Other'];
                            @endphp
                            @foreach ($details as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                Action
                            </button>
                            <div class="dropdown-menu" aria-labelledby="">
                                <a href="javascript:" class="dropdown-item add_labour" data-toggle="modal" data-target="#attachLabourModal"><i class="fa fa-plus"></i> Job Hrs</a>
                                <a href="javascript:" class="dropdown-item text-danger del" type="button"><i class="fa fa-trash"></i> Remove</a>
                            </div>
                        </div>
                    </td>
                    <input type="hidden" name="jcitem_id[]" value="0" id="jcitemid">
                    <input type="hidden" name="equipment_id[]" value="0" id="equipmentid">
                    <!-- modal inputs -->
                    <input type="hidden" name="job_date[]" id="job_date" class="job_date">
                    <input type="hidden" name="job_type[]" id="job_type" class="job_type">
                    <input type="hidden" name="job_employee[]" id="job_employee" class="job_employee">
                    <input type="hidden" name="job_ref_type[]" id="job_ref_type" class="job_ref_type">
                    <input type="hidden" name="job_jobcard_no[]" id="job_jobcard_no" class="job_jobcard_no">
                    <input type="hidden" name="job_hrs[]" id="job_hrs" class="job_hrs">
                    <input type="hidden" name="job_is_payable[]" id="job_is_payable" class="job_is_payable">
                    <input type="hidden" name="job_note[]" id="job_note" class="job_note">
                    <!-- end modal inputs -->
                </tr>
                <!-- End Row template -->
            </tbody>
        </table>
    </div>
    <br>
    <a href="javascript:" class="btn btn-success btn-sm" aria-label="Left Align" id="add-jobcard">
        <i class="fa fa-plus-square"></i>  Jobcard / DNote
    </a>                                            
</fieldset>
