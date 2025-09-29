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

namespace App\Http\Controllers\Focus\stock_rcv;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\hrm\Hrm;
use App\Models\stock_rcv\StockRcv;
use App\Models\stock_transfer\StockTransfer;
use App\Repositories\Focus\stock_rcv\StockRcvRepository;
use Illuminate\Http\Request;

class StockRcvsController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockRcvRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockRcvRepository $repository ;
     */
    public function __construct(StockRcvRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.stock_rcvs.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        $employees = Hrm::where([
            'customer_id' => null,
            'supplier_id' => null,
            'client_vendor_id' => null,
            'client_user_id' => null,
        ])->get(['id', 'first_name', 'last_name']);
        
        $stock_transfer = StockTransfer::find(request('stock_transfer_id'));

        return view('focus.stock_rcvs.create', compact('employees', 'stock_transfer'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Stock Receiving '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.stock_rcvs.index'), ['flash_success' => 'Stock Receiving Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StockRcv $stock_rcv
     * @return \Illuminate\Http\Response
     */
    public function edit(StockRcv $stock_rcv)
    {
        $employees = Hrm::where([
            'customer_id' => null,
            'supplier_id' => null,
            'client_vendor_id' => null,
            'client_user_id' => null,
        ])->get(['id', 'first_name', 'last_name']);

        return view('focus.stock_rcvs.edit', compact('stock_rcv', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  StockRcv $stock_rcv
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockRcv $stock_rcv)
    {
        try {
            $this->repository->update($stock_rcv, $request->except('_token', '_method'));
        } catch (\Throwable $th) { dd($th);
            return errorHandler('Error Updating Stock Receiving', $th);
        }

        return new RedirectResponse(route('biller.stock_rcvs.index'), ['flash_success' => 'StockRcv Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  StockRcv $stock_rcv
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockRcv $stock_rcv)
    {
        try {
            $this->repository->delete($stock_rcv);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Stock Receiving', $th);
        }

        return new RedirectResponse(route('biller.stock_rcvs.index'), ['flash_success' => 'StockRcv Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  StockRcv $stock_rcv
     * @return \Illuminate\Http\Response
     */
    public function show(StockRcv $stock_rcv)
    {
        return view('focus.stock_rcvs.view', compact('stock_rcv'));
    }
}
