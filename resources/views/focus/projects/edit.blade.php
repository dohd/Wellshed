@extends ('core.layouts.app')

@section ('title', trans('labels.backend.projects.edit') . ' | ' . trans('labels.backend.projects.management'))

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-2">
            <div class="content-header-left col-md-6 col-12">
                <h4 class="content-header-title">{{ trans('labels.backend.projects.edit') }}</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.projects.partials.projects-header-buttons')
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
                                {{ Form::model($project, ['route' => ['biller.projects.update', $project], 'method' => 'PATCH', 'id' => 'edit-project']) }}
                                <div class="form-group">
                                    @include("focus.projects.form")
                                    <div class="edit-form-btn float-right mb-2">
                                        {{ link_to_route('biller.projects.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
        $('[data-toggle="datepicker"]').datepicker({
            autoHide: true,
            format: '{{config('core.user_date_format')}}'
        });
        $('.from_date').datepicker('setDate', '{{dateFormat($project->start_date)}}');
        $('.from_date').datepicker({autoHide: true, format: '{{date(config('core.user_date_format'))}}'});
        $('.to_date').datepicker('setDate', '{{dateFormat($project->end_date)}}');
        $('.to_date').datepicker({autoHide: true, format: '{{date(config('core.user_date_format'))}}'});

        $('#color').colorpicker();

        $("#wip_account").select2({allowClear: true});
        $("#tags").select2({allowClear: true});
        $("#employee").select2({allowClear:true});
        $("#person").select2({
            allowClear: true,
            ajax: {
                url: "{{route('biller.customers.select')}}",
                dataType: 'json',
                type: 'POST',
                data: person => ({person}),
                processResults: (data) => {
                    return { results: data.map(v => ({text: v.company, id: v.id})) }
                },
            }
        })
        .change(function() {
            $("#branch_id").val('').trigger('change');
        });  

        $("#branch_id").select2({
            allowClear:true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: (person) => ({person, customer_id: $('#person').val()}),
                processResults: (data) => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        });
    </script>
@endsection
