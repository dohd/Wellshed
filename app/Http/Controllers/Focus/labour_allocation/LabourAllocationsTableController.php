<?php

namespace App\Http\Controllers\Focus\labour_allocation;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\labour_allocation\LabourAllocationRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class LabourAllocationsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var LabourAllocationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LabourAllocationRepository $repository ;
     */
    public function __construct(LabourAllocationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();
        $prefixes = prefixesArray(['quote', 'proforma_invoice'], auth()->user()->ins);
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('period', function($model) {
                if ($model->period_from && $model->period_to) {
                    return dateFormat($model->period_from) . '<br>' . dateFormat($model->period_to);
                }
            })
            ->addColumn('tid', function ($labour_allocation) use($prefixes) {
                $project = $labour_allocation->project;
                if ($project) {
                    return '<a href="'.route('biller.projects.show', $project).'">'.gen4tid('PRJ-', $project->tid).'</a>';
                }
                return '';
            })
            ->filterColumn('tid', function ($query, $keyword) {
                $query->whereHas('project', function ($q) use ($keyword) {
                    $q->where('tid', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('project_id', function ($labour_allocation) {
                
                return;
            })
            ->addColumn('employee_name', function ($labour_allocation) {
               $names = [];
                foreach ($labour_allocation->items as $item) {
                    if ($item->employee) $names[] = $item->employee->full_name;
                }
                return $names;
            })
            ->addColumn('project_name', function ($labour_allocation) {
                return @$labour_allocation->project->name;
            })
            ->filterColumn('project_name', function ($query, $keyword) {
                $query->whereHas('project', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('customer_branch', function ($labour_allocation) {
                $project = $labour_allocation->project;
                if($project){
                    if($project->quote){
                        $customer = @$project->customer->company;
                        $branch = @$project->branch->name;
                        if ($branch) $customer  .= " - {$branch}";
                        return $customer;
                    }
                }
                
            })
            ->filterColumn('customer_branch', function ($query, $keyword) {
                $query->whereHas('project.customer', function ($q) use ($keyword) {
                    $q->where('company', 'like', "%{$keyword}%");
                })->orWhereHas('project.branch', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('quote_tid', function ($labour_allocation) {
                $quote_tids = [];
                if($labour_allocation->project){
                    $project = $labour_allocation->project;
                    foreach ($project->quotes as $quote) {
                        $tid = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                        $quote_tids[] = '<a href="'. route('biller.quotes.show', $quote) .'">'. $tid .'</a>';
                    }
                }
                return implode(', ', $quote_tids);
            })
            ->filterColumn('quote_tid', function ($query, $keyword) {
                $query->whereHas('project.quotes', function ($q) use ($keyword) {
                    $q->where('tid', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('job_card', function ($labour_allocation) {
                return $labour_allocation->job_card;
            })
            ->filterColumn('job_card', function ($query, $keyword) {
                $query->where('job_card', 'like', "%{$keyword}%");
            })
            ->addColumn('note', function ($labour_allocation) {
                return $labour_allocation->note;
            })
            ->addColumn('date', function ($labour_allocation) {
                return dateFormat($labour_allocation->date);
            })
            ->orderColumn('date', '-date $1')
            ->addColumn('hrs', function ($labour_allocation) {
                return $labour_allocation->hrs;
            })
            ->addColumn('type', function ($labour_allocation) {
                return strtoupper($labour_allocation->type);
            })
            
            ->addColumn('actions', function ($labour_allocation) {
                return $labour_allocation->action_buttons;
            })
            ->make(true);
    }
}
