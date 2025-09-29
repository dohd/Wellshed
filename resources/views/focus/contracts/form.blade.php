<legend>Contract Details</legend>
<hr>
<div class="form-group row">
    <div class="col-2">
        <label for="contract_no">Contract No</label>
        {{ Form::text('tid', @$contract? $contract->tid : $last_tid+1, ['class' => 'form-control', 'readonly']) }}
    </div>
    <div class="col-4">
        <label for="customer">Customer</label>
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose customer" required>
            @isset($contract)
                <option value="{{ $contract->customer_id }}" selected>
                    {{ $contract->customer? $contract->customer->company : '' }}
                </option>
            @endisset
        </select>
    </div>
    <div class="col-6">
        <label for="title">Title</label>
        {{ Form::text('title', null, ['class' => 'form-control', 'required']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-2">
        <label for="start_date">Start Date</label>
        {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'id' => 'start_date']) }}
    </div>
    <div class="col-2">
        <label for="start_date">End Date</label>
        {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'id' => 'end_date']) }}
    </div>
    <div class="col-2">
        <label for="amount">Amount</label>
        {{ Form::text('amount', numberFormat(@$contract->amount), ['class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div>
    <div class="col-2">
        <label for="period">Duration (Years)</label>
        {{ Form::number('period', 1, ['class' => 'form-control', 'id' => 'periodYr', 'required']) }}
    </div>
    <div class="col-2">
        <label for="period">Duration per Schedule (months)</label>
        {{ Form::number('schedule_period', 3, ['class' => 'form-control', 'id' => 'periodMn', 'required']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-6">
        <label for="description">Description</label>
        {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => '3', 'required']) }}
    </div>
</div>

<legend>Task Schedules</legend><hr>
<div class="form-group row">
    <div class="col-10">
        <div class="table-responsive">
            <table id="scheduleTbl" class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th width="15%">Start Date</th>
                        <th width="15%">End Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- schedule row template -->
                    <tr>
                        <td><input type="text" name="s_title[]" class="form-control" id="title-0" required></td>
                        <td><input type="text" name="s_start_date[]" class="form-control datepicker" id="startdate-0"></td>
                        <td><input type="text" name="s_end_date[]" class="form-control datepicker" id="enddate-0"></td>
                        <td>
                            <button type="button" class="btn btn-outline-light btn-sm mt-1 remove">
                                <i class="fa fa-trash fa-lg text-danger"></i>
                            </button>
                        </td>
                        <input type="hidden" name="s_id[]" value="0">
                    </tr>
                    <!-- edit contract task schedules -->
                    @isset($contract)
                        @foreach($contract->task_schedules as $row)
                            <tr>
                                <td><input type="text" name="s_title[]" value="{{ $row->title }}" class="form-control" id="title-0" required></td>
                                <td><input type="text" name="s_start_date[]" value="{{ dateFormat($row->start_date) }}" class="form-control datepicker" id="startdate-0"></td>
                                <td><input type="text" name="s_end_date[]" value="{{ dateFormat($row->end_date) }}" class="form-control datepicker" id="enddate-0"></td>
                                <td>
                                    <button type="button" class="btn btn-outline-light btn-sm mt-1 remove">
                                        <i class="fa fa-trash fa-lg text-danger"></i>
                                    </button>
                                </td>
                                <input type="hidden" name="s_id[]" value="{{ $row->id }}">
                            </tr>
                        @endforeach
                    @endisset
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="form-group row">
    <div class="col-12">
        <button class="btn btn-success btn-sm ml-2" type="button" id="addSchedule">
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
        </button>
    </div>    
</div>

<legend>Upload Contracts</legend><hr>
<div class="form-group row">
    <div class="col-10">
        <div class="table-responsive">
            <table id="docTbl" class="table">
                <thead>
                    <tr>
                        <th>Caption</th>
                        <th width="30%">Document</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- schedule row template -->
                    <tr>
                        <td><input type="text" name="caption[]" class="form-control" id="caption-0" required></td>
                        <td><input type="file" name="contract_doc[]" class="form-control" id="contract_doc-0"></td>
                        <input type="hidden" name="existing_contract_doc[]" value="example.png">
                        <td>
                            <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                <i class="fa fa-trash fa-lg text-danger"></i>
                            </button>
                        </td>
                        <input type="hidden" name="doc_id[]" value="0">
                    </tr>
                    <!-- edit contract task schedules -->
                    @isset($contract)
                        @if(count($contract->pm_docs) > 0)
                            @foreach($contract->pm_docs as $index => $row)
                                <tr>
                                    <td>
                                        <input type="text" name="caption[]" value="{{ $row->caption }}" class="form-control" id="caption-{{ $index }}" required>
                                    </td>
                                    <td>
                                        <input type="file" name="contract_doc[]" class="form-control" id="contract_doc-{{ $index }}">
                                        @isset($row->contract_doc)
                                            @if($row->contract_doc)
                                                <p>
                                                    <a href="{{ Storage::disk('public')->url('img/pm_documents/' . $row->contract_doc) }}" target="_blank">{{ $row->contract_doc }}</a>
                                                </p>
                                                <input type="hidden" name="existing_contract_doc[]" value="{{ $row->contract_doc }}">
                                                
                                            @endif
                                        @endisset
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                            <i class="fa fa-trash fa-lg text-danger"></i>
                                        </button>
                                    </td>
                                    <input type="hidden" name="doc_id[]" value="{{ $row->id }}">
                                </tr>
                            @endforeach
                        @endif
                    @endisset

                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="form-group row">
    <div class="col-12">
        <button class="btn btn-success btn-sm ml-2" type="button" id="addDoc">
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add Document
        </button>
    </div>    
</div>

<legend>Customer Equipments</legend><hr>
<div class="form-inline mb-2 filter-block">
    <label for="branch" class="mr-1">Filter</label>
    <div class="col-2">
        <select name="branch_id" id="branch" class="form-control" data-placeholder="Choose branch"></select>
    </div>    
</div>
<div class="table-responsive mb-1">
    <table id="equipmentTbl" class="table">
        <thead>
            <tr>
                <th>Equipment No.</th>
                <th>Serial No.</th>
                <th>Type</th>
                <th>Branch</th>
                <th>Location</th>
                <th>                    
                    Action
                    <div class="d-inline ml-2">
                        <input type="checkbox" class="form-check-input" id="selectAll">
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <!-- equipment row template -->
            <tr class="d-none">
                <td>#tid</td>
                <td>#equip_serial</td>
                <td>#make_type</td>
                <td>#branch</td>
                <td>#location</td>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input ml-1 select">
                    </div>
                </td>
                <input type="hidden" name="equipment_id[]" value="#id" class="equipId" disabled>
            </tr>
            <!-- edit equipment -->
            @isset($contract)
                @foreach ($contract->equipments as $row)
                    <tr>
                        <td>{{ gen4tid('Eq-', $row->tid) }}</td>
                        <td>{{ $row->equip_serial }}</td>
                        <td>{{ $row->make_type }}</td>
                        <td>{{ $row->branch->name }}</td>
                        <td>{{ $row->location }}</td>
                        <td>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input ml-1 select">
                            </div>
                        </td>
                        <input type="hidden" class="equipId" name="equipment_id[]" value="{{ $row->id }}" disabled>
                        <input type="hidden" class="contEquipId" name="contracteq_id[]" value="{{ $row->contract_equipment? $row->contract_equipment->id : '' }}" disabled>
                    </tr>
                @endforeach
            @endisset
        </tbody>
    </table>
</div>
<div class="form-group row">
    <div class="col-11">
        {{ Form::submit(@$contract ? 'Update' : 'Create', ['class' => 'btn btn-primary float-right btn-lg']) }}
    </div>
</div>
