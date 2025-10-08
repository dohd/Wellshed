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
namespace App\Http\Controllers\Focus\target_zone;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\target_zone\TargetZoneRepository;
use Yajra\DataTables\Facades\DataTables;
/**
 * Class TargetZonesTableController.
 */
class TargetZonesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TargetZoneRepository
     */
    protected $target_zone;

    /**
     * contructor to initialize repository object
     * @param TargetZoneRepository $target_zone ;
     */
    public function __construct(TargetZoneRepository $target_zone)
    {
        $this->target_zone = $target_zone;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->target_zone->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($target_zone) {
                return $target_zone->name;
            })
            ->addColumn('created_at', function ($target_zone) {
                return Carbon::parse($target_zone->created_at)->toDateString();
            })
            ->addColumn('actions', function ($target_zone) {
                return $target_zone->action_buttons;
            })
            ->make(true);
    }
}
