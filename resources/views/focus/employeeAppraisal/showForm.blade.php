<!DOCTYPE html>

@include('tinymce.scripts')

<!---Employee Appraisal-->
<div class="tab-pane" id="tab10" role="tabpanel" aria-labelledby="base-tab10">

    <div class="form-group">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                            <i class="fa fa-pencil" aria-hidden="true"></i> Status
                        </a>
                    </div>
                    <div class="card-header bg-primary text-white">
                        
                        
                        <h3 style="color: white">Employee Appraisal </h3>
                        <h4 style="color: white"> {{ $appraisal->employee->first_name . " " . $appraisal->employee->last_name }} </h4>
                        <h5 style="color: white"> {{(new DateTime($appraisal->start_date))->format('jS F, Y') . " - " . (new DateTime($appraisal->end_date))->format('jS F, Y') }} </h5>
                    </div>
                    <div class="card-body">

                            <!-- Employee -->
                            <h5>Employee</h5>
                            <div class="row mb-3">
                                <div class="col-12 col-md-6">
                                    <select name="employee_id" id="employee_id" class="form-control" @if(@$appraisal) disabled @endif>
                                        <option value=""> Select Employee </option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp['id'] }}" @if(@$appraisal->employee_id === $emp['id']) selected @endif>{{ $emp['first_name'] . " " . $emp['last_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>



                            <!-- Appraisal Period -->
                            <h5>Appraisal Period</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" disabled value="{{ @$appraisal->start_date }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" disabled value="{{ @$appraisal->end_date }}">
                                </div>
                            </div>

                            <!-- Performance Evaluation -->
                            <h5>Performance Evaluation</h5>
                            @php
                                $ratings = [
                                    '1' => '1 - Poor',
                                    '2' => '2 - Below Average',
                                    '3' => '3 - Average',
                                    '4' => '4 - Good',
                                    '5' => '5 - Excellent'
                                ];
                            @endphp

                            <div class="row mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="job_knowledge" class="form-label">Job Knowledge</label>
                                    <select class="form-control" id="job_knowledge" name="job_knowledge" disabled>
                                        <option value="" disabled selected>Select rating</option>
                                        @foreach($ratings as $value => $description)
                                            <option value="{{ $value }}" @if(@$appraisal->job_knowledge === $value) selected @endif>{{ $description }} </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="quality_of_work" class="form-label">Quality of Work</label>
                                    <select class="form-control" id="quality_of_work" name="quality_of_work" disabled>
                                        <option value="" disabled selected>Select rating</option>
                                        @foreach($ratings as $value => $description)
                                            <option value="{{ $value }}" @if(@$appraisal->quality_of_work === $value) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 mt-md-1">
                                    <label for="communication" class="form-label">Communication Skills</label>
                                    <select class="form-control" id="communication" name="communication" disabled>
                                        <option value="" disabled selected>Select rating</option>
                                        @foreach($ratings as $value => $description)
                                            <option value="{{ $value }}"  @if(@$appraisal->communication === $value) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 mt-md-1">
                                    <label for="attendance" class="form-label">Attendance and Punctuality</label>
                                    <select class="form-control" id="attendance" name="attendance" disabled>
                                        <option value="" disabled selected>Select rating</option>
                                        @foreach($ratings as $value => $description)
                                            <option value="{{ $value }}" @if(@$appraisal->attendance === $value) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <!-- Comments Section -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="supervisor_comments" class="form-label">Supervisor Comments</label>
                                    <textarea class="form-control tinyinput" id="supervisor_comments" name="supervisor_comments" rows="4"> {{ @$appraisal->supervisor_comments }}</textarea>
                                </div>
                            </div>

                            <div>
{{--                                <input type="number" name="employee_id" value="{{$hrms->id}}" hidden>--}}
                                <input type="number" name="supervisor_id" value="{{\Illuminate\Support\Facades\Auth::user()->id}}" hidden>
                            </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('focus.employeeAppraisal.partials.status')
</div>

@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>

        $('#employee_id').select2({allowClear: true});

        tinymce.init({
            selector: '.tinyinput',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 400,
            readonly  : true,
        });

    </script>
@endsection
