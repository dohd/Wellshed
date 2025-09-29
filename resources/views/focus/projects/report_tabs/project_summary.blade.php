<div class="tab-pane in" id="tab_data14" aria-labelledby="tab14" role="tabpanel">
    <div class="card">
        <div class="card-head">
            <div class="card-header">
                <h4 class="card-title">{{ gen4tid('Prj-', $project->tid) }} ; {{ $project['name'] }}</h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            </div>
            <div class="px-1">
                <p>{{ $project['short_desc'] }}</p>
                <div class="heading-elements">
                    @foreach ($project->tags as $row)
                        <span class="badge" style="background-color:{{ $row['color'] }}">{{ $row['name'] }}</span>
                    @endforeach
                </div>
                <br>
                <ul class="list-inline list-inline-pipe text-center p-1 border-bottom-grey border-bottom-lighten-3">
                    <li>{{ trans('projects.owner') }}: 
                        <span class="text-muted text-bold-600 blue">{{ @$project->creator->fullname }}</span>
                    </li>
                    <li>{{ trans('customers.customer') }}: 
                        <span class="text-bold-600 primary">
                            @if (@$project->customer)
                            <a href="{{ route('biller.customers.show', $project->customer) }}">
                                {{ @$project->customer->company }}
                            </a>
                            @endif
                        </span>                               
                    </li>
                    <li>Branch: 
                        <span class="text-bold-600 primary">
                            @if (@$project->branch)
                            <a href="{{ route('biller.branches.show', $project->branch) }}">
                                {{ @$project->branch->name }}
                            </a>
                            @endif
                        </span>                               
                    </li>
                    @if ($project->worth > 0.0)
                        <li>{{ trans('projects.worth') }}: 
                            <span class="text-bold-600 primary">{{ amountFormat($project->worth) }}</span>
                        </li>
                    @endif
                </ul>
                <ul class="list-inline list-inline-pipe text-center p-1 border-bottom-grey border-bottom-lighten-3 h5">
                    <li>{{ trans('projects.start_date') }}: 
                        <span class=" text-bold-600 purple">{{ dateTimeFormat($project['start_date']) }}</span>
                    </li>
                    <li>{{ trans('projects.end_date') }}: 
                        <span class="text-bold-600 danger">{{ dateTimeFormat($project['end_date']) }}</span>
                    </li>
                </ul>
                <ul class="list-inline list-inline-pipe text-center border-bottom-grey border-bottom-lighten-3 h5">
                    <li>Project Created At: <span class=" text-bold-600 purple">{{ dateFormat($project->created_at) }}</span></li>
                    <li>Project Ended By: <span class=" text-bold-600 purple">{{ @$project->user->full_name }}</span></li>
                </ul>
                @if ($project->end_note)
                    <ul class="list-inline list-inline-pipe text-center border-bottom-grey border-bottom-lighten-3 h5">
                        <li>Project End Note: 
                            <span class=" text-bold-600 purple">{{ $project->end_note}}</span>
                        </li>
                    </ul>    
                @endif
            </div>
        </div>
        <!-- project-info -->
        <div id="project-info" class="card-body row">
            <div class="project-info-count col-lg-3 col-md-12">
                <div class="project-info-icon">
                    <h2>{{ @count($project->users) }}</h2>
                    <div class="project-info-sub-icon">
                        <span class="fa fa-user-o"></span>
                    </div>
                </div>
                <div class="project-info-text pt-1">
                    <h5>{{ trans('projects.users') }}</h5>
                </div>
            </div>
            <div class="project-info-count col-lg-3 col-md-12">
                <div class="project-info-icon">
                    <h2 id="prog">{{ @numberFormat($project->progress) }}%</h2>
                    <div class="project-info-sub-icon">
                        <span class="fa fa-rocket"></span>
                    </div>
                </div>
                <div class="project-info-text pt-1">
                    <h5>{{ trans('projects.progress') }}</h5>
                    <input type="range" min="0" max="100" value="{{ $project['progress'] }}"
                        class="slider" id="progress">
                </div>
            </div>
            <div class="project-info-count col-lg-3 col-md-12">
                <div class="project-info-icon">
                    <h2>{{ @count($project->tasks) }}</h2>
                    <div class="project-info-sub-icon">
                        <span class="fa fa-calendar-check-o"></span>
                    </div>
                </div>
                <div class="project-info-text pt-1">
                    <h5>{{ trans('tasks.tasks') }}</h5>
                </div>
            </div>
            <div class="project-info-count col-lg-3 col-md-12">
                <div class="project-info-icon">
                    <h2>{{ @count($project->milestones) }}</h2>
                    <div class="project-info-sub-icon">
                        <span class="fa fa-flag-checkered"></span>
                    </div>
                </div>
                <div class="project-info-text pt-1">
                    <h5>{{ trans('projects.milestones') }}</h5>
                </div>
            </div>
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
