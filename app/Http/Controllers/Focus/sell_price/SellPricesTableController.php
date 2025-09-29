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
namespace App\Http\Controllers\Focus\sell_price;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\sell_price\SellPriceRepository;

/**
 * Class SellPricesTableController.
 */
class SellPricesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var SellPriceRepository
     */
    protected $sell_price;

    /**
     * contructor to initialize repository object
     * @param SellPriceRepository $sell_price ;
     */
    public function __construct(SellPriceRepository $sell_price)
    {
        $this->sell_price = $sell_price;
    }

    /**
     * This method return the data of the model
     * @param ManageDepartmentRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->sell_price->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('import_request', function ($sell_price) {
                 return $sell_price->import_request ? gen4tid('IMP-',$sell_price->import_request->tid) .'-'.$sell_price->import_request->notes : '';
            })
            ->addColumn('type', function ($sell_price) {
                 return ucfirst($sell_price->type);
            })
            ->addColumn('status', function ($sell_price) {
                 return ucfirst($sell_price->status);
            })
            ->addColumn('percent_fixed_value', function ($sell_price) {
                 return numberFormat($sell_price->percent_fixed_value);
            })
            ->addColumn('created_at', function ($sell_price) {
                return Carbon::parse($sell_price->created_at)->toDateString();
            })
            ->addColumn('actions', function ($sell_price) {
                return $sell_price->action_buttons;
            })
            ->make(true);
    }
}
