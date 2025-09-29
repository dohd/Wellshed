<!DOCTYPE html>

@include('tinymce.scripts')


<div class='form-group row'>
    <div class="col-2">
        <label for="date">Date</label>
        <input type="text" class="form-control datepicker" name="date" id="date">
    </div>
    <div class="col-3">
        <label for="customer">Customer Name</label>
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Customer" required>
            <option value="">Choose Customer</option>
            @foreach ($customers as $customer)
                <option value="{{$customer->id}}" {{$customer->id == @$customer_complains->customer_id ? 'selected' : ''}}>{{$customer->company ?: $customer->name}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-3">
        <label for="project">Search Project</label>
        <select name="project_id" id="project" class="form-control" data-placeholder="Search Project" required>
            <option value="">Search Project</option>
        </select>
    </div>
    <div class="col-3">
        <label for="employees">Complaint To:</label>
        <select name="employees[]" id="employees" class="form-control" data-placeholder="Search Employees" multiple required>
            @foreach($employees as $employee)
            <option value="{{ $employee['id'] }}" {{ in_array($employee->id, json_decode(@$customer_complains->employees) ?: [])? 'selected' : '' }}>
                {{ $employee->fullname }}
            </option>
        @endforeach
        </select>
    </div>
</div>
<div class='form-group row mt-3'>
    <div class="col-2">
        <label for="Solver">Complaint Solver</label>
        <select name="solver_id" id="solver" class="form-control" data-placeholder="Search Solver" required>
            <option value="">Search Solver</option>
            @foreach($employees as $employee)
            <option value="{{ $employee['id'] }}" {{$employee->id == @$customer_complains->solver_id ? 'selected' : ''}}>
                {{ $employee->fullname }}
            </option>
        @endforeach
        </select>
    </div>
    <div class="col-3">
        <label for="type_of_complain">Type of Complain</label>
        <input type="text" name="type_of_complaint" id="" value="{{@$customer_complains->type_of_complaint}}" class="form-control" placeholder="eg. Quality, Service ...">
    </div>
    <div class="col-6">
        <label for="description">Description of Issue</label>
        <textarea name="issue_description" id="issue_description " class="form-control tinyinput" cols="30" rows="3">{{@$customer_complains->issue_description}}</textarea>
    </div>
    <div class="col-2">
        <label for="">Initial Scale</label>
        <div class="project-info-icon">
            <h2 id="prog">%</h2>
        </div>
        <div class="project-info-text pt-1">
            <h5></h5>
            <input type="range" min="0" max="10" name="initial_scale" value="{{@$customer_complains->initial_scale ?: 0}}" class="slider initial_scale" id="initial_scale">
        </div>
    </div>
    <div class="col-2">
        <label for="">Final Scale</label>
        <div class="project-info-icon">
            <h2 id="prog1">%</h2>
        </div>
        <div class="project-info-text pt-1">
            <h5></h5>
            <input type="range" min="0" max="10" name="current_scale" value="{{@$customer_complains->current_scale ?: 0}}" class="slider final_scale" id="final_scale">
        </div>
    </div>
</div>

<div class="mt-3 col-12" id="pro_tabs">

    <h2 class="mb-1">PDCA Cycle</h2>
    <h5 class="mb-2">
        The PDCA cycle or Plan-Do-Check-Act, is a four-step iterative management method used for the control and
        continuous improvement of processes. It promotes systematic problem-solving and continual enhancement.
    </h5>

    <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
        <li class="nav-item">
            <a class="nav-link active px-2" id="tab1" data-toggle="tab" href="#tab_data1" aria-controls="tab_data1" role="tab" aria-selected="true" style="font-size: 20px">
                <i class="fa fa-lightbulb-o"></i> Planing Phase
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link px-2" id="tab2" data-toggle="tab" href="#tab_data2" aria-controls="tab_data2" role="tab" aria-selected="true" style="font-size: 20px">
                <i class="fa fa-gears"></i> Implementation Phase
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link px-2" id="tab3" data-toggle="tab" href="#tab_data3" aria-controls="tab_data3" role="tab" aria-selected="true" style="font-size: 20px">
                <i class="fa fa-life-bouy"></i> Checking Results Phase
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link px-2" id="tab4" data-toggle="tab" href="#tab_data4" aria-controls="tab_data4" role="tab" aria-selected="true" style="font-size: 20px">
                <i class="fa fa-bullseye"></i> Customer Feedback Phase
            </a>
        </li>

    </ul>

    <div class="tab-content px-1 pt-1">

        <div class="tab-pane active in row" id="tab_data1" aria-labelledby="tab1" role="tabpanel">

            <div class="col-12 col-lg-10">
                <h4>Planing Phase(P)</h4>
                <h5>Develop a plan to address the problem or implement the improvement. This includes identifying resources, setting timelines, and assigning responsibilities.</h5>
                <textarea name="planing" id="plan" class="tinyinput form-control" cols="30" rows="10">{{@$customer_complains->planing}}</textarea>
            </div>

        </div>


        <div class="tab-pane row" id="tab_data2" aria-labelledby="tab2" role="tabpanel">

            <div class="col-12 col-lg-10">
                <h4>Implementation (D)</h4>
                <h5>Implement the Plan, Document Observations & Record any issues, unexpected outcomes, or insights
                    gained during implementation.</h5>
                <textarea name="doing" id="do" class="tinyinput form-control" cols="30" rows="10">{{@$customer_complains->doing}}</textarea>
            </div>

        </div>


        <div class="tab-pane row" id="tab_data3" aria-labelledby="tab3" role="tabpanel">

            <div class="col-12 col-lg-10">
                <h4>Check Result (C)</h4>
                <h5>Analyze Results: Compare the collected data against the expected outcomes. <br>
                    Evaluate Effectiveness: Determine whether the plan worked as intended and met the goals.</h5>
                <textarea name="checking" id="check" class="tinyinput form-control" cols="30" rows="10">{{@$customer_complains->checking}}</textarea>
            </div>

        </div>


        <div class="tab-pane row" id="tab_data4" aria-labelledby="tab4" role="tabpanel">

            <div class="col-12 col-lg-10">
                <h4>Customer FeedBack (A)</h4>
                <h5>Customer Response documentation, that will enable to get the final scale</h5>
                <textarea name="customer_feedback" id="act" class="tinyinput form-control" cols="35" rows="10">{{@$customer_complains->customer_feedback}}</textarea>
            </div>

        </div>


    </div>

</div>



<div class="mt-3 col-12 mb-3">
    <hr class="px-4 mb-2">
    <div class="col-12 col-lg-10">
        <h5>Comments</h5>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <textarea name="comments" id="comments-p0" cols="35" rows="10" class="form-control tinyinput" placeholder="Coments">{{@$customer_complains->comments}}</textarea>
        </div>
    </div>

</div>

@section('extra-scripts')

    {{ Html::script('focus/js/select2.min.js') }}
    <script>

        $(document).ready(function () {

            tinymce.init({
                selector: '.tinyinput',
                menubar: false,
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
                height: 160,
            });

            tinymce.init({
                selector: '.tinyinput-readonly',
                menubar: '',
                plugins: '',
                toolbar: '',
                height: 300,
                readonly: true,
            });

        });


    </script>
@endsection
