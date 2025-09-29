<div class="tab-pane in" id="tab_data6" aria-labelledby="tab6" role="tabpanel">
    <div class="card-body">
        <table id="tasksTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Milestome</th>
                    <th>Task</th>
                    <th>Start Date</th>
                    <th>Due Date</th>
                    <th>Task Status</th>
                    <th>Assigned To</th>
                    <th>Task Progress</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $i => $task)
                {{-- {{dd($task)}} --}}
                   @if ($task)
                   <tr>
                    @php
                    
                       $task_back = task_status($task->status);
                       $task_st =  '<span class="badge" style="background-color:'. $task_back['color'] .'">'. $task_back['name'] . '</span> ';
                       $task_users = $task->users->map(fn($v) => $v->full_name)->toArray();
                       $assignes = implode(', ', $task_users)
                    @endphp
                    <td>{{$i+1}}</td>
                    <td>{{@$task->milestone->name}}</td>
                    <td>{{$task->name}}</td>
                    <td>{{dateTimeFormat($task->start)}}</td>
                    <td>{{dateTimeFormat($task->duedate)}}</td>
                    <td>{!! $task_st !!}</td>
                    <td>{{@$assignes}}</td>
                    <td>{{$task->task_completion}}<span>%</span></td>
                </tr>
                   @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>