<div class="row">
    {{-- Supplier Info --}}
    <div class="col-6 pr-0">
        <div class="card pb-5">
            <div class="card-content pb-5">
                <div class="card-body">
                    <h6 class="mb-2">Vendor Info</h6>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                {{ Form::label('company', 'Company',['class' => 'col-12 control-label']) }}
                                <div class='col'>
                                    {{ Form::text('company', null, ['class' => 'form-control', 'placeholder' => 'Company', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="form-group">
                                {{ Form::label('name', 'Supplier Name',['class' => 'col-12 control-label']) }}
                                <div class='col'>
                                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Supplier Name', 'required' => 'required']) }}
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        {{ Form::label('address', 'Street Address',['class' => 'col-12 control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('address', null, ['class' => 'form-control', 'placeholder' => 'Street Address', 'required' => 'required']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        {{ Form::label('postbox', 'Post Box',['class' => 'col-12 control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('postbox', null, ['class' => 'form-control', 'placeholder' => 'Post Box', 'required' => 'required']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        {{ Form::label('phone', 'Phone',['class' => 'col-12 control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => 'Phone', 'required' => 'required']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        {{ Form::label('email', 'Email',['class' => 'col-12 control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Email', 'required' => 'required']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- User Info --}}
    <div class="col-6">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <h6 class="mb-2">User Info</h6>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                {{ Form::label('first_name', 'First Name',['class' => 'col-12 control-label']) }}
                                <div class='col'>
                                    {{ Form::text('first_name', @$client_vendor->user->first_name, ['class' => 'form-control', 'placeholder' => 'First Name', 'required' => 'required']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                {{ Form::label('last_name', 'Last Name',['class' => 'col-12 control-label']) }}
                                <div class='col'>
                                    {{ Form::text('last_name', @$client_vendor->user->last_name, ['class' => 'form-control', 'placeholder' => 'Last Name', 'required' => 'required']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                {{ Form::label('user_email', 'Email',['class' => 'col-12 control-label']) }}
                                <div class='col'>
                                    {{ Form::text('user_email', @$client_vendor->user->email, ['class' => 'form-control', 'placeholder' => 'Email', 'required' => 'required']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                {{ Form::label('password', 'Password',['class' => 'col-lg-2 control-label']) }}
                                <div class='col'>
                                    {{ Form::password('password', ['class' => 'form-control', 'id' => 'password']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                {{ Form::label('password_confirmation', 'Confirm Password',['class' => 'col-12 control-label']) }}
                                <div class='col'>
                                    {{ Form::password('password_confirmation', ['class' => 'form-control', 'id' => 'confirm_password']) }}
                                    <label for="password_match" class="text-danger d-none">Password does not match !</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ml-2 password-condition">
                                <h4>Password must have:</h4>
                                <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least 7 Characters</h5>
                                <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> Contain Upper and Lowercase letters</h5>
                                <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least one number</h5>
                                <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least one symbol</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section("after-scripts")
<script type="text/javascript">
    // password validation
    $('#password').on('keyup', function() {
        const div = $('.password-condition');
        const value = $(this).val();
        if (value.length >= 7) div.find('h5:first').removeClass('text-danger').addClass('text-success');
        else div.find('h5:first').removeClass('text-success').addClass('text-danger');
        if (new RegExp("[a-z][A-Z]|[A-Z][a-z]").test(value)) div.find('h5:eq(1)').removeClass('text-danger').addClass('text-success');
        else div.find('h5:eq(1)').removeClass('text-success').addClass('text-danger');
        if (new RegExp("[0-9]").test(value)) div.find('h5:eq(-2)').removeClass('text-danger').addClass('text-success');
        else div.find('h5:eq(-2)').removeClass('text-success').addClass('text-danger');
        if (new RegExp("[^A-Za-z 0-9]").test(value)) div.find('h5:last').removeClass('text-danger').addClass('text-success');
        else div.find('h5:last').removeClass('text-success').addClass('text-danger');
    });
    $('#confirm_password').on('keyup', function() {
        if ($(this).val() != $('#password').val()) $(this).next().removeClass('d-none');
        else $(this).next().addClass('d-none');
    });
</script>
@endsection
