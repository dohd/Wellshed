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
namespace App\Http\Controllers\Focus\fault;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\fault\FaultRepository;
//use App\Http\Requests\Focus\fault\ManagefaultRequest;

/**
 * Class faultsTableController.
 */
class FaultTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var faultRepository
     */
    protected $fault;

    /**
     * contructor to initialize repository object
     * @param faultRepository $fault ;
     */
    public function __construct(FaultRepository $fault)
    {
        $this->fault = $fault;
    }

    /**
     * This method return the data of the model
     * @param ManagefaultRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->fault->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($fault) {
                  return $fault->name;
            })
            ->addColumn('notes', function ($fault) {
                return $fault->notes;
            })
            ->addColumn('created_at', function ($fault) {
                return Carbon::parse($fault->created_at)->toDateString();
            })
            ->addColumn('actions', function ($fault) {
                return $fault->action_buttons;
            })
            ->make(true);
    }
}
