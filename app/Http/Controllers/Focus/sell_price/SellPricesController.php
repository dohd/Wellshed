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

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\import_request\ImportRequest;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\productvariable\Productvariable;
use App\Models\sell_price\SellPrice;
use App\Models\sell_price\SellPriceItem;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\sell_price\SellPriceRepository;
use Illuminate\Validation\ValidationException;

/**
 * SellPricesController
 */
class SellPricesController extends Controller
{
    /**
     * variable to store the repository object
     * @var SellPriceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SellPriceRepository $repository ;
     */
    public function __construct(SellPriceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\sell_price\Managesell_priceRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.sell_prices.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Createsell_priceRequestNamespace $request
     * @return \App\Http\Responses\Focus\sell_price\CreateResponse
     */
    public function create()
    {
        $import_requests = ImportRequest::orderBy('id','desc')->where('status','approved')->get();

        return view('focus.sell_prices.create', compact('import_requests'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Storesell_priceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //Input received from the request
        $data = $request->only(['import_request_id','type','percent_fixed_value','recommend_type','recommended_value']);
        $data['ins'] = auth()->user()->ins;
        $data_items = $request->only(['landed_price','minimum_selling_price',
            'recommended_selling_price','moq','reorder_level','product_id','import_request_item_id'
        ]);
        $data_items = modify_array($data_items);
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error creating SP Costing', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.sell_prices.index'), ['flash_success' => 'SP Costing Created Successully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\sell_price\sell_price $sell_price
     * @param Editsell_priceRequestNamespace $request
     * @return \App\Http\Responses\Focus\sell_price\EditResponse
     */
    public function edit(SellPrice $sell_price)
    {
        $import_requests = ImportRequest::orderBy('id','desc')->get();
        return view('focus.sell_prices.edit', compact('sell_price','import_requests'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updatesell_priceRequestNamespace $request
     * @param App\Models\sell_price\sell_price $sell_price
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, SellPrice $sell_price)
    {
        $data = $request->only(['type','percent_fixed_value','recommend_type','recommended_value']);
        $data['ins'] = auth()->user()->ins;
        $data_items = $request->only(['landed_price','minimum_selling_price',
            'recommended_selling_price','moq','reorder_level','product_id','import_request_item_id','id'
        ]);
        $data_items = modify_array($data_items);
        try {
            $this->repository->update($sell_price, compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error updating SP Costing', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.sell_prices.index'), ['flash_success' => 'SP Costing Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletesell_priceRequestNamespace $request
     * @param App\Models\sell_price\sell_price $sell_price
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(SellPrice $sell_price)
    {
        try {
            //Calling the delete method on repository
            $this->repository->delete($sell_price);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error deleting SP Costing', $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.sell_prices.index'), ['flash_success' => 'SP Costing Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletesell_priceRequestNamespace $request
     * @param App\Models\sell_price\sell_price $sell_price
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(SellPrice $sell_price)
    {

        $products = ProductVariation::all();
        //returning with successfull message
        return new ViewResponse('focus.sell_prices.view', compact('sell_price','products'));
    }

    public function update_prices($sell_price_id)
    {
        $sell_price_item = SellPriceItem::find($sell_price_id);
        try {
            $product = ProductVariation::find($sell_price_item->product_id);
            if(!$product){
                throw ValidationException::withMessages(['Product Does not Exist, First create It!!']);
            }
            if($product->selling_price == 0){
                $product->selling_price = $sell_price_item->minimum_selling_price;
                $product->price = $sell_price_item->recommended_selling_price;
                $product->moq = $sell_price_item->moq;
                $product->alert = $sell_price_item->reorder_level;
                $product->update();
            }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Prices', $th);
        }
        return back()->with('flash_success','Prices Updated Successfully!!');
    }

    public function product_link(Request $request)
    {
        // dd($request->all());
        $data = $request->only(['id','product_id']);
        try {
            $product = ProductVariation::find($data['product_id']);
            $sell_price_item = SellPriceItem::find($data['id']);
            $sell_price_item->product_id = $product->id;
            $sell_price_item->update();
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Prices', $th);
        }
        return back()->with('flash_success','Product Linked Successfully!!');
    }

    public function create_product($sellPrice_id)
    {
        $sell_price = SellPriceItem::find($sellPrice_id);
        // Prefill data from sell_price if available
        $prefill = [
            'name' => $sell_price->import_req_item ? $sell_price->import_req_item->product_name : '',
            'purchase_price' => $sell_price->landed_price,
            'selling_price' => $sell_price->minimum_selling_price,
            'price' => $sell_price->recommended_selling_price,
            'moq' => $sell_price->moq,
            'alert' => $sell_price->reorder_level,
        ];
        $product_categories = Productcategory::all();
        $warehouses = Warehouse::all();
        $productvariables = Productvariable::query()
            ->when(config('services.efris.base_url'), fn($q) => $q->whereNotNull('efris_unit'))
            ->get();
        $accounts = Account::whereIn('account_type', ['Asset', 'Expense'])
            ->whereDoesntHave('accountType', fn($q) => $q->where('system', 'bank'))
            ->get(['id', 'number', 'holder', 'account_type', 'parent_id'])
            ->filter(fn($v) => !$v->has_sub_accounts);

        return view('focus.products.create', compact('prefill','product_categories','warehouses','productvariables','accounts'));
    }

    public function change_status(Request $request, $sell_price_id)
    {
        try {
            $sell_price = SellPrice::find($sell_price_id);
            $data = $request->only(['status','status_note']);
            if($data['status'] == 'approved'){

                $data['approved_by'] = auth()->user()->id;
                $data['approval_date'] = date_for_database(date('d-m-Y'));
            }
            $sell_price->update($data);
            foreach ($sell_price->items as $item) {
                $product = $item->product;
                if($product && $sell_price->status == 'approved'){
                    $product->qty += $item->import_req_item->qty;
                    $product->update();
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Changing Status Import Request', $th);
        }
        return back()->with('flash_success', 'Import Request status changed successfully!!');
    }
}
