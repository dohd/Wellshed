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
namespace App\Http\Controllers\Focus\key_activity;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\key_activity\KeyActivity;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class KeyActivitiesTableController.
 */
class KeyActivitiesTableController extends Controller
{

    /**
     * This method return the data of the model

     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = KeyActivity::all();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($key_activity) {
                 return $key_activity->name;
            })
            ->addColumn('description', function ($key_activity) {
                return $key_activity->description;
            })
            ->addColumn('created_at', function ($key_activity) {
                return Carbon::parse($key_activity->created_at)->toDateString();
            })
            ->addColumn('actions', function ($key_activity) {
                return $key_activity->action_buttons;
            })
            ->make(true);
    }
}
