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

namespace App\Http\Controllers\Focus\pricelistSupplier;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\pricelistSupplier\PriceListRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Models\product\ProductVariation;

/**
 * Class BranchTableController.
 */
class PriceListTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PriceListRepository
     */
    protected $pricelist;

    /**
     * contructor to initialize repository object
     * @param PriceListRepository $pricelist ;
     */
    public function __construct(PriceListRepository $pricelist)
    {

        $this->pricelist = $pricelist;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->pricelist->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('supplier', function ($supplier_product) {
                $supplier = $supplier_product->supplier;
                if ($supplier) return $supplier->company;
            })
            ->addColumn('product_desc', function ($supplier_product) {
                $variations = ProductVariation::where('code', $supplier_product->product_code)->first();
               // return $variations;
                // if ($variations) return $variations->name;
                return $supplier_product->product_variation ? $supplier_product->product_variation->name : '';
            })
            ->addColumn('row', function ($supplier_product) {
                return $supplier_product->row_num;
            })
            ->addColumn('rate', function ($supplier_product) {
                return numberFormat($supplier_product->rate);
            })
            ->addColumn('status', function ($supplier_product) {
                return $supplier_product->status == 'active' 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">In Active</span>';
            })            
            ->addColumn('actions', function ($supplier_product) {
                return $supplier_product->action_buttons;
            })
            ->make(true);
    }
}
