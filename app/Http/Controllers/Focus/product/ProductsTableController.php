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

namespace App\Http\Controllers\Focus\product;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\product\ProductRepository;
use App\Http\Requests\Focus\product\ManageProductRequest;
use App\Models\productcategory\Productcategory;
use DB;

/**
 * Class ProductsTableController.
 */
class ProductsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductRepository
     */
    protected $repository;

    // standard product
    protected $standard_product;

    /**
     * contructor to initialize repository object
     * @param ProductRepository $repository ;
     */
    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageProductRequest $request
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->repository->getForDataTable();

        // aggregate
        $product_count = 0;
        $product_worth = 0;
        foreach ($core as $product) {
            $product_count += $product->variations->count();
            $product_worth += $product->variations()->sum(DB::raw('purchase_price*qty'));
        }
        $product_worth = amountFormat($product_worth);
        $aggregate = compact('product_count', 'product_worth');
       
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($product) {
                $this->standard_product = $product->standard ?: $product;
                return '<a class="font-weight-bold" href="' . route('biller.products.show', [$product->id]) . '">' . $product->name . '</a>';
            })
             ->addColumn('productcategory_id', function ($product) {
                $this->standard_product = $product->standard ?: $product;
                $name = Productcategory::where('id',$product->productcategory_id)->first();
                return  empty($name) ? 'MISSING PRODUCT CATEGORY' : $name->title;
            })
            ->addColumn('code', function ($product) {
                $code = $this->standard_product->code;
                 if ($code) 
                return '<a class="font-weight-bold" href="' . route('biller.products.view', [$code]) . '">' . $code . '</a>';
            })
            ->addColumn('qty', function ($product) {
                return $product->variations->sum('qty');       
            })
            ->addColumn('unit', function ($product) {
                $unit = $product->unit;
                if ($unit) return $unit->code;   
            })
            ->addColumn('purchase_price', function ($product) {
                $permission = 'product-view_purchase_price';
                if (access()->allow($permission)) {
                    return NumberFormat($this->standard_product->purchase_price);
                }
                return 'No Permission';
            })
            ->addColumn('total', function ($product) {
                $total = 0;
                foreach ($product->variations as $product_var) {
                    $total += $this->standard_product->purchase_price * $product_var->qty;
                }
                return NumberFormat($total);
            })
            ->addColumn('created_at', function ($product) {
                return $product->created_at->format('d-m-Y');
            })
           ->addColumn('expiry', function ($product) {
                $expiry = $this->standard_product->expiry;
                if ($expiry) {
                    return dateFormat($expiry);
                }
               return '';
            })
           ->addColumn('moq', function ($product) {
                $moq = $this->standard_product->moq;
                if ($moq) {
                    return numberFormat($moq);
                }
               return 0;
            })
            ->addColumn('actions', function ($product) {
                if ($product->action_buttons) return $product->action_buttons;
                if (isset($product->product->action_buttons)) return $product->product->action_buttons;
            })
            ->addColumn('aggregate', function () use($aggregate) {
                return $aggregate;
            })
            ->make(true);
    }
}
