<?php

namespace App\Http\Controllers\Focus\project;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\project\TaskRepository;
use App\Http\Requests\Focus\project\ManageTaskRequest;

/**
 * Class TasksTableController.
 */
class TasksTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaskRepository
     */
    protected $task;

    /**
     * contructor to initialize repository object
     * @param TaskRepository $task ;
     */
    public function __construct(TaskRepository $task)
    {
        $this->task = $task;
    }

    /**
     * This method return the data of the model
     * @param ManageTaskRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->task->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('name', function ($task) {
                if ($task->item_type != 'task') return '<b>'. $task->name .'</b>';
                return $task->name;
            })
            ->addColumn('milestone', function ($task) {
                return @$task->milestone->name;
            })
            ->addColumn('tags', function ($task) {
                $tags = '';
                foreach ($task->tags as $row) {
                    $tags .= '<span class="badge" style="background-color:' . $row['color'] . '">' . $row['name'] . '</span> ';
                }
                return $task->name . '<br><div>' . $tags . '</div>';
            })
            ->addColumn('start', function ($task) {
                if ($task->item_type != 'task') return '<b>'. date('d M Y', strtotime($task->start)) .'</b>';
                return '<span  class="font-size-small">'. date('d M Y', strtotime($task->start)) .'</span>';
            })
            ->addColumn('duration', function ($task) {
                if ($task->item_type != 'task') return '<b>'. $task->duration .'</b>';
                return '<span  class="font-size-small">'. $task->duration .'</span>';
            })
            ->addColumn('duedate', function ($task) {
                if ($task->item_type != 'task') return '<b>'. date('d M Y', strtotime($task->duedate)) .'</b>';
                return '<span  class="font-size-small">'. date('d M Y', strtotime($task->duedate)) .'</span>';
            })
            ->addColumn('status', function ($task) {
                $task_back = task_status($task->status);
                if (!$task_back) return '';
                return '<span class="badge" style="background-color:'. $task_back['color'] .'">'. $task_back['name'] . '</span> ';
            })
            ->addColumn('assigned_to', function ($task) {
                $task_users = $task->users->map(fn($v) => $v->full_name)->toArray();
                return implode(', ', $task_users);
            })
            ->addColumn('task_completion', function ($task) {
                if ($task->item_type == 'task')
                return '
                    <div>
                        <div class="project-info-icon">
                            <div class="project-info-sub-icon">
                                <span class="fa fa-rocket"></span>
                            </div>
                            <h2 id="prog">'.$task->task_completion.'%</h2>
                        </div>                    
                    </div>
                ';
                // <div class="project-info-text pt-1">
                //         <h5></h5>
                //         <input type="range" min="0" max="100" data-id="'.$task->id.'" value="'.$task->task_completion.'" class="slider task_progress" id="task_progress">
                //     </div>
            })

            ->addColumn('actions', function ($task) {
                $btn = '<a href="#" title="View" class="view_task success" data-toggle="modal" data-target="#ViewTaskModal" data-id="'. $task->id .'">
                    <i class="ft-eye" style="font-size:1.5em;"></i></a> ';
                if (access()->allow('edit-task')) 
                    $btn .= '&nbsp;&nbsp;<a href="'. route("biller.tasks.edit", [$task->id]) . '" data-toggle="tooltip" data-placement="top" title="Edit">
                        <i class="ft-edit" style="font-size:1.2em;"></i></a>';                
                if (access()->allow('delete-task')) 
                    $btn .= '&nbsp;&nbsp;<a class="danger" href="' . route("biller.tasks.destroy", [$task->id]) . '" table-method="delete" data-trans-button-cancel="' 
                        .trans('buttons.general.cancel') . '" data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '" data-trans-title="'
                        .trans('strings.backend.general.are_you_sure') .'"title="Delete"> <i  class="fa fa-trash fa-lg"></i> </a>';
                // $btn.= '<a href="#" title="Progress" class="progress_task success" data-toggle="modal" data-target="#ProgressTaskModal" data-id="'. $task->id .'">
                //     <i class="fa fa-rocket"></i></a> ';
                        
                return $btn;
            })
            ->make(true);
        
    }
}    
