<?php

namespace App\Http\Controllers\Focus\project;

use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use App\Models\project\Project;
use App\Models\project\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\task\EditResponse;
use App\Repositories\Focus\project\TaskRepository;
use App\Http\Requests\Focus\project\ManageTaskRequest;
use App\Http\Requests\Focus\project\CreateTaskRequest;
use App\Http\Requests\Focus\project\EditTaskRequest;
use App\Models\project\ProjectMileStone;
use App\Models\project\TaskRelations;
use Illuminate\Support\Facades\DB;

/**
 * TasksController
 */
class TasksController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaskRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TaskRepository $repository ;
     */
    public function __construct(TaskRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\project\ManageTaskRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageTaskRequest $request)
    {
        $mics = Misc::all();
        $employees = Hrm::where('ins', auth()->user()->ins)->get();
        $user = auth()->user()->id;
        $project_select = Project::whereHas('users', fn($q) => $q->where('users.id', '=', $user))->get();

        return new ViewResponse('focus.projects.tasks.index', compact('mics', 'employees', 'project_select'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTaskRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateTaskRequest $request)
    {
        try {
            $result = $this->repository->create($request->except(['_token', 'ins']));
        } catch (\Throwable $th) { 
            return errorHandler('Error Creating Tasks', $th);
        }

        // mail alert
        $feature = feature(11);
        $alert = json_decode($feature->value2, true);
        if ($alert['task_new']) {
            $mail = [
                'mail_to' => $feature->value1,
                'customer_name' => trans('tasks.task'),
                'subject' => trans('tasks.task') . '#' . $result->name,
                'text' => trans('tasks.task') . ' #' . $result->name . '<br>' . trans('tasks.duedate') . '<br> ' . dateTimeFormat($result->duedate) . ' <br> - ' . $result->short_desc,
            ];
            business_alerts($mail);
        }

        $task_users = $result->users->map(fn($v) => $v->full_name)->toArray();
        $task_users = implode(', ', $task_users);

        $task_back = task_status($result->status);
        $status = '<span class="badge" style="background-color:' . $task_back['color'] . '">' . $task_back['name'] . '</span>';

        $tag = '';
        foreach ($result->tags as $row) {
            $tag .= '<span class="badge" style="background-color:'. $row['color'] .'">'. $row['name'] .'</span> ';
        }
        $btn = '<a href="' . route("biller.tasks.edit", [$result->id]) . '" data-toggle="tooltip" data-placement="top" title="View" class="success"><i  class="ft-eye"></i></a> ';
        $btn .= '&nbsp;&nbsp;<a href="' . route("biller.tasks.edit", [$result->id]) . '" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="ft-edit"></i></a>';
        $btn .= '&nbsp;&nbsp;<a class="danger" href="' . route("biller.tasks.destroy", [$result->id]) . '" table-method="delete" data-trans-button-cancel="' . trans('buttons.general.cancel') . '" data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '" data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" data-toggle="tooltip" data-placement="top" title="Delete"> <i  class="fa fa-trash"></i> </a>';

        
        // project task
        if ($result->milestone) {
            $row = '<tr>
                <td>*</td>
                <td>'. @$result->milestone->name. '</td>
                <td><div class="todo-item media"><div class="media-body"><div class="todo-title"><a href="' . route("biller.tasks.show", [$result->id]) . '" >' . $result->name . '</a><div class="float-right">' . $tag . '</div></div><span class="todo-desc">' . $result->short_desc . '</span></div> </div></td>
                <td>' . dateTimeFormat($result->start) . '</td>
                <td>' . dateTimeFormat($result->duedate) . '</td>
                <td>' . $status . '</td>
                <td>' . $task_users . '</td>
                <td>' . $btn . '</td>
            </tr>';            
        } else {
            $row = '<tr>
                <td><div class="todo-item media"><div class="media-body"><div class="todo-title"><a href="' . route("biller.tasks.show", [$result->id]) . '" >' . $result->name . '</a><div class="float-right">' . $tag . '</div></div><span class="todo-desc">' . $result->short_desc . '</span></div> </div></td>
                <td>' . dateTimeFormat($result->start) . '</td><td>' . dateTimeFormat($result->duedate) . '</td>
                <td>' . $status . '</td>
                <td>' . $btn . '</td>
            </tr>';
        }

        return response()->json(['status' => 'Success', 'message' => trans('alerts.backend.tasks.created'), 'title' => $result->name, 'short_desc' => $result->short_desc, 'row' => $row, 't_type' => 3,  'refresh' => 1]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\project\Task $task
     * @param EditTaskRequestNamespace $request
     * @return \App\Http\Responses\Focus\project\EditResponse
     */
    public function edit(Task $task, EditTaskRequest $request)
    {
        return new EditResponse($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTaskRequestNamespace $request
     * @param App\Models\project\Task $task
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(EditTaskRequest $request, Task $task)
    {           
        $input = $request->except(['_token', 'ins']);

        try {
            $project = @$task->milestone->project ?: @$task->project;
            $this->repository->update($task, $input);
            
            if ($project) {
                return new RedirectResponse(route('biller.projects.show', $project), ['flash_success' => trans('alerts.backend.tasks.updated')]);
            }
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Tasks', $th);
        }
        
        return new RedirectResponse(route('biller.tasks.index'), ['flash_success' => trans('alerts.backend.tasks.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteTaskRequestNamespace $request
     * @param App\Models\project\Task $task
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Task $task)
    {
        try {
            $this->repository->delete($task);
        } catch (\Throwable $th) {
            \Log::error($th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
            return response()->json(['status' => 'Error', 'message' => trans('alerts.backend.tasks.deleted')]);
        }

        return response()->json(['status' => 'Success', 'message' => trans('alerts.backend.tasks.deleted')]);
    } 

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteTaskRequestNamespace $request
     * @param App\Models\project\Task $task
     * @return \App\Http\Responses\RedirectResponse
     */
    public function load(ManageTaskRequest $request)
    {
        $task = Task::findOrFail($request->task_id);

        $task_users = $task->users->map(fn($v) => $v->full_name)->toArray();
        $task_back = task_status($task->status);
        $task->fill([
            'start' => dateTimeFormat($task['start']),
            'duedate' => dateTimeFormat($task['duedate']),
            'creator' => $task->creator? $task->creator->full_name : '', 
            'assigned' => implode(', ', $task_users),
            'status' => '<span class="badge" style="background-color:' . $task_back['color'] . '">' . $task_back['name'] . '</span> ',
            'status_list' => '',
            'task_completion' => numberFormat($task->task_completion),
            'project_id' => $task->project_id,
            'task_items' => $task->task_relations,
        ]);

        foreach (status_list() as $row) {
            if ($row['id'] == @$task_back->id) $task->status_list .= '<option value="' . $row['id'] . '" selected>' . $row['name'] . '</option>';
            else $task->status_list .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
        }

        return response()->json($task->only('id', 'name', 'status', 'start', 'duedate', 'description', 'short_desc', 'priority', 'creator', 'assigned', 'status', 'status_list','task_completion','project_id','task_items'));
    }

    public function update_status(Request $request)
    {
        $flag = false;
        if (access()->allow('update-task')) $flag = true;
        if ($request->id and !$flag) {
            $task = Task::find($request->id);
            if ($task->creator_id == auth()->user()->id) $flag = true;
            $assigned = $task->users->where('id', '=', auth()->user()->id)->first();
            if (isset($assigned->id)) {
                $flag = true;
            }
        }
        //Update the model using repository update method
        if ($flag) {
            $task = Task::find($request->id);
            $task->status = $request->sid;
            $task->save();
            $task_back = task_status($task->status);
            $status = '<span class="badge" style="background-color:' . $task_back['color'] . '">' . $task_back['name'] . '</span> ';
            return json_encode(array('status' => $status));
        }
    }

    public function update_task_completion(Request $request)
    {
        try {
            DB::beginTransaction();
            $task = Task::findOrFail($request->task_id);
            if($request->type == 'increment'){
                $task->task_completion += $request->percent_qty;
            }else if($request->type == 'decrement'){
                $task->task_completion -= $request->percent_qty;
            }
            $task->save(); // This will trigger the milestone and project updates
            $data = $request->only(['description','date','task_id','type','percent_qty']);
            $data['date'] = date_for_database($data['date']);
            $data['percent_qty'] = numberClean($data['percent_qty']);
            $data['todolist_id'] = $data['task_id'];
            unset($data['task_id']);
            TaskRelations::create($data);
            updateNewTask($task);
            if($task){
                DB::commit();
            }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return errorHandler('Error Updating Task ', $th);

        }
        return back()->with('flash_success', 'Task and related milestone and project completion percentages updated successfully');
    }

    public function get_tasks(Request $request)
    {
        $tasks = Task::where('milestone_id', $request->milestone_id)->get();
        return response()->json($tasks);
    }
    public function get_milestone(Request $request)
    {
        $milestone = ProjectMileStone::where('id', $request->milestone_id)->first();
        // dd($milestone);
        return response()->json($milestone);
    }

}
