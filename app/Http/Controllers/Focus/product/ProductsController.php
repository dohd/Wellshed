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

use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\warehouse\Warehouse;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\product\CreateResponse;
use App\Http\Responses\Focus\product\CreateModalResponse;
use App\Http\Responses\Focus\product\EditResponse;
use App\Repositories\Focus\product\ProductRepository;
use App\Http\Requests\Focus\product\ManageProductRequest;
use App\Http\Requests\Focus\product\CreateProductRequest;
use App\Http\Requests\Focus\product\EditProductRequest;
use App\Jobs\UpdateFifoCost;
use App\Models\client_product\ClientProduct;
use App\Models\currency\Currency;
use App\Models\estimate\EstimateItem;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\InvoiceItem;
use App\Models\items\OpeningStockItem;
use App\Models\items\OrderItem;
use App\Models\items\ProjectstockItem;
use App\Models\items\PurchaseItem;
use App\Models\items\PurchaseorderItem;
use App\Models\items\QuoteItem;
use App\Models\items\StockTransferItem;
use App\Models\items\VerifiedItem;
use App\Models\job_valuation\JobValuationItem;
use App\Models\pricegroup\PriceGroupVariation;
use App\Models\product\EfrisGood;
use App\Models\product\EfrisGoodsAdjLog;
use App\Models\product\EfrisGoodsCategory;
use App\Models\supplier_product\SupplierProduct;
use App\Models\product\ProductMeta;
use App\Models\project\BudgetItem;
use App\Models\purchase\Purchase;
use App\Models\rfq\RfQItem;
use App\Models\sale_return\SaleReturnItem;
use App\Models\stock_adj\StockAdjItem;
use App\Models\stock_issue\StockIssueItem;
use App\Models\stock_rcv\StockRcvItem;
use App\Models\template_quote\TemplateQuoteItem;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;
use Log;

/**
 * ProductsController
 */
class ProductsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductRepository $repository ;
     */
    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\product\ManageProductRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageProductRequest $request)
    {
        $warehouses = Warehouse::get(['id', 'title']);
        $categories = Productcategory::where('rel_id',0)->get(['id', 'title']);
        
        return new ViewResponse('focus.products.index', compact('warehouses', 'categories'));
    }

    public function deleteDuplicateProducts()
    {
        try {
            \DB::beginTransaction();

            // product variations
            $duplicates = ProductVariation::selectRaw('MIN(id) as id, name')
            ->where('ins', auth()->user()->ins)
            ->groupBy('name', 'warehouse_id') // Ensures checking happens within the same warehouse
            ->havingRaw('COUNT(*) > 1')
            ->get();

            foreach ($duplicates as $key => $duplicate) {
                $duplicateProducts = ProductVariation::where('name', $duplicate->name)->where('warehouse_id', $duplicate->warehouse_id)->where('id', '!=', $duplicate->id)->pluck('id');
    
                OpeningStockItem::whereIn('productvar_id', $duplicateProducts)->update(['productvar_id' => $duplicate->id]);
                PurchaseorderItem::whereIn('product_id', $duplicateProducts)->update(['product_id' => $duplicate->id]);
                SupplierProduct::whereIn('product_id', $duplicateProducts)->update(['product_id' => $duplicate->id]);
                PurchaseItem::whereIn('item_id', $duplicateProducts)->where('type', 'Stock')->update(['item_id' => $duplicate->id]);
                SaleReturnItem::whereIn('productvar_id', $duplicateProducts)->update(['productvar_id' => $duplicate->id]);
                StockAdjItem::whereIn('productvar_id', $duplicateProducts)->update(['productvar_id' => $duplicate->id]);
                StockTransferItem::whereIn('productvar_id', $duplicateProducts)->update(['productvar_id' => $duplicate->id]);
                StockRcvItem::whereIn('productvar_id', $duplicateProducts)->update(['productvar_id' => $duplicate->id]);
                StockAdjItem::whereIn('productvar_id', $duplicateProducts)->update(['productvar_id' => $duplicate->id]);
                StockIssueItem::whereIn('productvar_id', $duplicateProducts)->update(['productvar_id' => $duplicate->id]);

                QuoteItem::whereHas('variation', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['product_id' => $duplicate->id]);
                BudgetItem::whereHas('product', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['product_id' => $duplicate->id]);
                VerifiedItem::whereHas('product_variation', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['product_id' => $duplicate->id]);
                JobValuationItem::whereHas('productvar', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['productvar_id' => $duplicate->id]);
                InvoiceItem::whereHas('variation', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['product_id' => $duplicate->id]);

                EstimateItem::whereHas('productvar', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['productvar_id' => $duplicate->id]);
                ProjectstockItem::whereHas('product_variation', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['product_id' => $duplicate->id]);
                OrderItem::whereHas('variation', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['product_id' => $duplicate->id]);
                TemplateQuoteItem::whereHas('variation', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['product_id' => $duplicate->id]);
                RfQItem::whereHas('product', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['product_id' => $duplicate->id]);
                PriceGroupVariation::whereHas('product_variation', fn($q) => $q->whereIn('product_variations.id', $duplicateProducts))
                ->update(['product_variation_id' => $duplicate->id]);

                ProductVariation::where('ins', auth()->user()->ins)
                ->where('name', $duplicate->name)->where('id', '!=', $duplicate->id)->delete();
            }
            updateStockQty($duplicates->pluck('id')->toArray());

            // products
            $duplicates = Product::selectRaw('MIN(id) as id, name')
            ->where('ins', auth()->user()->ins)
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            foreach ($duplicates as $key => $duplicate) {
                $duplicateProducts = Product::where('name', $duplicate->name)->where('id', '!=', $duplicate->id)->pluck('id');
                ProductVariation::whereIn('parent_id', $duplicateProducts)->update(['parent_id' => $duplicate->id]);

                PriceGroupVariation::whereHas('product', fn($q) => $q->whereIn('products.id', $duplicateProducts))
                ->update(['product_id' => $duplicate->id]);

                Product::where('ins', auth()->user()->ins)
                ->where('name', $duplicate->name)->where('id', '!=', $duplicate->id)->delete();
            }

            \DB::commit();
        } catch (\Throwable $th) {
            dd($th);
        }
        return true;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductRequestNamespace $request
     * @return \App\Http\Responses\Focus\product\CreateResponse
     */
    public function create(CreateProductRequest $request)
    {  
        // $products = Product::all();
        // foreach($products as $product)
        // {
        //     if (empty($product['compound_unit_id'])) $product['compound_unit_id'] = array();
        //     $product->units()->sync(array_merge([$product->unit_id], $product['compound_unit_id']));
        // }      
        return new CreateResponse('focus.products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    { 
        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Product', $th);
        }

        return new RedirectResponse(route('biller.products.index'), ['flash_success' => trans('alerts.backend.products.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\product\Product $product
     * @param EditProductRequestNamespace $request
     * @return \App\Http\Responses\Focus\product\EditResponse
     */
    public function edit(Product $product, EditProductRequest $request)
    {
        return new EditResponse($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequestNamespace $request
     * @param App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(EditProductRequest $request, Product $product)
    {
        try {
            $this->repository->update($product, $request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Product', $th);
        }

        return new RedirectResponse(route('biller.products.index'), ['flash_success' => trans('alerts.backend.products.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Product $product)
    {
        try {
            $this->repository->delete($product);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Product', $th);
        }

        return new RedirectResponse(route('biller.products.index'), ['flash_success' => trans('alerts.backend.products.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductRequestNamespace $request
     * @param App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Product $product, ManageProductRequest $request)
    {
        return new ViewResponse('focus.products.view', compact('product'));
    }

    /**
     * Quote or PI searchable product drop down options
     */
    public function quote_product_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        // fetch pricelist customer products
        if ($request->price_customer_id) {
            $products = ClientProduct::where('customer_id', request('price_customer_id'))
            ->where('descr', 'LIKE', '%'. request('keyword') .'%')
            ->limit(6)->get()
            ->map(function ($v) {
                $row_num = $v->row_num > 0 ? "($v->row_num)" : '';
                $v->fill([
                    'product_id' => $v->variation ? $v->variation->id : 0,
                    'name' => "{$v->descr} {$row_num}",
                    'unit' => $v->uom,
                    'price' => $v->rate,
                    'purchase_price' => latestPurchaseCost(@$v->variation->id) ?: @$v->variation->purchase_price,
                    'product_type' => 'client_product',
                    'client_product_id' => $v->id,
                ]);
                return $v;
            });

            return response()->json($products);
        }

        // fetch inventory products
        $productvariations = ProductVariation::when(request('warehouse_id'), fn($q) => $q->where('warehouse_id', request('warehouse_id')))
        ->where(function ($q) {
            $q->whereHas('product', function ($q) {
                $q->where('name', 'LIKE', '%' . request('keyword') . '%')->orWhere('code', 'LIKE', '%' . request('keyword') . '%');
            })->orWhere('name', 'LIKE', '%' . request('keyword') . '%');
        })
        ->with(['warehouse' => fn($q) => $q->select(['id', 'title'])])
        ->with('product')->limit(6)->get()->unique('name');
        
        $products = [];
        foreach ($productvariations as $row) {
            $name = $row->name;
            if (isset($row->product) && $row->product->stock_type === 'service') {
                $name = 'SRVC - ' . $name;
            }
            $product = array_intersect_key($row->toArray(), array_flip([
                'id', 'product_id', 'code', 'qty', 'image', 'purchase_price', 'price', 'alert'
            ]));
            $product = array_replace($product, [
                'name' => $name,
                'taxrate' => @$row->product->taxrate,
                'product_des' => @$row->product->product_des,
                'units' => $row->product? $row->product->units->toArray(): [],
                'unit' => $row->product? @$row->product->unit->code: [],
                'warehouse' => $row->warehouse? $row->warehouse->toArray() : [],
                'product_type' => 'inventory_product',
                'purchase_price' => latestPurchaseCost($row->id) ?: $row->purchase_price,
                'client_product_id' => 0,
                'product_id' => $row->id,
            ]);
            if (request('is_stock_issue') || request('is_stock_adj')) $product['purchase_price'] = fifoCost($row->id) ?: $row->purchase_price;
            
            // set product qty
            $product['qty'] = 0;
            $warehouses = Warehouse::when(request('warehouse_id'), fn($q) => $q->where('id', request('warehouse_id')))
            ->whereHas('products', fn($q) => $q->where('name', 'LIKE', "%{$row->name}%"))
            ->with(['products' => fn($q) => $q->where('name', 'LIKE', "%{$row->name}%")])
            ->get();
            foreach ($warehouses as $key1 => $wh) {
                $product['qty'] += $wh->products->sum('qty');
                $warehouses[$key1]['products_qty'] = $wh->products->sum('qty');
                unset($warehouses[$key1]['products']);
            }
            $product['warehouses'] = $warehouses;

            $products[] =  $product;
        }
        
        return response()->json($products);
    }

    public function update_fifo_cost(){
        $products = ProductVariation::all();
        foreach ($products as $product){
            UpdateFifoCost::dispatch($product);
        }
        return back()->with('flash_success', 'Fifo cost updated successfully!!');
    }

    public function purchase_search(Request $request)
    {
        // if (!access()->allow('product_search')) return false;

        // fetch pricelist customer products
        if ($request->pricegroup_id) {
            $products = SupplierProduct::where('supplier_id', request('pricegroup_id'))->where('status', 'active')
                ->where('descr', 'LIKE', '%'. request('keyword') .'%')->limit(6)->get()
                ->map(function ($v) {
                    $row_num = $v->row_num? "({$v->row_num})" : '';
                    return $v->fill([
                        'name' => "{$v->descr} {$row_num}",
                        'unit' => $v->uom,
                        'units' => @$v->product_variation->product->units,
                        'price' => $v->rate,
                        'purchase_price' => $v->rate,
                    ]);
                });

            return response()->json($products);
        }

        // fetch inventory products
        $productvariations = ProductVariation::whereHas('product', function ($q) {
            $q->where('name', 'LIKE', '%' . request('keyword') . '%');
        })
        ->with([
            'warehouse:id,title',
            'product',
            'product.unit'
        ])
        ->limit(6)
        ->get()
        ->unique('name');
    
        $products = [];
        foreach ($productvariations as $row) {
            // Modify name if stock_type is service
            $name = $row->name;
            if (isset($row->product) && $row->product->stock_type === 'service') {
                $name = 'SRVC - ' . $name;
            }
        
            $product = array_intersect_key(
                $row->toArray(),
                array_flip(['id', 'product_id', 'code', 'qty', 'image', 'purchase_price', 'price', 'alert'])
            );
        
            $products[] = array_merge($product, [
                'name' => $name,
                'purchase_price' => latestPurchaseCost($row->id) ?: $row->purchase_price,
                'product_des' => @$row->product->product_des ?? '',
                'units' => @$row->product->units ?? '',
                'uom' => @$row->product->unit->code ?? '',
                'uom_id' => @$row->product->unit->id ?? '',
                'warehouse' => $row->warehouse ? $row->warehouse->toArray() : '',
                'product' => @$row->product ?? '',
            ]);
        }
    

        return response()->json($products);
    }

    // 
    public function product_sub_load(Request $request)
    {
        $q = $request->get('id');
        $result = \App\Models\productcategory\Productcategory::all()->where('c_type', '=', 1)->where('rel_id', '=', $q);

        return json_encode($result);
    }

    // 
    public function quick_add(CreateProductRequest $request)
    {
        return new CreateModalResponse('focus.modal.product');
    }

    /**
     * Point of Sale
     */
    public function pos(Request $request, $bill_type)
    {
        if (!access()->allow('pos')) return false;
        
        $input = $request->except('_token');
        $limit = $request->post('search_limit', 20);
        $bill_type = $request->bill_type ?: $request->type;

        if ($bill_type == 'label' && isset($input['product']['term']))
            $input['keyword'] = $input['product']['term'];

        if ($input['serial_mode'] == 1 && $input['keyword']) {
            $products = ProductMeta::where('value', 'LIKE', '%' . $input['keyword'] . '%')
                ->whereNull('value2')
                ->whereHas('product_serial', function ($q) use ($input) {
                    if ($input['wid'] > 0) $q->where('warehouse_id', $input['wid']);
                })->with(['product_standard'])->limit($limit)->get();

            $output = array();
            foreach ($products as $row) {
                $serial_product = $row->product_serial;
                $stock_product = $serial_product->product;
                $output[] = [
                    'name' => $stock_product->name, 
                    'disrate' => $serial_product->disrate, 
                    'purchase_price' => $this->repository->eval_purchase_price(
                        $stock_product->id, $stock_product->qty, $stock_product->purchase_price
                    ),
                    'price' => $serial_product['price'], 
                    'id' => $serial_product['id'], 
                    'taxrate' => $stock_product['taxrate'], 
                    'product_des' => $stock_product['product_des'], 
                    'units' => $stock_product['units'], 
                    'code' => $serial_product['code'], 
                    'alert' => $serial_product['qty'], 
                    'image' => $serial_product['image'], 
                    'serial' => $row->value,
                ];
            }
        } else {
            $products = ProductVariation::whereHas('product', function ($q) use ($input) {
                $q->where('name', 'LIKE', '%' . $input['keyword'] . '%');
                if ($input['cat_id'] > 0) $q->where('productcategory_id', $input['cat_id']);
            })->when($input['wid'] > 0, function ($q) use ($input) {
                $q->where('warehouse_id', $input['wid']);
            })->limit($limit)->get();

            $output = array();
            foreach ($products as $row) {
                $output[] = [
                    'name' => $row->name ?: $row->product->name,
                    'disrate' => numberFormat($row->disrate),
                    'purchase_price' => $this->repository->eval_purchase_price(
                        $row->id, $row->qty, $row->purchase_price
                    ),
                    'price' => numberFormat($row->price), 
                    'id' => $row->id, 
                    'taxrate' => numberFormat($row->product['taxrate']), 
                    'product_des' => $row->product['product_des'], 
                    'units' => $row->product->units, 
                    'code' => $row->code, 
                    'alert' => $row->qty, 
                    'image' => $row->image, 
                    'serial' => '',
                ];
            }
        }
        
        return view('focus.products.partials.pos')->withDetails($output);
    }
    public function view($code)
    {
        $supplier_pricelist = SupplierProduct::where('product_code', $code)->get();
        return view('focus.products.view_pricelist', compact('supplier_pricelist'));
    }

    public function deleteMultipleProducts()
    {
        $toDelete = [
            1839, 1853, 1911, 1988, 1989, 1990, 2188, 2270, //DP SWITCHES
            2586, 2354, 2300, 2219, 1794, 1691, //COPPER PIPES 1/4
            2545, 2220, 2206, 2152, 2063, 2062, 1950, 1791, 1728, //COPPER PIPES 3/8
            2544, 2543, 1932, 2451, 2224, 1432, //ARMAFLEX 3/8
            2678, 2588, 2322, 1838, 1787, //ARMAFLEX 1/4
            ];
        $missingProdVarEntries = [];
        $missingProdEntries = [];
        try{
            DB::beginTransaction();
            ProductVariation::withTrashed()->restore();
            Product::withTrashed()->restore();
            DB::commit();
        }catch(Exception $exception){
            DB::rollBack();
            return
                ['message' => $exception->getMessage()];
        }

        return [
            'success' => "BULK DELETE SUCCESS!!!",
            'Missing Product variations' => $missingProdVarEntries,
            'Missing Products' => $missingProdEntries,
        ];
    }

    public function clearNegativeQuantities(){
        $negProducts = ProductVariation::where('qty', '<' , 0)->get();
        foreach ($negProducts as $np){
            $np->qty = 0;
            $np->save();
        }
        return ProductVariation::all();
    }

    /**
     * Products Index Page Datatable
     */
    function datatable_rows() {
        $products = $this->repository->getForDataTable();
        return response()->json($products);
    }

    /**
     * Ending Inventory Cost
     */
    function ending_inventory(Request $request) {
        $endingInventory = 0;
        $product_inventory = productInventoryIn();
        $product_qtys = productInventoryOut();
        $variation_ids = ProductVariation::pluck('id')->toArray();
        foreach ($variation_ids as $id) {
            if (isset($product_inventory[$id])) {
                if (!isset($product_qtys[$id])) $product_qtys[$id] = 0;
                $endingInventory += endingInventoryCostByFifo($product_inventory[$id], $product_qtys[$id]);
            }
        }
        return response()->json(['total' => $endingInventory]);
    }

    public function show_product_inventory(){
        $products = ProductVariation::all();
        return view('focus.products.show_product_inventory', compact('products'));
    }

    public function get_all_products(){
        $q = ProductVariation::query();

        $q->when(request('product_id'), fn($q) => $q->where('id', request('product_id')));

        $results = $q->take(500)->get();
        $products = [];

        foreach($results as $result) {

            $stockAdjItems = StockAdjItem::where('productvar_id', $result->id);
            $stockAdjItems->when(request('start_date') && request('end_date'), function ($q) {
                $q->whereHas('stock_adj', function ($q) {
                    $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
                });
            });
            $stockAdjItems->get();
            $sale_return_item = SaleReturnItem::where('productvar_id', $result->id);
            $sale_return_item->when(request('start_date') && request('end_date'), function ($q) {
                $q->whereHas('sale_return', function ($q) {
                    $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
                });
            });
            $sale_return_item->get();
            $stock_issue_item = StockIssueItem::where('productvar_id', $result->id);
            $stock_issue_item->when(request('start_date') && request('end_date'), function ($q) {
                $q->whereHas('stock_issue', function ($q) {
                    $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
                });
            });
            $stock_issue_item->get();

            $grn_items = GoodsreceivenoteItem::whereHas('warehouse')->whereHas('purchaseorder_item', fn($q) => $q->where('product_id', $result->id));
            $grn_items->when(request('start_date') && request('end_date'), function ($q) {
                $q->whereHas('goodsreceivenote', function($q) {
                    $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
                });
            });
            
            $grn_items->get();
            $grn_qty = 0;
            foreach ($grn_items->get() as $i => $v) {
                $uom = @$v->purchaseorder_item->uom;
                $product = @$v->purchaseorder_item->productvariation->product;
                if ($product && $uom) $grn_qty += convertUnitQty($product, $v->qty, $uom);
            }

            $stock_adj_qty = $stockAdjItems->sum('qty_diff') ?? 0;
            $sale_return_qty = $sale_return_item->sum('return_qty') ?? 0;
            $stock_issue_qty = $stock_issue_item->sum('issue_qty') ?? 0;
            $products[] =  (object) [
                'product_name' => $result->name,
                'code' => $result->code,
                'unit' => $result->product ? @$result->product->unit->code : '',
                'opening_balance' => @$result->openingstock_item()->latest()->first()->qty,
                'date' => '',
                'grn_qty' => $grn_qty,
                'issue_qty' => $stock_issue_qty,
                'return_qty' => $sale_return_qty,
                'stock_adj_qty' => $stock_adj_qty,
                'qty' => $result->qty,
                'warehouse' => @$result->warehouse->title ?? ''
            ];
        }

        
        return DataTables::of($products)
        ->escapeColumns(['id'])
        ->addIndexColumn()    
        ->editColumn('date', function ($item) {
            return $item->date;
        })
        ->addColumn('name', function ($item) {
            return @$item->product_name;
        })
        ->addColumn('code', function ($item) {
            return @$item->code;
        })
        ->addColumn('unit', function ($item) {
            return @$item->unit;
        })
        ->addColumn('opening_balance', function ($item) {
            return numberFormat($item->opening_balance);
        })
        ->addColumn('grn_qty', function ($item) {
            return numberFormat($item->grn_qty);
        })
        ->addColumn('issue_qty', function ($item) {
            return numberFormat($item->issue_qty);
        })
        ->addColumn('return_qty', function ($item) {
            return numberFormat($item->return_qty);
        })
        ->addColumn('stock_adj_qty', function ($item) {
            return numberFormat($item->stock_adj_qty);
        })
        ->addColumn('qty', function ($item) {
            return numberFormat($item->qty);
        })
        ->addColumn('warehouse', function ($item) {
            return  @$item->warehouse;
        })
        ->make(true);
    }

    // EFRIS Goods Configuration Index Page
    public function efrisGoodsConfig()
    {
        $productCategories = Productcategory::whereHas('product_variations')
            ->withCount(['product_variations' => fn($q) => $q->whereNull('efris_commodity_code')])
            ->get();
        $warehouses = Warehouse::all();
        $efrisGoodsCategories = EfrisGoodsCategory::selectRaw('MIN(family_code) min_family_code, MAX(family_code) max_family_code, segment_name')
            ->groupBy('segment_name')
            ->get();

        return view('focus.products.efris_goods_config', compact('efrisGoodsCategories', 'productCategories', 'warehouses'));
    }

    // Load Efris commodities
    public function efrisGoodsConfigData(Request $request)
    {
        $minFamilyCode = $request->min_family_code;
        $maxFamilyCode = $request->max_family_code;
        $minClassCode = $request->min_class_code;

        $efrisGoodsCategories = [];
        if ($minFamilyCode && $maxFamilyCode) {
            $efrisGoodsCategories = EfrisGoodsCategory::selectRaw('MIN(family_code) min_family_code, family_name')
                ->whereBetween('family_code', [$minFamilyCode, $maxFamilyCode])
                ->groupBy('family_name')
                ->get();
        } else if ($minFamilyCode) {
            $efrisGoodsCategories = EfrisGoodsCategory::selectRaw('MIN(class_code) min_class_code, class_name')
                ->where('family_code', $minFamilyCode)
                ->groupBy('class_name')
                ->get();
        } else if ($minClassCode) {
            $efrisGoodsCategories = EfrisGoodsCategory::selectRaw('MIN(commodity_code) min_commodity_code, commodity_name')
                ->where('class_code', $minClassCode)
                ->groupBy('commodity_name')
                ->get();
        }

        return response()->json($efrisGoodsCategories);
    }

    /**
     * Product Selection list datatable
     */
    public function efrisGoodsConfigProductVarData(Request $request)
    {
        $query = ProductVariation::query()
            ->when(request('is_goods_upload'), function($q) {
                if (request('product_status')) {
                    $q->when(request('product_status') == 'uploaded', fn($q) => $q->whereHas('efris_good'));
                    $q->when(request('product_status') == 'not-uploaded', fn($q) => $q->doesntHave('efris_good')->whereNotNull('efris_commodity_code'));
                } else {
                    $q->where(function($q) {
                        $q->whereHas('efris_good');
                        $q->orWhere(fn($q) => $q->doesntHave('efris_good')->whereNotNull('efris_commodity_code'));
                    });
                }
            })
            ->when(request('is_goods_config'), function($q) {
                $q->doesntHave('efris_good');
            })
            ->when(request('category_id'), fn($q) => $q->where('productcategory_id', request('category_id')))
            ->when(request('warehouse_id') > 0, fn($q) => $q->where('warehouse_id', request('warehouse_id')))
            ->when(request('warehouse_id') == 'none', fn($q) => $q->whereNull('warehouse_id'))
            ->with('efris_good');
        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('row_check', function ($query) {
                // checkbox for selecting assigned products
                if (request('is_goods_upload')) {
                    if (@$query->efris_good || !$query->efris_commodity_code) 
                        return '<input disabled type="checkbox" class="check-row" data-id="'. $query->id .'">';  
                }
                return '<input type="checkbox" class="check-row" data-id="'. $query->id .'">';        
            })
            ->editColumn('name', function($query) {
                return '<a href="'. route('biller.products.edit', $query->product) .'">'. $query->name .'</a>'; 
            })
            ->addColumn('goods_code', function ($query) {
                return @$query->efris_good->goods_code;   
            })
            ->addColumn('category', function ($query) {
                return @$query->product->category->title;   
            })
            ->make(true);
    }

    // Assign Efris Goods Code to Inventory Products
    public function efrisAssignCommodityCode(Request $request)
    {
        $request->validate(['commodity_code' => 'required', 'productvar_ids' => 'required']);
        $input = $request->only('commodity_code', 'productvar_ids');
        try {
            DB::beginTransaction();
            $commodity= EfrisGoodsCategory::where('commodity_code', $input['commodity_code'])->firstOrFail();
            $productvarIds = explode(',', $input['productvar_ids']);
            ProductVariation::whereIn('id', $productvarIds)->update([
                'efris_commodity_code_id' => $commodity->id, 
                'efris_commodity_code' => $commodity->commodity_code
            ]);   
            DB::commit();
            return redirect()->back()->with('flash_success', "Commodity Code Assigned to Products Successfully");
        } catch (\Exception $e) {
            return errorHandler('Error Assigning Commodity Code to Products', $e);
        } 
    }

    // Search EFRIS Goods Code
    public function efrisGoodsCodeSearch(Request $request)
    {
        $kw = $request->search;
        $commodities = EfrisGoodsCategory::where('commodity_name', 'LIKE', '%'. $kw .'%')->limit(6)->get();
        
        return view('focus.products.partials.efris_goods_search', compact('commodities'));
    }

    // EFRIS Goods Upload View
    public function efrisGoodsUploadView()
    {
        $productCategories = Productcategory::whereHas('product_variations')
            ->withCount(['product_variations' => fn($q) => $q->doesntHave('efris_good')])
            ->get();
        $warehouses = Warehouse::all();
        $currencies = Currency::where('efris_currency_name', 'UGX')->get();
        if (!$currencies->count()) throw ValidationException::withMessages(['UGX currency required']);
        
        return view('focus.products.efris_goods_upload', compact('productCategories', 'warehouses', 'currencies'));
    }

    public function efrisGoodsModalData(Request $request)
    {
        $productvarIds = explode(',', request('productvar_ids')) ?: [];
        $productvars = ProductVariation::whereHas('product')
            ->whereIn('id', $productvarIds)
            ->whereNotNull('efris_commodity_code')
            ->with(['product.unit'])
            ->get(['id', 'parent_id', 'name', 'efris_commodity_code', 'alert', 'price', 'purchase_price'])
            ->map(function($item) {
                $item['efris_unit_name'] = (string) @$item->product->unit->efris_unit_name;
                $item['efris_unit'] = (string) @$item->product->unit->efris_unit;
                $item['efris_goods_code'] = md5(str_replace(' ', '', strtolower($item->name)));
                $item['unit_price'] = round($item->price * (1 + $item->product->taxrate * 0.01), 4); // tax inclusive
                $isServiceItem = $item->product->stock_type == 'service';
                $item['have_piece_unit'] = $isServiceItem? '102' : '101'; // 102:No, 101:Yes
                $item['alert'] = $isServiceItem? '' : $item->alert;
                $item['piece_unit_price'] = $isServiceItem? '' : $item['unit_price'];
                $item['piece_measure_unit_name'] = $isServiceItem? '' : $item['efris_unit_name'];
                $item['piece_measure_unit'] = $isServiceItem? '' : $item['efris_unit'];
                $item['package_scaled_value'] = $isServiceItem? '' : '1';
                $item['piece_scaled_value'] = $isServiceItem? '' : '1';
                return $item;
            });
        return response()->json($productvars);
    }

    public function efrisGoodsUpload(Request $request)
    {
        $input = $request->except('_token');
        try {
            // format data
            $inputItems = modify_array($input);
            $efrisInputItems = array_map(function($item) {
                $keys = array_map(function($key) {
                    return \Illuminate\Support\Str::camel($key);
                }, array_keys($item));
                $efrisInput = array_combine($keys, array_values($item));
                unset($efrisInput['productvarId']);
                return $efrisInput;
            }, $inputItems);

            // Upload to EFRIS
            $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
            $result = $controller->goodsUpload($efrisInputItems);
            // check error
            if ($result) {
                Log::error('EFRIS Goods Upload Error', (array) $result);
                return errorHandler('Something went wrong. Try again later');
            } 

            // Save Locally
            DB::beginTransaction();
            foreach ($inputItems as $key => $item) {
                $inputItems[$key] = array_replace($item, [
                    'ins' => auth()->user()->ins,
                    'unit_price' => numberClean($item['unit_price']),
                    'stock_prewarning' => numberClean($item['stock_prewarning']),
                    'piece_unit_price' => numberClean($item['piece_unit_price']),
                ]);
            }
            EfrisGood::insert($inputItems);
            DB::commit();

            return redirect()->back()->with('flash_success', 'Goods Uploaded and Saved Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('flash_error', $e->getMessage());
        }
    }

    public function efrisGoodsAdjModal(Request $request)
    {
        // purchase
        $purchase = Purchase::find($request->purchase_id) ?: new Purchase;
        $purchase->items = $purchase->items()
            ->whereHas('productvariation', fn($q) => $q->whereHas('efris_good'))
            ->where('type', 'Stock')
            ->where('warehouse_id', '>', 0)
            ->where(fn($q) => $q->where('itemproject_id', 0)->orWhereNull('itemproject_id'))
            ->with(['productvariation.efris_good'])
            ->get();
        
        // goods receiving
        $grn = Goodsreceivenote::find($request->grn_id) ?: new Goodsreceivenote;
        $grn->items = $grn->items()
            ->whereHas('purchaseorder_item', fn($q) => $q->whereHas('productvariation', fn($q) => $q->whereHas('efris_good')))
            ->where('warehouse_id', '>', 0)
            ->where(fn($q) => $q->where('itemproject_id', 0)->orWhereNull('itemproject_id'))
            ->with(['purchaseorder_item.productvariation.efris_good'])
            ->get();
        
        return view('focus.products.partials.efris-stock-adjustment')
            ->with(compact('purchase', 'grn'));
    }

    public function efrisGoodsAdjustment(Request $request)
    {
        try {
            $operationType = $request->operation_type;
            $stockItems = $request->only('goods_code', 'qty', 'unit_price', 'productvar_id', 'purchase_item_id', 'grn_item_id');
            $stockItems = modify_array($stockItems);
            $stockItems = array_filter($stockItems, fn($v) => numberClean($v['qty']) > 0);
            // dd($stockItems);

            $purchase = Purchase::find($request->purchase_id);
            $grn = Goodsreceivenote::find($request->grn_id);
            
            // Increase
            if ($operationType == '101') {
                $supplierName = '';
                $supplierTin = '';
                $stockInDate = '';
                if ($purchase) {
                    $supplierName = $purchase->supplier->name;
                    $supplierTin = $purchase->supplier->taxid;
                    $stockInDate = $purchase->date;
                } elseif ($grn) {
                    $supplierName = $grn->supplier->name;
                    $supplierTin = $grn->supplier->taxid;
                    $stockInDate = $grn->date;
                }

                $stockGoodIn = config('efris.stock_good_in');
                $stockGoodIn['goodsStockIn'] = array_replace($stockGoodIn['goodsStockIn'], [
                    'supplierName' => $supplierName,
                    'supplierTin' => $supplierTin,
                    'stockInDate' => $stockInDate,
                ]);
                $stockGoodIn['goodsStockInItem'] = array_map(function($v) {
                    return [
                        'goodsCode' => $v['goods_code'],
                        'quantity' => numberClean($v['qty']),
                        'unitPrice' => numberClean($v['unit_price']),
                    ];
                }, $stockItems);

                // Post to EFRIS
                $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
                $adjResult = $controller->stockMaintain($stockGoodIn);
                if ($adjResult) throw Exception('Error Adjusting Purchase Stock');

                // Log Stock
                DB::beginTransaction();
                $itemLogs = array_map(function($v) {
                    $qty = numberClean($v['qty']);
                    $currQty = EfrisGoodsAdjLog::where('productvar_id', $v['productvar_id'])->sum('item_qty');
                    return [
                        'ins' => auth()->user()->ins,
                        'user_id' => auth()->user()->id,
                        'productvar_id' => $v['productvar_id'],
                        'item_qty' => $qty,
                        'stock_qty' => $qty + $currQty,
                        'purchase_id' => request('purchase_id'),
                        'purchase_item_id' => @$v['purchase_item_id'],
                        'grn_id' => request('grn_id'),
                        'grn_item_id' => @$v['grn_item_id'],
                    ];
                }, $stockItems);
                EfrisGoodsAdjLog::insert($itemLogs);
                DB::commit();
            }

            // Decrease
            if ($operationType == '102') {
                $stockGoodOut = config('efris.stock_good_out');
                $stockGoodOut['goodsStockInItem'] = array_map(function($v) {
                    return [
                        'goodsCode' => $v['goods_code'],
                        'quantity' => numberClean($v['qty']),
                        'unitPrice' => numberClean($v['unit_price']),
                    ];
                }, $stockItems);

                // Post to EFRIS
                $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
                $adjResult = $controller->stockMaintain($stockGoodOut);
                if ($adjResult) throw Exception('Error Adjusting Purchase Stock');

                // Log Stock
                DB::beginTransaction();
                $itemLogs = array_map(function($v) {
                    $qty = numberClean($v['qty']);
                    $currQty = EfrisGoodsAdjLog::where('productvar_id', $v['productvar_id'])->sum('item_qty');
                    return [
                        'ins' => auth()->user()->ins,
                        'user_id' => auth()->user()->id,
                        'productvar_id' => $v['productvar_id'],
                        'item_qty' => -$qty,
                        'stock_qty' => -$qty + $currQty,
                        'purchase_id' => request('purchase_id'),
                        'purchase_item_id' => @$v['purchase_item_id'],
                        'grn_id' => request('grn_id'),
                        'grn_item_id' => @$v['grn_item_id'],
                    ];
                }, $stockItems);
                EfrisGoodsAdjLog::insert($itemLogs);
                DB::commit();
            }
            return response()->json(['status' => 'Success', 'message' => 'Stock Adjustment Posted Successfully']);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['status' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function get_categories(Request $request)
    {
        $category_id = $request->cat_id;
        $sub_category_id = $request->sub_cat_id;
        $product_categories = Productcategory::where('rel_id',0)->get();
        if($request->type == 'parent'){
            $product_categories = Productcategory::where('rel_id',0)->get();

        }
        elseif($request->type == 'sub_category'){
            $product_categories = Productcategory::where('rel_id',$category_id)->where('child_id',0)->get();
        }
        elseif($request->type == 'sub_sub_category'){
            $product_categories = Productcategory::where('child_id',$sub_category_id)->get();
        }
        return response()->json($product_categories);
    }
}
