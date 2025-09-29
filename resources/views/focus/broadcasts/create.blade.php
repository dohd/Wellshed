@extends ('core.layouts.app')
@section ('title', 'Create | Whatsapp Broadcast')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Create Broadcast</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            @include('focus.broadcasts.partials.broadcasts-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <form id="whatsappBroadcastForm" action="{{ route('api.whatsapp_broadcast.store') }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="userSegment">User Segment</label>
                                                <select name="user_segment" id="userSegment" class="custom-select">
                                                    <option value="local">Subscribed Users (All users who have talked to bot)</option>
                                                    <option value="import">Upload User File </option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="phoneNumbers">User Contacts<span class="text-danger">*</span></label>
                                                <div class="form-check form-check-inline float-right">
                                                    <input class="form-check-input" type="checkbox" id="checkAllUsers">
                                                    <label class="form-check-label" for="selectAll">All</label>
                                                </div>  
                                                <select name="phone_no[]" id="phoneNo" class="custom-select" data-placeholder="Search Contact" multiple>
                                                    @foreach ($contacts as $phone => $username)
                                                        <option value="{{ $phone }}">{{ $username }} - {{ $phone }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group d-none">
                                                <label for="uploadFile">
                                                    <span class="mr-2">Upload File (CSV)<span class="text-danger">*</span></span>
                                                    <a href="{{ route('biller.omniconvo.media_block_template') }}" target="_blank" style="text-decoration: underline;"><b>Download CSV Template</b></a>
                                                </label>
                                                {{ Form::file('csv_file', ['id' => 'uploadFile', 'class' => 'form-control', 'accept' => '.csv']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="messageTemplate">
                                                    Message Template <span class="text-danger">*</span>
                                                    <div style="width: 1rem; height: 1rem;" class="spinner-border unit-loading" role="status"><span class="sr-only">Loading...</span></div>
                                                </label>
                                                <select name="template_id" id="msgTemplate" class="custom-select" data-placeholder="Search Template" required>
                                                    <option value=""></option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="edit-form-btn row">
                                        {{ link_to_route('biller.whatsapp_broadcast.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-1 mr-1']) }}
                                        {{ Form::button('Post Message', ['id' => 'submitBtn', 'disabled' => false,  'type' => 'submit', 'class' => 'btn btn-primary btn-md col-2 mr-2']) }}                                        
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: { 
            headers: { 
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Authorization': "Bearer {{ config('agentToken') }}",
            },
            contentType: false,
            processData: false,
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        spinner: '<span><i class="fa fa-spinner spinner"></i></span>',
    };


    // form submit success callback
    function trigger(res) {
        $('#submitBtn').html('Post Message').attr('disabled', false);
    }
    // form submit error callback
    function triggerError(res) {
        $('#submitBtn').html('Post Message').attr('disabled', false);
    }

    const Form = {
        init() {
            $.ajaxSetup(config.ajax);
            $('#msgTemplate').select2({allowClear: true});
            $('#phoneNo').select2();
            Form.fetchTemplates();

            $('#submitBtn').click(Form.onFormSubmit);
            $('#userSegment').change(Form.onChangeUserSegment);
            $('#checkAllUsers').change(Form.onChangeCheckAllUsers);
        },

        onFormSubmit(e) {
            e.preventDefault();
            $(this).html('Post Message ' + config.spinner).attr('disabled', true);

            const form = $('#whatsappBroadcastForm');
            const formData = new FormData(form[0]);
            addObject({'form': formData,'url': form.attr('action')}, true);
        },

        onChangeCheckAllUsers() {
            if ($(this).prop('checked')) {
                const phoneNos = [];
                $('#phoneNo option').each(function() {
                    phoneNos.push($(this).val());
                });
                $('#phoneNo').val(phoneNos).change();
            } else {
                $('#phoneNo').val('').change();
            }
        },

        onChangeUserSegment() {
            if ($(this).val() == 'local') {
                $('#uploadFile').val('');
                $('#uploadFile').parents('.form-group').addClass('d-none');
                $('#phoneNo').parents('.form-group').removeClass('d-none');
            } else {
                $('#phoneNo').val('').change();
                $('#phoneNo').parents('.form-group').addClass('d-none');
                $('#uploadFile').parents('.form-group').removeClass('d-none');
            }
        },

        fetchTemplates() {
            $.get("{{ route('api.media_blocks.index') }}")
            .then(({templates}) => {
                $('.unit-loading').addClass('d-none');
                if (templates && templates.length) {
                    templates.forEach(v => {
                        const templateId = 'WHATSAPP_TEMPLATE_ID-' + v.id;
                        $('#msgTemplate').append(`<option value="${templateId}">${v.name}</option>`);
                    });
                }
            })
            .fail((xhr,status,err) => {
                $('.unit-loading').addClass('d-none');
            })
        },
    };

    $(Form.init)
</script>
@endsection
