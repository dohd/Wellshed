<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
@extends ('core.layouts.app')

@include('tinymce.scripts')

@section ('title',  'Employee Daily Log')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Review Employee Daily Log</h2>
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
                            {{-- @foreach($data['edl'] as $edl) --}}
                            @php
                                $edl = $data['edl'];
                            @endphp

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

                                </div>

                            {{-- @endforeach --}}

                            <h2 class="font-weight-bold mb-2 mt-2">EDL Key Activities</h2>
                            @php
                                $i = 1;
                            @endphp

                            @foreach($data['edlTasks'] as $task)

                                <div class="row mb-2">

{{--                                    <div class="col-md-3">--}}
{{--                                        <label>Key Performance Indicator:</label>--}}
{{--                                        <input type="text" readonly value="{{ $task['category'] }}" class="form-control box-size">--}}
{{--                                    </div>--}}
                                    <div class="col-12 mb-1">{{ 'Key Activity ' . $i }}</div>

                                    <div class="col-8 col-md-4">
                                        <label>Key Performance Indicator:</label>
                                        <input type="text" readonly value="{{ $task['subcategory'] . " | " . $task['frequency'] }}" class="form-control box-size">
                                    </div>
                                    <div class="col-6 col-md-2 mt-1 mt-lg-0">
                                        <label>Frequency:</label>
                                        <input type="text" readonly value="{{ $task['frequency'] }}" class="form-control box-size">
                                    </div>
                                    <div class="col-4 col-md-1 mt-1 mt-lg-0">
                                        <label>Hours:</label>
                                        <input type="text" readonly value="{{ $task['hours'] }}" class="form-control box-size">
                                    </div>
                                    <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                                        <label>Performance:</label>
                                        <input type="text" value="{{ $task['performance'] }}" class="form-control box-size performance" step="0.01" readonly>
                                    </div>
                                    <div class="col-5 col-lg-2 mt-1 mt-lg-0">
                                        <label>Target / UoM</label>
                                        <div class="row no-gutters">
                                            <div class="col-5">
                                                <input type="text" value="{{ $task['target'] }}" class="form-control box-size" step="0.01" readonly>
                                            </div>
                                            <div class="col-5">
                                                <input type="text" value="{{ $task['uom'] }}" class="form-control box-size" step="0.01" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                                        <label > (%) Performance</label>
                                        <input type="text" value="{{ numberFormat($task['work_done']) }}" class="form-control box-size work_done" step="0.01" readonly>
                                    </div>
                                    <div class="col-6 col-md-8 mt-1 mt-lg-1">
                                        <label>Description:</label>
                                        <textarea readonly id="description{{$i}}" class="form-control box-size taskdescription" rows="3">{{ $task['description'] }}</textarea>
                                    </div>

                                </div>

                                <hr>

                                @php
                                    $i++;
                                @endphp

                            @endforeach

                            {{ Form::open(['route' => ['biller.edl-remark-save', $edlNumber], 'method' => 'POST', 'id' => 'create-employee-log-remark']) }}
                            <h2 class="font-weight-bold mb-2 mt-2">Remarks</h2>

                                <div class="form-group col-md-4">
                                    <label for="rating">Rating:</label>
                                    <select class="form-control" id="rating" name="rating">
                                        <option value="">-- Select Rating --</option>
                                        @foreach($ratings as $k => $rtg)
                                        {{-- {{browserlog($data['edl'][0]['rating'])}} --}}
                                            <option value="{{ $k }}" {{$k == @$edl['rating'] ? 'selected' : ''}} >{{ $rtg }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-10">
                                    <label for="remarks">Remarks:</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="6">  @if(!empty($data['edl'][0]['remarks'])) {{ $data['edl'][0]['remarks'] }} @endif </textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit</button>

                            {{ Form::close() }}

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
<script type="text/javascript">

    tinymce.init({
        selector: '#remarks',
        menubar: 'file edit view format table tools',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 230,
    });

    tinymce.init({
        selector: '.taskdescription',
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