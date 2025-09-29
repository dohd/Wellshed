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
namespace App\Http\Controllers\Focus\estimate;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\estimate\Estimate;
use App\Models\items\VerifiedItem;
use App\Models\product\ProductVariation;
use App\Models\quote\Quote;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\estimate\EstimateRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EstimatesController extends Controller
{
    /**
     * variable to store the repository object
     * @var EstimateRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param EstimateRepository $repository ;
     */
    public function __construct(EstimateRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.estimates.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $customers = Customer::whereHas('quotes', fn($q) => $q->whereHas('verified_products'))
        ->get(['id', 'company', 'name']);
        
        return view('focus.estimates.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) { 
            return errorHandler('Error Creating Estimate', $th);
        }
    
        return new RedirectResponse(route('biller.estimates.index'), ['flash_success' => 'Estimate Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Estimate $estimate
     * @return \Illuminate\Http\Response
     */
    public function edit(Estimate $estimate)
    {
        if ($estimate->invoice) 
            throw ValidationException::withMessages(['Not allowed, Estimate has related Invoice: ' . gen4tid('INV-', $estimate->invoice->tid)]);

        $customers = Customer::whereHas('quotes', fn($q) => $q->whereHas('verified_products'))
        ->get(['id', 'company', 'name']);

        $estimate['is_editable'] = false;
        $last_estimate = Estimate::where('quote_id', $estimate->quote_id)->get(['id'])->last();
        if ($last_estimate->id == $estimate->id) $estimate['is_editable'] = true;
    
        return view('focus.estimates.edit', compact('estimate', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Estimate $estimate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Estimate $estimate)
    {
        try {
            $this->repository->update($estimate, $request->except('_token', '_method'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Estimate', $th);
        }

        return new RedirectResponse(route('biller.estimates.index'), ['flash_success' => 'Estimate Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Estimate $estimate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Estimate $estimate)
    {
        if ($estimate->invoice) 
            throw ValidationException::withMessages(['Not allowed, Estimate has related Invoice: ' . gen4tid('INV-', $estimate->invoice->tid)]);
    
        try {
            $this->repository->delete($estimate);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Estimate', $th);
        }

        return new RedirectResponse(route('biller.estimates.index'), ['flash_success' => 'Estimate Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  Estimate $estimate
     * @return \Illuminate\Http\Response
     */
    public function show(Estimate $estimate)
    {
        return view('focus.estimates.view', compact('estimate'));
    }

    /**
     * Quote Select
     */
    public function quote_select(Request $request)
    {   
        $k = $request->keyword;
        $quotes = Quote::where(['customer_id' => request('customer_id'), 'verified' => 'Yes'])
            ->whereNotNull('verification_date')
            ->whereHas('verified_products')
            ->where(function($q) use($k) {
                $chars = substr($k, 0, 1);
                if ($chars == 'QT' || $chars == 'PI') {
                    $chars = substr($k,3);
                    $q->where('tid', "%{$chars}%");
                } else $q->where('notes', 'LIKE', "%{$k}%");
            })
            ->limit(10)
            ->get(['id', 'tid', 'notes'])
            ->map(function($v) {
                return (object)[
                    'id' => $v->id, 
                    'name' => gen4tid($v->bank_id? 'PI-': 'QT-', $v->tid) . ' ' . $v->notes,
                ];
            });

        return response()->json($quotes);
    }

    /**
     * Verified Products
     */
    public function verified_products()
    {   
        $items = collect();
        $verified_items = VerifiedItem::where('quote_id', request('quote_id'))->get();
        foreach ($verified_items as $key => $item) {
            $item_amount = $item->product_qty * ($item->product_price - $item->product_tax);
            $est_amount = $item->est_items->sum('est_amount');
            $item['rem_amount'] = round($item_amount - $est_amount);
            $rem_qty = $item->product_qty - $item->est_items->sum('est_qty');
            $item['rem_qty'] = $rem_qty > 0? $rem_qty : 0;
            unset($item['est_items']);
            if ($item['rem_amount'] != 0) $items->add($item);
        }

        return response()->json($items);
    }
}
