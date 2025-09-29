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

namespace App\Http\Controllers\Focus\assetequipment;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\assetequipment\AssetequipmentRepository;
use App\Http\Requests\Focus\assetequipment\ManageAssetequipmentRequest;

/**
 * Class AssetequipmentsTableController.
 */
class AssetequipmentsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $assetequipment;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(AssetequipmentRepository $assetequipment)
    {

        $this->assetequipment = $assetequipment;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageAssetequipmentRequest $request)
    {
        $core = $this->assetequipment->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($assetequipment) {
                return $assetequipment->name;
            })
            ->addColumn('account_name', function ($assetequipment) {
                if ($assetequipment->account) 
                return $assetequipment->account->holder;
            })
            ->addColumn('created_at', function ($assetequipment) {
                return $assetequipment->created_at->format('d-m-Y');
            })
            ->addColumn('actions', function ($assetequipment) {
                return $assetequipment->action_buttons;
            })
            ->make(true);
    }
}
