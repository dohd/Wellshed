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
namespace App\Http\Controllers\Focus\queuerequisition;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\queuerequisition\QueueRequisitionRepository;
//use App\Http\Requests\Focus\queuerequisition\ManagequeuerequisitionRequest;

/**
 * Class queuerequisitionsTableController.
 */
class QueueRequisitionTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var queuerequisitionRepository
     */
    protected $queuerequisition;

    /**
     * contructor to initialize repository object
     * @param queuerequisitionRepository $queuerequisition ;
     */
    public function __construct(QueueRequisitionRepository $queuerequisition)
    {
        $this->queuerequisition = $queuerequisition;
    }

    /**
     * This method return the data of the model
     * @param ManagequeuerequisitionRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->queuerequisition->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($queuerequisition) {
                return '<input type="checkbox" data-id="'. $queuerequisition->id .'" id="queue" class="select-row" value="'. $queuerequisition->id .'">';
            })
            ->addColumn('item_name', function ($queuerequisition) {
                return $queuerequisition->item_name;
             })
            ->addColumn('uom', function ($queuerequisition) {
                  return $queuerequisition->uom;
            })
            ->addColumn('qty_balance', function ($queuerequisition) {
                return $queuerequisition->qty_balance;
            })
            ->addColumn('quote_no', function ($queuerequisition) {
                return 'Qt-'.$queuerequisition->quote_no;
            })
            ->addColumn('status', function ($queuerequisition) {
                if ($queuerequisition->status > 1) {
                    return 'PO'.$queuerequisition->status;
                }
                elseif ($queuerequisition->status == 1) {
                    return "Pushed";
                }
                return $queuerequisition->status;
            })
            ->addColumn('system_name', function ($queuerequisition) {
                return $queuerequisition->system_name;
            })
            ->addColumn('product_code', function ($queuerequisition) {
                return $queuerequisition->product_code;
            })
            ->addColumn('qty', function ($queuerequisition) {
                return $queuerequisition->item_qty;
            })
            ->addColumn('button', function ($queuerequisition) {
                $id = $queuerequisition->id;
                return '<button class="font-weight-bold click btn btn-sm btn-primary" data-toggle="modal" data-id="'.$id.'" item-name="'.$queuerequisition->item_name.'" data-target="#exampleModal">Add</button>';
            })
            ->addColumn('actions', function ($queuerequisition) {
                return $queuerequisition->action_buttons;
            })
            ->make(true);
    }
}
