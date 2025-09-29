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
namespace App\Http\Controllers\Focus\productcategory;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\productcategory\ProductcategoryRepository;
use App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest;

/**
 * Class ProductcategoriesTableController.
 */
class ProductcategoriesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $productcategory;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(ProductcategoryRepository $productcategory)
    {
        $this->productcategory = $productcategory;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageProductcategoryRequest $request)
    {
        //
        $core = $this->productcategory->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($productcategory) {
                $name = '';
                if($productcategory->parent_category){
                    $name = $productcategory->parent_category->title;
                    if($productcategory->child){
                        $name .= '||' .$productcategory->child->title;
                    }
                }
                if($name) {

                    $name .= '||'. $productcategory->title;
                    return '<a class="font-weight-bold" href="' . route('biller.products.index') . '?rel_type=' . $productcategory->c_type . '&rel_id=' . $productcategory->id . '">' . $name . '</a>';
                }
                return '<a class="font-weight-bold" href="' . route('biller.products.index') . '?rel_type=' . $productcategory->c_type . '&rel_id=' . $productcategory->id . '">' . $productcategory->title . '</a>';
            })
            ->addColumn('total', function ($productcategory) {
                return $productcategory->products->count();
            })
            ->addColumn('code_initials', function ($productcategory) {
                return $productcategory->code_initials;
            })
            ->addColumn('parent', function ($productcategory) {
                $parent = '';
                if($productcategory->parent_category){
                    $parent = $productcategory->parent_category->title;
                }
                return $parent;
            })
            ->addColumn('child', function ($productcategory) {
                $child = '';
                if($productcategory->parent_category){
                    // $child = $productcategory->parent_category->title;
                    if($productcategory->child){
                        $child = $productcategory->child->title;
                    }
                }
                return $child;
            })
            ->addColumn('worth', function ($productcategory) {
                $total = 0;
                if ($productcategory->products){
                    foreach ($productcategory->products as $product){
                        if ($product->qty > 0){
                            $total += $product->qty * $product->purchase_price;
                        }
                    }
                }
                return amountFormat($total);
            })
            ->addColumn('created_at', function ($productcategory) {
                return dateFormat($productcategory->created_at);
            })
            ->addColumn('actions', function ($productcategory) {
                return '<a class="btn btn-purple round" href="' . route('biller.products.index', 'productcategory_id=' . $productcategory->id) .'" title="List"><i class="fa fa-list"></i></a>' 
                 . $productcategory->action_buttons;
            })
            ->make(true);
    }
}
