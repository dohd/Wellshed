@extends ('core.layouts.app')

@section ('title', 'Salary Management' . ' | ' . 'Create')

@section('page-header')
    <h1>
        {{ 'Salary Management' }}
        <small>{{ 'Create' }}</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">{{ 'View Salary' }}</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.salary.partials.salary-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="tab-content px-1 pt-1">
                                <!-- tab1 -->
                                <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                                    <p>Employee Name</p>
                                                </div>
                                                <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                                    <p>{{$user['first_name'] . ' ' . $user['last_name']}}</p>
                                                    <input type="hidden" id="salary_employee" data-name="{{$salary['employee_name']}}"  value="{{$salary['employee_name']}}">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                                    <p>Job Grade</p>
                                                </div>
                                                <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                                    <p>{{ optional(optional($salary->user)->meta)->job_grade ?? 'No Grade Set' }}</p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                                    <p>Basic Pay</p>
                                                </div>
                                                <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                                    <p>{{amountFormat($salary['basic_salary'])}}</p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                                    <p>Max Hourly Salary</p>
                                                </div>
                                                <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                                    <p>{{amountFormat(bcmul($salary['basic_salary'], $salary['hourly_salary']))}}</p>
                                                </div>
                                            </div>


                                            @if(!empty($salaryHistory))
                                                <div class="container mt-4">
                                                    <h1>Salary History</h1>
                                                    <table class="table table-bordered table-striped">
                                                        <thead class="table-primary">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Set On</th>
                                                            <th>Commencement Date</th>
                                                            <th>Job Grade</th>
                                                            <th>Basic Salary</th>
                                                            <th>Hourly Salary</th>
                                                            <th>SHIF</th>
                                                            <th>Deductions Exempt</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach (@$salaryHistory as $index => $record)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ (new DateTime($record['date']))->format("jS F, Y") }}</td>
                                                                <td>{{ $record['commencement_date'] ? (new DateTime($record['commencement_date']))->format("jS F, Y") : '' }}</td>
                                                                <td>{{ $record['job_grade'] }}</td>
                                                                <td>{{ $record['basic_salary'] }}</td>
                                                                <td>{{ $record['hourly_salary'] }}</td>
                                                                <td>{{ boolval($record['nhif']) ? "Deduct" : 'Exempt'}}</td>
                                                                <td>{{ boolval($record['deduction_exempt']) ? "Exempt" : 'Deducted'}}</td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif




                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.salary.partials.terminate-contract')
    </div>
    @include('focus.salary.partials.add-renew')
@endsection

@section('extra-scripts')
    <script>
        $('#renew_contract').click(function (e) { 
            var name = $('#salary_employee').val();
            //$('#employee').val(name);
        });
    </script>
@endsection
