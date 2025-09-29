<div class="tab-pane  active in" id="tab_data1" aria-labelledby="tab1" role="tabpanel">
    <div class="card">
        <div class="card-head">
            <div class="card-header">
                <h4 class="card-title">{{ gen4tid('Prj-', $project->tid) }} ; {{ $project['name'] }}</h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            </div>
            <div class="px-1">
                <div class="row form-group">
                    <div class="col-3">

                        <p>{{ $project['short_desc'] }}</p>
                    </div>
                    <div class="col-3">

                        <h4>Overall Project %</h4>
                        <p><b>{{$project['progress']}}%</b></p>
                    </div>
                </div>
                {{-- <div class="heading-elements">
                    @foreach ($project->tags as $row)
                        <span class="badge" style="background-color:{{ $row['color'] }}">{{ $row['name'] }}</span>
                    @endforeach
                </div>
                <br> --}}


                
            </div>
        </div>
        <!-- project-info -->
        
        <div class="card-body">
            <legend>Overall Project Report</legend>
            <table class="table table-striped table-nobordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Client Name & Branch</th>
                        <th>Project No & Name</th>
                        <th>Project Start Date</th>
                        <th>Project End Date</th>
                        <th>Number of Milestones</th>
                        <th>Overall Project Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span class="text-bold primary">
                                @if (@$project->customer)
                                <a href="{{ route('biller.customers.show', $project->customer) }}">
                                    {{ @$project->customer->company }}
                                </a>
                                @endif
                            </span> &
                            <span class="text-bold-600 primary">
                                @if (@$project->branch)
                                <a href="{{ route('biller.branches.show', $project->branch) }}">
                                    {{ @$project->branch->name }}
                                </a>
                                @endif
                            </span>
                        </td>
                        <td>
                            {{ gen4tid('Prj-', $project->tid) }} ; {{ $project['name'] }}
                        </td>
                        <td><p class="text-bold-600 purple">{{ dateTimeFormat($project['start_date']) }}</p></td>
                        <td>
                            <p class="text-bold-600 danger">{{ dateTimeFormat($project['end_date']) }}</p>
                        </td>
                        <td>{{ @count($project->milestones) }}</td>
                        <td>{{ @$project->misc->name }}</td>
                    </tr>
                </tbody>
            </table>
            @if (count($project->milestones) > 0)
            <legend class="mt-2">Project Milestone</legend>
            <table class="table table-striped table-nobordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actual Cost to date</th>
                        <th>Budgeted Amount</th>
                        <th>Unused Budget Amount</th>
                        <th>% Cost Used</th>
                        <th>% Work Progress</th>
                        <th>Start date / End date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->milestones as $milestone)
                        <tr>
                            @php
                                $date = dateFormat($milestone->start_date) . '/' . dateFormat($milestone->end_date);
                                $expense_amount = $milestone->amount - $milestone->balance;
                                $expense_percent = div_num($expense_amount, $milestone->amount)*100
                            @endphp
                            <td>{{$milestone->name}}</td>
                            
                            <td>{{numberFormat($expense_amount)}}</td>
                            
                            <td>{{numberFormat($milestone->amount)}}</td>
                            
                            <td>{{numberFormat($milestone->balance)}}</td>
                            
                            <td>{{numberFormat($expense_percent)}}</td>
                            
                            <td>{{$milestone->milestone_completion}}</td>
                            <td>{{$date}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                
            @endif

            @if (count($project->milestones) > 0)
            <legend class="mt-2">Task Project (For Milestone)</legend>
            <table class="table table-striped table-nobordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Milestone</th>
                        <th>% Progress</th>
                        <th>Assigned To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->milestones as $milestone)
                    
                        @foreach ($milestone->tasks as $task)
                            @php
                                $task_users = $task->users;
                                $users = '';
                                if(count($task_users) > 0){
                                    foreach ($task_users as $user) {
                                        $users .= $user->fullname .',';
                                    }
                                }
                            @endphp
                            <tr>
                                <td>{{@$task->name}}</td>
                                <td>{{@$task->milestone->name}}</td>
                                <td>{{@$task->task_completion}}</td>
                                <td>{{@$users}}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
                
            @endif
            @if (count($project->users) > 0)
            <legend class="mt-2">Project Users</legend>
            <table class="table table-striped table-nobordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->users as $user)
                        <tr>
                            <td>{{$user->fullname}}</td>
                            <td>{{@$user->role->name}}</td>
                            <td>{{@$user->meta->primary_contact}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                
            @endif

            <legend class="mt-2">Other Details</legend>
            <table class="table table-striped table-nobordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Project Created By</th>
                        <th>Project Created At</th>
                        <th>Project Ended By</th>
                    </tr>
                </thead>
                <tbody>
                   <tr>
                        <td>{{ @$project->creator->fullname }}</td>
                        <td><span class=" text-bold-600 purple">{{ dateFormat($project->created_at) }}</span</td>
                        <td><span class=" text-bold-600 purple">{{ @$project->user->full_name }}</span></td>
                   </tr>
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <div class="card-subtitle line-on-side text-muted text-center font-small-3 mx-2 my-1">
                <span>{{ trans('projects.eagle_view') }}</span>
            </div>
        </div>
    </div>
    <section class="row">
        <div class="col-xl-12 col-lg-12 col-md-12">
            <!-- Project Overview -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('general.description') }}</h4>
                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="list-inline mb-0">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="close"><i class="ft-x"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        {{ $project->note }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
