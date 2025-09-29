<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\project;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\project\ProjectRepository;

/**
 * Class ProjectsTableController.
 */
class BudgetsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProjectRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProjectRepository $repository ;
     */
    public function __construct(ProjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function($project) {
                $name = '';
                $customer = $project->customer_project;
                $branch = $project->branch;
                if ($customer && $branch) $name = "{$customer->company} - {$branch->name}";
                elseif ($customer) $name = $customer->company;
                
                return $name;
            })
            ->addColumn('tid', function($project) {
                return gen4tid('Prj-', $project->tid);
            })
            ->addColumn('quote_budget', function($project) {
                $links = '';
                foreach ($project->quotes as $quote) {
                    $tid = gen4tid($quote->bank_id ? 'PI-' : 'QT-', $quote->tid);
                    $status = $quote->budget? 'budgeted' : 'pending';
                    $links .= '<a href="'. route('biller.projects.create_project_budget', $quote). '" data-toggle="tooltip" title="Budget">
                        <b>'. $tid . '</b></a> :'. $status .'<br>';
                }
                
                return $links;
            })
            ->addColumn('lead_tid', function($project) {
                $tids = array();                
                foreach ($project->quotes as $quote) {
                    $tids[] = gen4tid('Tkt-', $quote->lead->reference);
                }
                return implode(', ', $tids);
            })
            ->addColumn('start_date', function ($project) {
                return dateFormat($project->start_date);
            })
            ->addColumn('end_date', function ($project) {
                return dateFormat($project->end_date);
            })
            ->addColumn('status', function ($project) {
                if ($project->misc)
                return ucfirst($project->misc->name);
            })
            ->addColumn('actions', function ($project) {
                return $project->action_buttons;
            })
            ->make(true);
    }
}