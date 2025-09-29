@extends ('core.layouts.app')
@section ('title', 'Create | Media Blocks Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Create Media Block</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.media_blocks.partials.media-blocks-header-buttons')
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
                                <form action="{{ route('api.media_blocks.create') }}" id="mediaBlockForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="category">Template Category</label>
                                                <select name="" id="category" class="custom-select">
                                                    <option value="utility">UTILITY</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="mediaType">Media Block Type</label>
                                                <select name="" id="mediaType" class="custom-select">
                                                    <option value="standard">Standard (Text Only)</option>
                                                </select>                                                
                                            </div>
                                            <div class="form-group">
                                                <label for="name">Template Name<span class="danger">*</span></label>
                                                <input type="text" name="name" class="form-control" placeholder="Template Name" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="message">Message<span class="danger">*</span></label>
                                                <textarea name="text" id="" class="form-control" placeholder="Message to be sent" required></textarea>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <div class="edit-form-btn row">
                                        {{ link_to_route('biller.omniconvo.media_blocks_index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-2 mr-1']) }}
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
<script>
    const config = {
        ajax: { 
            headers: { 
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Authorization': "Bearer {{ config('agentToken') }}",
            } 
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        spinner: '<div style="width: 1rem; height: 1rem;" class="spinner-border unit-loading" role="status"><span class="sr-only">Loading...</span></div>',
    };

    // form submit success callback
    function trigger(res) {
        $('#submitBtn').html('Create').attr('disabled', false);
    }
    // form submit error callback
    function triggerError(res) {
        $('#submitBtn').html('Create').attr('disabled', false);
    }
    
    const Form = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);
            
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
    };
    
    $(Form.init);
</script>
@endsection
