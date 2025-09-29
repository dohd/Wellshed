<div class='form-group'>
    {{ Form::label( 'name', 'Enter Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'Enter Name', 'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'phone', 'Enter Phone',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('phone', null, ['class' => 'form-control round', 'placeholder' => 'Enter Phone', 'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'email', 'Enter Email (Optional)',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('email', null, ['class' => 'form-control round', 'placeholder' => 'Enter Email (Optional)']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'id_number', 'Enter Id Number',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('id_number', null, ['class' => 'form-control round', 'placeholder' => 'Enter Id Number', 'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'address', 'Enter Address',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('address', null, ['class' => 'form-control round', 'placeholder' => 'Enter Address', 'required']) }}
    </div>
</div>

@section("after-scripts")
    <script type="text/javascript">
       
    </script>
@endsection
