<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Performance Evaluation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($appraisal, ['route' => ['biller.employee_appraisals.performance_evaluation', $appraisal], 'method' => 'POST']) }}
            <div class="modal-body">
                <h5>Performance Evaluation</h5>
                @php
                    $ratings = [
                        '1' => '1 - Poor',
                        '2' => '2 - Below Average',
                        '3' => '3 - Average',
                        '4' => '4 - Good',
                        '5' => '5 - Excellent',
                    ];
                @endphp


                <div class="form-group">
                    <label for="job_knowledge" class="form-label">Job Knowledge</label>
                    <select class="form-control" id="job_knowledge" name="job_knowledge" required>
                        <option value="" disabled selected>Select rating</option>
                        @foreach ($ratings as $value => $description)
                            <option value="{{ $value }}" @if (@$appraisal->job_knowledge === $value) selected @endif>
                                {{ $description }} </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="quality_of_work" class="form-label">Quality of Work</label>
                    <select class="form-control" id="quality_of_work" name="quality_of_work" required>
                        <option value="" disabled selected>Select rating</option>
                        @foreach ($ratings as $value => $description)
                            <option value="{{ $value }}" @if (@$appraisal->quality_of_work === $value) selected @endif>
                                {{ $description }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-md-1">
                    <label for="communication" class="form-label">Communication Skills</label>
                    <select class="form-control" id="communication" name="communication" required>
                        <option value="" disabled selected>Select rating</option>
                        @foreach ($ratings as $value => $description)
                            <option value="{{ $value }}" @if (@$appraisal->communication === $value) selected @endif>
                                {{ $description }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-md-1">
                    <label for="attendance" class="form-label">Attendance and Punctuality</label>
                    <select class="form-control" id="attendance" name="attendance" required>
                        <option value="" disabled selected>Select rating</option>
                        @foreach ($ratings as $value => $description)
                            <option value="{{ $value }}" @if (@$appraisal->attendance === $value) selected @endif>
                                {{ $description }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                {{ Form::submit('Update', ['class' => 'btn btn-primary']) }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
