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
namespace App\Http\Controllers\Focus\bom;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\bom\BoMRepository;
use Yajra\DataTables\Facades\DataTables;


/**
 * Class bomsTableController.
 */
class BoMsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var bomRepository
     */
    protected $bom;

    /**
     * contructor to initialize repository object
     * @param bomRepository $bom ;
     */
    public function __construct(BoMRepository $bom)
    {
        $this->bom = $bom;
    }

    /**
     * This method return the data of the model
     * @param ManagebomRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->bom->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($bom) {
                  return $bom->name;
            })
            ->addColumn('bom', function ($bom) {
                return gen4tid("BoM-",$bom->tid);
            })
            ->addColumn('created_at', function ($bom) {
                return Carbon::parse($bom->created_at)->toDateString();
            })
            ->addColumn('actions', function ($bom) {
                return $bom->action_buttons;
            })
            ->make(true);
    }
}
