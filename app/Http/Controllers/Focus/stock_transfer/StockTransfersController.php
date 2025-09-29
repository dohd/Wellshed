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

namespace App\Http\Controllers\Focus\stock_transfer;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\hrm\Hrm;
use App\Models\lead\Lead;
use App\Models\stock_transfer\StockTransfer;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\stock_transfer\StockTransferRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockTransfersController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockTransferRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockTransferRepository $repository ;
     */
    public function __construct(StockTransferRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.stock_transfers.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $source_warehouses = Warehouse::whereHas('products')->get(['id', 'title']);
        $dest_warehouses = Warehouse::get(['id', 'title']);
        $employees = Hrm::select('id', 'first_name', 'last_name')->get();
        $leads = Lead::where('status', 0)->orderBy('id', 'DESC')->get();
        $prefixes = prefixesArray(['quote', 'lead'], auth()->user()->ins);

        return view('focus.stock_transfers.create', compact('source_warehouses', 'dest_warehouses', 'employees','leads','prefixes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        if ($request->source_id == $request->dest_id)
            throw ValidationException::withMessages(['Please choose a different transfer destination']);

        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Stock Transfer '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.stock_transfers.index'), ['flash_success' => 'Stock Transfer Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StockTransfer $stock_transfer
     * @return \Illuminate\Http\Response
     */
    public function edit(StockTransfer $stock_transfer)
    {
        if ($stock_transfer->stock_rcvs()->exists())
            throw ValidationException::withMessages(['Transfer with received goods cannot be edited']);

        $source_warehouses = Warehouse::whereHas('products')->get(['id', 'title']);
        $dest_warehouses = Warehouse::get(['id', 'title']);
        $employees = Hrm::select('id', 'first_name', 'last_name')->get();
        $leads = Lead::orderBy('id', 'DESC')->get();
        $prefixes = prefixesArray(['quote', 'lead'], auth()->user()->ins);

        return view('focus.stock_transfers.edit', compact('stock_transfer', 'source_warehouses', 'dest_warehouses', 'employees','leads','prefixes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  StockTransfer $stock_transfer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockTransfer $stock_transfer)
    {
        if ($request->source_id == $request->dest_id)
            throw ValidationException::withMessages(['Please choose a different transfer destination']);
        if ($stock_transfer->stock_rcvs()->exists())
            throw ValidationException::withMessages(['Transfer with received goods cannot be edited']);

        try {
            $this->repository->update($stock_transfer, $request->except('_token', '_method'));
        } catch (\Exception $ex) {

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
            return errorHandler('Error Updating Stock Transfer', $ex);
        }

        return new RedirectResponse(route('biller.stock_transfers.index'), ['flash_success' => 'StockTransfer Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  StockTransfer $stock_transfer
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockTransfer $stock_transfer)
    {
        if ($stock_transfer->stock_rcvs()->exists())
            throw ValidationException::withMessages(['Transfer with received goods cannot be deleted']);

        try {
            $this->repository->delete($stock_transfer);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Stock Transfer', $th);
        }

        return new RedirectResponse(route('biller.stock_transfers.index'), ['flash_success' => 'StockTransfer Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  StockTransfer $stock_transfer
     * @return \Illuminate\Http\Response
     */
    public function show(StockTransfer $stock_transfer)
    {
        return view('focus.stock_transfers.view', compact('stock_transfer'));
    }
}
