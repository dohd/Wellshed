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
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\product\ProductRepository;
use App\Http\Requests\Focus\product\ManageProductRequest;
use App\Models\product\Product;
use App\Models\productcategory\Productcategory;
use App\Models\product\ProductVariation;

/**
 * Class ProductsTableController.
 */
class SupplierPriceListTableController extends Controller
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
        $core = Product::query()->with([
            'standard' => fn($q) => $q->select('parent_id', 'code', 'purchase_price', 'expiry','moq','fifo_cost'), 
            'variations' => fn($q) => $q->select('parent_id', 'qty'),
            'category' => fn($q) => $q->select('id', 'title'), 
            'unit' => fn($q) => $q->select('id', 'code'), 
        ]);
        
        $core->when(request('warehouse_id'), function ($q) {
            $q->whereHas('variations', function ($q) {
                $q->where('warehouse_id', request('warehouse_id'));
            });
        })
        ->when(request('category_id'), function ($q) {            
            $q->where('productcategory_id', request('category_id'));
        })->when(request('product_id'), function ($q) {            
            $q->where('id', request('product_id'));
        });

        // return collect();
        // return $q->limit(500)->get();
        $items = $core->limit(500)->get();
       
        return Datatables::of($items)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($product) {
                $this->standard_product = $product->standard ?: $product;
                return  $product->name;
            })
            ->addColumn('productcategory_id', function ($product) {
                $this->standard_product = $product->standard ?: $product;
                $name = Productcategory::where('id',$product->productcategory_id)->first();
                return  $name->title;
            })
            ->addColumn('code', function ($product) {
                 $code = $this->standard_product->code;
                $variations = ProductVariation::where('code', $this->standard_product->code)->first();
                $unit = $product->unit;
                if ($code && $unit) 
                 return '<a class="font-weight-bold click" data-toggle="modal" item_id="'.$variations->id.'"  uom="'.$unit->code.'" des="'.$variations->name.'" product_code="'.$code.'" href="' . route('biller.pricelistsSupplier.list', [$code]) . '  " data-target="#exampleModal">' . $code . '</a>';
                 elseif ($unit && $code) {
                    return '<a class="font-weight-bold click" data-toggle="modal" item_id="'.$variations->id.' uom="'.$unit->code.'" des="'.$variations->name.'" product_code="'.$code.'" href="' . route('biller.pricelistsSupplier.list', [$code]) . '  " data-target="#exampleModal">' . $code . '</a>';
                 }
            })
            ->addColumn('qty', function ($product) {
                return $product->variations->sum('qty');       
            })
            ->addColumn('unit', function ($product) {
                $unit = $product->unit;
                if ($unit) return $unit->code;  
            })
            ->addColumn('price', function ($product) {
                return NumberFormat($this->standard_product->purchase_price);
            })
            ->addColumn('created_at', function ($product) {
                return $product->created_at->format('d-m-Y');
            })
            ->addColumn('actions', function ($product) {
                $buttons = '';
                if ($product->action_buttons) $buttons = $product->action_buttons;
                if (isset($product->product->action_buttons)) $buttons = $product->product->action_buttons;

                return $buttons;
            })
            ->make(true);
    }
}
