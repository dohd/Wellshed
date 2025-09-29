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
namespace App\Http\Controllers\Focus\part;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\part\PartRepository;

/**
 * Class PartsTableController.
 */
class PartsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var partRepository
     */
    protected $part;

    /**
     * contructor to initialize repository object
     * @param partRepository $part ;
     */
    public function __construct(PartRepository $part)
    {
        $this->part = $part;
    }

    /**
     * This method return the data of the model
     * @param ManagepartRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->part->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($part) {
                 return $part->name;
            })
            ->addColumn('tid', function ($part) {
                 return gen4tid('FG-',$part->tid);
            })
            ->addColumn('description', function ($part) {
                return $part->description;
            })
            ->addColumn('created_at', function ($part) {
                return Carbon::parse($part->created_at)->toDateString();
            })
            ->addColumn('actions', function ($part) {
                return $part->action_buttons;
            })
            ->make(true);
    }
}
