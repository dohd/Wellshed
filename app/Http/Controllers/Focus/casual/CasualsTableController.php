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
namespace App\Http\Controllers\Focus\casual;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\casual\CasualRepository;

/**
 * Class casualsTableController.
 */
class CasualsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var CasualRepository
     */
    protected $casual;

    /**
     * contructor to initialize repository object
     * @param CasualRepository $casual ;
     */
    public function __construct(CasualRepository $casual)
    {
        $this->casual = $casual;
    }

    /**
     * This method return the data of the model
     * @param ManagecasualRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->casual->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($casual) {
                 return $casual->name;
            })
            ->addColumn('job_category', function ($casual) {
                 return @$casual->job_category->name;
            })
            ->addColumn('work_type', function ($casual) {
                if($casual->work_type == 'non_contract'){
                    return 'Non Contract';
                }
                 return ucfirst($casual->work_type);
            })
            ->addColumn('actions', function ($casual) {
                return $casual->action_buttons;
            })
            ->make(true);
    }
}
