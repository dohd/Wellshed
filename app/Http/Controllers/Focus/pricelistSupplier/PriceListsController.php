<?php

namespace App\Http\Controllers\Focus\pricelistSupplier;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\supplier_product\SupplierProduct;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\pricelistSupplier\PriceListRepository;
use Illuminate\Http\Request;
use App\Models\productcategory\Productcategory;
use App\Models\warehouse\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PriceListsController extends Controller
{
    /**
     * variable to store the repository object
     * @var PriceListRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PriceListRepository $repository ;
     */
    public function __construct(PriceListRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = Supplier::whereHas('supplier_products')->get(['id', 'company']);
        $contracts = SupplierProduct::get(['contract', 'supplier_id'])->unique('contract');
        $contracts = [...$contracts];

        return new ViewResponse('focus.pricelistsSupplier.index', compact('suppliers', 'contracts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Supplier::get(['id', 'company']);
        $warehouses = Warehouse::get(['id', 'title']);
        $categories = Productcategory::get(['id', 'title']);
        $products = Product::get(['id', 'name']);

        return new ViewResponse('focus.pricelistsSupplier.create', compact('suppliers','warehouses','categories','products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        }
        catch (ValidationException $e) {
            // Return validation errors
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
         catch (\Throwable $e) {
            return errorHandler("Error: '" . $e->getMessage() . "' | on File: " . $e->getFile() . " | Line: " . $e->getLine());
        }
        return new RedirectResponse(route('biller.pricelistsSupplier.index'), ['flash_success' => 'Pricelist Item Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $supplier_product = SupplierProduct::find($id);
        return view('focus.pricelistsSupplier.view', compact('supplier_product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $supplier_product = SupplierProduct::find($id);
        $suppliers = Supplier::get(['id', 'company']);
        $products = ProductVariation::get();

        return view('focus.pricelistsSupplier.edit', compact('supplier_product', 'suppliers','products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $supplier_product = SupplierProduct::find($id);
        $this->repository->update($supplier_product, $request->except('_token'));

        return new RedirectResponse(route('biller.pricelistsSupplier.index'), ['flash_success' => 'Pricelist Item Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        try {
            if ($id == 0) {
                $request->validate(['supplier_id' => 'required']);
                $this->repository->mass_delete($request->except('_token'));
            } else {
                $supplier_product = SupplierProduct::find($id);
                $this->repository->delete($supplier_product);    
            }
        }
        catch (ValidationException $e) {
            // Return validation errors
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
         catch (\Throwable $e) {
            return errorHandler("Error: '" . $e->getMessage() . "' | on File: " . $e->getFile() . " | Line: " . $e->getLine());
        }
        return new RedirectResponse(route('biller.pricelistsSupplier.index'), ['flash_success' => 'Pricelist Item Deleted Successfully']);
    }

    public function change_attachment(Request $request, $supplier_product_id)
    {
        $data = $request->only(['product_id']);
        $supplier_product = SupplierProduct::find($supplier_product_id);
        $pricelist = SupplierProduct::where(['product_id'=> $data['product_id'], 'supplier_id'=> $supplier_product_id])->first();
        if($pricelist && $supplier_product->product_id != $data['product_id']){
            throw ValidationException::withMessages(['The Item with Same Supplier Already Exists!']);
        }
        
        $product_variation = ProductVariation::find($data['product_id']);
        $product_ids = [];
        try {
            DB::beginTransaction();
            $supplier_product->product_code = $product_variation->code;
            $product_ids[] = $supplier_product->product_id; 
            $supplier_product->product_id = $product_variation->id;
            $supplier_product->uom = $product_variation->product ? $product_variation->product->unit->code : '';
            $supplier_product->update();
            //Change Purchase order Items
            if(count($supplier_product->purchase_order_item) > 0){
                foreach($supplier_product->purchase_order_item as $poitem){
                    
                    $poitem->product_id = $product_variation->id;
                    $poitem->product_code = $product_variation->code;
                    $poitem->uom = $product_variation->product ? $product_variation->product->unit->code : '';
                    $poitem->update();
                    
                    if(count($poitem->grn_items) > 0){
                        foreach($poitem->grn_items as $grn_item){
                            $grn_item->item_id = $product_variation->id;
                            $product_ids[] = $product_variation->id;
                            $grn_item->update();
                        }
                    }
                }
            }
            updateStockQty($product_ids);
            if($supplier_product){
                DB::commit();
            }

        } catch (\Throwable $e) {
            //throw $th;
            DB::rollBack();
            return errorHandler("Error: '" . $e->getMessage() . "' | on File: " . $e->getFile() . " | Line: " . $e->getLine());
        }
        return back()->with('flash_success','Supplier Product Updated Successully');
    }

    public function change_status(Request $request, $supplier_product_id)
    {
        try {
            $data = $request->only(['status']);
            $supplier_product = SupplierProduct::find($supplier_product_id);
            $supplier_product->update($data);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Changing status in Supplier Pricelist', $th);
        }
        return back()->with('flash_success','Status Updated Successfully!!');
    }
}
