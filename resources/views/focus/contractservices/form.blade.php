<div class="form-group row">
    <div class="col-4">
        <label for="client">Customer</label>
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Client" required>
            @isset ($contractservice)
                <option value="{{ $contractservice->customer_id }}">
                    {{ $contractservice->customer? $contractservice->customer->company : 'None' }}
                </option>
            @endisset
        </select>
    </div>
    
    <div class="col-5">
        <label for="contract">Contract</label>
        <select name="contract_id" id="contract" class="form-control" data-placeholder="Choose Contract" required>
            @isset ($contractservice)
                <option value="{{ $contractservice->contract_id }}">
                    {{ $contractservice->contract? $contractservice->contract->title : 'None' }}
                </option>
            @endisset
        </select>
    </div>
    <div class="col-3">
        <label for="schedule">Schedule (Budgeted Maintenance Proforma)</label>
        <select name="schedule_id" id="schedule" class="form-control" data-placeholder="Choose Schedule" required>
            @isset ($contractservice)
                <option value="{{ $contractservice->schedule_id }}">
                    {{ $contractservice->task_schedule? $contractservice->task_schedule->title : 'None' }}
                </option>
            @endisset
        </select>
    </div>
</div>
<div class="form-group row">
    <div class="col-2">
        <label for="date">Jobcard Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div>
    <div class="col-2">
        <label for="jobcard_no">Jobcard No</label>
        {{ Form::text('jobcard_no', null, ['class' => 'form-control', 'id' => 'jobcard_no']) }}
    </div>
    <div class="col-2">
        <label for="technician">Technician</label>
        {{ Form::text('technician', null, ['class' => 'form-control', 'id' => 'technician']) }}
    </div>
    <div class="col-6">
        <label for="remark">General Remark</label>
        {{ Form::text('remark', null, ['class' => 'form-control', 'id' => 'remark']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-2">
        <label for="branch">Branch</label>
        <select name="branch_id" id="branch" class="form-control" data-placeholder="Choose Branch" required>
            @isset ($contractservice)
                <option value="{{ $contractservice->branch_id }}">
                    {{ $contractservice->branch? $contractservice->branch->name : 'None' }}
                </option>
            @endisset
        </select>
    </div>
</div>
<div class="table-reponsive">
    <table id="equipTbl" class="table text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Equipment No</th>
                <th>Description</th>                                           
                <th>Location</th>
                <th>Rate</th>
                <th>Status</th>
                <th width="9%">Bill</th>
                <th width="30%">Note</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>        
            <!-- row template -->
            <tr>                                                    
                <td><span id="tid-0"></span></td>                                                               
                <td>
                    <textarea class="form-control" name="description" id="descr-0" cols="20" required></textarea>
                </td> 
                <td><span id="location-0"></span></td>     
                <td><span id="rate-0" class="rate"></span></td>	
                <td>
                    <select name="status[]" class="custom-select" id="status-0">
                        @foreach (['working', 'faulty', 'cannibalised', 'decommissioned', 'under warranty'] as $val)
                            <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                        @endforeach
                    </select>                                                   
                </td>
                <td>
                    <select name="is_bill[]" class="custom-select bill" id="bill-0">
                        @foreach (['No', 'Yes'] as $k => $val)
                            <option value="{{ $k }}" {{ $k? 'selected' : ''}}>{{ $val }}</option>
                        @endforeach
                    </select>     
                </td>                       
                <td><input type="text" class="form-control" name="note[]" id="note-0"></td>    
                <td><a href="javascript:" class="btn btn-light del"><i class="danger fa fa-trash fa-lg"></i></a></td> 
                <input type="hidden" name="equipment_id[]" id="equipmentid-0">        
                <input type="hidden" name="item_id[]" value="0" id="itemid-0">     
            </tr>   
            <!-- edit contract service equipments -->
            @isset ($contractservice)
                @foreach ($contractservice->items as $i => $row)
                    @if ($row->equipment)
                    <tr>                                                    
                        <td><span id="tid-{{$i}}">{{ gen4tid('Eq-', @$row->equipment->tid) }}</span></td>                                                               
                        <td>
                            @php
                                $extract_keys = ['make_type', 'equip_serial', 'unique_id', 'capacity', 'machine_gas'];
                                $equip = array_intersect_key($row->equipment->toArray(), array_flip($extract_keys));
                                $description = implode('; ', array_values($equip))
                            @endphp  
                            <textarea class="form-control" name="description" id="descr-{{$i}}" cols="20" required>{{ $description }}</textarea>
                        </td> 
                        <td><span id="location-{{$i}}">{{ $row->equipment->location }}</span></td>     
                        <td><span id="rate-{{$i}}" class="rate">{{ numberFormat($row->equipment->service_rate) }}</span></td>	
                        <td>
                            <select name="status[]" class="custom-select" id="status-{{$i}}">
                                @foreach (['working', 'faulty', 'cannibalised', 'decommissioned', 'under warranty'] as $val)
                                    <option value="{{ $val }}" {{ $val == $row->status? 'selected' : ''  }}>
                                        {{ ucfirst($val) }}
                                    </option>
                                @endforeach
                            </select>                                                   
                        </td>
                        <td>
                            <select name="is_bill[]" class="custom-select bill" id="bill-{{$i}}">
                                @foreach (['No', 'Yes'] as $k => $val)
                                    <option value="{{ $k }}" {{ $k == $row->is_bill? 'selected' : ''}}>{{ $val }}</option>
                                @endforeach
                            </select>     
                        </td>                       
                        <td><input type="text" class="form-control" name="note[]" value="{{ $row->note }}" id="note-{{$i}}"></td>    
                        <td><a href="javascript:" class="btn btn-light del"><i class="danger fa fa-trash fa-lg"></i></a></td> 
                        <input type="hidden" name="equipment_id[]" value="{{ $row->equipment_id }}" id="equipmentid-{{$i}}">  
                        <input type="hidden" name="item_id[]" value="{{ $row->id }}" id="itemid-{{$i}}">         
                    </tr>  
                    @endif 
                @endforeach
            @endisset
        </tbody>
    </table>
</div>
<a href="javascript:" class="btn btn-success" aria-label="Left Align" id="add_equip">
    <i class="fa fa-plus-square"></i> Add Equipment
</a>
<div class="row">
    <div class="col-2 ml-auto">
        <label for="total_rate">Total Service Amount</label>
        {{ Form::text('rate_ttl', null, ['class' => 'form-control', 'id' => 'rate_ttl', 'readonly']) }}
    </div>
</div>
<div class="row">
    <div class="col-2 ml-auto">
        <label for="total_bill">Total Service Bill (Ksh.)</label>
        {{ Form::text('bill_ttl', null, ['class' => 'form-control', 'id' => 'bill_ttl', 'readonly']) }}
    </div>
</div>
<div class="form-group row mt-1">
    <div class="col-12">
        {{ Form::submit(@$contractservice? 'Update' : 'Generate', ['class' => 'btn btn-primary btn-lg float-right']) }}
    </div>
</div>