<!DOCTYPE html>

@extends ('core.layouts.app')

@include('tinymce.scripts')

@section ('title', "Quality Tracking")



@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row mb-1">
                <div class="content-header-left col-6">
                    <h4 class="content-header-title"> Quality Tracking </h4>
                </div>
                <div class="content-header-right col-6">
                    <div class="media width-250 float-right">
                        <div class="media-body media-right text-right">
                            @include('focus.quality_tracking.partials.quality-tracking-header-buttons')
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

                                   <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <h4 style="color: #0b0b0b">{{(new DateTime($data['date']))->format('l, jS F, Y')}}</h4>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Customer</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <h4 style="color: #0b0b0b">{{optional($data->customer)->company}}</h4>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Branch</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <h4 style="color: #0b0b0b">{{$data->branch ? $data->branch->name : " "}}</h4>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Project</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <h4 style="color: #0b0b0b">{{optional($data->project)->name}}</h4>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Employees Involved</p>
                                        </div>

                                        @if($employees)

                                            <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">

                                                @php

                                                    $halfSize = ceil(count($employees) / 2);
                                                    $chunks = array_chunk($employees, $halfSize);

                                                @endphp

                                                <div class="d-flex flex-row">
                                                    <div>
                                                        @foreach ($chunks[0] as $emp)
                                                            <p>{{ $emp['a'] }}</p>
                                                        @endforeach
                                                    </div>

                                                    <div class="ml-5">
                                                        @foreach ($chunks[1] as $emp)
                                                            <p>{{ $emp['a'] }}</p>
                                                        @endforeach
                                                    </div>

                                                </div>


                                            </div>

                                        @endif

                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Incident </p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <textarea class="form-control tinyinput-small">{{ $data['incident_desc'] }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Root Cause</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <textarea class="form-control tinyinput-large">{{$data['route_course']}}</textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Status</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            @if($data['status'] === 'first-aid-case') <h4 style="color: #0b0b0b"> First Aid Case </h4>
                                            @elseif($data['status'] === 'lost-work-day') <h4 style="color: #0b0b0b"> Lost Work Day </h4> @endif
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Responsibility</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <h4 style="color: #0b0b0b">{{optional($data->res)->first_name}} {{optional($data->res)->last_name}}</h4>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Days to Resolution</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <h4 style="color: #0b0b0b">{{$data->timing}}</h4>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5 p-1">
                                            <p style="font-size: 16px">PDCA Cycle</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5 p-1 font-weight-bold">
                                            <div class="row">
                                                <div class="col-6 p-1">
                                                    <h3>Plan</h3>
                                                    <textarea class="form-control tinyinput-large">{{ $data['plan'] }}</textarea>
                                                </div>
                                                <div class="col-6 p-1">
                                                    <h3>Do</h3>
                                                    <textarea class="form-control tinyinput-large">{{ $data['do'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 p-1">
                                                    <h3>Check</h3>
                                                    <textarea class="form-control tinyinput-large">{{ $data['check'] }}</textarea>
                                                </div>
                                                <div class="col-6 p-1">
                                                    <h3>Act</h3>
                                                    <textarea class="form-control tinyinput-large">{{ $data['act'] }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Comments</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <textarea class="form-control tinyinput-small">{{ $data['comments'] }}</textarea>
                                        </div>
                                    </div>

                                    <h2 class="mt-4">Countermeasure Closeout</h2>

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Countermeasure</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <textarea class="form-control tinyinput-large">{{ $data['countermeasure'] }}</textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Responsible Person</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            @if($data->cmResponsible)
                                                <h4 style="color: #0b0b0b">{{$data->cmResponsible->first_name . " " . $data->cmResponsible->last_name}}</h4>
                                            @else
                                                <h4 style="color: #0b0b0b"> </h4>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Completion Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <h4 style="color: #0b0b0b">{{(new DateTime($data['completion_date']))->format('l, jS F, Y')}}</h4>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p style="font-size: 16px">Verification</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <textarea class="form-control tinyinput-large">{{ $data['verification'] }}</textarea>
                                        </div>
                                    </div>

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>

        tinymce.init({
            selector: '.tinyinput-large',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 280,
            readonly  : true,
        });

        tinymce.init({
            selector: '.tinyinput-small',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 140,
            readonly  : true,
        });




        // initialize datepicker
        $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
        $('#purchase_date').datepicker('setDate', new Date());
        $('#warranty_expiry_date').datepicker('setDate', new Date());
    </script>
@endsection