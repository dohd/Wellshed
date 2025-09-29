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
namespace App\Http\Controllers\Focus\withholding;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\withholding\CreateResponse;
use App\Repositories\Focus\withholding\WithholdingRepository;
use App\Http\Requests\Focus\withholding\ManageWithholdingRequest;
use App\Http\Requests\Focus\withholding\StoreWithholdingRequest;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\withholding\Withholding;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

/**
 * BanksController
 */
class WithholdingsController extends Controller
{
    /**
     * variable to store the repository object
     * @var WithholdingRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param WithholdingRepository $repository ;
     */
    public function __construct(WithholdingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\bank\ManageBankRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageWithholdingRequest $request)
    {
        return new ViewResponse('focus.withholdings.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\CreateResponse
     */
    public function create(StoreWithholdingRequest $request)
    {
        return new CreateResponse('focus.withholdings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBankRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreWithholdingRequest $request)
    {
        // extract request fields
        $data = $request->only([
            'customer_id', 'tid', 'certificate', 'cert_date', 'tr_date', 'amount', 'reference', 'allocate_ttl', 
            'note', 'withholding_tax_id'
        ]);
        $data_items = $request->only(['invoice_id', 'paid', 'paid_invoice_item_id']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => numberClean(@$v['paid']) > 0);
        $data['allocate_ttl'] = array_reduce($data_items, fn($prev, $curr) => $prev + numberClean(@$curr['paid']), 0);

        try {
            $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Withholding Certificate', $th);
        }

       return new RedirectResponse(route('biller.withholdings.index'), ['flash_success' => 'Withholding Certificate Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\bank\Bank $bank
     * @param EditBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\EditResponse
     */
    public function edit(Withholding $withholding)
    {
        $customers = Customer::whereHas('currency', fn($q) => $q->where('rate', 1))->get(['id', 'company', 'name']);
        return redirect(route('biller.withholdings.index'));

        // return new EditResponse($withholding);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreWithholdingRequest $request, Withholding $withholding)
    {
        try {
            $this->repository->update($withholding, $request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Withholding Certificate', $th);
        }

        return new RedirectResponse(route('biller.withholdings.index'), ['flash_success' => 'Withholding Certificate Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Withholding $withholding)
    {
        try {
            $this->repository->delete($withholding);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Withholding Certificate', $th);
        }

        return new RedirectResponse(route('biller.withholdings.index'), ['flash_success' => 'Withholding Certificate Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Withholding $withholding)
    {
        return new ViewResponse('focus.withholdings.view', compact('withholding'));
    }

    /**
     * Unallocated Withholdings
     */
    public function select_unallocated_wh_tax(Request $request)
    {
        $wh_tax = Withholding::where('customer_id', request('customer_id'))
        ->where('certificate', 'tax')
        ->whereColumn('amount', '!=', 'allocate_ttl')
        ->orderBy('tr_date', 'ASC')
        ->get();

        return response()->json($wh_tax);
    }

    
    /**
     * Select Customer Invoices
     */
    public function select_invoices(Request $request)
    {
        $cert_type = $request->cert_type;
        $invoices = Invoice::where('customer_id', request('customer_id'))->whereIn('status', ['due', 'partial'])
        ->whereHas('currency', function($q) {
            if (request('currency_id')) $q->where('currency_id', request('currency_id'));
            else $q->where('rate', 1);
        })
        ->with('payments')
        ->orderBy('invoiceduedate', 'ASC')
        ->get()
        ->map(function($v) use($cert_type) {
            if ($cert_type == 'vat') $payments = $v->payments->where('wh_vat', '>', 0);
            elseif ($cert_type == 'tax') $payments = $v->payments->where('wh_tax', '>', 0);
            $v->paid_invoice_item_id = $payments->count()? $payments->first()->id : null;
            $v->receipt_amount = $cert_type == 'vat' && $payments->count()? 
                $payments->first()->wh_vat : ($cert_type == 'tax' && $payments->count()? $payments->first()->wh_tax : 0);
                
            return $v;
        });
        
        return response()->json($invoices);
    }
}
