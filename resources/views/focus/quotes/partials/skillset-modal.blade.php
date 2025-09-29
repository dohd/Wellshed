<div class="modal fade" id="skillModal" tabindex="-1" role="dialog" aria-labelledby="skillModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Skilled Labour</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="skillTbl" class="table tfr my_stripe_single">
                        <thead>
                            <tr class="bg-gradient-directional-blue white" style="">
                                <th width="20%" class="text-center">Skill Type</th>
                                <th width="15%" class="text-center">Charge</th>
                                <th width="15%" class="text-center">Working Hrs</th>
                                <th width="15%" class="text-center">No. Technicians</th> 
                                <th width="15%" class="text-center">Amount</th>
                                <th width="10%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- skill row template -->
                            <tr class="d-none">
                                <td>
                                    <select class="form-control type" name="skill[]" id="skill-0">
                                        <option value="">-- Select Skill --</option>                        
                                        @foreach (['casual', 'contract', 'attachee', 'outsourced'] as $val)
                                            <option value="{{ $val }}">
                                                {{ ucfirst($val) }}
                                            </option>    
                                        @endforeach 
                                    </select>
                                </td>
                                <td><input type="number" class="form-control chrg" name="charge[]" id="charge-0" readonly></td>
                                <td><input type="number" class="form-control hrs" name="hours[]" id="hours-0"></td>               
                                <td><input type="number" class="form-control tech" name="no_technician[]" id="notech-0" ></td>
                                <td class="text-center"><span class="amount">0</span></td>
                                <td class="text-center"><button type="button" class="btn btn-danger btn-sm rem"><i class="fa fa-trash"></i></button></td>
                                <input type="hidden" name="skill_id[]" id="skillid-0">
                            </tr>  
                            
                            <!-- edit quote or pi skill row -->
                            @isset($quote)
                                @foreach ($quote->skill_items as $k => $item)
                                <tr>
                                    <td>
                                        <select class="form-control type" name="skill[]" id="skill-{{$k}}" required>
                                            <option value="">-- Select Skill --</option> 
                                            @foreach (['casual', 'contract', 'attachee', 'outsourced'] as $val)
                                                <option value="{{ $val }}" {{ $val == $item->skill ? 'selected' : ''}}>
                                                    {{ ucfirst($val) }}
                                                </option>    
                                            @endforeach                  
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control chrg" name="charge[]" value="{{ $item->charge }}" id="charge-{{$k}}" required readonly></td>
                                    <td><input type="number" class="form-control hrs" name="hours[]" value="{{ $item->hours }}" id="hours-{{$k}}" required></td>               
                                    <td><input type="number" class="form-control tech" name="no_technician[]" value="{{ $item->no_technician }}" id="notech-{{$k}}" required></td>
                                    <td class="text-center"><span class="amount">0</span></td>
                                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm rem"><i class="fa fa-trash"></i></button></td>
                                    <input type="hidden" name="skill_id[]" value="{{ $item->id }}" id="skillid-{{$k}}">
                                </tr> 
                                @endforeach
                            @endisset
                        </tbody>
                    </table>  
                </div>              
                <div class="row">
                    <div class="col-2 ml-auto">
                        <label for="total">Total (Ksh.)</label>
                        <input type="text" class="form-control" id="skill_total" readonly>
                    </div>
                </div>         
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="addRow"><i class="fa fa-plus-square"></i> Add Row</button>
            </div>
        </div>
    </div>
</div>