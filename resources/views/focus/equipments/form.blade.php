<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label('system_id', 'System No',['class' => 'col-12 control-label']) }}
        {{ Form::text('tid', @$equipment->tid ?: @$tid+1, ['class' => 'col form-control', 'readonly']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('customer_id', 'Customer',['class' => 'col-12 control-label']) }}
        <select id="person" name="customer_id" class="form-control round required select-box" data-placeholder="Choose Customer">
            @isset ($equipment)
                <option value="{{ $equipment->customer_id }}" selected>
                    {{ $equipment->customer? $equipment->customer->name . ' - ' . $equipment->customer->company : ''}}
                </option>
            @endisset
        </select>
    </div>
    <div class='col-md-4'>
        {{ Form::label('branch_id', 'Branch',['class' => 'col-12 control-label']) }}
        <select id="branch" name="branch_id" class="form-control select-box" data-placeholder="Branch">
            @isset ($equipment)
                <option value="{{ $equipment->branch_id }}" selected>
                    {{ $equipment->branch? $equipment->branch->name : '' }}
                </option>
            @endisset
        </select>
    </div>
</div>

<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label('equip_serial', 'Equipment Serial No.',['class' => 'col-12 control-label']) }}
        {{ Form::text('equip_serial', null, ['class' => 'col form-control ', 'placeholder' => 'Serial No']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('machine_gas', 'Gas / Fuel Type',['class' => 'col-12 control-label']) }}
        {{ Form::text('machine_gas', null, ['class' => 'col form-control ', 'placeholder' => 'Gas / Fuel', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('equipment_category_id', 'Equipment Category',['class' => 'col-12 control-label']) }}
        <select name="equipment_category_id" class="custom-select" id="category_id">
            <option value="">-- Select Category --</option>
            @foreach ($categories as $row)
                <option value="{{ $row->id }}" {{ @$equipment->equipment_category_id == $row->id ? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label('make', 'Make / Type',['class' => 'col-12 control-label']) }}
        {{ Form::text('make_type', null, ['class' => 'col form-control ', 'placeholder' => 'Make / Type', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'model', 'Model / Model No',['class' => 'col-12 control-label']) }}
        {{ Form::text('model', null, ['class' => 'col form-control ', 'placeholder' => 'Model Name / Number']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('capacity', 'Capacity / Size',['class' => 'col-12 control-label']) }}
        {{ Form::text('capacity', null, ['class' => 'col form-control ', 'placeholder' => 'Capacity', 'required']) }}
    </div>
</div>

<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label('location', 'Equipment Location',['class' => 'col-12 control-label']) }}
        {{ Form::text('location', null, ['class' => 'col form-control ', 'placeholder' => 'Location', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('building', 'Equipment Building',['class' => 'col-12 control-label']) }}
        {{ Form::text('building', null, ['class' => 'col form-control ', 'placeholder' => 'Building', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('floor', 'Building Floor',['class' => 'col-12 control-label']) }}
        {{ Form::text('floor', null, ['class' => 'col form-control ', 'placeholder' => 'Building Floor']) }}
    </div>
</div>

<div class="form-group row">
    <div class='col-md-4'>
        {{ Form::label('unique_id', 'Client Tag No',['class' => 'col-12 control-label']) }}
        {{ Form::text('unique_id', null, ['class' => 'col form-control ', 'placeholder' => 'Client Tag Number']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('service_rate', 'Service Rate (VAT Exc)',['class' => 'col-12 control-label']) }}
        {{ Form::text('service_rate', null, ['class' => 'col form-control ', 'placeholder' => 'Rate Exc VAT', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('install_date', 'Installaton Date',['class' => 'col-12 control-label']) }}
        {{ Form::text('install_date', null, ['class' => 'col form-control datepicker', 'id' => 'install_date']) }}
    </div>
</div>

<div class="form-group row">
    <div class='col-md-4'>
        {{ Form::label('end_of_warranty', 'End of Warranty',['class' => 'col-12 control-label']) }}
        {{ Form::text('end_of_warranty', null, ['class' => 'col form-control datepicker', 'id' => 'end_of_warranty']) }}
    </div>
</div>

<div class="form-group row">
    <div class='col-md-4'>
        {{ Form::label('note', 'Remark',['class' => 'col-12 control-label']) }}
        {{ Form::text('note', null, ['class' => 'col form-control ', 'placeholder' => 'Remark']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('pm_duration', 'PM Duration(mins)',['class' => 'col-12 control-label']) }}
        {{ Form::number('pm_duration', '30', ['class' => 'col form-control ']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('status', 'Equipment Status',['class' => 'col-12 control-label']) }}
        <select name="status" class="form-control" id="status-0">
            <option value="">-- Select Status --</option>
            @foreach (['working', 'faulty', 'cannibalised', 'decommissioned', 'under warranty'] as $val)
                @if(empty($equipment))
                    <option value="{{ $val }}" >{{ ucfirst($val) }}</option>
                @else
                    <option value="{{ $val }}" @if(@$equipment->status === $val) selected @endif>{{ ucfirst($val) }}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>
