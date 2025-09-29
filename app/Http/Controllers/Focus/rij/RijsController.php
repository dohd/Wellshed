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
namespace App\Http\Controllers\Focus\rij;

use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\account\CreateResponse;
use App\Http\Responses\Focus\account\EditResponse;
use App\Repositories\Focus\account\AccountRepository;
use App\Http\Requests\Focus\account\ManageAccountRequest;
use App\Http\Requests\Focus\account\StoreAccountRequest;
use Illuminate\Support\Facades\Response;
use mPDF;

/**
 * RijsController
 */
class RijsController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository ;
     */
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\account\ManageAccountRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageAccountRequest $request)
    {

        return new ViewResponse('focus.accounts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateAccountRequestNamespace $request
     * @return \App\Http\Responses\Focus\account\CreateResponse
     */
    public function create(StoreAccountRequest $request)
    {
        return new CreateResponse('focus.accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAccountRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreAccountRequest $request)
    {
        $request->validate([
            'number' => 'required',
            'holder' => 'required'
        ]);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $this->repository->create($input);
        //return with successfull message
        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\account\Account $account
     * @param EditAccountRequestNamespace $request
     * @return \App\Http\Responses\Focus\account\EditResponse
     */
    public function edit(Account $account, StoreAccountRequest $request)
    {
        return new EditResponse($account);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreAccountRequest $request, Account $account)
    {
        $request->validate([
            'number' => 'required',
            'holder' => 'required'
        ]);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($account, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Account $account, StoreAccountRequest $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($account);
        //returning with successfull message
        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Account $account, ManageAccountRequest $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.accounts.view', compact('account'));
    }
    public function account_search(Request $request, $bill_type)
    {
    
        if (!access()->allow('product_search')) return false;

        $q = $request->post('keyword');
        $w = $request->post('wid');
        $s = $request->post('serial_mode');
        if ($bill_type == 'label') $q = @$q['term'];
        $wq = compact('q', 'w');
            

         $account = Account::where('holder', 'LIKE', '%' . $q . '%')
           -> where('account_type' ,'Expenses')
           -> orWhere('number', 'LIKE', '%' . $q . '%')->limit(6)->get();
            $output = array();

            foreach ($account as $row) {

                 if ($row->id > 0) {
         $output[] = array('name' => $row->holder . ' - '.$row->number, 'id' => $row['id']);
            }
                
            }

        

        if (count($output) > 0)

            return view('focus.products.partials.search')->withDetails($output);
    }

    public function balance_sheet(Request $request)
    {
        $bg_styles = array('bg-gradient-x-info', 'bg-gradient-x-purple', 'bg-gradient-x-grey-blue', 'bg-gradient-x-danger', 'bg-gradient-x-success', 'bg-gradient-x-warning');
        $account = Account::all();
        $account_types = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 17)->first('value1');
        $account_types = json_decode($account_types->value1, true);
        if ($request->type == 'v') {
            return new ViewResponse('focus.accounts.balance_sheet', compact('account', 'bg_styles', 'account_types'));
        } else {

            $html = view('focus.accounts.print_balance_sheet', compact('account', 'account_types'))->render();
            $pdf = new \Mpdf\Mpdf(config('pdf'));
            $pdf->WriteHTML($html);
               $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                );
               return Response::stream($pdf->Output('balance_sheet.pdf', 'I'), 200, $headers);
        }

    }


       public function trial_balance(Request $request)
    {
        $bg_styles = array('bg-gradient-x-info', 'bg-gradient-x-purple', 'bg-gradient-x-grey-blue', 'bg-gradient-x-danger', 'bg-gradient-x-success', 'bg-gradient-x-warning');
        $account = Account::orderBy('number', 'asc')->get();
        $account_types = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 17)->first('value1');
        $account_types = json_decode($account_types->value1, true);
        if ($request->type == 'v') {
            return new ViewResponse('focus.accounts.trial_balance', compact('account', 'bg_styles', 'account_types'));
        } else {

            $html = view('focus.accounts.print_balance_sheet', compact('account', 'account_types'))->render();
            $pdf = new \Mpdf\Mpdf(config('pdf'));
            $pdf->WriteHTML($html);
               $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                );
               return Response::stream($pdf->Output('balance_sheet.pdf', 'I'), 200, $headers);
        }

    }


}
