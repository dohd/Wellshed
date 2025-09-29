<div class="tab-pane" id="tab_data2" aria-labelledby="tab2" role="tabpanel">
    {{-- @if(project_access($project->id)) --}}
        {{-- <button type="button" class="btn btn-info" id="addMilestone" data-toggle="modal" data-target="#AddMileStoneModal">
            <i class="fa fa-plus-circle"></i> Add Budget Line
        </button> --}}
    {{-- @endif --}}
    @if (!auth()->user()->customer_id)
        <a href="{{route('biller.milestones.create_milestone', ['project_id'=> $project->id])}}" class="btn btn-info">
            <i class="fa fa-plus-circle"></i> Add Budget Line / Milestone
        </a>
    @endif
    <ul class="timeline">
        @php
            $flag = true;
            $total = count($project->milestones);
        @endphp
        @foreach ($project->milestones as $row)
            <li class="{!! (!$flag)? 'timeline-inverted' : '' !!}" id="m_{{$row['id']}}">
                <div class="timeline-badge" style="background-color:@if ($row['color']) {{$row['color']}} @else #0b97f4  @endif;">
                    {{$total}}
                </div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4 class="timeline-title">{{$row['name']}}</h4>
                        <p>
                            <small class="text-muted">
                                [{{trans('general.due_date')}} {{dateTimeFormat($row['due_date'])}}]
                            </small>
                        </p>
                    </div>
                    {{-- @if (project_access($project->id)) --}}
                        <div class="timeline-body mb-1">
                            @php
                                $expensed = $row['amount'] - $row['balance'];
                            @endphp
                            <p>{{$row['note']}}</p>
                            <p>Amount: <b>{{ amountFormat($row['amount']) }}</b></p>
                            <p>Expensed Amount: <b>{{ amountFormat($expensed) }}</b></p>
                            <p>Balance: <b>{{ amountFormat($row['balance']) }}</b></p>
                            <p>% of Milestone: <b>{{ numberFormat($row['milestone_expected_percent']) }}%</b></p>
                            <p>Milestone Completion %: <b>{{ numberFormat($row['milestone_completion']) }}%</b></p>
                        </div>
                    {{-- @endif --}}
                    <small class="text-muted"><i class="fa fa-user"></i>
                        <strong>{{ @$row->creator->fullname }}</strong>
                        <i class="fa fa-clock-o"></i> {{trans('general.created')}} {{dateTimeFormat($row['created_at'])}}
                    </small>
                    <div>
                        <div class="btn-group float-right">
                            {{-- <button class="btn btn-link milestone-edit" obj-type="2" data-id="{{$row['id']}}" data-url="{{ route('biller.projects.edit_meta') }}">
                                <i class="ft ft-edit" style="font-size: 1.2em"></i>
                            </button> --}}
                            <a href="{{route('biller.milestones.edit',['milestone'=>$row])}}" class="btn btn-link mr-1" style="padding:0;height:2em;margin-top:8px;">
                                <i class="ft ft-edit ft-lg info" style="font-size:2em"></i>
                            </a>
                            <button class="btn btn-link milestone-del" obj-type="2" data-id="{{$row['id']}}" data-url="{{ route('biller.projects.delete_meta') }}">
                                <i class="fa fa-trash fa-lg danger" style="font-size:2em"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </li>
            @php
                $flag = !$flag;
                $total--;
            @endphp
        @endforeach
    </ul>

    <div class="form-group row">
        <div class="col-4">
            <label for="">Select Milestone</label>
            <select name="milestone_id" id="milestone_id" class="form-control" data-placeholder="Search Milestone">
                <option value="">Search Milestone</option>
                @foreach ($budgetLines as $milestone)
                    <option value="{{$milestone->id}}">{{$milestone->name}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card-body">
        <table id="milestonesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                @if (auth()->user()->customer_id)
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Product Code</th>
                        <th>UoM</th>
                        <th>Allocated Qty</th>
                        <th>Unallocated Qty</th>
                        <th>Selling Price</th>
                    </tr>
                @else
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Product Code</th>
                        <th>UoM</th>
                        <th>Allocated Qty</th>
                        <th>Unallocated Qty</th>
                        <th>Purchase Price</th>
                        <th>Selling Price</th>
                        <th>Difference (SP - BP)</th>
                        <th>Percentage</th>
                    </tr>
                @endif
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>