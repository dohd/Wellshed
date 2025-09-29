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
namespace App\Http\Controllers\Focus\import_request;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Repositories\Focus\import_request\ImportRequestRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class ImportRequestsTableController.
 */
class ImportRequestsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var import_requestRepository
     */
    protected $import_request;

    /**
     * contructor to initialize repository object
     * @param import_requestRepository $import_request ;
     */
    public function __construct(ImportRequestRepository $import_request)
    {
        $this->import_request = $import_request;
    }

    /**
     * This method return the data of the model
     * @param Manageimport_requestRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->import_request->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('supplier', function ($import_request) {
                 return $import_request->supplier_name;
            })
            ->addColumn('notes', function ($import_request) {
                return $import_request->notes;
            })
            ->addColumn('pr_no', function ($import_request) {
                $ids = explode(',', $import_request->purchase_requisition_ids);
                
                return collect($ids)->map(function($pr_id) {
                    $pr = PurchaseRequisition::find($pr_id);
                    if ($pr) {
                        return gen4tid('PR-', $pr->tid) . ' - ' . $pr->note;
                    }
                    return null;
                })->filter()->implode('<br>');
            })            
            ->addColumn('tid', function ($import_request) {
                return gen4tid('IMP-',$import_request->tid);
            })
            ->addColumn('created_at', function ($import_request) {
                return Carbon::parse($import_request->created_at)->toDateString();
            })
            ->addColumn('actions', function ($import_request) {
                $btn = '';
                if($import_request->status != 'approved'){

                    $btn = '<a href="'.route('biller.import_requests.edit_import_request', [$import_request]).'" class="btn btn-secondary round" data-toggle="tooltip" data-placement="top" title="Add CBM"><i class="fa fa-gear" aria-hidden="true"></i></a>';
                }
                return $btn.$import_request->action_buttons;
            })
            ->make(true);
    }
}
