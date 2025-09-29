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

namespace App\Http\Controllers\Focus\spvariations;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\spvariations\SpVariableRepository;
use App\Http\Requests\Focus\spvariations\ManageSpVariableRequest;

/**
 * Class ProductcategoriesTableController.
 */
class SpVariablesControllerTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $spvariation;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(SpVariableRepository $spvariation)
    {
        $this->spvariation = $spvariation;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageSpVariableRequest $request)
    {
        $core = $this->spvariation->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('item_name', function ($spvariation) {
                return $spvariation->product->name;
            })
            ->addColumn('item_code', function ($spvariation) {
                if (isset($spvariation->product_variation->code))
                    return $spvariation->product_variation->code;
            })
            ->addColumn('selling_price', function ($spvariation) {
                return amountFormat($spvariation->selling_price);
            })
            ->addColumn('created_at', function ($spvariation) {
                return $spvariation->created_at->format('d-m-Y');
            })
            ->make(true);
    }
}
