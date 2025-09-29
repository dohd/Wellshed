@extends ('core.layouts.app')

@section ('title', 'Update Password' . ' | ' . trans('labels.backend.hrms.edit'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('menus.backend.access.users.change-password') }}</h4>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div id="errors" class="well"></div>
                    <div class="card-content p-2">
                        {{ Form::open(['route' => ['biller.change_profile_password'], 'class' => 'form-horizontal', 'method' => 'post']) }}
                            <div class="form-group">
                                {{ Form::label('old_password', trans('validation.attributes.frontend.register-user.old_password'), ['class' => 'col-md-4 control-label']) }}
                                <div class="col-md-6">
                                    {{ Form::input('password', 'old_password', null, ['class' => 'form-control', 'id'=>'old_password','placeholder' => trans('validation.attributes.frontend.register-user.old_password')]) }}
                                </div>
                            </div>
                            <div class="form-group">
                                {{ Form::label('password', trans('validation.attributes.frontend.register-user.new_password'), ['class' => 'col-md-4 control-label']) }}
                                <div class="col-md-6">
                                    {{ Form::input('password', 'password', null, ['class' => 'form-control',  'id'=>'new_password','placeholder' => trans('validation.attributes.frontend.register-user.new_password')]) }}
                                </div>
                            </div>
                            <div class="form-group">
                                {{ Form::label('password_confirmation', trans('validation.attributes.frontend.register-user.new_password_confirmation'), ['class' => 'col-md-4 control-label']) }}
                                <div class="col-md-6">
                                    {{ Form::input('password', 'password_confirmation', null, ['class' => 'form-control', 'id'=>'password_confirmation', 'placeholder' => trans('validation.attributes.frontend.register-user.new_password_confirmation')]) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-12">
                                    {{ link_to_route('biller.profile', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 mr-1']) }}
                                    {{ Form::submit(trans('labels.general.buttons.update'), ['class' => 'btn btn-primary col-1', 'id' => 'change-password']) }}
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
    {{ Html::script('focus/js/jquery.password-validation.js') }}
    <script>
        $(document).ready(function () {
            $("#new_password").passwordValidation({
                minLength: 6,
                minUpperCase: 1,
                minLowerCase: 1,
                minDigits: 1,
                minSpecial: 1,
                maxRepeats: 5,
                maxConsecutive: 3,
                noUpper: false,
                noLower: false,
                noDigit: false,
                noSpecial: false,
                failRepeats: true,
                failConsecutive: true,
                confirmField: "#password_confirmation"
            }, function (element, valid, match, failedCases) {
                $("#errors").html("<pre>" + failedCases.join("\n") + "</pre>");
                if (valid) {
                    $(element).css("border", "2px solid green");
                } else {
                    $(element).css("border", "2px solid red");
                    $("#e_btn").prop('disabled', true);
                }
                
                if (valid && match) {
                    $("#password_confirmation").css("border", "2px solid green");
                    $("#e_btn").prop('disabled', false);
                } else {
                    $("#password_confirmation").css("border", "2px solid red");
                }
            });
        });
    </script>
@endsection