<!DOCTYPE html>
@extends ('core.layouts.app')

@include('tinymce.scripts')
@section('title', 'Create Customer Enrollment')

@section('page-header')
    <h1>
        <small>Create Customer Enrollment</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Customer Enrollment</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.customer_enrollments.partials.customer_enrollments-header-buttons')
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
                                    {{ Form::open(['route' => 'biller.customer_enrollments.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include('focus.customer_enrollments.form')
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.customer_enrollments.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
@include('tinymce.scripts')
@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        $(document).ready(function () {
            function toggleClientFields() {
                if ($("input[name='client_status']:checked").val() === "customer") {
                    // Enable customer select
                    $("#customer").prop("disabled", false);

                    // Disable new client fields
                    $(".new-client-field").prop("disabled", true).val('');
                } else {
                    // Disable customer select
                    $("#customer").prop("disabled", true).val('');

                    // Enable new client fields
                    $(".new-client-field").prop("disabled", false);
                }
            }

            // Run on load
            toggleClientFields();

            // Run on change
            $(".client-type").on("change", toggleClientFields);
        });

        $(document).ready(function() {
            let value = {{ @$value ?? 0 }}; // value from server

            if (value == 1) {
                $('#my_checkbox').prop('checked', true);
            } else {
                $('#my_checkbox').prop('checked', false);
            }

            // Ensure hidden input is updated when checkbox is toggled
            $('#my_checkbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $(this).val(1);
                } else {
                    $(this).val(0);
                }
            });
        });

         $(document).ready(function () {
                tinymce.init({
                selector: '.tinyinput',
                menubar: 'file edit view format table tools',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                height: 300,
            });});
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };
        const Index = {
            init() {
                
                $.ajaxSetup(config.ajax);
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#customer').select2({
                    allowClear: true
                });
                $('#productCategories').select2({
                    allowClear: true
                });
                $('#products').select2({
                    allowClear: true
                });
                $('#redeemable_code').change(this.redeemableCodeChange);
            },
            redeemableCodeChange() {
                const redeemable_code = $(this).val();
                const $messageSpan = $('#redeemable_message');

                $.ajax({
                    url: "{{ route('biller.customer_enrollments.get_redeemable_codes') }}",
                    method: "POST",
                    data: {
                        redeemable_code
                    },
                    success: function(data) {
                        // Always hide wrappers before doing anything
                        $('#categorySelectWrapper').hide();
                        $('#productSelectWrapper').hide();
                        $('#descriptionPromoWrapper').hide();

                        if (data.success && data.promo_code) {
                            const promo = data.promo_code;
                            const promoType = promo.promo_type;
                            const promo_code_id = promo.id;

                            // set hidden field
                            $('#promo_type').val(promoType);
                            $('#promo_code_id').val(promo_code_id);

                            if (promoType === 'product_categories') {
                                $('#categorySelectWrapper').show();
                                $('#productCategories').empty();
                                if (promo.product_categories) {
                                    promo.product_categories.forEach(cat => {
                                        $('#productCategories').append(
                                            `<option value="${cat.id}" selected>${cat.title}</option>`
                                        );
                                    });
                                }
                            } else if (promoType === 'specific_products') {
                                $('#productSelectWrapper').show();
                                $('#products').empty();
                                if (promo.products) {
                                    promo.products.forEach(p => {
                                        $('#products').append(
                                            `<option value="${p.id}" selected>${p.name}</option>`
                                        );
                                    });
                                }
                            } else if (promoType === 'description_promo') {
                                $('#descriptionPromoWrapper').show();
                               if (tinymce.get('descriptionPromoText')) {
                                    tinymce.get('descriptionPromoText').setContent(promo.description_promo || '');
                                } else {
                                    $('#descriptionPromoText').val(promo.description_promo || '');
                                }

                            }

                            $messageSpan.text("Promo code applied successfully!")
                                .css("color", "green").show();

                        } else {
                            // Invalid code: hide all fields, clear values
                            $messageSpan.text(data.message || "Invalid redeemable code.")
                                .css("color", "red").show();
                            $('#redeemable_code').val('');
                            $('#promo_type').val('');
                            $('#productCategories').empty();
                            $('#products').empty();
                            $('#descriptionPromoText').val('');
                        }
                    },
                    error: function() {
                        // On error: also hide & clear everything
                        $('#categorySelectWrapper').hide();
                        $('#productSelectWrapper').hide();
                        $('#descriptionPromoWrapper').hide();
                        $('#productCategories').empty();
                        $('#products').empty();
                        $('#descriptionPromoText').val('');
                        $('#promo_type').val('');
                        $('#redeemable_code').val('');

                        $messageSpan.text("Something went wrong. Please try again.")
                            .css("color", "red").show();
                    }
                });
            }



        };
        $(() => Index.init());
    </script>
@endsection
