<?php

namespace App\Repositories\Focus\project;

use App\Models\Access\User\User;
use App\Models\event\Event;
use App\Models\event\EventRelation;
use App\Models\hrm\Hrm;
use App\Models\project\TaskRelations;
use App\Notifications\Rose;
use DB;
use App\Models\project\Task;
use App\Exceptions\GeneralException;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectRelations;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class TaskRepository.
 */
class TaskRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Task::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable($uid = 0)
    {
        $q = $this->query();

        // filter project
        if (request('project_id')) {
            // $q->whereHas('users', fn($q) => $q->where('project_relations.project_id', request('project_id')));
            $q->where(function($q) {
                $q->where('project_id', request('project_id'));
                $q->orWhereHas('milestone', fn($q) => $q->where('project_milestones.project_id', request('project_id')));
            });
        }

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return Task $task
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $project_id = @$input['projects'][0];
        if (!$project_id) trigger_error('Project required!');

        $milestone_id = @$input['milestone_id'];
        if (!$project_id) trigger_error('Milestone required!');

        // task
        $task_input = array_diff_key($input, array_flip(['tags', 'employees', 'projects', 'link_to_calender', 'color']));
        $task_input = array_replace($task_input, [
            'start' => datetime_for_database("{$task_input['start']} {$task_input['time_from']}"),
            'duedate' => datetime_for_database("{$task_input['duedate']} {$task_input['time_to']}"),
            'project_id' => $project_id,
            'creator_id' => auth()->user()->id,
        ]);
        unset($task_input['time_from'], $task_input['time_to']);

        // set duration
        $startdate = strtotime($task_input['start']);
        $duedate = strtotime($task_input['duedate']);
        $diffInSeconds = $duedate - $startdate; 
        $days = abs(round($diffInSeconds / 86400));
        $task_input['duration'] = $days+1;
        $task_input['row_index'] = Task::where('project_id', $task_input['project_id'])->max('row_index')+1;

        $result = Task::create($task_input);

        // log
        $data = ['project_id' => $project_id, 'value' => '[Milestone Task][' . trans('general.create') . '] ' . $result->name, 'user_id' => $result->user_id];
        ProjectLog::create($data);

        // tags
        $tags = @$input['tags'] ?: [];
        $tag_group = array_map(fn($v) => ['misc_id' => $v, 'task_id' => $result->id, 'milestone_id' => $milestone_id, 'project_id' => $project_id], $tags);
        ProjectRelations::insert($tag_group);
        
        // task users
        $employees = @$input['employees'] ?: [];
        $employees_group = array_map(fn($v) => ['user_id' => $v, 'task_id' => $result->id, 'milestone_id' => $milestone_id, 'project_id' => $project_id], $employees);
        ProjectRelations::insert($employees_group);

        if ($result->milestone_id && !$employees && !$tags) {
            ProjectRelations::create(
                ['task_id' => $result->id, 'milestone_id' => $milestone_id, 'project_id' => $project_id]
            );
        }
    
        // calendar link
        if (@$input['link_to_calender']) {
            $data = [
                'title' => trans('tasks.task') . ' - ' . $input['name'],
                'description' => $input['description'], 
                'start' => $result->start, 
                'end' => $result->duedate, 
                'color' => @$input['color'], 
                'user_id' => $result->user_id, 
                'ins' => $result['ins']
            ];
            $event = Event::create($data);
            EventRelation::create(['event_id' => $event->id, 'related' => 2, 'r_id' => $result->id]);
        }
        updateNewTask($result);
        if ($result) {
            DB::commit();

            // user notify
            $message = [
                'title' => trans('tasks.task') . ' - ' . $input['name'], 
                'icon' => 'fa-bullhorn',
                'background' => 'bg-success',
                'data' => $input['description']
            ];
            if ($employees_group) {
                $users = Hrm::whereIn('id', $employees_group)->get();
                \Illuminate\Support\Facades\Notification::send($users, new Rose('', $message));
            } else {
                $notification = new Rose(auth()->user(), $message);
                auth()->user()->notify($notification);
            }

            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Task $task
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Task $task, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        // task
        $task_input = array_diff_key($input, array_flip(['tags', 'employees', 'projects', 'link_to_calender', 'color']));
        $task_input = array_replace($task_input, [
            'start' => datetime_for_database("{$task_input['start']} {$task_input['time_from']}"),
            'duedate' => datetime_for_database("{$task_input['duedate']} {$task_input['time_to']}"),
        ]);
        unset($task_input['time_from'], $task_input['time_to']);
        $prevMilestoneId = @$task->milestone->id;

        // set duration
        $startdate = strtotime($task_input['start']);
        $duedate = strtotime($task_input['duedate']);
        $diffInSeconds = $duedate - $startdate; 
        $days = abs(round($diffInSeconds / 86400));
        $task_input['duration'] = $days+1;

        $task->update($task_input);

        $milestone_id = @$task->milestone->id ?: $task->milestone_id;
        $project_id = @$task->milestone->project->id ?: $task->project_id;

        // tags
        $tags = @$input['tags'] ?: [];
        ProjectRelations::whereNotIn('misc_id', $tags)->where('project_id', $project_id)->where('task_id', $task->id)->delete();
        foreach ($tags as $tag) {
            ProjectRelations::updateOrCreate(
                ['misc_id' => $tag, 'task_id' => $task->id],
                ['misc_id' => $tag, 'task_id' => $task->id, 'milestone_id' => $milestone_id, 'project_id' => $project_id]
            );
        }
        
        // milestone
        ProjectRelations::where(['task_id' => $task->id, 'milestone_id' => $prevMilestoneId, 'misc_id' => null, 'user_id' => null])->delete();
        if ($task->milestone_id) {
            $result1 = ProjectRelations::updateOrCreate(
                ['task_id' => $task->id, 'milestone_id' => $prevMilestoneId, 'misc_id' => null, 'user_id' => null],
                ['task_id' => $task->id, 'milestone_id' => $milestone_id, 'project_id' => $project_id]
            );
        } 

        // task users
        $employees = @$input['employees'] ?: [];
        ProjectRelations::whereNotIn('user_id', $employees)->whereNotNull('user_id')
            ->whereNotNull('task_id')->where('task_id', $task->id)->delete();
        foreach ($employees as $id) {
            ProjectRelations::updateOrCreate(
                ['user_id' => $id, 'task_id' => $task->id],
                ['user_id' => $id, 'task_id' => $task->id, 'milestone_id' => $milestone_id, 'project_id' => $project_id]
            );
        }

        // calendar link
        if (@$input['link_to_calender']) {
            $data = [
                'title' => trans('tasks.task') . ' - ' . $input['name'],
                'description' => $input['description'], 
                'start' => $task['start'], 
                'end' => $task['duedate'], 
                'color' => @$input['color'], 
                'user_id' => $task->user_id, 
                'ins' => $task->ins
            ];
            $event_relation = EventRelation::where('r_id', $task->id)->first();
            if ($event_relation) {
                $event = Event::find($event_relation->event_id);
                if ($event) $event->update($data);
            }
        }
        
        if ($task) {
            DB::commit();
            return $task;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Task $task
     * @return bool
     * @throws GeneralException
     */
    public function delete(Task $task)
    {
        DB::beginTransaction();

        // log
        $project = @$task->milestone->project;
        $data = ['project_id' => @$project->id, 'value' => '[Milestone Task][' . trans('general.delete') . '] ' . $task->name, 'user_id' => $task->user_id];
        ProjectLog::create($data);

        // event 
        $event_relation = EventRelation::where(['related' => 2, 'r_id' => $task->id])->first();
        if ($event_relation) {
            if ($event_relation) $event_relation->event->delete();
            $event_relation->delete();
        }

        if ($task->delete()) {
            DB::commit();
            return true;
        }
    }
}
