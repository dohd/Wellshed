<!DOCTYPE html>

@extends ('core.layouts.app')

@include('tinymce.scripts')

@section('title', 'Environmental Tracking')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">{{ 'Environmental Tracking' }}</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.environmental_tracking.partials.header-buttons')
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
                                {{ Form::model($data, ['route' => ['biller.environmental-tracking.update', $data], 'method' => 'PATCH', 'id' => 'edit-environmental-tracking']) }}
                                <div class="form-group">
                                    @include('focus.environmental_tracking.edit_form')
                                    <div class="edit-form-btn float-right mb-2">
                                        {{ link_to_route('biller.environmental-tracking.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                        {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                    </div>
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
@section('after-styles')
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection
@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
    {{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>


        $('#date').datepicker();

        $("#cm_responsible_person").select2();


        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 300,
        });

        tinymce.init({
            selector: '.tinyinput-small',
            menubar: false,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 180,
        });



        function select2Config(url, callback) {
            return {
                ajax: {
                    url,
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: ({term}) => ({q: term, keyword: term}),
                    processResults: callback
                }
            }
        }

        // load projects dropdown
        const projectUrl = "{{ route('biller.projects.project_search') }}";
        function projectData(data) {

            return {results: data.map(v => ({id: v.id, text: v.name}))};
        }
        $("#project").select2(select2Config(projectUrl, projectData));


        $(document).ready(function () {

            // project
            @php
                $project_name = '';
                $project = $data->project;
                if ($project) {
                    $sirProject = \App\Models\project\Project::find($project);
                }
            @endphp
            const projectName = "{{ $sirProject->name }}";
            const projectId = "{{ $sirProject->id }}";
            $('#project').append(new Option(projectName, projectId, true, true)).change();


        });


            $("#tags").select2();
        $("#employee").select2();
        $("#person").select2({
            ajax: {
                url: "{{ route('biller.customers.select') }}",
                dataType: 'json',
                type: 'POST',
                data: person => ({
                    person
                }),
                processResults: (data) => {
                    return {
                        results: data.map(v => ({
                            text: v.company,
                            id: v.id
                        }))
                    }
                },
            }
        });

        $("#person").change(function() {
            $("#branch_id").val('').trigger('change');
            $("#branch_id").select2({
                ajax: {
                    url: "{{ route('biller.branches.select') }}",
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: (person) => ({
                        person,
                        customer_id: $('#person').val()
                    }),
                    processResults: (data) => {
                        return {
                            results: data.map(v => ({
                                text: v.name,
                                id: v.id
                            }))
                        }
                    },
                }
            });
        });
    </script>
@endsection
