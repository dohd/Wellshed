<div class="row form-group">
    <div class="col-md-6">
        {{ Form::label('name', 'Customer Name',['class' => 'control-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Customer Name', 'required'=>'required']) }}
    </div>

    <div class="col-md-6">
        {{ Form::label('phone', 'Phone', ['class' => 'control-label']) }}
        {{ Form::text('phone', null, ['class' => 'form-control box-size', 'placeholder' => 'e.g +254700100100', 'required'=>'required']) }}
    </div>
</div>

<div class="row form-group">
    <div class="col-md-6">
        {{ Form::label('email', 'Email',['class' => 'control-label']) }}
        {{ Form::text('email', null, ['class' => 'form-control box-size', 'placeholder' => 'e.g johndoe@mail.com']) }}
    </div>

    <div class="col-md-6">
        {{ Form::label('address', 'Address', ['class' => 'control-label']) }}
        {{ Form::text('address', null, ['class' => 'form-control box-size', 'placeholder' => 'PO Box 00100, Nairobi']) }}
    </div>
</div>
