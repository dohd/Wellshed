@extends ('core.layouts.app')
@section ('title', 'Whatsapp Broadcast')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Whatsapp Broadcast</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            
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
                                {{ Form::open(['route' => 'biller.omniconvo.whatsapp_broadcast', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                                    <div class="row">
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
                                            <div class="form-group">
                                                <label for="userSegment">User Segment</label>
                                                <select name="user_segment" id="userSegment" class="custom-select">
                                                    <option value="local">Subscribed Users (All users who have talked to bot)</option>
                                                    <option value="import">Upload User File </option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="uploadFile">
                                                    Upload File (CSV) 
                                                    <a href="{{ route('biller.omniconvo.media_block_template') }}" target="_blank" style="text-decoration: underline;"><b>Download CSV Template</b></a>
                                                </label>
                                                {{ Form::file('csv_file', ['class' => 'form-control', 'accept' => '.csv']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="edit-form-btn row">
                                        {{ Form::submit('Post Message', ['class' => 'btn btn-primary btn-md col-2 ml-2']) }}                                           
                                    </div>
                                {{ Form::close() }}
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
            } 
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            $('#msgTemplate').select2({allowClear: true});

            // fetch media blocks
            $.get("{{ route('api.media_blocks.index') }}")
            .then(data => {
                $('.unit-loading').addClass('d-none');
                if (data?.templates && data.templates.length) {
                    Index.renderTemplates(data.templates);
                }
            })
            .fail((xhr,status,err) => {
                $('.unit-loading').addClass('d-none');
                // alert('Server Error! Please try again later or contact System Admin');
            })
        },

        renderTemplates(data) {
            data.forEach(v => {
                const templateId = 'WHATSAPP_TEMPLATE_ID-' + v.id;
                $('#msgTemplate').append(`<option value="${templateId}">${v.name}</option>`);
            });
        },
    };

    $(Index.init)
</script>
@endsection
