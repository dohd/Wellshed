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
namespace App\Http\Controllers\Focus\appraisal_type;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\appraisal_type\AppraisalTypeRepository;

/**
 * Class AppraisalTypesTableController.
 */
class AppraisalTypesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AppraisalTypeRepository
     */
    protected $appraisal_type;

    /**
     * contructor to initialize repository object
     * @param AppraisalTypeRepository $appraisal_type ;
     */
    public function __construct(AppraisalTypeRepository $appraisal_type)
    {
        $this->appraisal_type = $appraisal_type;
    }

    /**
     * This method return the data of the model
     * @param Manageappraisal_typeRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->appraisal_type->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
          
            ->addColumn('created_at', function ($appraisal_type) {
                return Carbon::parse($appraisal_type->created_at)->toDateString();
            })
            ->addColumn('actions', function ($appraisal_type) {
                return  $appraisal_type->action_buttons;
            })
            ->make(true);
    }
}
