<div class="form-group row">
    <div class="col-5">
        <label for="contract">Contract</label>
        <select name="contract_id" id="contract" class="form-control" data-placeholder="Choose Contract" required>
            <option value="">-- Select Contract --</option>
            @foreach ($contracts as $row)
                <option value="{{ $row->id }}">
                    {{ $row->tid }} - {{ $row->title }} - {{ $row->customer? $row->customer->company : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-3">
        <label for="schedule">Task Schedule</label>
        <select name="schedule_id" id="schedule" class="form-control" data-placeholder="Choose Task Schedule" required>
            <option value="">-- Select Schedule --</option>
        </select>
    </div>
    <div class="col-2">
        <label for="actual_date">Actual Start Date</label>
        {{ Form::text('actual_startdate', null, ['class' => 'form-control datepicker', 'id' => 'actual_startdate']) }}
    </div>
    <div class="col-2">
        <label for="actual_date">Actual End Date</label>
        {{ Form::text('actual_enddate', null, ['class' => 'form-control datepicker', 'id' => 'actual_enddate']) }}
    </div>
</div>
<legend>Equipments</legend>
<div class="table-responsive mb-1">
    <table id="equipmentTbl" class="table">
        <thead>
            <tr>
                <th>Equipment No</th>
                <th>Serial No</th>
                <th>Type</th>
                <th>Branch</th>
                <th>Location</th>
                <th>Rate</th>
                <th>                    
                    Action
                    <div class="d-inline ml-2">
                        <input type="checkbox" class="form-check-input" id="selectAll">
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="d-none">
                <td>#tid</td>
                <td>#equip_serial</td>
                <td>#make_type</td>
                <td>#branch</td>
                <td>#location</td>
                <td>#service_rate</td>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input select">
                    </div>
                </td>
                <input type="hidden" name="equipment_id[]" value="#id" class="equipId" disabled>
                <input type="hidden" name="service_rate[]" value="#service_rate" class="rate" disabled>
            </tr>
        </tbody>
    </table>
</div>
<div class="form-group row">
    <div class="col-2 ml-auto">
        <label for="rate">Total Rate (Ksh.)</label>
        <input type="text" name="" class="form-control" id="totalRate" disabled>
    </div>
</div>
<div class="form-group row">
    {{ Form::hidden('equipment_ids', null, ['id' => 'equipment_ids']) }}
    <div class="col-2 ml-auto">
        {{ Form::submit('Load Equipments', ['class' => 'btn btn-primary btn-lg']) }}
    </div>
</div>