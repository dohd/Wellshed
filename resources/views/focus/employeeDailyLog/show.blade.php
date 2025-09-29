<!DOCTYPE html >

@extends ('core.layouts.app')

@include('tinymce.scripts')


@section ('title',  'View EDL')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">View Employee Daily Log</h2>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.employeeDailyLog.partials.edl-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">


                            <h2 class="font-weight-bold mb-2">EDL</h2>

                            <div class="row">

                                <div class="col-10 col-lg-3">
                                    <label>Employee:</label>
                                    <input type="text" readonly value="{{ $edl['employee'] }}" class="form-control box-size mb-2">
                                </div>
                                <div class="col-10 col-lg-2">
                                    <label>Role:</label>
                                    <input type="text" readonly value="{{ $edl['role'] }}" class="form-control box-size mb-2">
                                </div>

                                <div class="col-6 col-lg-3">
                                    <label>Date:</label>
                                    <input type="text" readonly value="{{ (new DateTime($edl['date']))->format('l, jS F, Y') }}" class="form-control box-size mb-2">
                                </div>
                                <div class="col-6 col-lg-1">
                                    <label>Total Hours:</label>
                                    <input type="text" readonly value="{{ $totalHours }}" class="form-control box-size mb-2">
                                </div>
                                <div class="col-6 col-lg-1">
                                    <label>% Average Performance:</label>
                                    @php
                                        $performance = count($edlTasks) > 0 
                                            ? $edlTasks->sum('work_done') / count($edlTasks) 
                                            : 0;
                                    @endphp
                                    <input type="text" readonly value="{{ $performance }}" class="form-control box-size mb-2">
                                </div>

                            </div>

                            <h2 class="font-weight-bold mb-2 mt-2">EDL Key Activities</h2>

                            @php
                                $i = 1;
                            @endphp
                            @foreach($edlTasks as $task)

                                <div class="row mb-2">

{{--                                            <div class="col-md-3">--}}
{{--                                                <label>Key Performance Indicator:</label>--}}
{{--                                                <input type="text" readonly value="{{ $task['category'] }}" class="form-control box-size">--}}
{{--                                            </div>--}}

                                    <div class="col-12 mb-1">{{ 'Key Activity ' . $i }} <span>{{$task['key_activities']}}</span></div>

                                    <div class="col-12 col-md-4">
                                        <label>Key Performance Indicator:</label>
                                        <input type="text" readonly value="{{ $task['subcategory'] }}" class="form-control box-size">
                                    </div>
                                    <div class="col-6 col-md-2 mt-1 mt-lg-0">
                                        <label>Frequency:</label>
                                        <input type="text" readonly value="{{ $task['frequency'] }}" class="form-control box-size">
                                    </div>
                                    <div class="col-4 col-md-1 mt-1 mt-lg-0">
                                        <label>Hours:</label>
                                        <input type="text" readonly value="{{ $task['hours'] }}" class="form-control box-size">
                                    </div>
                                    <div class="col-4 col-md-1 mt-1 mt-lg-0">
                                        <label for="performance" >Performance:</label>
                                        <input type="text" value="{{ number_format($task['performance'], 2) }}" class="form-control box-size performance" step="0.01" readonly>
                                    </div>
                                    <div class="col-6 col-md-2 mt-1 mt-lg-0">
                                        <label for="target">Target / UoM</label>
                                        <div class="row no-gutters">
                                            <div class="col-5">
                                                <input type="text" class="form-control box-size" value="{{number_format($task->target, '2')}}" step="0.01" readonly>
                                            </div>
                                            <div class="col-5">
                                                <input type="text" class="form-control box-size" step="0.01" value="{{$task->uom}}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4 col-md-1 mt-1 mt-lg-0">
                                        <label for="work_done" >Work Done (%):</label>
                                        <input type="text" value="{{ number_format($task['work_done'], 2) }}" class="form-control box-size work_done" step="0.01" readonly>
                                    </div>
                                    <div  class="col-12 col-md-11 mt-1 mt-lg-1">
                                        <label>Description:</label>
                                        <textarea readonly class="form-control box-size descriptions" rows="3">{{ $task['description'] }}</textarea>
                                    </div>

                                </div>

                                <hr>

                                @php
                                    $i++;
                                @endphp

                            @endforeach

                            <h2 class="font-weight-bold mb-2 mt-2">EDL Remarks</h2>

                            <div class="row mb-2">

                                <div class="col-11 col-md-4">
                                    <label>Rating:</label>
                                    <input type="text" readonly value="{{ $edl['rating'] }}" class="form-control box-size">
                                </div>

                                <div class="form-group col-8 col-md-2 mt-1 mt-lg-0">
                                    <label>Reviewer:</label>
                                    <input type="text" readonly value="{{ $edl['reviewer'] }}" class="form-control box-size">
                                </div>

                                <div class="col-8 form-group col-md-2">
                                    <label>Reviewed at:</label>
                                    <input type="text" readonly value="{{ $edl['reviewed_at'] }}" class="form-control box-size">
                                </div>

                                <div  class="col-md-11 mt-1">
                                    <label>Remarks:</label>
                                    <textarea id="remarks" readonly class="form-control box-size" rows="5">{{ $edl['remarks'] }}</textarea>
                                </div>

                            </div>



                            <a class="btn btn-primary" href="{{ route('biller.employee-daily-log.index') }}"> Exit </a>

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
        selector: '.descriptions',
        menubar: '',
        plugins: '',
        toolbar: '',
        height: 140,
        readonly  : true,
        content_style: 'body { background-color: #ECEFF1; }',
    });

    tinymce.init({
        selector: '#remarks',
        menubar: '',
        plugins: '',
        toolbar: '',
        height: 160,
        readonly  : true,
        content_style: 'body { background-color: #ECEFF1; }',
    });



    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#purchase_date').datepicker('setDate', new Date());
    $('#warranty_expiry_date').datepicker('setDate', new Date());
</script>
@endsection