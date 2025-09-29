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
namespace App\Http\Controllers\Focus\purchase_requisition;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\purchase_requisition\PurchaseRequisitionRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class PurchaseRequisitionsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseRequisitionRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseRequisitionRepository $repository ;
     */
    public function __construct(PurchaseRequisitionRepository $repository)
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

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('tid', function ($purchase_requisition) {
                if ($purchase_requisition->pr_parent_id) {
                    return gen4tid('PR-', $purchase_requisition->pr_parent->tid) . 'B';
                }elseif ($purchase_requisition->pr_child) {
                    return gen4tid('PR-', $purchase_requisition->tid) . 'A';
                }
                return gen4tid('PR-', $purchase_requisition->tid);
            })
            ->addColumn('date', function ($purchase_requisition) {
                return dateFormat($purchase_requisition->date);
            })
            ->addColumn('employee', function ($purchase_requisition) {
                if ($purchase_requisition->employee) 
                return $purchase_requisition->employee->full_name;
            })
            ->addColumn('mr_no', function ($purchase_requisition) {
                if ($purchase_requisition->purchase_request) {
                    $mrNo = gen4tid('REQ-', $purchase_requisition->purchase_request->tid);
                    $url = route('biller.purchase_requests.show', $purchase_requisition->purchase_request->id); // Adjust route name if different
                    return '<a href="'.$url.'" target="_blank">'.$mrNo.'</a>'; // Link opens in a new tab
                }
            })
            ->addColumn('note', function ($purchase_requisition) {
                if ($purchase_requisition->purchase_request)
                return $purchase_requisition->purchase_request->note;
            })
            ->addColumn('project', function ($purchase_requisition) {
                $purchase_requisition['project_name'] = '';
                if ($purchase_requisition->project){
                    $quote_tid = !$purchase_requisition->project->quote ?: gen4tid('QT-', $purchase_requisition->project->quote->tid);
                    $customer = !$purchase_requisition->project->customer ?: $purchase_requisition->project->customer->company;
                    $branch = !$purchase_requisition->project->branch ?: $purchase_requisition->project->branch->name;
                    $project_tid = gen4tid('PRJ-', $purchase_requisition->project->tid);
                    $project = $purchase_requisition->project->name;
                    $customer_branch = "{$customer}" .'-'. "{$branch}";
                    $purchase_requisition['project_name'] = "[" . $quote_tid ."]"." - " . $customer_branch. " - ".$project_tid." - ".$project;
                }
                return $purchase_requisition['project_name'];
            })
            ->addColumn('expect_date', function ($purchase_requisition) {
                return dateFormat($purchase_requisition->expect_date);
            })
            ->addColumn('actions', function ($purchase_requisition) {
                $btn = "";
                if (!$purchase_requisition->pr_parent_id && $purchase_requisition->status == 'approved') {
                    $btn = '<a href="'.route('biller.purchase_requisitions.create_pr_copy', [$purchase_requisition]).'" class="btn btn-secondary round" data-toggle="tooltip" data-placement="top" onclick="return confirm(\'Are you sure you want to generate Purchase Requisition?\')" title="Copy PR"><i class="fa fa-copy" aria-hidden="true"></i></a>';
                }
                return $btn.$purchase_requisition->action_buttons;
            })
            ->make(true);
    }
}
