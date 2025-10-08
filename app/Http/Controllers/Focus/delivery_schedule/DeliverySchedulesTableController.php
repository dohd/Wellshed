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
namespace App\Http\Controllers\Focus\delivery_schedule;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\delivery_schedule\DeliverySchedule;
use Yajra\DataTables\Facades\DataTables;
/**
 * Class DeliverySchedulesTableController.
 */
class DeliverySchedulesTableController extends Controller
{
    

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = DeliverySchedule::all();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($delivery_schedule) {
                 return gen4tid('DS-',$delivery_schedule->tid);
            })
            ->addColumn('order', function ($delivery_schedule) {
                 return $delivery_schedule->order ? gen4tid('ORD-',$delivery_schedule->order->tid) : '';
            })
            ->addColumn('customer', function ($delivery_schedule) {
                return optional(optional($delivery_schedule->order)->customer)->company ?? '';
            })
            ->addColumn('delivery_days', function ($delivery_schedule) {
                return $delivery_schedule->delivery_frequency ? $delivery_schedule->delivery_frequency->delivery_days :'';
            })
            ->addColumn('delivery_date', function ($delivery_schedule) {
                return dateFormat($delivery_schedule->delivery_date);
            })
            ->addColumn('delivery_time', function ($delivery_schedule) {
                return timeFormat($delivery_schedule->delivery_time);
            })
            ->addColumn('status', function ($delivery_schedule) {
                return ucfirst($delivery_schedule->status);
            })
            ->addColumn('created_at', function ($delivery_schedule) {
                return Carbon::parse($delivery_schedule->created_at)->toDateString();
            })
            ->addColumn('actions', function ($delivery_schedule) {
                return $delivery_schedule->action_buttons;
            })
            ->make(true);
    }
}
