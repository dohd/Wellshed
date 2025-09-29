<div class="card job-card-ctn d-none">
    <div class="card-content">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 80vh">
                <table id="jobcardsTbl" class="table pb-2 tfr text-center">
                    <thead class="bg-gradient-directional-blue white pb-1">
                        <tr class="item_header bg-gradient-directional-blue white">
                            <th>Item Type</th>
                            <th>Ref No</th>                                                    
                            <th>Date</th>
                            <th>Technician</th>
                            <th>Equipment</th>
                            <th>Location</th>
                            <th>Fault</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Jobcard/DNote Row Template -->
                        <tr>
                            <td>
                                <select class="custom-select jc_type" name="type[]">
                                    <option value="1" selected>Jobcard</option>
                                    <option value="2">DNote</option> 
                                </select>
                            </td>
                            <td><input type="text" class="form-control jc_ref" name="reference[]"></td>
                            <td><input type="text" class="form-control datepicker jc_date" name="date[]"></td>
                            <td><input type="text" class="form-control jc_tech" name="technician[]"></td>
                            <td><textarea class="form-control jc_equip" name="equipment[]" rows="1"></textarea>
                            <td><input type="text" class="form-control jc_loc" name="location[]"></td>
                            <td>
                                <select class="custom-select jc_fault" name="fault[]">
                                    <option value="none">None</option>
                                    <option value="faulty_compressor">Faulty Compressor</option>
                                    <option value="faulty_pcb">Faulty PCB</option>
                                    <option value="leakage_arrest">Leakage Arrest</option>
                                    <option value="electrical_fault">Electrical Fault</option>
                                    <option value="drainage">Drainage</option>
                                    <option value="other">Other</option>
                                </select>
                            </td>
                            <td><a href="javascript:" class="btn btn-danger btn-md remove" type="button">Remove</a></td>
                            <input type="hidden" name="equipment_id[]" class="jc_equipid">
                            <input type="hidden" name="jcitem_id[]" class="jc_itemid">
                        </tr>
                    </tbody>
                </table>
                <a href="javascript:" class="btn btn-sm btn-success" aria-label="Left Align" id="addJobcard">
                    <i class="fa fa-plus-square"></i>  Jobcard / DNote
                </a> 
            </div>
        </div>
    </div>
</div>