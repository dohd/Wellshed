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
namespace App\Http\Controllers\Focus\part;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\part\Part;
use App\Models\product\ProductVariation;
use App\Models\standard_template\StandardTemplate;
use App\Repositories\Focus\part\PartRepository;
use DB;
use Illuminate\Validation\ValidationException;

/**
 * partsController
 */
class PartsController extends Controller
{
    /**
     * variable to store the repository object
     * @var PartRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PartRepository $repository ;
     */
    public function __construct(PartRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\part\
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.parts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatepartRequestNamespace $request
     * @return \App\Http\Responses\Focus\part\CreateResponse
     */
    public function create()
    {
        $templates = StandardTemplate::all();
        return view('focus.parts.create', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorepartRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $data = $request->only(['name','description','template_id','total_qty','type']);
        $data['ins'] = auth()->user()->ins;

        $data_items = $request->only(['product_id','unit_id','qty','qty_for_single']);
        $data_items = modify_array($data_items);
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Product Part', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.parts.index'), ['flash_success' => 'Product Part Added Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\part\part $part
     * @param EditpartRequestNamespace $request
     * @return \App\Http\Responses\Focus\part\EditResponse
     */
    public function edit(Part $part)
    {
        $templates = StandardTemplate::all();
        return view('focus.parts.edit', compact('part','templates'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatepartRequestNamespace $request
     * @param App\Models\part\part $part
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Part $part)
    {
        //Input received from the request
        $data = $request->only(['name','description','template_id','total_qty','type']);

        $data_items = $request->only(['product_id','unit_id','qty','id','qty_for_single']);
        $data_items = modify_array($data_items);
        try {
            //Update the model using repository update method
            $this->repository->update($part, compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Product Part', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.parts.index'), ['flash_success' => 'Product Part updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletepartRequestNamespace $request
     * @param App\Models\part\part $part
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Part $part)
    {
        try {
            //Calling the delete method on repository
            $this->repository->delete($part);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error deleting Product Part', $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.parts.index'), ['flash_success' => 'Product Parts Deleted Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletepartRequestNamespace $request
     * @param App\Models\part\part $part
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Part $part)
    {

        //returning with successfull message
        $products = ProductVariation::whereHas('product', fn($q) => $q->where('stock_type', 'finished_goods'))->get();
        return new ViewResponse('focus.parts.view', compact('part','products'));
    }

    public function add_finished_product($part_id,Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->only(['product_id','note']);
            $part = Part::find($part_id);
            
            // foreach($part->part_items as $item){
            //     if($item->product->qty < $item->qty){
            //         throw ValidationException::withMessages(['Quantity Available to create the Finished Product by '.@$item->product->name.' is less than the required quantity']);
            //     }
            //     $item->product->qty -= $item->qty;
            //     $item->product->update();
            // }
            if($data['product_id'] != $part->product_id && isset($part->product_id))
            {
                //previous product
                $previous_product = ProductVariation::find($part->product_id);
                $previous_product->qty -= $part->total_qty;
                $previous_product->update();
                //New product
                $product = ProductVariation::find($data['product_id']);
                $product->qty += $part->total_qty; 
                $product->update();
            }else if($data['product_id'] == $part->product_id && isset($part->product_id))
            {
                return back()->with('flash_success','Finished Goods Already Updated!!');
            }else{
                $product = ProductVariation::find($data['product_id']);
                $product->qty += $part->total_qty; 
                $product->update();
            }

            $part->update($data);
            if($part){
                DB::commit();
            }
        } 
        catch (ValidationException $e) {
            // Return validation errors
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
        catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return errorHandler('Error Updating FG Product Qty', $th);
        }
        return back()->with('flash_success','Finished Goods Updated Successfully!!');
    }

    public function get_items(Request $request)
    {
        $fg_id = $request->finished_good_id;
        $f_good = Part::find($fg_id);
        $items = $f_good->part_items()->get()
        ->map(function($v){
            $v->product_name = $v->product ? $v->product->name : '';
            $v->unit_id = $v->unit ? @$v->unit->id : '';
            $v->uom = $v->unit ? @$v->unit->code : '';
            $v->code = $v->product ? $v->product->code : '';
            $v->qty_requested = 0;
            $v->price = $v->product ? fifoCost($v->product_id) : '';
            $v->milestone_item_id = '';
            $v->budget_item_id = '';
            $v->part_item_id = $v->id;
            return $v;
        });
        return response()->json($items);
    }
}
