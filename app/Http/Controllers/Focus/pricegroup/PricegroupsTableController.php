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

namespace App\Http\Controllers\Focus\pricegroup;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\pricegroup\PricegroupRepository;
use App\Http\Requests\Focus\pricegroup\ManagePricegroupRequest;

/**
 * Class WarehousesTableController.
 */
class PricegroupsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var WarehouseRepository
     */
    protected $pricegroup;

    /**
     * contructor to initialize repository object
     * @param WarehouseRepository $warehouse ;
     */
    public function __construct(PricegroupRepository $pricegroup)
    {
        $this->pricegroup = $pricegroup;
    }

    /**
     * This method return the data of the model
     * @param ManageWarehouseRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManagePricegroupRequest $request)
    {
        $core = $this->pricegroup->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($pricegroup) {
                return '<a class="font-weight-bold" href="' . route('biller.spvariations.index') . '?rel_id=' . $pricegroup->id . '">' . $pricegroup->name . '</a>';
            })->addColumn('total', function ($pricegroup) {
                // return numberFormat($pricegroup->products->sum('items'));
            })
            ->addColumn('worth', function ($pricegroup) {
                // return numberFormat($pricegroup->products->sum('total_value'));
            })
            ->addColumn('created_at', function ($pricegroup) {
                return $pricegroup->created_at->format('d-m-Y');
            })
            ->addColumn('actions', function ($pricegroup) {
                return '<a class="btn btn-purple round" href="' . route('biller.spvariations.index') . '?rel_id=' . $pricegroup->id . '" title="List"><i class="fa fa-list"></i></a>' 
                    . $pricegroup->action_buttons;
            })
            ->make(true);
    }
}
