@extends ('core.layouts.app')
@section ('title', 'Whatsapp Broadcast / Single Message')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Whatsapp Broadcast / Single Message</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            @include('focus.whatsapp.partials.message-header-buttons')
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
                                <form id="whatsappBroadcastForm" action="{{ route('biller.whatsapp.messages.store') }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="Single-message" checked>
                                                    <label class="form-check-label" for="inlineRadio1">Single-message</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="Broadcast">
                                                    <label class="form-check-label" for="inlineRadio2">Broadcast</label>
                                                </div>                                                
                                            </div>
                                            <div class="form-group">
                                                <input type="hidden" name="template_name" id="templateName">
                                                <label for="template">Message Template <span class="text-danger">*</span></label>
                                                <select name="template_id" id="msgTemplate" class="custom-select" data-placeholder="Search Template" required>
                                                    <option value=""></option>
                                                </select>
                                            </div>  
                                            <div class="form-group">
                                                <label for="phoneNo">Phone Number <span class="text-danger">*</span></label>
                                                <input type="text" name="phone_no" class="form-control" placeholder="+254 700100100" id="phoneNo">
                                            </div>

                                            {{-- <div class="form-group">
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
                                                <select name="phone_no_arr[]" id="phoneNo" class="custom-select" data-placeholder="Search Contact" multiple>
                                                    @foreach ($contacts=[] as $phone => $username)
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
                                            </div> --}}
                                        </div>

                                        <div class="col-md-6">
                                            <h6>Variable Values</h6>
                                            <div class="w-50">
                                                <table id="varsTbl" class="table table-borderless d-none">
                                                    <thead>
                                                        <tr>
                                                            <th width="20%">Variable</th>
                                                            <th>Value</th>                                                        
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="item-row">
                                                            <td></td>
                                                            <td>
                                                                <input type="text" name="variable[]" class="form-control vars">
                                                                <input type="hidden" name="variable_type[]" value="body" class="vars-type">
                                                            </td>
                                                        </tr>
                                                    </tbody>                                                
                                                </table>                                                
                                            </div>

                                            <h6>Preview</h6>
                                            <style>
                                              .pre-text {
                                                white-space: pre-wrap;
                                                word-wrap: break-word;
                                                overflow-wrap: break-word;
                                                max-width: 100%; /* Optional: ensures it respects parent width */
                                              }
                                            </style>
                                            <pre class="pre-text p-1"></pre>
                                        </div>   
                                    </div>                                
        
                                    <div class="edit-form-btn row">
                                        {{ link_to_route('biller.whatsapp.messages.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-1 mr-1']) }}
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
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        spinner: '<span><i class="fa fa-spinner spinner"></i></span>',
        ajax: { 
            headers: { 
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Authorization': "Bearer {{ $business->whatsapp_access_token }}",
            },
            contentType: false,
            processData: false,
        },
        templateSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ $business->graph_api_url }}/{{ $business->whatsapp_business_account_id }}/message_templates",
                dataType: 'json',
                type: 'GET',
                delay: 250,
                cache: true,
                minimumInputLength: 2,
                {{-- data: ({term}) => ({search: term}), --}}
                processResults: ({data}) => {
                    return { 
                        results: data.map(v => ({
                            text: v.name, 
                            id: v.id,
                            origin: v,
                        })) 
                    }
                },
            }
        }
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
        rowHTML: $('#varsTbl tbody tr:first').clone(),
        preHTML: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('#msgTemplate').select2(config.templateSelect2);

            $('#msgTemplate').change(Form.onChangeTemplate);
            $('#userSegment').change(Form.onChangeUserSegment);
            $('#checkAllUsers').change(Form.onChangeCheckAllUsers);
            $('#varsTbl').on('keyup', '.vars', Form.onKeyupVariable);

            $('#submitBtn').click(Form.onFormSubmit);
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

        onChangeTemplate() {
            $('#templateName').val('');
            const data = $(this).select2('data')[0];
            if (data.text) $('#templateName').val(data.text);
            const origin = data.origin;

            // Render preview data
            $('.pre-text').html('');
            $('input[name="button_type[]"]').remove();
            (origin?.components || []).forEach(v => {
                if (['HEADER', 'BODY', 'FOOTER'].includes(v.type)) {
                    $('.pre-text').append(`<p>${v.text}</p>`)
                }
                if (v.type == 'BUTTONS') {
                    v.buttons.forEach(v1 => {
                        $('.pre-text').after(`<input type="hidden" name="button_type[]" value="${v1.type.toLowerCase()}">`);
                    });
                }
            });
            Form.preHTML = $('.pre-text').html();

            // Render input variables
            const previewText = $('.pre-text').text();
            const pattern = /\{\{\d+\}\}/;
            const hasMatch = pattern.test(previewText);
            if (hasMatch) $('#varsTbl').removeClass('d-none');
            else $('#varsTbl').addClass('d-none');
            $('#varsTbl tbody').html('');
            const matches = previewText.match(/\{\{\d+\}\}/g);
            (matches || []).forEach((v,i) => {
                const tr = Form.rowHTML.clone()
                tr.find('td:first').html(v);
                $('#varsTbl tbody').append(tr);
            }); 
        },

        onKeyupVariable() {
            const row = $(this).parents('tr:first');
            const varIndex = row.find('td:first').html();
            let content = Form.preHTML;
            $('#varsTbl tbody tr').each(function() {
                const varIndex = $(this).find('td:first').html();
                const variable = $(this).find('.vars').val() || '';
                if (varIndex && content.includes(varIndex)) {
                    // Escapes all special regex characters
                    const str = varIndex.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const target = new RegExp(str, "gi");
                    content = content.replace(target, variable);
                }
            });
            $('.pre-text').html(content);
        },  
    };

    $(Form.init)
</script>
@endsection
