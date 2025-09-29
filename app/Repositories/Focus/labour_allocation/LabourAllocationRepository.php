<?php

namespace App\Repositories\Focus\labour_allocation;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Focus\casual\CasualLabourerAllocation;
use App\Models\labour_allocation\CasualWeeklyHr;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\project\Project;
use App\Models\project\ProjectMileStone;
use App\Models\project\Task;
use App\Models\project\TaskRelations;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;

class LabourAllocationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = LabourAllocation::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('client_id'), function ($q) {
            $q->whereHas('project', fn ($q) =>  $q->where('customer_id', request('client_id')));
        });
        $q->when(request('employee_id'), function ($q) {
            $q->whereHas('items', fn ($q) =>  $q->where('employee_id', request('employee_id')));
        });
        $q->when(request('casual_id'), function ($q) {
            $q->whereHas('casualLabourers', fn ($q) =>  $q->where('casual_labourers.id', request('casual_id')));
        });
        $q->when(request('labour_month'), function ($q) {
            $params = explode('-', request('labour_month'));
            if (count($params) == 2) {
                $q->whereMonth('date', $params[0])->whereYear('date', $params[1]);
            }
        });


        if (!request('labour_month')) {
            $q->limit(500);
        }

        $q->with(['items.employee' => function($q) {
            $q->withoutGlobalScopes(['status']);
        }]);
        
        return $q;
    }
    
    /**
     * Employee Labour Report
     * 
     * */
    public function getForEmployeeSummary()
    {
        $q = LabourAllocationItem::query();

        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        });
        $q->when(request('employee_id'), fn($q) => $q->where('employee_id', request('employee_id')));
        // $q->when(request('employee_id'), fn($q) => $q->where('employee_id', request('employee_id')));
        $q->when(request('labour_month'), function($q) {
            $dates = explode('-', request('labour_month'));
            if (count($dates) == 2 && intval(@$dates[0]) && intval(@$dates[1])) {
                $q->whereMonth('date', $dates[0])->whereYear('date', $dates[1]);
            }
        });

        if (!request('labour_month')) {
            $q->limit(500);
        }

        $q->with(['employee' => function($q) {
            $q->withoutGlobalScopes(['status']);
        }]);
        
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return LabourAllocation $labour_allocation
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        
        $data = $input['data'];
        $data['date'] = date_for_database($data['date']);
        $data['hrs'] = numberClean($data['hrs']); 
        $data['is_payable'] = isset($data['is_payable']) ? (int) $data['is_payable'] : 1;
        $data['project_milestone'] = isset($data['project_milestone']) ? (int) $data['project_milestone'] : 0;  
        $data['period_from'] = @$data['period_from']? date_for_database($data['period_from']) : null;
        $data['period_to'] = @$data['period_to']? date_for_database($data['period_to']) : null; 
        if (strtotime($data['date']) > strtotime(date('Y-m-d'))) {
            throw ValidationException::withMessages(['date' => 'Future date not allowed']);
        }
        
        // create labour allocation
        $labourAllocation = LabourAllocation::create($data);
        
        // create labour allocation item (employee)
        $data_items = $input['data_items'];
        $data_items = array_filter($data_items, fn($v) => @$v['employee_id']);
        $data_items = array_map(function ($v) use($labourAllocation) {
            return array_replace($v, [
                'ref_type' => $labourAllocation['ref_type'],
                'labour_id' => $labourAllocation['id'],
                'date' => $labourAllocation['date'],
                'type' => $labourAllocation['type'],
                'hrs' => $labourAllocation['hrs'],
                'note' => $labourAllocation['note'],
                'is_payable' => $labourAllocation['is_payable'],
                'user_id' => $labourAllocation['user_id'],
                'ins' => $labourAllocation['ins'],
            ]);
        }, $data_items);
        $result = LabourAllocationItem::insert($data_items);

        // create allocation of casuals
        $input['casualLabourers'] = array_values($input['casualLabourers']);
        foreach ($input['casualLabourers'] as $id){
            CasualLabourerAllocation::create([
                'lacl_number' => uniqid("LACL-"),
                'labour_allocation_id' => $labourAllocation->id,
                'casual_labourer_id' => $id,
            ]);
        }

        // create casual weekly hours
        $isCasualPeriod = isset($data['period_from'], $data['period_to']);
        $hasCasualHrs = array_filter(@$input['casualsHrs']['total_hrs'] ?: []);
        if ($isCasualPeriod && $hasCasualHrs) {
            $regHours = [];
            $otHours = [];
            $totalHours = @$input['casualsHrs']['total_hrs'] ?: [];
            foreach ($totalHours as $hrs) {
                $regHours[] = array_splice($input['casualsHrs']['regular_hrs'], 0, 7);
                $otHours[] = array_splice($input['casualsHrs']['overtime_hrs'], 0, 7);
            }
            $totalRegHours = @$input['casualsHrs']['total_reg_hrs'] ?: [];
            $totalOtHours = @$input['casualsHrs']['total_ot_hrs'] ?: [];
            foreach ($totalHours as $key => $hrs) {
                if (array_filter($totalRegHours)) {
                    $casualWeeklyHr = new CasualWeeklyHr([
                        'labour_allocation_id' => $labourAllocation->id,
                        // 'casual_labourer_id' => $input['casualsHrs']['casual_labourer_id'][$key],
                        'casual_labourer_id' => $input['casualLabourers'][$key],
                        'total_reg_hrs' => numberClean($input['casualsHrs']['total_reg_hrs'][$key]),
                        'total_hrs' => numberClean($input['casualsHrs']['total_hrs'][$key]),
                    ]);
                    $casualWeeklyHr->fill(array_combine(
                        ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],  
                        array_map(fn($v) => numberClean($v), $regHours[$key])
                    ));                
                    $casualWeeklyHr->save();
                }
                if (array_filter($totalOtHours)) {
                    $casualWeeklyHr = new CasualWeeklyHr([
                        'labour_allocation_id' => $labourAllocation->id,
                        // 'casual_labourer_id' => $input['casualsHrs']['casual_labourer_id'][$key],
                        'casual_labourer_id' => $input['casualLabourers'][$key],
                        'total_ot_hrs' => numberClean($input['casualsHrs']['total_ot_hrs'][$key]),
                        'total_hrs' => numberClean($input['casualsHrs']['total_hrs'][$key]),
                        'is_overtime' => 1,
                    ]);
                    $casualWeeklyHr->fill(array_combine(
                        ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], 
                        array_map(fn($v) => numberClean($v), $otHours[$key])
                    ));                
                    $casualWeeklyHr->save();
                }
            }
            if ($totalRegHours || $totalOtHours) {
                $labourAllocation->update([
                    'hrs' => array_sum($totalRegHours),
                    'overtime_hrs' => array_sum($totalOtHours),
                ]);
            }
        }

        // update project tasks
        $this->updateProjectTask($input, $labourAllocation, 'create');
                
        if ($result) {
            DB::commit();
            return $result; 
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param LabourAllocation $labour_allocation
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(LabourAllocation $labour_allocation, array $input)
    {
        // dd($input);
        DB::beginTransaction();
        
        $data = $input['data'];
        $data['is_payable'] = isset($data['is_payable']) ? (int) $data['is_payable'] : 1;
        $data['project_milestone'] = isset($data['project_milestone']) ? (int) $data['project_milestone'] : 0;  
        $data['date'] = date_for_database($data['date']);
        $data['hrs'] = numberClean($data['hrs']);    
        $data['period_from'] = @$data['period_from']? date_for_database($data['period_from']) : null;
        $data['period_to'] = @$data['period_to']? date_for_database($data['period_to']) : null; 
        if (strtotime($data['date']) > strtotime(date('Y-m-d'))) {
            throw ValidationException::withMessages(['date' => 'Future date not allowed']);
        }  

        // update labour allocation
        $result = $labour_allocation->update($data);
        $labourAllocation = $labour_allocation;
        
        // create allocation items (employee)
        $labour_allocation->items()->delete();
        $data_items = $input['data_items'];
        $data_items = array_filter($data_items, fn($v) => @$v['employee_id']);
        $data_items = array_map(function ($v) use($labourAllocation) {
            return array_replace($v, [
                'ref_type' => $labourAllocation['ref_type'],
                'labour_id' => $labourAllocation['id'],
                'date' => $labourAllocation['date'],
                'type' => $labourAllocation['type'],
                'hrs' => $labourAllocation['hrs'],
                'note' => $labourAllocation['note'],
                'is_payable' => $labourAllocation['is_payable'],
                'user_id' => $labourAllocation['user_id'],
                'ins' => $labourAllocation['ins'],
            ]);
        }, $data_items);
        LabourAllocationItem::insert($data_items);

        // create allocations of casuals
        $labourAllocation->casualLabourers()->detach();
        foreach ($input['casualLabourers'] as $id) {
            CasualLabourerAllocation::create([
                'lacl_number' => uniqid("LACL-"),
                'labour_allocation_id' => $labour_allocation->id,
                'casual_labourer_id' => $id,
            ]);
        }

        // create casual weekly hours
        $labourAllocation->casualWeeklyHrs()->delete();
        $isCasualPeriod = isset($data['period_from'], $data['period_to']);
        $hasCasualHrs = array_filter(@$input['casualsHrs']['total_hrs'] ?: []);
        if ($isCasualPeriod && $hasCasualHrs) {
            $regHours = [];
            $otHours = [];
            $totalHours = @$input['casualsHrs']['total_hrs'] ?: [];
            foreach ($totalHours as $hrs) {
                $regHours[] = array_splice($input['casualsHrs']['regular_hrs'], 0, 7);
                $otHours[] = array_splice($input['casualsHrs']['overtime_hrs'], 0, 7);
            }
            // dd($regHours, $otHours);
            $totalRegHours = @$input['casualsHrs']['total_reg_hrs'] ?: [];
            $totalOtHours = @$input['casualsHrs']['total_ot_hrs'] ?: [];
            foreach ($totalHours as $key => $hrs) {
                if (array_filter($totalRegHours)) {
                    $casualWeeklyHr = new CasualWeeklyHr([
                        'labour_allocation_id' => $labourAllocation->id,
                        'casual_labourer_id' => $input['casualsHrs']['casual_labourer_id'][$key],
                        'total_reg_hrs' => numberClean($input['casualsHrs']['total_reg_hrs'][$key]),
                        'total_hrs' => numberClean($input['casualsHrs']['total_hrs'][$key]),
                    ]);
                    $casualWeeklyHr->fill(array_combine(
                        ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],  
                        array_map(fn($v) => numberClean($v), $regHours[$key])
                    ));                
                    $casualWeeklyHr->save();
                }
                if (array_filter($totalOtHours)) {
                    $casualWeeklyHr = new CasualWeeklyHr([
                        'labour_allocation_id' => $labourAllocation->id,
                        'casual_labourer_id' => $input['casualsHrs']['casual_labourer_id'][$key],
                        'total_ot_hrs' => numberClean($input['casualsHrs']['total_ot_hrs'][$key]),
                        'total_hrs' => numberClean($input['casualsHrs']['total_hrs'][$key]),
                        'is_overtime' => 1,
                    ]);
                    $casualWeeklyHr->fill(array_combine(
                        ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], 
                        array_map(fn($v) => numberClean($v), $otHours[$key])
                    ));                
                    $casualWeeklyHr->save();
                }
            }
            if ($totalRegHours || $totalOtHours) {
                $labourAllocation->update([
                    'hrs' => array_sum($totalRegHours),
                    'overtime_hrs' => array_sum($totalOtHours),
                ]);
            }
        }

        // update project tasks
        $this->updateProjectTask($input, $labour_allocation, 'update');

        if ($result) {
            DB::commit();
            return $result;
        }        
    }

    /**
     * For deleting the respective model from storage
     *
     * @param LabourAllocation $labour_allocation
     * @throws GeneralException
     * @return bool
     */
    public function delete(LabourAllocation $labour_allocation)
    {   
        DB::beginTransaction();

        $project = Project::find($labour_allocation->project_id);
        updateCompletionPercentage(0, $project);

        $labour_allocation->items()->delete();
        $labour_allocation->casualLabourers()->detach();
        $labour_allocation->casualWeeklyHrs()->delete();
        
        if ($labour_allocation->delete()) {
            DB::commit();
            return true;
        }
    }

    /** 
     * Update Project Task 
     * */
    public function updateProjectTask($input, $labourAllocation, $method)
    {
        $data = $input['data'];
        $task_item = $input['task'];
        $task = Task::find($task_item['task_id']);

        if ($method == 'create') {
            if($task){
                if ($task_item['percent_type'] == 'increment'){
                    $task->task_completion += $task_item['percent_qty'];
                } else if ($task_item['percent_type'] == 'decrement'){
                    $task->task_completion -= $task_item['percent_qty'];
                }
                $task->save(); // This will trigger the milestone and project updates
                $task_item['description'] = $task_item['note'];
                $task_item['type'] = $task_item['percent_type'];
                $task_item['date'] = date_for_database($task_item['date']);
                $task_item['percent_qty'] = numberClean($task_item['percent_qty']);
                $task_item['todolist_id'] = $task_item['task_id'];
                $task_item['labour_id'] = $labourAllocation->id;
                unset($task_item['task_id'], $task_item['note'],$task_item['percent_type']);
                TaskRelations::create($task_item);
                updateNewTask($task);
            } else {
                if (is_numeric($data['project_milestone']) && $data['project_milestone'] > 0) {
                    $milestone = ProjectMileStone::find($data['project_milestone']);
                    if ($task_item['percent_type']) {
                        if ($task_item['percent_type'] == 'increment') {
                            $milestone->milestone_completion += $task_item['percent_qty'];
                        } else if ($task_item['percent_type'] == 'decrement'){
                            $milestone->milestone_completion -= $task_item['percent_qty'];
                        }
                        $task_item['description'] = $task_item['note'];
                        $task_item['type'] = $task_item['percent_type'];
                        $task_item['date'] = date_for_database($task_item['date']);
                        $task_item['percent_qty'] = numberClean($task_item['percent_qty']);
                        $task_item['labour_id'] = $labourAllocation->id;
                        unset($task_item['task_id'], $task_item['note'],$task_item['percent_type']);
                        TaskRelations::create($task_item);
                        // dd($milestone->milestone_completion);
                        if ($milestone->milestone_completion > 100) $milestone->milestone_completion = 100;
                        elseif ($milestone->milestone_completion < 0) $milestone->milestone_completion = 0;
                        // dd($milestone);
                        $milestone->update();
                        $project = Project::find($data['project_id']); 
                        if ($milestone) updateCompletionPercentage($milestone, $project);
                    }
                } else {
                    $project = Project::find($data['project_id']);
                    if ($task_item['percent_type']) {
                        if ($task_item['percent_type'] == 'increment') {
                            $project->progress += $task_item['percent_qty'];
                        } else if ($task_item['percent_type'] == 'decrement'){
                            $project->progress -= $task_item['percent_qty'];
                        }
                        $task_item['description'] = $task_item['note'];
                        $task_item['type'] = $task_item['percent_type'];
                        $task_item['date'] = date_for_database($task_item['date']);
                        $task_item['percent_qty'] = numberClean($task_item['percent_qty']);
                        $task_item['labour_id'] = $labourAllocation->id;
                        unset($task_item['task_id'], $task_item['note'],$task_item['percent_type']);
                        TaskRelations::create($task_item);
                        if ($project->progress > 100) $project->progress = 100;
                        else if ($project->progress < 0) $project->progress = 0;
                        $project->update();
                    }
                }
            }
        }

        if ($method == 'update') {
            if ($task){
                $task_relation = TaskRelations::where('todolist_id', $task_item['task_id'])->where('labour_id',$labourAllocation->id)->first();
                if($task_item['percent_type'] == 'increment' && $task_relation->type == 'increment'){
                    $percent = numberClean($task_item['percent_qty']) - $task_relation->percent_qty;
                    $task->task_completion += $percent;
                }else if($task_item['percent_type'] == 'decrement' && $task_relation->type == 'decrement'){
                    $percent = numberClean($task_item['percent_qty']) - $task_relation->percent_qty;
                    $task->task_completion -= $percent;
                }else if($task_relation->type == 'increment' && $task_item['percent_type'] == 'decrement'){
                    $task->task_completion -= numberClean($task_item['percent_qty']);
                }else if($task_relation->type == 'decrement' && $task_item['percent_type'] == 'increment'){
                    $task->task_completion += numberClean($task_item['percent_qty']);
                }
                $task->save(); // This will trigger the milestone and project updates
                //Find task items
               

                $task_item['description'] = $task_item['note'];
                $task_item['type'] = $task_item['percent_type'];
                $task_item['date'] = date_for_database($task_item['date']);
                $task_item['percent_qty'] = numberClean($task_item['percent_qty']);
                $task_item['todolist_id'] = $task_item['task_id'];
                $task_item['labour_id'] = $labourAllocation->id;
                unset($task_item['task_id'], $task_item['note'],$task_item['percent_type']);
                $task_relation->update($task_item);
                // TaskRelations::create($task_item);
                updateNewTask($task);
            } else {
                if(is_numeric($data['project_milestone']) && $data['project_milestone'] > 0){
                    $milestone = ProjectMileStone::find($data['project_milestone']);
                    if($task_item['percent_type']){
                        $task_relation = TaskRelations::where('todolist_id', 0)->where('labour_id',$labourAllocation->id)->first();
                        if($task_item['percent_type'] == 'increment' && $task_relation->type == 'increment'){
                            $percent = numberClean($task_item['percent_qty']) - $task_relation->percent_qty;
                            $milestone->milestone_completion += $percent;
                        }else if($task_item['percent_type'] == 'decrement' && $task_relation->type == 'decrement'){
                            $percent = numberClean($task_item['percent_qty']) - $task_relation->percent_qty;
                            $milestone->milestone_completion -= $percent;
                        }else if($task_relation->type == 'increment' && $task_item['percent_type'] == 'decrement'){
                            $milestone->milestone_completion -= numberClean($task_item['percent_qty']);
                        }else if($task_relation->type == 'decrement' && $task_item['percent_type'] == 'increment'){
                            $milestone->milestone_completion += numberClean($task_item['percent_qty']);
                        }
                        // dd($milestone->milestone_completion);
                        if($milestone->milestone_completion > 100) $milestone->milestone_completion = 100;
                        elseif($milestone->milestone_completion < 0) $milestone->milestone_completion = 0;
                        $milestone->update();
                        $project = Project::find($labourAllocation['project_id']); 
                        
                        $task_item['description'] = $task_item['note'];
                        $task_item['type'] = $task_item['percent_type'];
                        $task_item['date'] = date_for_database($task_item['date']);
                        $task_item['percent_qty'] = numberClean($task_item['percent_qty']);
                        $task_item['todolist_id'] = 0;
                        $task_item['labour_id'] = $labourAllocation->id;
                        unset($task_item['task_id'], $task_item['note'],$task_item['percent_type']);
                        // TaskRelations::create($task_item);
                        $task_relation->update($task_item);
                        if($milestone) updateCompletionPercentage($milestone, $project);
                    }
                }else{
                    $project = Project::find($labourAllocation['project_id']);
                    if($task_item['percent_type']){
                        $task_relation = TaskRelations::where('todolist_id', 0)->where('labour_id',$labourAllocation->id)->first();
                        if($task_item['percent_type'] == 'increment' && $task_relation->type == 'increment'){
                            $percent = numberClean($task_item['percent_qty']) - $task_relation->percent_qty;
                            $project->progress += $percent;
                        }else if($task_item['percent_type'] == 'decrement' && $task_relation->type == 'decrement'){
                            $percent = numberClean($task_item['percent_qty']) - $task_relation->percent_qty;
                            $project->progress -= $percent;
                        }else if($task_relation->type == 'increment' && $task_item['percent_type'] == 'decrement'){
                            $project->progress -= numberClean($task_item['percent_qty']);

                        }else if($task_relation->type == 'decrement' && $task_item['percent_type'] == 'increment'){
                            $project->progress += numberClean($task_item['percent_qty']);
                        }
                        if($project->progress > 100) $project->progress = 100;
                        elseif($project->progress < 0) $project->progress = 0;
                        $project->update();
                        
                        $task_item['description'] = $task_item['note'];
                        $task_item['type'] = $task_item['percent_type'];
                        $task_item['date'] = date_for_database($task_item['date']);
                        $task_item['percent_qty'] = numberClean($task_item['percent_qty']);
                        $task_item['todolist_id'] = 0;
                        $task_item['labour_id'] = $labourAllocation->id;
                        unset($task_item['task_id'], $task_item['note'],$task_item['percent_type']);
                        $task_relation->update($task_item);
                    }
                }
            }
        }
    }
}
