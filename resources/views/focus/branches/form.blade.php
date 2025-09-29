<div class="form-group">
    {{ Form::label( 'rel_id', 'Customer',['class' => 'col-lg-2 control-label']) }}
    <div class='col'>
        <select class="form-control col-lg-10" name="customer_id" id="customer_id" required>
            <option value="">-- Select Customer --</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ isset($branches) && $customer->id == $branches->customer_id ? 'selected' : '' }}>
                    {{ $customer->company }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="row">
    <div class='form-group col-4'>
        {{ Form::label('code', 'Branch Code', ['class' => 'control-label ml-2']) }}
        <div>
            {{ Form::text('branch_code', null, ['class' => 'form-control box-size ml-2', 'placeholder' => 'Branch Code']) }}
        </div>
    </div>
    <div class='form-group col-6'>
        {{ Form::label( 'name', 'Branch Name',['class' => 'control-label']) }}
        <div>
            {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Branch Name*', 'required']) }}
        </div>
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'location', 'Physical Location',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('location', null, ['class' => 'form-control box-size', 'placeholder' => 'Physical Address*', 'required']) }}
    </div>
</div>

<div class='form-group'>
    {{ Form::label( 'contact_name', 'Contact Person',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('contact_name', null, ['class' => 'form-control box-size', 'placeholder' => 'Contact Person']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'contact_phone', 'Contact Person Cell',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('contact_phone', null, ['class' => 'form-control box-size', 'placeholder' => 'Contact Person']) }}
    </div>
</div>
