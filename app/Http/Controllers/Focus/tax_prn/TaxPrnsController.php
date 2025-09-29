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

namespace App\Http\Controllers\Focus\tax_prn;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\tax_prn\TaxPrn;
use App\Repositories\Focus\tax_prn\TaxPrnRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TaxPrnsController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaxPrnRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TaxPrnRepository $repository ;
     */
    public function __construct(TaxPrnRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.tax_prns.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dates = [new Carbon('first day of this month'), new Carbon('last day of this month')];
        $month = date('m')-1? date('m')-1 : 12;
        $year = date('m')-1? date('Y') : date('Y')-1;
        $prev_month = strlen($month) == 1? "0{$month}-{$year}" : "{$month}-{$year}";

        return view('focus.tax_prns.create', compact('dates', 'prev_month'));
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
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Tax PRN', $th);
        }

        return new RedirectResponse(route('biller.tax_prns.index'), ['flash_success' => 'Tax PRN Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  TaxPrn $tax_prn
     * @return \Illuminate\Http\Response
     */
    public function edit(TaxPrn $tax_prn)
    {
        return view('focus.tax_prns.edit', compact('tax_prn'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  TaxPrn $tax_prn
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TaxPrn $tax_prn)
    {
        try {
            $this->repository->update($tax_prn, $request->except('_token'));
        } catch (\Throwable $th) { 
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Tax PRN', $th);
        }

        return new RedirectResponse(route('biller.tax_prns.index'), ['flash_success' => 'Tax PRN Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  TaxPrn $tax_prn
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaxPrn $tax_prn)
    {
        try {
            $this->repository->delete($tax_prn);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Tax PRN', $th);
        }

        return new RedirectResponse(route('biller.tax_prns.index'), ['flash_success' => 'Tax PRN Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  TaxPrn $tax_prn
     * @return \Illuminate\Http\Response
     */
    public function show(TaxPrn $tax_prn)
    {
        return view('focus.tax_prns.view', compact('tax_prn'));
    }
}
