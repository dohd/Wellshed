@extends ('core.layouts.app')

@section('title', 'Health And Safety Objective')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">{{ 'Health And Safety Objective' }}</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.health_and_safety_objectives.partials.health-and-safety-objectives-header-buttons')
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
                                {{ Form::model($healthAndSafetyObjective, ['route' => ['biller.health-and-safety-objectives.update', $healthAndSafetyObjective], 'method' => 'PATCH', 'id' => 'edit-health-and-safety']) }}
                                <div class="form-group">
                                    @include('focus.health_and_safety_objectives.form')
                                    <br>
                                    <div class="edit-form-btn mb-2">
                                        {{ link_to_route('biller.health-and-safety-objectives.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
        // $('#date').datepicker({
        //     autoHide: true,
        //     format: '{{ config('core.user_date_format') }}'
        // });
        // $('#date').datepicker('setDate', '{{ date(config('core.user_date_format')) }}');

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
