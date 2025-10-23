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
namespace App\Http\Controllers\Focus\delivery;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\delivery\Delivery;
use Yajra\DataTables\Facades\DataTables;
/**
 * Class DeliveriesTableController.
 */
class DeliveriesTableController extends Controller
{
    

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = Delivery::all();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($delivery) {
                 return $delivery ? gen4tid('DV-',$delivery->tid) : '';
            })
            ->addColumn('order', function ($delivery) {
                 return $delivery->order ? gen4tid('ORD-',$delivery->order->tid) : '';
            })
            ->addColumn('customer', function ($delivery) {
                return optional(optional($delivery->order)->customer)->company ?? '';
            })
            ->addColumn('delivery_schedule', function ($delivery) {
                return $delivery->delivery_schedule ? gen4tid('DS-',$delivery->delivery_schedule->tid) :'';
            })
            ->addColumn('date', function ($delivery) {
                return dateFormat($delivery->date);
            })
            ->addColumn('status', function ($delivery) {
                return ucfirst($delivery->status);
            })
            ->addColumn('created_at', function ($delivery) {
                return Carbon::parse($delivery->created_at)->toDateString();
            })
            ->addColumn('actions', function ($delivery) {
                $actions = '
                    <a href="#" 
                    class="btn btn-secondary round change-status-btn" 
                    data-id="'.$delivery->id.'" 
                    title="Change Status">
                        <i class="fa fa-gear" aria-hidden="true"></i>
                    </a>
                ';


                return $actions.$delivery->action_buttons;
            })
            ->make(true);
    }
}
