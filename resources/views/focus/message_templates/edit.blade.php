@extends ('core.layouts.app')

@section ('title', 'Edit Message Template')

@section('page-header')
    <h1>
        <small>Edit Message Template</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit Message Template</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.message_templates.partials.message_templates-header-buttons')
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
                                    {{ Form::model($message_template, ['route' => ['biller.message_templates.update', $message_template], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.message_templates.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.message_templates.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!--form-group-->

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
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: { 
            headers: { 
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Authorization': "Bearer {{ $business->whatsapp_access_token }}",
            } 
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        spinner: '<div class="text-center"><span class="font-large-1"><i class="fa fa-spinner spinner"></i></span></div>',
    };

    const Index = {
        business: @json($business),
        selectedTemplate: "{{ $message_template->template_id ?? '' }}", // ✅ preselect on edit

        init() {
            $.ajaxSetup(config.ajax);
            $('#mediaBlocksSelect').select2({ allowClear: true });
            $('#mediaBlocksSelect').change(Index.templateChange);
            $('#type').change(Index.typeChange);
            Index.drawDataToSelect();
            Index.typeChange();
        },
        typeChange(){
            const type = $('#type').val();
            if(type){
                $('#mediaBlocksSelect').attr('disabled',false);
            }else $('#mediaBlocksSelect').attr('disabled',true);
        },

        templateChange() {
            let template_id = $(this).val();
            $('#text_message').attr('readonly',true);
            $.ajax({
                url: "{{ route('biller.message_templates.get_whatapp_temps') }}",
                method: "POST",
                data: {
                    template_id: template_id
                },
                success: function(response) {
                    let bodyText = response.template.components[0].text;
                    $('#text_message').val(bodyText);
                }
            })
        },

        drawDataToSelect() {
            // clear previous options
            $('#mediaBlocksSelect').empty();

            const business = Index.business;
            const url = `${business.graph_api_url}/${business.whatsapp_business_account_id}/message_templates`;

            $.get(url)
                .then(({ data }) => {
                    if (data.length) {
                        // Add a default placeholder option
                        $('#mediaBlocksSelect').append('<option value="">-- Select Template --</option>');

                        data.forEach((v) => {
                            let isSelected = (v.id === Index.selectedTemplate) ? 'selected' : '';
                            $('#mediaBlocksSelect').append(`
                                <option value="${v.id}" ${isSelected}>
                                    ${v.name} (${v.status ? 'Approved' : 'Rejected'}) - ${v.category}
                                </option>
                            `);
                        });

                        // ✅ ensure Select2 UI updates on edit
                        if (Index.selectedTemplate) {
                            $('#mediaBlocksSelect').val(Index.selectedTemplate).trigger('change');
                        }
                    }
                })
                .fail((xhr, status, err) => {
                    console.log(err);
                });
        }
    };

    $(() => Index.init());
</script>
@endsection
