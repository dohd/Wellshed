@extends ('core.layouts.app')
@section ('title', 'Create | Message Template')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Create Message Template</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.whatsapp.partials.template-header-buttons')
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
                                <form action="{{ route('biller.whatsapp.templates.store') }}" id="mediaBlockForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="category">Template Category</label>
                                                <select name="category" id="category" class="custom-select">
                                                    <option value="UTILITY">UTILITY</option>
                                                    <option value="MARKETING">MARKETING</option>
                                                    <option value="AUTHENTICATION">AUTHENTICATION</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="mediaType">Template Type</label>
                                                <select name="" id="mediaType" class="custom-select">
                                                    <option value="standard">Standard (Text Only)</option>
                                                </select>                                                
                                            </div>
                                            <div class="form-group">
                                                <label for="name">Template Name<span class="danger">*</span></label>
                                                <input type="text" name="name" class="form-control" placeholder="Template Name" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="bodyContent">
                                                    Message Content<span class="danger">*</span>&nbsp;
                                                    <span role="button" tabindex="0" class="badge bg-info add-var"><i class="fa fa-plus-circle" aria-hidden="true"></i> Add Variable</span>
                                                </label>
                                                <textarea name="body_content" id="bodyContent" class="form-control round tinyinput" placeholder="Message to be sent"></textarea>
                                            </div>
                                        </div>     
                                        <div class="col-md-6">
                                            <h6>Example Values</h6>
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
                                            <pre class="pre-text p-1"></pre>
                                        </div>                                   
                                    </div>
                                    <div class="edit-form-btn row">
                                        {{ link_to_route('biller.whatsapp.templates.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-2 mr-1']) }}
                                        {{ Form::button(trans('buttons.general.crud.create'), ['id' => 'submitBtn', 'disabled' => false,  'type' => 'submit', 'class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                        
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
@include('tinymce.scripts')
<script>
    const config = {
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        spinner: '<div style="width: 1rem; height: 1rem;" class="spinner-border unit-loading" role="status"><span class="sr-only">Loading...</span></div>',
        ajax: { 
            headers: { 
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Authorization': "Bearer {{ $business->whatsapp_access_token }}",
            } 
        },
        tinymce: {
            selector: '.tinyinput',
            menubar: 'file edit view format table tools',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 300,
            setup: function (editor) {
                // Fires when content changes (e.g., typing, formatting, paste)
                editor.on('change keyup paste cut', function () {
                  const content = editor.getContent();
                  {{-- console.log('Editor content changed:', content); --}}
                  // Optional: trigger jQuery event
                  $('.tinyinput').trigger('tinymce-change', [content]);
                });
            }
        }
    };

    // form submit success callback
    function trigger(data) {
        $('#submitBtn').html('Create').attr('disabled', false);
        if (data.redirectTo) setTimeout(() => {
            window.location.href = data.redirectTo;
        }, 1500); 
    }
    // form submit error callback
    function triggerError(res) {
        $('#submitBtn').html('Create').attr('disabled', false);
    }
    
    const Form = {
        business: @json($business),
        rowHtml: $('#varsTbl tbody tr:first').clone(),

        init() {
            $.ajaxSetup(config.ajax);
            tinymce.init(config.tinymce);

            $('.add-var').click(Form.onClickAddVariable);
            $('.tinyinput').on('tinymce-change', Form.onChangeTinymce);
            $('#varsTbl').on('keyup', '.vars', Form.onKeyupVariable);

            $('#submitBtn').click(Form.onFormSubmit);
        },

        onFormSubmit(e) {
            e.preventDefault();
            $(this).html('Create ' + config.spinner).attr('disabled', true);

            const formData = {};
            formData['form'] = $("#mediaBlockForm").serialize();
            formData['url'] = $("#mediaBlockForm").attr('action');
            addObject(formData, true);
        },

        varIndex: 0,
        tinymceText: '',
        onClickAddVariable() {
            Form.varIndex++;
            // encode curly braces
            const n = `&#123;&#123;${Form.varIndex}&#125;&#125;`
            tinymce.activeEditor.execCommand('mceInsertContent', false, n);
            Form.addRow();
        },
        addRow() {
            $('#varsTbl').removeClass('d-none');
            const n = `&#123;&#123;${Form.varIndex}&#125;&#125;`
            if (Form.varIndex == 1) {
                $('#varsTbl tbody td:first').html(n);
            } else {
                const row = Form.rowHtml.clone();
                row.find('td:first').html(n);
                $('#varsTbl tbody').append(row);
            }
        },
        onChangeTinymce(e,content) { 
            $('#varsTbl tbody tr').each(function() {
                const varIndex = $(this).find('td:first').html();
                if (varIndex && !content.includes(varIndex)) {
                    $(this).remove();
                }
            });
            if (!$('#varsTbl tbody td:first').length) {
                $('#varsTbl').addClass('d-none');
            }

            Form.tinymceText = content;
            $('#bodyContent').val(htmlToWhatsAppFormat(content));
        },
        onKeyupVariable() {
            const row = $(this).parents('tr:first');
            const varIndex = row.find('td:first').html();
            let content = Form.tinymceText;
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
    
    $(Form.init);
</script>
@endsection
