<?php

namespace App\Http\Controllers\Focus\client_product;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\client_product\ClientProduct;
use App\Models\customer\Customer;
use App\Repositories\Focus\client_product\ClientProductRepository;
use Illuminate\Http\Request;

class ClientProductsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ClientProductRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ClientProductRepository $repository ;
     */
    public function __construct(ClientProductRepository $repository)
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
        $customers = Customer::whereHas('client_products')->get(['id', 'company']);
        $contracts = ClientProduct::get(['contract', 'customer_id'])->unique('contract');
        $contracts = [...$contracts];

        return new ViewResponse('focus.client_products.index', compact('customers', 'contracts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::get(['id', 'company']);
        $contracts = ClientProduct::get(['contract', 'customer_id'])->unique('contract');
        $contracts = [...$contracts];

        return new ViewResponse('focus.client_products.create', compact('customers','contracts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.client_products.index'), ['flash_success' => 'Pricelist Item Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $client_product = ClientProduct::find($id);
        return view('focus.client_products.view', compact('client_product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $client_product = ClientProduct::find($id);
        $customers = Customer::get(['id', 'company']);

        return view('focus.client_products.edit', compact('client_product', 'customers'));
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
        $client_product = ClientProduct::find($id);
        $this->repository->update($client_product, $request->except('_token'));

        return new RedirectResponse(route('biller.client_products.index'), ['flash_success' => 'Pricelist Item Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if ($id == 0) {
            $request->validate(['customer_id' => 'required']);
            $this->repository->mass_delete($request->except('_token'));
        } else {
            $client_product = ClientProduct::find($id);
            $this->repository->delete($client_product);    
        }
            
        return new RedirectResponse(route('biller.client_products.index'), ['flash_success' => 'Pricelist Item Deleted Successfully']);
    }
    
    public function store_code(Request $request){
        //dd($request->all());
        $client_product = ClientProduct::find($request->id);
        $client_product->product_code = $request->product_code;
        $client_product->item_id = $request->item_id;
        $client_product->update();
        return new RedirectResponse(route('biller.client_products.index'), ['flash_success' => 'ClientProduct Code Added!!']);
    }
}
