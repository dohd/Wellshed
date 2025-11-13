<div class="card-content">
    <div class="card-body">
            <!-- Tab Menu -->
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                       aria-selected="true">Account Details</a>
                </li>            
                <li class="nav-item">
                    <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9" role="tab"
                       aria-selected="false">Delivery Zone</a>
                </li>
            </ul>

            <!-- Tab Content -->
            @include('focus.customers.tabs.form_tabs')

            @if(session('flash_user_error'))
                <div class="alert bg-warning alert-dismissible m-1" role="alert">
                    <button type="button" class="close" data-dismiss="alert"
                            aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    {{session('flash_user_error')}}
                </div>
            @endif
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const targetZones = @json($targetzones);
    const config = {
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
    };


    const Form = {
        init() {
            $('#segment, #targetzoneItem').select2({allowClear: true});

            $('#segment').change(Form.segmentChange).change();
            $('#targetzoneItem').change(Form.targetZoneItemChange).change();
            $('#password').on('keyup', Form.passwordKeyUp);
            $('#confirm_password').on('keyup', Form.confirmPasswordKeyUp);

            // Edit Mode
            const data = @json(@$customer);
            if (data?.id) {
                {{-- console.log(data); --}}
                if (data.has_onetime_fee) {
                    $('#colorCheck1').prop('checked', true);
                    $('#colorCheck3').prop('checked', false);
                } else {
                    $('#colorCheck1').prop('checked', false);
                    $('#colorCheck3').prop('checked', true);
                }
                
                $('#subpackage').prop('disabled', true);
                $('#company').val(data.company);
            }
        },

        passwordKeyUp() {
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
        },

        confirmPasswordKeyUp() {
            if ($(this).val() != $('#password').val()) $(this).next().removeClass('d-none');
            else $(this).next().addClass('d-none');
        },

        segmentChange() {
            $('#firstname, #lastname, #company').val('');
            if ($(this).val() === 'office') {
                $('#company').closest('.form-group').removeClass('d-none');
                $('#company').attr('required', true);
                $('#firstname, #lastname').each(function() {
                    $(this).closest('.form-group').addClass('d-none');
                    $(this).attr('required', false);
                });
            } else {
                $('#company').closest('.form-group').addClass('d-none');
                $('#company').attr('required', false);
                $('#firstname, #lastname').each(function() {
                    $(this).closest('.form-group').removeClass('d-none');
                    $(this).attr('required', true);
                });
            }
        },

        targetZoneItemChange() {
            const targetzoneId = $(this).find(':selected').data('target_zone_id');
            $('#targetzone').val(targetzoneId);
        },

        targetZoneChange() {
            return;
            $('#targetzoneItem').html('').closest('.form-group').addClass('d-none');
            if ($(this).val()) {
                const zone = targetZones.filter(zone => zone.id == $(this).val())[0] || null;
                if (zone?.id) {
                    $('#targetzoneItem').closest('.form-group').removeClass('d-none');
                    zone.items.forEach(v => {
                        $('#targetzoneItem').append(`<option value="${v.id}">${v.sub_zone_name}</option>`);
                    });
                }               
            }
        },    
    };

    $(Form.init);
</script>
@endsection
