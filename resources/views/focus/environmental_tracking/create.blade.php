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
                                {{ Form::open(['route' => 'biller.environmental-tracking.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-environmental-tracking']) }}
                                @include('focus.environmental_tracking.form')
                                {{ link_to_route('biller.environmental-tracking.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
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
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
    {{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        };


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


        $("#employee").select2();
        $("#customer_id").select2({});
        $("#cm_responsible_person").select2({});

        $("#customer_id").select2({
            ajax: {
                url: "{{route('biller.customers.select')}}",
                dataType: 'json',
                type: 'POST',
                data: customer_id => ({customer_id}),
                processResults: (data) => {
                    return { results: data.map(v => ({text: v.company, id: v.id})) }
                },
            }
        });

        $("#customer_id").change(function() {
            $("#branch_id").val('').trigger('change');
            $("#branch_id").select2({
                ajax: {
                    url: "{{ route('biller.branches.select') }}",
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: (customer_id) => ({customer_id, customer_id: $('#customer_id').val()}),
                    processResults: (data) => {
                        return { results: data.map(v => ({text: v.name, id: v.id})) }
                    },
                }
            });
            $("#project_id").select2({
                ajax: {
                    url: "{{ route('biller.p.client-projects') }}",
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: (customer_id) => ({customer_id, customer_id: $('#customer_id').val()}),
                    processResults: (data) => {
                        return { results: data.map(v => ({text: v.name, id: v.id})) }
                    },
                }
            });
        }); 

        let rowId = 0;
        const rowHtml = [$('#environmental_tracking_table tbody tr:eq(0)').html(), $('#environmental_tracking_table tbody tr:eq(1)').html()]
   
        $("#addIssue").click(function(){
            rowId++;
            const i = rowId;
            const html = rowHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#environmental_tracking_table tbody tr:eq(-3)').before(html);
        });

        // $(".remove").click(function(){
        //     const $tr = $(this).parents('tr:first');
        //     $tr.next().remove();
        //     $tr.remove();
        // });

    </script>
@endsection
