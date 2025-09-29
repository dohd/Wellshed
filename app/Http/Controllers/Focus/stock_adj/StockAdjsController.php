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
namespace App\Http\Controllers\Focus\stock_adj;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\stock_adj\StockAdj;
use App\Repositories\Focus\stock_adj\StockAdjRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockAdjsController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockAdjRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockAdjRepository $repository ;
     */
    public function __construct(StockAdjRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.stock_adjs.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['cost_of_goods_sold', 'misc_income']))
            ->get(['id', 'number', 'holder', 'account_type']);
        
        return view('focus.stock_adjs.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token', 'item_id'));
        } catch (\Throwable $th) { 
            return errorHandler('Error Creating Stock Adjustment', $th);
        }
    
        return new RedirectResponse(route('biller.stock_adjs.index'), ['flash_success' => 'Stock Adjustment Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StockAdj $stock_adj
     * @return \Illuminate\Http\Response
     */
    public function edit(StockAdj $stock_adj)
    {
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['cost_of_goods_sold', 'misc_income']))
            ->get(['id', 'number', 'holder', 'account_type']);

        return view('focus.stock_adjs.edit', compact('stock_adj', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  StockAdj $stock_adj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockAdj $stock_adj)
    {
        try {
            $this->repository->update($stock_adj, $request->except('_token', '_method'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Stock Adjustment', $th);
        }

        return new RedirectResponse(route('biller.stock_adjs.index'), ['flash_success' => 'Stock Adjustment Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  StockAdj $stock_adj
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockAdj $stock_adj)
    {
        try {
            $this->repository->delete($stock_adj);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Stock Adjustment', $th);
        }

        return new RedirectResponse(route('biller.stock_adjs.index'), ['flash_success' => 'Stock Adjustment Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  StockAdj $stock_adj
     * @return \Illuminate\Http\Response
     */
    public function show(StockAdj $stock_adj)
    {
        return view('focus.stock_adjs.view', compact('stock_adj'));
    }

    /**
     * Approve Stock Adjustment
     */
    public function approve_adjustment(StockAdj $stock_adj, Request $request)
    { 
        try {
            $this->repository->approveStockAdjustment($stock_adj, $request->only('approval_status'));
        } catch (\Throwable $th) {
            return errorHandler('Error Approving Stock Adjustment', $th);
        }

        return new RedirectResponse(route('biller.stock_adjs.index'), ['flash_success' => 'Stock Adjustment Approved Successfully']);
    }
}
