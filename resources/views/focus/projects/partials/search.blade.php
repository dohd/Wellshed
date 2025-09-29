<ul>
    @foreach($projects as $project)
    @php
    $project['id'] = $project->id;
    $project['project_name'] = $project->name;
    $project['client_name'] = $project->customer_project->company;
    $project['branch_name'] = $project->branch->name;
    $project['tid'] = $project->tid;
    $project['customer_id'] = $project->customer_project->id;
    $project['branch_id'] = $project->branch->id;

    @endphp
     
        <li onClick="selectProjects({{json_encode($project)}})"><p>{{$project->customer_project->company}} &nbsp;
                 &nbsp {{$project->branch->name}} &nbsp {{ $project->name}} &nbsp {{$project->tid}}</p></li>
    @endforeach
</ul>