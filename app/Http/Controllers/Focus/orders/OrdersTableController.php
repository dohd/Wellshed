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
namespace App\Http\Controllers\Focus\orders;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\orders\OrdersRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class OrdersTableController.
 */
class OrdersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var OrdersRepository
     */
    protected $orders;

    /**
     * contructor to initialize repository object
     * @param OrdersRepository $orders ;
     */
    public function __construct(OrdersRepository $orders)
    {
        $this->orders = $orders;
    }

    /**
     * This method return the data of the model
     * @param ManageordersRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->orders->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function ($orders) {
                return $orders->customer ? $orders->customer->company : '';
            })
            ->addColumn('tid', function ($orders) {
                return gen4tid('ORD-',$orders->tid);
            })
            ->addColumn('status', function ($orders) {
                $status = strtolower($orders->status);
                $color = 'dark'; // default

                if ($status === 'draft') {
                    $color = 'secondary';
                } elseif ($status === 'confirmed') {
                    $color = 'primary';
                } elseif ($status === 'started') {
                    $color = 'info';
                } elseif ($status === 'completed') {
                    $color = 'success';
                } elseif ($status === 'cancelled') {
                    $color = 'danger';
                }

                return '<span class="round badge bg-' . $color . ' text-uppercase px-1 py-1">' 
                    . ucfirst($status) .
                    '</span>';
            })
            ->addColumn('created_at', function ($orders) {
                return Carbon::parse($orders->created_at)->toDateString();
            })
            ->addColumn('actions', function ($orders) {
                return $orders->action_buttons;
            })
            ->make(true);
    }
}
