@extends ('core.layouts.app')

@section ('title', 'Create Mpesa Config')

@section('page-header')
    <h1>
        <small>Create Mpesa Config</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Mpesa Config</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.mpesa_configs.partials.mpesa_configs-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    {{ Form::open(['route' => 'biller.mpesa_configs.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.mpesa_configs.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.mpesa_configs.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!-- form-group -->

                                    {{ Form::close() }}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('after-scripts')
    <script>
        $(document).ready(function() {
            function toggleFields(type) {
                // hide all first
                $("[id^='field-']").hide();

                switch (type) {
                    case 'b2c':
                        $("#field-cert_file, #field-shortcode, #field-initiator-name, #field-initiator-password, #field-result-url, #field-timeout-url, #field-cert")
                            .show();
                        break;

                    case 'c2b_paybill':
                        $("#field-shortcode, #field-head-office-shortcode, #field-validation-url, #field-confirmation-url")
                            .show();
                        break;

                    case 'c2b_store':
                        $("#field-shortcode, #field-validation-url, #field-confirmation-url").show();
                        break;

                    case 'stk_push':
                        $("#field-shortcode, #field-passkey, #field-account-reference, #field-callback-url").show();
                        break;
                }
            }

            // init on load
            toggleFields($("#mpesa-type").val());

            // change event
            $("#mpesa-type").on("change", function() {
                toggleFields($(this).val());
            });
        });
    </script>

@endsection
